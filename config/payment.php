<?php
// Payment Gateway Configuration
define('PAYMENT_GATEWAY', $_ENV['PAYMENT_GATEWAY'] ?? 'stripe');

// Stripe Configuration
define('STRIPE_PUBLISHABLE_KEY', $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '');
define('STRIPE_SECRET_KEY', $_ENV['STRIPE_SECRET_KEY'] ?? '');
define('STRIPE_WEBHOOK_SECRET', $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '');

// PayPal Configuration
define('PAYPAL_CLIENT_ID', $_ENV['PAYPAL_CLIENT_ID'] ?? '');
define('PAYPAL_CLIENT_SECRET', $_ENV['PAYPAL_CLIENT_SECRET'] ?? '');
define('PAYPAL_MODE', $_ENV['PAYPAL_MODE'] ?? 'sandbox'); // sandbox or live

// Razorpay Configuration
define('RAZORPAY_KEY_ID', $_ENV['RAZORPAY_KEY_ID'] ?? '');
define('RAZORPAY_KEY_SECRET', $_ENV['RAZORPAY_KEY_SECRET'] ?? '');

// Payment Settings
define('CURRENCY', $_ENV['CURRENCY'] ?? 'USD');
define('CURRENCY_SYMBOL', $_ENV['CURRENCY_SYMBOL'] ?? '$');

// Commission Settings
define('PLATFORM_COMMISSION_RATE', $_ENV['PLATFORM_COMMISSION_RATE'] ?? 0.10); // 10%
define('TEACHER_PAYOUT_RATE', $_ENV['TEACHER_PAYOUT_RATE'] ?? 0.90); // 90%

// Subscription Plans
define('SUBSCRIPTION_PLANS', [
    'basic' => [
        'name' => 'Basic Plan',
        'price' => 9.99,
        'features' => ['Access to basic courses', 'Email support'],
        'stripe_price_id' => 'price_basic_monthly'
    ],
    'premium' => [
        'name' => 'Premium Plan',
        'price' => 19.99,
        'features' => ['Access to all courses', 'Priority support', 'Certificates'],
        'stripe_price_id' => 'price_premium_monthly'
    ],
    'enterprise' => [
        'name' => 'Enterprise Plan',
        'price' => 49.99,
        'features' => ['Everything in Premium', 'Custom courses', 'Dedicated support'],
        'stripe_price_id' => 'price_enterprise_monthly'
    ]
]);

// Refund Settings
define('REFUND_POLICY_DAYS', $_ENV['REFUND_POLICY_DAYS'] ?? 30);
define('AUTO_REFUND_ENABLED', $_ENV['AUTO_REFUND_ENABLED'] ?? false);
?>