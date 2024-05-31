<?php
// Подключение к базе данных
require 'db_connection.php';

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

            header('Location: sign.php');
        }
    } else {
        echo "Неверный или просроченный токен.";
    }
} else {
    echo "Токен не указан.";
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
