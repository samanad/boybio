<?php
/**
 * Simple check - minimal dependencies
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get config
$product_path = __DIR__;
if (file_exists($product_path . '/config.php')) {
    require_once $product_path . '/config.php';
} else {
    die("Cannot find config.php");
}

echo "<h1>Simple Pages Check</h1>";
echo "<style>body{font-family:Arial;margin:20px;} table{border-collapse:collapse;width:100%;} th,td{padding:8px;border:1px solid #ddd;} th{background:#f2f2f2;} .success{color:green;} .error{color:red;}</style>";

// Connect to database
$mysqli = new mysqli(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check pages in database
echo "<h2>1. Database Pages (position='bottom')</h2>";
$result = $mysqli->query("SELECT page_id, url, title, position, is_published, language, `type` FROM pages WHERE position = 'bottom' ORDER BY `order` ASC");
if ($result) {
    echo "<p>Found: <strong>" . $result->num_rows . "</strong> pages</p>";
    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Title</th><th>URL</th><th>Published</th><th>Language</th><th>Type</th></tr>";
        while($row = $result->fetch_assoc()) {
            $lang = $row['language'] ? $row['language'] : 'NULL';
            $published = $row['is_published'] ? 'Yes' : 'No';
            echo "<tr>";
            echo "<td>{$row['page_id']}</td>";
            echo "<td><strong>{$row['title']}</strong></td>";
            echo "<td>{$row['url']}</td>";
            echo "<td>$published</td>";
            echo "<td>$lang</td>";
            echo "<td>{$row['type']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p class='error'>Error: " . $mysqli->error . "</p>";
}

// Check settings
echo "<h2>2. Content Settings</h2>";
$result = $mysqli->query("SELECT `key`, `value` FROM `settings` WHERE `key` = 'content'");
if ($result && $row = $result->fetch_assoc()) {
    $content = json_decode($row['value'], true);
    $pages_enabled = isset($content['pages_is_enabled']) && $content['pages_is_enabled'];
    echo "<p>Pages enabled: <strong>" . ($pages_enabled ? "<span class='success'>YES</span>" : "<span class='error'>NO</span>") . "</strong></p>";
    
    if (!$pages_enabled) {
        echo "<p class='error'><strong>⚠️ Pages feature is DISABLED!</strong></p>";
        echo "<p>Run this SQL:</p>";
        echo "<pre>UPDATE `settings` SET `value` = JSON_SET(COALESCE(`value`, '{}'), '$.pages_is_enabled', 1) WHERE `key` = 'content';</pre>";
    }
} else {
    echo "<p class='error'>Content setting not found!</p>";
}

// Check cache files
echo "<h2>3. Cache Files</h2>";
$cache_path = $product_path . '/uploads/cache';
if (is_dir($cache_path)) {
    $files = glob($cache_path . '/*');
    echo "<p>Cache directory exists. Found " . count($files) . " cache files.</p>";
    if (count($files) > 0) {
        echo "<p>Cache files:</p><ul>";
        foreach(array_slice($files, 0, 10) as $file) {
            echo "<li>" . basename($file) . " (" . filesize($file) . " bytes)</li>";
        }
        echo "</ul>";
        echo "<p><strong>To clear cache, delete these files or run:</strong></p>";
        echo "<pre>rm -rf " . $cache_path . "/*</pre>";
    }
} else {
    echo "<p>Cache directory not found at: $cache_path</p>";
}

// Test a simple query that should work
echo "<h2>4. Test Simple Query (without plans_ids)</h2>";
$queries = [
    "SELECT `url`, `title`, `type`, `position` FROM `pages` WHERE `is_published` = 1 AND `position` = 'bottom'",
    "SELECT `url`, `title`, `type`, `position`, `open_in_new_tab` FROM `pages` WHERE `is_published` = 1 AND `position` = 'bottom'",
];

foreach($queries as $i => $query) {
    echo "<p><strong>Query " . ($i+1) . ":</strong></p>";
    $result = @$mysqli->query($query);
    if ($result) {
        echo "<p class='success'>✅ Success - Found " . $result->num_rows . " rows</p>";
        if ($result->num_rows > 0) {
            echo "<ul>";
            while($row = $result->fetch_assoc()) {
                echo "<li><strong>{$row['title']}</strong> → {$row['url']}</li>";
            }
            echo "</ul>";
        }
        break;
    } else {
        echo "<p class='error'>❌ Failed: " . $mysqli->error . "</p>";
    }
}

$mysqli->close();

echo "<hr>";
echo "<h2>Recommendations:</h2>";
echo "<ol>";
echo "<li>If pages exist in database but don't show → Check if Page.php handles missing columns</li>";
echo "<li>If pages feature is disabled → Run SQL to enable it</li>";
echo "<li>If cache has old data → Delete cache files</li>";
echo "<li>Check if footer template is using <code>\$data->pages</code> correctly</li>";
echo "</ol>";



















