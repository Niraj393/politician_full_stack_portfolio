<?php
// backend/admin/videos.php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.php';

$message = '';
$videos = [];
$edit_mode = false;
$edit_video = null;

// Handle form submission for new video
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['edit_id'])) {
    $title_en = trim($_POST['title_en'] ?? '');
    $title_np = trim($_POST['title_np'] ?? '');
    $description_en = trim($_POST['description_en'] ?? '');
    $description_np = trim($_POST['description_np'] ?? '');
    $youtube_url = trim($_POST['youtube_url'] ?? '');
    $video_url = trim($_POST['video_url'] ?? '');
    $category = $_POST['category'] ?? 'speeches';
    
    // Extract YouTube ID if URL provided
    $youtube_id = '';
    if (!empty($youtube_url)) {
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $youtube_url, $matches)) {
            $youtube_id = $matches[1];
        } else {
            $message = '<i class="fas fa-exclamation-triangle"></i> Invalid YouTube URL format.';
        }
    }
    
    // Handle thumbnail upload
    $thumbnail_url = '';
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['thumbnail'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($file['type'], $allowedTypes) && $file['size'] <= 10 * 1024 * 1024) {
            $uploadDir = '../uploads/videos/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $filename = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.\-_]/', '', basename($file['name']));
            $filepath = $uploadDir . $filename;
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $thumbnail_url = $filename;
            }
        }
    }
    
    if (empty($title_en) || (empty($video_url) && empty($youtube_id))) {
        $message = '<i class="fas fa-exclamation-triangle"></i> Title and at least one video source (URL or YouTube) are required.';
    } else {
        try {
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO videos (title, title_np, description, description_np, video_url, youtube_url, thumbnail_url, category, uploaded_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$title_en, $title_np, $description_en, $description_np, $video_url, $youtube_id, $thumbnail_url, $category]);
            
            $message = '<i class="fas fa-check-circle"></i> Video added successfully!';
        } catch (PDOException $e) {
            $message = '<i class="fas fa-exclamation-triangle"></i> Error adding video: ' . $e->getMessage();
        }
    }
}

// Handle edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id = (int)$_POST['edit_id'];
    $title_en = trim($_POST['title_en'] ?? '');
    $title_np = trim($_POST['title_np'] ?? '');
    $description_en = trim($_POST['description_en'] ?? '');
    $description_np = trim($_POST['description_np'] ?? '');
    $youtube_url = trim($_POST['youtube_url'] ?? '');
    $video_url = trim($_POST['video_url'] ?? '');
    $category = $_POST['category'] ?? 'speeches';
    
    // Extract YouTube ID if URL provided
    $youtube_id = '';
    if (!empty($youtube_url)) {
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $youtube_url, $matches)) {
            $youtube_id = $matches[1];
        } else {
            $message = '<i class="fas fa-exclamation-triangle"></i> Invalid YouTube URL format.';
        }
    }
    
    try {
        // Handle new thumbnail upload
        $new_thumbnail_url = null;
        if (isset($_FILES['new_thumbnail']) && $_FILES['new_thumbnail']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['new_thumbnail'];
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            
            if (in_array($file['type'], $allowedTypes) && $file['size'] <= 10 * 1024 * 1024) {
                // Get old thumbnail to delete it
                $stmt = $conn->prepare("SELECT thumbnail_url FROM videos WHERE id = ?");
                $stmt->execute([$id]);
                $oldVideo = $stmt->fetch();
                
                // Delete old thumbnail file
                if ($oldVideo && $oldVideo['thumbnail_url'] && file_exists('../uploads/videos/' . $oldVideo['thumbnail_url'])) {
                    unlink('../uploads/videos/' . $oldVideo['thumbnail_url']);
                }
                
                // Upload new thumbnail
                $uploadDir = '../uploads/videos/';
                $filename = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.\-_]/', '', basename($file['name']));
                $filepath = $uploadDir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $new_thumbnail_url = $filename;
                }
            }
        }
        
        // Update video details
        if ($new_thumbnail_url !== null) {
            $stmt = $conn->prepare("UPDATE videos SET title = ?, title_np = ?, description = ?, description_np = ?, video_url = ?, youtube_url = ?, thumbnail_url = ?, category = ? WHERE id = ?");
            $stmt->execute([$title_en, $title_np, $description_en, $description_np, $video_url, $youtube_id, $new_thumbnail_url, $category, $id]);
        } else {
            $stmt = $conn->prepare("UPDATE videos SET title = ?, title_np = ?, description = ?, description_np = ?, video_url = ?, youtube_url = ?, category = ? WHERE id = ?");
            $stmt->execute([$title_en, $title_np, $description_en, $description_np, $video_url, $youtube_id, $category, $id]);
        }
        
        $message = '<i class="fas fa-check-circle"></i> Video updated successfully!';
        $edit_mode = false;
        $edit_video = null;
    } catch (PDOException $e) {
        $message = '<i class="fas fa-exclamation-triangle"></i> Error updating video: ' . $e->getMessage();
    }
}

// Handle delete with POST method for security
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    
    try {
        // Get video details to delete thumbnail
        $stmt = $conn->prepare("SELECT thumbnail_url FROM videos WHERE id = ?");
        $stmt->execute([$id]);
        $video = $stmt->fetch();
        
        // Delete thumbnail file if exists
        if ($video && $video['thumbnail_url'] && file_exists('../uploads/videos/' . $video['thumbnail_url'])) {
            unlink('../uploads/videos/' . $video['thumbnail_url']);
        }
        
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM videos WHERE id = ?");
        $stmt->execute([$id]);
        $message = '<i class="fas fa-check-circle"></i> Video deleted successfully.';
    } catch (PDOException $e) {
        $message = '<i class="fas fa-exclamation-triangle"></i> Error deleting video: ' . $e->getMessage();
    }
}

// Handle edit request
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM videos WHERE id = ?");
    $stmt->execute([$id]);
    $edit_video = $stmt->fetch();
    
    if ($edit_video) {
        $edit_mode = true;
    } else {
        $message = '<i class="fas fa-exclamation-triangle"></i> Video not found.';
    }
}

// Cancel edit mode
if (isset($_GET['cancel_edit'])) {
    $edit_mode = false;
    $edit_video = null;
}

// Fetch videos
$stmt = $conn->query("SELECT * FROM videos ORDER BY uploaded_at DESC");
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Management - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Modern CSS Reset and Variables - Video Theme */
        :root {
            --primary: #8b5cf6;
            --primary-dark: #7c3aed;
            --primary-light: #ede9fe;
            --secondary: #10b981;
            --secondary-dark: #059669;
            --warning: #f59e0b;
            --warning-dark: #d97706;
            --danger: #ef4444;
            --danger-dark: #dc2626;
            --info: #3b82f6;
            --info-light: #dbeafe;
            --dark: #1e293b;
            --light: #f8fafc;
            --surface: #ffffff;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
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
            background: linear-gradient(90deg, var(--primary), var(--danger));
            border-radius: var(--radius-full);
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--danger) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-header h1 i {
            font-size: 1.8rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--danger) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Form Container - Modern */
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
            background: linear-gradient(90deg, var(--primary), var(--danger));
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

        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--gray-200);
        }

        .form-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-header h2 i {
            color: var(--primary);
        }

        .form-mode {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            color: white;
            border-radius: var(--radius-full);
            font-size: 0.875rem;
            font-weight: 600;
        }

        /* Form Elements */
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

        .form-input:hover, .form-textarea:hover, .form-select:hover, .file-input-wrapper:hover {
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
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning) 0%, var(--warning-dark) 100%);
        }

        .btn-warning:hover {
            box-shadow: 0 10px 25px rgba(245, 158, 11, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%);
        }

        .btn-success:hover {
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
        }

        .btn-youtube {
            background: linear-gradient(135deg, #ff0000 0%, #cc0000 100%);
        }

        .btn-youtube:hover {
            box-shadow: 0 10px 25px rgba(255, 0, 0, 0.3);
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
            color: var(--secondary-dark);
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
            color: var(--danger-dark);
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

        /* Videos Grid - Modern */
        .videos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.75rem;
            margin-top: 2rem;
        }

        .video-item {
            background: white;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all var(--transition);
            border: 1px solid var(--gray-200);
            position: relative;
        }

        .video-item:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-light);
        }

        .video-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--danger));
            z-index: 2;
        }

        .video-thumbnail {
            position: relative;
            width: 100%;
            height: 200px;
            overflow: hidden;
            background: #000;
        }

        .video-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .video-item:hover .video-thumbnail img {
            transform: scale(1.1);
        }

        .video-play-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
            background: rgba(255, 0, 0, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all var(--transition);
            z-index: 3;
        }

        .video-play-btn:hover {
            background: #cc0000;
            transform: translate(-50%, -50%) scale(1.1);
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

        .video-info {
            padding: 1.5rem;
        }

        .video-info h3 {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.75rem;
            line-height: 1.4;
        }

        .video-description {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 1rem;
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .video-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-200);
        }

        .video-date {
            font-size: 0.75rem;
            color: var(--gray-500);
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .video-actions {
            display: flex;
            gap: 0.5rem;
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

        /* Video Preview */
        .video-preview {
            margin-top: 1rem;
            display: none;
        }

        .video-preview img {
            max-width: 300px;
            max-height: 200px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
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

        /* Category Color Variations - Videos */
        .category-speeches {
            background: linear-gradient(135deg, #ff0000 0%, #cc0000 100%);
        }

        .category-interviews {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .category-campaigns {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }

        .category-press {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        /* Table Container - Modern */
        .table-container {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: var(--radius-xl);
            padding: 2.5rem;
            box-shadow: var(--glass-shadow);
            border: 1px solid var(--glass-border);
            animation: formFadeIn 0.8s ease-out;
            position: relative;
            overflow: hidden;
        }

        .table-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--danger), var(--primary));
            border-radius: var(--radius-xl) var(--radius-xl) 0 0;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .table-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .table-header h2 i {
            color: var(--danger);
        }

        .count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.25rem 0.75rem;
            background: linear-gradient(135deg, var(--danger), var(--warning));
            color: white;
            border-radius: var(--radius-full);
            font-size: 0.875rem;
            font-weight: 600;
            margin-left: 0.75rem;
        }

        /* Modern Table Styling */
        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.9375rem;
        }

        .modern-table thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .modern-table th {
            background: linear-gradient(135deg, var(--gray-100) 0%, var(--gray-50) 100%);
            color: var(--gray-700);
            font-weight: 600;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1.25rem 1.5rem;
            border-bottom: 2px solid var(--gray-200);
            white-space: nowrap;
            position: relative;
        }

        .modern-table th::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 1px;
            height: 20px;
            background: var(--gray-300);
        }

        .modern-table th:last-child::after {
            display: none;
        }

        .modern-table tbody tr {
            background: white;
            border-bottom: 1px solid var(--gray-100);
            transition: all var(--transition);
        }

        .modern-table tbody tr:hover {
            background: var(--gray-50);
            transform: translateX(4px);
            box-shadow: var(--shadow-sm);
        }

        .modern-table td {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-100);
            vertical-align: middle;
        }

        .modern-table tr:last-child td {
            border-bottom: none;
        }

        /* Video Specific Table Styles */
        .thumbnail-cell {
            text-align: center;
            min-width: 100px;
        }

        .thumbnail-img {
            display: inline-block;
            width: 80px;
            height: 60px;
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .thumbnail-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .title-cell {
            min-width: 200px;
        }

        .title-en {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .title-np {
            font-size: 0.875rem;
            color: var(--gray-600);
            font-style: italic;
        }

        .source-cell {
            min-width: 150px;
        }

        .source-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.375rem 0.75rem;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            gap: 0.375rem;
        }

        .source-youtube {
            background: linear-gradient(135deg, #ff0000 0%, #cc0000 100%);
            color: white;
        }

        .source-local {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
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
            background: linear-gradient(90deg, var(--warning), var(--primary));
            border-radius: var(--radius-xl) var(--radius-xl) 0 0;
        }

        .current-thumbnail {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .current-thumbnail img {
            max-width: 200px;
            max-height: 150px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
        }

        .current-thumbnail p {
            font-size: 0.875rem;
            color: var(--gray-500);
            margin-top: 0.5rem;
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

        .view-video-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .view-video {
            position: relative;
            width: 100%;
            max-height: 400px;
            border-radius: var(--radius-md);
            overflow: hidden;
            background: #000;
        }

        .view-video iframe {
            width: 100%;
            height: 400px;
            border: none;
        }

        .view-video video {
            width: 100%;
            max-height: 400px;
            border-radius: var(--radius-md);
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

        /* Responsive Design */
        @media (max-width: 1200px) {
            .main-content {
                padding: 2rem;
            }
            .videos-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            .modern-table {
                display: block;
                overflow-x: auto;
            }
        }

        @media (max-width: 1024px) {
            .main-content {
                margin-left: 240px;
                padding: 1.75rem;
            }
            .form-row {
                grid-template-columns: 1fr;
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
            .form-container, .table-container {
                padding: 1.5rem;
            }
            .form-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            .videos-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            .btn {
                width: 100%;
                justify-content: center;
            }
            .action-buttons {
                flex-direction: column;
            }
            .modern-table th,
            .modern-table td {
                padding: 1rem;
            }
        }

        @media (max-width: 640px) {
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            .videos-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1><i class="fas fa-video"></i> Video Management</h1>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> <span>Back to Dashboard</span>
                </a>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo strpos($message, 'Failed') !== false || strpos($message, 'Invalid') !== false || strpos($message, 'Error') !== false || strpos($message, 'required') !== false ? 'error' : ''; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if ($edit_mode && $edit_video): ?>
                <!-- Edit Form -->
                <div class="edit-form-container">
                    <div class="form-header">
                        <h2>
                            <i class="fas fa-edit"></i>
                            Edit Video
                        </h2>
                        <a href="?cancel_edit" class="btn btn-secondary">
                            <i class="fas fa-times"></i> <span>Cancel Edit</span>
                        </a>
                    </div>

                    <?php if ($edit_video['thumbnail_url']): ?>
                        <div class="current-thumbnail">
                            <img src="../uploads/videos/<?php echo htmlspecialchars($edit_video['thumbnail_url']); ?>" 
                                 alt="Current Thumbnail">
                            <p>Current Thumbnail</p>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" id="editForm">
                        <input type="hidden" name="edit_id" value="<?php echo $edit_video['id']; ?>">
                        
                        <div class="form-section">
                            <h3><i class="fas fa-info-circle"></i> Video Details</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-heading"></i> Title (English)
                                    </label>
                                    <input type="text" 
                                           name="title_en" 
                                           class="form-input" 
                                           value="<?php echo htmlspecialchars($edit_video['title']); ?>" 
                                           placeholder="Enter title in English" 
                                           required
                                           maxlength="200">
                                    <span class="char-count" data-for="title_en"></span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-heading"></i> Title (Nepali)
                                    </label>
                                    <input type="text" 
                                           name="title_np" 
                                           class="form-input" 
                                           value="<?php echo htmlspecialchars($edit_video['title_np'] ?? ''); ?>"
                                           placeholder="Enter title in Nepali (optional)"
                                           maxlength="200">
                                    <span class="char-count" data-for="title_np"></span>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fab fa-youtube"></i> YouTube URL
                                    </label>
                                    <input type="url" 
                                           name="youtube_url" 
                                           class="form-input" 
                                           value="<?php echo $edit_video['youtube_url'] ? 'https://youtube.com/watch?v=' . htmlspecialchars($edit_video['youtube_url']) : ''; ?>"
                                           placeholder="https://youtube.com/watch?v=..."
                                           pattern="https?://(www\.)?(youtube\.com|youtu\.be)/.+">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-video"></i> Local Video URL
                                    </label>
                                    <input type="text" 
                                           name="video_url" 
                                           class="form-input" 
                                           value="<?php echo htmlspecialchars($edit_video['video_url'] ?? ''); ?>"
                                           placeholder="videos/filename.mp4">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-folder"></i> Category
                                </label>
                                <select name="category" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <option value="speeches" <?php echo $edit_video['category'] == 'speeches' ? 'selected' : ''; ?>>🎤 Speeches</option>
                                    <option value="interviews" <?php echo $edit_video['category'] == 'interviews' ? 'selected' : ''; ?>>🎙️ Interviews</option>
                                    <option value="campaigns" <?php echo $edit_video['category'] == 'campaigns' ? 'selected' : ''; ?>>🗳️ Campaigns</option>
                                    <option value="press" <?php echo $edit_video['category'] == 'press' ? 'selected' : ''; ?>>📰 Press Conferences</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-image"></i> Update Thumbnail (Optional)
                                </label>
                                <div class="file-input-wrapper">
                                    <input type="file" name="new_thumbnail" id="newThumbnailInput" accept="image/*">
                                    <div class="file-input-content">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <div class="file-input-text">
                                            <h4>Click to update thumbnail</h4>
                                            <p>Leave empty to keep current thumbnail</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="video-preview">
                                    <img id="newThumbnailPreview" src="" alt="New thumbnail preview">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-align-left"></i> Description (English)
                                    </label>
                                    <textarea name="description_en" 
                                              class="form-textarea" 
                                              placeholder="Enter description in English"
                                              maxlength="500"><?php echo htmlspecialchars($edit_video['description'] ?? ''); ?></textarea>
                                    <span class="char-count" data-for="description_en"></span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-align-left"></i> Description (Nepali)
                                    </label>
                                    <textarea name="description_np" 
                                              class="form-textarea" 
                                              placeholder="Enter description in Nepali (optional)"
                                              maxlength="500"><?php echo htmlspecialchars($edit_video['description_np'] ?? ''); ?></textarea>
                                    <span class="char-count" data-for="description_np"></span>
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save"></i> <span>Update Video</span>
                            </button>
                            <a href="?cancel_edit" class="btn btn-secondary">
                                <i class="fas fa-times"></i> <span>Cancel</span>
                            </a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <!-- Add New Video Form -->
                <div class="form-container">
                    <h2><i class="fas fa-plus-circle"></i> Add New Video</h2>
                    <form method="POST" enctype="multipart/form-data" id="addForm">
                        <div class="form-section">
                            <h3><i class="fas fa-info-circle"></i> Video Details</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-heading"></i> Title (English)
                                    </label>
                                    <input type="text" 
                                           name="title_en" 
                                           class="form-input" 
                                           placeholder="Enter title in English" 
                                           required
                                           maxlength="200">
                                    <span class="char-count" data-for="title_en"></span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-heading"></i> Title (Nepali)
                                    </label>
                                    <input type="text" 
                                           name="title_np" 
                                           class="form-input" 
                                           placeholder="Enter title in Nepali (optional)"
                                           maxlength="200">
                                    <span class="char-count" data-for="title_np"></span>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fab fa-youtube"></i> YouTube URL
                                    </label>
                                    <input type="url" 
                                           name="youtube_url" 
                                           class="form-input" 
                                           placeholder="https://youtube.com/watch?v=..."
                                           pattern="https?://(www\.)?(youtube\.com|youtu\.be)/.+">
                                    <small style="display: block; margin-top: 0.5rem; color: var(--gray-500);">
                                        Or upload a local video file
                                    </small>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-video"></i> Local Video URL
                                    </label>
                                    <input type="text" 
                                           name="video_url" 
                                           class="form-input" 
                                           placeholder="videos/filename.mp4">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-folder"></i> Category
                                </label>
                                <select name="category" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <option value="speeches">🎤 Speeches</option>
                                    <option value="interviews">🎙️ Interviews</option>
                                    <option value="campaigns">🗳️ Campaigns</option>
                                    <option value="press">📰 Press Conferences</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-image"></i> Thumbnail Image (Optional)
                                </label>
                                <div class="file-input-wrapper">
                                    <input type="file" name="thumbnail" id="thumbnailInput" accept="image/*">
                                    <div class="file-input-content">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <div class="file-input-text">
                                            <h4>Click to upload thumbnail</h4>
                                            <p>PNG, JPG, GIF or WebP (Max. 10MB)</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="video-preview">
                                    <img id="thumbnailPreview" src="" alt="Thumbnail preview">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-align-left"></i> Description (English)
                                    </label>
                                    <textarea name="description_en" 
                                              class="form-textarea" 
                                              placeholder="Enter description in English"
                                              maxlength="500"></textarea>
                                    <span class="char-count" data-for="description_en"></span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-align-left"></i> Description (Nepali)
                                    </label>
                                    <textarea name="description_np" 
                                              class="form-textarea" 
                                              placeholder="Enter description in Nepali (optional)"
                                              maxlength="500"></textarea>
                                    <span class="char-count" data-for="description_np"></span>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn" id="addBtn">
                            <i class="fas fa-plus"></i> <span>Add Video</span>
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Videos Table -->
            <div class="table-container">
                <div class="table-header">
                    <h2>
                        <i class="fas fa-th-large"></i> Videos List
                        <span class="count-badge"><?php echo count($videos); ?></span>
                    </h2>
                    <div style="display: flex; gap: 1rem;">
                        <button onclick="sortTable('date', 'desc')" class="btn btn-sm btn-secondary">
                            <i class="fas fa-sort-numeric-down"></i> Sort by Date
                        </button>
                        <button onclick="filterByCategory('all')" class="btn btn-sm btn-success">
                            <i class="fas fa-filter"></i> Filter by Category
                        </button>
                    </div>
                </div>
                
                <?php if (empty($videos)): ?>
                    <div class="empty-state">
                        <i class="fas fa-video-slash"></i>
                        <h3>No videos added yet</h3>
                        <p>Add your first video using the form above</p>
                        <button onclick="document.querySelector('input[name=\"title_en\"]').focus()" class="btn">
                            <i class="fas fa-plus"></i> Add First Video
                        </button>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="modern-table" id="videosTable">
                            <thead>
                                <tr>
                                    <th class="thumbnail-cell">Thumbnail</th>
                                    <th class="title-cell">Title</th>
                                    <th class="source-cell">Source</th>
                                    <th>Category</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($videos as $video): 
                                    $uploadDate = date('M d, Y', strtotime($video['uploaded_at']));
                                    $thumbnail = $video['thumbnail_url'] 
                                        ? '../uploads/videos/' . $video['thumbnail_url']
                                        : ($video['youtube_url'] ? "https://img.youtube.com/vi/{$video['youtube_url']}/maxresdefault.jpg" : '');
                                    $source = $video['youtube_url'] ? 'youtube' : 'local';
                                ?>
                                    <tr data-id="<?php echo $video['id']; ?>" data-category="<?php echo $video['category']; ?>">
                                        <td class="thumbnail-cell">
                                            <?php if ($thumbnail): ?>
                                                <div class="thumbnail-img">
                                                    <img src="<?php echo htmlspecialchars($thumbnail); ?>" 
                                                         alt="<?php echo htmlspecialchars($video['title']); ?>"
                                                         loading="lazy">
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="title-cell">
                                            <div class="title-en"><?php echo htmlspecialchars($video['title']); ?></div>
                                            <?php if ($video['title_np']): ?>
                                                <div class="title-np"><?php echo htmlspecialchars($video['title_np']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="source-cell">
                                            <span class="source-badge source-<?php echo $source; ?>">
                                                <i class="<?php echo $source === 'youtube' ? 'fab fa-youtube' : 'fas fa-video'; ?>"></i>
                                                <?php echo ucfirst($source); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="category-badge category-<?php echo $video['category']; ?>" style="position: static;">
                                                <?php 
                                                    $categoryNames = [
                                                        'speeches' => 'Speeches',
                                                        'interviews' => 'Interviews', 
                                                        'campaigns' => 'Campaigns',
                                                        'press' => 'Press'
                                                    ];
                                                    echo $categoryNames[$video['category']] ?? ucfirst($video['category']);
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="video-date">
                                                <i class="far fa-calendar"></i> <?php echo $uploadDate; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button type="button" class="btn btn-sm btn-info view-btn" 
                                                        data-title-en="<?php echo htmlspecialchars($video['title']); ?>"
                                                        data-title-np="<?php echo htmlspecialchars($video['title_np'] ?? ''); ?>"
                                                        data-description-en="<?php echo htmlspecialchars($video['description'] ?? ''); ?>"
                                                        data-description-np="<?php echo htmlspecialchars($video['description_np'] ?? ''); ?>"
                                                        data-category="<?php echo htmlspecialchars($video['category']); ?>"
                                                        data-date="<?php echo $uploadDate; ?>"
                                                        data-youtube-url="<?php echo htmlspecialchars($video['youtube_url'] ?? ''); ?>"
                                                        data-video-url="<?php echo htmlspecialchars($video['video_url'] ?? ''); ?>"
                                                        data-thumbnail="<?php echo htmlspecialchars($thumbnail); ?>">
                                                    <i class="fas fa-eye"></i> <span>View</span>
                                                </button>
                                                <a href="?edit=<?php echo $video['id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i> <span>Edit</span>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $video['id']; ?>">
                                                    <i class="fas fa-trash"></i> <span>Delete</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- View Modal -->
    <div class="modal" id="viewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-eye"></i> Video Details</h2>
                <button type="button" class="modal-close" onclick="closeViewModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="view-video-container">
                <div class="view-video" id="videoPlayer">
                    <!-- Video will be loaded here -->
                </div>
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
                    Delete Video
                </h3>
                <p style="color: var(--gray-600);">
                    Are you sure you want to delete this video? This action cannot be undone.
                </p>
            </div>
            
            <div class="delete-actions">
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="delete_id" id="deleteIdInput">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Video
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
            // Thumbnail preview for add form
            const thumbnailInput = document.getElementById('thumbnailInput');
            const thumbnailPreview = document.getElementById('thumbnailPreview');
            const videoPreview = document.querySelector('.video-preview');
            
            if (thumbnailInput) {
                thumbnailInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            thumbnailPreview.src = e.target.result;
                            videoPreview.style.display = 'block';
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            // Thumbnail preview for edit form
            const newThumbnailInput = document.getElementById('newThumbnailInput');
            const newThumbnailPreview = document.getElementById('newThumbnailPreview');
            
            if (newThumbnailInput) {
                newThumbnailInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            newThumbnailPreview.src = e.target.result;
                            document.querySelector('.video-preview').style.display = 'block';
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            // YouTube URL preview
            const youtubeInputs = document.querySelectorAll('input[name="youtube_url"]');
            youtubeInputs.forEach(input => {
                input.addEventListener('input', function() {
                    const url = this.value;
                    if (url.includes('youtube.com') || url.includes('youtu.be')) {
                        const videoId = extractYouTubeId(url);
                        if (videoId) {
                            // Update thumbnail preview with YouTube thumbnail
                            const previewImg = this.closest('form').querySelector('#thumbnailPreview, #newThumbnailPreview');
                            if (previewImg && !thumbnailInput?.files[0]) {
                                previewImg.src = `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg`;
                                const previewContainer = previewImg.closest('.video-preview');
                                if (previewContainer) {
                                    previewContainer.style.display = 'block';
                                }
                            }
                        }
                    }
                });
            });
            
            function extractYouTubeId(url) {
                const regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#&?]*).*/;
                const match = url.match(regExp);
                return (match && match[7].length === 11) ? match[7] : false;
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
            const addForm = document.getElementById('addForm');
            const addBtn = document.getElementById('addBtn');
            const editForm = document.getElementById('editForm');
            
            if (addForm && addBtn) {
                addForm.addEventListener('submit', function() {
                    addBtn.classList.add('loading');
                    addBtn.disabled = true;
                    addBtn.querySelector('span').textContent = 'Adding...';
                });
            }
            
            if (editForm) {
                editForm.addEventListener('submit', function() {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                        submitBtn.querySelector('span').textContent = 'Updating...';
                    }
                });
            }

            // View modal functionality
            const viewButtons = document.querySelectorAll('.view-btn');
            const viewModal = document.getElementById('viewModal');
            const videoPlayer = document.getElementById('videoPlayer');
            const modalTitleEn = document.getElementById('modalTitleEn');
            const modalTitleNp = document.getElementById('modalTitleNp');
            const modalDescriptionEn = document.getElementById('modalDescriptionEn');
            const modalDescriptionNp = document.getElementById('modalDescriptionNp');
            const modalCategory = document.getElementById('modalCategory');
            const modalDate = document.getElementById('modalDate');
            
            const categoryMap = {
                'speeches': 'Speeches',
                'interviews': 'Interviews',
                'campaigns': 'Campaigns',
                'press': 'Press Conferences'
            };
            
            const categoryMapNp = {
                'speeches': 'भाषणहरू',
                'interviews': 'अन्तर्वार्ताहरू',
                'campaigns': 'चुनाव अभियान',
                'press': 'प्रेस कान्फ्रेन्स'
            };
            
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const titleEn = this.getAttribute('data-title-en');
                    const titleNp = this.getAttribute('data-title-np');
                    const descriptionEn = this.getAttribute('data-description-en');
                    const descriptionNp = this.getAttribute('data-description-np');
                    const category = this.getAttribute('data-category');
                    const date = this.getAttribute('data-date');
                    const youtubeUrl = this.getAttribute('data-youtube-url');
                    const videoUrl = this.getAttribute('data-video-url');
                    const thumbnail = this.getAttribute('data-thumbnail');
                    
                    // Set text content
                    modalTitleEn.textContent = titleEn || 'Not specified';
                    modalTitleNp.textContent = titleNp || 'उल्लेख छैन';
                    modalDescriptionEn.textContent = descriptionEn || 'No description provided';
                    modalDescriptionNp.textContent = descriptionNp || 'कुनै विवरण उपलब्ध छैन';
                    modalCategory.textContent = categoryMap[category] || category;
                    modalDate.textContent = date;
                    
                    document.getElementById('modalCategoryNp').textContent = categoryMapNp[category] || category;
                    document.getElementById('modalDateNp').textContent = date;
                    
                    // Load video player
                    videoPlayer.innerHTML = '';
                    if (youtubeUrl) {
                        videoPlayer.innerHTML = `
                            <iframe src="https://www.youtube.com/embed/${youtubeUrl}" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen>
                            </iframe>`;
                    } else if (videoUrl) {
                        videoPlayer.innerHTML = `
                            <video controls>
                                <source src="../${videoUrl}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>`;
                    } else if (thumbnail) {
                        videoPlayer.innerHTML = `<img src="${thumbnail}" alt="Video thumbnail" style="width:100%;height:auto;">`;
                    }
                    
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
            
            // Sort table functionality
            window.sortTable = function(column, direction) {
                const table = document.getElementById('videosTable');
                if (!table) return;
                
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                
                rows.sort((a, b) => {
                    let aValue, bValue;
                    
                    if (column === 'date') {
                        aValue = new Date(a.querySelector('.video-date').textContent.replace('📅 ', ''));
                        bValue = new Date(b.querySelector('.video-date').textContent.replace('📅 ', ''));
                    } else if (column === 'title') {
                        aValue = a.querySelector('.title-en').textContent.toLowerCase();
                        bValue = b.querySelector('.title-en').textContent.toLowerCase();
                    }
                    
                    if (direction === 'asc') {
                        return aValue > bValue ? 1 : -1;
                    } else {
                        return aValue < bValue ? 1 : -1;
                    }
                });
                
                // Clear and re-add rows
                rows.forEach(row => tbody.appendChild(row));
            };
            
            // Filter by category
            window.filterByCategory = function(category) {
                const rows = document.querySelectorAll('#videosTable tbody tr');
                rows.forEach(row => {
                    if (category === 'all' || row.getAttribute('data-category') === category) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            };
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