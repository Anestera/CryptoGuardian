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

// Получаем список документов пользователя
$documents_stmt = $pdo->prepare("SELECT file_id, filename FROM files WHERE user_id = ?");
$documents_stmt->execute([$user_id]);
$documents = $documents_stmt->fetchAll(PDO::FETCH_ASSOC);

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
                <h1 id="username">Hello, <?= htmlspecialchars($user['username']) ?>!</h1>
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
            <form action="upload.php" method="post" enctype="multipart/form-data" class="profile-picture-form">
                <label for="profile-picture-upload" class="btn choose-file-btn">Choose File</label>
                <input type="file" name="profile-picture" id="profile-picture-upload">
                <input type="submit" value="Upload" class="btn upload-btn">
            </form>
            <form action="generate_keys.php" method="post">
                <label for="password" class="btn password-label">Enter Password to Generate Keys</label>
                <input type="password" name="password" id="password" required>
                <button type="submit" class="btn upload-btn">Generate Keys</button>
            </form>
        </div>

        <div id="my-documents" class="area-tab-content">
            <h2>My Documents</h2>
            <?php if ($documents): ?>
    <ul>
        <?php foreach ($documents as $doc): ?>
            <li>
                <?= htmlspecialchars($doc['filename']) ?>
                <a href="download.php?file_id=<?= htmlspecialchars($doc['file_id']) ?>" class="btn download-btn">Download</a>
            </li>
        <?php endforeach; ?>
    </ul>
        <?php else: ?>
            <p>No documents found.</p>
        <?php endif; ?>
        </div>

        <div id="creating-signature" class="area-tab-content">
    <h2>Creating a Signature</h2>
    <form action="sign_file.php" method="post" enctype="multipart/form-data">
        <label for="file-upload" class="btn choose-file-btn">Choose File</label>
        <input type="file" name="file" id="file-upload" required>
        <label for="password" class="btn password-label">Enter Password</label>
        <input type="password" name="password" id="password" required>
        <button type="submit" class="btn upload-btn">Upload and Sign</button>
    </form>
</div>

        <div id="signature-verification" class="area-tab-content">
            <h2>Signature Verification</h2>
            <form action="verify_file.php" method="post" enctype="multipart/form-data">
                <label for="verify-file-upload" class="btn choose-file-btn">Choose File</label>
                <input type="file" name="file" id="verify-file-upload" required>
                <button type="submit" class="btn upload-btn">Upload and Verify</button>
            </form>
        </div>

        <form id="logout-form" action="logout.php" method="post">
            <button type="submit" class="btn logout-btn">Logout</button>
        </form>
    </div>

    <script src="js/main.js"></script>
</body>
</html>
