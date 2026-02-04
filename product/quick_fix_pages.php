<?php
/**
 * Quick fix - directly test and fix pages
 */

const DEBUG = 0;
const MYSQL_DEBUG = 0;
const LOGGING = 1;
const CACHE = 1;
const ALTUMCODE = 66;

$product_path = __DIR__;
require_once realpath($product_path) . '/app/init.php';

echo "<h1>Quick Pages Fix</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;}</style>";

// Step 1: Clear all cache
echo "<h2>1. Clearing Cache</h2>";
try {
    $cache = cache();
    $cache->deleteItem('pages_all');
    if (method_exists($cache, 'deleteItemsByTag')) {
        $cache->deleteItemsByTag('pages');
    }
    // Also delete cache files directly
    $cache_path = realpath($product_path) . '/uploads/cache';
    if (is_dir($cache_path)) {
        $files = glob($cache_path . '/*');
        foreach($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
    echo "<p class='success'>✅ Cache cleared</p>";
} catch(\Exception $e) {
    echo "<p class='error'>⚠️ " . $e->getMessage() . "</p>";
}

// Step 2: Test Page model
echo "<h2>2. Testing Page Model</h2>";
try {
    $pages = (new \Altum\Models\Page())->get_pages('bottom');
    echo "<p>Page model returned: <strong>" . count($pages) . " pages</strong></p>";
    
    if (count($pages) > 0) {
        echo "<p class='success'>✅ Pages are loading correctly!</p>";
        echo "<ul>";
        foreach($pages as $page) {
            echo "<li><strong>{$page->title}</strong> → {$page->url}</li>";
        }
        echo "</ul>";
        echo "<p><strong>If you see pages here but not on the homepage, the issue is in the Controller or template.</strong></p>";
    } else {
        echo "<p class='error'>❌ No pages returned. Checking why...</p>";
        
        // Check database directly
        $result = database()->query("SELECT page_id, url, title, position, is_published, language FROM pages WHERE position = 'bottom'");
        if ($result) {
            echo "<p>Database has " . $result->num_rows . " bottom pages:</p>";
            echo "<ul>";
            while($row = $result->fetch_object()) {
                $lang = $row->language ? $row->language : 'NULL';
                echo "<li>ID: {$row->page_id}, Title: <strong>{$row->title}</strong>, URL: {$row->url}, Published: " . ($row->is_published ? 'Yes' : 'No') . ", Language: $lang</li>";
            }
            echo "</ul>";
            
            // Check current language
            echo "<p>Current site language: <strong>" . \Altum\Language::$name . "</strong></p>";
            echo "<p>If pages have a language set and it doesn't match, they'll be filtered out.</p>";
        }
    }
} catch(\Exception $e) {
    echo "<p class='error'>❌ ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Step 3: Check if pages feature is enabled
echo "<h2>3. Settings Check</h2>";
$pages_enabled = isset(settings()->content->pages_is_enabled) && settings()->content->pages_is_enabled;
echo "<p>Pages feature enabled: <strong>" . ($pages_enabled ? "YES ✅" : "NO ❌") . "</strong></p>";

if (!$pages_enabled) {
    echo "<p class='error'><strong>⚠️ Pages feature is DISABLED! This is why footer pages aren't showing.</strong></p>";
    echo "<p>Run this SQL to enable it:</p>";
    echo "<pre>UPDATE `settings` SET `value` = JSON_SET(COALESCE(`value`, '{}'), '$.pages_is_enabled', 1) WHERE `key` = 'content';</pre>";
}

echo "<hr>";
echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>If pages feature is disabled → Run SQL to enable it</li>";
echo "<li>If pages have wrong language → Update pages to set language = NULL</li>";
echo "<li>If Page model returns pages → Check Controller.php to see if pages are passed to footer</li>";
echo "<li>Clear cache again after making changes</li>";
echo "</ol>";



















