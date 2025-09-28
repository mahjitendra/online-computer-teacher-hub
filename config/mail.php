<?php
// Email Configuration
define('MAIL_HOST', $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com');
define('MAIL_PORT', $_ENV['MAIL_PORT'] ?? 587);
define('MAIL_USERNAME', $_ENV['MAIL_USERNAME'] ?? '');
define('MAIL_PASSWORD', $_ENV['MAIL_PASSWORD'] ?? '');
define('MAIL_ENCRYPTION', $_ENV['MAIL_ENCRYPTION'] ?? 'tls');

// Email Settings
define('MAIL_FROM_ADDRESS', $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@example.com');
define('MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME'] ?? 'Online Computer Teacher Hub');

// Email Templates
define('EMAIL_TEMPLATES_PATH', dirname(__DIR__) . '/views/emails/');

// Email Queue Settings
define('MAIL_QUEUE_ENABLED', $_ENV['MAIL_QUEUE_ENABLED'] ?? false);
define('MAIL_QUEUE_DRIVER', $_ENV['MAIL_QUEUE_DRIVER'] ?? 'database');

// Notification Settings
define('NOTIFICATION_EMAIL_ENABLED', $_ENV['NOTIFICATION_EMAIL_ENABLED'] ?? true);
define('NOTIFICATION_SMS_ENABLED', $_ENV['NOTIFICATION_SMS_ENABLED'] ?? false);

// SMS Configuration (if enabled)
define('SMS_PROVIDER', $_ENV['SMS_PROVIDER'] ?? 'twilio');
define('SMS_API_KEY', $_ENV['SMS_API_KEY'] ?? '');
define('SMS_API_SECRET', $_ENV['SMS_API_SECRET'] ?? '');
define('SMS_FROM_NUMBER', $_ENV['SMS_FROM_NUMBER'] ?? '');
?>