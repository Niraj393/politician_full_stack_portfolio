<?php
// config/bootstrap.php

// Set absolute paths
define('BASE_DIR', dirname(__DIR__));
define('CONFIG_DIR', BASE_DIR . '/config');
define('BACKEND_DIR', BASE_DIR . '/backend');
define('ADMIN_DIR', BACKEND_DIR . '/admin');
define('API_DIR', BACKEND_DIR . '/api');
define('UPLOADS_DIR', BASE_DIR . '/uploads');

// Include database configuration
require_once CONFIG_DIR . '/database.php';

// Include other config files if needed
if (file_exists(CONFIG_DIR . '/auth.php')) {
    require_once CONFIG_DIR . '/auth.php';
}
?>