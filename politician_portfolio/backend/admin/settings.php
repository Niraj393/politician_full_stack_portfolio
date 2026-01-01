<?php
// backend/admin/settings.php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.php';

$message = '';
$settings = [];
$categories = [];

// Check for saved parameter from redirect
if (isset($_GET['saved']) && $_GET['saved'] == 1) {
    $message = '<div class="alert alert-success">Settings saved successfully!</div>';
}

// Load all settings
try {
    $stmt = $conn->query("SELECT * FROM site_settings ORDER BY setting_category, setting_group, setting_key");
    $allSettings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organize settings by category and group
    foreach ($allSettings as $setting) {
        $category = $setting['setting_category'];
        $group = $setting['setting_group'];
        
        if (!isset($categories[$category])) {
            $categories[$category] = [];
        }
        
        if (!isset($categories[$category][$group])) {
            $categories[$category][$group] = [];
        }

        // Index settings by their key so templates can access by setting_key
        $categories[$category][$group][$setting['setting_key']] = $setting;
    }
    
} catch (PDOException $e) {
    $message = '<div class="alert alert-danger">Error loading settings: ' . $e->getMessage() . '</div>';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();
        
        // Process file uploads first
        $fileFields = [
            'hero_bg_image', 'hero_main_image', 'hero_party_logo',
            'footer_party_logo', 'secretary_photo', 'donation_qr_code'
        ];
        
        foreach ($fileFields as $field) {
            $fileInputName = 'setting_' . $field;
            
            if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES[$fileInputName];
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                
                if (in_array($file['type'], $allowedTypes)) {
                    $uploadDir = __DIR__ . '/../uploads/settings/';
                    
                    // Create directory if it doesn't exist
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    // Clean filename
                    $filename = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.\-_]/', '', basename($file['name']));
                    $filepath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        // Save relative path for database
                        $relativePath = 'uploads/settings/' . $filename;
                        
                        // Update in POST data so it gets saved in the next loop
                        $_POST[$fileInputName] = $relativePath;
                        
                        // Delete old file if exists
                        $stmt = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
                        $stmt->execute([$field]);
                        $oldFile = $stmt->fetchColumn();
                        
                        if ($oldFile && file_exists(__DIR__ . '/../' . $oldFile) && $oldFile != $relativePath) {
                            @unlink(__DIR__ . '/../' . $oldFile);
                        }
                    }
                }
            }
        }
        
        // Now process all POST data including the uploaded file paths
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'setting_') === 0) {
                $settingKey = substr($key, 8); // Remove 'setting_' prefix
                $value = trim($value);
                
                // Skip if this was a file field that had upload error
                if (isset($_FILES[$key]) && $_FILES[$key]['error'] !== UPLOAD_ERR_OK && $_FILES[$key]['error'] !== 4) {
                    continue;
                }
                
                $stmt = $conn->prepare("
                    INSERT INTO site_settings (setting_key, setting_value, updated_at) 
                    VALUES (?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE 
                    setting_value = VALUES(setting_value), 
                    updated_at = NOW()
                ");
                $stmt->execute([$settingKey, $value]);
            }
        }
        
        $conn->commit();
        
        // REDIRECT to clear form state and prevent browser draft warning
        header("Location: settings.php?saved=1");
        exit;
        
    } catch (PDOException $e) {
        $conn->rollBack();
        $message = '<div class="alert alert-danger">Error saving settings: ' . $e->getMessage() . '</div>';
    }
}

// Helper function to get setting value
function getSetting($category, $group, $key, $default = '') {
    global $categories;
    return $categories[$category][$group][$key]['setting_value'] ?? $default;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Settings - Admin Dashboard</title>
    <link rel="icon" href="logo.jpeg" type="image/jpeg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #4338ca;
            --secondary: #7c3aed;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --dark: #1f2937;
            --light: #90bfeeff;
            --gray: #0d0d0eff;
            --light-gray: #151516ff;
            --border: #d1d5db;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            --radius-sm: 0.375rem;
            --radius: 0.5rem;
            --radius-md: 0.75rem;
            --radius-lg: 1rem;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--dark);
            min-height: 100vh;
            line-height: 1.6;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 0;
            background: var(--light);
        }

        /* Header */
        .page-header {
            padding: 1.2rem 2rem;
            background: white;
            border-bottom: 1px solid var(--light-gray);
            position: sticky;
            top: 0;
            z-index: 30;
            backdrop-filter: blur(8px);
            background: rgba(255, 255, 255, 0.95);
        }

        .page-header h1 {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.25rem;
        }

        .page-header h1 i {
            color: var(--primary);
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-header p {
            color: var(--gray);
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* Alerts */
        .alert {
            margin: 0 2rem 1.5rem;
            padding: 1rem 1.25rem;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideDown 0.3s ease;
            border-left: 4px solid transparent;
        }

        .alert-success {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
            border-left-color: var(--success);
        }

        .alert-danger {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
            border-left-color: var(--danger);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Modern Navigation */
        .settings-nav {
            position: sticky;
            top: 88px;
            z-index: 30;
            background: white;
            border-bottom: 1px solid var(--light-gray);
            padding: 0.75rem 2rem;
            box-shadow: var(--shadow-sm);
        }

        .nav-scroll-container {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding: 0.25rem 0;
            scrollbar-width: thin;
            scrollbar-color: var(--primary) transparent;
        }

        .nav-scroll-container::-webkit-scrollbar {
            height: 4px;
        }

        .nav-scroll-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .nav-scroll-container::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 2px;
        }

        .nav-tab {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            background: white;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            color: var(--gray);
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: var(--transition);
            white-space: nowrap;
            position: relative;
            overflow: hidden;
        }

        .nav-tab:hover {
            background: var(--light);
            border-color: var(--primary-light);
            color: var(--primary);
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }

        .nav-tab.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-color: transparent;
            box-shadow: var(--shadow-md);
        }

        .nav-tab i {
            font-size: 0.875rem;
        }

        .nav-tab.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 20px;
            height: 3px;
            background: white;
            border-radius: 2px;
        }

        /* Settings Container */
        .settings-container {
            padding: 2rem;
        }

        .settings-group {
            display: none;
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            overflow: hidden;
            animation: fadeInUp 0.5s ease;
            border: 1px solid var(--light-gray);
        }

        .settings-group.active {
            display: block;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .group-header {
            padding: 1.5rem 2rem;
            background: linear-gradient(135deg, var(--light), #f1f5f9);
            border-bottom: 1px solid var(--light-gray);
            position: relative;
        }

        .group-header h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .group-header h3 i {
            color: var(--primary);
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .group-description {
            color: var(--gray);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
            padding: 2rem;
        }

        .setting-item {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .setting-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.875rem;
        }

        .setting-label i {
            color: var(--primary);
            font-size: 0.875rem;
        }

        .setting-description {
            color: var(--gray);
            font-size: 0.75rem;
            line-height: 1.4;
        }

        /* Form Elements */
        .form-input, .form-textarea, .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-family: 'Inter', sans-serif;
            font-size: 0.875rem;
            transition: var(--transition);
            background: white;
        }

        .form-input:focus, .form-textarea:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
            line-height: 1.5;
        }

        /* File Upload */
        .file-input {
            position: relative;
        }

        .file-input input[type="file"] {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 2;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--light), #f8fafc);
            border: 2px dashed var(--border);
            border-radius: var(--radius);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .file-input-label::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, transparent, rgba(79, 70, 229, 0.05));
            opacity: 0;
            transition: var(--transition);
        }

        .file-input-label:hover::before {
            opacity: 1;
        }

        .file-input-label:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .file-input-label i {
            font-size: 1.5rem;
            color: var(--primary);
            z-index: 1;
        }

        .file-input-text {
            z-index: 1;
        }

        .file-input-text h4 {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .file-input-text p {
            font-size: 0.75rem;
            color: var(--gray);
        }

        .file-preview {
            margin-top: 1rem;
            position: relative;
        }

        .file-preview img {
            width: 100%;
            max-width: 240px;
            max-height: 180px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            object-fit: cover;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }

        .file-preview img:hover {
            transform: scale(1.02);
            box-shadow: var(--shadow);
        }

        .remove-preview {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            width: 28px;
            height: 28px;
            background: white;
            border: 1px solid var(--danger);
            border-radius: 50%;
            color: var(--danger);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            opacity: 0;
            transform: scale(0.8);
        }

        .file-preview:hover .remove-preview {
            opacity: 1;
            transform: scale(1);
        }

        .remove-preview:hover {
            background: var(--danger);
            color: white;
        }

        /* Language Toggle */
        .language-toggle {
            display: inline-flex;
            background: var(--light);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 0.25rem;
            margin-bottom: 1rem;
        }

        .lang-tab {
            padding: 0.5rem 1rem;
            background: transparent;
            border: none;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 0.75rem;
            cursor: pointer;
            transition: var(--transition);
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .lang-tab:hover {
            color: var(--primary);
            background: rgba(79, 70, 229, 0.1);
        }

        .lang-tab.active {
            background: white;
            color: var(--primary);
            box-shadow: var(--shadow-sm);
        }

        .lang-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .lang-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Save Buttons */
        .save-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--light-gray);
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        .save-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.75rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .save-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }

        .save-btn:hover::before {
            left: 100%;
        }

        .save-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .save-btn:active {
            transform: translateY(0);
        }

        .save-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .save-btn.loading i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Section Save Button */
        .section-save {
            grid-column: 1 / -1;
            padding: 1.5rem 0 0;
            margin-top: 1rem;
            border-top: 1px solid var(--light-gray);
            display: flex;
            justify-content: flex-start;
        }

        .save-btn-section {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            color: white;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .save-btn-section:hover {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        /* Stats Display */
        .stats-display {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .stat-box {
            flex: 1;
            padding: 1rem;
            background: linear-gradient(135deg, var(--light), #0a6acbff);
            border-radius: var(--radius);
            text-align: center;
            border: 1px solid var(--light-gray);
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0;
            }
            
            .settings-grid {
                grid-template-columns: 1fr;
                gap: 1.25rem;
            }
            
            .page-header,
            .settings-nav,
            .settings-container {
                padding: 1.25rem;
            }
        }

        @media (max-width: 768px) {
            .settings-nav {
                top: 76px;
                padding: 0.5rem 1rem;
            }
            
            .nav-tab {
                padding: 0.5rem 1rem;
                font-size: 0.75rem;
            }
            
            .group-header {
                padding: 1.25rem 1.5rem;
            }
            
            .settings-grid {
                padding: 1.5rem;
            }
            
            .save-section {
                flex-direction: column;
            }
            
            .save-btn {
                width: 100%;
                justify-content: center;
            }
            
            .section-save {
                justify-content: center;
            }
            
            .save-btn-section {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .page-header h1 {
                font-size: 1.5rem;
            }
            
            .nav-scroll-container {
                gap: 0.25rem;
            }
            
            .nav-tab {
                padding: 0.5rem 0.75rem;
            }
            
            .stats-display {
                flex-direction: column;
            }
        }

        /* Scroll Progress */
        .scroll-progress {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            transform-origin: 0%;
            z-index: 50;
        }

        /* Floating Action Button */
        .fab {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            box-shadow: var(--shadow-lg);
            transition: var(--transition);
            z-index: 40;
            border: none;
        }

        .fab:hover {
            transform: scale(1.1) rotate(90deg);
            box-shadow: var(--shadow-lg);
        }

        .fab i {
            font-size: 1.25rem;
        }

        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 100;
        }

        .loading-overlay.active {
            display: flex;
        }

        .loader {
            width: 48px;
            height: 48px;
            border: 3px solid var(--light-gray);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <!-- Scroll Progress -->
            <div class="scroll-progress" id="scrollProgress"></div>
            
            <!-- Header -->
            <div class="page-header">
                <h1><i class="fas fa-sliders-h"></i> Site Settings</h1>
                <p>Manage all website configurations and content</p>
            </div>

            <!-- Messages -->
            <?php echo $message; ?>

            <!-- Modern Navigation -->
            <nav class="settings-nav">
                <div class="nav-scroll-container" id="settingsNav">
                    <button class="nav-tab active" data-tab="hero">
                        <i class="fas fa-home"></i> Hero
                    </button>
                    <button class="nav-tab" data-tab="about">
                        <i class="fas fa-user"></i> About
                    </button>
                    <button class="nav-tab" data-tab="moreVideos">
                        <i class="fas fa-link"></i> More Videos
                    </button>
                    <button class="nav-tab" data-tab="donation">
                        <i class="fas fa-donate"></i> Donation
                    </button>
                    <button class="nav-tab" data-tab="secretary">
                        <i class="fas fa-user-tie"></i> Secretary
                    </button>
                    <button class="nav-tab" data-tab="contact">
                        <i class="fas fa-address-book"></i> Contact
                    </button>
                    <button class="nav-tab" data-tab="social">
                        <i class="fas fa-share-alt"></i> Social
                    </button>
                    
                    <button class="nav-tab" data-tab="general">
                        <i class="fas fa-cog"></i> General
                    </button>
                </div>
            </nav>

            <!-- Loading Overlay -->
            <div class="loading-overlay" id="loadingOverlay">
                <div class="loader"></div>
            </div>

            <!-- Settings Form -->
            <form method="POST" enctype="multipart/form-data" class="settings-form" id="settingsForm" autocomplete="off">
                <div class="settings-container">
                    
                    <!-- Hero Section -->
                    <div class="settings-group active" id="heroGroup">
                        <div class="group-header">
                            <h3><i class="fas fa-home"></i> Hero Section Settings</h3>
                            <p class="group-description">Configure the main hero section with politician information</p>
                        </div>
                        
                        <div class="settings-grid">
                            <!-- Background Image -->
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-image"></i> Background Image
                                </label>
                                <p class="setting-description">Hero section background image (1920x1080 recommended)</p>
                                <div class="file-input">
                                    <input type="file" name="setting_hero_bg_image" id="heroBgImage" accept="image/*">
                                    <label for="heroBgImage" class="file-input-label">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <div class="file-input-text">
                                            <h4>Upload Background</h4>
                                            <p>JPG, PNG or WebP</p>
                                        </div>
                                    </label>
                                </div>
                                <?php if (isset($categories['hero']['hero_section']['hero_bg_image'])): ?>
                                <div class="file-preview">
                                    <img src="../<?php echo htmlspecialchars($categories['hero']['hero_section']['hero_bg_image']['setting_value']); ?>" 
                                         alt="Current Background" 
                                         id="heroBgImagePreview">
                                    <button type="button" class="remove-preview" onclick="removePreview('heroBgImage')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Main Politician Image -->
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-portrait"></i> Politician Photo
                                </label>
                                <p class="setting-description">Main profile photo (400x500 recommended)</p>
                                <div class="file-input">
                                    <input type="file" name="setting_hero_main_image" id="heroMainImage" accept="image/*">
                                    <label for="heroMainImage" class="file-input-label">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <div class="file-input-text">
                                            <h4>Upload Photo</h4>
                                            <p>JPG, PNG or WebP</p>
                                        </div>
                                    </label>
                                </div>
                                <?php if (isset($categories['hero']['hero_section']['hero_main_image'])): ?>
                                <div class="file-preview">
                                    <img src="../<?php echo htmlspecialchars($categories['hero']['hero_section']['hero_main_image']['setting_value']); ?>" 
                                         alt="Current Politician Photo" 
                                         id="heroMainImagePreview">
                                    <button type="button" class="remove-preview" onclick="removePreview('heroMainImage')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Party Logo -->
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-flag"></i> Party Logo
                                </label>
                                <p class="setting-description">Political party logo for hero section</p>
                                <div class="file-input">
                                    <input type="file" name="setting_hero_party_logo" id="heroPartyLogo" accept="image/*">
                                    <label for="heroPartyLogo" class="file-input-label">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <div class="file-input-text">
                                            <h4>Upload Logo</h4>
                                            <p>Square or rectangular image</p>
                                        </div>
                                    </label>
                                </div>
                                <?php if (isset($categories['hero']['hero_section']['hero_party_logo'])): ?>
                                <div class="file-preview">
                                    <img src="../<?php echo htmlspecialchars($categories['hero']['hero_section']['hero_party_logo']['setting_value']); ?>" 
                                         alt="Current Party Logo" 
                                         id="heroPartyLogoPreview">
                                    <button type="button" class="remove-preview" onclick="removePreview('heroPartyLogo')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Politician Information -->
                            <div class="setting-item">
                                <div class="language-toggle">
                                    <button type="button" class="lang-tab active" data-lang="en">English</button>
                                    <button type="button" class="lang-tab" data-lang="np">नेपाली</button>
                                </div>
                                
                                <div class="lang-content active" data-lang="en">
                                    <label class="setting-label">
                                        <i class="fas fa-user"></i> Politician Name
                                    </label>
                                    <input type="text" 
                                           name="setting_hero_politician_name_en" 
                                           class="form-input"
                                           value="<?php echo htmlspecialchars(getSetting('hero', 'hero_section', 'hero_politician_name_en', 'Khadga Prasad Sharma Oli')); ?>"
                                           placeholder="Enter politician name in English">
                                    
                                    <label class="setting-label" style="margin-top: 1rem;">
                                        <i class="fas fa-briefcase"></i> Politician Title
                                    </label>
                                    <input type="text" 
                                           name="setting_hero_politician_title_en" 
                                           class="form-input"
                                           value="<?php echo htmlspecialchars(getSetting('hero', 'hero_section', 'hero_politician_title_en', 'Former Prime Minister of Nepal · Chairman of CPN (UML)')); ?>"
                                           placeholder="Enter politician title in English">
                                    
                                    <label class="setting-label" style="margin-top: 1rem;">
                                        <i class="fas fa-flag"></i> Party Name
                                    </label>
                                    <input type="text" 
                                           name="setting_hero_party_name_en" 
                                           class="form-input"
                                           value="<?php echo htmlspecialchars(getSetting('hero', 'hero_section', 'hero_party_name_en', 'CPN (UML)')); ?>"
                                           placeholder="Enter party name in English">
                                </div>
                                
                                <div class="lang-content" data-lang="np">
                                    <label class="setting-label">
                                        <i class="fas fa-user"></i> नेता नाम
                                    </label>
                                    <input type="text" 
                                           name="setting_hero_politician_name_np" 
                                           class="form-input"
                                           value="<?php echo htmlspecialchars(getSetting('hero', 'hero_section', 'hero_politician_name_np', 'खड्गप्रसाद शर्मा ओली')); ?>"
                                           placeholder="नेपालीमा नाम लेख्नुहोस्">
                                    
                                    <label class="setting-label" style="margin-top: 1rem;">
                                        <i class="fas fa-briefcase"></i> उपाधि
                                    </label>
                                    <input type="text" 
                                           name="setting_hero_politician_title_np" 
                                           class="form-input"
                                           value="<?php echo htmlspecialchars(getSetting('hero', 'hero_section', 'hero_politician_title_np', 'नेपालका पूर्व प्रधानमन्त्री · नेकपा (एमाले) का अध्यक्ष')); ?>"
                                           placeholder="नेपालीमा उपाधि लेख्नुहोस्">
                                    
                                    <label class="setting-label" style="margin-top: 1rem;">
                                        <i class="fas fa-flag"></i> पार्टीको नाम
                                    </label>
                                    <input type="text" 
                                           name="setting_hero_party_name_np" 
                                           class="form-input"
                                           value="<?php echo htmlspecialchars(getSetting('hero', 'hero_section', 'hero_party_name_np', 'नेकपा (एमाले)')); ?>"
                                           placeholder="नेपालीमा पार्टीको नाम">
                                </div>
                            </div>

                            <!-- Statistics -->
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-chart-line"></i> Statistics
                                </label>
                                
                                <input type="number" 
                                       name="setting_hero_years_in_politics" 
                                       class="form-input"
                                       value="<?php echo htmlspecialchars(getSetting('hero', 'hero_section', 'hero_years_in_politics', '42')); ?>"
                                       min="0" max="100" 
                                       oninput="updateStats()">
                                
                                <label class="setting-label" style="margin-top: 1rem;">
                                    <i class="fas fa-crown"></i> Terms as PM
                                </label>
                                <input type="number" 
                                       name="setting_hero_terms_as_pm" 
                                       class="form-input"
                                       value="<?php echo htmlspecialchars(getSetting('hero', 'hero_section', 'hero_terms_as_pm', '4')); ?>"
                                       min="0" max="20"
                                       oninput="updateStats()">
                                
                                <label class="setting-label" style="margin-top: 1rem;">
                                    <i class="fas fa-percentage"></i> Approval Rating
                                </label>
                                <input type="number" 
                                       name="setting_hero_approval_rating" 
                                       class="form-input"
                                       value="<?php echo htmlspecialchars(getSetting('hero', 'hero_section', 'hero_approval_rating', '75')); ?>"
                                       min="0" max="100"
                                       oninput="updateStats()">
                            </div>

                            <!-- Stats Preview -->
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-eye"></i> Live Preview
                                </label>
                                <div class="stats-display">
                                    <div class="stat-box">
                                        <div class="stat-value" id="previewYears"><?php echo getSetting('hero', 'hero_section', 'hero_years_in_politics', '42'); ?></div>
                                        <div class="stat-label">Years</div>
                                    </div>
                                    <div class="stat-box">
                                        <div class="stat-value" id="previewTerms"><?php echo getSetting('hero', 'hero_section', 'hero_terms_as_pm', '4'); ?></div>
                                        <div class="stat-label">Terms as PM</div>
                                    </div>
                                    <div class="stat-box">
                                        <div class="stat-value" id="previewRating"><?php echo getSetting('hero', 'hero_section', 'hero_approval_rating', '75'); ?>%</div>
                                        <div class="stat-label">Approval</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section Save Button -->
                            <div class="section-save">
                                <button type="submit" class="save-btn-section">
                                    <i class="fas fa-save"></i> Save Hero Settings
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- About Section -->
                    <div class="settings-group" id="aboutGroup">
                        <div class="group-header">
                            <h3><i class="fas fa-user"></i> About Section Settings</h3>
                            <p class="group-description">Configure about section content and quick facts</p>
                        </div>
                        
                        <div class="settings-grid">
                            <!-- About Titles -->
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-heading"></i> Section Titles
                                </label>
                                <div class="language-toggle">
                                    <button type="button" class="lang-tab active" data-lang="en">English</button>
                                    <button type="button" class="lang-tab" data-lang="np">नेपाली</button>
                                </div>
                                
                                <div class="lang-content active" data-lang="en">
                                    <input type="text" 
                                           name="setting_about_title_en" 
                                           class="form-input"
                                           value="<?php echo htmlspecialchars(getSetting('about', 'about_section', 'about_title_en', 'About')); ?>"
                                           placeholder="Section title in English">
                                    
                                    <input type="text" 
                                           name="setting_about_subtitle_en" 
                                           class="form-input"
                                           style="margin-top: 0.75rem;"
                                           value="<?php echo htmlspecialchars(getSetting('about', 'about_section', 'about_subtitle_en', 'A visionary leader dedicated to Nepal\'s development and prosperity')); ?>"
                                           placeholder="Subtitle in English">
                                </div>
                                
                                <div class="lang-content" data-lang="np">
                                    <input type="text" 
                                           name="setting_about_title_np" 
                                           class="form-input"
                                           value="<?php echo htmlspecialchars(getSetting('about', 'about_section', 'about_title_np', 'बारेमा')); ?>"
                                           placeholder="नेपालीमा शीर्षक">
                                    
                                    <input type="text" 
                                           name="setting_about_subtitle_np" 
                                           class="form-input"
                                           style="margin-top: 0.75rem;"
                                           value="<?php echo htmlspecialchars(getSetting('about', 'about_section', 'about_subtitle_np', 'नेपालको विकास र समृद्धिका लागि समर्पित दूरदर्शी नेता')); ?>"
                                           placeholder="नेपालीमा उपशीर्षक">
                                </div>
                            </div>

                            <!-- About Content -->
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-align-left"></i> About Content
                                </label>
                                <div class="language-toggle">
                                    <button type="button" class="lang-tab active" data-lang="en">English</button>
                                    <button type="button" class="lang-tab" data-lang="np">नेपाली</button>
                                </div>
                                
                                <div class="lang-content active" data-lang="en">
                                    <textarea name="setting_about_content_en" 
                                              class="form-textarea"
                                              placeholder="Write about content in English"
                                              rows="8"><?php echo htmlspecialchars(getSetting('about', 'about_section', 'about_content_en', '')); ?></textarea>
                                </div>
                                
                                <div class="lang-content" data-lang="np">
                                    <textarea name="setting_about_content_np" 
                                              class="form-textarea"
                                              placeholder="नेपालीमा सामग्री लेख्नुहोस्"
                                              rows="8"><?php echo htmlspecialchars(getSetting('about', 'about_section', 'about_content_np', '')); ?></textarea>
                                </div>
                            </div>

                            <!-- Quick Facts - English -->
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-info-circle"></i> Quick Facts (English)
                                </label>
                                
                                <input type="text" 
                                       name="setting_about_birth_date_en" 
                                       class="form-input"
                                       value="<?php echo htmlspecialchars(getSetting('about', 'quick_facts', 'about_birth_date_en', 'February 22, 1952')); ?>"
                                       placeholder="Date of Birth">
                                
                                <input type="text" 
                                       name="setting_about_education_en" 
                                       class="form-input"
                                       style="margin-top: 0.75rem;"
                                       value="<?php echo htmlspecialchars(getSetting('about', 'quick_facts', 'about_education_en', 'Tribhuvan University')); ?>"
                                       placeholder="Education">
                                
                                <input type="text" 
                                       name="setting_about_constituency_en" 
                                       class="form-input"
                                       style="margin-top: 0.75rem;"
                                       value="<?php echo htmlspecialchars(getSetting('about', 'quick_facts', 'about_constituency_en', 'Jhapa-5')); ?>"
                                       placeholder="Constituency">
                                
                                <input type="text" 
                                       name="setting_about_career_start_en" 
                                       class="form-input"
                                       style="margin-top: 0.75rem;"
                                       value="<?php echo htmlspecialchars(getSetting('about', 'quick_facts', 'about_career_start_en', 'Since 1970')); ?>"
                                       placeholder="Political Career Start">
                            </div>

                            <!-- Quick Facts - Nepali -->
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-info-circle"></i> Quick Facts (नेपाली)
                                </label>
                                
                                <input type="text" 
                                       name="setting_about_birth_date_np" 
                                       class="form-input"
                                       value="<?php echo htmlspecialchars(getSetting('about', 'quick_facts', 'about_birth_date_np', '२००८ फागुन १०')); ?>"
                                       placeholder="जन्म मिति">
                                
                                <input type="text" 
                                       name="setting_about_education_np" 
                                       class="form-input"
                                       style="margin-top: 0.75rem;"
                                       value="<?php echo htmlspecialchars(getSetting('about', 'quick_facts', 'about_education_np', 'त्रिभुवन विश्वविद्यालय')); ?>"
                                       placeholder="शिक्षा">
                                
                                <input type="text" 
                                       name="setting_about_constituency_np" 
                                       class="form-input"
                                       style="margin-top: 0.75rem;"
                                       value="<?php echo htmlspecialchars(getSetting('about', 'quick_facts', 'about_constituency_np', 'झापा-५')); ?>"
                                       placeholder="निर्वाचन क्षेत्र">
                                
                                <input type="text" 
                                       name="setting_about_career_start_np" 
                                       class="form-input"
                                       style="margin-top: 0.75rem;"
                                       value="<?php echo htmlspecialchars(getSetting('about', 'quick_facts', 'about_career_start_np', 'सन् १९७० देखि')); ?>"
                                       placeholder="राजनीतिक करियर">
                            </div>

                            <!-- Section Save Button -->
                            <div class="section-save">
                                <button type="submit" class="save-btn-section">
                                    <i class="fas fa-save"></i> Save About Settings
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Contact & Footer Section -->
                    <div class="settings-group" id="contactGroup">
                        <div class="group-header">
                            <h3><i class="fas fa-address-book"></i> Contact & Footer Settings</h3>
                            <p class="group-description">Configure contact information and footer content</p>
                        </div>
                        
                        <div class="settings-grid">
                            <!-- Footer Content -->
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-font"></i> Footer Content
                                </label>
                                <div class="language-toggle">
                                    <button type="button" class="lang-tab active" data-lang="en">English</button>
                                    <button type="button" class="lang-tab" data-lang="np">नेपाली</button>
                                </div>
                                
                                <div class="lang-content active" data-lang="en">
                                    <input type="text" 
                                           name="setting_footer_politician_name_en" 
                                           class="form-input"
                                           value="<?php echo htmlspecialchars(getSetting('footer', 'footer_section', 'footer_politician_name_en', 'KP Oli')); ?>"
                                           placeholder="Footer name in English">
                                    
                                    <textarea name="setting_footer_description_en" 
                                              class="form-textarea"
                                              style="margin-top: 0.75rem;"
                                              placeholder="Footer description in English"
                                              rows="4"><?php echo htmlspecialchars(getSetting('footer', 'footer_section', 'footer_description_en', 'Official portfolio of Khadga Prasad Sharma Oli, former Prime Minister of Nepal and Chairman of CPN (UML).')); ?></textarea>
                                </div>
                                
                                <div class="lang-content" data-lang="np">
                                    <input type="text" 
                                           name="setting_footer_politician_name_np" 
                                           class="form-input"
                                           value="<?php echo htmlspecialchars(getSetting('footer', 'footer_section', 'footer_politician_name_np', 'केपी ओली')); ?>"
                                           placeholder="नेपालीमा फुटर नाम">
                                    
                                    <textarea name="setting_footer_description_np" 
                                              class="form-textarea"
                                              style="margin-top: 0.75rem;"
                                              placeholder="नेपालीमा फुटर विवरण"
                                              rows="4"><?php echo htmlspecialchars(getSetting('footer', 'footer_section', 'footer_description_np', 'खड्गप्रसाद शर्मा ओली, नेपालका पूर्व प्रधानमन्त्री र नेकपा (एमाले) का अध्यक्षको आधिकारिक पोर्टफोलियो।')); ?></textarea>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-map-marker-alt"></i> Contact Information
                                </label>
                                <div class="language-toggle">
                                    <button type="button" class="lang-tab active" data-lang="en">English</button>
                                    <button type="button" class="lang-tab" data-lang="np">नेपाली</button>
                                </div>
                                
                                <div class="lang-content active" data-lang="en">
                                    <input type="text" 
                                           name="setting_footer_address_en" 
                                           class="form-input"
                                           value="<?php echo htmlspecialchars(getSetting('footer', 'contact_info', 'footer_address_en', 'Kathmandu, Nepal')); ?>"
                                           placeholder="Address in English">
                                </div>
                                
                                <div class="lang-content" data-lang="np">
                                    <input type="text" 
                                           name="setting_footer_address_np" 
                                           class="form-input"
                                           value="<?php echo htmlspecialchars(getSetting('footer', 'contact_info', 'footer_address_np', 'काठमाडौं, नेपाल')); ?>"
                                           placeholder="नेपालीमा ठेगाना">
                                </div>
                                
                                <input type="text" 
                                       name="setting_footer_phone" 
                                       class="form-input"
                                       style="margin-top: 0.75rem;"
                                       value="<?php echo htmlspecialchars(getSetting('footer', 'contact_info', 'footer_phone', '+977-1-2345678')); ?>"
                                       placeholder="Phone number">
                                
                                <input type="email" 
                                       name="setting_footer_email" 
                                       class="form-input"
                                       style="margin-top: 0.75rem;"
                                       value="<?php echo htmlspecialchars(getSetting('footer', 'contact_info', 'footer_email', 'contact@kpoli-portfolio.com')); ?>"
                                       placeholder="Email address">
                            </div>

                            <!-- Footer Logo -->
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-flag"></i> Footer Logo
                                </label>
                                <p class="setting-description">Logo displayed in footer (50x50 recommended)</p>
                                <div class="file-input">
                                    <input type="file" name="setting_footer_party_logo" id="footerPartyLogo" accept="image/*">
                                    <label for="footerPartyLogo" class="file-input-label">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <div class="file-input-text">
                                            <h4>Upload Logo</h4>
                                            <p>Square image works best</p>
                                        </div>
                                    </label>
                                </div>
                                <?php if (isset($categories['footer']['footer_section']['footer_party_logo'])): ?>
                                <div class="file-preview">
                                    <img src="../<?php echo htmlspecialchars($categories['footer']['footer_section']['footer_party_logo']['setting_value']); ?>" 
                                         alt="Current Footer Logo" 
                                         id="footerPartyLogoPreview">
                                    <button type="button" class="remove-preview" onclick="removePreview('footerPartyLogo')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Copyright -->
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-copyright"></i> Copyright
                                </label>
                                <div class="language-toggle">
                                    <button type="button" class="lang-tab active" data-lang="en">English</button>
                                    <button type="button" class="lang-tab" data-lang="np">नेपाली</button>
                                </div>
                                
                                <div class="lang-content active" data-lang="en">
                                    <input type="text" 
                                           name="setting_footer_copyright_en" 
                                           class="form-input"
                                           value="<?php echo htmlspecialchars(getSetting('footer', 'copyright', 'footer_copyright_en', '© 2024 KP Sharma Oli Portfolio. All rights reserved.')); ?>"
                                           placeholder="Copyright text in English">
                                </div>
                                
                                <div class="lang-content" data-lang="np">
                                    <input type="text" 
                                           name="setting_footer_copyright_np" 
                                           class="form-input"
                                           value="<?php echo htmlspecialchars(getSetting('footer', 'copyright', 'footer_copyright_np', '© २०२४ केपी शर्मा ओली पोर्टफोलियो। सर्वाधिकार सुरक्षित।')); ?>"
                                           placeholder="नेपालीमा कपीराइट पाठ">
                                </div>
                            </div>

                            <!-- Section Save Button -->
                            <div class="section-save">
                                <button type="submit" class="save-btn-section">
                                    <i class="fas fa-save"></i> Save Contact & Footer Settings
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Donation Section -->
                    <div class="settings-group" id="donationGroup">
                        <div class="group-header">
                            <h3><i class="fas fa-donate"></i> Donation Settings</h3>
                            <p class="group-description">Configure donation information and QR code</p>
                        </div>
                        
                        <div class="settings-grid">
                            <!-- QR Code -->
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-qrcode"></i> Donation QR Code
                                </label>
                                <p class="setting-description">QR code for bank transfer/online payment</p>
                                <div class="file-input">
                                    <input type="file" name="setting_donation_qr_code" id="donationQrCode" accept="image/*">
                                    <label for="donationQrCode" class="file-input-label">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <div class="file-input-text">
                                            <h4>Upload QR Code</h4>
                                            <p>Square image (300x300 recommended)</p>
                                        </div>
                                    </label>
                                </div>
                                <?php if (isset($categories['donation']['donation_section']['donation_qr_code'])): ?>
                                <div class="file-preview">
                                    <img src="../<?php echo htmlspecialchars($categories['donation']['donation_section']['donation_qr_code']['setting_value']); ?>" 
                                         alt="Current QR Code" 
                                         id="donationQrCodePreview">
                                    <button type="button" class="remove-preview" onclick="removePreview('donationQrCode')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Donation Amounts -->
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-money-bill-wave"></i> Amount Settings
                                </label>
                                
                                <input type="number" 
                                       name="setting_donation_default_amount" 
                                       class="form-input"
                                       value="<?php echo htmlspecialchars(getSetting('donation', 'donation_section', 'donation_default_amount', '1000')); ?>"
                                       min="0" step="100">
                                <p class="setting-description">Default amount (NPR)</p>
                                
                                <input type="number" 
                                       name="setting_donation_min_amount" 
                                       class="form-input"
                                       style="margin-top: 0.75rem;"
                                       value="<?php echo htmlspecialchars(getSetting('donation', 'donation_section', 'donation_min_amount', '100')); ?>"
                                       min="0" step="10">
                                <p class="setting-description">Minimum amount (NPR)</p>
                            </div>

                            <!-- Bank Information -->
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-university"></i> Bank Information
                                </label>
                                <div class="language-toggle">
                                    <button type="button" class="lang-tab active" data-lang="en">English</button>
                                    <button type="button" class="lang-tab" data-lang="np">नेपाली</button>
                                </div>
                                
                                <div class="lang-content active" data-lang="en">
                                    <input type="text" 
                                           name="setting_donation_bank_name_en" 
                                           class="form-input"
                                           value="<?php echo htmlspecialchars(getSetting('donation', 'donation_section', 'donation_bank_name_en', 'Nepal Investment Mega Bank')); ?>"
                                           placeholder="Bank name in English">
                                    
                                    <input type="text" 
                                           name="setting_donation_account_name" 
                                           class="form-input"
                                           style="margin-top: 0.75rem;"
                                           value="<?php echo htmlspecialchars(getSetting('donation', 'donation_section', 'donation_account_name', 'KP Sharma Oli Donation Account')); ?>"
                                           placeholder="Account holder name">
                                </div>
                                
                                <div class="lang-content" data-lang="np">
                                    <input type="text" 
                                           name="setting_donation_bank_name_np" 
                                           class="form-input"
                                           value="<?php echo htmlspecialchars(getSetting('donation', 'donation_section', 'donation_bank_name_np', 'नेपाल ईन्भेस्टमेन्ट मेगा बैंक')); ?>"
                                           placeholder="नेपालीमा बैंकको नाम">
                                </div>
                                
                                <input type="text" 
                                       name="setting_donation_account_number" 
                                       class="form-input"
                                       style="margin-top: 0.75rem;"
                                       value="<?php echo htmlspecialchars(getSetting('donation', 'donation_section', 'donation_account_number', '1234567890123456')); ?>"
                                       placeholder="Bank account number">
                            </div>

                            <!-- Section Save Button -->
                            <div class="section-save">
                                <button type="submit" class="save-btn-section">
                                    <i class="fas fa-save"></i> Save Donation Settings
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Secretary Information -->
                    <div class="settings-group" id="secretaryGroup">
                        <div class="group-header">
                            <h3><i class="fas fa-user-tie"></i> Secretary Information</h3>
                            <p class="group-description">Configure secretary/personal assistant information</p>
                        </div>
                        
                        <div class="settings-grid">
                            <!-- Secretary Photo -->
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-portrait"></i> Secretary Photo
                                </label>
                                <p class="setting-description">Photo of secretary/personal assistant</p>
                                <div class="file-input">
                                    <input type="file" name="setting_secretary_photo" id="secretaryPhoto" accept="image/*">
                                    <label for="secretaryPhoto" class="file-input-label">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <div class="file-input-text">
                                            <h4>Upload Photo</h4>
                                            <p>JPG, PNG or WebP</p>
                                        </div>
                                    </label>
                                </div>
                                <?php if (isset($categories['contact']['secretary']['secretary_photo'])): ?>
                                <div class="file-preview">
                                    <img src="../<?php echo htmlspecialchars($categories['contact']['secretary']['secretary_photo']['setting_value']); ?>" 
                                         alt="Current Secretary Photo" 
                                         id="secretaryPhotoPreview">
                                    <button type="button" class="remove-preview" onclick="removePreview('secretaryPhoto')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Secretary Details -->
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-user"></i> Secretary Details
                                </label>
                                <div class="language-toggle">
                                    <button type="button" class="lang-tab active" data-lang="en">English</button>
                                    <button type="button" class="lang-tab" data-lang="np">नेपाली</button>
                                </div>
                                
                                <div class="lang-content active" data-lang="en">
                                    <input type="text" 
                                           name="setting_secretary_name_en" 
                                           class="form-input"
                                           value="<?php echo htmlspecialchars(getSetting('contact', 'secretary', 'secretary_name_en', 'Rajesh Sharma')); ?>"
                                           placeholder="Secretary name in English">
                                    
                                    <input type="text" 
                                           name="setting_secretary_title_en" 
                                           class="form-input"
                                           style="margin-top: 0.75rem;"
                                           value="<?php echo htmlspecialchars(getSetting('contact', 'secretary', 'secretary_title_en', 'Personal Secretary')); ?>"
                                           placeholder="Secretary title in English">
                                </div>
                                
                                <div class="lang-content" data-lang="np">
                                    <input type="text" 
                                           name="setting_secretary_name_np" 
                                           class="form-input"
                                           value="<?php echo htmlspecialchars(getSetting('contact', 'secretary', 'secretary_name_np', 'राजेश शर्मा')); ?>"
                                           placeholder="नेपालीमा सचिवको नाम">
                                    
                                    <input type="text" 
                                           name="setting_secretary_title_np" 
                                           class="form-input"
                                           style="margin-top: 0.75rem;"
                                           value="<?php echo htmlspecialchars(getSetting('contact', 'secretary', 'secretary_title_np', 'व्यक्तिगत सचिव')); ?>"
                                           placeholder="नेपालीमा सचिवको उपाधि">
                                </div>
                                
                                <input type="text" 
                                       name="setting_secretary_phone" 
                                       class="form-input"
                                       style="margin-top: 0.75rem;"
                                       value="<?php echo htmlspecialchars(getSetting('contact', 'secretary', 'secretary_phone', '+977-9864499368')); ?>"
                                       placeholder="Secretary phone number">
                                
                                <input type="email" 
                                       name="setting_secretary_email" 
                                       class="form-input"
                                       style="margin-top: 0.75rem;"
                                       value="<?php echo htmlspecialchars(getSetting('contact', 'secretary', 'secretary_email', 'kpolisecretary@opmcm.gov.np')); ?>"
                                       placeholder="Secretary email address">
                            </div>

                            <!-- Section Save Button -->
                            <div class="section-save">
                                <button type="submit" class="save-btn-section">
                                    <i class="fas fa-save"></i> Save Secretary Settings
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="settings-group" id="socialGroup">
                        <div class="group-header">
                            <h3><i class="fas fa-share-alt"></i> Social Media Links</h3>
                            <p class="group-description">Configure social media profile links</p>
                        </div>
                        
                        <div class="settings-grid">
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fab fa-facebook"></i> Facebook URL
                                </label>
                                <input type="url" 
                                       name="setting_social_facebook" 
                                       class="form-input"
                                       value="<?php echo htmlspecialchars(getSetting('footer', 'social_media', 'social_facebook', 'https://www.facebook.com/UMLprezKPSharmaOli/')); ?>"
                                       placeholder="https://facebook.com/username">
                                
                                <label class="setting-label" style="margin-top: 1rem;">
                                    <i class="fab fa-twitter"></i> Twitter/X URL
                                </label>
                                <input type="url" 
                                       name="setting_social_twitter" 
                                       class="form-input"
                                       value="<?php echo htmlspecialchars(getSetting('footer', 'social_media', 'social_twitter', 'https://x.com/kpsharmaoli')); ?>"
                                       placeholder="https://x.com/username">
                                
                                <label class="setting-label" style="margin-top: 1rem;">
                                    <i class="fab fa-instagram"></i> Instagram URL
                                </label>
                                <input type="url" 
                                       name="setting_social_instagram" 
                                       class="form-input"
                                       value="<?php echo htmlspecialchars(getSetting('footer', 'social_media', 'social_instagram', 'https://www.instagram.com/kpsharmaoli/')); ?>"
                                       placeholder="https://instagram.com/username">
                                
                                <label class="setting-label" style="margin-top: 1rem;">
                                    <i class="fab fa-youtube"></i> YouTube URL
                                </label>
                                <input type="url" 
                                       name="setting_social_youtube" 
                                       class="form-input"
                                       value="<?php echo htmlspecialchars(getSetting('footer', 'social_media', 'social_youtube', 'https://www.youtube.com/@kpsharmaoli')); ?>"
                                       placeholder="https://youtube.com/@username">
                            </div>

                            <!-- Section Save Button -->
                            <div class="section-save">
                                <button type="submit" class="save-btn-section">
                                    <i class="fas fa-save"></i> Save Social Media Settings
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Simple More Videos Link Section -->
                    <div class="settings-group" id="moreVideosGroup">
                        <div class="group-header">
                            <h3><i class="fas fa-link"></i> More Videos Link</h3>
                            <p class="group-description">Configure the URL for the "More Videos" button</p>
                        </div>
                        
                        <div class="settings-grid">
                            <!-- Button Texts -->
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-mouse-pointer"></i> Button Text
                                </label>
                                <div class="language-toggle">
                                    <button type="button" class="lang-tab active" data-lang="en">English</button>
                                    <button type="button" class="lang-tab" data-lang="np">नेपाली</button>
                                </div>
                                
                                <div class="lang-content active" data-lang="en">
                                    <input type="text" 
                                        name="setting_more_videos_button_text_en" 
                                        class="form-input"
                                        value="<?php echo htmlspecialchars(getSetting('videos', 'more_videos', 'more_videos_button_text_en', 'More Video Links')); ?>"
                                        placeholder="Button text in English">
                                </div>
                                
                                <div class="lang-content" data-lang="np">
                                    <input type="text" 
                                        name="setting_more_videos_button_text_np" 
                                        class="form-input"
                                        value="<?php echo htmlspecialchars(getSetting('videos', 'more_videos', 'more_videos_button_text_np', 'थप भिडियो लिङ्कहरू')); ?>"
                                        placeholder="नेपालीमा बटन पाठ">
                                </div>
                            </div>

                            <!-- Single URL Input -->
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-link"></i> More Videos Link URL
                                </label>
                                <p class="setting-description">Enter the URL where the "More Videos" button should go</p>
                                <input type="url" 
                                    name="setting_more_videos_link" 
                                    class="form-input"
                                    value="<?php echo htmlspecialchars(getSetting('videos', 'more_videos', 'more_videos_link', 'https://www.youtube.com/results?search_query=kp+sharma+oli+speeches')); ?>"
                                    placeholder="https://example.com/videos"
                                    style="width: 100%; padding: 12px; font-size: 14px;">
                                <p class="setting-description" style="margin-top: 8px;">
                                    <i class="fas fa-info-circle"></i> This can be a YouTube search URL, playlist URL, or any external video page
                                </p>
                            </div>

                            <!-- Section Save Button -->
                            <div class="section-save">
                                <button type="submit" class="save-btn-section">
                                    <i class="fas fa-save"></i> Save More Videos Link
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- General Settings -->
                    <div class="settings-group" id="generalGroup">
                        <div class="group-header">
                            <h3><i class="fas fa-globe"></i> General Site Settings</h3>
                            <p class="group-description">Configure general website information</p>
                        </div>
                        
                        <div class="settings-grid">
                            <div class="setting-item">
                                <label class="setting-label">
                                    <i class="fas fa-heading"></i> Site Title
                                </label>
                                <input type="text" 
                                       name="setting_site_title" 
                                       class="form-input"
                                       value="<?php echo htmlspecialchars(getSetting('general', 'site_info', 'site_title', 'KP Sharma Oli - Official Portfolio | NetaKnown')); ?>"
                                       placeholder="Website title">
                                
                                <label class="setting-label" style="margin-top: 1rem;">
                                    <i class="fas fa-align-left"></i> Site Description
                                </label>
                                <textarea name="setting_site_description" 
                                          class="form-textarea"
                                          placeholder="Meta description for SEO"
                                          rows="3"><?php echo htmlspecialchars(getSetting('general', 'site_info', 'site_description', 'Official portfolio of KP Sharma Oli - Former Prime Minister of Nepal, Chairman of CPN (UML)')); ?></textarea>
                                
                                <label class="setting-label" style="margin-top: 1rem;">
                                    <i class="fas fa-tags"></i> Site Keywords
                                </label>
                                <input type="text" 
                                       name="setting_site_keywords" 
                                       class="form-input"
                                       value="<?php echo htmlspecialchars(getSetting('general', 'site_info', 'site_keywords', 'KP Sharma Oli, Nepal Prime Minister, CPN UML, Nepali politician, political portfolio')); ?>"
                                       placeholder="Comma-separated keywords">
                                
                                <label class="setting-label" style="margin-top: 1rem;">
                                    <i class="fas fa-user"></i> Site Author
                                </label>
                                <input type="text" 
                                       name="setting_site_author" 
                                       class="form-input"
                                       value="<?php echo htmlspecialchars(getSetting('general', 'site_info', 'site_author', 'NetaKnown')); ?>"
                                       placeholder="Website author">
                            </div>

                            <!-- Section Save Button -->
                            <div class="section-save">
                                <button type="submit" class="save-btn-section">
                                    <i class="fas fa-save"></i> Save General Settings
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="save-section">
                        <button type="submit" class="save-btn" id="saveBtn">
                            <i class="fas fa-save"></i> Save All Settings
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <!-- Floating Action Button -->
    <button class="fab" id="scrollTopBtn">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Navigation functionality
            const navTabs = document.querySelectorAll('.nav-tab');
            const settingsGroups = document.querySelectorAll('.settings-group');
            const scrollProgress = document.getElementById('scrollProgress');
            const scrollTopBtn = document.getElementById('scrollTopBtn');
            const saveBtn = document.getElementById('saveBtn');
            const loadingOverlay = document.getElementById('loadingOverlay');
            
            // Initialize navigation
            function initNavigation() {
                navTabs.forEach(tab => {
                    tab.addEventListener('click', function() {
                        const tabId = this.getAttribute('data-tab');
                        
                        // Update active tab
                        navTabs.forEach(t => t.classList.remove('active'));
                        this.classList.add('active');
                        
                        // Show corresponding group
                        settingsGroups.forEach(group => {
                            group.classList.remove('active');
                            if (group.id === tabId + 'Group') {
                                group.classList.add('active');
                                
                                // Smooth scroll to group
                                const headerOffset = 88;
                                const elementPosition = group.offsetTop;
                                const offsetPosition = elementPosition - headerOffset;
                                
                                window.scrollTo({
                                    top: offsetPosition,
                                    behavior: 'smooth'
                                });
                            }
                        });
                        
                        // Update URL hash
                        window.location.hash = tabId;
                    });
                });
                
                // Handle URL hash on load
                if (window.location.hash) {
                    const hash = window.location.hash.substring(1);
                    const tab = document.querySelector(`.nav-tab[data-tab="${hash}"]`);
                    if (tab) {
                        setTimeout(() => tab.click(), 100);
                    }
                }
                
                // Scroll spy for navigation
                window.addEventListener('scroll', function() {
                    const scrollPosition = window.scrollY + 100;
                    
                    settingsGroups.forEach(group => {
                        const groupTop = group.offsetTop;
                        const groupHeight = group.offsetHeight;
                        
                        if (scrollPosition >= groupTop && scrollPosition < groupTop + groupHeight) {
                            const groupId = group.id.replace('Group', '');
                            const tab = document.querySelector(`.nav-tab[data-tab="${groupId}"]`);
                            if (tab && !tab.classList.contains('active')) {
                                navTabs.forEach(t => t.classList.remove('active'));
                                tab.classList.add('active');
                            }
                        }
                    });
                    
                    // Update scroll progress
                    const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
                    const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                    const scrolled = (winScroll / height) * 100;
                    scrollProgress.style.transform = `scaleX(${scrolled / 100})`;
                    
                    // Show/hide scroll to top button
                    if (winScroll > 300) {
                        scrollTopBtn.style.opacity = '1';
                        scrollTopBtn.style.transform = 'scale(1)';
                    } else {
                        scrollTopBtn.style.opacity = '0';
                        scrollTopBtn.style.transform = 'scale(0)';
                    }
                });
            }
            
            // Scroll to top functionality
            scrollTopBtn.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
            
            // Language toggle functionality
            const langTabs = document.querySelectorAll('.lang-tab');
            langTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const lang = this.getAttribute('data-lang');
                    const parent = this.closest('.setting-item') || this.closest('.settings-group');
                    
                    // Update active language tab
                    parent.querySelectorAll('.lang-tab').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show corresponding content
                    parent.querySelectorAll('.lang-content').forEach(content => {
                        content.classList.remove('active');
                        if (content.getAttribute('data-lang') === lang) {
                            content.classList.add('active');
                        }
                    });
                });
            });
            
            // File upload preview with drag and drop
            const fileInputs = document.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => {
                const label = input.nextElementSibling;
                
                // Add drag and drop events
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    label.addEventListener(eventName, preventDefaults, false);
                });
                
                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                
                ['dragenter', 'dragover'].forEach(eventName => {
                    label.addEventListener(eventName, highlight, false);
                });
                
                ['dragleave', 'drop'].forEach(eventName => {
                    label.addEventListener(eventName, unhighlight, false);
                });
                
                function highlight() {
                    label.style.borderColor = 'var(--primary)';
                    label.style.transform = 'translateY(-2px)';
                    label.style.boxShadow = 'var(--shadow-md)';
                }
                
                function unhighlight() {
                    label.style.borderColor = '';
                    label.style.transform = '';
                    label.style.boxShadow = '';
                }
                
                label.addEventListener('drop', handleDrop, false);
                
                function handleDrop(e) {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    
                    if (files.length > 0) {
                        input.files = files;
                        handleFileSelect(input);
                    }
                }
                
                input.addEventListener('change', function() {
                    handleFileSelect(this);
                });
                
                function handleFileSelect(inputElement) {
                    const file = inputElement.files[0];
                    if (file) {
                        // Validate file type
                        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                        if (!validTypes.includes(file.type)) {
                            alert('Please select a valid image file (JPG, PNG, GIF, or WebP)');
                            inputElement.value = '';
                            return;
                        }
                        
                        // Validate file size (5MB max)
                        if (file.size > 5 * 1024 * 1024) {
                            alert('File size must be less than 5MB');
                            inputElement.value = '';
                            return;
                        }
                        
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const previewId = inputElement.id + 'Preview';
                            let previewContainer = inputElement.closest('.setting-item').querySelector('.file-preview');
                            
                            if (!previewContainer) {
                                previewContainer = document.createElement('div');
                                previewContainer.className = 'file-preview';
                                inputElement.closest('.setting-item').appendChild(previewContainer);
                            }
                            
                            previewContainer.innerHTML = `
                                <img src="${e.target.result}" alt="Preview" id="${previewId}">
                                <button type="button" class="remove-preview" onclick="removePreview('${inputElement.id}')">
                                    <i class="fas fa-times"></i>
                                </button>
                            `;
                        };
                        reader.readAsDataURL(file);
                    }
                }
            });
            
            // Remove preview function
            window.removePreview = function(inputId) {
                const input = document.getElementById(inputId);
                const previewId = inputId + 'Preview';
                const previewImg = document.getElementById(previewId);
                
                if (input) {
                    input.value = '';
                }
                
                if (previewImg && previewImg.parentElement) {
                    previewImg.parentElement.remove();
                }
            };
            
            // Update stats preview
            window.updateStats = function() {
                const years = document.querySelector('input[name="setting_hero_years_in_politics"]').value;
                const terms = document.querySelector('input[name="setting_hero_terms_as_pm"]').value;
                const rating = document.querySelector('input[name="setting_hero_approval_rating"]').value;
                
                document.getElementById('previewYears').textContent = years || '42';
                document.getElementById('previewTerms').textContent = terms || '4';
                document.getElementById('previewRating').textContent = (rating || '75') + '%';
            };
            
            // Form submission - SIMPLIFIED to prevent browser draft warnings
            const form = document.getElementById('settingsForm');
            form.addEventListener('submit', function(e) {
                // Show confirmation dialog
                if (!confirm('Are you sure you want to save all changes?')) {
                    e.preventDefault();
                    return;
                }
                
                // Show loading overlay
                loadingOverlay.classList.add('active');
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                
                // Let the form submit normally - PHP will handle the redirect
                // This prevents browser draft warnings
            });
            
            // Initialize all functionality
            initNavigation();
        });
    </script>
</body>
</html>