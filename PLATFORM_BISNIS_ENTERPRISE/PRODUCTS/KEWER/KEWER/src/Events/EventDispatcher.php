<?php
namespace Kewer\Events;

class EventDispatcher {
    private static $listeners = [];
    private static $eventLog = [];
    
    /**
     * Register event listener
     */
    public static function listen($event, $listener) {
        if (!isset(self::$listeners[$event])) {
            self::$listeners[$event] = [];
        }
        
        self::$listeners[$event][] = $listener;
    }
    
    /**
     * Dispatch event
     */
    public static function dispatch($event, $payload = []) {
        self::$eventLog[] = [
            'event' => $event,
            'payload' => $payload,
            'timestamp' => microtime(true)
        ];
        
        if (!isset(self::$listeners[$event])) {
            return true;
        }
        
        foreach (self::$listeners[$event] as $listener) {
            try {
                if (is_callable($listener)) {
                    call_user_func($listener, $payload);
                } elseif (is_array($listener)) {
                    $class = $listener[0];
                    $method = $listener[1];
                    
                    if (is_object($class)) {
                        $class->$method($payload);
                    } else {
                        $instance = new $class();
                        $instance->$method($payload);
                    }
                }
            } catch (\Exception $e) {
                // Log error but don't stop other listeners
                error_log("Event listener error: " . $e->getMessage());
            }
        }
        
        return true;
    }
    
    /**
     * Remove event listener
     */
    public static function forget($event, $listener = null) {
        if ($listener === null) {
            unset(self::$listeners[$event]);
        } else {
            if (isset(self::$listeners[$event])) {
                self::$listeners[$event] = array_filter(self::$listeners[$event], function($l) use ($listener) {
                    return $l !== $listener;
                });
            }
        }
    }
    
    /**
     * Get event log
     */
    public static function getEventLog($limit = 100) {
        return array_slice(array_reverse(self::$eventLog), 0, $limit);
    }
    
    /**
     * Clear event log
     */
    public static function clearEventLog() {
        self::$eventLog = [];
    }
}

/**
 * Helper function to register event listener
 */
function event_listen($event, $listener) {
    EventDispatcher::listen($event, $listener);
}

/**
 * Helper function to dispatch event
 */
function event_dispatch($event, $payload = []) {
    return EventDispatcher::dispatch($event, $payload);
}
?>
