<?php
require 'db_connection.php';

// Обработка входа пользователя
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Получение хешированного пароля из базы данных
        $stmt = $pdo->prepare("SELECT user_id, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Устанавливаем user_id в сессии
            session_start();
            $_SESSION['user_id'] = $user['user_id'];

            // Редирект на страницу личного кабинета
            header("Location: personal_area.php");
            exit();
        } else {
            // Неверный email или пароль
            $login_error = "Неверный email или пароль.";
        }
    } else {
        // Неверный формат email
        $login_error = "Неверный формат email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/normalize.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join us</title>
</head>
<body>
<div class="wrapper_sign">
    <img class="sign_img" src="img/sign_photo.png" alt="a">
    <a href="/" class="sign-link">CryptoGuardianU</a>
</div>
<div class="container_sign">
    <input type="checkbox" id="check">
    <div class="login form">
        <header>Sign in</header>
        <?php if (isset($login_error)): ?>
            <p class="error-message"><?php echo $login_error; ?></p>
        <?php endif; ?>

        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <input type="email" name="email" placeholder="Enter your email" required>
            <input type="password" name="password" placeholder="Enter your password" required>
            <a href="forgot.php">Forgot password?</a>
            <input type="submit" class="button" name="login" value="Login">
        </form>
        <div class="signup">
            <span class="signup">Don't have an account?
             <label for="check"> <a href="sing_up.php">Sign up</a></label>
            </span>
        </div>
    </div>
</div>
</body>
</html>