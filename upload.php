<?php
require 'db_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: sign.php");
    exit();
}

// Обработка загрузки файла
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['profile-picture']) && $_FILES['profile-picture']['error'] == UPLOAD_ERR_OK) {
        $user_id = $_SESSION['user_id'];
        $file = $_FILES['profile-picture']['tmp_name'];
        $fileType = $_FILES['profile-picture']['type'];

        // Проверка, является ли файл изображением
        if (strpos($fileType, 'image') === false) {
            die("Файл не является изображением.");
        }

        // Проверка размеров изображения (не более 2MB)
        if ($_FILES['profile-picture']['size'] > 2 * 1024 * 1024) {
            die("Размер файла слишком большой. Максимальный размер: 2MB.");
        }

        // Создание изображения из файла
        $image = imagecreatefromstring(file_get_contents($file));
        if (!$image) {
            die("Ошибка обработки изображения.");
        }

        // Сжатие и сохранение изображения в строку
        ob_start();
        if ($fileType == 'image/jpeg') {
            imagejpeg($image, null, 50); // 50 - уровень качества JPEG для максимального сжатия
        } elseif ($fileType == 'image/png') {
            imagepng($image, null, 9); // 9 - максимальное сжатие для PNG
        } elseif ($fileType == 'image/gif') {
            imagegif($image); // GIF не поддерживает уровни сжатия
        }
        $compressedImageContent = ob_get_clean();

        // Освобождение памяти, используемой для изображения
        imagedestroy($image);

        // Обновление фото в базе данных
        $stmt = $pdo->prepare("UPDATE users SET photo = ? WHERE user_id = ?");
        $stmt->bindParam(1, $compressedImageContent, PDO::PARAM_LOB);
        $stmt->bindParam(2, $user_id, PDO::PARAM_INT);
        $stmt->execute();

        // Редирект на страницу личного кабинета
        header("Location: personal_area.php");
        exit();
    } else {
        echo "<script>alert('Добавьте фото для загрузки.');window.location.href = 'personal_area.php'</script>";
    }
}
?>
