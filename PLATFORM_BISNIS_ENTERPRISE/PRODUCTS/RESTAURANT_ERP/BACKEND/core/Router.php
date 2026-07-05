<?php

declare(strict_types=1);

class Router
{

    /** @var array<array{method: string, path: string, handler: callable}> */
    private array $routes = [];

    public function addRoute(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function add(string $method, string $path, callable $handler): void
    {
        $this->addRoute($method, $path, $handler);
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method) {
                // Convert route pattern to regex
                $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $route['path']);
                $pattern = str_replace('/', '\/', $pattern);
                
                if (preg_match('/^' . $pattern . '$/', $uri, $matches)) {
                    // Extract path parameters
                    array_shift($matches); // Remove full match
                    
                    // Build request array
                    $request = [
                        'method' => $method,
                        'uri' => $uri,
                        'body' => json_decode(file_get_contents('php://input'), true) ?? [],
                        'query' => $_GET
                    ];
                    
                    // Extract parameter names from path
                    preg_match_all('/\{([a-zA-Z_]+)\}/', $route['path'], $paramNames);
                    $paramNames = $paramNames[1] ?? [];
                    
                    // Add parameters to request
                    foreach ($paramNames as $index => $name) {
                        $request[$name] = $matches[$index] ?? null;
                    }
                    
                    call_user_func($route['handler'], $request);
                    exit;
                }
            }
        }

        Response::error("Route not found", 404);
    }
}
