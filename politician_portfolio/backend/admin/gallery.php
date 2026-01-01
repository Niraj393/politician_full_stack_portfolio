<?php
// backend/admin/gallery.php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.php';

$message = '';
$images = [];
$edit_mode = false;
$edit_image = null;

// Handle form submission for upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image']) && !isset($_POST['edit_id'])) {
    $category = $_POST['category'] ?? '';
    $title_en = trim($_POST['title_en'] ?? '');
    $title_np = trim($_POST['title_np'] ?? '');
    $description_en = trim($_POST['description_en'] ?? '');
    $description_np = trim($_POST['description_np'] ?? '');
    
    if (empty($title_en) || empty($category)) {
        $message = '<i class="fas fa-exclamation-triangle"></i> Title (English) and Category are required.';
    } elseif (!in_array($category, ['speeches', 'meetings', 'public'])) {
        $message = '<i class="fas fa-exclamation-triangle"></i> Invalid category.';
    } else {
        // Handle file upload
        $file = $_FILES['image'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (in_array($file['type'], $allowedTypes) && $file['size'] <= 10 * 1024 * 1024) { // 10MB
                $uploadDir = '../uploads/gallery/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $filename = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.\-_]/', '', basename($file['name']));
                $filepath = $uploadDir . $filename;
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    // Insert into DB
                    $stmt = $conn->prepare("INSERT INTO gallery_images (image_url, category, title, description, title_np, description_np, uploaded_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([$filename, $category, $title_en, $description_en, $title_np, $description_np]);
                    $message = '<i class="fas fa-check-circle"></i> Image uploaded successfully!';
                } else {
                    $message = '<i class="fas fa-exclamation-triangle"></i> Failed to save image.';
                }
            } else {
                $message = '<i class="fas fa-exclamation-triangle"></i> Invalid file type or size. Only JPG, PNG, GIF, WebP up to 10MB allowed.';
            }
        } else {
            $message = '<i class="fas fa-exclamation-triangle"></i> Upload error. Please try again.';
        }
    }
}

// Handle edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id = (int)$_POST['edit_id'];
    $category = $_POST['category'] ?? '';
    $title_en = trim($_POST['title_en'] ?? '');
    $title_np = trim($_POST['title_np'] ?? '');
    $description_en = trim($_POST['description_en'] ?? '');
    $description_np = trim($_POST['description_np'] ?? '');
    
    if (empty($title_en) || empty($category)) {
        $message = '<i class="fas fa-exclamation-triangle"></i> Title (English) and Category are required.';
    } else {
        try {
            // Update image details
            $stmt = $conn->prepare("UPDATE gallery_images SET category = ?, title = ?, description = ?, title_np = ?, description_np = ? WHERE id = ?");
            $stmt->execute([$category, $title_en, $description_en, $title_np, $description_np, $id]);
            
            // Handle new image upload if provided
            if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['new_image'];
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                
                if (in_array($file['type'], $allowedTypes) && $file['size'] <= 10 * 1024 * 1024) {
                    // Get old image to delete it
                    $stmt = $conn->prepare("SELECT image_url FROM gallery_images WHERE id = ?");
                    $stmt->execute([$id]);
                    $oldImage = $stmt->fetch();
                    
                    // Delete old image file
                    if ($oldImage && file_exists('../uploads/gallery/' . $oldImage['image_url'])) {
                        unlink('../uploads/gallery/' . $oldImage['image_url']);
                    }
                    
                    // Upload new image
                    $uploadDir = '../uploads/gallery/';
                    $filename = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.\-_]/', '', basename($file['name']));
                    $filepath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        // Update database with new filename
                        $stmt = $conn->prepare("UPDATE gallery_images SET image_url = ? WHERE id = ?");
                        $stmt->execute([$filename, $id]);
                    }
                }
            }
            
            $message = '<i class="fas fa-check-circle"></i> Image updated successfully!';
            $edit_mode = false;
            $edit_image = null;
        } catch (PDOException $e) {
            $message = '<i class="fas fa-exclamation-triangle"></i> Error updating image: ' . $e->getMessage();
        }
    }
}

// Handle edit request
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM gallery_images WHERE id = ?");
    $stmt->execute([$id]);
    $edit_image = $stmt->fetch();
    
    if ($edit_image) {
        $edit_mode = true;
    } else {
        $message = '<i class="fas fa-exclamation-triangle"></i> Image not found.';
    }
}

// Handle delete with POST method for security
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    $stmt = $conn->prepare("SELECT image_url FROM gallery_images WHERE id = ?");
    $stmt->execute([$id]);
    $image = $stmt->fetch();
    
    if ($image) {
        try {
            $filepath = '../uploads/gallery/' . $image['image_url'];
            if (file_exists($filepath)) {
                unlink($filepath); // Delete file
            }
            $stmt = $conn->prepare("DELETE FROM gallery_images WHERE id = ?");
            $stmt->execute([$id]);
            $message = '<i class="fas fa-check-circle"></i> Image deleted successfully.';
        } catch (PDOException $e) {
            $message = '<i class="fas fa-exclamation-triangle"></i> Error deleting image: ' . $e->getMessage();
        }
    }
}

// Cancel edit mode
if (isset($_GET['cancel_edit'])) {
    $edit_mode = false;
    $edit_image = null;
}

// Fetch images
$stmt = $conn->query("SELECT * FROM gallery_images ORDER BY uploaded_at DESC");
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Management - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Modern CSS Reset and Variables - Gallery Theme */
        :root {
            --primary: #8b5cf6;
            --primary-dark: #7c3aed;
            --primary-light: #ede9fe;
            --secondary: #10b981;
            --accent: #f59e0b;
            --danger: #ef4444;
            --danger-dark: #dc2626;
            --success: #10b981;
            --warning: #f59e0b;
            --warning-dark: #d97706;
            --info: #0ea5e9;
            --info-dark: #0284c7;
            --dark: #1e293b;
            --light: #f8fafc;
            --surface: #ffffff;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-600: #475569;
            --gray-700: #334155;
            
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-full: 9999px;
            
            --shadow-xs: 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
            --shadow-md: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --shadow-lg: 0 25px 50px -12px rgba(0, 0, 0, 0.12);
            --shadow-inner: inset 0 2px 4px 0 rgba(0, 0, 0, 0.05);
            
            --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition: 250ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: 350ms cubic-bezier(0.4, 0, 0.2, 1);
            
            --glass-bg: rgba(255, 255, 255, 0.95);
            --glass-border: rgba(255, 255, 255, 0.2);
            --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #f0f4f8 100%);
            color: var(--dark);
            min-height: 100vh;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Main Content - Enhanced */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 2.5rem;
            max-width: 1400px;
            width: 100%;
            animation: contentSlideIn 0.6s ease-out;
        }

        @keyframes contentSlideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--gray-200);
            position: relative;
        }

        .page-header::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 80px;
            height: 2px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            border-radius: var(--radius-full);
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-header h1 i {
            font-size: 1.8rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Form Container - Modern Card */
        .form-container {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: var(--radius-xl);
            padding: 3rem;
            box-shadow: var(--glass-shadow);
            border: 1px solid var(--glass-border);
            animation: formFadeIn 0.8s ease-out;
            position: relative;
            overflow: hidden;
            margin-bottom: 3rem;
        }

        .form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            border-radius: var(--radius-xl) var(--radius-xl) 0 0;
        }

        @keyframes formFadeIn {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .form-container h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-container h2 i {
            color: var(--primary);
        }

        .form-group {
            margin-bottom: 1.75rem;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: var(--gray-700);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-label i {
            color: var(--primary);
            font-size: 0.9rem;
        }

        .form-input, .form-textarea, .form-select, .file-input-wrapper {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-family: inherit;
            font-size: 0.9375rem;
            transition: all var(--transition);
            background: white;
            box-shadow: var(--shadow-sm);
        }

        .file-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            cursor: pointer;
            border: 2px dashed var(--gray-300);
            background: var(--gray-50);
        }

        .file-input-wrapper:hover {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .file-input-wrapper input[type="file"] {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            left: 0;
            top: 0;
        }

        .file-input-content {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--gray-600);
        }

        .file-input-content i {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .file-input-text h4 {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .file-input-text p {
            font-size: 0.8125rem;
            color: var(--gray-500);
        }

        .form-input:hover, .form-textarea:hover, .form-select:hover {
            border-color: var(--primary-light);
            box-shadow: var(--shadow);
        }

        .form-input:focus, .form-textarea:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.15);
            background: white;
            transform: translateY(-1px);
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
            line-height: 1.7;
        }

        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1.25rem;
            padding-right: 3rem;
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1rem 2.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all var(--transition);
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            text-decoration: none;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            opacity: 0;
            transition: opacity var(--transition);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .btn:hover::before {
            opacity: 1;
        }

        .btn:active {
            transform: translateY(-1px);
        }

        .btn i, .btn span {
            position: relative;
            z-index: 1;
        }

        .btn-secondary {
            background: linear-gradient(135deg, var(--gray-400) 0%, var(--gray-600) 100%);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, var(--danger-dark) 100%);
        }

        .btn-danger:hover {
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.25);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning) 0%, var(--warning-dark) 100%);
        }

        .btn-warning:hover {
            box-shadow: 0 10px 25px rgba(245, 158, 11, 0.25);
        }

        .btn-info {
            background: linear-gradient(135deg, var(--info) 0%, var(--info-dark) 100%);
        }

        .btn-info:hover {
            box-shadow: 0 10px 25px rgba(14, 165, 233, 0.25);
        }

        .btn-sm {
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
            gap: 0.5rem;
        }

        /* Message Styling */
        .message {
            padding: 1.25rem 1.75rem;
            margin-bottom: 2.5rem;
            border-radius: var(--radius-md);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
            color: var(--success);
            border: 2px solid rgba(16, 185, 129, 0.2);
            display: flex;
            align-items: center;
            gap: 1rem;
            font-weight: 500;
            box-shadow: var(--shadow-sm);
            animation: slideDown 0.5s ease-out;
            backdrop-filter: blur(10px);
        }

        .message.error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%);
            color: var(--danger);
            border-color: rgba(239, 68, 68, 0.2);
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

        /* Gallery Grid - Modern */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.75rem;
            margin-top: 2rem;
        }

        .gallery-item {
            background: white;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all var(--transition);
            border: 1px solid var(--gray-200);
            position: relative;
        }

        .gallery-item:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-light);
        }

        .gallery-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            z-index: 2;
        }

        .gallery-image {
            position: relative;
            width: 100%;
            height: 220px;
            overflow: hidden;
        }

        .gallery-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .gallery-item:hover .gallery-image img {
            transform: scale(1.1);
        }

        .category-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 0.375rem 0.75rem;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            box-shadow: var(--shadow-sm);
            z-index: 3;
        }

        .gallery-info {
            padding: 1.5rem;
        }

        .gallery-info h3 {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.75rem;
            line-height: 1.4;
        }

        .gallery-description {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 1rem;
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .gallery-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-200);
        }

        .gallery-date {
            font-size: 0.75rem;
            color: var(--gray-500);
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .gallery-actions {
            display: flex;
            gap: 0.5rem;
        }

        .gallery-actions form {
            display: inline;
        }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 4rem 2rem;
            background: var(--gray-50);
            border-radius: var(--radius-lg);
            border: 2px dashed var(--gray-300);
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--gray-400);
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            color: var(--gray-600);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: var(--gray-500);
            max-width: 400px;
            margin: 0 auto 1.5rem;
        }

        /* Form Layout */
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.75rem;
            margin-bottom: 1.75rem;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .main-content {
                padding: 2rem;
            }
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        @media (max-width: 1024px) {
            .main-content {
                margin-left: 240px;
                padding: 1.75rem;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 70px;
                padding: 1.5rem;
            }
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            .form-container {
                padding: 2rem;
            }
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 640px) {
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
            .gallery-grid {
                grid-template-columns: 1fr;
            }
            .gallery-actions {
                flex-direction: column;
            }
            .gallery-actions .btn {
                width: 100%;
            }
        }

        /* Loading Animation */
        .loading {
            position: relative;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 24px;
            height: 24px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* Character Count */
        .char-count {
            display: block;
            text-align: right;
            font-size: 0.75rem;
            color: var(--gray-400);
            margin-top: 0.5rem;
        }

        /* Category Color Variations */
        .category-speeches {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }

        .category-meetings {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .category-public {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        /* Image Preview on Upload */
        #imagePreview {
            max-width: 200px;
            max-height: 150px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            margin-top: 1rem;
            display: none;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: var(--radius-xl);
            padding: 2rem;
            max-width: 800px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
            animation: slideUp 0.3s ease;
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .modal-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--gray-400);
            cursor: pointer;
            transition: color var(--transition);
        }

        .modal-close:hover {
            color: var(--danger);
        }

        .view-image-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .view-image {
            max-width: 100%;
            max-height: 400px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
        }

        .view-details {
            background: var(--gray-50);
            padding: 1.5rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
        }

        .view-details h3 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .detail-item {
            margin-bottom: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-value {
            color: var(--gray-600);
            line-height: 1.6;
        }

        .language-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .language-tab {
            padding: 0.5rem 1rem;
            background: var(--gray-100);
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-weight: 500;
            transition: all var(--transition);
        }

        .language-tab.active {
            background: var(--primary);
            color: white;
        }

        .language-content {
            display: none;
        }

        .language-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Edit form specific */
        .edit-form-container {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: var(--radius-xl);
            padding: 3rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            border: 1px solid var(--primary-light);
            position: relative;
        }

        .edit-form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent), var(--primary));
            border-radius: var(--radius-xl) var(--radius-xl) 0 0;
        }

        .current-image {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .current-image img {
            max-width: 200px;
            max-height: 150px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
        }

        .current-image p {
            font-size: 0.875rem;
            color: var(--gray-500);
            margin-top: 0.5rem;
        }

        /* Delete confirmation modal */
        .delete-modal .modal-content {
            max-width: 500px;
            text-align: center;
        }

        .delete-icon {
            background: linear-gradient(135deg, var(--danger), var(--warning));
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }

        .delete-icon i {
            color: white;
            font-size: 1.5rem;
        }

        .delete-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 1.5rem;
        }

        /* Form layout improvements */
        .form-section {
            margin-bottom: 2rem;
        }

        .form-section h3 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-section h3 i {
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1><i class="fas fa-images"></i> Gallery Management</h1>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> <span>Back to Dashboard</span>
                </a>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo strpos($message, 'Failed') !== false || strpos($message, 'Invalid') !== false || strpos($message, 'error') !== false || strpos($message, 'Error') !== false ? 'error' : ''; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if ($edit_mode && $edit_image): ?>
                <!-- Edit Form -->
                <div class="edit-form-container">
                    <div class="page-header" style="border-bottom: none; margin-bottom: 1.5rem;">
                        <h2><i class="fas fa-edit"></i> Edit Image</h2>
                        <a href="?cancel_edit" class="btn btn-secondary">
                            <i class="fas fa-times"></i> <span>Cancel Edit</span>
                        </a>
                    </div>

                    <div class="current-image">
                        <img src="../uploads/gallery/<?php echo htmlspecialchars($edit_image['image_url']); ?>" 
                             alt="Current Image">
                        <p>Current Image</p>
                    </div>

                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="edit_id" value="<?php echo $edit_image['id']; ?>">
                        
                        <div class="form-section">
                            <h3><i class="fas fa-camera"></i> Update Image (Optional)</h3>
                            <div class="form-group">
                                <div class="file-input-wrapper">
                                    <input type="file" name="new_image" id="newImageInput" accept="image/*">
                                    <div class="file-input-content">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <div class="file-input-text">
                                            <h4>Click to update image</h4>
                                            <p>Leave empty to keep current image</p>
                                        </div>
                                    </div>
                                </div>
                                <img id="newImagePreview" src="" alt="New image preview" style="display: none;">
                            </div>
                        </div>

                        <div class="form-section">
                            <h3><i class="fas fa-info-circle"></i> Image Details</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-folder"></i> Category
                                    </label>
                                    <select name="category" class="form-select" required>
                                        <option value="">Select Category</option>
                                        <option value="speeches" <?php echo $edit_image['category'] == 'speeches' ? 'selected' : ''; ?>>🎤 Speeches</option>
                                        <option value="meetings" <?php echo $edit_image['category'] == 'meetings' ? 'selected' : ''; ?>>🤝 Meetings</option>
                                        <option value="public" <?php echo $edit_image['category'] == 'public' ? 'selected' : ''; ?>>🏛️ Public Events</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-heading"></i> Title (English)
                                    </label>
                                    <input type="text" name="title_en" class="form-input" 
                                           value="<?php echo htmlspecialchars($edit_image['title']); ?>" 
                                           placeholder="Enter title in English" 
                                           required
                                           maxlength="100">
                                    <span class="char-count" data-for="title_en"></span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-heading"></i> Title (Nepali)
                                    </label>
                                    <input type="text" name="title_np" class="form-input" 
                                           value="<?php echo htmlspecialchars($edit_image['title_np'] ?? ''); ?>"
                                           placeholder="Enter title in Nepali (optional)"
                                           maxlength="100">
                                    <span class="char-count" data-for="title_np"></span>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-align-left"></i> Description (English)
                                    </label>
                                    <textarea name="description_en" class="form-textarea" 
                                              placeholder="Enter description in English"
                                              maxlength="500"><?php echo htmlspecialchars($edit_image['description'] ?? ''); ?></textarea>
                                    <span class="char-count" data-for="description_en"></span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-align-left"></i> Description (Nepali)
                                    </label>
                                    <textarea name="description_np" class="form-textarea" 
                                              placeholder="Enter description in Nepali (optional)"
                                              maxlength="500"><?php echo htmlspecialchars($edit_image['description_np'] ?? ''); ?></textarea>
                                    <span class="char-count" data-for="description_np"></span>
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save"></i> <span>Update Image</span>
                            </button>
                            <a href="?cancel_edit" class="btn btn-secondary">
                                <i class="fas fa-times"></i> <span>Cancel</span>
                            </a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <!-- Upload Form -->
                <div class="form-container">
                    <h2><i class="fas fa-cloud-upload-alt"></i> Upload New Image</h2>
                    <form method="POST" enctype="multipart/form-data" id="uploadForm">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-image"></i> Image File
                            </label>
                            <div class="file-input-wrapper">
                                <input type="file" name="image" id="imageInput" accept="image/*" required>
                                <div class="file-input-content">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <div class="file-input-text">
                                        <h4>Click to upload</h4>
                                        <p>PNG, JPG, GIF or WebP (Max. 10MB)</p>
                                    </div>
                                </div>
                            </div>
                            <img id="imagePreview" src="" alt="Image preview">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-folder"></i> Category
                                </label>
                                <select name="category" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <option value="speeches">🎤 Speeches</option>
                                    <option value="meetings">🤝 Meetings</option>
                                    <option value="public">🏛️ Public Events</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-heading"></i> Title (English)
                                </label>
                                <input type="text" name="title_en" class="form-input" 
                                       placeholder="Enter title in English" 
                                       required
                                       maxlength="100">
                                <span class="char-count" data-for="title_en"></span>
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-heading"></i> Title (Nepali)
                                </label>
                                <input type="text" name="title_np" class="form-input" 
                                       placeholder="Enter title in Nepali (optional)"
                                       maxlength="100">
                                <span class="char-count" data-for="title_np"></span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-align-left"></i> Description (English)
                                </label>
                                <textarea name="description_en" class="form-textarea" 
                                          placeholder="Enter description in English"
                                          maxlength="500"></textarea>
                                <span class="char-count" data-for="description_en"></span>
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-align-left"></i> Description (Nepali)
                                </label>
                                <textarea name="description_np" class="form-textarea" 
                                          placeholder="Enter description in Nepali (optional)"
                                          maxlength="500"></textarea>
                                <span class="char-count" data-for="description_np"></span>
                            </div>
                        </div>

                        <button type="submit" class="btn" id="uploadBtn">
                            <i class="fas fa-cloud-upload-alt"></i> <span>Upload Image</span>
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Existing Images Grid -->
            <div class="form-container">
                <h2><i class="fas fa-th-large"></i> Gallery Images <span class="badge"><?php echo count($images); ?></span></h2>
                
                <?php if (empty($images)): ?>
                    <div class="empty-state">
                        <i class="fas fa-images"></i>
                        <h3>No images uploaded yet</h3>
                        <p>Upload your first image using the form above</p>
                        <a href="#uploadForm" class="btn">
                            <i class="fas fa-plus"></i> Upload Image
                        </a>
                    </div>
                <?php else: ?>
                    <div class="gallery-grid">
                        <?php foreach ($images as $img): 
                            $categoryClass = 'category-' . $img['category'];
                            $uploadDate = date('M d, Y', strtotime($img['uploaded_at']));
                        ?>
                            <div class="gallery-item" id="gallery-item-<?php echo $img['id']; ?>">
                                <div class="gallery-image">
                                    <img src="../uploads/gallery/<?php echo htmlspecialchars($img['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($img['title']); ?>"
                                         loading="lazy">
                                    <span class="category-badge <?php echo $categoryClass; ?>">
                                        <?php 
                                            $categoryNames = [
                                                'speeches' => 'Speeches',
                                                'meetings' => 'Meetings', 
                                                'public' => 'Public'
                                            ];
                                            echo $categoryNames[$img['category']] ?? ucfirst($img['category']);
                                        ?>
                                    </span>
                                </div>
                                <div class="gallery-info">
                                    <h3><?php echo htmlspecialchars($img['title']); ?></h3>
                                    <?php if ($img['title_np']): ?>
                                        <p style="font-size: 0.875rem; color: var(--gray-600); margin-bottom: 0.5rem;">
                                            <?php echo htmlspecialchars($img['title_np']); ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if ($img['description']): ?>
                                        <div class="gallery-description">
                                            <?php echo htmlspecialchars($img['description']); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="gallery-meta">
                                        <span class="gallery-date">
                                            <i class="far fa-calendar"></i> <?php echo $uploadDate; ?>
                                        </span>
                                        <div class="gallery-actions">
                                            <button type="button" class="btn btn-info btn-sm view-btn" 
                                                    data-image="<?php echo htmlspecialchars($img['image_url']); ?>"
                                                    data-title="<?php echo htmlspecialchars($img['title']); ?>"
                                                    data-title-np="<?php echo htmlspecialchars($img['title_np'] ?? ''); ?>"
                                                    data-description="<?php echo htmlspecialchars($img['description'] ?? ''); ?>"
                                                    data-description-np="<?php echo htmlspecialchars($img['description_np'] ?? ''); ?>"
                                                    data-category="<?php echo htmlspecialchars($img['category']); ?>"
                                                    data-date="<?php echo $uploadDate; ?>">
                                                <i class="fas fa-eye"></i> <span>View</span>
                                            </button>
                                            <a href="?edit=<?php echo $img['id']; ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> <span>Edit</span>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $img['id']; ?>">
                                                <i class="fas fa-trash"></i> <span>Delete</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- View Modal -->
    <div class="modal" id="viewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-eye"></i> Image Details</h2>
                <button type="button" class="modal-close" onclick="closeViewModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="view-image-container">
                <img id="modalImage" src="" alt="" class="view-image">
            </div>
            
            <div class="language-tabs">
                <button type="button" class="language-tab active" data-lang="en">English</button>
                <button type="button" class="language-tab" data-lang="np">Nepali</button>
            </div>
            
            <div class="view-details">
                <div id="englishContent" class="language-content active">
                    <h3>English Details</h3>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-heading"></i> Title</div>
                        <div class="detail-value" id="modalTitleEn"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-align-left"></i> Description</div>
                        <div class="detail-value" id="modalDescriptionEn"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-folder"></i> Category</div>
                        <div class="detail-value" id="modalCategory"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="far fa-calendar"></i> Upload Date</div>
                        <div class="detail-value" id="modalDate"></div>
                    </div>
                </div>
                
                <div id="nepaliContent" class="language-content">
                    <h3>नेपाली विवरण</h3>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-heading"></i> शीर्षक</div>
                        <div class="detail-value" id="modalTitleNp"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-align-left"></i> विवरण</div>
                        <div class="detail-value" id="modalDescriptionNp"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-folder"></i> श्रेणी</div>
                        <div class="detail-value" id="modalCategoryNp"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="far fa-calendar"></i> अपलोड मिति</div>
                        <div class="detail-value" id="modalDateNp"></div>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center;">
                <button type="button" class="btn btn-secondary" onclick="closeViewModal()">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal delete-modal" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-exclamation-triangle"></i> Confirm Delete</h2>
                <button type="button" class="modal-close" onclick="closeDeleteModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div style="text-align: center; margin-bottom: 1.5rem;">
                <div class="delete-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--dark); margin-bottom: 0.5rem;">
                    Delete Image
                </h3>
                <p style="color: var(--gray-600);">
                    Are you sure you want to delete this image? This action cannot be undone.
                </p>
            </div>
            
            <div class="delete-actions">
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="delete_id" id="deleteIdInput">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Image
                    </button>
                </form>
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Image preview for upload
            const imageInput = document.getElementById('imageInput');
            const imagePreview = document.getElementById('imagePreview');
            const uploadBtn = document.getElementById('uploadBtn');
            
            if (imageInput) {
                imageInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            imagePreview.src = e.target.result;
                            imagePreview.style.display = 'block';
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            // Image preview for edit
            const newImageInput = document.getElementById('newImageInput');
            const newImagePreview = document.getElementById('newImagePreview');
            
            if (newImageInput) {
                newImageInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            newImagePreview.src = e.target.result;
                            newImagePreview.style.display = 'block';
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            // Character count
            const textInputs = document.querySelectorAll('input[type="text"], textarea');
            textInputs.forEach(input => {
                const maxLength = input.getAttribute('maxlength');
                if (maxLength) {
                    const charCount = input.parentElement.querySelector('.char-count');
                    if (charCount) {
                        input.addEventListener('input', function() {
                            const currentLength = this.value.length;
                            charCount.textContent = `${currentLength}/${maxLength}`;
                            
                            charCount.classList.remove('warning', 'danger');
                            if (currentLength > maxLength * 0.8) {
                                charCount.classList.add('warning');
                            }
                            if (currentLength >= maxLength) {
                                charCount.classList.add('danger');
                            }
                        });
                        
                        input.dispatchEvent(new Event('input'));
                    }
                }
            });
            
            // Form submission loading state
            const uploadForm = document.getElementById('uploadForm');
            if (uploadForm) {
                uploadForm.addEventListener('submit', function() {
                    uploadBtn.classList.add('loading');
                    uploadBtn.disabled = true;
                    uploadBtn.querySelector('span').textContent = 'Uploading...';
                });
            }
            
            // View modal functionality
            const viewButtons = document.querySelectorAll('.view-btn');
            const viewModal = document.getElementById('viewModal');
            const modalImage = document.getElementById('modalImage');
            const modalTitleEn = document.getElementById('modalTitleEn');
            const modalTitleNp = document.getElementById('modalTitleNp');
            const modalDescriptionEn = document.getElementById('modalDescriptionEn');
            const modalDescriptionNp = document.getElementById('modalDescriptionNp');
            const modalCategory = document.getElementById('modalCategory');
            const modalDate = document.getElementById('modalDate');
            
            const categoryMap = {
                'speeches': 'Speeches',
                'meetings': 'Meetings',
                'public': 'Public Events'
            };
            
            const categoryMapNp = {
                'speeches': 'भाषणहरू',
                'meetings': 'बैठकहरू',
                'public': 'सार्वजनिक कार्यक्रम'
            };
            
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const imageUrl = this.getAttribute('data-image');
                    const titleEn = this.getAttribute('data-title');
                    const titleNp = this.getAttribute('data-title-np');
                    const descriptionEn = this.getAttribute('data-description');
                    const descriptionNp = this.getAttribute('data-description-np');
                    const category = this.getAttribute('data-category');
                    const date = this.getAttribute('data-date');
                    
                    modalImage.src = '../uploads/gallery/' + imageUrl;
                    modalTitleEn.textContent = titleEn || 'Not specified';
                    modalTitleNp.textContent = titleNp || 'उल्लेख छैन';
                    modalDescriptionEn.textContent = descriptionEn || 'No description provided';
                    modalDescriptionNp.textContent = descriptionNp || 'कुनै विवरण उपलब्ध छैन';
                    modalCategory.textContent = categoryMap[category] || category;
                    modalDate.textContent = date;
                    
                    document.getElementById('modalCategoryNp').textContent = categoryMapNp[category] || category;
                    document.getElementById('modalDateNp').textContent = date;
                    
                    viewModal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                });
            });
            
            // Language tabs for view modal
            document.querySelectorAll('.language-tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    const lang = this.getAttribute('data-lang');
                    
                    // Update active tab
                    document.querySelectorAll('.language-tab').forEach(t => {
                        t.classList.remove('active');
                    });
                    this.classList.add('active');
                    
                    // Update content visibility
                    document.querySelectorAll('.language-content').forEach(content => {
                        content.classList.remove('active');
                    });
                    document.getElementById(lang === 'en' ? 'englishContent' : 'nepaliContent').classList.add('active');
                });
            });
            
            // Delete modal functionality
            const deleteButtons = document.querySelectorAll('.delete-btn');
            const deleteModal = document.getElementById('deleteModal');
            const deleteForm = document.getElementById('deleteForm');
            const deleteIdInput = document.getElementById('deleteIdInput');
            
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    deleteIdInput.value = id;
                    deleteModal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                });
            });
            
            // Smooth scroll to upload form
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });
        
        function closeViewModal() {
            const viewModal = document.getElementById('viewModal');
            viewModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
        
        function closeDeleteModal() {
            const deleteModal = document.getElementById('deleteModal');
            deleteModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
        
        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeViewModal();
                closeDeleteModal();
            }
        });
        
        // Close modal on outside click
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                closeViewModal();
                closeDeleteModal();
            }
        });
    </script>
</body>
</html>