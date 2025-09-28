<?php
// Social Media Integration Configuration

// Google OAuth
define('GOOGLE_CLIENT_ID', $_ENV['GOOGLE_CLIENT_ID'] ?? '');
define('GOOGLE_CLIENT_SECRET', $_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
define('GOOGLE_REDIRECT_URI', $_ENV['GOOGLE_REDIRECT_URI'] ?? URLROOT . '/auth/google/callback');

// Facebook OAuth
define('FACEBOOK_APP_ID', $_ENV['FACEBOOK_APP_ID'] ?? '');
define('FACEBOOK_APP_SECRET', $_ENV['FACEBOOK_APP_SECRET'] ?? '');
define('FACEBOOK_REDIRECT_URI', $_ENV['FACEBOOK_REDIRECT_URI'] ?? URLROOT . '/auth/facebook/callback');

// LinkedIn OAuth
define('LINKEDIN_CLIENT_ID', $_ENV['LINKEDIN_CLIENT_ID'] ?? '');
define('LINKEDIN_CLIENT_SECRET', $_ENV['LINKEDIN_CLIENT_SECRET'] ?? '');
define('LINKEDIN_REDIRECT_URI', $_ENV['LINKEDIN_REDIRECT_URI'] ?? URLROOT . '/auth/linkedin/callback');

// GitHub OAuth
define('GITHUB_CLIENT_ID', $_ENV['GITHUB_CLIENT_ID'] ?? '');
define('GITHUB_CLIENT_SECRET', $_ENV['GITHUB_CLIENT_SECRET'] ?? '');
define('GITHUB_REDIRECT_URI', $_ENV['GITHUB_REDIRECT_URI'] ?? URLROOT . '/auth/github/callback');

// Social Login Settings
define('SOCIAL_LOGIN_ENABLED', $_ENV['SOCIAL_LOGIN_ENABLED'] ?? true);
define('SOCIAL_AUTO_REGISTER', $_ENV['SOCIAL_AUTO_REGISTER'] ?? true);

// Social Sharing
define('SOCIAL_SHARING_ENABLED', $_ENV['SOCIAL_SHARING_ENABLED'] ?? true);
define('SOCIAL_SHARE_PLATFORMS', [
    'facebook' => true,
    'twitter' => true,
    'linkedin' => true,
    'whatsapp' => true,
    'telegram' => true
]);

// Social Media Links
define('SOCIAL_MEDIA_LINKS', [
    'facebook' => $_ENV['FACEBOOK_PAGE'] ?? '',
    'twitter' => $_ENV['TWITTER_HANDLE'] ?? '',
    'linkedin' => $_ENV['LINKEDIN_PAGE'] ?? '',
    'youtube' => $_ENV['YOUTUBE_CHANNEL'] ?? '',
    'instagram' => $_ENV['INSTAGRAM_HANDLE'] ?? ''
]);
?>