<?php
// Video Configuration
define('VIDEO_UPLOAD_PATH', $_ENV['VIDEO_UPLOAD_PATH'] ?? dirname(__DIR__) . '/public/uploads/courses/videos/');
define('VIDEO_MAX_SIZE', $_ENV['VIDEO_MAX_SIZE'] ?? 524288000); // 500MB
define('VIDEO_ALLOWED_FORMATS', ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm']);

// Video Processing
define('VIDEO_PROCESSING_ENABLED', $_ENV['VIDEO_PROCESSING_ENABLED'] ?? true);
define('FFMPEG_PATH', $_ENV['FFMPEG_PATH'] ?? '/usr/bin/ffmpeg');

// Video Quality Settings
define('VIDEO_QUALITIES', [
    '240p' => ['width' => 426, 'height' => 240, 'bitrate' => '400k'],
    '360p' => ['width' => 640, 'height' => 360, 'bitrate' => '800k'],
    '480p' => ['width' => 854, 'height' => 480, 'bitrate' => '1200k'],
    '720p' => ['width' => 1280, 'height' => 720, 'bitrate' => '2500k'],
    '1080p' => ['width' => 1920, 'height' => 1080, 'bitrate' => '5000k']
]);

// Video Streaming
define('VIDEO_STREAMING_ENABLED', $_ENV['VIDEO_STREAMING_ENABLED'] ?? true);
define('VIDEO_CHUNK_SIZE', $_ENV['VIDEO_CHUNK_SIZE'] ?? 1024); // KB

// Video CDN Settings
define('VIDEO_CDN_ENABLED', $_ENV['VIDEO_CDN_ENABLED'] ?? false);
define('VIDEO_CDN_URL', $_ENV['VIDEO_CDN_URL'] ?? '');

// Video Analytics
define('VIDEO_ANALYTICS_ENABLED', $_ENV['VIDEO_ANALYTICS_ENABLED'] ?? true);
define('VIDEO_PROGRESS_TRACKING', $_ENV['VIDEO_PROGRESS_TRACKING'] ?? true);

// Video Security
define('VIDEO_HOTLINK_PROTECTION', $_ENV['VIDEO_HOTLINK_PROTECTION'] ?? true);
define('VIDEO_TOKEN_EXPIRY', $_ENV['VIDEO_TOKEN_EXPIRY'] ?? 3600); // 1 hour

// Thumbnail Settings
define('THUMBNAIL_GENERATION_ENABLED', $_ENV['THUMBNAIL_GENERATION_ENABLED'] ?? true);
define('THUMBNAIL_COUNT', $_ENV['THUMBNAIL_COUNT'] ?? 3);
define('THUMBNAIL_SIZE', $_ENV['THUMBNAIL_SIZE'] ?? '320x180');
?>