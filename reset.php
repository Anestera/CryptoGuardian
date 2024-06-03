<?php
// Подключение к базе данных
require 'db_connection.php';
require 'audit_log.php';

if (isset($_GET['user_id']) && isset($_GET['expiry'])) {
    $user_id = $_GET['user_id'];
    $expiry = $_GET['expiry'];

    // Проверка валидности токена
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ? AND reset_token_expiry > NOW()");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && time() < $expiry) {
        if (isset($_POST['password'])) {
            $new_password = $_POST['password'];
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Обновление пароля в базе данных
            $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token_expiry = NULL WHERE user_id = ?");
            $stmt->execute([$hashed_password, $user['user_id']]);


            log_action($user['user_id'], 'change_password', 'User changed password');
            

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
            $encryption_key = hash('sha256', $new_password, true);
            $iv = openssl_random_pseudo_bytes(16);
            $encrypted_private_key = openssl_encrypt($private_key, 'aes-256-cbc', $encryption_key, 0, $iv);

            // Обновление ключей
            $stmt = $pdo->prepare("UPDATE user_keys SET public_key = ?, private_key = ?, iv = ? WHERE user_id = ?");
            $stmt->execute([$public_key, $encrypted_private_key, base64_encode($iv), $user['user_id']]);
 
            header('Location: sign.php');
        }
    } else {
        echo "<script>alert('Неверный или просроченный токен.');window.location.href = 'sign.php'</script>";
    }
} else {
    echo "<script>alert('Токен не указан.');window.location.href = 'sign.php'</script>";
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/normalize.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password reset</title>
</head>
<body>
<section>
<div class="wrapper_sign">
    <img class="sign_img" src="img/sign_photo.png" alt="a">
    <a href="/" class="sign-link">CryptoGuardianU</a>
</div>
    <form class="container_sign" action="" method="post">
        <div class="login form">
        <h2>Password reset</h2>
        <input type="password" name="password" id="password" placeholder="Enter new password" required>
        <input class="button" type="submit" value="Password reset">
        </div>
    </form>
</section>
</body>
</html>
