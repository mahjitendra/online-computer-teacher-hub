<?php
// Database Configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? 'password');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'online_computer_teacher_hub');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');

// Database Connection Options
define('DB_OPTIONS', [
    PDO::ATTR_PERSISTENT => true,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
]);

// Connection Pool Settings
define('DB_POOL_SIZE', $_ENV['DB_POOL_SIZE'] ?? 10);
define('DB_TIMEOUT', $_ENV['DB_TIMEOUT'] ?? 30);

// Backup Database Settings
define('BACKUP_DB_HOST', $_ENV['BACKUP_DB_HOST'] ?? DB_HOST);
define('BACKUP_DB_USER', $_ENV['BACKUP_DB_USER'] ?? DB_USER);
define('BACKUP_DB_PASS', $_ENV['BACKUP_DB_PASS'] ?? DB_PASS);
define('BACKUP_DB_NAME', $_ENV['BACKUP_DB_NAME'] ?? DB_NAME . '_backup');
?>