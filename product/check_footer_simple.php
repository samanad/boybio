<?php
/**
 * Simple diagnostic - handles all errors gracefully
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

const DEBUG = 0;
const MYSQL_DEBUG = 0;
const LOGGING = 1;
const CACHE = 1;
const ALTUMCODE = 66;

$product_path = __DIR__ . '/product';
if (!is_dir($product_path)) {
    $product_path = __DIR__;
}

try {
    require_once realpath($product_path) . '/app/init.php';
} catch(\Exception $e) {
    die("Error loading app: " . $e->getMessage());
}

echo "<h1>Footer Pages Diagnostic</h1>";
echo "<style>body{font-family:Arial;margin:20px;background:#f5f5f5;} .section{background:white;padding:15px;margin:10px 0;border-radius:5px;} .error{color:red;} .success{color:green;}</style>";

// 1. Check settings
echo "<div class='section'><h2>1. Settings Check</h2>";
try {
    $settings = settings();
    if (isset($settings->content)) {
        $pages_enabled = isset($settings->content->pages_is_enabled) ? $settings->content->pages_is_enabled : false;
        echo "<p>Pages feature: <strong>" . ($pages_enabled ? "<span class='success'>ENABLED</span>" : "<span class='error'>DISABLED</span>") . "</strong></p>";
        if (!$pages_enabled) {
            echo "<p class='error'><strong>⚠️ This is likely why footer pages aren't showing!</strong></p>";
        }
    } else {
        echo "<p class='error'>Content setting not found in database</p>";
    }
} catch(\Exception $e) {
    echo "<p class='error'>Error checking settings: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// 2. Check database
echo "<div class='section'><h2>2. Database Check</h2>";
try {
    $query = "SELECT page_id, url, title, position, is_published FROM pages WHERE position = 'bottom'";
    $result = @database()->query($query);
    
    if ($result && $result !== false) {
        $count = $result->num_rows;
        echo "<p>Found <strong>$count</strong> pages with position='bottom'</p>";
        
        if ($count > 0) {
            echo "<table border='1' cellpadding='5' style='border-collapse:collapse;width:100%;'>";
            echo "<tr><th>ID</th><th>Title</th><th>URL</th><th>Published</th></tr>";
            while($row = $result->fetch_object()) {
                $published = isset($row->is_published) && $row->is_published ? 'Yes' : 'No';
                echo "<tr>";
                echo "<td>{$row->page_id}</td>";
                echo "<td><strong>{$row->title}</strong></td>";
                echo "<td>{$row->url}</td>";
                echo "<td>$published</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='error'>No footer pages found in database!</p>";
        }
    } else {
        $error = database()->mysqli()->error ?? 'Unknown error';
        echo "<p class='error'>Database query failed: " . htmlspecialchars($error) . "</p>";
    }
} catch(\Exception $e) {
    echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// 3. Try to get pages via model
echo "<div class='section'><h2>3. Page Model Check</h2>";
try {
    $pages = (new \Altum\Models\Page())->get_pages('bottom');
    $count = count($pages);
    echo "<p>Page model returned <strong>$count</strong> pages</p>";
    
    if ($count > 0) {
        echo "<ul>";
        foreach($pages as $page) {
            echo "<li><strong>{$page->title}</strong> → {$page->url}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='error'>No pages returned by model</p>";
    }
} catch(\Exception $e) {
    echo "<p class='error'>Error getting pages: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// 4. Recommendations
echo "<div class='section'><h2>4. Recommendations</h2>";
echo "<ol>";
echo "<li><strong>If pages feature is disabled:</strong> Run <code>fix_footer_complete_final.sql</code> to enable it</li>";
echo "<li><strong>If no pages in database:</strong> Run <code>fix_footer_complete_final.sql</code> to create them</li>";
echo "<li><strong>After making changes:</strong> Clear cache at <code>clear_cache.php</code></li>";
echo "</ol>";
echo "</div>";
















