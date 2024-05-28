<?php 
require "db_connection.php"; 
 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) { 
    $email = $_POST['email']; 
 
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?"); 
    $stmt->execute([$email]); 
    $user = $stmt->fetch(PDO::FETCH_ASSOC); 
 
    if ($user) { 
        $user_id = $user['user_id']; 
        $expiry_time = time() + 3600; // 1 hour 
 
        $stmt = $pdo->prepare("UPDATE users SET reset_token_expiry = to_timestamp(?) WHERE user_id = ?"); 
        if ($stmt->execute([$expiry_time, $user_id])) { 
            $reset_link = "http://CryptoGuardian/reset.php?user_id=$user_id&expiry=$expiry_time"; 
            $subject = "Восстановление пароля"; 
            $message = "Для восстановления пароля перейдите по следующей ссылке: $reset_link"; 
            $headers = "From: no-reply@yourdomain.com"; 
 
            if (mail($email, $subject, $message, $headers)) { 
                header("Location: sign.php");
            } else { 
                echo "Не удалось отправить письмо. Попробуйте снова."; 
            } 
        } else { 
            echo "Не удалось сохранить токен. Попробуйте снова."; 
        } 
    } else { 
        echo "Пользователь с такой электронной почтой не найден."; 
    } 
} 
?> 
 
<!DOCTYPE html> 
<html lang="ru"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Восстановление пароля</title> 
</head> 
<body> 
    <form action="forgot.php" method="post"> 
        <h2>Восстановление пароля</h2> 
        <label for="email">Введите вашу электронную почту:</label> 
        <input type="email" name="email" id="email" required> 
        <input type="submit" value="Отправить"> 
    </form> 
</body> 
</html>