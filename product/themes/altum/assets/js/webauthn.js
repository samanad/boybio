'use strict';

window.cloubWebauthn = {
    b64urlToBuf(value) {
        value = (value || '').replace(/-/g, '+').replace(/_/g, '/');
        while (value.length % 4) {
            value += '=';
        }
        const binary = atob(value);
        const bytes = new Uint8Array(binary.length);
        for (let i = 0; i < binary.length; i++) {
            bytes[i] = binary.charCodeAt(i);
        }
        return bytes.buffer;
    },

    bufToB64url(buffer) {
        const bytes = new Uint8Array(buffer);
        let binary = '';
        bytes.forEach(byte => {
            binary += String.fromCharCode(byte);
        });
        return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/g, '');
    },

    preparePublicKey(publicKey) {
        const copy = JSON.parse(JSON.stringify(publicKey));
        copy.challenge = this.b64urlToBuf(publicKey.challenge);
        if (copy.user && copy.user.id) {
            copy.user.id = this.b64urlToBuf(publicKey.user.id);
        }
        ['excludeCredentials', 'allowCredentials'].forEach(key => {
            if (Array.isArray(copy[key])) {
                copy[key] = copy[key].map(item => ({
                    ...item,
                    id: this.b64urlToBuf(item.id)
                }));
            }
        });
        return copy;
    },

    credentialToJson(credential) {
        const response = credential.response;
        const json = {
            id: credential.id,
            rawId: this.bufToB64url(credential.rawId),
            type: credential.type,
            clientDataJSON: this.bufToB64url(response.clientDataJSON)
        };

        if (response.attestationObject) {
            json.attestationObject = this.bufToB64url(response.attestationObject);
        }

        json.response = {
            clientDataJSON: json.clientDataJSON,
            authenticatorData: response.authenticatorData ? this.bufToB64url(response.authenticatorData) : null,
            signature: response.signature ? this.bufToB64url(response.signature) : null,
            userHandle: response.userHandle ? this.bufToB64url(response.userHandle) : null,
            attestationObject: response.attestationObject ? this.bufToB64url(response.attestationObject) : null
        };

        return json;
    },

    async post(requestType, extra = {}) {
        const body = new URLSearchParams();
        body.set('request_type', requestType);
        body.set('global_token', global_token);
        Object.keys(extra).forEach(key => {
            body.set(key, extra[key]);
        });

        const result = await fetch(url + 'webauthn-ajax', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body
        });

        return result.json();
    }
};
