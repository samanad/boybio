<?php
/**
 * Verify Pages and Clear Cache
 * This script verifies the pages in database and clears the pages cache
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

echo "<h1>Pages Verification and Cache Clear</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .section { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background-color: #f2f2f2; }
    .success { color: green; }
    .error { color: red; }
</style>";

try {
    // Show current pages in database
    echo "<div class='section'>";
    echo "<h2>Current Footer Pages in Database</h2>";
    $result = database()->query("SELECT page_id, url, title, position, is_published, `order` FROM pages WHERE position = 'bottom' ORDER BY `order` ASC");
    echo "<table>";
    echo "<tr><th>ID</th><th>URL</th><th>Title</th><th>Position</th><th>Published</th><th>Order</th></tr>";
    while($row = $result->fetch_object()) {
        echo "<tr>";
        echo "<td>{$row->page_id}</td>";
        echo "<td>{$row->url}</td>";
        echo "<td>{$row->title}</td>";
        echo "<td>{$row->position}</td>";
        echo "<td>" . ($row->is_published ? 'Yes' : 'No') . "</td>";
        echo "<td>{$row->order}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // Clear pages cache
    echo "<div class='section'>";
    echo "<h2>Clearing Cache</h2>";
    
    // Initialize cache
    if (class_exists('\Altum\Cache')) {
        \Altum\Cache::initialize();
    }
    
    // Clear pages cache
    if (function_exists('cache')) {
        $cache = cache();
        if ($cache && method_exists($cache, 'deleteItemsByTag')) {
            $cache->deleteItemsByTag('pages');
            echo "<p class='success'>✅ Pages cache cleared successfully!</p>";
        } else {
            echo "<p class='error'>⚠️ Cache helper not available, trying direct file deletion...</p>";
        }
    }
    
    // Also try to delete cache files directly
    $cache_path = realpath($product_path) . '/uploads/cache';
    if (is_dir($cache_path)) {
        $files = glob($cache_path . '/*pages*');
        $deleted = 0;
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
                $deleted++;
            }
        }
        if ($deleted > 0) {
            echo "<p class='success'>✅ Deleted $deleted pages cache files</p>";
        }
    }
    
    echo "</div>";
    
    // Verify the pages are loaded correctly
    echo "<div class='section'>";
    echo "<h2>Pages Loaded by Application</h2>";
    $bottom_pages = (new \Altum\Models\Page())->get_pages('bottom');
    if (count($bottom_pages) > 0) {
        echo "<p class='success'>✅ Found " . count($bottom_pages) . " footer pages:</p>";
        echo "<ul>";
        foreach ($bottom_pages as $page) {
            echo "<li><strong>{$page->title}</strong> → <a href='{$page->url}' target='_blank'>{$page->url}</a></li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='error'>❌ No footer pages found. Check if pages_is_enabled is true in content settings.</p>";
    }
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='section'>";
    echo "<h2 class='error'>Error</h2>";
    echo "<p class='error'>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "<div class='section'>";
echo "<p><strong>⚠️ IMPORTANT:</strong> Delete this file after use for security.</p>";
echo "</div>";











