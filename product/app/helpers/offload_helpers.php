<?php
/*
 * Offload Storage Helpers
 * Functions to load files from offload storage (S3) when local files don't exist
 */

namespace Altum;

defined('ALTUMCODE') || die();

/**
 * Get file content from offload storage or local file
 * Downloads from S3 to temp cache if needed
 */
function get_file_from_offload_or_local($relative_path, $s3_prefix = '') {
    $local_path = ROOT_PATH . $relative_path;
    
    /* If local file exists, use it */
    if(file_exists($local_path)) {
        return $local_path;
    }
    
    /* If offload is not configured, return null */
    if(!\Altum\Plugin::is_active('offload') || !settings()->offload->uploads_url) {
        return null;
    }
    
    /* Try to download from S3 to temp cache */
    $s3_key = $s3_prefix . $relative_path;
    $temp_cache_dir = ROOT_PATH . 'uploads/cache/offload/';
    $temp_cache_path = $temp_cache_dir . md5($s3_key) . '_' . basename($relative_path);
    
    /* If already cached, use cached version */
    if(file_exists($temp_cache_path)) {
        /* Check if cache is fresh (24 hours) */
        if(filemtime($temp_cache_path) > (time() - 86400)) {
            return $temp_cache_path;
        }
    }
    
    /* Download from S3 */
    try {
        $s3 = new \Aws\S3\S3Client(get_aws_s3_config());
        
        /* Create temp cache directory if needed */
        if(!is_dir($temp_cache_dir)) {
            mkdir($temp_cache_dir, 0777, true);
        }
        
        /* Download file */
        $result = $s3->getObject([
            'Bucket' => settings()->offload->storage_name,
            'Key' => $s3_key,
        ]);
        
        /* Save to temp cache */
        file_put_contents($temp_cache_path, $result['Body']);
        
        return $temp_cache_path;
    } catch (\Exception $e) {
        /* If download fails, return null */
        return null;
    }
}

/**
 * Get URL for a file from offload storage or local
 */
function get_file_url_from_offload_or_local($relative_path, $s3_prefix = '') {
    $local_path = ROOT_PATH . $relative_path;
    
    /* If local file exists, return local URL */
    if(file_exists($local_path)) {
        return SITE_URL . $relative_path;
    }
    
    /* If offload is configured, return offload URL */
    if(\Altum\Plugin::is_active('offload') && settings()->offload->uploads_url) {
        $s3_key = $s3_prefix . $relative_path;
        return rtrim(settings()->offload->uploads_url, '/') . '/' . $s3_key;
    }
    
    /* Fallback to local URL */
    return SITE_URL . $relative_path;
}

/**
 * Check if file exists in offload storage
 */
function file_exists_in_offload($s3_key) {
    if(!\Altum\Plugin::is_active('offload') || !settings()->offload->uploads_url) {
        return false;
    }
    
    try {
        $s3 = new \Aws\S3\S3Client(get_aws_s3_config());
        
        $result = $s3->headObject([
            'Bucket' => settings()->offload->storage_name,
            'Key' => $s3_key,
        ]);
        
        return true;
    } catch (\Exception $e) {
        return false;
    }
}

/**
 * List files in offload storage directory
 */
function list_files_in_offload($s3_prefix) {
    if(!\Altum\Plugin::is_active('offload') || !settings()->offload->uploads_url) {
        return [];
    }
    
    try {
        $s3 = new \Aws\S3\S3Client(get_aws_s3_config());
        
        $result = $s3->listObjectsV2([
            'Bucket' => settings()->offload->storage_name,
            'Prefix' => $s3_prefix,
        ]);
        
        $files = [];
        if(isset($result['Contents'])) {
            foreach($result['Contents'] as $object) {
                $files[] = $object['Key'];
            }
        }
        
        return $files;
    } catch (\Exception $e) {
        return [];
    }
}




