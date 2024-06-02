<?php
require 'db_connection.php';
require 'audit_log.php';
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

// Получаем данные о файле из базы данных
$stmt = $pdo->prepare("SELECT signed_by, signature FROM files WHERE filename = ?");
$stmt->execute([$filename]);
$file_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file_data) {
    die("File not found in the database.");
}

$signed_by = $file_data['signed_by'];
$signature_base64 = $file_data['signature'];
$signature = base64_decode($signature_base64);


if ($_SESSION['user_id'] != $signed_by) {
    $check_stmt = $pdo->prepare("SELECT * FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
    $check_stmt->execute([$_SESSION['user_id'], $signed_by, $signed_by, $_SESSION['user_id']]);
    $is_friend = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$is_friend) {
        die("Айяйяй, нельзя проверять неотправленные тебе документы.");
    }
}


// Получаем открытый ключ отправителя из базы данных
$stmt = $pdo->prepare("SELECT public_key FROM user_keys WHERE user_id = ?");
$stmt->execute([$signed_by]);
$key_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$key_data) {
    die("Sender's public key not found.");
}

$public_key = $key_data['public_key'];
$public_key_resource = openssl_pkey_get_public($public_key);

// Проверяем подпись
$ok = openssl_verify($file_content, $signature, $public_key_resource, OPENSSL_ALGO_SHA256);

if ($ok == 1) {
    echo "Signature is valid.";
    log_action($_SESSION['user_id'], 'verify_signature', "Verified signature for file: $filename");
} elseif ($ok == 0) {
    echo "Signature is invalid.";
    log_action($_SESSION['user_id'], 'verify_signature', "Failed to verify signature for file: $filename");
} else {
    echo "Error verifying signature.";
    log_action($_SESSION['user_id'], 'verify_signature', "Error verifying signature for file: $filename");
}
?>
