<?php
// backend/admin/achievements.php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.php';

$message = '';
$achievements = [];
$edit_mode = false;
$edit_achievement = null;

// Handle form submission for add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_achievement' && !isset($_POST['edit_id'])) {
    $title_en = trim($_POST['title_en'] ?? '');
    $title_np = trim($_POST['title_np'] ?? '');
    $description_en = trim($_POST['description_en'] ?? '');
    $description_np = trim($_POST['description_np'] ?? '');
    $icon = $_POST['icon'] ?? 'fas fa-trophy';
    $category = $_POST['category'] ?? 'political';
    $year = $_POST['year'] ?? date('Y');
    $importance = $_POST['importance'] ?? 'medium';
    $display_order = (int)($_POST['display_order'] ?? 0);
    
    if (empty($title_en)) {
        $message = '<i class="fas fa-exclamation-triangle"></i> Title (English) is required.';
    } else {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO achievements (title_en, title_np, description_en, description_np, icon, category, year, importance_level, display_order, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$title_en, $title_np, $description_en, $description_np, $icon, $category, $year, $importance, $display_order]);
        
        if ($stmt->rowCount() > 0) {
            $message = '<i class="fas fa-check-circle"></i> Achievement added successfully!';
        } else {
            $message = '<i class="fas fa-exclamation-triangle"></i> Failed to add achievement.';
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
    $icon = $_POST['icon'] ?? 'fas fa-trophy';
    $category = $_POST['category'] ?? 'political';
    $year = $_POST['year'] ?? date('Y');
    $importance = $_POST['importance'] ?? 'medium';
    $display_order = (int)($_POST['display_order'] ?? 0);
    
    if (empty($title_en)) {
        $message = '<i class="fas fa-exclamation-triangle"></i> Title (English) is required.';
    } else {
        try {
            // Update achievement
            $stmt = $conn->prepare("UPDATE achievements SET title_en = ?, title_np = ?, description_en = ?, description_np = ?, icon = ?, category = ?, year = ?, importance_level = ?, display_order = ? WHERE id = ?");
            $stmt->execute([$title_en, $title_np, $description_en, $description_np, $icon, $category, $year, $importance, $display_order, $id]);
            
            $message = '<i class="fas fa-check-circle"></i> Achievement updated successfully!';
            $edit_mode = false;
            $edit_achievement = null;
        } catch (PDOException $e) {
            $message = '<i class="fas fa-exclamation-triangle"></i> Error updating achievement: ' . $e->getMessage();
        }
    }
}

// Handle edit request
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM achievements WHERE id = ?");
    $stmt->execute([$id]);
    $edit_achievement = $stmt->fetch();
    
    if ($edit_achievement) {
        $edit_mode = true;
    } else {
        $message = '<i class="fas fa-exclamation-triangle"></i> Achievement not found.';
    }
}

// Handle delete with POST method for security
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM achievements WHERE id = ?");
        $stmt->execute([$id]);
        $message = '<i class="fas fa-check-circle"></i> Achievement deleted successfully.';
    } catch (PDOException $e) {
        $message = '<i class="fas fa-exclamation-triangle"></i> Error deleting achievement: ' . $e->getMessage();
    }
}

// Handle update order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_order') {
    if (isset($_POST['order']) && is_array($_POST['order'])) {
        try {
            foreach ($_POST['order'] as $id => $order) {
                $stmt = $conn->prepare("UPDATE achievements SET display_order = ? WHERE id = ?");
                $stmt->execute([(int)$order, (int)$id]);
            }
            $message = '<i class="fas fa-check-circle"></i> Display order updated successfully!';
        } catch (PDOException $e) {
            $message = '<i class="fas fa-exclamation-triangle"></i> Error updating order: ' . $e->getMessage();
        }
    }
}

// Cancel edit mode
if (isset($_GET['cancel_edit'])) {
    $edit_mode = false;
    $edit_achievement = null;
}

// Fetch achievements
$stmt = $conn->query("SELECT * FROM achievements ORDER BY display_order ASC, created_at DESC");
$achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Available Font Awesome icons
$faIcons = [
    'fas fa-trophy', 'fas fa-medal', 'fas fa-award', 'fas fa-star',
    'fas fa-handshake', 'fas fa-globe-asia', 'fas fa-university',
    'fas fa-landmark', 'fas fa-road', 'fas fa-users', 'fas fa-book',
    'fas fa-gavel', 'fas fa-balance-scale', 'fas fa-flag',
    'fas fa-heart', 'fas fa-seedling', 'fas fa-building',
    'fas fa-hand-holding-heart', 'fas fa-chart-line', 'fas fa-briefcase'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achievements Management - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Modern CSS Reset and Variables - Achievements Theme */
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

        .form-input, .form-textarea, .form-select {
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

        /* Achievements Grid - Modern */
        .achievements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.75rem;
            margin-top: 2rem;
        }

        .achievement-item {
            background: white;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all var(--transition);
            border: 1px solid var(--gray-200);
            position: relative;
            padding: 2rem;
            text-align: center;
        }

        .achievement-item:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-light);
        }

        .achievement-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            z-index: 2;
        }

        .achievement-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            position: relative;
        }

        .achievement-icon::after {
            content: '';
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            border-radius: 50%;
            background: inherit;
            opacity: 0.2;
            z-index: -1;
        }

        .achievement-info {
            margin-bottom: 1.5rem;
        }

        .achievement-info h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.75rem;
            line-height: 1.4;
        }

        .achievement-description {
            font-size: 0.875rem;
            color: var(--gray-600);
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .achievement-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-200);
        }

        .achievement-year, .achievement-category, .achievement-importance {
            font-size: 0.75rem;
            color: var(--gray-500);
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .achievement-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin-top: 1rem;
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

        /* Icon Selection */
        .icon-preview {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        }

        .icon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));
            gap: 0.75rem;
            margin-top: 1rem;
            padding: 1rem;
            background: var(--gray-50);
            border-radius: var(--radius-md);
        }

        .icon-option {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border: 2px solid var(--gray-200);
            cursor: pointer;
            transition: all var(--transition);
            font-size: 1.2rem;
            color: var(--gray-600);
        }

        .icon-option:hover, .icon-option.selected {
            border-color: var(--primary);
            background: var(--primary-light);
            color: var(--primary);
            transform: scale(1.1);
        }

        /* Order Input */
        .order-input {
            width: 80px;
            padding: 0.5rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-md);
            font-weight: 600;
            text-align: center;
        }

        /* Category Color Variations - Achievements */
        .category-political { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
        .category-development { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .category-international { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); }
        .category-social { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }

        /* Importance Color Variations */
        .importance-high { color: #ef4444; }
        .importance-medium { color: #f59e0b; }
        .importance-low { color: #10b981; }

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

        /* Responsive Design */
        @media (max-width: 1200px) {
            .main-content {
                padding: 2rem;
            }
            .achievements-grid {
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
            .achievements-grid {
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
            .achievements-grid {
                grid-template-columns: 1fr;
            }
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

        .view-icon-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .view-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            position: relative;
        }

        .view-icon::after {
            content: '';
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            border-radius: 50%;
            background: inherit;
            opacity: 0.2;
            z-index: -1;
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
                <h1><i class="fas fa-trophy"></i> Achievements Management</h1>
                <div style="display: flex; gap: 1rem;">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> <span>Back to Dashboard</span>
                    </a>
                    <?php if (!empty($achievements) && !$edit_mode): ?>
                        <button type="button" id="updateOrderBtn" class="btn btn-secondary">
                            <i class="fas fa-save"></i> <span>Save Order</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo strpos($message, 'Failed') !== false || strpos($message, 'Invalid') !== false || strpos($message, 'error') !== false || strpos($message, 'Error') !== false ? 'error' : ''; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if ($edit_mode && $edit_achievement): ?>
                <!-- Edit Form -->
                <div class="edit-form-container">
                    <div class="page-header" style="border-bottom: none; margin-bottom: 1.5rem;">
                        <h2><i class="fas fa-edit"></i> Edit Achievement</h2>
                        <a href="?cancel_edit" class="btn btn-secondary">
                            <i class="fas fa-times"></i> <span>Cancel Edit</span>
                        </a>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="edit_id" value="<?php echo $edit_achievement['id']; ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-heading"></i> Title (English)
                                </label>
                                <input type="text" name="title_en" class="form-input" 
                                       value="<?php echo htmlspecialchars($edit_achievement['title_en']); ?>" 
                                       placeholder="Enter achievement title in English" 
                                       required maxlength="255">
                                <span class="char-count" data-for="title_en"></span>
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-heading"></i> Title (Nepali)
                                </label>
                                <input type="text" name="title_np" class="form-input" 
                                       value="<?php echo htmlspecialchars($edit_achievement['title_np'] ?? ''); ?>"
                                       placeholder="Enter achievement title in Nepali (optional)"
                                       maxlength="255">
                                <span class="char-count" data-for="title_np"></span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-folder"></i> Category
                                </label>
                                <select name="category" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <option value="political" <?php echo $edit_achievement['category'] == 'political' ? 'selected' : ''; ?>>🏛️ Political</option>
                                    <option value="development" <?php echo $edit_achievement['category'] == 'development' ? 'selected' : ''; ?>>🏗️ Development</option>
                                    <option value="international" <?php echo $edit_achievement['category'] == 'international' ? 'selected' : ''; ?>>🌍 International</option>
                                    <option value="social" <?php echo $edit_achievement['category'] == 'social' ? 'selected' : ''; ?>>🤝 Social</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-calendar"></i> Year
                                </label>
                                <input type="number" name="year" class="form-input" 
                                       min="1900" max="<?php echo date('Y') + 10; ?>"
                                       value="<?php echo $edit_achievement['year']; ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-icons"></i> Icon
                            </label>
                            <input type="hidden" name="icon" id="selectedIcon" value="<?php echo htmlspecialchars($edit_achievement['icon']); ?>" required>
                            <div class="icon-preview">
                                <i id="iconPreview" class="<?php echo htmlspecialchars($edit_achievement['icon']); ?>"></i>
                            </div>
                            <div class="icon-grid">
                                <?php foreach ($faIcons as $icon): ?>
                                    <div class="icon-option <?php echo $icon === $edit_achievement['icon'] ? 'selected' : ''; ?>" 
                                         data-icon="<?php echo $icon; ?>">
                                        <i class="<?php echo $icon; ?>"></i>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-align-left"></i> Description (English)
                                </label>
                                <textarea name="description_en" class="form-textarea" 
                                          placeholder="Enter detailed description in English"
                                          rows="4" maxlength="1000"><?php echo htmlspecialchars($edit_achievement['description_en'] ?? ''); ?></textarea>
                                <span class="char-count" data-for="description_en"></span>
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-align-left"></i> Description (Nepali)
                                </label>
                                <textarea name="description_np" class="form-textarea" 
                                          placeholder="Enter detailed description in Nepali (optional)"
                                          rows="4" maxlength="1000"><?php echo htmlspecialchars($edit_achievement['description_np'] ?? ''); ?></textarea>
                                <span class="char-count" data-for="description_np"></span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-signal"></i> Importance Level
                                </label>
                                <select name="importance" class="form-select" required>
                                    <option value="high" <?php echo $edit_achievement['importance_level'] == 'high' ? 'selected' : ''; ?>>🔴 High</option>
                                    <option value="medium" <?php echo $edit_achievement['importance_level'] == 'medium' ? 'selected' : ''; ?>>🟡 Medium</option>
                                    <option value="low" <?php echo $edit_achievement['importance_level'] == 'low' ? 'selected' : ''; ?>>🟢 Low</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-sort-numeric-down"></i> Display Order
                                </label>
                                <input type="number" name="display_order" class="form-input" 
                                       value="<?php echo $edit_achievement['display_order']; ?>" min="0" max="999" required>
                                <small style="display: block; margin-top: 0.5rem; color: var(--gray-500);">
                                    Lower numbers appear first
                                </small>
                            </div>
                        </div>

                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save"></i> <span>Update Achievement</span>
                            </button>
                            <a href="?cancel_edit" class="btn btn-secondary">
                                <i class="fas fa-times"></i> <span>Cancel</span>
                            </a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <!-- Add Achievement Form -->
                <div class="form-container">
                    <h2><i class="fas fa-plus-circle"></i> Add New Achievement</h2>
                    <form method="POST" id="achievementForm">
                        <input type="hidden" name="action" value="add_achievement">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-heading"></i> Title (English)
                                </label>
                                <input type="text" name="title_en" class="form-input" 
                                       placeholder="Enter achievement title in English" 
                                       required maxlength="255">
                                <span class="char-count" data-for="title_en"></span>
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-heading"></i> Title (Nepali)
                                </label>
                                <input type="text" name="title_np" class="form-input" 
                                       placeholder="Enter achievement title in Nepali (optional)"
                                       maxlength="255">
                                <span class="char-count" data-for="title_np"></span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-folder"></i> Category
                                </label>
                                <select name="category" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <option value="political">🏛️ Political</option>
                                    <option value="development">🏗️ Development</option>
                                    <option value="international">🌍 International</option>
                                    <option value="social">🤝 Social</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-calendar"></i> Year
                                </label>
                                <input type="number" name="year" class="form-input" 
                                       min="1900" max="<?php echo date('Y') + 10; ?>"
                                       value="<?php echo date('Y'); ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-icons"></i> Icon
                            </label>
                            <input type="hidden" name="icon" id="selectedIcon" value="fas fa-trophy" required>
                            <div class="icon-preview">
                                <i id="iconPreview" class="fas fa-trophy"></i>
                            </div>
                            <div class="icon-grid">
                                <?php foreach ($faIcons as $icon): ?>
                                    <div class="icon-option <?php echo $icon === 'fas fa-trophy' ? 'selected' : ''; ?>" 
                                         data-icon="<?php echo $icon; ?>">
                                        <i class="<?php echo $icon; ?>"></i>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-align-left"></i> Description (English)
                                </label>
                                <textarea name="description_en" class="form-textarea" 
                                          placeholder="Enter detailed description in English"
                                          rows="4" maxlength="1000"></textarea>
                                <span class="char-count" data-for="description_en"></span>
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-align-left"></i> Description (Nepali)
                                </label>
                                <textarea name="description_np" class="form-textarea" 
                                          placeholder="Enter detailed description in Nepali (optional)"
                                          rows="4" maxlength="1000"></textarea>
                                <span class="char-count" data-for="description_np"></span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-signal"></i> Importance Level
                                </label>
                                <select name="importance" class="form-select" required>
                                    <option value="high">🔴 High</option>
                                    <option value="medium" selected>🟡 Medium</option>
                                    <option value="low">🟢 Low</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-sort-numeric-down"></i> Display Order
                                </label>
                                <input type="number" name="display_order" class="form-input" 
                                       value="0" min="0" max="999" required>
                                <small style="display: block; margin-top: 0.5rem; color: var(--gray-500);">
                                    Lower numbers appear first
                                </small>
                            </div>
                        </div>

                        <button type="submit" class="btn" id="submitBtn">
                            <i class="fas fa-plus-circle"></i> <span>Add Achievement</span>
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Achievements Grid -->
            <div class="form-container">
                <h2><i class="fas fa-th-large"></i> Achievements List <span class="badge"><?php echo count($achievements); ?></span></h2>
                
                <?php if (empty($achievements)): ?>
                    <div class="empty-state">
                        <i class="fas fa-trophy"></i>
                        <h3>No achievements added yet</h3>
                        <p>Add your first achievement using the form above</p>
                        <a href="#achievementForm" class="btn">
                            <i class="fas fa-plus"></i> Add Achievement
                        </a>
                    </div>
                <?php else: ?>
                    <form method="POST" id="orderForm">
                        <input type="hidden" name="action" value="update_order">
                        <div class="achievements-grid" id="achievementsList">
                            <?php foreach ($achievements as $achievement): 
                                $createdDate = date('M d, Y', strtotime($achievement['created_at']));
                                $categoryClass = 'category-' . $achievement['category'];
                                $importanceClass = 'importance-' . $achievement['importance_level'];
                            ?>
                                <div class="achievement-item" data-id="<?php echo $achievement['id']; ?>">
                                    <div class="achievement-icon <?php echo $categoryClass; ?>">
                                        <i class="<?php echo htmlspecialchars($achievement['icon']); ?>"></i>
                                    </div>
                                    <div class="achievement-info">
                                        <h3><?php echo htmlspecialchars($achievement['title_en']); ?></h3>
                                        <?php if ($achievement['title_np']): ?>
                                            <p style="font-size: 0.875rem; color: var(--gray-600); margin-bottom: 0.75rem;">
                                                <?php echo htmlspecialchars($achievement['title_np']); ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <?php if ($achievement['description_en']): ?>
                                            <div class="achievement-description">
                                                <?php echo htmlspecialchars($achievement['description_en']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="achievement-meta">
                                        <span class="achievement-year">
                                            <i class="fas fa-calendar"></i> <?php echo $achievement['year']; ?>
                                        </span>
                                        <span class="achievement-category">
                                            <i class="fas fa-folder"></i> 
                                            <?php 
                                                $categoryNames = [
                                                    'political' => 'Political',
                                                    'development' => 'Development',
                                                    'international' => 'International',
                                                    'social' => 'Social'
                                                ];
                                                echo $categoryNames[$achievement['category']] ?? ucfirst($achievement['category']);
                                            ?>
                                        </span>
                                        <span class="achievement-importance <?php echo $importanceClass; ?>">
                                            <i class="fas fa-signal"></i> 
                                            <?php echo ucfirst($achievement['importance_level']); ?>
                                        </span>
                                    </div>
                                    <div class="achievement-actions">
                                        <button type="button" class="btn btn-info btn-sm view-btn" 
                                                data-title="<?php echo htmlspecialchars($achievement['title_en']); ?>"
                                                data-title-np="<?php echo htmlspecialchars($achievement['title_np'] ?? ''); ?>"
                                                data-description="<?php echo htmlspecialchars($achievement['description_en'] ?? ''); ?>"
                                                data-description-np="<?php echo htmlspecialchars($achievement['description_np'] ?? ''); ?>"
                                                data-icon="<?php echo htmlspecialchars($achievement['icon']); ?>"
                                                data-category="<?php echo htmlspecialchars($achievement['category']); ?>"
                                                data-year="<?php echo $achievement['year']; ?>"
                                                data-importance="<?php echo htmlspecialchars($achievement['importance_level']); ?>"
                                                data-date="<?php echo $createdDate; ?>"
                                                data-order="<?php echo $achievement['display_order']; ?>">
                                            <i class="fas fa-eye"></i> <span>View</span>
                                        </button>
                                        <a href="?edit=<?php echo $achievement['id']; ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> <span>Edit</span>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $achievement['id']; ?>">
                                            <i class="fas fa-trash"></i> <span>Delete</span>
                                        </button>
                                    </div>
                                    <div style="margin-top: 1rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                                        <label style="font-size: 0.75rem; color: var(--gray-500);">Order:</label>
                                        <input type="number" name="order[<?php echo $achievement['id']; ?>]" 
                                               class="order-input" value="<?php echo $achievement['display_order']; ?>"
                                               min="0" max="999">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- View Modal -->
    <div class="modal" id="viewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-eye"></i> Achievement Details</h2>
                <button type="button" class="modal-close" onclick="closeViewModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="view-icon-container">
                <div class="view-icon" id="modalIcon"></div>
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
                        <div class="detail-label"><i class="fas fa-calendar"></i> Year</div>
                        <div class="detail-value" id="modalYear"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-signal"></i> Importance Level</div>
                        <div class="detail-value" id="modalImportance"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-sort-numeric-down"></i> Display Order</div>
                        <div class="detail-value" id="modalOrder"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="far fa-calendar"></i> Created Date</div>
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
                        <div class="detail-label"><i class="fas fa-calendar"></i> वर्ष</div>
                        <div class="detail-value" id="modalYearNp"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-signal"></i> महत्त्व स्तर</div>
                        <div class="detail-value" id="modalImportanceNp"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-sort-numeric-down"></i> प्रदर्शन क्रम</div>
                        <div class="detail-value" id="modalOrderNp"></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="far fa-calendar"></i> सिर्जना मिति</div>
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
                    Delete Achievement
                </h3>
                <p style="color: var(--gray-600);">
                    Are you sure you want to delete this achievement? This action cannot be undone.
                </p>
            </div>
            
            <div class="delete-actions">
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="delete_id" id="deleteIdInput">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Achievement
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
            // Icon selection
            const iconOptions = document.querySelectorAll('.icon-option');
            const selectedIconInput = document.getElementById('selectedIcon');
            const iconPreview = document.getElementById('iconPreview');
            
            if (iconOptions.length > 0) {
                iconOptions.forEach(option => {
                    option.addEventListener('click', function() {
                        const icon = this.getAttribute('data-icon');
                        
                        // Update selected icon
                        iconOptions.forEach(opt => opt.classList.remove('selected'));
                        this.classList.add('selected');
                        
                        // Update form values
                        selectedIconInput.value = icon;
                        iconPreview.className = icon;
                    });
                });
            }
            
            // Update order button
            const updateOrderBtn = document.getElementById('updateOrderBtn');
            if (updateOrderBtn) {
                updateOrderBtn.addEventListener('click', function() {
                    document.getElementById('orderForm').submit();
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
            const achievementForm = document.getElementById('achievementForm');
            if (achievementForm) {
                achievementForm.addEventListener('submit', function() {
                    const submitBtn = document.getElementById('submitBtn');
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                    submitBtn.querySelector('span').textContent = 'Adding...';
                });
            }
            
            // View modal functionality
            const viewButtons = document.querySelectorAll('.view-btn');
            const viewModal = document.getElementById('viewModal');
            const modalIcon = document.getElementById('modalIcon');
            const modalTitleEn = document.getElementById('modalTitleEn');
            const modalTitleNp = document.getElementById('modalTitleNp');
            const modalDescriptionEn = document.getElementById('modalDescriptionEn');
            const modalDescriptionNp = document.getElementById('modalDescriptionNp');
            const modalCategory = document.getElementById('modalCategory');
            const modalYear = document.getElementById('modalYear');
            const modalImportance = document.getElementById('modalImportance');
            const modalOrder = document.getElementById('modalOrder');
            const modalDate = document.getElementById('modalDate');
            
            const categoryMap = {
                'political': 'Political',
                'development': 'Development',
                'international': 'International',
                'social': 'Social'
            };
            
            const categoryMapNp = {
                'political': 'राजनीतिक',
                'development': 'विकास',
                'international': 'अन्तर्राष्ट्रिय',
                'social': 'सामाजिक'
            };
            
            const importanceMap = {
                'high': 'High',
                'medium': 'Medium',
                'low': 'Low'
            };
            
            const importanceMapNp = {
                'high': 'उच्च',
                'medium': 'मध्यम',
                'low': 'न्यून'
            };
            
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const titleEn = this.getAttribute('data-title');
                    const titleNp = this.getAttribute('data-title-np');
                    const descriptionEn = this.getAttribute('data-description');
                    const descriptionNp = this.getAttribute('data-description-np');
                    const icon = this.getAttribute('data-icon');
                    const category = this.getAttribute('data-category');
                    const year = this.getAttribute('data-year');
                    const importance = this.getAttribute('data-importance');
                    const date = this.getAttribute('data-date');
                    const order = this.getAttribute('data-order');
                    
                    // Set icon with category class
                    modalIcon.className = 'view-icon ' + icon;
                    modalIcon.classList.add('category-' + category);
                    
                    // Set text content
                    modalTitleEn.textContent = titleEn || 'Not specified';
                    modalTitleNp.textContent = titleNp || 'उल्लेख छैन';
                    modalDescriptionEn.textContent = descriptionEn || 'No description provided';
                    modalDescriptionNp.textContent = descriptionNp || 'कुनै विवरण उपलब्ध छैन';
                    modalCategory.textContent = categoryMap[category] || category;
                    modalYear.textContent = year;
                    modalImportance.textContent = importanceMap[importance] || importance;
                    modalOrder.textContent = order;
                    modalDate.textContent = date;
                    
                    // Set Nepali content
                    document.getElementById('modalCategoryNp').textContent = categoryMapNp[category] || category;
                    document.getElementById('modalYearNp').textContent = year;
                    document.getElementById('modalImportanceNp').textContent = importanceMapNp[importance] || importance;
                    document.getElementById('modalOrderNp').textContent = order;
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
            
            // Smooth scroll to form
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
    
    <!-- Optional: Include Sortable.js for drag and drop ordering -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <script>
        if (typeof Sortable !== 'undefined' && document.getElementById('achievementsList')) {
            new Sortable(document.getElementById('achievementsList'), {
                animation: 150,
                onEnd: function() {
                    // Update order numbers based on new position
                    const items = document.querySelectorAll('.achievement-item');
                    items.forEach((item, index) => {
                        const orderInput = item.querySelector('.order-input');
                        if (orderInput) {
                            orderInput.value = index + 1;
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>