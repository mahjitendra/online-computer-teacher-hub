<?php
/*
 * Base Controller
 * Common functionality for all controllers
 */
class BaseController extends Controller {
    protected $middleware = [];
    
    public function __construct(){
        $this->applyMiddleware();
    }

    protected function applyMiddleware(){
        foreach($this->middleware as $middleware){
            $middlewareClass = $middleware . 'Middleware';
            if(class_exists($middlewareClass)){
                $middlewareInstance = new $middlewareClass();
                $middlewareInstance->handle();
            }
        }
    }

    protected function addMiddleware($middleware){
        $this->middleware[] = $middleware;
    }

    protected function jsonResponse($data, $statusCode = 200){
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    protected function validateRequest($rules, $data){
        $errors = [];
        
        foreach($rules as $field => $rule){
            if(isset($rule['required']) && $rule['required'] && empty($data[$field])){
                $errors[$field] = $field . ' is required';
            }
            
            if(isset($rule['min']) && strlen($data[$field]) < $rule['min']){
                $errors[$field] = $field . ' must be at least ' . $rule['min'] . ' characters';
            }
            
            if(isset($rule['max']) && strlen($data[$field]) > $rule['max']){
                $errors[$field] = $field . ' must not exceed ' . $rule['max'] . ' characters';
            }
            
            if(isset($rule['email']) && $rule['email'] && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)){
                $errors[$field] = $field . ' must be a valid email address';
            }
        }
        
        return $errors;
    }

    protected function redirect($url, $message = null, $type = 'success'){
        if($message){
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_type'] = $type;
        }
        header('location: ' . URLROOT . $url);
        exit();
    }

    protected function back($message = null, $type = 'error'){
        if($message){
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_type'] = $type;
        }
        header('location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
}
?>