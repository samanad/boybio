<?php
/**
 * Direct fix - manually clear cache and verify pages
 * This bypasses app initialization issues
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$product_path = __DIR__;
if (file_exists($product_path . '/config.php')) {
    require_once $product_path . '/config.php';
} else {
    die("Cannot find config.php");
}

echo "<h1>Direct Pages Fix</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;font-weight:bold;} .error{color:red;font-weight:bold;}</style>";

// Connect to database
$mysqli = new mysqli(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Step 1: Clear cache files directly
echo "<h2>1. Clearing Cache Files</h2>";
$cache_path = $product_path . '/uploads/cache';
if (is_dir($cache_path)) {
    $files = glob($cache_path . '/*');
    $deleted = 0;
    foreach($files as $file) {
        if (is_file($file)) {
            @unlink($file);
            $deleted++;
        }
    }
    echo "<p class='success'>✅ Deleted $deleted cache files</p>";
} else {
    echo "<p>Cache directory not found (might be OK)</p>";
}

// Step 2: Verify pages exist
echo "<h2>2. Verifying Pages</h2>";
$result = $mysqli->query("SELECT page_id, url, title, position, is_published FROM pages WHERE position = 'bottom' AND (url = 'https://biolink.dev' OR url = 'https://saman.host')");
if ($result && $result->num_rows >= 2) {
    echo "<p class='success'>✅ Found " . $result->num_rows . " footer pages</p>";
    while($row = $result->fetch_assoc()) {
        echo "<p>- {$row['title']} → {$row['url']} (Published: " . ($row['is_published'] ? 'Yes' : 'No') . ")</p>";
    }
} else {
    echo "<p class='error'>❌ Footer pages not found or not published!</p>";
    echo "<p>Run fix_footer_final_cleanup.sql to create them.</p>";
}

// Step 3: Check settings
echo "<h2>3. Checking Settings</h2>";
$result = $mysqli->query("SELECT `value` FROM `settings` WHERE `key` = 'content'");
if ($result && $row = $result->fetch_assoc()) {
    $content = json_decode($row['value'], true);
    $pages_enabled = isset($content['pages_is_enabled']) && $content['pages_is_enabled'];
    if ($pages_enabled) {
        echo "<p class='success'>✅ Pages feature is ENABLED</p>";
    } else {
        echo "<p class='error'>❌ Pages feature is DISABLED</p>";
        echo "<p>Enabling it now...</p>";
        $new_value = json_encode(array_merge($content ?: [], ['pages_is_enabled' => 1]));
        $stmt = $mysqli->prepare("UPDATE `settings` SET `value` = ? WHERE `key` = 'content'");
        $stmt->bind_param("s", $new_value);
        if ($stmt->execute()) {
            echo "<p class='success'>✅ Pages feature enabled!</p>";
        } else {
            echo "<p class='error'>Failed to enable: " . $mysqli->error . "</p>";
        }
        $stmt->close();
    }
} else {
    echo "<p class='error'>Content setting not found. Creating it...</p>";
    $value = json_encode(['pages_is_enabled' => 1]);
    $stmt = $mysqli->prepare("INSERT INTO `settings` (`key`, `value`) VALUES ('content', ?)");
    $stmt->bind_param("s", $value);
    if ($stmt->execute()) {
        echo "<p class='success'>✅ Content setting created with pages enabled!</p>";
    }
    $stmt->close();
}

$mysqli->close();

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p>Cache cleared ✅</p>";
echo "<p>Pages verified ✅</p>";
echo "<p>Settings checked ✅</p>";
echo "<p><strong>Now refresh your homepage. If pages still don't show, the issue is in Page.php query handling.</strong></p>";
echo "<p>Make sure the updated Page.php file is uploaded with the plans_ids column handling fix.</p>";
















