<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.php';

$message = '';
$activities = [];
$stats = [
    'total' => 0,
    'this_month' => 0,
    'upcoming' => 0,
    'this_year' => 0,
    'ongoing' => 0,
    'completed' => 0,
    'cancelled' => 0
];

try {
    $conn = getPDOConnection();
    
    // Fetch all activities
    $stmt = $conn->query("
        SELECT *, 
               DATE_FORMAT(activity_date, '%Y-%m-%d') as formatted_date,
               TIME_FORMAT(start_time, '%H:%i:%s') as formatted_start_time,
               TIME_FORMAT(end_time, '%H:%i:%s') as formatted_end_time
        FROM activities 
        ORDER BY activity_date DESC, priority DESC
    ");
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get statistics
    $stmt = $conn->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN MONTH(activity_date) = MONTH(CURRENT_DATE()) AND YEAR(activity_date) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END) as this_month,
            SUM(CASE WHEN activity_date >= CURRENT_DATE() AND status IN ('upcoming', 'ongoing') THEN 1 ELSE 0 END) as upcoming,
            SUM(CASE WHEN YEAR(activity_date) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END) as this_year,
            SUM(CASE WHEN status = 'ongoing' THEN 1 ELSE 0 END) as ongoing,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
        FROM activities
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add'])) {
            // Add new activity
            $title_en = $_POST['title_en'] ?? '';
            $title_np = $_POST['title_np'] ?? '';
            $description_en = $_POST['description_en'] ?? '';
            $description_np = $_POST['description_np'] ?? '';
            $category = $_POST['category'] ?? 'general';
            $activity_date = $_POST['activity_date'] ?? '';
            $start_time = $_POST['start_time'] ?? null;
            $end_time = $_POST['end_time'] ?? null;
            $location = $_POST['location'] ?? '';
            $location_map = $_POST['location_map'] ?? '';
            $priority = $_POST['priority'] ?? 1;
            $status = $_POST['status'] ?? 'upcoming';
            $image_url = $_POST['image_url'] ?? '';
            $organizer = $_POST['organizer'] ?? '';
            $attendee_count = $_POST['attendee_count'] ?? 0;
            
            $stmt = $conn->prepare("
                INSERT INTO activities 
                (title_en, title_np, description_en, description_np, category, activity_date, start_time, end_time, 
                 location, location_map, priority, status, image_url, organizer, attendee_count, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $title_en, $title_np, $description_en, $description_np, $category, $activity_date, $start_time, $end_time,
                $location, $location_map, $priority, $status, $image_url, $organizer, $attendee_count
            ]);
            $message = '<i class="fas fa-check-circle"></i> Activity added successfully!';
            
        } elseif (isset($_POST['edit'])) {
            // Edit existing activity
            $id = $_POST['id'];
            $title_en = $_POST['title_en'] ?? '';
            $title_np = $_POST['title_np'] ?? '';
            $description_en = $_POST['description_en'] ?? '';
            $description_np = $_POST['description_np'] ?? '';
            $category = $_POST['category'] ?? 'general';
            $activity_date = $_POST['activity_date'] ?? '';
            $start_time = $_POST['start_time'] ?? null;
            $end_time = $_POST['end_time'] ?? null;
            $location = $_POST['location'] ?? '';
            $location_map = $_POST['location_map'] ?? '';
            $priority = $_POST['priority'] ?? 1;
            $status = $_POST['status'] ?? 'upcoming';
            $image_url = $_POST['image_url'] ?? '';
            $organizer = $_POST['organizer'] ?? '';
            $attendee_count = $_POST['attendee_count'] ?? 0;
            
            $stmt = $conn->prepare("
                UPDATE activities SET 
                title_en = ?, title_np = ?, description_en = ?, description_np = ?, category = ?, 
                activity_date = ?, start_time = ?, end_time = ?, location = ?, location_map = ?, 
                priority = ?, status = ?, image_url = ?, organizer = ?, attendee_count = ? 
                WHERE id = ?
            ");
            $stmt->execute([
                $title_en, $title_np, $description_en, $description_np, $category, $activity_date, 
                $start_time, $end_time, $location, $location_map, $priority, $status, 
                $image_url, $organizer, $attendee_count, $id
            ]);
            $message = '<i class="fas fa-check-circle"></i> Activity updated successfully!';
            
        } elseif (isset($_POST['delete'])) {
            // Delete activity
            $id = $_POST['id'];
            $stmt = $conn->prepare("DELETE FROM activities WHERE id = ?");
            $stmt->execute([$id]);
            $message = '<i class="fas fa-trash-alt"></i> Activity deleted successfully!';
        }
        
        // Refresh data
        $stmt = $conn->query("
            SELECT *, 
                   DATE_FORMAT(activity_date, '%Y-%m-%d') as formatted_date,
                   TIME_FORMAT(start_time, '%H:%i:%s') as formatted_start_time,
                   TIME_FORMAT(end_time, '%H:%i:%s') as formatted_end_time
            FROM activities 
            ORDER BY activity_date DESC, priority DESC
        ");
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Refresh statistics
        $stmt = $conn->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN MONTH(activity_date) = MONTH(CURRENT_DATE()) AND YEAR(activity_date) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END) as this_month,
                SUM(CASE WHEN activity_date >= CURRENT_DATE() AND status IN ('upcoming', 'ongoing') THEN 1 ELSE 0 END) as upcoming,
                SUM(CASE WHEN YEAR(activity_date) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END) as this_year,
                SUM(CASE WHEN status = 'ongoing' THEN 1 ELSE 0 END) as ongoing,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
            FROM activities
        ");
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $message = '<i class="fas fa-exclamation-triangle"></i> Error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Activities - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Modern CSS Reset and Variables - Activities Theme */
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

        /* Stats Cards - Enhanced */
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

        .stat-card.this-month {
            border-top-color: var(--warning);
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, rgba(245, 158, 11, 0.02) 100%);
        }

        .stat-card.upcoming {
            border-top-color: var(--secondary);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(16, 185, 129, 0.02) 100%);
        }

        .stat-card.this-year {
            border-top-color: var(--info);
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(59, 130, 246, 0.02) 100%);
        }

        .stat-card.ongoing {
            border-top-color: #10b981;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(16, 185, 129, 0.02) 100%);
        }

        .stat-card.completed {
            border-top-color: #3b82f6;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(59, 130, 246, 0.02) 100%);
        }

        .stat-card.cancelled {
            border-top-color: #ef4444;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.05) 0%, rgba(239, 68, 68, 0.02) 100%);
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

        .stat-card.this-month .stat-icon {
            background: var(--warning);
            color: white;
        }

        .stat-card.upcoming .stat-icon {
            background: var(--secondary);
            color: white;
        }

        .stat-card.this-year .stat-icon {
            background: var(--info);
            color: white;
        }

        .stat-card.ongoing .stat-icon {
            background: #10b981;
            color: white;
        }

        .stat-card.completed .stat-icon {
            background: #3b82f6;
            color: white;
        }

        .stat-card.cancelled .stat-icon {
            background: #ef4444;
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
        }

        .stat-card.this-month .stat-number {
            color: var(--warning);
        }

        .stat-card.upcoming .stat-number {
            color: var(--secondary);
        }

        .stat-card.this-year .stat-number {
            color: var(--info);
        }

        .stat-card.ongoing .stat-number {
            color: #10b981;
        }

        .stat-card.completed .stat-number {
            color: #3b82f6;
        }

        .stat-card.cancelled .stat-number {
            color: #ef4444;
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

        .form-toggle {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Form Elements */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.75rem;
            margin-bottom: 2rem;
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

        .form-input, .form-textarea, .form-select, .form-date, .form-time {
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

        .form-input:hover, .form-textarea:hover, .form-select:hover, .form-date:hover, .form-time:hover {
            border-color: var(--primary-light);
            box-shadow: var(--shadow);
        }

        .form-input:focus, .form-textarea:focus, .form-select:focus, .form-date:focus, .form-time:focus {
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

        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1.25rem;
            padding-right: 3rem;
        }

        .form-date {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1.25rem;
            padding-right: 3rem;
        }

        .form-time {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'%3E%3C/path%3E%3C/svg%3E");
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
            opacity: 0;
            transition: opacity var(--transition);
        }

        .btn:hover::before {
            opacity: 1;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .btn:active {
            transform: translateY(-1px);
        }

        .btn i, .btn span {
            position: relative;
            z-index: 1;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .btn-primary::before {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
        }

        .btn-secondary {
            background: linear-gradient(135deg, var(--gray-400) 0%, var(--gray-600) 100%);
            color: white;
        }

        .btn-secondary:hover {
            box-shadow: 0 10px 25px rgba(100, 116, 139, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, var(--danger-dark) 100%);
            color: white;
        }

        .btn-danger:hover {
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%);
            color: white;
        }

        .btn-success:hover {
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning) 0%, var(--warning-dark) 100%);
            color: white;
        }

        .btn-warning:hover {
            box-shadow: 0 10px 25px rgba(245, 158, 11, 0.3);
        }

        .btn-sm {
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
            gap: 0.5rem;
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
            background: linear-gradient(90deg, var(--warning), var(--secondary));
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

        /* Filter Controls */
        .filter-controls {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 0.5rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-md);
            background: white;
            color: var(--gray-700);
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition);
        }

        .filter-select:hover {
            border-color: var(--primary);
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.15);
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

        .activity-row {
            background: white;
            border-bottom: 1px solid var(--gray-100);
            transition: all var(--transition);
        }

        .activity-row.upcoming {
            border-left: 3px solid var(--secondary);
        }

        .activity-row.ongoing {
            border-left: 3px solid #10b981;
        }

        .activity-row.completed {
            border-left: 3px solid #3b82f6;
        }

        .activity-row.cancelled {
            border-left: 3px solid var(--danger);
        }

        .activity-row:hover {
            background: var(--gray-50);
            transform: translateX(4px);
            box-shadow: var(--shadow-sm);
        }

        .modern-table td {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-100);
            vertical-align: top;
        }

        .modern-table .activity-row:last-child td {
            border-bottom: none;
        }

        /* Activity Table Cells */
        .title-cell {
            min-width: 200px;
        }

        .activity-title-en {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .activity-title-np {
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

        .date-cell {
            white-space: nowrap;
        }

        .activity-date {
            font-weight: 500;
            color: var(--gray-700);
            font-size: 0.875rem;
        }

        .activity-time {
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-top: 0.25rem;
        }

        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            padding: 0.375rem 1rem;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border: 1px solid transparent;
        }

        .status-upcoming {
            background: linear-gradient(135deg, var(--secondary-light) 0%, var(--secondary-light) 100%);
            color: var(--secondary-dark);
            border-color: rgba(16, 185, 129, 0.2);
        }

        .status-ongoing {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #059669;
            border-color: rgba(16, 185, 129, 0.2);
        }

        .status-completed {
            background: linear-gradient(135deg, var(--info-light) 0%, var(--info-light) 100%);
            color: #1d4ed8;
            border-color: rgba(59, 130, 246, 0.2);
        }

        .status-cancelled {
            background: linear-gradient(135deg, var(--danger-light) 0%, var(--danger-light) 100%);
            color: var(--danger-dark);
            border-color: rgba(239, 68, 68, 0.2);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
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
            .form-grid {
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
            .filter-controls {
                flex-direction: column;
                width: 100%;
            }
            .filter-select {
                width: 100%;
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

        /* Edit Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background: white;
            border-radius: var(--radius-xl);
            padding: 2rem;
            max-width: 800px;
            max-height: 90vh;
            overflow: auto;
            position: relative;
            animation: slideUp 0.3s ease;
            box-shadow: var(--shadow-lg);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gray-200);
        }

        .modal-title {
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
            cursor: pointer;
            color: var(--gray-500);
            width: 40px;
            height: 40px;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition);
        }

        .modal-close:hover {
            background: var(--gray-100);
            color: var(--danger);
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
                <h1><i class="fas fa-calendar-alt"></i> Manage Activities</h1>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : ''; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="stats-cards">
                <div class="stat-card total">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['total'] ?? 0; ?></div>
                        <div class="stat-label">Total Activities</div>
                        <div class="stat-trend">
                            <i class="fas fa-database"></i>
                            <span>All activities recorded</span>
                        </div>
                    </div>
                </div>
                <div class="stat-card this-month">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['this_month'] ?? 0; ?></div>
                        <div class="stat-label">This Month</div>
                        <div class="stat-trend">
                            <i class="fas fa-chart-line"></i>
                            <span>Current month activities</span>
                        </div>
                    </div>
                </div>
                <div class="stat-card upcoming">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['upcoming'] ?? 0; ?></div>
                        <div class="stat-label">Upcoming</div>
                        <div class="stat-trend">
                            <i class="fas fa-arrow-up"></i>
                            <span>Future activities</span>
                        </div>
                    </div>
                </div>
                <div class="stat-card ongoing">
                    <div class="stat-icon">
                        <i class="fas fa-spinner"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['ongoing'] ?? 0; ?></div>
                        <div class="stat-label">Ongoing</div>
                        <div class="stat-trend">
                            <i class="fas fa-play-circle"></i>
                            <span>Currently happening</span>
                        </div>
                    </div>
                </div>
                <div class="stat-card completed">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['completed'] ?? 0; ?></div>
                        <div class="stat-label">Completed</div>
                        <div class="stat-trend">
                            <i class="fas fa-check"></i>
                            <span>Finished activities</span>
                        </div>
                    </div>
                </div>
                <div class="stat-card this-year">
                    <div class="stat-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['this_year'] ?? 0; ?></div>
                        <div class="stat-label">This Year</div>
                        <div class="stat-trend">
                            <i class="fas fa-chart-bar"></i>
                            <span>Year-to-date activities</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add New Activity Form -->
            <div class="form-container">
                <div class="form-header">
                    <h2><i class="fas fa-plus-circle"></i> Add New Activity</h2>
                </div>

                <form method="POST" id="activityForm">
                    <input type="hidden" name="add" value="1">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-calendar-day"></i> Activity Date
                            </label>
                            <input type="date" 
                                   name="activity_date" 
                                   class="form-date" 
                                   required
                                   id="activityDate">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-clock"></i> Start Time
                            </label>
                            <input type="time" 
                                   name="start_time" 
                                   class="form-time">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-clock"></i> End Time
                            </label>
                            <input type="time" 
                                   name="end_time" 
                                   class="form-time">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-tag"></i> Category
                            </label>
                            <select name="category" class="form-select" required>
                                <option value="general">General</option>
                                <option value="public_event">Public Event</option>
                                <option value="meeting">Meeting</option>
                                <option value="conference">Conference</option>
                                <option value="inauguration">Inauguration</option>
                                <option value="health_camp">Health Camp</option>
                                <option value="party_meeting">Party Meeting</option>
                                <option value="development">Development</option>
                                <option value="media">Media</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-exclamation-circle"></i> Priority
                            </label>
                            <select name="priority" class="form-select">
                                <option value="1">Low</option>
                                <option value="2">Medium</option>
                                <option value="3">High</option>
                                <option value="4">Critical</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-tasks"></i> Status
                            </label>
                            <select name="status" class="form-select">
                                <option value="upcoming">Upcoming</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-map-marker-alt"></i> Location
                            </label>
                            <input type="text" 
                                   name="location" 
                                   class="form-input" 
                                   placeholder="Enter activity location"
                                   maxlength="200">
                            <span class="char-count" data-for="location"></span>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-map-marked-alt"></i> Location Map URL
                            </label>
                            <input type="text" 
                                   name="location_map" 
                                   class="form-input" 
                                   placeholder="Enter Google Maps URL"
                                   maxlength="500">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-image"></i> Image URL
                            </label>
                            <input type="text" 
                                   name="image_url" 
                                   class="form-input" 
                                   placeholder="Enter image URL"
                                   maxlength="500">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-user-tie"></i> Organizer
                            </label>
                            <input type="text" 
                                   name="organizer" 
                                   class="form-input" 
                                   placeholder="Enter organizer name"
                                   maxlength="100">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-users"></i> Attendee Count
                            </label>
                            <input type="number" 
                                   name="attendee_count" 
                                   class="form-input" 
                                   placeholder="Enter attendee count"
                                   min="0">
                        </div>
                    </div>

                    <!-- Side-by-Side English and Nepali Fields -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-heading"></i> Title (English)
                            </label>
                            <input type="text" 
                                   name="title_en" 
                                   class="form-input" 
                                   placeholder="Enter activity title in English" 
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
                                   placeholder="Enter activity title in Nepali" 
                                   required
                                   maxlength="200">
                            <span class="char-count" data-for="title_np"></span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-align-left"></i> Description (English)
                            </label>
                            <textarea name="description_en" 
                                      class="form-textarea" 
                                      placeholder="Enter activity description in English" 
                                      required
                                      maxlength="5000"></textarea>
                            <span class="char-count" data-for="description_en"></span>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-align-left"></i> Description (Nepali)
                            </label>
                            <textarea name="description_np" 
                                      class="form-textarea" 
                                      placeholder="Enter activity description in Nepali" 
                                      required
                                      maxlength="5000"></textarea>
                            <span class="char-count" data-for="description_np"></span>
                        </div>
                    </div>

                    <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-plus"></i> <span>Add Activity</span>
                        </button>
                        <button type="button" onclick="resetForm()" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> <span>Clear Form</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Activities List -->
            <div class="table-container">
                <div class="table-header">
                    <h2>
                        <i class="fas fa-list"></i> Existing Activities
                        <span class="count-badge"><?php echo count($activities); ?></span>
                    </h2>
                    
                    <div class="filter-controls">
                        <select class="filter-select" id="statusFilter">
                            <option value="all">All Status</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="ongoing">Ongoing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <select class="filter-select" id="categoryFilter">
                            <option value="all">All Categories</option>
                            <option value="public_event">Public Event</option>
                            <option value="meeting">Meeting</option>
                            <option value="conference">Conference</option>
                            <option value="inauguration">Inauguration</option>
                            <option value="health_camp">Health Camp</option>
                            <option value="party_meeting">Party Meeting</option>
                            <option value="development">Development</option>
                            <option value="media">Media</option>
                            <option value="general">General</option>
                        </select>
                    </div>
                </div>
                
                <?php if (empty($activities)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h3>No activities yet</h3>
                        <p>Start by adding your first activity using the form above.</p>
                        <button onclick="document.getElementById('title_en').focus()" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add First Activity
                        </button>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="modern-table" id="activitiesTable">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Date & Time</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th style="min-width: 180px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activities as $activity): 
                                    $activityDate = new DateTime($activity['activity_date']);
                                    $now = new DateTime();
                                    $statusClass = $activity['status'] ?? 'upcoming';
                                    $priorityLabels = ['Low', 'Medium', 'High', 'Critical'];
                                    $priorityClass = $activity['priority'] <= 2 ? 'low' : ($activity['priority'] == 3 ? 'medium' : 'high');
                                ?>
                                    <tr class="activity-row <?php echo $statusClass; ?>" 
                                        data-status="<?php echo $statusClass; ?>"
                                        data-category="<?php echo $activity['category']; ?>"
                                        data-date="<?php echo $activity['formatted_date']; ?>">
                                        <td class="title-cell">
                                            <div class="activity-title-en"><?php echo htmlspecialchars($activity['title_en']); ?></div>
                                            <?php if ($activity['title_np']): ?>
                                                <div class="activity-title-np"><?php echo htmlspecialchars($activity['title_np']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $activity['category']))); ?>
                                        </td>
                                        <td class="date-cell">
                                            <div class="activity-date">
                                                <i class="far fa-calendar"></i> 
                                                <?php echo $activityDate->format('M d, Y'); ?>
                                            </div>
                                            <?php if ($activity['formatted_start_time'] || $activity['formatted_end_time']): ?>
                                                <div class="activity-time">
                                                    <i class="far fa-clock"></i>
                                                    <?php 
                                                        $time = '';
                                                        if ($activity['formatted_start_time']) {
                                                            $time .= date('h:i A', strtotime($activity['formatted_start_time']));
                                                        }
                                                        if ($activity['formatted_end_time']) {
                                                            $time .= ' - ' . date('h:i A', strtotime($activity['formatted_end_time']));
                                                        }
                                                        echo $time;
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($activity['location']); ?>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $statusClass; ?>">
                                                <i class="fas fa-<?php 
                                                    echo $statusClass === 'upcoming' ? 'clock' : 
                                                         ($statusClass === 'ongoing' ? 'spinner' : 
                                                         ($statusClass === 'completed' ? 'check' : 'times')); 
                                                ?>"></i>
                                                <?php echo ucfirst($statusClass); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="priority-badge priority-<?php echo $priorityClass; ?>">
                                                <?php echo $priorityLabels[$activity['priority'] - 1] ?? 'Low'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button onclick="editActivity(<?php echo htmlspecialchars(json_encode($activity), ENT_QUOTES, 'UTF-8'); ?>)" 
                                                        class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i> <span>Edit</span>
                                                </button>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="delete" value="1">
                                                    <input type="hidden" name="id" value="<?php echo $activity['id']; ?>">
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-danger" 
                                                            onclick="return confirmDelete('<?php echo addslashes($activity['title_en']); ?>', '<?php echo $activityDate->format('M d, Y'); ?>', event);">
                                                        <i class="fas fa-trash"></i> <span>Delete</span>
                                                    </button>
                                                </form>
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

    <!-- Edit Modal -->
    <div id="editModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <i class="fas fa-edit"></i> Edit Activity
                </div>
                <button class="modal-close" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="editModalContent">
                <!-- Edit form will be inserted here -->
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
                            
                            // Update styling
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

            // Filter functionality
            const statusFilter = document.getElementById('statusFilter');
            const categoryFilter = document.getElementById('categoryFilter');
            
            if (statusFilter && categoryFilter) {
                const filterActivities = () => {
                    const status = statusFilter.value;
                    const category = categoryFilter.value;
                    const rows = document.querySelectorAll('.activity-row');
                    
                    rows.forEach(row => {
                        const rowStatus = row.getAttribute('data-status');
                        const rowCategory = row.getAttribute('data-category');
                        let showRow = true;
                        
                        // Filter by status
                        if (status !== 'all' && rowStatus !== status) showRow = false;
                        
                        // Filter by category
                        if (category !== 'all' && rowCategory !== category) showRow = false;
                        
                        row.style.display = showRow ? '' : 'none';
                    });
                };
                
                statusFilter.addEventListener('change', filterActivities);
                categoryFilter.addEventListener('change', filterActivities);
            }

            // Edit activity
            window.editActivity = function(activity) {
                const modal = document.getElementById('editModal');
                const modalContent = document.getElementById('editModalContent');
                
                // Format times for input fields
                const formatTimeForInput = (time) => {
                    if (!time) return '';
                    return time.substring(0, 5); // Get HH:MM part
                };
                
                const html = `
                    <form method="POST" style="padding: 1rem;">
                        <input type="hidden" name="edit" value="1">
                        <input type="hidden" name="id" value="${activity.id}">
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                            <div>
                                <label class="form-label">
                                    <i class="fas fa-calendar-day"></i> Activity Date
                                </label>
                                <input type="date" 
                                       name="activity_date" 
                                       class="form-date" 
                                       value="${activity.formatted_date || activity.activity_date.split(' ')[0]}" 
                                       required>
                            </div>
                            
                            <div>
                                <label class="form-label">
                                    <i class="fas fa-clock"></i> Start Time
                                </label>
                                <input type="time" 
                                       name="start_time" 
                                       class="form-time" 
                                       value="${formatTimeForInput(activity.formatted_start_time || activity.start_time)}">
                            </div>
                            
                            <div>
                                <label class="form-label">
                                    <i class="fas fa-clock"></i> End Time
                                </label>
                                <input type="time" 
                                       name="end_time" 
                                       class="form-time" 
                                       value="${formatTimeForInput(activity.formatted_end_time || activity.end_time)}">
                            </div>
                            
                            <div>
                                <label class="form-label">
                                    <i class="fas fa-tag"></i> Category
                                </label>
                                <select name="category" class="form-select" required>
                                    <option value="general" ${activity.category === 'general' ? 'selected' : ''}>General</option>
                                    <option value="public_event" ${activity.category === 'public_event' ? 'selected' : ''}>Public Event</option>
                                    <option value="meeting" ${activity.category === 'meeting' ? 'selected' : ''}>Meeting</option>
                                    <option value="conference" ${activity.category === 'conference' ? 'selected' : ''}>Conference</option>
                                    <option value="inauguration" ${activity.category === 'inauguration' ? 'selected' : ''}>Inauguration</option>
                                    <option value="health_camp" ${activity.category === 'health_camp' ? 'selected' : ''}>Health Camp</option>
                                    <option value="party_meeting" ${activity.category === 'party_meeting' ? 'selected' : ''}>Party Meeting</option>
                                    <option value="development" ${activity.category === 'development' ? 'selected' : ''}>Development</option>
                                    <option value="media" ${activity.category === 'media' ? 'selected' : ''}>Media</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="form-label">
                                    <i class="fas fa-exclamation-circle"></i> Priority
                                </label>
                                <select name="priority" class="form-select">
                                    <option value="1" ${activity.priority == 1 ? 'selected' : ''}>Low</option>
                                    <option value="2" ${activity.priority == 2 ? 'selected' : ''}>Medium</option>
                                    <option value="3" ${activity.priority == 3 ? 'selected' : ''}>High</option>
                                    <option value="4" ${activity.priority == 4 ? 'selected' : ''}>Critical</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="form-label">
                                    <i class="fas fa-tasks"></i> Status
                                </label>
                                <select name="status" class="form-select">
                                    <option value="upcoming" ${activity.status === 'upcoming' ? 'selected' : ''}>Upcoming</option>
                                    <option value="ongoing" ${activity.status === 'ongoing' ? 'selected' : ''}>Ongoing</option>
                                    <option value="completed" ${activity.status === 'completed' ? 'selected' : ''}>Completed</option>
                                    <option value="cancelled" ${activity.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                                </select>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                            <div>
                                <label class="form-label">
                                    <i class="fas fa-map-marker-alt"></i> Location
                                </label>
                                <input type="text" 
                                       name="location" 
                                       class="form-input" 
                                       value="${(activity.location || '').replace(/"/g, '&quot;')}" 
                                       placeholder="Enter activity location"
                                       maxlength="200">
                            </div>
                            
                            <div>
                                <label class="form-label">
                                    <i class="fas fa-map-marked-alt"></i> Location Map URL
                                </label>
                                <input type="text" 
                                       name="location_map" 
                                       class="form-input" 
                                       value="${(activity.location_map || '').replace(/"/g, '&quot;')}" 
                                       placeholder="Enter Google Maps URL"
                                       maxlength="500">
                            </div>
                            
                            <div>
                                <label class="form-label">
                                    <i class="fas fa-image"></i> Image URL
                                </label>
                                <input type="text" 
                                       name="image_url" 
                                       class="form-input" 
                                       value="${(activity.image_url || '').replace(/"/g, '&quot;')}" 
                                       placeholder="Enter image URL"
                                       maxlength="500">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                            <div>
                                <label class="form-label">
                                    <i class="fas fa-user-tie"></i> Organizer
                                </label>
                                <input type="text" 
                                       name="organizer" 
                                       class="form-input" 
                                       value="${(activity.organizer || '').replace(/"/g, '&quot;')}" 
                                       placeholder="Enter organizer name"
                                       maxlength="100">
                            </div>
                            
                            <div>
                                <label class="form-label">
                                    <i class="fas fa-users"></i> Attendee Count
                                </label>
                                <input type="number" 
                                       name="attendee_count" 
                                       class="form-input" 
                                       value="${activity.attendee_count || 0}" 
                                       placeholder="Enter attendee count"
                                       min="0">
                            </div>
                        </div>
                        
                        <!-- Side-by-Side English and Nepali Fields -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                            <div>
                                <label class="form-label">
                                    <i class="fas fa-heading"></i> Title (English)
                                </label>
                                <input type="text" 
                                       name="title_en" 
                                       class="form-input" 
                                       value="${activity.title_en.replace(/"/g, '&quot;')}" 
                                       required>
                            </div>
                            <div>
                                <label class="form-label">
                                    <i class="fas fa-heading"></i> Title (Nepali)
                                </label>
                                <input type="text" 
                                       name="title_np" 
                                       class="form-input" 
                                       value="${(activity.title_np || '').replace(/"/g, '&quot;')}">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                            <div>
                                <label class="form-label">
                                    <i class="fas fa-align-left"></i> Description (English)
                                </label>
                                <textarea name="description_en" 
                                          class="form-textarea" 
                                          rows="4"
                                          required>${(activity.description_en || '').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</textarea>
                            </div>
                            <div>
                                <label class="form-label">
                                    <i class="fas fa-align-left"></i> Description (Nepali)
                                </label>
                                <textarea name="description_np" 
                                          class="form-textarea" 
                                          rows="4">${(activity.description_np || '').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</textarea>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Activity
                            </button>
                            <button type="button" onclick="closeModal()" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </form>
                `;
                
                modalContent.innerHTML = html;
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            };

            // Close modal
            window.closeModal = function() {
                const modal = document.getElementById('editModal');
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            };

            // Close modal on escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeModal();
                }
            });

            // Close modal on outside click
            document.getElementById('editModal').addEventListener('click', (e) => {
                if (e.target === document.getElementById('editModal')) {
                    closeModal();
                }
            });

            // Enhanced delete confirmation
            window.confirmDelete = function(title, date, event) {
                event.preventDefault();
                event.stopPropagation();
                
                const modal = document.createElement('div');
                modal.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.7);
                    backdrop-filter: blur(8px);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 10000;
                    animation: fadeIn 0.3s ease;
                `;
                
                modal.innerHTML = `
                    <div style="background: white; border-radius: 20px; padding: 2.5rem; max-width: 500px; width: 90%; box-shadow: 0 25px 60px rgba(0,0,0,0.3);">
                        <div style="text-align: center; margin-bottom: 2rem;">
                            <div style="background: linear-gradient(135deg, #ef4444, #dc2626); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                                <i class="fas fa-calendar-times" style="color: white; font-size: 2rem;"></i>
                            </div>
                            <h3 style="font-size: 1.5rem; font-weight: 700; color: #1e293b; margin-bottom: 0.75rem;">Delete Activity</h3>
                            <p style="color: #64748b; line-height: 1.6;">
                                Are you sure you want to delete the activity "<strong>${title}</strong>" 
                                scheduled for <strong>${date}</strong>?
                                <br><br>
                                This action <span style="color: #ef4444; font-weight: 600;">cannot be undone</span>.
                            </p>
                        </div>
                        <div style="display: flex; gap: 1rem; justify-content: center;">
                            <button id="cancelDeleteBtn" 
                                    style="padding: 1rem 2rem; background: #f1f5f9; color: #64748b; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; transition: all 0.3s; flex: 1;">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button id="confirmDeleteBtn" 
                                    style="padding: 1rem 2rem; background: linear-gradient(135deg, #ef4444, #dc2626); color: white; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; transition: all 0.3s; flex: 1;">
                                <i class="fas fa-trash"></i> Delete Activity
                            </button>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(modal);
                
                // Get the form that triggered this
                const form = event.target.closest('form');
                
                // Handle cancel button
                modal.querySelector('#cancelDeleteBtn').addEventListener('click', () => {
                    document.body.removeChild(modal);
                });
                
                // Handle confirm button
                modal.querySelector('#confirmDeleteBtn').addEventListener('click', () => {
                    document.body.removeChild(modal);
                    // Submit the form
                    if (form) {
                        form.submit();
                    }
                });
                
                // Close modal on outside click
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        document.body.removeChild(modal);
                    }
                });
                
                // Close modal on escape key
                const escapeHandler = (e) => {
                    if (e.key === 'Escape') {
                        document.body.removeChild(modal);
                        document.removeEventListener('keydown', escapeHandler);
                    }
                };
                document.addEventListener('keydown', escapeHandler);
                
                return false;
            };

            // Form submission loading state
            const form = document.getElementById('activityForm');
            const submitBtn = document.getElementById('submitBtn');
            
            if (form && submitBtn) {
                form.addEventListener('submit', function() {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                    submitBtn.querySelector('span').textContent = 'Adding...';
                });
            }

            // Reset form
            window.resetForm = function() {
                if (confirm('Are you sure you want to clear the form?')) {
                    form.reset();
                    textInputs.forEach(input => input.dispatchEvent(new Event('input')));
                }
            };

            // Initialize with today's date
            if (!document.getElementById('activityDate').value) {
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('activityDate').value = today;
            }

            // Add priority badge styling
            const style = document.createElement('style');
            style.textContent = `
                .priority-badge {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    padding: 0.25rem 0.75rem;
                    border-radius: var(--radius-full);
                    font-size: 0.75rem;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.05em;
                }
                
                .priority-low {
                    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
                    color: #065f46;
                }
                
                .priority-medium {
                    background: linear-gradient(135deg, #fef3c7, #fde68a);
                    color: #92400e;
                }
                
                .priority-high {
                    background: linear-gradient(135deg, #fecaca, #fca5a5);
                    color: #991b1b;
                }
            `;
            document.head.appendChild(style);
        });
    </script>
</body>
</html>