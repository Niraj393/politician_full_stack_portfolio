<?php
// backend/admin/donations.php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.php';

$message = '';
$donations = [];
$stats = [
    'total' => 0,
    'verified' => 0,
    'pending' => 0,
    'rejected' => 0,
    'total_amount' => 0
];

try {
    $conn = getPDOConnection();

    // Handle actions (verify, reject, delete)
    if (isset($_GET['action']) && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        
        switch ($_GET['action']) {
            case 'verify':
                $stmt = $conn->prepare("UPDATE donations SET status = 'verified', verified_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                $message = '<i class="fas fa-check-circle"></i> Donation verified successfully!';
                break;
            case 'reject':
                $stmt = $conn->prepare("UPDATE donations SET status = 'rejected' WHERE id = ?");
                $stmt->execute([$id]);
                $message = '<i class="fas fa-times-circle"></i> Donation rejected.';
                break;
            case 'delete':
                $stmt = $conn->prepare("DELETE FROM donations WHERE id = ?");
                $stmt->execute([$id]);
                $message = '<i class="fas fa-trash-alt"></i> Donation deleted successfully.';
                break;
        }
        
        // Redirect to avoid re-processing on refresh
        header('Location: donations.php');
        exit();
    }

    // Fetch all donations
    $stmt = $conn->query("SELECT * FROM donations ORDER BY created_at DESC");
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get statistics
    $stmt = $conn->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) as verified,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
            COALESCE(SUM(CASE WHEN status = 'verified' THEN amount ELSE 0 END), 0) as total_amount
        FROM donations
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
    <title>Donations Management - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Modern CSS Reset and Variables - Donations Theme - COMPACT VERSION */
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
            --gray-300: #fefefeff;
            --gray-400: #fdfdfdff;
            --gray-500: #bec2c9ff;
            --gray-600: #c52121ca;
            --gray-700: #235395ff;
            
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
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: var(--radius-full);
        }

        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .page-header h1 i {
            font-size: 1.4rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Stats Cards - Enhanced - COMPACT */
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
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.05) 0%, rgba(138, 92, 246, 0.59) 100%);
        }

        .stat-card.verified {
            border-top-color: var(--secondary);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(16, 185, 129, 0.41) 100%);
        }

        .stat-card.pending {
            border-top-color: var(--warning);
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, rgba(245, 159, 11, 0.38) 100%);
        }

        .stat-card.rejected {
            border-top-color: var(--danger);
            background: linear-gradient(135deg, rgba(200, 67, 67, 0.3) 0%, rgba(239, 68, 68, 0.37) 100%);
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

        .stat-card.verified .stat-icon {
            background: var(--secondary);
            color: white;
        }

        .stat-card.pending .stat-icon {
            background: var(--warning);
            color: white;
        }

        .stat-card.rejected .stat-icon {
            background: var(--danger);
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

        .stat-card.verified .stat-number {
            color: var(--secondary);
            background: linear-gradient(135deg, var(--secondary), var(--secondary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-card.pending .stat-number {
            color: var(--warning);
            background: linear-gradient(135deg, var(--warning), var(--warning-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-card.rejected .stat-number {
            color: var(--danger);
            background: linear-gradient(135deg, var(--danger), var(--danger-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            font-size: 0.75rem;
                color: #3e7dca;
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

        .stat-trend.positive {
            color: var(--secondary);
        }

        .stat-trend.negative {
            color: var(--danger);
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
            background: linear-gradient(90deg, var(--primary), var(--secondary));
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

        .modern-table tbody tr {
            background: white;
            border-bottom: 1px solid var(--gray-100);
            transition: all var(--transition);
        }

        .modern-table tbody tr:hover {
            background: var(--gray-50);
            transform: translateX(3px);
            box-shadow: var(--shadow-sm);
        }

        .modern-table td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--gray-100);
            vertical-align: top;
        }

        .modern-table tr:last-child td {
            border-bottom: none;
        }

        /* Table Cell Styles - COMPACT */
        .donor-info {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }

        .donor-name {
            font-weight: 600;
            color: var(--dark);
            font-size: 0.85rem;
        }

        .donor-email {
            font-size: 0.75rem;
            color: var(--gray-600);
            word-break: break-all;
        }

        .amount-cell {
            font-weight: 700;
            font-size: 1rem;
            color: var(--primary);
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .phone-number {
            font-weight: 500;
            color: var(--dark);
            font-size: 0.85rem;
        }

        .donor-message {
            font-size: 0.75rem;
            color: var(--gray-600);
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .donor-message:hover {
            white-space: normal;
            overflow: visible;
        }

        .proof-cell {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

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

        .status-pending {
            background: linear-gradient(135deg, var(--warning-light) 0%, var(--warning-light) 100%);
            color: var(--warning-dark);
            border-color: rgba(245, 158, 11, 0.2);
        }

        .status-verified {
            background: linear-gradient(135deg, var(--secondary-light) 0%, var(--secondary-light) 100%);
            color: var(--secondary-dark);
            border-color: rgba(16, 185, 129, 0.2);
        }

        .status-rejected {
            background: linear-gradient(135deg, var(--danger-light) 0%, var(--danger-light) 100%);
            color: var(--danger-dark);
            border-color: rgba(239, 68, 68, 0.2);
        }

        .date-cell {
            font-size: 0.8rem;
            color: var(--gray-600);
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }

        .donation-date {
            font-weight: 500;
            color: var(--gray-700);
        }

        .donation-time {
            font-size: 0.7rem;
            color: var(--gray-500);
        }

        /* Action Buttons - COMPACT */
        .action-buttons {
            display: flex;
            gap: 0.4rem;
            flex-wrap: wrap;
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

        .btn-verify {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--secondary-dark) 100%);
            color: white;
            box-shadow: 0 3px 10px rgba(16, 185, 129, 0.2);
        }

        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.3);
        }

        .btn-reject {
            background: linear-gradient(135deg, var(--danger) 0%, var(--danger-dark) 100%);
            color: white;
            box-shadow: 0 3px 10px rgba(239, 68, 68, 0.2);
        }

        .btn-reject:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(239, 68, 68, 0.3);
        }

        .btn-delete {
            background: linear-gradient(135deg, var(--gray-400) 0%, var(--gray-600) 100%);
            color: white;
            box-shadow: 0 3px 10px rgba(100, 116, 139, 0.2);
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(100, 116, 139, 0.3);
        }

        .btn-view {
            background: linear-gradient(135deg, var(--info) 0%, #2563eb 100%);
            color: white;
            box-shadow: 0 3px 10px rgba(59, 130, 246, 0.2);
        }

        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.3);
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

        /* Back Button - COMPACT */
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

        /* Modal for Image Preview - COMPACT */
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
            max-width: 90%;
            max-height: 90%;
            overflow: auto;
            position: relative;
            animation: slideUp 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
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
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1><i class="fas fa-donate"></i> Donations Management</h1>
                <a href="dashboard.php" class="btn">
                    <i class="fas fa-arrow-left"></i> <span>Back to Dashboard</span>
                </a>
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
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">Rs. <?php echo number_format($stats['total_amount'], 2); ?></div>
                        <div class="stat-label"style="color: #0b0b0bff;">Total Verified Amount</div>
                        <div class="stat-trend positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>All verified donations</span>
                        </div>
                    </div>
                </div>
                <div class="stat-card verified">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['verified']; ?></div>
                        <div class="stat-label"style="color: #0d0d0dff;">Verified Donations</div>
                        <div class="stat-trend positive">
                            <i class="fas fa-check"></i>
                            <span>Verified & processed</span>
                        </div>
                    </div>
                </div>
                <div class="stat-card pending">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['pending']; ?></div>
                        <div class="stat-label" style="color: #0e0f0fff;">Pending Approval</div>
                        <div class="stat-trend">
                            <i class="fas fa-hourglass-half"></i>
                            <span>Awaiting verification</span>
                        </div>
                    </div>
                </div>
                <div class="stat-card rejected">
                    <div class="stat-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['rejected']; ?></div>
                        <div class="stat-label" style="color: #111112ff;">Rejected Donations</div>
                        <div class="stat-trend negative">
                            <i class="fas fa-times"></i>
                            <span>Not approved</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Donations Table -->
            <div class="table-container">
                <div class="table-header">
                    <h2>
                        <i class="fas fa-list"></i> All Donations
                        <span class="count-badge"><?php echo count($donations); ?></span>
                    </h2>
                    
                    <div class="filter-controls">
                        <select class="filter-select" id="statusFilter">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="verified">Verified</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        <select class="filter-select" id="amountFilter">
                            <option value="all">All Amounts</option>
                            <option value="0-1000">Rs. 0 - 1,000</option>
                            <option value="1000-5000">Rs. 1,000 - 5,000</option>
                            <option value="5000+">Rs. 5,000+</option>
                        </select>
                    </div>
                </div>
                
                <?php if (empty($donations)): ?>
                    <div class="empty-state">
                        <i class="fas fa-donate"></i>
                        <h3>No donations yet</h3>
                        <p>Donations will appear here when received.</p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Donor Information</th>
                                    <th>Amount</th>
                                    <th>Contact</th>
                                    <th>Proof</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($donations as $donation): 
                                    $uploadDate = date('M d, Y', strtotime($donation['created_at']));
                                    $uploadTime = date('h:i A', strtotime($donation['created_at']));
                                ?>
                                    <tr data-status="<?php echo $donation['status']; ?>" 
                                        data-amount="<?php echo $donation['amount']; ?>">
                                        <td>
                                            <div class="donor-info">
                                                <div class="donor-name">
                                                    <?php echo htmlspecialchars($donation['donor_name'] ?? 'Anonymous'); ?>
                                                </div>
                                                <div class="donor-email">
                                                    <?php echo htmlspecialchars($donation['donor_email'] ?? 'No email'); ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="amount-cell">
                                                Rs. <?php echo number_format($donation['amount'], 2); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="contact-info">
                                                <div class="phone-number">
                                                    <?php echo htmlspecialchars($donation['phone'] ?? 'N/A'); ?>
                                                </div>
                                                <div class="donor-message" title="<?php echo htmlspecialchars($donation['message'] ?? ''); ?>">
                                                    <?php 
                                                        $donMessage = $donation['message'] ?? 'No message';
                                                        echo strlen($donMessage) > 50 ? substr($donMessage, 0, 50) . '...' : $donMessage;
                                                    ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($donation['screenshot_path'])): ?>
                                                <a href="javascript:void(0);" 
                                                   onclick="viewProof('<?php echo htmlspecialchars($donation['screenshot_path']); ?>')" 
                                                   class="action-btn btn-view">
                                                    <i class="fas fa-eye"></i>
                                                    <span>View Proof</span>
                                                </a>
                                            <?php else: ?>
                                                <span style="color: var(--gray-400); font-size: 0.8rem;">No proof</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $donation['status']; ?>">
                                                <i class="fas fa-<?php echo $donation['status'] === 'verified' ? 'check' : ($donation['status'] === 'rejected' ? 'times' : 'clock'); ?>"></i>
                                                <?php echo ucfirst($donation['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="date-cell">
                                                <div class="donation-date"><?php echo $uploadDate; ?></div>
                                                <div class="donation-time"><?php echo $uploadTime; ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php if ($donation['status'] === 'pending'): ?>
                                                    <a href="?action=verify&id=<?php echo $donation['id']; ?>" 
                                                       class="action-btn btn-verify">
                                                        <i class="fas fa-check"></i>
                                                        <span>Verify</span>
                                                    </a>
                                                    <a href="?action=reject&id=<?php echo $donation['id']; ?>" 
                                                       class="action-btn btn-reject">
                                                        <i class="fas fa-times"></i>
                                                        <span>Reject</span>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="?action=delete&id=<?php echo $donation['id']; ?>" 
                                                   class="action-btn btn-delete" 
                                                   onclick="return confirm('Are you sure you want to delete this donation?');">
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

    <!-- Image Preview Modal -->
    <div id="imageModal" class="modal-overlay">
        <div class="modal-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.15rem;">
                <h3 style="font-size: 1.15rem; font-weight: 600; color: var(--dark);">
                    <i class="fas fa-receipt"></i> Donation Proof
                </h3>
                <button onclick="closeModal()" style="background: none; border: none; font-size: 1.35rem; color: var(--gray-500); cursor: pointer; padding: 0.4rem;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <img id="modalImage" src="" alt="Donation Proof" style="max-width: 100%; max-height: 70vh; border-radius: var(--radius-md);">
            <div style="margin-top: 1.15rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button onclick="closeModal()" style="padding: 0.6rem 1.25rem; background: var(--gray-200); color: var(--gray-700); border: none; border-radius: var(--radius-md); font-weight: 500; cursor: pointer; font-size: 0.85rem;">
                    Close
                </button>
                <a id="downloadLink" href="#" download style="padding: 0.6rem 1.25rem; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; border: none; border-radius: var(--radius-md); font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.85rem;">
                    <i class="fas fa-download"></i> Download
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Image preview modal
            window.viewProof = function(imagePath) {
                const modal = document.getElementById('imageModal');
                const modalImage = document.getElementById('modalImage');
                const downloadLink = document.getElementById('downloadLink');
                
                modalImage.src = imagePath;
                downloadLink.href = imagePath;
                downloadLink.download = 'donation-proof-' + Date.now() + '.jpg';
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            };
            
            window.closeModal = function() {
                const modal = document.getElementById('imageModal');
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            };
            
            // Close modal on ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeModal();
                }
            });
            
            // Close modal on overlay click
            document.getElementById('imageModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });
            
            // Filter functionality
            const statusFilter = document.getElementById('statusFilter');
            const amountFilter = document.getElementById('amountFilter');
            
            if (statusFilter && amountFilter) {
                const filterTable = () => {
                    const status = statusFilter.value;
                    const amount = amountFilter.value;
                    const rows = document.querySelectorAll('.modern-table tbody tr');
                    
                    rows.forEach(row => {
                        const rowStatus = row.getAttribute('data-status');
                        const rowAmount = parseFloat(row.getAttribute('data-amount'));
                        let showRow = true;
                        
                        // Filter by status
                        if (status !== 'all' && rowStatus !== status) {
                            showRow = false;
                        }
                        
                        // Filter by amount
                        if (amount !== 'all') {
                            let [min, max] = amount.split('-');
                            if (amount === '5000+') {
                                if (rowAmount < 5000) showRow = false;
                            } else {
                                min = parseFloat(min);
                                max = parseFloat(max);
                                if (rowAmount < min || rowAmount > max) showRow = false;
                            }
                        }
                        
                        row.style.display = showRow ? '' : 'none';
                    });
                };
                
                statusFilter.addEventListener('change', filterTable);
                amountFilter.addEventListener('change', filterTable);
            }
        });
    </script>
</body>
</html>