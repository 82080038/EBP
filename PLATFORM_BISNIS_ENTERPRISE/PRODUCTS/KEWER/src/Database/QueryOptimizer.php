<?php
namespace Kewer\Database;

use Kewer\Logging\Logger;

class QueryOptimizer {
    private static $queryLog = [];
    private static $slowQueryThreshold = 1.0; // seconds
    
    /**
     * Log query execution time
     */
    public static function logQuery($sql, $params, $executionTime) {
        self::$queryLog[] = [
            'sql' => $sql,
            'params' => $params,
            'execution_time' => $executionTime,
            'timestamp' => microtime(true)
        ];
        
        // Log slow queries
        if ($executionTime > self::$slowQueryThreshold) {
            Logger::warning("Slow Query Detected", [
                'sql' => $sql,
                'execution_time' => $executionTime,
                'threshold' => self::$slowQueryThreshold
            ]);
        }
        
        Logger::query($sql, $params, $executionTime);
    }
    
    /**
     * Get query statistics
     */
    public static function getQueryStats() {
        if (empty(self::$queryLog)) {
            return [
                'total_queries' => 0,
                'total_time' => 0,
                'avg_time' => 0,
                'slow_queries' => 0,
                'queries' => []
            ];
        }
        
        $totalQueries = count(self::$queryLog);
        $totalTime = array_sum(array_column(self::$queryLog, 'execution_time'));
        $avgTime = $totalTime / $totalQueries;
        $slowQueries = count(array_filter(self::$queryLog, function($q) {
            return $q['execution_time'] > self::$slowQueryThreshold;
        }));
        
        return [
            'total_queries' => $totalQueries,
            'total_time' => $totalTime,
            'avg_time' => $avgTime,
            'slow_queries' => $slowQueries,
            'queries' => self::$queryLog
        ];
    }
    
    /**
     * Suggest indexes for slow queries
     */
    public static function suggestIndexes($slowQueries) {
        $suggestions = [];
        
        foreach ($slowQueries as $query) {
            $sql = $query['sql'];
            
            // Detect WHERE clauses
            if (preg_match('/WHERE\s+([^\s]+)\s*=/i', $sql, $matches)) {
                $column = $matches[1];
                $table = self::extractTableName($sql);
                
                if ($table && $column) {
                    $suggestions[] = [
                        'table' => $table,
                        'column' => $column,
                        'suggestion' => "Consider adding index on {$table}.{$column}",
                        'query' => $sql
                    ];
                }
            }
            
            // Detect JOIN conditions
            if (preg_match('/JOIN\s+(\w+)\s+ON\s+([^\s]+)/i', $sql, $matches)) {
                $table = $matches[1];
                $condition = $matches[2];
                
                if (preg_match('/(\w+)\.(\w+)/', $condition, $colMatches)) {
                    $suggestions[] = [
                        'table' => $table,
                        'column' => $colMatches[2],
                        'suggestion' => "Consider adding index on {$table}.{$colMatches[2]} for JOIN optimization",
                        'query' => $sql
                    ];
                }
            }
        }
        
        return $suggestions;
    }
    
    /**
     * Extract table name from SQL query
     */
    private static function extractTableName($sql) {
        if (preg_match('/FROM\s+(\w+)/i', $sql, $matches)) {
            return $matches[1];
        }
        
        if (preg_match('/UPDATE\s+(\w+)/i', $sql, $matches)) {
            return $matches[1];
        }
        
        if (preg_match('/INSERT\s+INTO\s+(\w+)/i', $sql, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * Optimize query by adding hints
     */
    public static function optimizeQuery($sql) {
        // Add USE INDEX hint for simple queries
        if (preg_match('/SELECT.*FROM\s+(\w+)\s+WHERE/i', $sql, $matches)) {
            $table = $matches[1];
            // This is a simple heuristic - in production, analyze actual indexes
            return preg_replace(
                '/SELECT/i',
                'SELECT /*+ USE INDEX(' . $table . ') */',
                $sql,
                1
            );
        }
        
        return $sql;
    }
    
    /**
     * Clear query log
     */
    public static function clearLog() {
        self::$queryLog = [];
    }
}
?>
