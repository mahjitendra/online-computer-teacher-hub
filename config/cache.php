<?php
// Cache Configuration
define('CACHE_DRIVER', $_ENV['CACHE_DRIVER'] ?? 'file');
define('CACHE_PREFIX', $_ENV['CACHE_PREFIX'] ?? 'octh_');
define('CACHE_DEFAULT_TTL', $_ENV['CACHE_DEFAULT_TTL'] ?? 3600); // 1 hour

// File Cache Settings
define('CACHE_PATH', $_ENV['CACHE_PATH'] ?? dirname(__DIR__) . '/storage/cache/');

// Redis Cache Settings (if using Redis)
define('REDIS_HOST', $_ENV['REDIS_HOST'] ?? '127.0.0.1');
define('REDIS_PORT', $_ENV['REDIS_PORT'] ?? 6379);
define('REDIS_PASSWORD', $_ENV['REDIS_PASSWORD'] ?? '');
define('REDIS_DATABASE', $_ENV['REDIS_DATABASE'] ?? 0);

// Memcached Settings (if using Memcached)
define('MEMCACHED_HOST', $_ENV['MEMCACHED_HOST'] ?? '127.0.0.1');
define('MEMCACHED_PORT', $_ENV['MEMCACHED_PORT'] ?? 11211);

// Cache Tags and Groups
define('CACHE_TAGS', [
    'courses' => 'courses',
    'users' => 'users',
    'exams' => 'exams',
    'jobs' => 'jobs',
    'payments' => 'payments'
]);

// Cache TTL for different data types
define('CACHE_TTL', [
    'courses' => 1800,      // 30 minutes
    'users' => 3600,        // 1 hour
    'exams' => 7200,        // 2 hours
    'jobs' => 1800,         // 30 minutes
    'analytics' => 300,     // 5 minutes
    'static_content' => 86400 // 24 hours
]);
?>