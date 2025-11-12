<?php
/**
 * Quick diagnostic to check why footer pages aren't showing
 */

const DEBUG = 0;
const MYSQL_DEBUG = 0;
const LOGGING = 1;
const CACHE = 1;
const ALTUMCODE = 66;

$product_path = __DIR__ . '/product';
if (!is_dir($product_path)) {
    $product_path = __DIR__;
}

require_once realpath($product_path) . '/app/init.php';

echo "<h1>Footer Pages Diagnostic</h1><pre>";

// 1. Check if pages feature is enabled
echo "1. Pages feature enabled: ";
try {
    $pages_enabled = isset(settings()->content->pages_is_enabled) && settings()->content->pages_is_enabled;
    echo ($pages_enabled ? "YES" : "NO") . "\n";
    if (!$pages_enabled) {
        echo "   ⚠️ Pages feature is DISABLED - this is why footer pages aren't showing!\n";
    }
} catch(\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// 2. Check database directly
echo "\n2. Database check:\n";
try {
    // Try with order column first
    $result = @database()->query("SELECT page_id, url, title, position, is_published, `order` FROM pages WHERE position = 'bottom'");
    if (!$result) {
        // Try without order column
        $result = @database()->query("SELECT page_id, url, title, position, is_published FROM pages WHERE position = 'bottom'");
    }
    
    if ($result && $result !== false) {
        echo "   Found " . $result->num_rows . " pages with position='bottom'\n";
        while($row = $result->fetch_object()) {
            $order = isset($row->order) ? $row->order : 'N/A';
            echo "   - ID: {$row->page_id}, Title: {$row->title}, URL: {$row->url}, Published: " . (isset($row->is_published) && $row->is_published ? 'Yes' : 'No') . ", Order: {$order}\n";
        }
    } else {
        $error = database()->mysqli()->error ?? 'Unknown error';
        echo "   ERROR: " . $error . "\n";
    }
} catch(\Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

// 3. Check what Page model returns
echo "\n3. Page model get_pages('bottom') result:\n";
try {
    $pages = (new \Altum\Models\Page())->get_pages('bottom');
    echo "   Returned " . count($pages) . " pages\n";
    foreach($pages as $page) {
        echo "   - Title: {$page->title}, URL: {$page->url}\n";
    }
} catch(\Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

// 4. Check cache
echo "\n4. Cache check:\n";
try {
    $cache = cache();
    if ($cache) {
        $cache_item = $cache->getItem('pages_all');
        $cached_data = $cache_item->get();
        if ($cached_data && is_array($cached_data)) {
            echo "   Cache has " . count($cached_data) . " pages\n";
            $bottom_count = 0;
            foreach($cached_data as $page) {
                if(isset($page->position) && $page->position == 'bottom') {
                    $bottom_count++;
                    $title = isset($page->title) ? $page->title : 'N/A';
                    $url = isset($page->url) ? $page->url : 'N/A';
                    echo "   - Title: {$title}, URL: {$url}\n";
                }
            }
            echo "   Bottom pages in cache: $bottom_count\n";
        } else {
            echo "   Cache is empty\n";
        }
    } else {
        echo "   Cache not available\n";
    }
} catch(\Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

// 5. Clear cache and retry
echo "\n5. Clearing cache and retrying...\n";
try {
    $cache = cache();
    if ($cache) {
        if (method_exists($cache, 'deleteItem')) {
            $cache->deleteItem('pages_all');
        }
        if (method_exists($cache, 'deleteItemsByTag')) {
            $cache->deleteItemsByTag('pages');
        }
        echo "   Cache cleared\n";
    } else {
        echo "   Cache not available\n";
    }
} catch(\Exception $e) {
    echo "   ERROR clearing cache: " . $e->getMessage() . "\n";
}

// 6. Try again after cache clear
echo "\n6. After cache clear - Page model result:\n";
try {
    $pages = (new \Altum\Models\Page())->get_pages('bottom');
    echo "   Returned " . count($pages) . " pages\n";
    foreach($pages as $page) {
        echo "   - Title: {$page->title}, URL: {$page->url}\n";
    }
} catch(\Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

echo "\n</pre>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ul>";
echo "<li>If pages feature is disabled, enable it in Admin → Settings → Content</li>";
echo "<li>If no pages in database, run fix_footer_links_complete.sql</li>";
echo "<li>If pages exist but aren't showing, check if they're published (is_published = 1)</li>";
echo "</ul>";

