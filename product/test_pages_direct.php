<?php
/**
 * Direct test - bypasses all app initialization
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get database config - config.php is in the product root
$product_path = __DIR__;
if (file_exists($product_path . '/config.php')) {
    require_once $product_path . '/config.php';
} else {
    // Try parent directory
    $product_path = dirname(__DIR__);
    if (file_exists($product_path . '/product/config.php')) {
        require_once $product_path . '/product/config.php';
    } else {
        die("Cannot find config.php file. Please check the path.");
    }
}

echo "<h1>Direct Database Test</h1>";
echo "<style>body{font-family:Arial;margin:20px;} table{border-collapse:collapse;width:100%;} th,td{padding:8px;border:1px solid #ddd;} th{background:#f2f2f2;}</style>";

// Connect directly - use constants from config.php
if (!defined('DATABASE_SERVER')) {
    die("Database constants not defined. Make sure config.php is loaded correctly.");
}

$mysqli = new mysqli(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check settings
echo "<h2>1. Content Setting</h2>";
$result = $mysqli->query("SELECT `key`, `value` FROM `settings` WHERE `key` = 'content'");
if ($result && $row = $result->fetch_assoc()) {
    $content = json_decode($row['value'], true);
    $pages_enabled = isset($content['pages_is_enabled']) && $content['pages_is_enabled'] ? 'YES' : 'NO';
    echo "<p>Pages enabled: <strong>$pages_enabled</strong></p>";
    if ($pages_enabled == 'NO') {
        echo "<p style='color:red;'><strong>⚠️ Pages feature is DISABLED!</strong></p>";
    }
} else {
    echo "<p style='color:red;'>Content setting not found!</p>";
}

// Check pages table
echo "<h2>2. Footer Pages in Database</h2>";
$result = $mysqli->query("SELECT page_id, url, title, position, is_published FROM pages WHERE position = 'bottom'");
if ($result) {
    echo "<p>Found: <strong>" . $result->num_rows . "</strong> pages</p>";
    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Title</th><th>URL</th><th>Published</th></tr>";
        while($row = $result->fetch_assoc()) {
            $published = $row['is_published'] ? 'Yes' : 'No';
            echo "<tr>";
            echo "<td>{$row['page_id']}</td>";
            echo "<td><strong>{$row['title']}</strong></td>";
            echo "<td>{$row['url']}</td>";
            echo "<td>$published</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:red;'>No footer pages found! Run fix_footer_complete_final.sql</p>";
    }
} else {
    echo "<p style='color:red;'>Error: " . $mysqli->error . "</p>";
}

// Test the query that Page model uses
echo "<h2>3. Testing Page Model Query</h2>";
$queries = [
    "SELECT `url`, `title`, `type`, `open_in_new_tab`, `language`, `icon`, `position`, `plans_ids` FROM `pages` WHERE `is_published` = 1 ORDER BY `pages`.`order` ASC",
    "SELECT `url`, `title`, `type`, `open_in_new_tab`, `language`, `icon`, `position`, `plans_ids` FROM `pages` WHERE `is_published` = 1",
    "SELECT `url`, `title`, `type`, `position` FROM `pages`"
];

foreach($queries as $i => $query) {
    echo "<p><strong>Query " . ($i+1) . ":</strong></p>";
    $result = @$mysqli->query($query);
    if ($result) {
        echo "<p style='color:green;'>✅ Success - Found " . $result->num_rows . " rows</p>";
        if ($result->num_rows > 0) {
            $bottom_count = 0;
            while($row = $result->fetch_assoc()) {
                if(isset($row['position']) && $row['position'] == 'bottom') {
                    $bottom_count++;
                }
            }
            echo "<p>Bottom pages: $bottom_count</p>";
        }
        break; // Stop at first successful query
    } else {
        echo "<p style='color:red;'>❌ Failed: " . $mysqli->error . "</p>";
    }
}

$mysqli->close();

echo "<hr>";
echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>If pages feature is disabled, run: <code>fix_footer_complete_final.sql</code></li>";
echo "<li>If no pages found, run: <code>fix_footer_complete_final.sql</code></li>";
echo "<li>Make sure you uploaded the fixed <code>Page.php</code> file to the server</li>";
echo "<li>Clear cache: <code>clear_cache.php</code></li>";
echo "</ol>";

