<?php

// This file is loaded by PHPUnit to prepare the testing environment.

// 1. Load Composer's autoloader to make vendor packages (like PHPUnit itself) available.
require_once dirname(__DIR__) . '/vendor/autoload.php';

// 2. Load the application's main bootstrap file.
// This contains our custom autoloader, configuration constants, and starts the session.
require_once dirname(__DIR__) . '/app/bootstrap.php';