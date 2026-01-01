<?php
session_start();
require_once '../includes/header.php';
require_once '../includes/functions.php';

if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Handle backup
if (isset($_POST['backup'])) {
    $backupFile = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $tables = ['about', 'activities', 'gallery_images', 'timeline_entries', 'settings', 'donations', 'appointments', 'contact_messages'];
    
    $backupContent = "";
    
    foreach ($tables as $table) {
        // Get table structure
        $query = "SHOW CREATE TABLE $table";
        $stmt = $pdo->query($query);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $backupContent .= $row['Create Table'] . ";\n\n";
        
        // Get table data
        $query = "SELECT * FROM $table";
        $stmt = $pdo->query($query);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($rows) > 0) {
            $columns = array_keys($rows[0]);
            $backupContent .= "INSERT INTO $table (" . implode(', ', $columns) . ") VALUES \n";
            
            $values = [];
            foreach ($rows as $row) {
                $rowValues = array_map(function($value) use ($pdo) {
                    return $pdo->quote($value);
                }, array_values($row));
                $values[] = "(" . implode(', ', $rowValues) . ")";
            }
            
            $backupContent .= implode(",\n", $values) . ";\n\n";
        }
    }
    
    // Save backup file
    file_put_contents($backupFile, $backupContent);
    
    // Download file
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . $backupFile . '"');
    readfile($backupFile);
    unlink($backupFile);
    exit;
}

// Handle restore
if (isset($_POST['restore']) && isset($_FILES['backup_file'])) {
    $file = $_FILES['backup_file'];
    
    if ($file['error'] === UPLOAD_ERR_OK && $file['type'] === 'application/sql') {
        $content = file_get_contents($file['tmp_name']);
        
        // Split SQL statements
        $statements = explode(";\n", $content);
        
        $pdo->beginTransaction();
        try {
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $pdo->exec($statement);
                }
            }
            $pdo->commit();
            $message = "Backup restored successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error restoring backup: " . $e->getMessage();
        }
    } else {
        $error = "Invalid backup file!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Backup & Restore | KP Oli Admin</title>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="admin-container">
        <div class="main-content">
            <h1>Backup & Restore</h1>
            
            <?php if (isset($message)): ?>
                <div class="alert success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="backup-section">
                <h2>Create Backup</h2>
                <form method="post">
                    <p>This will create a backup of all database tables.</p>
                    <button type="submit" name="backup" class="btn btn-primary">
                        <i class="fas fa-download"></i> Download Backup
                    </button>
                </form>
            </div>
            
            <div class="restore-section">
                <h2>Restore Backup</h2>
                <form method="post" enctype="multipart/form-data">
                    <p>Warning: This will overwrite existing data!</p>
                    <div class="form-group">
                        <label>Select backup file (.sql):</label>
                        <input type="file" name="backup_file" accept=".sql" required>
                    </div>
                    <button type="submit" name="restore" class="btn btn-warning">
                        <i class="fas fa-upload"></i> Restore Backup
                    </button>
                </form>
            </div>
            
            <div class="export-section">
                <h2>Export Settings for Another Site</h2>
                <p>You can export the settings to use on another politician's portfolio site.</p>
                <a href="export-settings.php" class="btn btn-success">
                    <i class="fas fa-file-export"></i> Export Settings
                </a>
            </div>
        </div>
    </div>
</body>
</html>