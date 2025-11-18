<?php
/**
 * Diagnostic Script - Check for Missing Settings
 * This script will check your database for missing or incomplete settings
 * 
 * Usage: 
 * 1. Upload this file to your website root directory
 * 2. Access it via browser: https://yourdomain.com/diagnose_errors.php
 * 3. Review the output and fix any issues
 * 4. Delete this file after use for security
 */

// Define required constants
const DEBUG = 1; // Enable to see all errors
const MYSQL_DEBUG = 0;
const LOGGING = 1;
const CACHE = 0; // Disable cache for this diagnostic
const ALTUMCODE = 66;

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Determine the product path
$product_path = __DIR__ . '/product';
if (!is_dir($product_path)) {
    $product_path = __DIR__;
}

// Include the application initialization
require_once realpath($product_path) . '/app/init.php';

echo "<h1>Database Settings Diagnostic</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .section { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .success { color: green; font-weight: bold; }
    .info { color: blue; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background-color: #f2f2f2; }
    pre { background: #f4f4f4; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>";

try {
    // Get all settings from database
    $result = database()->query("SELECT `key`, `value` FROM `settings` ORDER BY `key`");
    $settings = [];
    while($row = $result->fetch_object()) {
        $settings[$row->key] = json_decode($row->value);
    }
    
    echo "<div class='section'>";
    echo "<h2>Settings Check</h2>";
    
    // Check critical settings
    $critical_settings = [
        'cookie_consent' => ['is_enabled'],
        'codes' => ['qr_codes_is_enabled'],
        'links' => ['sixsixpusher_is_enabled'],
        'socials' => ['share_buttons']
    ];
    
    $issues = [];
    
    foreach ($critical_settings as $setting_key => $required_properties) {
        echo "<h3>Checking: {$setting_key}</h3>";
        
        if (!isset($settings[$setting_key])) {
            echo "<p class='error'>❌ Setting '{$setting_key}' does NOT exist in database!</p>";
            $issues[] = "Missing setting: {$setting_key}";
        } else {
            echo "<p class='success'>✅ Setting '{$setting_key}' exists</p>";
            
            foreach ($required_properties as $property) {
                $property_path = explode('->', $property);
                $value = $settings[$setting_key];
                
                foreach ($property_path as $path) {
                    if (is_object($value) && isset($value->$path)) {
                        $value = $value->$path;
                    } else {
                        $value = null;
                        break;
                    }
                }
                
                if ($value === null) {
                    echo "<p class='error'>❌ Property '{$property}' is MISSING in {$setting_key}</p>";
                    $issues[] = "Missing property in {$setting_key}: {$property}";
                } else {
                    $value_display = '';
                    if (is_bool($value)) {
                        $value_display = $value ? 'true' : 'false';
                    } elseif (is_object($value) || is_array($value)) {
                        $value_display = json_encode($value);
                    } else {
                        $value_display = (string)$value;
                    }
                    echo "<p class='success'>✅ Property '{$property}' exists (value: " . htmlspecialchars($value_display) . ")</p>";
                }
            }
            
            // Special check for share_buttons
            if ($setting_key === 'socials' && isset($settings['socials']->share_buttons)) {
                $share_buttons = ['facebook', 'threads', 'x', 'pinterest', 'linkedin', 'reddit', 'whatsapp', 'telegram', 'snapchat', 'microsoft_teams', 'email', 'copy', 'share', 'print'];
                foreach ($share_buttons as $button) {
                    if (!isset($settings['socials']->share_buttons->$button)) {
                        echo "<p class='error'>❌ share_buttons->{$button} is MISSING</p>";
                        $issues[] = "Missing share_buttons property: {$button}";
                    }
                }
            }
        }
        echo "<hr>";
    }
    
    // Check for project_id column
    echo "<h3>Checking Database Structure</h3>";
    try {
        $result = database()->query("SHOW COLUMNS FROM `track_links` LIKE 'project_id'");
        if ($result->num_rows > 0) {
            echo "<p class='success'>✅ Column 'project_id' exists in track_links table</p>";
        } else {
            echo "<p class='error'>❌ Column 'project_id' is MISSING in track_links table</p>";
            $issues[] = "Missing database column: track_links.project_id";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Could not check track_links table: " . $e->getMessage() . "</p>";
    }
    
    echo "</div>";
    
    // Summary
    echo "<div class='section'>";
    echo "<h2>Summary</h2>";
    if (empty($issues)) {
        echo "<p class='success'><strong>✅ No issues found! All settings appear to be correct.</strong></p>";
    } else {
        echo "<p class='error'><strong>❌ Found " . count($issues) . " issue(s):</strong></p>";
        echo "<ul>";
        foreach ($issues as $issue) {
            echo "<li class='error'>{$issue}</li>";
        }
        echo "</ul>";
        echo "<p class='info'><strong>💡 Solution:</strong> Run the fix_database_errors.sql script again, then clear your cache.</p>";
    }
    echo "</div>";
    
    // Show all settings (for debugging)
    echo "<div class='section'>";
    echo "<h2>All Settings (for reference)</h2>";
    echo "<table>";
    echo "<tr><th>Setting Key</th><th>Has Value</th><th>Preview</th></tr>";
    foreach ($settings as $key => $value) {
        $preview = is_object($value) ? json_encode($value, JSON_PRETTY_PRINT) : (string)$value;
        $preview = substr($preview, 0, 200) . (strlen($preview) > 200 ? '...' : '');
        echo "<tr>";
        echo "<td><strong>{$key}</strong></td>";
        echo "<td>" . ($value ? "✅" : "❌") . "</td>";
        echo "<td><pre>" . htmlspecialchars($preview) . "</pre></td>";
        echo "</tr>";
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
echo "<p><strong>⚠️ IMPORTANT:</strong> Delete this file (diagnose_errors.php) after use for security reasons.</p>";
echo "</div>";

