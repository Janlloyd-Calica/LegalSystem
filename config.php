<?php
$host = 'localhost';
$db   = 'secure_library'; // Make sure this matches your DB in phpMyAdmin
$user = 'root';
$pass = ''; // If using XAMPP, password is usually empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    return $pdo;
} catch (PDOException $e) {
    echo "Connection failed in config.php: " . $e->getMessage(); // Add this line
    return null;
}
