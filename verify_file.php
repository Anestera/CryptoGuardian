<?php
require 'db_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: sign.php");
    exit();
}

$file = $_FILES['file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    die("File upload error.");
}

$file_content = file_get_contents($file['tmp_name']);
$filename = $file['name'];

// Получение публичного ключа из базы данных
$stmt = $pdo->prepare("SELECT public_key FROM user_keys WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$key_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$key_data) {
    die("User keys not found.");
}

$public_key = $key_data['public_key'];

// Получение соответствующей подписи из базы данных
$stmt = $pdo->prepare("SELECT signature FROM files WHERE filename = ?");
$stmt->execute([$filename]);
$file_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file_data) {
    die("File not found in the database.");
}

$signature_base64 = $file_data['signature'];
$signature = base64_decode($signature_base64);

$public_key_resource = openssl_pkey_get_public($public_key);
$ok = openssl_verify($file_content, $signature, $public_key_resource, OPENSSL_ALGO_SHA256);


if ($ok == 1) {
    echo "Signature is valid.";
} elseif ($ok == 0) {
    echo "Signature is invalid.";
} else {
    echo "Error verifying signature.";
}
?>
