<?php
require 'db_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: sign.php");
    exit();
}

// Получаем данные пользователя из базы данных
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, email, photo FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Пользователь не найден.");
}

$photo = $user['photo'] ? 'data:image/jpeg;base64,' . base64_encode(stream_get_contents($user['photo'])) : 'default-profile.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/normalize.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Account</title>
</head>
<body class="personal_area">
    <div class="personalarea_container">
        <div class="profile-header" id="profile-header">
            <img src="<?= htmlspecialchars($photo) ?>" alt="Profile Picture" id="profile-picture">
            <div>
                <h1 id="username"><?= htmlspecialchars($user['username']) ?></h1>
                <p id="email"><?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>

        <div class="area_tabs">
            <button class="tab-link active" onclick="openTab(event, 'personal-account')">Personal account</button>
            <button class="tab-link" onclick="openTab(event, 'my-documents')">My documents</button>
            <button class="tab-link" onclick="openTab(event, 'creating-signature')">Creating a signature</button>
            <button class="tab-link" onclick="openTab(event, 'signature-verification')">Signature verification</button>
        </div>

        <div id="personal-account" class="area-tab-content active">
            <h2>Personal Account</h2>
            <p>Username: <?= htmlspecialchars($user['username']) ?></p>
            <p>Email: <?= htmlspecialchars($user['email']) ?></p>
            <form action="upload.php" method="post" enctype="multipart/form-data">
                <label for="profile-picture">Upload Profile Picture:</label>
                <input type="file" name="profile-picture" id="profile-picture">
                <input type="submit" value="Upload">
            </form>
        </div>
        <div id="my-documents" class="area-tab-content">
            <h2>My Documents</h2>
            <p>Content for my documents.</p>
        </div>
        <div id="creating-signature" class="area-tab-content">
            <h2>Creating a Signature</h2>
            <p>Content for creating a signature.</p>
        </div>
        <div id="signature-verification" class="area-tab-content">
            <h2>Signature Verification</h2>
            <p>Content for signature verification.</p>
        </div>
    </div>

    <script src="js/main.js">
    </script>
</body>
</html>
