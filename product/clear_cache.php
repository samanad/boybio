<?php
/**
 * Cache Clearing Script
 * Run this file after running fix_database_errors.sql to clear the settings cache
 * 
 * Usage: 
 * 1. Upload this file to your website root directory (same level as product/)
 * 2. Access it via browser: https://yourdomain.com/clear_cache.php
 * 3. Delete this file after use for security
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define required constants (same as index.php)
const DEBUG = 0;
const MYSQL_DEBUG = 0;
const LOGGING = 1;
const CACHE = 1;
const ALTUMCODE = 66;

// Determine the product path
// If this file is in root, product is in a subdirectory
// If this file is in product folder, use current directory
$product_path = __DIR__ . '/product';
if (!is_dir($product_path)) {
    $product_path = __DIR__;
}

// Include the application initialization
require_once realpath($product_path) . '/app/init.php';

// Clear the settings cache
try {
    // Initialize cache if needed
    if (class_exists('\Altum\Cache')) {
        \Altum\Cache::initialize();
    }
    
    // Try to use the cache helper function
    if (function_exists('cache')) {
        $cache = cache();
        if ($cache && method_exists($cache, 'deleteItem')) {
            $cache->deleteItem('settings');
            echo "✅ Settings cache cleared successfully!<br>";
        }
        
        // Also clear pages cache (multiple methods to ensure it's cleared)
        if ($cache && method_exists($cache, 'deleteItemsByTag')) {
            $cache->deleteItemsByTag('pages');
            echo "✅ Pages cache cleared (by tag)!<br>";
        }
        
        // Clear pages_all cache directly
        if ($cache && method_exists($cache, 'deleteItem')) {
            $cache->deleteItem('pages_all');
            echo "✅ Pages cache cleared (pages_all key)!<br>";
        }
        
        echo "You can now refresh your website and the changes should be visible.<br>";
    } else {
        throw new Exception('Cache function not available');
    }
} catch (Exception $e) {
    // Fallback: Delete cache files directly
    echo "⚠️ Cache helper not available, trying direct file deletion...<br>";
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
        echo "✅ Deleted $deleted cache files successfully!<br>";
        echo "You can now refresh your website and the errors should be resolved.<br>";
    } else {
        echo "❌ Cache directory not found at: $cache_path<br>";
        echo "Error: " . $e->getMessage() . "<br>";
        echo "<br>You may need to manually clear your cache or restart PHP-FPM.<br>";
        echo "Try: <code>rm -rf " . $product_path . "/uploads/cache/*</code>";
    }
}

echo "<br><strong>IMPORTANT:</strong> Delete this file (clear_cache.php) after use for security reasons.";

