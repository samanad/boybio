<?php
/**
 * Debug what Page model actually returns
 */

const DEBUG = 0;
const MYSQL_DEBUG = 0;
const LOGGING = 1;
const CACHE = 1;
const ALTUMCODE = 66;

$product_path = __DIR__;
require_once realpath($product_path) . '/app/init.php';

echo "<h1>Page Model Debug</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .section{background:#f5f5f5;padding:15px;margin:10px 0;border-radius:5px;} pre{background:#fff;padding:10px;border:1px solid #ddd;}</style>";

// Clear cache first
echo "<div class='section'><h2>1. Clearing Cache</h2>";
try {
    $cache = cache();
    $cache->deleteItem('pages_all');
    if (method_exists($cache, 'deleteItemsByTag')) {
        $cache->deleteItemsByTag('pages');
    }
    echo "<p>✅ Cache cleared</p>";
} catch(\Exception $e) {
    echo "<p>⚠️ Cache clear error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Test Page model
echo "<div class='section'><h2>2. Page Model get_pages('bottom') Result</h2>";
try {
    $pages = (new \Altum\Models\Page())->get_pages('bottom');
    echo "<p><strong>Returned " . count($pages) . " pages</strong></p>";
    
    if (count($pages) > 0) {
        echo "<table border='1' cellpadding='8' style='border-collapse:collapse;width:100%;'>";
        echo "<tr><th>Title</th><th>URL</th><th>Type</th><th>Target</th><th>Position</th><th>Language</th><th>Plans IDs</th></tr>";
        foreach($pages as $page) {
            $title = isset($page->title) ? $page->title : 'N/A';
            $url = isset($page->url) ? $page->url : 'N/A';
            $type = isset($page->type) ? $page->type : 'N/A';
            $target = isset($page->target) ? $page->target : 'N/A';
            $position = isset($page->position) ? $page->position : 'N/A';
            $language = isset($page->language) ? ($page->language ?: 'NULL') : 'N/A';
            $plans_ids = isset($page->plans_ids) ? json_encode($page->plans_ids) : 'N/A';
            
            echo "<tr>";
            echo "<td><strong>$title</strong></td>";
            echo "<td>$url</td>";
            echo "<td>$type</td>";
            echo "<td>$target</td>";
            echo "<td>$position</td>";
            echo "<td>$language</td>";
            echo "<td>$plans_ids</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:red;'><strong>❌ No pages returned!</strong></p>";
        echo "<p>This means pages are being filtered out. Possible reasons:</p>";
        echo "<ul>";
        echo "<li>Language mismatch</li>";
        echo "<li>Plan restrictions</li>";
        echo "<li>Position mismatch</li>";
        echo "</ul>";
    }
} catch(\Exception $e) {
    echo "<p style='color:red;'><strong>ERROR:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
echo "</div>";

// Check what's in cache after
echo "<div class='section'><h2>3. Cache Contents (After Query)</h2>";
try {
    $cache = cache();
    $cache_item = $cache->getItem('pages_all');
    $cached_data = $cache_item->get();
    
    if ($cached_data && is_array($cached_data)) {
        echo "<p>Cache has " . count($cached_data) . " total pages</p>";
        $bottom_count = 0;
        foreach($cached_data as $page) {
            if(isset($page->position) && $page->position == 'bottom') {
                $bottom_count++;
            }
        }
        echo "<p>Bottom pages in cache: <strong>$bottom_count</strong></p>";
        
        if ($bottom_count > 0) {
            echo "<ul>";
            foreach($cached_data as $page) {
                if(isset($page->position) && $page->position == 'bottom') {
                    $title = isset($page->title) ? $page->title : 'N/A';
                    $url = isset($page->url) ? $page->url : 'N/A';
                    $lang = isset($page->language) ? ($page->language ?: 'NULL') : 'N/A';
                    echo "<li><strong>$title</strong> → $url (lang: $lang)</li>";
                }
            }
            echo "</ul>";
        }
    } else {
        echo "<p>Cache is empty</p>";
    }
} catch(\Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Check current language
echo "<div class='section'><h2>4. Current Language Settings</h2>";
echo "<p>Current language: <strong>" . \Altum\Language::$name . "</strong></p>";
echo "<p>Active languages:</p>";
echo "<pre>" . print_r(array_keys(\Altum\Language::$active_languages), true) . "</pre>";
echo "</div>";











