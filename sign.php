<?php
require 'db_connection.php';
require 'audit_log.php';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $pdo->prepare("SELECT user_id, password, email_verified FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if (!$user['email_verified']) {
                $login_error = "Email not verified. Please check your email.";
            } else {
                session_start();
                $_SESSION['user_id'] = $user['user_id'];

                log_action($user['user_id'], 'login', 'User logged in');
                
                header("Location: personal_area.php");
                exit();
            }
        } else {
            $login_error = "Invalid email or password.";
        }
    } else {
        $login_error = "Invalid email format.";
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
