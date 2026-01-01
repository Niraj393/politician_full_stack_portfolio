<?php
// backend/admin/appointment.php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../database/connection.php';

$message = '';
$appointments = [];
$stats = [
    'total' => 0,
    'pending' => 0,
    'approved' => 0,
    'completed' => 0
];

try {
    $conn = getPDOConnection();

    // Handle actions (approve, reject, delete)
    if (isset($_GET['action']) && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        if ($_GET['action'] === 'approve') {
            $stmt = $conn->prepare("UPDATE appointments SET status = 'approved' WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Appointment approved!';
        } elseif ($_GET['action'] === 'reject') {
            $stmt = $conn->prepare("UPDATE appointments SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Appointment rejected!';
        } elseif ($_GET['action'] === 'delete') {
            $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Appointment deleted!';
        }
        
        header('Location: appointment.php');
        exit();
    }

    // Fetch all appointments
    $stmt = $conn->query("SELECT * FROM appointments ORDER BY submitted_at DESC");
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get statistics
    $stmt = $conn->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
        FROM appointments
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $message = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Management - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* COMPACT VERSION - Appointment Theme */
        :root {
            --primary: #8b5cf6;
            --primary-dark: #7c3aed;
            --secondary: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-600: #475569;
            --radius-md: 12px;
            --radius-lg: 16px;
            --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
            --transition: 250ms cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #f0f4f8 100%);
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
            margin-left: 280px;
            padding: 1.5rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 0.85rem;
            border-bottom: 2px solid var(--gray-200);
        }

        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .header-actions {
            display: flex;
            gap: 0.75rem;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.85rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.15rem;
            box-shadow: var(--shadow);
            border-left: 3px solid var(--primary);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-number {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--gray-600);
            text-transform: uppercase;
        }

        .table-container {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.15rem;
        }

        .table-header h2 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
        }

        .count-badge {
            background: var(--primary);
            color: white;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .modern-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
        }

        .modern-table th {
            background: var(--gray-100);
            padding: 0.85rem;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid var(--gray-200);
        }

        .modern-table td {
            padding: 0.85rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .modern-table tr:hover {
            background: var(--gray-100);
        }

        .status-badge {
            padding: 0.25rem 0.65rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending { background: #fef3c7; color: #92400e; }
        .status-approved { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-completed { background: #dbeafe; color: #1e40af; }

        .action-buttons {
            display: flex;
            gap: 0.4rem;
        }

        .btn {
            padding: 0.4rem 0.85rem;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            font-size: 0.7rem;
        }

        .btn-approve { background: var(--secondary); color: white; }
        .btn-reject { background: var(--danger); color: white; }
        .btn-delete { background: var(--warning); color: white; }
        .btn:hover { opacity: 0.9; }

        .message {
            padding: 0.85rem;
            margin-bottom: 1.15rem;
            border-radius: var(--radius-md);
            background: #d1fae5;
            color: #065f46;
            border-left: 3px solid var(--secondary);
            font-size: 0.85rem;
        }

        .empty-state {
            text-align: center;
            padding: 2.5rem;
            color: var(--gray-600);
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 0.85rem;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            .page-header {
                flex-direction: column;
                gap: 0.75rem;
            }
            .stats-cards {
                grid-template-columns: 1fr;
            }
            .table-container {
                padding: 1rem;
            }
            .modern-table {
                font-size: 0.7rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1><i class="fas fa-calendar-check"></i> Appointment Management</h1>
                <div class="header-actions">
                    <a href="dashboard.php" class="btn">Back to Dashboard</a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total']; ?></div>
                    <div class="stat-label"style="color: #2b87f9ff;">Total Appointments</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['pending']; ?></div>
                    <div class="stat-label"style="color: #2585fbff;">Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['approved']; ?></div>
                    <div class="stat-label"style="color: #2382f7ff;">Approved</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['completed']; ?></div>
                    <div class="stat-label"style="color: #2484faff;">Completed</div>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h2>All Appointments</h2>
                    <span class="count-badge"><?php echo count($appointments); ?></span>
                </div>

                <?php if (empty($appointments)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h3>No appointments yet</h3>
                        <p>Appointment requests will appear here.</p>
                    </div>
                <?php else: ?>
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>Purpose</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $appt): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($appt['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($appt['mobile_number']); ?></td>
                                    <td><?php echo htmlspecialchars($appt['purpose']); ?></td>
                                    <td><?php echo $appt['preferred_date'] ? date('M d, Y', strtotime($appt['preferred_date'])) : 'N/A'; ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $appt['status']; ?>">
                                            <?php echo ucfirst($appt['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($appt['status'] === 'pending'): ?>
                                                <a href="?action=approve&id=<?php echo $appt['id']; ?>" class="btn btn-approve">Approve</a>
                                                <a href="?action=reject&id=<?php echo $appt['id']; ?>" class="btn btn-reject">Reject</a>
                                            <?php endif; ?>
                                            <a href="?action=delete&id=<?php echo $appt['id']; ?>" class="btn btn-delete" onclick="return confirm('Delete this appointment?')">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>