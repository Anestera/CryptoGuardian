<?php 
require 'db_connection.php';

// Стартуем сессию
session_start();

// Обработка регистрации 
if (isset($_POST['signup'])) { 
    $username = $_POST['username']; 
    $email = $_POST['email']; 
    $password = $_POST['password']; 
    $confirm_password = $_POST['confirm_password']; 

    // Проверка минимальной длины пароля и содержания букв, цифр и знаков
    if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password) || !preg_match('/[^A-Za-z\d]/', $password)) {
        $registration_error = "Пароль должен содержать не менее 8 символов, включая буквы, цифры и специальные символы.";
    } elseif ($password !== $confirm_password) {
        $registration_error = "Пароли не совпадают.";
    } else {
        // Проверка наличия пользователя с таким email в базе данных 
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?"); 
        $stmt->execute([$email]); 
        $count = $stmt->fetchColumn(); 
 
        if ($count > 0) { 
            $registration_error = "Пользователь с таким email уже зарегистрирован."; 
        } else { 
            // Хеширование пароля 
            $hashed_password = password_hash($password, PASSWORD_DEFAULT); 

            // Вставка новой записи в таблицу пользователей 
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)"); 
            $stmt->execute([$username, $email, $hashed_password]); 

            // Получаем user_id только что зарегистрированного пользователя 
            $user_id = $pdo->lastInsertId(); 

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
            $stmt = $pdo->prepare("INSERT INTO user_keys (user_id, public_key, private_key, iv) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $public_key, $encrypted_private_key, base64_encode($iv)]);

            // Устанавливаем user_id в сессии 
            $_SESSION['user_id'] = $user_id; 

            // Редирект на страницу успешной регистрации или другие действия 
            header("Location: personal_area.php"); 
            exit(); 
        } 
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
    <title>Sign Up</title>
</head>
<body>
    <div class="wrapper_sign">
        <img class="sign_img" src="img/sign_photo.png" alt="a">
        <a href="/" class="sign-link">CryptoGuardianU</a>
        <div class="container_sign">
            <input type="checkbox" id="check">
            <div class="registration form">
                <header>Sign up</header>
                <?php if (isset($registration_error)): ?> 
                    <p class="error-message"><?php echo $registration_error; ?></p> 
                <?php endif; ?>
                <form action="" method="post"> 
                    <input type="email" name="email" placeholder="Enter your email" required>
                    <input type="text" name="username" placeholder="Enter your nickname" required>
                    <input type="password" name="password" placeholder="Create a password" required>
                    <input type="password" name="confirm_password" placeholder="Confirm your password" required>
                    <?php if (isset($registration_error)): ?> 
                        <p class="error-message"><?php echo $registration_error; ?></p> 
                    <?php endif; ?>
                    <input type="submit" class="button" name="signup" value="Sign up">
                </form>

                <div class="signup">
                    <span class="signup">Already have an account?
                        <label for="check"> <a href="sign.php">Login</a></label>
                    </span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
