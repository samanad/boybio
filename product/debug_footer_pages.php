<?php
/**
 * Debug Footer Pages
 * This script will show exactly what's in the database and what the application is loading
 */

// Define required constants
const DEBUG = 0;
const MYSQL_DEBUG = 0;
const LOGGING = 1;
const CACHE = 1;
const ALTUMCODE = 66;

// Determine the product path
$product_path = __DIR__ . '/product';
if (!is_dir($product_path)) {
    $product_path = __DIR__;
}

// Include the application initialization
require_once realpath($product_path) . '/app/init.php';

echo "<h1>Footer Pages Debug</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .section { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background-color: #f2f2f2; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    pre { background: #f4f4f4; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>";

try {
    // 1. Show what's in the database
    echo "<div class='section'>";
    echo "<h2>1. Database Content (Raw)</h2>";
    
    // First check if order column exists
    $check_order = database()->query("SHOW COLUMNS FROM pages LIKE 'order'");
    if ($check_order && $check_order->num_rows > 0) {
        $result = database()->query("SELECT page_id, url, title, position, is_published, `order` FROM pages WHERE position = 'bottom' ORDER BY `order` ASC");
    } else {
        $result = database()->query("SELECT page_id, url, title, position, is_published FROM pages WHERE position = 'bottom'");
        echo "<p class='error'>⚠️ Warning: 'order' column not found in pages table. Run fix_pages_table.sql to fix this.</p>";
    }
    
    if ($result === false) {
        echo "<p class='error'>❌ Database query failed: " . database()->mysqli()->error . "</p>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>URL</th><th>Title</th><th>Position</th><th>Published</th>";
        if ($check_order && $check_order->num_rows > 0) {
            echo "<th>Order</th>";
        }
        echo "</tr>";
        $db_pages = [];
        while($row = $result->fetch_object()) {
            $db_pages[] = $row;
            echo "<tr>";
            echo "<td>{$row->page_id}</td>";
            echo "<td>{$row->url}</td>";
            echo "<td><strong>{$row->title}</strong></td>";
            echo "<td>{$row->position}</td>";
            echo "<td>" . (isset($row->is_published) && $row->is_published ? 'Yes' : 'No') . "</td>";
            if ($check_order && $check_order->num_rows > 0 && isset($row->order)) {
                echo "<td>{$row->order}</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";
    
    // 2. Clear ALL cache
    echo "<div class='section'>";
    echo "<h2>2. Clearing Cache</h2>";
    
    // Initialize cache
    if (class_exists('\Altum\Cache')) {
        \Altum\Cache::initialize();
    }
    
    $cache_cleared = false;
    if (function_exists('cache')) {
        $cache = cache();
        
        // Clear settings cache
        if ($cache && method_exists($cache, 'deleteItem')) {
            $cache->deleteItem('settings');
            echo "<p class='success'>✅ Settings cache cleared</p>";
            $cache_cleared = true;
        }
        
        // Clear pages cache by tag
        if ($cache && method_exists($cache, 'deleteItemsByTag')) {
            $cache->deleteItemsByTag('pages');
            echo "<p class='success'>✅ Pages cache cleared (by tag)</p>";
            $cache_cleared = true;
        }
        
        // Try to clear pages_all cache directly
        if ($cache && method_exists($cache, 'deleteItem')) {
            $cache->deleteItem('pages_all');
            echo "<p class='success'>✅ Pages cache cleared (pages_all key)</p>";
            $cache_cleared = true;
        }
    }
    
    // Also delete cache files directly
    $cache_path = realpath($product_path) . '/uploads/cache';
    if (is_dir($cache_path)) {
        $files = glob($cache_path . '/*');
        $deleted = 0;
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
                $deleted++;
            }
        }
        if ($deleted > 0) {
            echo "<p class='success'>✅ Deleted $deleted cache files from disk</p>";
            $cache_cleared = true;
        }
    }
    
    if (!$cache_cleared) {
        echo "<p class='error'>⚠️ Could not clear cache via helper, but files were deleted</p>";
    }
    echo "</div>";
    
    // 3. Check what the application loads NOW (after cache clear)
    echo "<div class='section'>";
    echo "<h2>3. Pages Loaded by Application (After Cache Clear)</h2>";
    
    // Force reload by getting pages again
    $bottom_pages = (new \Altum\Models\Page())->get_pages('bottom');
    
    if (count($bottom_pages) > 0) {
        echo "<p class='success'>✅ Found " . count($bottom_pages) . " footer pages:</p>";
        echo "<table>";
        echo "<tr><th>Title</th><th>URL</th><th>Target</th><th>Type</th></tr>";
        foreach ($bottom_pages as $page) {
            echo "<tr>";
            echo "<td><strong>{$page->title}</strong></td>";
            echo "<td><a href='{$page->url}' target='_blank'>{$page->url}</a></td>";
            echo "<td>{$page->target}</td>";
            echo "<td>{$page->type}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check if the updated pages are there
        $found_biolink = false;
        $found_saman = false;
        foreach ($bottom_pages as $page) {
            if (strpos($page->url, 'biolink.dev') !== false) {
                $found_biolink = true;
            }
            if (strpos($page->url, 'saman.host') !== false) {
                $found_saman = true;
            }
        }
        
        if ($found_biolink && $found_saman) {
            echo "<p class='success'>✅ Both updated pages are loaded correctly!</p>";
        } else {
            echo "<p class='error'>❌ Updated pages not found in loaded pages. Cache might still be active.</p>";
        }
    } else {
        echo "<p class='error'>❌ No footer pages found.</p>";
        echo "<p class='info'>Check if pages_is_enabled is true: ";
        echo (settings()->content->pages_is_enabled ?? 'NOT SET') . "</p>";
    }
    echo "</div>";
    
    // 4. Check cache status
    echo "<div class='section'>";
    echo "<h2>4. Cache Status</h2>";
    if (function_exists('cache')) {
        $cache = cache();
        $cache_item = $cache->getItem('pages_all');
        if ($cache_item->get()) {
            echo "<p class='error'>⚠️ Cache still contains data (cache not cleared properly)</p>";
            echo "<pre>" . print_r($cache_item->get(), true) . "</pre>";
        } else {
            echo "<p class='success'>✅ Cache is empty (cleared successfully)</p>";
        }
    }
    echo "</div>";
    
    // 5. Verify database updates
    echo "<div class='section'>";
    echo "<h2>5. Verify Database Updates</h2>";
    $check_result = database()->query("SELECT page_id, url, title FROM pages WHERE page_id IN (6, 7)");
    echo "<table>";
    echo "<tr><th>ID</th><th>URL</th><th>Title</th></tr>";
    while($row = $check_result->fetch_object()) {
        echo "<tr>";
        echo "<td>{$row->page_id}</td>";
        echo "<td>{$row->url}</td>";
        echo "<td><strong>{$row->title}</strong></td>";
        echo "</tr>";
        
        // Check if it matches expected values
        if ($row->page_id == 6) {
            if ($row->url == 'https://saman.host' && $row->title == 'from saman') {
                echo "<tr><td colspan='3' class='success'>✅ Page 6 is correctly updated</td></tr>";
            } else {
                echo "<tr><td colspan='3' class='error'>❌ Page 6 is NOT updated correctly. Expected: https://saman.host / from saman</td></tr>";
            }
        }
        if ($row->page_id == 7) {
            if ($row->url == 'https://biolink.dev' && $row->title == 'built with love') {
                echo "<tr><td colspan='3' class='success'>✅ Page 7 is correctly updated</td></tr>";
            } else {
                echo "<tr><td colspan='3' class='error'>❌ Page 7 is NOT updated correctly. Expected: https://biolink.dev / built with love</td></tr>";
            }
        }
    }
    echo "</table>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='section'>";
    echo "<h2 class='error'>Error</h2>";
    echo "<p class='error'>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "<div class='section'>";
echo "<p><strong>⚠️ IMPORTANT:</strong> Delete this file after use for security.</p>";
echo "</div>";

