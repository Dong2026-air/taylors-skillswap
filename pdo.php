<?php

// Database connection settings
$host = 'localhost';
$port = '3306';
$dbname = 'campusconnect';
$username = 'root';
$password = '';

try {

    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    // Show database errors while developing
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {

    die("Database connection failed: " . $e->getMessage());

}

?>