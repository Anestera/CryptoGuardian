<?php
// Подключение к базе данных
$host = 'localhost'; // Хост базы данных
$port = '3306'; // Порт базы данных
$dbname = 'nastak6n_crypto';
$username = 'nastak6n_crypto';
$password = 'Lola5154Dryg9!';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
    }
?>