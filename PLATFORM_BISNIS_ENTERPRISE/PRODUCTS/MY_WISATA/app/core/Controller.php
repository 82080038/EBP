<?php
/**
 * MyWisata Application - Controller Class
 * 
 * Base controller class that all controllers extend.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-06-30
 */

class Controller {
    protected $db;
    
    /**
     * Constructor - Initialize database connection
     */
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Load a model
     * 
     * @param string $model Model name
     * @return Model
     */
    public function model($model) {
        require_once APP_ROOT . '/app/models/' . $model . '.php';
        return new $model();
    }
    
    /**
     * Load a view
     * 
     * @param string $view View file path
     * @param array $data Data to pass to view
     */
    public function view($view, $data = []) {
        if (file_exists(APP_ROOT . '/app/views/' . $view . '.php')) {
            extract($data);
            require_once APP_ROOT . '/app/views/' . $view . '.php';
        } else {
            die('View not found: ' . $view);
        }
    }
    
    /**
     * Load a service
     * 
     * @param string $service Service name
     * @return Service
     */
    public function service($service) {
        require_once APP_ROOT . '/app/services/' . $service . '.php';
        return new $service();
    }
    
    /**
     * Return JSON response
     * 
     * @param array $data Response data
     * @param int $statusCode HTTP status code
     */
    public function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Redirect to URL
     * 
     * @param string $url URL to redirect to
     */
    public function redirect($url) {
        header('Location: ' . BASE_URL . $url);
        exit;
    }
    
    /**
     * Check if request is AJAX
     * 
     * @return bool
     */
    protected function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    /**
     * Get POST data
     * 
     * @param string $key Key to retrieve
     * @param mixed $default Default value
     * @return mixed
     */
    protected function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }
    
    /**
     * Get GET data
     * 
     * @param string $key Key to retrieve
     * @param mixed $default Default value
     * @return mixed
     */
    protected function get($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }
    
    /**
     * Validate CSRF token
     * 
     * @return bool
     */
    protected function validateCsrf() {
        $token = $this->post('csrf_token');
        return $token === CSRF_TOKEN;
    }
}
