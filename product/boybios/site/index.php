<?php
require __DIR__ . '/config.php';
session_start();

$downloads = __DIR__ . '/downloads';
$logs = __DIR__ . '/logs';
$apk = $downloads . '/cloub.apk';
$status_file = $logs . '/status.json';
$log_file = $logs . '/build.log';
$lock = $logs . '/build.lock';

if (!is_dir($downloads)) mkdir($downloads, 0755, true);
if (!is_dir($logs)) mkdir($logs, 0755, true);

function logged_in(): bool {
    return !empty($_SESSION['boybios_ok']);
}

function status(): array {
    global $status_file, $lock, $apk, $log_file;
    if (!is_file($status_file)) {
        $st = ['state' => 'idle', 'message' => 'No build yet'];
    } else {
        $json = json_decode((string) file_get_contents($status_file), true);
        $st = is_array($json) ? $json : ['state' => 'idle', 'message' => 'Unknown'];
    }
    $pid = is_file($lock) ? (int) trim((string) file_get_contents($lock)) : 0;
    $alive = $pid > 1 && file_exists("/proc/$pid");
    if (!$alive && is_file($lock)) {
        @unlink($lock);
    }
    $st['building'] = $alive || (($st['state'] ?? '') === 'running' && $alive);
    if (!$alive && ($st['state'] ?? '') === 'running' && is_file($apk)) {
        $st['state'] = 'ok';
        $st['message'] = 'APK ready';
    }
    $st['has_apk'] = is_file($apk);
    $st['apk_size'] = is_file($apk) ? filesize($apk) : 0;
    $st['apk_time'] = is_file($apk) ? date('Y-m-d H:i', filemtime($apk)) : '';
    $st['log'] = is_file($log_file) ? (string) file_get_contents($log_file) : '';
    return $st;
}

function building(): bool {
    return !empty(status()['building']);
}

function redirect_home(string $error = ''): void {
    if ($error !== '') {
        $_SESSION['flash'] = $error;
    }
    header('Location: ./');
    exit;
}

if (isset($_GET['status']) && logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(status());
    exit;
}

if (isset($_GET['file']) && $_GET['file'] === 'apk' && logged_in() && is_file($apk)) {
    header('Content-Type: application/vnd.android.package-archive');
    header('Content-Disposition: attachment; filename="cloub.apk"');
    header('Content-Length: ' . filesize($apk));
    readfile($apk);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'login') {
        if (hash_equals(BUILD_PASSWORD, (string) ($_POST['password'] ?? ''))) {
            $_SESSION['boybios_ok'] = 1;
            redirect_home();
        }
        redirect_home('Wrong password');
    }
    if ($action === 'logout') {
        session_destroy();
        header('Location: ./');
        exit;
    }
    if ($action === 'build' && logged_in()) {
        if (building()) {
            redirect_home('A build is already running');
        }
        $script = __DIR__ . '/build-apk.sh';
        if (!is_executable($script)) {
            @chmod($script, 0755);
        }
        $cmd = 'nohup bash ' . escapeshellarg($script) . ' >> ' . escapeshellarg($log_file) . ' 2>&1 & echo $!';
        $pid = trim((string) shell_exec($cmd));
        if ($pid === '' || !ctype_digit($pid)) {
            redirect_home('Could not start the build. SSH: bash ' . $script);
        }
        file_put_contents($lock, $pid);
        file_put_contents($status_file, json_encode([
            'state' => 'running',
            'message' => 'Build started',
            'updated' => date('c'),
        ]));
        redirect_home();
    }
    redirect_home();
}

$error = (string) ($_SESSION['flash'] ?? '');
unset($_SESSION['flash']);
$st = status();
$is_building = !empty($st['building']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>cloub APK</title>
  <style>
    body { font-family: system-ui, sans-serif; max-width: 640px; margin: 40px auto; padding: 0 16px; color: #111; }
    button, input { font: inherit; padding: 10px 14px; }
    .card { border: 1px solid #ddd; border-radius: 12px; padding: 20px; }
    .ok { color: #0a7; }
    .err { color: #c00; }
    pre { background: #111; color: #ddd; padding: 12px; overflow: auto; max-height: 280px; font-size: 12px; white-space: pre-wrap; }
  </style>
</head>
<body>
  <h1>cloub APK</h1>
  <p>If the browser says the connection was interrupted or asks to resend the form: type this page URL again and press Enter. Do not click Resend. The build keeps running on the server.</p>

  <?php if (!logged_in()): ?>
    <div class="card">
      <?php if ($error): ?><p class="err"><?= htmlspecialchars($error) ?></p><?php endif; ?>
      <form method="post">
        <input type="hidden" name="action" value="login">
        <p><input type="password" name="password" placeholder="Builder password" required></p>
        <button type="submit">Enter</button>
      </form>
    </div>
  <?php else: ?>
    <div class="card">
      <?php if ($error): ?><p class="err"><?= htmlspecialchars($error) ?></p><?php endif; ?>
      <p>Status: <strong id="state"><?= htmlspecialchars($st['state'] ?? 'idle') ?></strong> — <span id="message"><?= htmlspecialchars($st['message'] ?? '') ?></span></p>
      <p id="apk-block">
        <?php if (!empty($st['has_apk'])): ?>
          <span class="ok">APK ready (<?= number_format($st['apk_size'] / 1048576, 2) ?> MB, <?= htmlspecialchars($st['apk_time']) ?>)</span><br>
          <a href="?file=apk">Download cloub.apk</a>
        <?php else: ?>
          No APK yet. First build downloads the SDK and can take several minutes.
        <?php endif; ?>
      </p>
      <form method="post">
        <input type="hidden" name="action" value="build">
        <button type="submit" id="build-btn" <?= $is_building ? 'disabled' : '' ?>><?= $is_building ? 'Building…' : 'Build APK' ?></button>
      </form>
      <form method="post" style="margin-top:8px">
        <input type="hidden" name="action" value="logout">
        <button type="submit">Log out</button>
      </form>
      <h2>Build log</h2>
      <pre id="log"><?= htmlspecialchars($st['log'] ?? '') ?></pre>
    </div>
    <script>
      function paint(data) {
        document.getElementById('state').textContent = data.state || 'idle';
        document.getElementById('message').textContent = data.message || '';
        document.getElementById('log').textContent = data.log || '';
        var btn = document.getElementById('build-btn');
        btn.disabled = !!data.building;
        btn.textContent = data.building ? 'Building…' : 'Build APK';
        var apk = document.getElementById('apk-block');
        if (data.has_apk) {
          apk.innerHTML = '<span class="ok">APK ready (' + (data.apk_size / 1048576).toFixed(2) + ' MB, ' + data.apk_time + ')</span><br><a href="?file=apk">Download cloub.apk</a>';
        }
      }
      async function tick() {
        try {
          var r = await fetch('?status=1', { cache: 'no-store' });
          if (r.ok) paint(await r.json());
        } catch (e) {}
      }
      setInterval(tick, 4000);
    </script>
  <?php endif; ?>
</body>
</html>
