<?php
require 'db_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: sign.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$password = $_POST['password'];
$file = $_FILES['file'];

if (empty($password)) {
    die("Password is required.");
}

if ($file['error'] !== UPLOAD_ERR_OK) {
    die("File upload error.");
}

$file_content = file_get_contents($file['tmp_name']);
$filename = $file['name'];

// Получение закрытого ключа пользователя из базы данных
$stmt = $pdo->prepare("SELECT private_key, iv FROM user_keys WHERE user_id = ?");
$stmt->execute([$user_id]);
$key_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$key_data) {
    die("User keys not found.");
}

$encrypted_private_key = $key_data['private_key'];
$iv = base64_decode($key_data['iv']);
$encryption_key = hash('sha256', $password, true);

// Дешифрование закрытого ключа
$private_key = openssl_decrypt($encrypted_private_key, 'aes-256-cbc', $encryption_key, 0, $iv);
if (!$private_key) {
    die("Unable to decrypt private key.");
}

$private_key_resource = openssl_pkey_get_private($private_key);

if (!$private_key_resource) {
    die("Unable to get private key.");
}

openssl_sign($file_content, $signature, $private_key_resource, OPENSSL_ALGO_SHA256);
openssl_free_key($private_key_resource);

$signature_base64 = base64_encode($signature);

// Сохранение файла и подписи в базе данных
$stmt = $pdo->prepare("INSERT INTO files (user_id, filename, source_file, signed_file, signature) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$user_id, $filename, $file_content, $file_content, $signature_base64]);

header("Location: personal_account.php");
exit();
?>
