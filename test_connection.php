<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pdo = require 'config.php';

if (!$pdo instanceof PDO) {
    die("Database connection failed. Check config.php for errors.");
}

try {
    $stmt = $pdo->query("SELECT NOW()");
    $row = $stmt->fetch();
    echo "Database connected! Current time: " . $row['NOW()'];
} catch (PDOException $e) {
    echo "DB error: " . $e->getMessage();
}
