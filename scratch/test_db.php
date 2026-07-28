<?php
try {
    $pdo = new \PDO('mysql:host=localhost;port=3306;dbname=sinala3', 'root', '');
    echo "Connected successfully to localhost!\n";
} catch (\Exception $e) {
    echo "Failed to localhost: " . $e->getMessage() . "\n";
}
try {
    $pdo = new \PDO('mysql:host=127.0.0.1;port=3306;dbname=sinala3', 'root', '');
    echo "Connected successfully to 127.0.0.1!\n";
} catch (\Exception $e) {
    echo "Failed to 127.0.0.1: " . $e->getMessage() . "\n";
}
