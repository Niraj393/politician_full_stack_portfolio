<?php
// backend/includes/settings-functions.php

/**
 * Get all settings from database
 */
function getAllSettings() {
    global $conn;
    
    try {
        $stmt = $conn->query("SELECT setting_key, setting_value FROM site_settings");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        return $settings;
    } catch (PDOException $e) {
        error_log("Error fetching settings: " . $e->getMessage());
        return [];
    }
}

/**
 * Get a single setting value
 */
function getSetting($key, $default = '') {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $row['setting_value'] : $default;
    } catch (PDOException $e) {
        error_log("Error fetching setting '$key': " . $e->getMessage());
        return $default;
    }
}

/**
 * Update or create a setting
 */
function updateSetting($key, $value) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO site_settings (setting_key, setting_value, updated_at) 
            VALUES (?, ?, NOW()) 
            ON DUPLICATE KEY UPDATE 
            setting_value = VALUES(setting_value), 
            updated_at = NOW()
        ");
        return $stmt->execute([$key, $value]);
    } catch (PDOException $e) {
        error_log("Error updating setting '$key': " . $e->getMessage());
        return false;
    }
}

/**
 * Get settings by category
 */
function getSettingsByCategory($category) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT setting_key, setting_value FROM site_settings WHERE setting_category = ?");
        $stmt->execute([$category]);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (PDOException $e) {
        error_log("Error fetching settings for category '$category': " . $e->getMessage());
        return [];
    }
}

/**
 * Handle file upload for settings
 */
function handleSettingFileUpload($fileInputName, $settingKey) {
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $file = $_FILES[$fileInputName];
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    
    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }
    
    $uploadDir = __DIR__ . '/../uploads/settings/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $filename = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.\-_]/', '', basename($file['name']));
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Delete old file if exists
        $oldFile = getSetting($settingKey);
        if ($oldFile && file_exists(__DIR__ . '/../' . $oldFile)) {
            unlink(__DIR__ . '/../' . $oldFile);
        }
        
        // Update setting
        updateSetting($settingKey, 'uploads/settings/' . $filename);
        return 'uploads/settings/' . $filename;
    }
    
    return false;
}
if (!function_exists('get_image_path')) {
    function get_image_path($key, $default = '') {
        $value = getSetting($key, '');

        if (!$value) {
            return $default;
        }

        // If it's already a full URL, use it
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        // Normalize stored path (remove leading ./ or /)
        $stored = ltrim($value, "./\\/");

        // Build candidate public paths that a browser can request
        $publicCandidates = [
            $stored,
            'backend/' . $stored,
            '/'.$stored,
            '/backend/'.$stored
        ];

        // Determine project root (two levels up from this file: backend/includes -> project root)
        $projectRoot = realpath(__DIR__ . '/../../');
        if ($projectRoot === false) {
            $projectRoot = __DIR__ . '/../../';
        }

        foreach ($publicCandidates as $candidate) {
            $fsPath = rtrim($projectRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($candidate, '/\\');
            if (file_exists($fsPath)) {
                // Return a candidate that the frontend can request (prefer no leading slash)
                // If candidate starts with '/', strip it for consistent relative URLs
                return ltrim($candidate, '/');
            }
        }

        // As a last resort, return default or the original stored value (so at least markup shows something)
        return $default ?: $stored;
    }
}

?>