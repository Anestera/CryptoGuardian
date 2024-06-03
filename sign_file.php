<?php
require 'db_connection.php';
require 'audit_log.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: sign.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file']) && isset($_POST['password'])) {
    $password = $_POST['password'];
    $file = $_FILES['file'];

    // Проверка загрузки файла
    if ($file['error'] === UPLOAD_ERR_OK) {
        $filename = $file['name'];
        $upload_dir = __DIR__ . '/uploads/';
        
        // Убедимся, что директория существует
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_path = $upload_dir . basename($filename);

        // Сохранение файла в файловую систему
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            $source_file = file_get_contents($file_path);

            // Получение зашифрованного закрытого ключа и iv из базы данных
            $stmt = $pdo->prepare("SELECT private_key, iv FROM user_keys WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $encrypted_private_key = $row['private_key'];
                $iv = base64_decode($row['iv']);

                // Дешифрование закрытого ключа
                $encryption_key = hash('sha256', $password, true);
                $private_key = openssl_decrypt($encrypted_private_key, 'aes-256-cbc', $encryption_key, 0, $iv);

                if ($private_key === false) {
                    die("Не удалось расшифровать закрытый ключ. Проверьте правильность пароля.");
                }

                // Создание подписи
                $private_key_resource = openssl_pkey_get_private($private_key);
                openssl_sign($source_file, $signature, $private_key_resource, OPENSSL_ALGO_SHA256);

                // Сохранение пути к файлу и подписи в базе данных
                $signature_base64 = base64_encode($signature);
                $stmt = $pdo->prepare("INSERT INTO files (user_id, filename, file_path, signature, signed_by) VALUES (?, ?, ?, ?, ?)");
                $stmt->bindParam(1, $user_id);
                $stmt->bindParam(2, $filename);
                $stmt->bindParam(3, $file_path);
                $stmt->bindParam(4, $signature_base64);
                $stmt->bindParam(5, $user_id); // signed_by указывает на того, кто подписал
                $stmt->execute();


                log_action($user_id, 'create_signature', "File signed: $filename");

                echo "<script>alert('Файл успешно подписан и сохранен.');window.location.href = 'personal_area.php'</script>";
            } else {
                die("Не удалось найти закрытый ключ для пользователя.");
            }
        } else {
            die("Ошибка при сохранении файла.");
        }
    } else {
        die("Ошибка загрузки файла.");
    }
} else {
    die("Неправильный запрос.");
}
?>
