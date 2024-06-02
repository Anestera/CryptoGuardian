<?php
require 'db_connection.php';
require 'audit_log.php';
require 'send_email.php';
session_start();

if (!isset($_SESSION['user_id'])) {
header("Location: sign.php");
exit();
}

$user_id = $_SESSION['user_id'];
$username = $_POST['username'];
$email = $_POST['email'];
$profile_picture = $_FILES['profile-picture']['tmp_name'];

// Check if email is changed and handle email confirmation
$stmt = $pdo->prepare("SELECT email FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$current_email = $stmt->fetchColumn();

if ($email !== $current_email) {
$verification_token = bin2hex(random_bytes(16));
$stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, email_verified = FALSE, verification_token = ? WHERE user_id = ?");
$stmt->execute([$username, $email, $verification_token, $user_id]);

$verification_link = "http://cryptoguardian/verify_email.php?token=$verification_token";
$subject = "Email Verification";
$message = "Hello $username,\n\nPlease click the link below to verify your email address:\n$verification_link";
send_email($email, $subject, $message);

log_action($user_id, 'update_profile', 'User updated profile and email verification sent');

// Handle key regeneration
$stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$hashed_password = $stmt->fetchColumn();

$user_id = $pdo->lastInsertId(); 
            $config = array(
                "digest_alg" => "sha256",
                "private_key_bits" => 2048,
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
            );

            $res = openssl_pkey_new($config);
            openssl_pkey_export($res, $private_key);
            $public_key = openssl_pkey_get_details($res)['key'];
            $encryption_key = hash('sha256', $password, true);
            $iv = openssl_random_pseudo_bytes(16);
            $encrypted_private_key = openssl_encrypt($private_key, 'aes-256-cbc', $encryption_key, 0, $iv);

$stmt = $pdo->prepare("UPDATE user_keys SET public_key = ?, private_key = ?, iv = ? WHERE user_id = ?");
$stmt->execute([$public_key, $encrypted_private_key, base64_encode($iv), $user_id]);
} else {
$stmt = $pdo->prepare("UPDATE users SET username = ? WHERE user_id = ?");
$stmt->execute([$username, $user_id]);

log_action($user_id, 'update_profile', 'User updated profile');
}

if ($profile_picture) {
$profile_picture = $_FILES['profile-picture']['tmp_name'];
$fileType = $_FILES['profile-picture']['type'];
$fileContent = file_get_contents($profile_picture);

// Проверка, является ли файл изображение
if (strpos($fileType, 'image') === false) {
die("Файл не является изображением.");
}

// Обновление фото в базе данных
$stmt = $pdo->prepare("UPDATE users SET photo = ? WHERE user_id = ?");
$stmt->bindParam(1, $fileContent, PDO::PARAM_LOB);
$stmt->bindParam(2, $user_id, PDO::PARAM_INT);
$stmt->execute();
}

header("Location: personal_area.php");
exit();
?>