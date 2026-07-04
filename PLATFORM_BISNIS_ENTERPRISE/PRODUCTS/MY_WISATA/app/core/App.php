<?php
/**
 * MyWisata Application - App Class
 * 
 * Main application class that handles routing and request processing.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-06-30
 */

class App {
    private $controller = 'Home';
    private $method = 'index';
    private $params = [];
    
    /**
     * Constructor - Parse URL and route request
     */
    public function __construct() {
        $url = $this->parseUrl();
        
        // Check if controller exists
        if (!empty($url) && $url[0] === 'api') {
            // API routes - check first
            $this->controller = 'Api';
            unset($url[0]);
        } elseif (!empty($url) && file_exists(APP_ROOT . '/app/controllers/' . ucfirst($url[0]) . 'Controller.php')) {
            $this->controller = ucfirst($url[0]);
            unset($url[0]);
        } elseif (!empty($url) && $url[0] === 'destinations') {
            // Handle plural 'destinations' to singular 'Destination' controller
            $this->controller = 'Destination';
            unset($url[0]);
        } elseif (!empty($url) && $url[0] === 'tourguides') {
            // Handle plural 'tourguides' to singular 'TourGuide' controller
            $this->controller = 'TourGuide';
            unset($url[0]);
        } elseif (!empty($url) && $url[0] === 'hotels') {
            // Handle plural 'hotels' to singular 'Hotel' controller
            $this->controller = 'Hotel';
            unset($url[0]);
        } elseif (!empty($url) && $url[0] === 'restaurants') {
            // Handle plural 'restaurants' to singular 'Restaurant' controller
            $this->controller = 'Restaurant';
            unset($url[0]);
        } elseif (!empty($url) && $url[0] === 'events') {
            // Handle plural 'events' to singular 'Event' controller
            $this->controller = 'Event';
            unset($url[0]);
        } else {
            // Default to Home controller
            $this->controller = 'Home';
        }
        
        // Require controller file
        $controllerFile = APP_ROOT . '/app/controllers/' . $this->controller . 'Controller.php';
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            // Instantiate controller with full class name
            $controllerClass = $this->controller . 'Controller';
            $this->controller = new $controllerClass();
        } else {
            die('Controller not found: ' . $this->controller . 'Controller.php');
        }
        
        // Check if method exists
        if (!empty($url) && isset($url[1]) && method_exists($this->controller, $url[1])) {
            $this->method = $url[1];
            unset($url[1]);
        } elseif (!empty($url) && isset($url[1])) {
            // Method specified but doesn't exist
            die('Method not found: ' . $url[1]);
        }
        
        // Get parameters
        $this->params = !empty($url) ? array_values($url) : [];
        
        // Call controller method with parameters
        call_user_func_array([$this->controller, $this->method], $this->params);
    }
    
    /**
     * Parse URL from GET parameter
     * 
     * @return array URL segments
     */
    private function parseUrl() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
        return [];
    }
    
    /**
     * Run the application
     */
    public function run() {
        try {
            $this->__construct();
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }
    
    /**
     * Handle errors
     * 
     * @param Exception $e Exception object
     */
    private function handleError($e) {
        if (APP_DEBUG) {
            echo '<h1>Error</h1>';
            echo '<p>' . $e->getMessage() . '</p>';
            echo '<pre>' . $e->getTraceAsString() . '</pre>';
        } else {
            http_response_code(500);
            require_once APP_ROOT . '/app/views/errors/500.php';
        }
    }
    
    /**
     * Handle 404 errors
     */
    public function handle404() {
        http_response_code(404);
        require_once APP_ROOT . '/app/views/errors/404.php';
        exit;
    }
    
    /**
     * Handle 403 errors
     */
    public function handle403() {
        http_response_code(403);
        require_once APP_ROOT . '/app/views/errors/403.php';
        exit;
    }
}
