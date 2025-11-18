<?php
/*
 * Migration Script: Upload Static Assets, Plugins, and Translations to Offload Storage
 * 
 * This script uploads existing files to offload storage:
 * - product/uploads/main/ (static assets like logos, favicons)
 * - product/uploads/pwa/ (PWA manifest and icons)
 * - product/uploads/favicons/ (favicon files)
 * - product/plugins/ (entire plugins folder)
 * - product/app/languages/ (translation files)
 * 
 * Usage:
 * 1. Configure offload settings in Admin Panel first
 * 2. Run this script: php product/migrate-to-offload.php
 * 3. After successful upload, you can optionally delete local files
 */

/* Only allow CLI execution */
if(php_sapi_name() !== 'cli') {
    die("This script can only be run from command line.\n");
}

define('ALTUMCODE', 66);

/* Define constants needed before init.php */
if(!defined('DEBUG')) {
    define('DEBUG', false);
}
if(!defined('LOGGING')) {
    define('LOGGING', false);
}

/* Suppress web output and enable error reporting for CLI */
ini_set('display_errors', 1);
error_reporting(E_ALL);

/* Try to load init.php with error handling */
try {
    require_once __DIR__ . '/app/init.php';
} catch (\Throwable $e) {
    die("ERROR: Failed to load application: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n");
}

/* Check if offload is configured */
if(!\Altum\Plugin::is_active('offload') || !settings()->offload->uploads_url) {
    die("ERROR: Offload plugin is not active or not configured. Please configure offload settings in Admin Panel first.\n");
}

echo "=== Migration to Offload Storage ===\n\n";
echo "Offload URL: " . settings()->offload->uploads_url . "\n";
echo "Storage Name: " . settings()->offload->storage_name . "\n";
echo "Region: " . (settings()->offload->region ?: 'us-east-1') . "\n\n";

/* Initialize AWS S3 Client */
try {
    $s3 = new \Aws\S3\S3Client(get_aws_s3_config());
    echo "✓ AWS S3 Client initialized\n\n";
} catch (\Exception $e) {
    die("ERROR: Failed to initialize AWS S3 client: " . $e->getMessage() . "\n");
}

/* Function to upload a file to S3 */
function upload_file_to_s3($s3, $local_path, $s3_key, $content_type = null) {
    global $uploaded_count, $failed_count;
    
    if(!file_exists($local_path)) {
        echo "  ⚠️  File not found: $local_path\n";
        $failed_count++;
        return false;
    }
    
    try {
        if(!$content_type) {
            $content_type = mime_content_type($local_path) ?: 'application/octet-stream';
        }
        
        $result = $s3->putObject([
            'Bucket' => settings()->offload->storage_name,
            'Key' => $s3_key,
            'ContentType' => $content_type,
            'SourceFile' => $local_path,
            'ACL' => 'public-read'
        ]);
        
        echo "  ✓ Uploaded: $s3_key\n";
        $uploaded_count++;
        return true;
    } catch (\Exception $e) {
        echo "  ✗ Failed: $s3_key - " . $e->getMessage() . "\n";
        $failed_count++;
        return false;
    }
}

/* Function to recursively upload directory */
function upload_directory_to_s3($s3, $local_dir, $s3_prefix, $base_path = null) {
    if($base_path === null) {
        $base_path = $local_dir;
    }
    
    if(!is_dir($local_dir)) {
        return;
    }
    
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($local_dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach($files as $file) {
        if($file->isFile()) {
            $local_path = $file->getPathname();
            $relative_path = str_replace($base_path . '/', '', $local_path);
            $s3_key = $s3_prefix . $relative_path;
            
            upload_file_to_s3($s3, $local_path, $s3_key);
        }
    }
}

$uploaded_count = 0;
$failed_count = 0;

/* 1. Upload static assets from uploads/main/ */
echo "1. Uploading static assets (uploads/main/)...\n";
if(is_dir(UPLOADS_PATH . 'main/')) {
    upload_directory_to_s3($s3, UPLOADS_PATH . 'main/', UPLOADS_URL_PATH . 'main/');
} else {
    echo "  ⚠️  Directory not found: " . UPLOADS_PATH . "main/\n";
}
echo "\n";

/* 2. Upload PWA assets */
echo "2. Uploading PWA assets (uploads/pwa/)...\n";
if(is_dir(UPLOADS_PATH . 'pwa/')) {
    upload_directory_to_s3($s3, UPLOADS_PATH . 'pwa/', UPLOADS_URL_PATH . 'pwa/');
} else {
    echo "  ⚠️  Directory not found: " . UPLOADS_PATH . "pwa/\n";
}
echo "\n";

/* 3. Upload favicons */
echo "3. Uploading favicons (uploads/favicons/)...\n";
if(is_dir(UPLOADS_PATH . 'favicons/')) {
    upload_directory_to_s3($s3, UPLOADS_PATH . 'favicons/', UPLOADS_URL_PATH . 'favicons/');
} else {
    echo "  ⚠️  Directory not found: " . UPLOADS_PATH . "favicons/\n";
}
echo "\n";

/* 4. Upload plugins folder */
echo "4. Uploading plugins (plugins/)...\n";
if(is_dir(PLUGINS_PATH)) {
    upload_directory_to_s3($s3, PLUGINS_PATH, 'plugins/');
} else {
    echo "  ⚠️  Directory not found: " . PLUGINS_PATH . "\n";
}
echo "\n";

/* 5. Upload translations */
echo "5. Uploading translations (app/languages/)...\n";
if(is_dir(APP_PATH . 'languages/')) {
    upload_directory_to_s3($s3, APP_PATH . 'languages/', 'app/languages/', APP_PATH . 'languages/');
} else {
    echo "  ⚠️  Directory not found: " . APP_PATH . "languages/\n";
}
echo "\n";

/* Summary */
echo "=== Migration Summary ===\n";
echo "✓ Successfully uploaded: $uploaded_count files\n";
if($failed_count > 0) {
    echo "✗ Failed to upload: $failed_count files\n";
}
echo "\n";

/* Ask if user wants to delete local files */
if($uploaded_count > 0 && $failed_count == 0) {
    echo "Migration completed successfully!\n";
    echo "\n";
    echo "⚠️  WARNING: Before deleting local files, make sure:\n";
    echo "  1. All files uploaded successfully\n";
    echo "  2. You have tested that the site loads files from offload\n";
    echo "  3. You have a backup of the local files\n";
    echo "\n";
    echo "To delete local files after verification, run:\n";
    echo "  php product/migrate-to-offload.php --delete-local\n";
    echo "\n";
}

/* Delete local files if --delete-local flag is set */
if(isset($argv[1]) && $argv[1] === '--delete-local') {
    if($failed_count > 0) {
        die("ERROR: Cannot delete local files because some uploads failed. Please fix errors first.\n");
    }
    
    echo "⚠️  DELETING LOCAL FILES...\n\n";
    
    $deleted = 0;
    
    /* Delete uploads/main/ */
    if(is_dir(UPLOADS_PATH . 'main/')) {
        delete_directory(UPLOADS_PATH . 'main/');
        $deleted++;
        echo "✓ Deleted: " . UPLOADS_PATH . "main/\n";
    }
    
    /* Delete uploads/pwa/ */
    if(is_dir(UPLOADS_PATH . 'pwa/')) {
        delete_directory(UPLOADS_PATH . 'pwa/');
        $deleted++;
        echo "✓ Deleted: " . UPLOADS_PATH . "pwa/\n";
    }
    
    /* Delete uploads/favicons/ */
    if(is_dir(UPLOADS_PATH . 'favicons/')) {
        delete_directory(UPLOADS_PATH . 'favicons/');
        $deleted++;
        echo "✓ Deleted: " . UPLOADS_PATH . "favicons/\n";
    }
    
    /* Delete plugins/ */
    if(is_dir(PLUGINS_PATH)) {
        delete_directory(PLUGINS_PATH);
        $deleted++;
        echo "✓ Deleted: " . PLUGINS_PATH . "\n";
    }
    
    /* Delete app/languages/ */
    if(is_dir(APP_PATH . 'languages/')) {
        delete_directory(APP_PATH . 'languages/');
        $deleted++;
        echo "✓ Deleted: " . APP_PATH . "languages/\n";
    }
    
    echo "\n✓ Deleted $deleted directories\n";
    echo "\n⚠️  IMPORTANT: Make sure your system is configured to load from offload storage!\n";
}

function delete_directory($dir) {
    if(!is_dir($dir)) {
        return;
    }
    
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    
    foreach($files as $file) {
        if($file->isDir()) {
            rmdir($file->getPathname());
        } else {
            unlink($file->getPathname());
        }
    }
    
    rmdir($dir);
}

