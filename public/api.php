<?php
// Set header to return JSON
header('Content-Type: application/json');

// Bootstrap the application
require_once '../app/bootstrap.php';

// Init API Core Library
// This will be a new class to handle API routing
$init = new ApiCore();
?>