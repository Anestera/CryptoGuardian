<?php
require 'db_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: sign.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$password = $_POST['password'];

if (empty($password)) {
    die("Password is required.");
}

// Генерация пары ключей
$config = array(
    "digest_alg" => "sha256",
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
);

$res = openssl_pkey_new($config);
openssl_pkey_export($res, $private_key);
$public_key = openssl_pkey_get_details($res)['key'];

// Шифрование закрытого ключа
$encryption_key = hash('sha256', $password, true);
$iv = openssl_random_pseudo_bytes(16);
$encrypted_private_key = openssl_encrypt($private_key, 'aes-256-cbc', $encryption_key, 0, $iv);

// Сохранение ключей в базе данных
$stmt = $pdo->prepare("INSERT INTO user_keys (user_id, public_key, private_key, iv) VALUES (?, ?, ?, ?)
                       ON CONFLICT (user_id) DO UPDATE SET public_key = EXCLUDED.public_key, private_key = EXCLUDED.private_key, iv = EXCLUDED.iv");
$stmt->execute([$user_id, $public_key, $encrypted_private_key, base64_encode($iv)]);

header("Location: personal_account.php");
exit();
?>
