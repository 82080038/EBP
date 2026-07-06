<?php
/**
 * Fix indentation issues in Services files
 */

$modulesDir = __DIR__ . '/modules';

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($modulesDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

$filesFixed = 0;

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php' || strpos($file->getPathname(), 'Service.php') === false) {
        continue;
    }

    $filePath = $file->getPathname();
    $content = file_get_contents($filePath);
    $originalContent = $content;
    $modified = false;

    // Fix \n literal in PDO block
    $content = str_replace('\n        $host', "\n        \$host", $content);
    $content = str_replace('\n        $dbname', "\n        \$dbname", $content);
    $content = str_replace('\n        $username', "\n        \$username", $content);
    $content = str_replace('\n        $password', "\n        \$password", $content);
    $content = str_replace('\n        $socket', "\n        \$socket", $content);
    $content = str_replace('\n        $dsn', "\n        \$dsn", $content);
    $content = str_replace('\n        $this->db', "\n        \$this->db", $content);
    $content = str_replace('\n        $this->db->setAttribute', "\n        \$this->db->setAttribute", $content);

    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        $filesFixed++;
        echo "Fixed: $filePath\n";
    }
}

echo "\n=== Summary ===\n";
echo "Files fixed: $filesFixed\n";
