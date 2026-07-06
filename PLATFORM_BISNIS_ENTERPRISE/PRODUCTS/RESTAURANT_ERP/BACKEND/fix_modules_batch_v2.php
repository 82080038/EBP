<?php
/**
 * Batch fix script v2 - handle remaining patterns
 * - Fix (new Database())->connect() pattern
 * - Fix Response::success parameter order
 * - Fix indentation issues
 */

$modulesDir = __DIR__ . '/modules';

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($modulesDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

$filesFixed = 0;
$filesSkipped = 0;

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }

    $filePath = $file->getPathname();
    $content = file_get_contents($filePath);
    $originalContent = $content;
    $modified = false;

    // Fix 1: Replace (new Database())->connect() pattern
    if (preg_match('/\$this->db\s*=\s*\(new\s+Database\(\)\)->connect\(\);/', $content)) {
        $content = preg_replace(
            '/\$this->db\s*=\s*\(new\s+Database\(\)\)->connect\(\);/',
            '$host = \'localhost\';
        $dbname = \'ebp_restaurant_db\';
        $username = \'ebp_app\';
        $password = \'ebp_secure_password_2026\';
        $socket = \'/opt/lampp/var/mysql/mysql.sock\';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);',
            $content
        );
        $modified = true;
    }

    // Fix 2: Fix Response::success parameter order (data first, message second)
    // Pattern: Response::success($message, $data) -> Response::success($data, $message)
    if (preg_match('/Response::success\(\$result\[\'message\'\],\s*\[/', $content)) {
        $content = preg_replace(
            '/Response::success\(\$result\[\'message\'\],\s*\[([^\]]+)\]\)/',
            'Response::success([$1], $result[\'message\'])',
            $content
        );
        if ($content !== $originalContent) {
            $modified = true;
        }
    }

    // Fix 3: Fix Response::success with single array parameter
    if (preg_match('/Response::success\(\$result\[\'message\'\],\s*array\(/', $content)) {
        $content = preg_replace(
            '/Response::success\(\$result\[\'message\'\],\s*array\(([^)]+)\)\)/',
            'Response::success([$1], $result[\'message\'])',
            $content
        );
        if ($content !== $originalContent) {
            $modified = true;
        }
    }

    // Fix 4: Fix indentation in Services (PDO block)
    if (strpos($filePath, 'Service.php') !== false) {
        $content = preg_replace(
            '/(\$this->repository\s*=\s*new\s+\w+Repository\(\);\s*)(\$host)/',
            '$1\n        $2',
            $content
        );
        if ($content !== $originalContent) {
            $modified = true;
        }
    }

    if ($modified) {
        file_put_contents($filePath, $content);
        $filesFixed++;
        echo "Fixed: $filePath\n";
    } else {
        $filesSkipped++;
    }
}

echo "\n=== Summary ===\n";
echo "Files fixed: $filesFixed\n";
echo "Files skipped: $filesSkipped\n";
