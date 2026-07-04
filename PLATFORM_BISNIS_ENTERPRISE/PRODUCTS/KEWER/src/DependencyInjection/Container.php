<?php
namespace Kewer\DependencyInjection;

class Container {
    private static $instance;
    private $bindings = [];
    private $instances = [];
    
    private function __construct() {
        // Private constructor for singleton
    }
    
    /**
     * Get container instance
     */
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Bind a class or interface to implementation
     */
    public function bind($abstract, $concrete = null) {
        if ($concrete === null) {
            $concrete = $abstract;
        }
        
        $this->bindings[$abstract] = $concrete;
    }
    
    /**
     * Bind a singleton
     */
    public function singleton($abstract, $concrete = null) {
        $this->bind($abstract, $concrete);
        $this->instances[$abstract] = null; // Mark as singleton
    }
    
    /**
     * Resolve a dependency
     */
    public function make($abstract, $parameters = []) {
        // Check if already instantiated (singleton)
        if (isset($this->instances[$abstract]) && $this->instances[$abstract] !== null) {
            return $this->instances[$abstract];
        }
        
        // Check if bound
        if (isset($this->bindings[$abstract])) {
            $concrete = $this->bindings[$abstract];
        } else {
            $concrete = $abstract;
        }
        
        // If it's a closure, execute it
        if ($concrete instanceof \Closure) {
            $instance = $concrete($this, $parameters);
        } else {
            // Instantiate class with dependencies
            $instance = $this->build($concrete, $parameters);
        }
        
        // If singleton, store instance
        if (isset($this->instances[$abstract])) {
            $this->instances[$abstract] = $instance;
        }
        
        return $instance;
    }
    
    /**
     * Build class with dependency injection
     */
    private function build($concrete, $parameters = []) {
        $reflector = new \ReflectionClass($concrete);
        
        if (!$reflector->isInstantiable()) {
            throw new \Exception("Class {$concrete} is not instantiable");
        }
        
        $constructor = $reflector->getConstructor();
        
        if ($constructor === null) {
            return new $concrete;
        }
        
        $dependencies = $this->resolveDependencies($constructor, $parameters);
        
        return $reflector->newInstanceArgs($dependencies);
    }
    
    /**
     * Resolve constructor dependencies
     */
    private function resolveDependencies(\ReflectionMethod $constructor, $parameters = []) {
        $dependencies = [];
        
        foreach ($constructor->getParameters() as $parameter) {
            $dependency = $parameter->getName();
            
            // Check if parameter is provided
            if (isset($parameters[$dependency])) {
                $dependencies[] = $parameters[$dependency];
                continue;
            }
            
            // Check if parameter has a type hint
            if ($parameter->getType()) {
                $type = $parameter->getType()->getName();
                
                // Try to resolve from container
                try {
                    $dependencies[] = $this->make($type);
                    continue;
                } catch (\Exception $e) {
                    // If not in container, check if it has default value
                    if ($parameter->isDefaultValueAvailable()) {
                        $dependencies[] = $parameter->getDefaultValue();
                        continue;
                    }
                    
                    throw new \Exception("Cannot resolve dependency {$dependency} for {$constructor->getDeclaringClass()->getName()}");
                }
            }
            
            // Check if parameter has default value
            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }
            
            throw new \Exception("Cannot resolve dependency {$dependency} for {$constructor->getDeclaringClass()->getName()}");
        }
        
        return $dependencies;
    }
    
    /**
     * Check if binding exists
     */
    public function has($abstract) {
        return isset($this->bindings[$abstract]);
    }
    
    /**
     * Clear all bindings and instances
     */
    public function flush() {
        $this->bindings = [];
        $this->instances = [];
    }
}

/**
 * Helper function to get container instance
 */
function app() {
    return Container::getInstance();
}

/**
 * Helper function to resolve dependency
 */
function resolve($abstract, $parameters = []) {
    return app()->make($abstract, $parameters);
}
?>
