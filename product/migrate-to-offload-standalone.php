<?php
/*
 * Standalone Migration Script: Upload Static Assets, Plugins, and Translations to Offload Storage
 * 
 * This script uploads existing files to offload storage WITHOUT requiring full app initialization.
 * 
 * Usage:
 * 1. Configure offload settings in Admin Panel first
 * 2. Run this script: php product/migrate-to-offload-standalone.php
 * 3. After successful upload, you can optionally delete local files
 */

/* Only allow CLI execution */
if(php_sapi_name() !== 'cli') {
    die("This script can only be run from command line.\n");
}

/* Enable error reporting */
ini_set('display_errors', 1);
error_reporting(E_ALL);

/* Define paths */
define('ROOT_PATH', realpath(__DIR__) . '/');
define('UPLOADS_PATH', ROOT_PATH . 'uploads/');
define('PLUGINS_PATH', ROOT_PATH . 'plugins/');
define('APP_PATH', ROOT_PATH . 'app/');

/* Load config */
if(!file_exists(ROOT_PATH . 'config.php')) {
    die("ERROR: config.php not found!\n");
}

require_once ROOT_PATH . 'config.php';

/* Load vendor autoloader for AWS SDK */
if(file_exists(ROOT_PATH . 'vendor/autoload.php')) {
    require_once ROOT_PATH . 'vendor/autoload.php';
} else {
    die("ERROR: vendor/autoload.php not found. Please run: composer install\n");
}

/* Check if AWS SDK is available */
if(!class_exists('Aws\S3\S3Client')) {
    die("ERROR: AWS SDK not found. Please install via Composer: composer require aws/aws-sdk-php\n");
}

/* Connect to database using PDO (more universally available) */
try {
    $dsn = "mysql:host=" . DATABASE_SERVER . ";dbname=" . DATABASE_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DATABASE_USERNAME, DATABASE_PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("ERROR: Database connection failed: " . $e->getMessage() . "\n");
}

/* Get offload settings from database */
$settings_query = "SELECT `value` FROM `settings` WHERE `key` = 'offload'";
$stmt = $pdo->query($settings_query);
$row = $stmt->fetch();

if(!$row) {
    die("ERROR: Offload settings not found in database. Please configure offload in Admin Panel first.\n");
}

$offload_settings = json_decode($row['value']);

if(!$offload_settings || !isset($offload_settings->uploads_url) || empty($offload_settings->uploads_url)) {
    die("ERROR: Offload is not configured. Please configure offload settings in Admin Panel first.\n");
}

/* Check if offload plugin is active */
$plugins_query = "SELECT `value` FROM `settings` WHERE `key` = 'plugins'";
$plugins_stmt = $pdo->query($plugins_query);
$plugins_row = $plugins_stmt->fetch();
$plugins_enabled = false;

if($plugins_row) {
    $plugins_data = json_decode($plugins_row['value']);
    if(isset($plugins_data->offload) && $plugins_data->offload == 1) {
        $plugins_enabled = true;
    }
}

if(!$plugins_enabled) {
    die("ERROR: Offload plugin is not active. Please enable it in Admin Panel first.\n");
}

echo "=== Migration to Offload Storage ===\n\n";
echo "Offload URL: " . $offload_settings->uploads_url . "\n";
echo "Storage Name: " . $offload_settings->storage_name . "\n";
echo "Region: " . ($offload_settings->region ?: 'us-east-1') . "\n\n";

/* Initialize AWS S3 Client */
try {
    $aws_config = [
        'region' => $offload_settings->region ?: 'us-east-1',
        'version' => 'latest',
        'credentials' => [
            'key' => $offload_settings->access_key,
            'secret' => $offload_settings->secret_access_key,
        ],
    ];
    
    if(isset($offload_settings->provider) && $offload_settings->provider != 'aws-s3') {
        $aws_config['endpoint'] = $offload_settings->endpoint_url;
        $aws_config['bucket_endpoint'] = $offload_settings->bucket_endpoint ?? false;
        $aws_config['use_path_style_endpoint'] = $offload_settings->bucket_endpoint ?? false;
    }
    
    $s3 = new \Aws\S3\S3Client($aws_config);
    echo "✓ AWS S3 Client initialized\n\n";
} catch (\Exception $e) {
    die("ERROR: Failed to initialize AWS S3 client: " . $e->getMessage() . "\n");
}

$uploaded_count = 0;
$failed_count = 0;

/* Function to upload a file to S3 */
function upload_file_to_s3($s3, $local_path, $s3_key, $storage_name, $content_type = null) {
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
            'Bucket' => $storage_name,
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
function upload_directory_to_s3($s3, $local_dir, $s3_prefix, $storage_name, $base_path = null) {
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
            $relative_path = str_replace('\\', '/', $relative_path); // Fix Windows paths
            $s3_key = $s3_prefix . $relative_path;
            
            upload_file_to_s3($s3, $local_path, $s3_key, $storage_name);
        }
    }
}

/* 1. Upload static assets from uploads/main/ */
echo "1. Uploading static assets (uploads/main/)...\n";
if(is_dir(UPLOADS_PATH . 'main/')) {
    upload_directory_to_s3($s3, UPLOADS_PATH . 'main/', 'uploads/main/', $offload_settings->storage_name);
} else {
    echo "  ⚠️  Directory not found: " . UPLOADS_PATH . "main/\n";
}
echo "\n";

/* 2. Upload PWA assets */
echo "2. Uploading PWA assets (uploads/pwa/)...\n";
if(is_dir(UPLOADS_PATH . 'pwa/')) {
    upload_directory_to_s3($s3, UPLOADS_PATH . 'pwa/', 'uploads/pwa/', $offload_settings->storage_name);
} else {
    echo "  ⚠️  Directory not found: " . UPLOADS_PATH . "pwa/\n";
}
echo "\n";

/* 3. Upload favicons */
echo "3. Uploading favicons (uploads/favicons/)...\n";
if(is_dir(UPLOADS_PATH . 'favicons/')) {
    upload_directory_to_s3($s3, UPLOADS_PATH . 'favicons/', 'uploads/favicons/', $offload_settings->storage_name);
} else {
    echo "  ⚠️  Directory not found: " . UPLOADS_PATH . "favicons/\n";
}
echo "\n";

/* 4. Upload plugins folder */
echo "4. Uploading plugins (plugins/)...\n";
if(is_dir(PLUGINS_PATH)) {
    upload_directory_to_s3($s3, PLUGINS_PATH, 'plugins/', $offload_settings->storage_name);
} else {
    echo "  ⚠️  Directory not found: " . PLUGINS_PATH . "\n";
}
echo "\n";

/* 5. Upload translations */
echo "5. Uploading translations (app/languages/)...\n";
if(is_dir(APP_PATH . 'languages/')) {
    upload_directory_to_s3($s3, APP_PATH . 'languages/', 'app/languages/', $offload_settings->storage_name, APP_PATH . 'languages/');
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
    echo "  php product/migrate-to-offload-standalone.php --delete-local\n";
    echo "\n";
}

/* Delete local files if --delete-local flag is set */
if(isset($argv[1]) && $argv[1] === '--delete-local') {
    if($failed_count > 0) {
        die("ERROR: Cannot delete local files because some uploads failed. Please fix errors first.\n");
    }
    
    echo "⚠️  DELETING LOCAL FILES...\n\n";
    
    $deleted = 0;
    
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

