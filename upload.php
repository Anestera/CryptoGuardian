<?php
require 'db_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
 header("Location: sign.php");
 exit();
}

// Обработка загрузки файла
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile-picture'])) {
    $user_id = $_SESSION['user_id'];
    $file = $_FILES['profile-picture']['tmp_name'];
    $fileType = $_FILES['profile-picture']['type'];
    $fileContent = file_get_contents($file);

    // Проверка, является ли файл изображение
    if (strpos($fileType, 'image') === false) {
 die("Файл не является изображением.");
    }

    // Обновление фото в базе данных
    $stmt = $pdo->prepare("UPDATE users SET photo = ? WHERE user_id = ?");
    $stmt->bindParam(1, $fileContent, PDO::PARAM_LOB);
    $stmt->bindParam(2, $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // Редирект на страницу личного кабинета
 header("Location: personal_area.php");
}
?>
