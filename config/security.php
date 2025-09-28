<?php
// Security Configuration
define('ENCRYPTION_KEY', $_ENV['ENCRYPTION_KEY'] ?? 'your-secret-encryption-key-change-this');
define('JWT_SECRET', $_ENV['JWT_SECRET'] ?? 'your-jwt-secret-key-change-this');

// Session Configuration
define('SESSION_LIFETIME', $_ENV['SESSION_LIFETIME'] ?? 7200); // 2 hours
define('SESSION_SECURE', $_ENV['SESSION_SECURE'] ?? false);
define('SESSION_HTTPONLY', $_ENV['SESSION_HTTPONLY'] ?? true);
define('SESSION_SAMESITE', $_ENV['SESSION_SAMESITE'] ?? 'Lax');

// Password Policy
define('PASSWORD_MIN_LENGTH', $_ENV['PASSWORD_MIN_LENGTH'] ?? 8);
define('PASSWORD_REQUIRE_UPPERCASE', $_ENV['PASSWORD_REQUIRE_UPPERCASE'] ?? true);
define('PASSWORD_REQUIRE_LOWERCASE', $_ENV['PASSWORD_REQUIRE_LOWERCASE'] ?? true);
define('PASSWORD_REQUIRE_NUMBERS', $_ENV['PASSWORD_REQUIRE_NUMBERS'] ?? true);
define('PASSWORD_REQUIRE_SYMBOLS', $_ENV['PASSWORD_REQUIRE_SYMBOLS'] ?? false);

// Rate Limiting
define('RATE_LIMIT_ENABLED', $_ENV['RATE_LIMIT_ENABLED'] ?? true);
define('RATE_LIMIT_REQUESTS', $_ENV['RATE_LIMIT_REQUESTS'] ?? 100);
define('RATE_LIMIT_WINDOW', $_ENV['RATE_LIMIT_WINDOW'] ?? 3600); // 1 hour

// Login Attempts
define('MAX_LOGIN_ATTEMPTS', $_ENV['MAX_LOGIN_ATTEMPTS'] ?? 5);
define('LOGIN_LOCKOUT_TIME', $_ENV['LOGIN_LOCKOUT_TIME'] ?? 900); // 15 minutes

// File Upload Security
define('UPLOAD_MAX_SIZE', $_ENV['UPLOAD_MAX_SIZE'] ?? 10485760); // 10MB
define('ALLOWED_FILE_TYPES', [
    'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    'documents' => ['pdf', 'doc', 'docx', 'txt', 'rtf'],
    'videos' => ['mp4', 'avi', 'mov', 'wmv', 'flv'],
    'audio' => ['mp3', 'wav', 'ogg', 'aac']
]);

// CORS Settings
define('CORS_ALLOWED_ORIGINS', $_ENV['CORS_ALLOWED_ORIGINS'] ?? '*');
define('CORS_ALLOWED_METHODS', $_ENV['CORS_ALLOWED_METHODS'] ?? 'GET,POST,PUT,DELETE,OPTIONS');
define('CORS_ALLOWED_HEADERS', $_ENV['CORS_ALLOWED_HEADERS'] ?? 'Content-Type,Authorization,X-Requested-With');

// Content Security Policy
define('CSP_ENABLED', $_ENV['CSP_ENABLED'] ?? true);
define('CSP_POLICY', $_ENV['CSP_POLICY'] ?? "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:;");

// XSS Protection
define('XSS_PROTECTION_ENABLED', $_ENV['XSS_PROTECTION_ENABLED'] ?? true);

// SQL Injection Protection
define('SQL_INJECTION_PROTECTION_ENABLED', $_ENV['SQL_INJECTION_PROTECTION_ENABLED'] ?? true);
?>