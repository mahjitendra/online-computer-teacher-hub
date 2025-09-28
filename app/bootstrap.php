<?php
// Start Session
session_start();

// Load Config
require_once dirname(__DIR__) . '/config/app.php';

// Autoload Core Libraries
spl_autoload_register(function($className){
    // Define possible paths for classes
    $paths = [
        __DIR__ . '/core/' . $className . '.php',
        __DIR__ . '/Models/User/' . $className . '.php',
        __DIR__ . '/Models/Course/' . $className . '.php',
        __DIR__ . '/Models/Enrollment/' . $className . '.php',
        __DIR__ . '/Models/Job/' . $className . '.php',
        __DIR__ . '/Models/Payment/' . $className . '.php',
        __DIR__ . '/Models/Exam/' . $className . '.php',
        __DIR__ . '/Controllers/Web/' . $className . '.php',
        __DIR__ . '/Controllers/Teacher/' . $className . '.php',
        __DIR__ . '/Controllers/Api/' . $className . '.php'
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});