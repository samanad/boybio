<?php
/*
 * Cleanup Script: Remove files with wrong paths from cloud storage
 * 
 * This script removes files that were uploaded with full server paths
 * (like /var/www/.../product/app) instead of relative paths (like app/)
 * 
 * Usage:
 * 1. Configure offload settings in Admin Panel first
 * 2. Run this script: php cleanup-wrong-cloud-paths.php
 * 3. Review the list before deletion
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
    die("ERROR: vendor/autoload.php not found.\n");
}

/* Check if AWS SDK is available */
if(!class_exists('Aws\S3\S3Client')) {
    die("ERROR: AWS SDK not found.\n");
}

/* Connect to database */
try {
    $dsn = "mysql:host=" . DATABASE_SERVER . ";dbname=" . DATABASE_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DATABASE_USERNAME, DATABASE_PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch(PDOException $e) {
    die("ERROR: Database connection failed: " . $e->getMessage() . "\n");
}

/* Get offload settings */
$stmt = $pdo->query("SELECT value FROM settings WHERE `key` = 'offload'");
$offload_row = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$offload_row) {
    die("ERROR: Offload settings not found in database.\n");
}

$offload_settings = json_decode($offload_row['value']);

if(!$offload_settings || empty($offload_settings->uploads_url)) {
    die("ERROR: Offload is not configured or uploads_url is not set.\n");
}

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

echo "=== Cloud Storage Cleanup Script ===\n";
echo "This will find and optionally delete files with wrong paths (containing /var/www/ or full server paths)\n\n";

/* List all objects in the bucket */
echo "Scanning cloud storage for files with wrong paths...\n\n";

$wrong_paths = [];
$correct_paths = [];

try {
    $result = $s3->listObjectsV2([
        'Bucket' => $offload_settings->storage_name,
    ]);
    
    if(isset($result['Contents'])) {
        foreach($result['Contents'] as $object) {
            $key = $object['Key'];
            
            /* Check if key contains full server path */
            if(strpos($key, '/var/www/') !== false || 
               strpos($key, 'var/www/') !== false ||
               preg_match('/\/[a-z]:\\\/i', $key) || // Windows paths like C:\
               preg_match('/\/[a-z]:\//i', $key)) {  // Windows paths like C:/
                $wrong_paths[] = $key;
            } else {
                $correct_paths[] = $key;
            }
        }
    }
} catch (\Exception $e) {
    die("ERROR: Failed to list objects: " . $e->getMessage() . "\n");
}

echo "Found " . count($wrong_paths) . " files with wrong paths\n";
echo "Found " . count($correct_paths) . " files with correct paths\n\n";

if(count($wrong_paths) == 0) {
    echo "✓ No files with wrong paths found. Your cloud storage is clean!\n";
    exit(0);
}

/* Show first 20 wrong paths */
echo "Files with wrong paths (showing first 20):\n";
foreach(array_slice($wrong_paths, 0, 20) as $path) {
    echo "  - $path\n";
}
if(count($wrong_paths) > 20) {
    echo "  ... and " . (count($wrong_paths) - 20) . " more\n";
}
echo "\n";

/* Ask for confirmation */
echo "Do you want to DELETE these files? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
fclose($handle);

if(strtolower($line) !== 'yes') {
    echo "Cancelled. No files were deleted.\n";
    exit(0);
}

/* Delete wrong path files */
echo "\nDeleting files with wrong paths...\n";
$deleted_count = 0;
$failed_count = 0;

foreach($wrong_paths as $key) {
    try {
        $s3->deleteObject([
            'Bucket' => $offload_settings->storage_name,
            'Key' => $key,
        ]);
        echo "  ✓ Deleted: $key\n";
        $deleted_count++;
    } catch (\Exception $e) {
        echo "  ✗ Failed to delete: $key - " . $e->getMessage() . "\n";
        $failed_count++;
    }
}

echo "\n=== Cleanup Complete ===\n";
echo "✓ Deleted: $deleted_count files\n";
if($failed_count > 0) {
    echo "✗ Failed: $failed_count files\n";
}
echo "\n";
echo "Now run the migration script again to upload files with correct paths:\n";
echo "  php product/migrate-to-offload-standalone.php\n";

