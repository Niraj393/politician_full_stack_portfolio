<?php
// backend/admin/dashboard.php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.php';

$message = '';
$stats = [
    'total_messages' => 0,
    'unread_messages' => 0,
    'total_donations' => 0,
    'pending_donations' => 0,
    'total_amount' => 0,
    'recent_gallery' => 0,
    'recent_timeline' => 0,
    'recent_appointments' => 0
];
$recent_messages = [];
$recent_donations = [];
$recent_appointments = [];

try {
    $conn = getPDOConnection();

    // Get statistics
    $stmt = $conn->query("SELECT COUNT(*) as count FROM contact_messages");
    $stats['total_messages'] = $stmt->fetchColumn() ?: 0;

    $stmt = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0");
    $stats['unread_messages'] = $stmt->fetchColumn() ?: 0;

    $stmt = $conn->query("SELECT COUNT(*) as count FROM donations");
    $stats['total_donations'] = $stmt->fetchColumn() ?: 0;

    $stmt = $conn->query("SELECT COUNT(*) as count FROM donations WHERE status = 'pending'");
    $stats['pending_donations'] = $stmt->fetchColumn() ?: 0;

    $stmt = $conn->query("SELECT SUM(amount) as total FROM donations WHERE status = 'verified'");
    $stats['total_amount'] = $stmt->fetchColumn() ?: 0;

    // Recent gallery items (last 7 days)
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM gallery_images WHERE uploaded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stmt->execute();
    $stats['recent_gallery'] = $stmt->fetchColumn() ?: 0;

    // Recent timeline entries (last 7 days)
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM timeline_entries WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stmt->execute();
    $stats['recent_timeline'] = $stmt->fetchColumn() ?: 0;

    // Recent appointments (last 7 days)
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stmt->execute();
    $stats['recent_appointments'] = $stmt->fetchColumn() ?: 0;

    // Get recent messages
    $stmt = $conn->query("SELECT * FROM contact_messages ORDER BY submitted_at DESC LIMIT 5");
    $recent_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent donations
    $stmt = $conn->query("SELECT id, transaction_id, donor_name, donor_email, amount, status, created_at FROM donations ORDER BY created_at DESC LIMIT 5");
    $recent_donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent appointments
    $stmt = $conn->query("SELECT * FROM appointments ORDER BY submitted_at DESC LIMIT 5");
    $recent_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $message = 'Database error: Unable to load dashboard data.';
    error_log("Dashboard error: " . $e->getMessage());
    
    // Ensure arrays are set even on error
    $recent_appointments = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard -Portfolio</title>
    <link rel="icon" href="logo.jpeg" type="image/jpeg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ============================================
           MODERN COMPACT DESIGN SYSTEM
           ============================================ */
        :root {
            /* Primary Colors */
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --primary-light: #6b7ff6;
            
            /* Secondary Colors */
            --secondary: #7209b7;
            --secondary-dark: #5a0791;
            
            /* Accent */
            --accent: #f72585;
            
            /* Neutral Colors */
            --dark: #1e293b;
            --dark-light: #334155;
            --light: #f8fafc;
            --gray: #64748b;
            --gray-light: #e2e8f0;
            --gray-lighter: #f1f5f9;
            
            /* Semantic Colors */
            --success: #10b981;
            --success-dark: #059669;
            --danger: #ef4444;
            --danger-dark: #dc2626;
            --warning: #f59e0b;
            --warning-dark: #d97706;
            --info: #06b6d4;
            --info-dark: #0891b2;
            --purple: #8b5cf6;
            --purple-dark: #7c3aed;
            --pink: #ec4899;
            --pink-dark: #db2777;
            --teal: #14b8a6;
            --teal-dark: #0d9488;
            
            /* Shadows */
            --shadow-xs: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
            --shadow-lg: 0 10px 30px rgba(0,0,0,0.12);
            --shadow-xl: 0 20px 60px rgba(0,0,0,0.15);
            
            /* Border Radius */
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-full: 9999px;
            
            /* Transitions */
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-fast: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #f8fafc 100%);
            color: var(--dark);
            min-height: 100vh;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 28px;
            transition: var(--transition);
            max-width: 1800px;
        }

        /* ============================================
           PAGE HEADER
           ============================================ */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--gray-light);
        }

        .page-header h1 {
            font-size: 1.875rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .welcome-text {
            font-size: 0.875rem;
            color: var(--gray);
            background: white;
            padding: 10px 18px;
            border-radius: var(--radius-full);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid var(--gray-light);
        }

        .welcome-text i {
            color: var(--primary);
        }

        /* ============================================
           ALERT MESSAGE
           ============================================ */
        .message {
            padding: 14px 18px;
            margin-bottom: 24px;
            border-radius: var(--radius-md);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: var(--shadow-sm);
            animation: slideDown 0.3s ease-out;
        }

        .message.error {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
            border-left: 4px solid var(--danger);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ============================================
           QUICK ACTIONS - COMPACT VERSION
           ============================================ */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }

        .action-card {
            background: white;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius-lg);
            padding: 20px;
            text-align: center;
            text-decoration: none;
            color: inherit;
            transition: var(--transition);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            transform: scaleX(0);
            transition: transform var(--transition);
            transform-origin: left;
        }

        .action-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .action-card:hover::before {
            transform: scaleX(1);
        }

        .action-card i {
            font-size: 1.75rem;
            margin-bottom: 12px;
            color: var(--primary);
            transition: var(--transition);
        }

        .action-card:hover i {
            transform: scale(1.15);
            color: var(--secondary);
        }

        .action-card h3 {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 4px;
            color: var(--dark);
        }

        .action-card p {
            font-size: 0.75rem;
            color: var(--gray);
            line-height: 1.4;
        }

        /* ============================================
           STATS GRID - COMPACT & MODERN
           ============================================ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 18px;
            margin-bottom: 36px;
        }

        .stat-card {
            background: white;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius-lg);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, var(--primary), var(--secondary));
            transform: scaleY(0);
            transition: transform var(--transition);
            transform-origin: top;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .stat-card:hover::before {
            transform: scaleY(1);
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            flex-shrink: 0;
            position: relative;
            overflow: hidden;
        }

        .stat-icon::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.2);
            opacity: 0;
            transition: opacity var(--transition);
        }

        .stat-card:hover .stat-icon::after {
            opacity: 1;
        }

        /* Stat Icon Colors */
        .messages .stat-icon { background: linear-gradient(135deg, #4361ee, #3a56d4); }
        .unread .stat-icon { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .donations .stat-icon { background: linear-gradient(135deg, #10b981, #059669); }
        .pending .stat-icon { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .amount .stat-icon { background: linear-gradient(135deg, #06b6d4, #0891b2); }
        .gallery .stat-icon { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .timeline .stat-icon { background: linear-gradient(135deg, #ec4899, #db2777); }
        .appointments .stat-icon { background: linear-gradient(135deg, #14b8a6, #0d9488); }

        .stat-info {
            flex: 1;
            min-width: 0;
        }

        .stat-info h3 {
            font-size: 0.75rem;
            color: var(--gray);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 600;
        }

        .stat-number {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 6px;
            color: var(--dark);
        }

        .stat-trend {
            font-size: 0.7rem;
            color: var(--gray);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .stat-trend i {
            font-size: 0.65rem;
        }

        /* ============================================
           DATA SECTIONS
           ============================================ */
        .data-section {
            margin-bottom: 32px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .section-header h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-header h2 i {
            color: var(--primary);
        }

        .view-all {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 8px 16px;
            border-radius: var(--radius-md);
            transition: var(--transition);
            border: 1px solid transparent;
        }

        .view-all:hover {
            background: var(--primary);
            color: white;
            transform: translateX(4px);
            border-color: var(--primary);
        }

        .card {
            background: white;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        /* ============================================
           TABLE STYLES - COMPACT VERSION
           ============================================ */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        thead {
            background: linear-gradient(135deg, var(--gray-lighter), var(--light));
            border-bottom: 2px solid var(--gray-light);
        }

        th {
            padding: 14px 16px;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tbody tr {
            border-bottom: 1px solid var(--gray-light);
            transition: var(--transition-fast);
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        tbody tr:hover {
            background: linear-gradient(90deg, rgba(67, 97, 238, 0.03), transparent);
        }

        td {
            padding: 14px 16px;
            color: var(--dark-light);
        }

        td strong {
            color: var(--dark);
            font-weight: 600;
        }

        .email {
            color: var(--gray);
            font-size: 0.8rem;
        }

        /* ============================================
           STATUS BADGES
           ============================================ */
        .status-badge {
            padding: 4px 12px;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            letter-spacing: 0.3px;
        }

        .status-badge i {
            font-size: 0.5rem;
        }

        .status-pending { 
            background: #fef3c7; 
            color: #92400e;
        }
        .status-verified,
        .status-approved { 
            background: #d1fae5; 
            color: #065f46;
        }
        .status-rejected { 
            background: #fee2e2; 
            color: #991b1b;
        }
        .status-unread { 
            background: #dbeafe; 
            color: #1e40af;
        }
        .status-read { 
            background: #e5e7eb; 
            color: #374151;
        }
        .status-completed {
            background: #e0e7ff;
            color: #3730a3;
        }

        /* ============================================
           ACTION BUTTONS
           ============================================ */
        .action-buttons {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 0.75rem;
            cursor: pointer;
            transition: var(--transition-fast);
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.7rem;
        }

        .btn-view { 
            background: var(--primary); 
            color: white; 
        }
        .btn-view:hover { 
            background: var(--primary-dark); 
        }

        .btn-verify { 
            background: var(--success); 
            color: white; 
        }
        .btn-verify:hover { 
            background: var(--success-dark); 
        }

        .btn-reject { 
            background: var(--danger); 
            color: white; 
        }
        .btn-reject:hover { 
            background: var(--danger-dark); 
        }

        /* ============================================
           EMPTY STATE
           ============================================ */
        .empty-state {
            text-align: center;
            padding: 50px 24px;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 3.5rem;
            margin-bottom: 16px;
            opacity: 0.25;
            color: var(--gray);
        }

        .empty-state h3 {
            font-size: 1.125rem;
            margin-bottom: 8px;
            color: var(--dark-light);
            font-weight: 600;
        }

        .empty-state p {
            font-size: 0.875rem;
            max-width: 400px;
            margin: 0 auto;
            color: var(--gray);
        }

        /* ============================================
           RESPONSIVE DESIGN
           ============================================ */
        @media (max-width: 1600px) {
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 992px) {
            .main-content {
                margin-left: 80px;
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 16px;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .page-header h1 {
                font-size: 1.5rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .action-buttons {
                width: 100%;
            }
            
            .btn {
                flex: 1;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .quick-actions {
                grid-template-columns: 1fr;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
        }

        /* ============================================
           ANIMATIONS
           ============================================ */
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

        .stat-card {
            animation: fadeInUp 0.5s ease-out forwards;
            opacity: 0;
            animation-delay: calc(var(--order) * 0.08s);
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.75;
            }
        }

        /* ============================================
           CUSTOM SCROLLBAR
           ============================================ */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--gray-light);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--gray);
            border-radius: 4px;
            transition: background var(--transition);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary);
        }

        /* ============================================
           UTILITY CLASSES
           ============================================ */
        .text-truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1>
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard Overview
                </h1>
                <div class="welcome-text">
                    <i class="fas fa-user-circle"></i>
                    <span>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>!</span>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="messages.php" class="action-card">
                    <i class="fas fa-comment-alt"></i>
                    <h3>Manage Messages</h3>
                    <p>View and respond to contact messages</p>
                </a>
                <a href="donations.php" class="action-card">
                    <i class="fas fa-hand-holding-usd"></i>
                    <h3>Verify Donations</h3>
                    <p>Review pending donation requests</p>
                </a>
                <a href="gallery.php" class="action-card">
                    <i class="fas fa-images"></i>
                    <h3>Manage Gallery</h3>
                    <p>Add or remove gallery images</p>
                </a>
                <a href="timeline.php" class="action-card">
                    <i class="fas fa-timeline"></i>
                    <h3>Update Timeline</h3>
                    <p>Add new timeline entries</p>
                </a>
                <a href="appointment.php" class="action-card">
                    <i class="fas fa-calendar-check"></i>
                    <h3>Manage Appointments</h3>
                    <p>View and manage appointment requests</p>
                </a>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <?php 
                $statCards = [
                    ['key' => 'total_messages', 'icon' => 'fas fa-envelope', 'label' => 'Total Messages', 'class' => 'messages', 'trend' => ''],
                    ['key' => 'unread_messages', 'icon' => 'fas fa-envelope-open-text', 'label' => 'Unread Messages', 'class' => 'unread', 'trend' => 'Requires attention'],
                    ['key' => 'total_donations', 'icon' => 'fas fa-donate', 'label' => 'Total Donations', 'class' => 'donations', 'trend' => ''],
                    ['key' => 'pending_donations', 'icon' => 'fas fa-clock', 'label' => 'Pending Donations', 'class' => 'pending', 'trend' => 'Awaiting review'],
                    ['key' => 'total_amount', 'icon' => 'fas fa-money-bill-wave', 'label' => 'Total Amount', 'class' => 'amount', 'trend' => 'Verified only'],
                    ['key' => 'recent_gallery', 'icon' => 'fas fa-images', 'label' => 'Recent Gallery', 'class' => 'gallery', 'trend' => 'Last 7 days'],
                    ['key' => 'recent_timeline', 'icon' => 'fas fa-history', 'label' => 'Recent Timeline', 'class' => 'timeline', 'trend' => 'Last 7 days'],
                    ['key' => 'recent_appointments', 'icon' => 'fas fa-calendar-check', 'label' => 'Recent Appointments', 'class' => 'appointments', 'trend' => 'Last 7 days'],
                ];
                
                foreach ($statCards as $index => $card): 
                ?>
                <div class="stat-card <?php echo $card['class']; ?>" style="--order: <?php echo $index; ?>">
                    <div class="stat-icon">
                        <i class="<?php echo $card['icon']; ?>"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $card['label']; ?></h3>
                        <div class="stat-number">
                            <?php 
                            if ($card['key'] === 'total_amount') {
                                echo 'NPR ' . number_format($stats[$card['key']], 2);
                            } else {
                                echo $stats[$card['key']];
                            }
                            ?>
                        </div>
                        <?php if ($card['trend']): ?>
                        <div class="stat-trend">
                            <i class="fas fa-info-circle"></i>
                            <?php echo $card['trend']; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Recent Messages Section -->
            <div class="data-section">
                <div class="section-header">
                    <h2><i class="fas fa-envelope"></i> Recent Messages</h2>
                    <a href="messages.php" class="view-all">
                        View All Messages
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="card">
                    <?php if (count($recent_messages) > 0): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_messages as $msg): ?>
                                <tr>
                                    <td><strong>#<?php echo $msg['id']; ?></strong></td>
                                    <td><strong><?php echo htmlspecialchars($msg['name']); ?></strong></td>
                                    <td class="email"><?php echo htmlspecialchars($msg['email']); ?></td>
                                    <td class="text-truncate" style="max-width: 200px;">
                                        <?php echo htmlspecialchars(substr($msg['subject'], 0, 30)) . (strlen($msg['subject']) > 30 ? '...' : ''); ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $msg['is_read'] ? 'read' : 'unread'; ?>">
                                            <i class="fas fa-circle"></i>
                                            <?php echo $msg['is_read'] ? 'Read' : 'Unread'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($msg['submitted_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="messages.php?action=view&id=<?php echo $msg['id']; ?>" class="btn btn-view btn-sm">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <?php if (!$msg['is_read']): ?>
                                            <a href="messages.php?action=mark_read&id=<?php echo $msg['id']; ?>" class="btn btn-verify btn-sm">
                                                <i class="fas fa-check"></i> Mark Read
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No messages yet</h3>
                        <p>Contact form messages will appear here when submitted by users.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Donations Section -->
            <div class="data-section">
                <div class="section-header">
                    <h2><i class="fas fa-donate"></i> Recent Donations</h2>
                    <a href="donations.php" class="view-all">
                        View All Donations
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="card">
                    <?php if (count($recent_donations) > 0): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Donor</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_donations as $donation): ?>
                                <tr>
                                    <td><strong>#<?php echo $donation['id']; ?></strong></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($donation['donor_name'] ?: 'Anonymous'); ?></strong><br>
                                        <span class="email"><?php echo htmlspecialchars($donation['donor_email']); ?></span>
                                    </td>
                                    <td><strong>NPR <?php echo number_format($donation['amount'], 2); ?></strong></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $donation['status']; ?>">
                                            <i class="fas fa-circle"></i>
                                            <?php echo ucfirst($donation['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($donation['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($donation['status'] === 'pending'): ?>
                                            <a href="donations.php?action=verify&id=<?php echo $donation['id']; ?>" class="btn btn-verify btn-sm">
                                                <i class="fas fa-check"></i> Verify
                                            </a>
                                            <a href="donations.php?action=reject&id=<?php echo $donation['id']; ?>" class="btn btn-reject btn-sm">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                            <?php endif; ?>
                                            <a href="donations.php?action=view&id=<?php echo $donation['id']; ?>" class="btn btn-view btn-sm">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-donate"></i>
                        <h3>No donations yet</h3>
                        <p>Donations will appear here when submitted by supporters.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Appointments Section -->
            <div class="data-section">
                <div class="section-header">
                    <h2><i class="fas fa-calendar-check"></i> Recent Appointments</h2>
                    <a href="appointment.php" class="view-all">
                        View All Appointments
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="card">
                    <?php if (count($recent_appointments) > 0): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>Purpose</th>
                                    <th>Preferred Date</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_appointments as $appt): ?>
                                <tr>
                                    <td><strong>#<?php echo $appt['id']; ?></strong></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($appt['full_name']); ?></strong><br>
                                        <span class="email"><?php echo htmlspecialchars($appt['address'] ?? 'Not specified'); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($appt['mobile_number']); ?></td>
                                    <td class="text-truncate" style="max-width: 150px;">
                                        <?php echo htmlspecialchars(substr($appt['purpose'], 0, 30)) . (strlen($appt['purpose']) > 30 ? '...' : ''); ?>
                                    </td>
                                    <td>
                                        <?php if ($appt['preferred_date']): ?>
                                            <?php echo date('M d, Y', strtotime($appt['preferred_date'])); ?>
                                        <?php else: ?>
                                            <span style="color: var(--gray);">Not specified</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $appt['status']; ?>">
                                            <i class="fas fa-circle"></i>
                                            <?php echo ucfirst($appt['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($appt['submitted_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($appt['status'] === 'pending'): ?>
                                            <a href="appointment.php?action=approve&id=<?php echo $appt['id']; ?>" class="btn btn-verify btn-sm">
                                                <i class="fas fa-check"></i> Approve
                                            </a>
                                            <a href="appointment.php?action=reject&id=<?php echo $appt['id']; ?>" class="btn btn-reject btn-sm">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                            <?php endif; ?>
                                            <a href="appointment.php?action=view&id=<?php echo $appt['id']; ?>" class="btn btn-view btn-sm">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h3>No appointments yet</h3>
                        <p>Appointment requests will appear here when submitted by users.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>

    <script>
        // Enhanced Dashboard Interactions
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Dashboard initialized successfully!');

            // Smooth scroll reveal for sections
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animationPlayState = 'running';
                    }
                });
            }, observerOptions);
            
            // Observe stat cards
            document.querySelectorAll('.stat-card').forEach(card => {
                card.style.animationPlayState = 'paused';
                observer.observe(card);
            });

            // Add pulse animation to urgent items
            const urgentItems = document.querySelectorAll('.status-unread, .status-pending');
            urgentItems.forEach(item => {
                item.style.animation = 'pulse 2s ease-in-out infinite';
            });

            // Animate counter numbers
            const animateValue = (element, start, end, duration) => {
                const startTime = performance.now();
                const isDecimal = end % 1 !== 0;
                
                const update = (currentTime) => {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    
                    // Easing function
                    const easeOutQuart = 1 - Math.pow(1 - progress, 4);
                    const current = start + (end - start) * easeOutQuart;
                    
                    if (isDecimal) {
                        element.textContent = current.toFixed(2);
                    } else {
                        element.textContent = Math.floor(current).toLocaleString();
                    }
                    
                    if (progress < 1) {
                        requestAnimationFrame(update);
                    } else {
                        // Final value
                        if (isDecimal) {
                            element.textContent = end.toFixed(2);
                        } else {
                            element.textContent = end.toLocaleString();
                        }
                    }
                };
                
                requestAnimationFrame(update);
            };

            // Animate stat numbers on page load
            setTimeout(() => {
                document.querySelectorAll('.stat-number').forEach(stat => {
                    const text = stat.textContent.trim();
                    
                    // Extract number from text (handle NPR currency)
                    const match = text.match(/[\d,]+\.?\d*/);
                    if (match) {
                        const numberStr = match[0].replace(/,/g, '');
                        const number = parseFloat(numberStr);
                        
                        if (!isNaN(number) && number > 0) {
                            const prefix = text.includes('NPR') ? 'NPR ' : '';
                            const isDecimal = text.includes('.');
                            
                            stat.textContent = prefix + '0';
                            
                            // Delay animation slightly for visual effect
                            setTimeout(() => {
                                const startTime = performance.now();
                                
                                const update = (currentTime) => {
                                    const elapsed = currentTime - startTime;
                                    const progress = Math.min(elapsed / 1200, 1);
                                    const easeOut = 1 - Math.pow(1 - progress, 3);
                                    const current = number * easeOut;
                                    
                                    if (isDecimal) {
                                        stat.textContent = prefix + current.toFixed(2);
                                    } else {
                                        stat.textContent = prefix + Math.floor(current).toLocaleString();
                                    }
                                    
                                    if (progress < 1) {
                                        requestAnimationFrame(update);
                                    }
                                };
                                
                                requestAnimationFrame(update);
                            }, 200);
                        }
                    }
                });
            }, 500);

            // Enhanced table row interactions
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.style.transition = 'all 0.2s ease';
                
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(4px)';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateX(0)';
                });
            });

            // Add hover effects for action cards
            document.querySelectorAll('.action-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-4px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });

            // Button click animation
            document.querySelectorAll('.btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.width = ripple.style.height = size + 'px';
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    ripple.classList.add('ripple');
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => ripple.remove(), 600);
                });
            });

            // Auto-refresh functionality (optional)
            let refreshInterval;
            const startAutoRefresh = () => {
                refreshInterval = setInterval(() => {
                    console.log('📊 Stats would refresh here in production');
                    // In production, fetch new data via AJAX
                }, 60000); // Every 60 seconds
            };

            // Pause refresh when page is hidden
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    clearInterval(refreshInterval);
                    console.log('⏸️ Auto-refresh paused');
                } else {
                    startAutoRefresh();
                    console.log('▶️ Auto-refresh resumed');
                }
            });

            // Start auto-refresh
            startAutoRefresh();

            // Keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                if (e.ctrlKey || e.metaKey) {
                    switch(e.key.toLowerCase()) {
                        case 'm':
                            e.preventDefault();
                            window.location.href = 'messages.php';
                            break;
                        case 'd':
                            e.preventDefault();
                            window.location.href = 'donations.php';
                            break;
                        case 'g':
                            e.preventDefault();
                            window.location.href = 'gallery.php';
                            break;
                        case 'a':
                            e.preventDefault();
                            window.location.href = 'appointment.php';
                            break;
                    }
                }
            });

            console.log('⌨️ Keyboard shortcuts enabled: Ctrl+M (Messages), Ctrl+D (Donations), Ctrl+G (Gallery), Ctrl+A (Appointments)');
        });

        // Add ripple effect CSS
        const style = document.createElement('style');
        style.textContent = `
            .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.6);
                transform: scale(0);
                animation: ripple-animation 0.6s ease-out;
                pointer-events: none;
            }
            
            @keyframes ripple-animation {
                to {
                    transform: scale(2);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>