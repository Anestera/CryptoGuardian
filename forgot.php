<?php
// Подключение к базе данных 
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];

    // Проверка наличия пользователя с такой электронной почтой
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $user_id = $user['user_id'];
        $expiry_time = time() + 3600; // Токен действителен в течение 1 часа

        // Сохранение времени окончания действия токена в базе данных
        $stmt = $pdo->prepare("UPDATE users SET reset_token_expiry = FROM_UNIXTIME(?) WHERE user_id = ?");
        $stmt->execute([$expiry_time, $user_id]);

        // Отправка письма пользователю
        $reset_link = "http://cryptoguardianu.ru/reset.php?user_id=$user_id&expiry=$expiry_time";
        $subject = "Восстановление пароля";
        $message = "Для восстановления пароля перейдите по следующей ссылке: $reset_link";
        $headers = "From: cryptoguardian-support@cryptoguardianu.ru";

        if (mail($email, $subject, $message, $headers)) {
            echo "<script>alert('Письмо для восстановления пароля было отправлено на вашу электронную почту.');window.location.href = 'sign.php'</script>";
        } else {
            echo "<script>alert('Не удалось отправить письмо. Попробуйте снова.');window.location.href = 'sign.php'</script>";
        }
    } else {
        echo "<script>alert('Пользователь с такой электронной почтой не найден.');window.location.href = 'sign.php'</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/normalize.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password recovery</title>
</head>
<body>
<div class="wrapper_sign">
    <img class="sign_img" src="img/sign_photo.png" alt="a">
    <a href="/" class="sign-link">CryptoGuardianU</a>
</div>
<div class="container_sign">
    <input type="checkbox" id="check">
    <div class="login form">
        <header>Password recovery</header>
        <form action="" method="POST">
            <input type="text" name="email" placeholder="Enter your email" required>
            <input type="submit" name="send" class="button" value="Send">
        </form>
    </div>
</div>
</body>
</html>
