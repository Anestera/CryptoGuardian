<?php
// Подключение к базе данных
$host = 'localhost'; // Хост базы данных
$port = '5432'; // Порт базы данных
$dbname = 'Users';
$username = 'postgres';
$password = '123';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>