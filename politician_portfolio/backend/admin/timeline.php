<?php
// backend/admin/timeline.php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.php';

$message = '';
$entries = [];
$edit_mode = false;
$edit_entry = null;

// Handle form submission for new entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['edit_id'])) {
    $year = trim($_POST['year'] ?? '');
    $title_en = trim($_POST['title_en'] ?? '');
    $title_np = trim($_POST['title_np'] ?? '');
    $content_en = trim($_POST['content_en'] ?? '');
    $content_np = trim($_POST['content_np'] ?? '');
    
    if (empty($year) || empty($title_en) || empty($content_en)) {
        $message = '<i class="fas fa-exclamation-triangle"></i> Year, Title (English), and Content (English) are required.';
    } else {
        try {
            // Insert new entry
            $stmt = $conn->prepare("INSERT INTO timeline_entries (year, title_en, title_np, content_en, content_np) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$year, $title_en, $title_np, $content_en, $content_np]);
            $message = '<i class="fas fa-check-circle"></i> Timeline entry added successfully!';
            
            // Clear form
            $_POST = [];
        } catch (PDOException $e) {
            $message = '<i class="fas fa-exclamation-triangle"></i> Error adding timeline entry: ' . $e->getMessage();
        }
    }
}

// Handle edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id = (int)$_POST['edit_id'];
    $year = trim($_POST['year'] ?? '');
    $title_en = trim($_POST['title_en'] ?? '');
    $title_np = trim($_POST['title_np'] ?? '');
    $content_en = trim($_POST['content_en'] ?? '');
    $content_np = trim($_POST['content_np'] ?? '');
    
    if (empty($year) || empty($title_en) || empty($content_en)) {
        $message = '<i class="fas fa-exclamation-triangle"></i> Year, Title (English), and Content (English) are required.';
    } else {
        try {
            // Update entry
            $stmt = $conn->prepare("UPDATE timeline_entries SET year = ?, title_en = ?, title_np = ?, content_en = ?, content_np = ? WHERE id = ?");
            $stmt->execute([$year, $title_en, $title_np, $content_en, $content_np, $id]);
            $message = '<i class="fas fa-check-circle"></i> Timeline entry updated successfully!';
            
            $edit_mode = false;
            $edit_entry = null;
        } catch (PDOException $e) {
            $message = '<i class="fas fa-exclamation-triangle"></i> Error updating timeline entry: ' . $e->getMessage();
        }
    }
}

// Handle delete with POST method for security
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM timeline_entries WHERE id = ?");
        $stmt->execute([$id]);
        $message = '<i class="fas fa-check-circle"></i> Timeline entry deleted successfully.';
    } catch (PDOException $e) {
        $message = '<i class="fas fa-exclamation-triangle"></i> Error deleting timeline entry: ' . $e->getMessage();
    }
}

// Handle edit request
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM timeline_entries WHERE id = ?");
    $stmt->execute([$id]);
    $edit_entry = $stmt->fetch();
    
    if ($edit_entry) {
        $edit_mode = true;
    } else {
        $message = '<i class="fas fa-exclamation-triangle"></i> Timeline entry not found.';
    }
}

// Cancel edit mode
if (isset($_GET['cancel_edit'])) {
    $edit_mode = false;
    $edit_entry = null;
}

// Fetch entries
$stmt = $conn->query("SELECT * FROM timeline_entries ORDER BY year DESC");
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stmt = $conn->query("SELECT COUNT(*) as total, MIN(year) as earliest, MAX(year) as latest FROM timeline_entries");
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline Management - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Modern CSS Reset and Variables - Timeline Theme */
        :root {
            --primary: #8b5cf6;
            --primary-dark: #7c3aed;
            --primary-light: #ede9fe;
            --secondary: #10b981;
            --secondary-dark: #059669;
            --secondary-light: #d1fae5;
            --warning: #f59e0b;
            --warning-dark: #d97706;
            --warning-light: #fef3c7;
            --danger: #ef4444;
            --danger-dark: #dc2626;
            --danger-light: #fee2e2;
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
            background: linear-gradient(90deg, var(--primary), var(--warning));
            border-radius: var(--radius-full);
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--warning) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-header h1 i {
            font-size: 1.8rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--warning) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Stats Cards - Timeline Theme */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--glass-shadow);
            border: 1px solid var(--glass-border);
            transition: all var(--transition);
            position: relative;
            overflow: hidden;
            border-top: 4px solid transparent;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, transparent 100%);
            opacity: 0;
            transition: opacity var(--transition);
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card.total {
            border-top-color: var(--primary);
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.05) 0%, rgba(139, 92, 246, 0.02) 100%);
        }

        .stat-card.earliest {
            border-top-color: var(--info);
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(59, 130, 246, 0.02) 100%);
        }

        .stat-card.latest {
            border-top-color: var(--warning);
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, rgba(245, 158, 11, 0.02) 100%);
        }

        .stat-card.years {
            border-top-color: var(--secondary);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(16, 185, 129, 0.02) 100%);
        }

        .stat-icon {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            width: 48px;
            height: 48px;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            opacity: 0.2;
            z-index: 0;
        }

        .stat-card.total .stat-icon {
            background: var(--primary);
            color: white;
        }

        .stat-card.earliest .stat-icon {
            background: var(--info);
            color: white;
        }

        .stat-card.latest .stat-icon {
            background: var(--warning);
            color: white;
        }

        .stat-card.years .stat-icon {
            background: var(--secondary);
            color: white;
        }

        .stat-content {
            position: relative;
            z-index: 1;
        }

        .stat-number {
            font-size: 2.25rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            line-height: 1;
        }

        .stat-card.total .stat-number {
            color: var(--primary);
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-card.earliest .stat-number {
            color: var(--info);
            background: linear-gradient(135deg, var(--info), #2563eb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-card.latest .stat-number {
            color: var(--warning);
            background: linear-gradient(135deg, var(--warning), var(--warning-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-card.years .stat-number {
            color: var(--secondary);
            background: linear-gradient(135deg, var(--secondary), var(--secondary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--gray-600);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.75rem;
            margin-top: 0.75rem;
            color: var(--gray-500);
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
            background: linear-gradient(90deg, var(--primary), var(--warning));
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

        .form-input, .form-textarea {
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

        .form-input:hover, .form-textarea:hover {
            border-color: var(--primary-light);
            box-shadow: var(--shadow);
        }

        .form-input:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.15);
            background: white;
            transform: translateY(-1px);
        }

        .form-textarea {
            min-height: 140px;
            resize: vertical;
            line-height: 1.7;
        }

        .form-input.year-input {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            text-align: center;
            border: 2px solid var(--primary-light);
            background: var(--primary-light);
        }

        .form-input.year-input:focus {
            background: white;
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

        /* Timeline Table Container - Modern */
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
            background: linear-gradient(90deg, var(--warning), var(--primary));
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
            color: var(--warning);
        }

        .count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.25rem 0.75rem;
            background: linear-gradient(135deg, var(--warning-light), var(--warning));
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

        /* Timeline Specific Table Styles */
        .year-cell {
            text-align: center;
            min-width: 100px;
        }

        .year-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: var(--radius-full);
            font-size: 1.25rem;
            font-weight: 700;
            box-shadow: var(--shadow);
            transition: all var(--transition);
        }

        .modern-table tbody tr:hover .year-badge {
            transform: scale(1.1) rotate(5deg);
            box-shadow: var(--shadow-md);
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

        .content-cell {
            max-width: 300px;
        }

        .content-preview {
            font-size: 0.875rem;
            color: var(--gray-600);
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: linear-gradient(135deg, var(--gray-50) 0%, var(--light) 100%);
            border-radius: var(--radius-lg);
            border: 2px dashed var(--gray-300);
        }

        .empty-state i {
            font-size: 3.5rem;
            color: var(--gray-400);
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: var(--gray-600);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .empty-state p {
            color: var(--gray-500);
            max-width: 400px;
            margin: 0 auto 1.5rem;
            font-size: 0.9375rem;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .main-content {
                padding: 2rem;
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
            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
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
            .stats-cards {
                grid-template-columns: 1fr;
            }
            .form-container, .table-container {
                padding: 1.5rem;
            }
            .form-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            .action-buttons {
                flex-direction: column;
            }
            .btn {
                width: 100%;
                justify-content: center;
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
            background: linear-gradient(90deg, var(--warning), var(--primary));
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

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.75rem;
            margin-bottom: 1.75rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1><i class="fas fa-history"></i> Timeline Management</h1>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> <span>Back to Dashboard</span>
                </a>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo strpos($message, 'Failed') !== false || strpos($message, 'Invalid') !== false || strpos($message, 'Error') !== false || strpos($message, 'required') !== false ? 'error' : ''; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="stats-cards">
                <div class="stat-card total">
                    <div class="stat-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['total'] ?? 0; ?></div>
                        <div class="stat-label">Total Entries</div>
                        <div class="stat-trend">
                            <i class="fas fa-database"></i>
                            <span>All timeline entries</span>
                        </div>
                    </div>
                </div>
                <div class="stat-card earliest">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-minus"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['earliest'] ?? 'N/A'; ?></div>
                        <div class="stat-label">Earliest Year</div>
                        <div class="stat-trend">
                            <i class="fas fa-arrow-down"></i>
                            <span>Starting point</span>
                        </div>
                    </div>
                </div>
                <div class="stat-card latest">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['latest'] ?? 'N/A'; ?></div>
                        <div class="stat-label">Latest Year</div>
                        <div class="stat-trend">
                            <i class="fas fa-arrow-up"></i>
                            <span>Most recent</span>
                        </div>
                    </div>
                </div>
                <div class="stat-card years">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">
                            <?php 
                                if ($stats['earliest'] && $stats['latest']) {
                                    echo intval($stats['latest']) - intval($stats['earliest']);
                                } else {
                                    echo 'N/A';
                                }
                            ?>
                        </div>
                        <div class="stat-label">Year Span</div>
                        <div class="stat-trend">
                            <i class="fas fa-expand-alt"></i>
                            <span>Years covered</span>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($edit_mode && $edit_entry): ?>
                <!-- Edit Form -->
                <div class="edit-form-container">
                    <div class="form-header">
                        <h2>
                            <i class="fas fa-edit"></i>
                            Edit Timeline Entry
                        </h2>
                        <a href="?cancel_edit" class="btn btn-secondary">
                            <i class="fas fa-times"></i> <span>Cancel Edit</span>
                        </a>
                    </div>

                    <form method="POST" id="editForm">
                        <input type="hidden" name="edit_id" value="<?php echo $edit_entry['id']; ?>">
                        
                        <div class="form-section">
                            <h3><i class="fas fa-calendar"></i> Timeline Details</h3>
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-calendar"></i> Year
                                </label>
                                <input type="text" 
                                       name="year" 
                                       class="form-input year-input" 
                                       placeholder="e.g., 1991" 
                                       value="<?php echo htmlspecialchars($edit_entry['year']); ?>"
                                       required
                                       maxlength="4"
                                       pattern="\d{4}"
                                       title="Please enter a valid 4-digit year">
                                <span class="char-count" data-for="year"></span>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3><i class="fas fa-info-circle"></i> Entry Details</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-heading"></i> Title (English)
                                    </label>
                                    <input type="text" 
                                           name="title_en" 
                                           class="form-input" 
                                           value="<?php echo htmlspecialchars($edit_entry['title_en']); ?>" 
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
                                           value="<?php echo htmlspecialchars($edit_entry['title_np'] ?? ''); ?>"
                                           placeholder="Enter title in Nepali (optional)"
                                           maxlength="200">
                                    <span class="char-count" data-for="title_np"></span>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-align-left"></i> Content (English)
                                    </label>
                                    <textarea name="content_en" 
                                              class="form-textarea" 
                                              placeholder="Enter content in English"
                                              required
                                              maxlength="2000"><?php echo htmlspecialchars($edit_entry['content_en']); ?></textarea>
                                    <span class="char-count" data-for="content_en"></span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-align-left"></i> Content (Nepali)
                                    </label>
                                    <textarea name="content_np" 
                                              class="form-textarea" 
                                              placeholder="Enter content in Nepali (optional)"
                                              maxlength="2000"><?php echo htmlspecialchars($edit_entry['content_np'] ?? ''); ?></textarea>
                                    <span class="char-count" data-for="content_np"></span>
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save"></i> <span>Update Entry</span>
                            </button>
                            <a href="?cancel_edit" class="btn btn-secondary">
                                <i class="fas fa-times"></i> <span>Cancel</span>
                            </a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <!-- Add New Entry Form -->
                <div class="form-container">
                    <h2><i class="fas fa-plus-circle"></i> Add New Timeline Entry</h2>
                    <form method="POST" id="addForm">
                        <div class="form-section">
                            <h3><i class="fas fa-calendar"></i> Timeline Details</h3>
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-calendar"></i> Year
                                </label>
                                <input type="text" 
                                       name="year" 
                                       class="form-input year-input" 
                                       placeholder="e.g., 1991" 
                                       value="<?php echo $_POST['year'] ?? ''; ?>"
                                       required
                                       maxlength="4"
                                       pattern="\d{4}"
                                       title="Please enter a valid 4-digit year">
                                <span class="char-count" data-for="year"></span>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3><i class="fas fa-info-circle"></i> Entry Details</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-heading"></i> Title (English)
                                    </label>
                                    <input type="text" 
                                           name="title_en" 
                                           class="form-input" 
                                           value="<?php echo $_POST['title_en'] ?? ''; ?>" 
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
                                           value="<?php echo $_POST['title_np'] ?? ''; ?>"
                                           placeholder="Enter title in Nepali (optional)"
                                           maxlength="200">
                                    <span class="char-count" data-for="title_np"></span>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-align-left"></i> Content (English)
                                    </label>
                                    <textarea name="content_en" 
                                              class="form-textarea" 
                                              placeholder="Enter content in English"
                                              required
                                              maxlength="2000"><?php echo $_POST['content_en'] ?? ''; ?></textarea>
                                    <span class="char-count" data-for="content_en"></span>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-align-left"></i> Content (Nepali)
                                    </label>
                                    <textarea name="content_np" 
                                              class="form-textarea" 
                                              placeholder="Enter content in Nepali (optional)"
                                              maxlength="2000"><?php echo $_POST['content_np'] ?? ''; ?></textarea>
                                    <span class="char-count" data-for="content_np"></span>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn" id="addBtn">
                            <i class="fas fa-plus"></i> <span>Add Timeline Entry</span>
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Existing Entries Table -->
            <div class="table-container">
                <div class="table-header">
                    <h2>
                        <i class="fas fa-list"></i> Timeline Entries
                        <span class="count-badge"><?php echo count($entries); ?></span>
                    </h2>
                    <div style="display: flex; gap: 1rem;">
                        <button onclick="sortTable('year', 'desc')" class="btn btn-sm btn-secondary">
                            <i class="fas fa-sort-numeric-down"></i> Sort by Year
                        </button>
                        <button onclick="exportTimeline()" class="btn btn-sm btn-success">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
                
                <?php if (empty($entries)): ?>
                    <div class="empty-state">
                        <i class="fas fa-history"></i>
                        <h3>No timeline entries yet</h3>
                        <p>Start by adding your first timeline entry using the form above.</p>
                        <button onclick="document.querySelector('input[name=\"year\"]').focus()" class="btn">
                            <i class="fas fa-plus"></i> Add First Entry
                        </button>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="modern-table" id="timelineTable">
                            <thead>
                                <tr>
                                    <th class="year-cell">Year</th>
                                    <th class="title-cell">Title</th>
                                    <th class="content-cell">Content Preview</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($entries as $entry): 
                                    $contentPreview = strlen($entry['content_en']) > 150 ? 
                                        substr($entry['content_en'], 0, 150) . '...' : 
                                        $entry['content_en'];
                                ?>
                                    <tr data-id="<?php echo $entry['id']; ?>">
                                        <td class="year-cell">
                                            <div class="year-badge"><?php echo htmlspecialchars($entry['year']); ?></div>
                                        </td>
                                        <td class="title-cell">
                                            <div class="title-en"><?php echo htmlspecialchars($entry['title_en']); ?></div>
                                            <?php if ($entry['title_np']): ?>
                                                <div class="title-np"><?php echo htmlspecialchars($entry['title_np']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="content-cell">
                                            <div class="content-preview"><?php echo htmlspecialchars($contentPreview); ?></div>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button type="button" class="btn btn-sm btn-info view-btn" 
                                                        data-year="<?php echo htmlspecialchars($entry['year']); ?>"
                                                        data-title-en="<?php echo htmlspecialchars($entry['title_en']); ?>"
                                                        data-title-np="<?php echo htmlspecialchars($entry['title_np'] ?? ''); ?>"
                                                        data-content-en="<?php echo htmlspecialchars($entry['content_en']); ?>"
                                                        data-content-np="<?php echo htmlspecialchars($entry['content_np'] ?? ''); ?>">
                                                    <i class="fas fa-eye"></i> <span>View</span>
                                                </button>
                                                <a href="?edit=<?php echo $entry['id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i> <span>Edit</span>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $entry['id']; ?>">
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
                <h2><i class="fas fa-eye"></i> Timeline Entry Details</h2>
                <button type="button" class="modal-close" onclick="closeViewModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div style="text-align: center; margin-bottom: 1.5rem;">
                <div class="year-badge" id="modalYearBadge" style="margin: 0 auto;"></div>
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
                        <div class="detail-label"><i class="fas fa-align-left"></i> Content</div>
                        <div class="detail-value" id="modalContentEn"></div>
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
                        <div class="detail-value" id="modalContentNp"></div>
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
                    Delete Timeline Entry
                </h3>
                <p style="color: var(--gray-600);">
                    Are you sure you want to delete this timeline entry? This action cannot be undone.
                </p>
            </div>
            
            <div class="delete-actions">
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="delete_id" id="deleteIdInput">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Entry
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
            // Character count
            const textInputs = document.querySelectorAll('.form-input, .form-textarea');
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
                        
                        // Initial count
                        input.dispatchEvent(new Event('input'));
                    }
                }
            });

            // Year validation
            const yearInputs = document.querySelectorAll('input[name="year"]');
            yearInputs.forEach(input => {
                input.addEventListener('input', function() {
                    this.value = this.value.replace(/\D/g, '').slice(0, 4);
                    
                    // Validate year range
                    const year = parseInt(this.value);
                    if (year && (year < 1000 || year > 2100)) {
                        this.setCustomValidity('Year must be between 1000 and 2100');
                    } else {
                        this.setCustomValidity('');
                    }
                });
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
            const modalYearBadge = document.getElementById('modalYearBadge');
            const modalTitleEn = document.getElementById('modalTitleEn');
            const modalTitleNp = document.getElementById('modalTitleNp');
            const modalContentEn = document.getElementById('modalContentEn');
            const modalContentNp = document.getElementById('modalContentNp');
            
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const year = this.getAttribute('data-year');
                    const titleEn = this.getAttribute('data-title-en');
                    const titleNp = this.getAttribute('data-title-np');
                    const contentEn = this.getAttribute('data-content-en');
                    const contentNp = this.getAttribute('data-content-np');
                    
                    modalYearBadge.textContent = year;
                    modalTitleEn.textContent = titleEn || 'Not specified';
                    modalTitleNp.textContent = titleNp || 'उल्लेख छैन';
                    modalContentEn.textContent = contentEn || 'No content provided';
                    modalContentNp.textContent = contentNp || 'कुनै विवरण उपलब्ध छैन';
                    
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
                const table = document.getElementById('timelineTable');
                if (!table) return;
                
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                
                rows.sort((a, b) => {
                    let aValue, bValue;
                    
                    if (column === 'year') {
                        aValue = parseInt(a.querySelector('.year-badge').textContent);
                        bValue = parseInt(b.querySelector('.year-badge').textContent);
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

            // Export functionality
            window.exportTimeline = function() {
                const entries = <?php echo json_encode($entries); ?>;
                const csvContent = "data:text/csv;charset=utf-8," 
                    + "Year,Title (English),Title (Nepali),Content (English),Content (Nepali)\n"
                    + entries.map(e => 
                        `"${e.year}","${e.title_en.replace(/"/g, '""')}","${(e.title_np || '').replace(/"/g, '""')}","${e.content_en.replace(/"/g, '""')}","${(e.content_np || '').replace(/"/g, '""')}"`
                    ).join("\n");
                
                const encodedUri = encodeURI(csvContent);
                const link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", "timeline_entries_" + new Date().toISOString().split('T')[0] + ".csv");
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
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