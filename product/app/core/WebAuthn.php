<?php
/*
 * WebAuthn / FIDO2 security keys for cloub.io
 */

namespace Altum;

defined('ALTUMCODE') || die();

class WebAuthn {

    public static function ensure_table() {
        static $ready = false;

        if($ready) {
            return;
        }

        try {
            db()->rawQuery("CREATE TABLE IF NOT EXISTS `users_security_keys` (
                `security_key_id` int unsigned NOT NULL AUTO_INCREMENT,
                `user_id` int NOT NULL,
                `name` varchar(64) NOT NULL DEFAULT '',
                `credential_id` varchar(1024) NOT NULL,
                `public_key` text NOT NULL,
                `counter` int unsigned NOT NULL DEFAULT 0,
                `datetime` datetime DEFAULT NULL,
                `last_used_datetime` datetime DEFAULT NULL,
                PRIMARY KEY (`security_key_id`),
                UNIQUE KEY `credential_id` (`credential_id`(191)),
                KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch(\Exception $exception) {
            /* Table may already exist */
        }

        $ready = true;
    }

    public static function rp_id() {
        return parse_url(SITE_URL, PHP_URL_HOST);
    }

    public static function origin() {
        $parts = parse_url(SITE_URL);

        $origin = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? '');

        if(!empty($parts['port'])) {
            $origin .= ':' . $parts['port'];
        }

        return $origin;
    }

    public static function random_challenge() {
        return random_bytes(32);
    }

    public static function b64url_encode($binary) {
        return rtrim(strtr(base64_encode($binary), '+/', '-_'), '=');
    }

    public static function b64url_decode($value) {
        $value = strtr((string) $value, '-_', '+/');
        $pad = strlen($value) % 4;

        if($pad) {
            $value .= str_repeat('=', 4 - $pad);
        }

        $decoded = base64_decode($value, true);

        return $decoded === false ? '' : $decoded;
    }

    public static function registration_options($user) {
        $challenge = self::random_challenge();
        $_SESSION['webauthn_register_challenge'] = self::b64url_encode($challenge);

        $exclude = [];
        foreach(self::credentials_for_user($user->user_id) as $row) {
            $exclude[] = [
                'type' => 'public-key',
                'id' => $row->credential_id,
            ];
        }

        return [
            'rp' => [
                'name' => settings()->main->title,
                'id' => self::rp_id(),
            ],
            'user' => [
                'id' => self::b64url_encode(pack('N', (int) $user->user_id) . hash('sha256', 'cloub-webauthn-' . $user->user_id, true)),
                'name' => $user->email,
                'displayName' => $user->name ?: $user->email,
            ],
            'challenge' => self::b64url_encode($challenge),
            'pubKeyCredParams' => [
                ['type' => 'public-key', 'alg' => -7],
                ['type' => 'public-key', 'alg' => -257],
            ],
            'timeout' => 120000,
            'attestation' => 'none',
            'excludeCredentials' => $exclude,
            'authenticatorSelection' => [
                'authenticatorAttachment' => 'cross-platform',
                'residentKey' => 'preferred',
                'requireResidentKey' => false,
                'userVerification' => 'preferred',
            ],
        ];
    }

    public static function assertion_options($user = null) {
        $challenge = self::random_challenge();
        $_SESSION['webauthn_login_challenge'] = self::b64url_encode($challenge);

        $allow = [];
        if($user) {
            foreach(self::credentials_for_user($user->user_id) as $row) {
                $allow[] = [
                    'type' => 'public-key',
                    'id' => $row->credential_id,
                ];
            }
        }

        return [
            'challenge' => self::b64url_encode($challenge),
            'timeout' => 120000,
            'rpId' => self::rp_id(),
            'allowCredentials' => $allow,
            'userVerification' => 'preferred',
        ];
    }

    public static function credentials_for_user($user_id) {
        self::ensure_table();

        return db()->where('user_id', $user_id)->get('users_security_keys') ?: [];
    }

    public static function verify_registration($attestation) {
        $challenge_b64 = $_SESSION['webauthn_register_challenge'] ?? null;
        unset($_SESSION['webauthn_register_challenge']);

        if(!$challenge_b64) {
            throw new \Exception('expired');
        }

        $client_data_json = self::b64url_decode($attestation['clientDataJSON'] ?? '');
        $attestation_object = self::b64url_decode($attestation['attestationObject'] ?? '');

        $client = json_decode($client_data_json, true);
        if(!is_array($client) || ($client['type'] ?? '') !== 'webauthn.create') {
            throw new \Exception('client');
        }

        if(self::b64url_decode($client['challenge'] ?? '') !== self::b64url_decode($challenge_b64)) {
            throw new \Exception('challenge');
        }

        if(($client['origin'] ?? '') !== self::origin()) {
            throw new \Exception('origin');
        }

        $cbor = self::cbor_decode($attestation_object);
        $auth_data = $cbor['authData'] ?? '';

        if(strlen($auth_data) < 37) {
            throw new \Exception('authdata');
        }

        $rp_id_hash = substr($auth_data, 0, 32);
        if(!hash_equals($rp_id_hash, hash('sha256', self::rp_id(), true))) {
            throw new \Exception('rpid');
        }

        $flags = ord($auth_data[32]);
        if(!($flags & 0x40)) {
            throw new \Exception('attested');
        }

        $offset = 37;
        $offset += 16;
        $cred_id_len = unpack('n', substr($auth_data, $offset, 2))[1];
        $offset += 2;
        $credential_id = substr($auth_data, $offset, $cred_id_len);
        $offset += $cred_id_len;
        $cose = substr($auth_data, $offset);
        $cose_key = self::cbor_decode($cose);
        $pem = self::cose_to_pem($cose_key);

        return [
            'credential_id' => self::b64url_encode($credential_id),
            'public_key' => $pem,
            'counter' => unpack('N', substr($auth_data, 33, 4))[1],
        ];
    }

    public static function verify_assertion($assertion) {
        $challenge_b64 = $_SESSION['webauthn_login_challenge'] ?? null;
        unset($_SESSION['webauthn_login_challenge']);

        if(!$challenge_b64) {
            throw new \Exception('expired');
        }

        $credential_id = $assertion['id'] ?? $assertion['rawId'] ?? '';
        $row = db()->where('credential_id', $credential_id)->getOne('users_security_keys');

        if(!$row) {
            throw new \Exception('unknown');
        }

        $client_data_json = self::b64url_decode($assertion['response']['clientDataJSON'] ?? $assertion['clientDataJSON'] ?? '');
        $authenticator_data = self::b64url_decode($assertion['response']['authenticatorData'] ?? $assertion['authenticatorData'] ?? '');
        $signature = self::b64url_decode($assertion['response']['signature'] ?? $assertion['signature'] ?? '');

        $client = json_decode($client_data_json, true);
        if(!is_array($client) || ($client['type'] ?? '') !== 'webauthn.get') {
            throw new \Exception('client');
        }

        if(self::b64url_decode($client['challenge'] ?? '') !== self::b64url_decode($challenge_b64)) {
            throw new \Exception('challenge');
        }

        if(($client['origin'] ?? '') !== self::origin()) {
            throw new \Exception('origin');
        }

        if(strlen($authenticator_data) < 37) {
            throw new \Exception('authdata');
        }

        $rp_id_hash = substr($authenticator_data, 0, 32);
        if(!hash_equals($rp_id_hash, hash('sha256', self::rp_id(), true))) {
            throw new \Exception('rpid');
        }

        $flags = ord($authenticator_data[32]);
        if(!($flags & 0x01)) {
            throw new \Exception('up');
        }

        $counter = unpack('N', substr($authenticator_data, 33, 4))[1];
        if($row->counter > 0 && $counter <= $row->counter) {
            throw new \Exception('counter');
        }

        $client_hash = hash('sha256', $client_data_json, true);
        $signed = $authenticator_data . $client_hash;
        $signature = self::normalize_ecdsa_signature($signature);

        $ok = openssl_verify($signed, $signature, $row->public_key, OPENSSL_ALGO_SHA256);
        if($ok !== 1) {
            throw new \Exception('signature');
        }

        db()->where('security_key_id', $row->security_key_id)->update('users_security_keys', [
            'counter' => $counter,
            'last_used_datetime' => get_date(),
        ]);

        return $row;
    }

    private static function cose_to_pem($cose) {
        $kty = $cose[1] ?? null;
        $alg = $cose[3] ?? null;

        if($kty == 2 && ($alg == -7)) {
            $x = $cose[-2] ?? '';
            $y = $cose[-3] ?? '';

            if(strlen($x) !== 32 || strlen($y) !== 32) {
                throw new \Exception('cose');
            }

            $point = "\x04" . $x . $y;
            $bit_string = "\x03" . self::der_length(1 + strlen($point)) . "\x00" . $point;
            $alg_id = hex2bin('301306072a8648ce3d020106082a8648ce3d030107');
            $spki = "\x30" . self::der_length(strlen($alg_id) + strlen($bit_string)) . $alg_id . $bit_string;

            return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($spki), 64, "\n") . "-----END PUBLIC KEY-----\n";
        }

        if($kty == 3 && $alg == -257) {
            $n = $cose[-1] ?? '';
            $e = $cose[-2] ?? '';

            $modulus = "\x02" . self::der_length(strlen($n) + ((ord($n[0]) & 0x80) ? 1 : 0)) . ((ord($n[0]) & 0x80) ? "\x00" : '') . $n;
            $exponent = "\x02" . self::der_length(strlen($e) + ((ord($e[0]) & 0x80) ? 1 : 0)) . ((ord($e[0]) & 0x80) ? "\x00" : '') . $e;
            $rsa = "\x30" . self::der_length(strlen($modulus) + strlen($exponent)) . $modulus . $exponent;
            $bit_string = "\x03" . self::der_length(1 + strlen($rsa)) . "\x00" . $rsa;
            $alg_id = hex2bin('300d06092a864886f70d0101010500');
            $spki = "\x30" . self::der_length(strlen($alg_id) + strlen($bit_string)) . $alg_id . $bit_string;

            return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($spki), 64, "\n") . "-----END PUBLIC KEY-----\n";
        }

        throw new \Exception('alg');
    }

    private static function normalize_ecdsa_signature($signature) {
        if(strlen($signature) === 64) {
            $r = substr($signature, 0, 32);
            $s = substr($signature, 32, 32);
            $r = ltrim($r, "\x00");
            $s = ltrim($s, "\x00");
            if(ord($r[0]) & 0x80) {
                $r = "\x00" . $r;
            }
            if(ord($s[0]) & 0x80) {
                $s = "\x00" . $s;
            }
            $r_der = "\x02" . self::der_length(strlen($r)) . $r;
            $s_der = "\x02" . self::der_length(strlen($s)) . $s;
            return "\x30" . self::der_length(strlen($r_der) + strlen($s_der)) . $r_der . $s_der;
        }

        return $signature;
    }

    private static function der_length($length) {
        if($length < 128) {
            return chr($length);
        }

        $bytes = ltrim(pack('N', $length), "\x00");

        return chr(0x80 | strlen($bytes)) . $bytes;
    }

    private static function cbor_decode($bin, &$offset = 0) {
        $ib = ord($bin[$offset++]);
        $major = $ib >> 5;
        $ai = $ib & 0x1f;
        $value = self::cbor_argument($bin, $offset, $ai);

        switch($major) {
            case 0:
                return $value;
            case 1:
                return -1 - $value;
            case 2:
                $out = substr($bin, $offset, $value);
                $offset += $value;
                return $out;
            case 3:
                $out = substr($bin, $offset, $value);
                $offset += $value;
                return $out;
            case 4:
                $arr = [];
                for($i = 0; $i < $value; $i++) {
                    $arr[] = self::cbor_decode($bin, $offset);
                }
                return $arr;
            case 5:
                $map = [];
                for($i = 0; $i < $value; $i++) {
                    $key = self::cbor_decode($bin, $offset);
                    $map[$key] = self::cbor_decode($bin, $offset);
                }
                return $map;
            case 6:
                return self::cbor_decode($bin, $offset);
            case 7:
                if($ai === 20) return false;
                if($ai === 21) return true;
                if($ai === 22) return null;
                return $value;
        }

        throw new \Exception('cbor');
    }

    private static function cbor_argument($bin, &$offset, $ai) {
        if($ai < 24) {
            return $ai;
        }

        if($ai === 24) {
            return ord($bin[$offset++]);
        }

        if($ai === 25) {
            $n = unpack('n', substr($bin, $offset, 2))[1];
            $offset += 2;
            return $n;
        }

        if($ai === 26) {
            $n = unpack('N', substr($bin, $offset, 4))[1];
            $offset += 4;
            return $n;
        }

        if($ai === 27) {
            $hi = unpack('N', substr($bin, $offset, 4))[1];
            $lo = unpack('N', substr($bin, $offset + 4, 4))[1];
            $offset += 8;
            return ($hi << 32) + $lo;
        }

        throw new \Exception('cbor');
    }

}
