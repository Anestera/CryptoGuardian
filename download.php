<?php
//скачивание файлов
require 'db_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: sign.php");
    exit();
}


$file_id = $_GET['file_id'];
$stmt = $pdo->prepare("SELECT filename, signed_file FROM files WHERE file_id = ?");
$stmt->execute([$file_id]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file) {
    die("Файл не найден.");
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $file['filename'] . '"');
echo $file['signed_file'];
exit();
?>
