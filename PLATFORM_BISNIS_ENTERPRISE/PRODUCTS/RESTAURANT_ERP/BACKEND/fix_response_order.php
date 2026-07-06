<?php
/**
 * Fix Response::success parameter order in all controllers
 * Pattern: Response::success($message, $data) -> Response::success($data, $message)
 */

$modulesDir = __DIR__ . '/modules';

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($modulesDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

$filesFixed = 0;
$filesSkipped = 0;

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php' || strpos($file->getPathname(), 'Controller.php') === false) {
        continue;
    }

    $filePath = $file->getPathname();
    $content = file_get_contents($filePath);
    $originalContent = $content;
    $modified = false;

    // Pattern 1: Response::success($result['message'], ['key' => $value])
    if (preg_match('/Response::success\(\$result\[\'message\'\],\s*\[([^\]]+)\]\)/', $content)) {
        $content = preg_replace(
            '/Response::success\(\$result\[\'message\'\],\s*\[([^\]]+)\]\)/',
            'Response::success([$1], $result[\'message\'])',
            $content
        );
        if ($content !== $originalContent) {
            $modified = true;
        }
    }

    // Pattern 2: Response::success($result['message'], array('key' => $value))
    if (preg_match('/Response::success\(\$result\[\'message\'\],\s*array\(([^)]+)\)\)/', $content)) {
        $content = preg_replace(
            '/Response::success\(\$result\[\'message\'\],\s*array\(([^)]+)\)\)/',
            'Response::success([$1], $result[\'message\'])',
            $content
        );
        if ($content !== $originalContent) {
            $modified = true;
        }
    }

    // Pattern 3: Response::success($message, $data) where $message is a string literal
    if (preg_match('/Response::success\((["\'])([^"\']+)\1,\s*\[([^\]]+)\]\)/', $content)) {
        $content = preg_replace(
            '/Response::success\((["\'])([^"\']+)\1,\s*\[([^\]]+)\]\)/',
            'Response::success([$3], \'$2\')',
            $content
        );
        if ($content !== $originalContent) {
            $modified = true;
        }
    }

    // Pattern 4: Response::success($message, $result['data'])
    if (preg_match('/Response::success\(\$result\[\'message\'\],\s*\$result\[\'data\'\]\)/', $content)) {
        $content = preg_replace(
            '/Response::success\(\$result\[\'message\'\],\s*\$result\[\'data\'\]\)/',
            'Response::success($result[\'data\'], $result[\'message\'])',
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
