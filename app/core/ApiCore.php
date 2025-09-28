<?php
/*
 * API Core Class
 * Handles API routing
 * URL FORMAT - /api/v1/controller/method/params
 */
class ApiCore {
    protected $currentController = 'DefaultApiController'; // A default to handle base /api calls
    protected $currentMethod = 'index';
    protected $params = [];

    public function __construct(){
        $url = $this->getUrl();

        // The first part of the URL should be the version, e.g., 'v1'
        $version = array_shift($url);

        // The second part is the controller
        if(isset($url[0]) && file_exists('../app/Controllers/Api/' . ucwords($url[0]) . 'ApiController.php')){
            $this->currentController = ucwords($url[0]) . 'ApiController';
            unset($url[0]);
        }

        // Require the controller
        require_once '../app/Controllers/Api/' . $this->currentController . '.php';

        // Instantiate controller class
        $this->currentController = new $this->currentController;

        // The third part is the method
        if(isset($url[1])){
            if(method_exists($this->currentController, $url[1])){
                $this->currentMethod = $url[1];
                unset($url[1]);
            }
        }

        // Get params
        $this->params = $url ? array_values($url) : [];

        // Call the method on the controller
        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }

    public function getUrl(){
        if(isset($_GET['url'])){
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
        return [];
    }
}
?>