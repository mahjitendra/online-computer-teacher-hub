<?php
/*
 * Base API Controller
 * Provides common functionality for API controllers
 */
class ApiController {
    // Helper method to send a JSON response
    protected function jsonResponse($data, $statusCode = 200){
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }

    // This base controller can be extended with other shared logic,
    // such as authentication checks, request validation, etc.
}
?>