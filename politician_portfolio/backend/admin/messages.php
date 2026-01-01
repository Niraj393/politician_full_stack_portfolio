<?php
// backend/admin/messages.php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.php';

$message = '';
$messages = [];
$stats = [
    'total' => 0,
    'unread' => 0,
    'today' => 0,
    'week' => 0
];

try {
    $conn = getPDOConnection();

    // Handle actions (mark_read, delete, mark_all_read)
    if (isset($_GET['action'])) {
        if ($_GET['action'] === 'mark_all_read') {
            $stmt = $conn->query("UPDATE contact_messages SET is_read = 1 WHERE is_read = 0");
            $message = '<i class="fas fa-check-circle"></i> All messages marked as read!';
        } elseif (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            if ($_GET['action'] === 'mark_read') {
                $stmt = $conn->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
                $stmt->execute([$id]);
                $message = '<i class="fas fa-check"></i> Message marked as read!';
            } elseif ($_GET['action'] === 'delete') {
                $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
                $stmt->execute([$id]);
                $message = '<i class="fas fa-trash-alt"></i> Message deleted successfully!';
            }
        }
        
        // Redirect to avoid re-processing on refresh
        header('Location: messages.php');
        exit();
    }

    // Fetch all messages
    $stmt = $conn->query("SELECT * FROM contact_messages ORDER BY submitted_at DESC");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get statistics
    $stmt = $conn->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
            SUM(CASE WHEN DATE(submitted_at) = CURDATE() THEN 1 ELSE 0 END) as today,
            SUM(CASE WHEN submitted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as week
        FROM contact_messages
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $message = '<i class="fas fa-exclamation-triangle"></i> Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages Management - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Modern CSS Reset and Variables - Messages Theme - COMPACT VERSION */
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

        /* Main Content - Enhanced - COMPACT */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 1.75rem;
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
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
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
            background: linear-gradient(90deg, var(--primary), var(--info));
            border-radius: var(--radius-full);
        }

        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--info) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .page-header h1 i {
            font-size: 1.4rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--info) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        /* Stats Cards - COMPACT VERSION */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: var(--radius-lg);
            padding: 1.25rem;
            box-shadow: var(--glass-shadow);
            border: 1px solid var(--glass-border);
            transition: all var(--transition);
            position: relative;
            overflow: hidden;
            border-top: 3px solid transparent;
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
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card.total {
            border-top-color: var(--primary);
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.05) 0%, rgba(139, 92, 246, 0.02) 100%);
        }

        .stat-card.unread {
            border-top-color: var(--info);
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(59, 130, 246, 0.02) 100%);
        }

        .stat-card.today {
            border-top-color: var(--secondary);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(16, 185, 129, 0.02) 100%);
        }

        .stat-card.week {
            border-top-color: var(--warning);
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, rgba(245, 158, 11, 0.02) 100%);
        }

        .stat-icon {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 40px;
            height: 40px;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            opacity: 0.2;
            z-index: 0;
        }

        .stat-card.total .stat-icon {
            background: var(--primary);
            color: white;
        }

        .stat-card.unread .stat-icon {
            background: var(--info);
            color: white;
        }

        .stat-card.today .stat-icon {
            background: var(--secondary);
            color: white;
        }

        .stat-card.week .stat-icon {
            background: var(--warning);
            color: white;
        }

        .stat-content {
            position: relative;
            z-index: 1;
        }

        .stat-number {
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: 0.35rem;
            line-height: 1;
        }

        .stat-card.total .stat-number {
            color: var(--primary);
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-card.unread .stat-number {
            color: var(--info);
            background: linear-gradient(135deg, var(--info), #2563eb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-card.today .stat-number {
            color: var(--secondary);
            background: linear-gradient(135deg, var(--secondary), var(--secondary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-card.week .stat-number {
            color: var(--warning);
            background: linear-gradient(135deg, var(--warning), var(--warning-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--gray-600);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.7rem;
            margin-top: 0.5rem;
            color: var(--gray-500);
        }

        /* Table Container - Modern - COMPACT */
        .table-container {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
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
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--info));
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

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
        }

        .table-header h2 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table-header h2 i {
            color: var(--primary);
        }

        .count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.2rem 0.6rem;
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            color: white;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        /* Modern Table Styling - COMPACT */
        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.85rem;
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
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.85rem 1rem;
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
            height: 15px;
            background: var(--gray-300);
        }

        .modern-table th:last-child::after {
            display: none;
        }

        .message-row {
            background: white;
            border-bottom: 1px solid var(--gray-100);
            transition: all var(--transition);
            position: relative;
        }

        .message-row::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: transparent;
            transition: background var(--transition);
        }

        .message-row.unread::before {
            background: var(--info);
        }

        .message-row:hover {
            background: var(--gray-50);
            transform: translateX(5px);
            box-shadow: var(--shadow-sm);
            cursor: pointer;
        }

        .message-row:hover::before {
            background: var(--primary);
        }

        .modern-table td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--gray-100);
            vertical-align: top;
        }

        .modern-table .message-row:last-child td {
            border-bottom: none;
        }

        /* Table Cell Styles - COMPACT */
        .sender-cell {
            min-width: 180px;
        }

        .sender-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .sender-name {
            font-weight: 600;
            color: var(--dark);
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        .unread .sender-name::after {
            content: '';
            width: 6px;
            height: 6px;
            background: var(--info);
            border-radius: 50%;
            display: inline-block;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .sender-email {
            font-size: 0.75rem;
            color: var(--gray-600);
            word-break: break-all;
        }

        .subject-cell {
            min-width: 150px;
        }

        .message-subject {
            font-weight: 600;
            color: var(--dark);
            font-size: 0.85rem;
            margin-bottom: 0.35rem;
        }

        .message-preview {
            font-size: 0.75rem;
            color: var(--gray-600);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.4;
        }

        .date-cell {
            white-space: nowrap;
        }

        .message-date {
            font-weight: 500;
            color: var(--gray-700);
            font-size: 0.8rem;
        }

        .message-time {
            font-size: 0.7rem;
            color: var(--gray-500);
            margin-top: 0.15rem;
        }

        /* Status Badges - COMPACT */
        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.3rem;
            padding: 0.3rem 0.75rem;
            border-radius: var(--radius-full);
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border: 1px solid transparent;
        }

        .status-unread {
            background: linear-gradient(135deg, var(--info-light) 0%, var(--info-light) 100%);
            color: #1d4ed8;
            border-color: rgba(59, 130, 246, 0.2);
        }

        .status-read {
            background: linear-gradient(135deg, var(--secondary-light) 0%, var(--secondary-light) 100%);
            color: var(--secondary-dark);
            border-color: rgba(16, 185, 129, 0.2);
        }

        /* Action Buttons - COMPACT */
        .action-buttons {
            display: flex;
            gap: 0.4rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            padding: 0.4rem 0.75rem;
            border: none;
            border-radius: var(--radius-md);
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition);
            text-decoration: none;
            white-space: nowrap;
            position: relative;
            overflow: hidden;
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity var(--transition);
        }

        .action-btn:hover::before {
            opacity: 1;
        }

        .action-btn i, .action-btn span {
            position: relative;
            z-index: 1;
        }

        .btn-mark-read {
            background: linear-gradient(135deg, var(--info) 0%, #2563eb 100%);
            color: white;
            box-shadow: 0 3px 10px rgba(59, 130, 246, 0.2);
        }

        .btn-mark-read:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.3);
        }

        .btn-delete {
            background: linear-gradient(135deg, var(--danger) 0%, var(--danger-dark) 100%);
            color: white;
            box-shadow: 0 3px 10px rgba(239, 68, 68, 0.2);
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(239, 68, 68, 0.3);
        }

        .btn-reply {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%);
            color: white;
            box-shadow: 0 3px 10px rgba(16, 185, 129, 0.2);
        }

        .btn-reply:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.3);
        }

        /* General Button Styles - COMPACT */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.65rem 1.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.85rem;
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

        .btn i, .btn span {
            position: relative;
            z-index: 1;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%);
        }

        .btn-success:hover {
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
        }

        /* Message Styling - COMPACT */
        .message {
            padding: 0.85rem 1.25rem;
            margin-bottom: 1.5rem;
            border-radius: var(--radius-md);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
            color: var(--secondary-dark);
            border: 2px solid rgba(16, 185, 129, 0.2);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
            font-size: 0.85rem;
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

        /* Empty State - COMPACT */
        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
            background: linear-gradient(135deg, var(--gray-50) 0%, var(--light) 100%);
            border-radius: var(--radius-lg);
            border: 2px dashed var(--gray-300);
        }

        .empty-state i {
            font-size: 2.75rem;
            color: var(--gray-400);
            margin-bottom: 0.85rem;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.25rem;
            color: var(--gray-600);
            margin-bottom: 0.4rem;
            font-weight: 600;
        }

        .empty-state p {
            color: var(--gray-500);
            max-width: 350px;
            margin: 0 auto 1rem;
            font-size: 0.85rem;
        }

        /* Filter Controls - COMPACT */
        .filter-controls {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 1rem;
            align-items: center;
        }

        .filter-select {
            padding: 0.4rem 0.85rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-md);
            background: white;
            color: var(--gray-700);
            font-weight: 500;
            font-size: 0.8rem;
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

        /* Responsive Design */
        @media (max-width: 1200px) {
            .main-content {
                padding: 1.5rem;
            }
            .modern-table {
                display: block;
                overflow-x: auto;
            }
        }

        @media (max-width: 1024px) {
            .main-content {
                margin-left: 240px;
                padding: 1.25rem;
            }
            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
            }
            .header-actions {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 70px;
                padding: 1rem;
            }
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }
            .stats-cards {
                grid-template-columns: 1fr;
            }
            .table-container {
                padding: 1rem;
            }
            .table-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
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
                width: 100%;
            }
            .action-btn {
                width: 100%;
                justify-content: center;
            }
            .modern-table th,
            .modern-table td {
                padding: 0.75rem;
            }
        }

        @media (max-width: 640px) {
            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }

        /* Message Detail Modal - COMPACT */
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
            padding: 1.5rem;
            max-width: 700px;
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
            margin-bottom: 1.15rem;
            padding-bottom: 0.85rem;
            border-bottom: 2px solid var(--gray-200);
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.35rem;
            cursor: pointer;
            color: var(--gray-500);
            width: 35px;
            height: 35px;
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

        .message-details {
            display: grid;
            gap: 1.15rem;
        }

        .message-field {
            display: grid;
            gap: 0.4rem;
        }

        .message-label {
            font-weight: 600;
            color: var(--gray-700);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .message-value {
            font-size: 0.85rem;
            color: var(--dark);
            line-height: 1.5;
        }

        .message-content-box {
            background: var(--gray-50);
            border-radius: var(--radius-md);
            padding: 1.15rem;
            margin-top: 0.35rem;
            white-space: pre-wrap;
            line-height: 1.7;
            font-size: 0.85rem;
        }

        .modal-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
            padding-top: 1.15rem;
            border-top: 1px solid var(--gray-200);
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
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* Floating Action Button - COMPACT */
        .fab {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            cursor: pointer;
            box-shadow: var(--shadow-lg);
            z-index: 100;
            transition: all var(--transition);
        }

        .fab:hover {
            transform: scale(1.1);
            box-shadow: 0 12px 28px rgba(139, 92, 246, 0.4);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1><i class="fas fa-envelope"></i> Messages Management</h1>
                <div class="header-actions">
                    <a href="?action=mark_all_read" class="btn btn-success">
                        <i class="fas fa-check-double"></i> <span>Mark All Read</span>
                    </a>
                    <a href="dashboard.php" class="btn">
                        <i class="fas fa-arrow-left"></i> <span>Back to Dashboard</span>
                    </a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo strpos($message, 'error') !== false ? 'error' : ''; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="stats-cards">
                <div class="stat-card total">
                    <div class="stat-icon">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['total']; ?></div>
                        <div class="stat-label"style="color: #3e7dca;">Total Messages</div>
                        <div class="stat-trend">
                            <i class="fas fa-database"></i>
                            <span>All messages received</span>
                        </div>
                    </div>
                </div>
                <div class="stat-card unread">
                    <div class="stat-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['unread']; ?></div>
                        <div class="stat-label"style="color: #3e7dca;">Unread Messages</div>
                        <div class="stat-trend">
                            <i class="fas fa-eye-slash"></i>
                            <span>Require attention</span>
                        </div>
                    </div>
                </div>
                <div class="stat-card today">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['today']; ?></div>
                        <div class="stat-label"style="color: #3a93e2ff;">Today's Messages</div>
                        <div class="stat-trend">
                            <i class="fas fa-clock"></i>
                            <span>Received today</span>
                        </div>
                    </div>
                </div>
                <div class="stat-card week">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['week']; ?></div>
                        <div class="stat-label"style="color: #3e7dca;">This Week</div>
                        <div class="stat-trend">
                            <i class="fas fa-chart-line"></i>
                            <span>Last 7 days</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages Table -->
            <div class="table-container">
                <div class="table-header">
                    <h2>
                        <i class="fas fa-list"></i> All Messages
                        <span class="count-badge"><?php echo count($messages); ?></span>
                    </h2>
                    
                    <div class="filter-controls">
                        <select class="filter-select" id="statusFilter">
                            <option value="all">All Messages</option>
                            <option value="unread">Unread Only</option>
                            <option value="read">Read Only</option>
                        </select>
                        <select class="filter-select" id="timeFilter">
                            <option value="all">All Time</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                        </select>
                    </div>
                </div>
                
                <?php if (empty($messages)): ?>
                    <div class="empty-state">
                        <i class="fas fa-envelope-open"></i>
                        <h3>No messages yet</h3>
                        <p>Messages from the contact form will appear here.</p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th class="sender-cell">Sender</th>
                                    <th class="subject-cell">Subject & Message</th>
                                    <th class="date-cell">Date</th>
                                    <th>Status</th>
                                    <th style="min-width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($messages as $msg): 
                                    $isUnread = !$msg['is_read'];
                                    $messageDate = date('M d, Y', strtotime($msg['submitted_at']));
                                    $messageTime = date('h:i A', strtotime($msg['submitted_at']));
                                    $messagePreview = strlen($msg['message']) > 150 ? substr($msg['message'], 0, 150) . '...' : $msg['message'];
                                ?>
                                    <tr class="message-row <?php echo $isUnread ? 'unread' : ''; ?>" 
                                        data-message='<?php echo htmlspecialchars(json_encode($msg), ENT_QUOTES, 'UTF-8'); ?>'
                                        data-read='<?php echo $msg['is_read']; ?>'
                                        data-date='<?php echo $msg['submitted_at']; ?>'>
                                        <td class="sender-cell">
                                            <div class="sender-info">
                                                <div class="sender-name">
                                                    <i class="fas fa-user"></i>
                                                    <?php echo htmlspecialchars($msg['name']); ?>
                                                </div>
                                                <div class="sender-email">
                                                    <i class="fas fa-envelope"></i>
                                                    <?php echo htmlspecialchars($msg['email']); ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="subject-cell">
                                            <div class="message-subject"><?php echo htmlspecialchars($msg['subject']); ?></div>
                                            <div class="message-preview"><?php echo htmlspecialchars($messagePreview); ?></div>
                                        </td>
                                        <td class="date-cell">
                                            <div class="message-date"><?php echo $messageDate; ?></div>
                                            <div class="message-time"><?php echo $messageTime; ?></div>
                                        </td>
                                        <td>
                                            <?php if ($isUnread): ?>
                                                <span class="status-badge status-unread">
                                                    <i class="fas fa-envelope"></i>
                                                    Unread
                                                </span>
                                            <?php else: ?>
                                                <span class="status-badge status-read">
                                                    <i class="fas fa-envelope-open"></i>
                                                    Read
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons" onclick="event.stopPropagation();">
                                                <?php if ($isUnread): ?>
                                                    <a href="?action=mark_read&id=<?php echo $msg['id']; ?>" class="action-btn btn-mark-read">
                                                        <i class="fas fa-check"></i>
                                                        <span>Mark Read</span>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="mailto:<?php echo urlencode($msg['email']); ?>?subject=Re: <?php echo urlencode($msg['subject']); ?>" class="action-btn btn-reply">
                                                    <i class="fas fa-reply"></i>
                                                    <span>Reply</span>
                                                </a>
                                                <a href="?action=delete&id=<?php echo $msg['id']; ?>" 
                                                   class="action-btn btn-delete" 
                                                   onclick="return confirmDelete('<?php echo addslashes($msg['name']); ?>', '<?php echo addslashes($msg['subject']); ?>');">
                                                    <i class="fas fa-trash"></i>
                                                    <span>Delete</span>
                                                </a>
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

    <!-- Message Detail Modal -->
    <div id="messageModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <i class="fas fa-envelope-open-text"></i> Message Details
                </div>
                <button class="modal-close" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="message-details" id="modalBody">
                <!-- Message details will be inserted here -->
            </div>
            <div class="modal-actions" id="modalActions">
                <!-- Action buttons will be inserted here -->
            </div>
        </div>
    </div>

    <!-- Floating Action Button for Mobile -->
    <div class="fab" id="mobileFab" onclick="document.querySelector('.header-actions').scrollIntoView({behavior: 'smooth'})">
        <i class="fas fa-cog"></i>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show/hide mobile FAB
            const mobileFab = document.getElementById('mobileFab');
            function updateFabVisibility() {
                mobileFab.style.display = window.innerWidth <= 768 ? 'flex' : 'none';
            }
            updateFabVisibility();
            window.addEventListener('resize', updateFabVisibility);

            // View message modal
            const messageRows = document.querySelectorAll('.message-row');
            messageRows.forEach(row => {
                row.addEventListener('click', function(e) {
                    if (e.target.closest('.action-buttons')) return;
                    
                    const messageData = JSON.parse(this.getAttribute('data-message'));
                    openMessageModal(messageData);
                    
                    // Mark as read if unread
                    if (this.getAttribute('data-read') === '0') {
                        const markReadBtn = this.querySelector('.btn-mark-read');
                        if (markReadBtn) {
                            markReadBtn.click();
                        }
                    }
                });
            });

            // Filter functionality
            const statusFilter = document.getElementById('statusFilter');
            const timeFilter = document.getElementById('timeFilter');
            
            if (statusFilter && timeFilter) {
                const filterMessages = () => {
                    const status = statusFilter.value;
                    const time = timeFilter.value;
                    const rows = document.querySelectorAll('.message-row');
                    const today = new Date();
                    
                    rows.forEach(row => {
                        const isRead = row.getAttribute('data-read') === '1';
                        const messageDate = new Date(row.getAttribute('data-date'));
                        let showRow = true;
                        
                        // Filter by status
                        if (status === 'unread' && isRead) showRow = false;
                        if (status === 'read' && !isRead) showRow = false;
                        
                        // Filter by time
                        if (time !== 'all') {
                            const diffTime = today - messageDate;
                            const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
                            
                            switch(time) {
                                case 'today':
                                    if (diffDays > 0) showRow = false;
                                    break;
                                case 'week':
                                    if (diffDays > 7) showRow = false;
                                    break;
                                case 'month':
                                    if (diffDays > 30) showRow = false;
                                    break;
                            }
                        }
                        
                        row.style.display = showRow ? '' : 'none';
                    });
                };
                
                statusFilter.addEventListener('change', filterMessages);
                timeFilter.addEventListener('change', filterMessages);
            }

            // Enhanced delete confirmation
            window.confirmDelete = function(senderName, subject) {
                return confirm(`Are you sure you want to delete the message from ${senderName} titled "${subject}"?\n\nThis action cannot be undone.`);
            };

            // Auto-refresh every 60 seconds (if modal not open)
            setInterval(() => {
                if (!document.getElementById('messageModal').style.display || 
                    document.getElementById('messageModal').style.display === 'none') {
                    // Placeholder for refresh logic
                    console.log('Auto-refresh check');
                }
            }, 60000);
        });

        // Modal functions
        function openMessageModal(message) {
            const modal = document.getElementById('messageModal');
            const modalBody = document.getElementById('modalBody');
            const modalActions = document.getElementById('modalActions');
            
            const formatDate = (dateString) => {
                const date = new Date(dateString);
                return date.toLocaleString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            };
            
            modalBody.innerHTML = `
                <div class="message-field">
                    <div class="message-label">From</div>
                    <div class="message-value">
                        <strong>${message.name}</strong> &lt;${message.email}&gt;
                    </div>
                </div>
                <div class="message-field">
                    <div class="message-label">Subject</div>
                    <div class="message-value">
                        <strong>${message.subject}</strong>
                    </div>
                </div>
                <div class="message-field">
                    <div class="message-label">Date & Time</div>
                    <div class="message-value">
                        <i class="far fa-clock"></i> ${formatDate(message.submitted_at)}
                    </div>
                </div>
                <div class="message-field">
                    <div class="message-label">Message</div>
                    <div class="message-content-box">
                        ${message.message.replace(/\n/g, '<br>')}
                    </div>
                </div>
            `;
            
            modalActions.innerHTML = `
                <a href="mailto:${message.email}?subject=Re: ${encodeURIComponent(message.subject)}" class="btn btn-reply">
                    <i class="fas fa-reply"></i> Reply
                </a>
                <a href="?action=mark_read&id=${message.id}" class="btn btn-mark-read">
                    <i class="fas fa-check"></i> Mark as Read
                </a>
                <a href="?action=delete&id=${message.id}" class="btn btn-delete" onclick="return confirmDelete('${message.name.replace(/'/g, "\\'")}', '${message.subject.replace(/'/g, "\\'")}');">
                    <i class="fas fa-trash"></i> Delete
                </a>
            `;
            
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            const modal = document.getElementById('messageModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modal on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Close modal on outside click
        document.getElementById('messageModal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('messageModal')) {
                closeModal();
            }
        });
    </script>
</body>
</html>