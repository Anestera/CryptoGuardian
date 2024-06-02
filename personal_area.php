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

// Ensure the photo data is a string, not a resource
$photo = '';
if ($user['photo']) {
    // Check if $user['photo'] is a string or resource
    if (is_string($user['photo'])) {
        $photo = 'data:image/jpeg;base64,' . base64_encode($user['photo']);
    } else {
        // If it's a resource, fetch its contents as a string
        $photo = 'data:image/jpeg;base64,' . base64_encode(stream_get_contents($user['photo']));
    }
} else {
    $photo = 'img/default-profile.png';
}

// Получаем список документов пользователя
$documents_stmt = $pdo->prepare("SELECT file_id, filename FROM files WHERE user_id = ?");
$documents_stmt->execute([$user_id]);
$documents = $documents_stmt->fetchAll(PDO::FETCH_ASSOC);

// Получение сообщений пользователя
$messages_stmt = $pdo->prepare("
    SELECT messages.message, messages.file_id, users.username AS sender 
    FROM messages 
    JOIN users ON messages.sender_id = users.user_id 
    WHERE messages.receiver_id = ?
");
$messages_stmt->execute([$user_id]);
$messages = $messages_stmt->fetchAll(PDO::FETCH_ASSOC);

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
            <button class="tab-link" onclick="openTab(event, 'my-messages')">My messages</button>
            <button class="tab-link" onclick="openTab(event, 'add-friend-send-message')">Add Friend / Send Message</button>
        </div>

        <div id="personal-account" class="area-tab-content active">
            <h2>Personal Account</h2>
            <form action="update_profile.php" method="post" enctype="multipart/form-data" class="profile-update-form">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                
                <label for="profile-picture-upload" class="btn choose-file-btn">Choose File</label>
                <input type="file" name="profile-picture" id="profile-picture-upload">
                
                <input type="submit" value="Update Profile" class="btn upload-btn">
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
                
                <input type="file" name="file" id="file-upload" required>
                <label for="password" class="btn password-label">Enter Password</label>
                <input type="password" name="password" id="password" required>
                <button type="submit" class="btn upload-btn">Upload and Sign</button>
            </form>
        </div>

        <div id="signature-verification" class="area-tab-content">
            <h2>Signature Verification</h2>
            <form action="verify_file.php" method="post" enctype="multipart/form-data">
                
                <input type="file" name="file" id="verify-file-upload" required>
                <button type="submit" class="btn upload-btn">Upload and Verify</button>
            </form>
        </div>

        <div id="my-messages" class="area-tab-content">
            <h2>My Messages</h2>
            <?php if ($messages): ?>
                <ul>
                    <?php foreach ($messages as $message): ?>
                        <li>
                            <strong>From:</strong> <?= htmlspecialchars($message['sender']) ?><br>
                            <strong>Message:</strong> <?= htmlspecialchars($message['message']) ?><br>
                            <?php if ($message['file_id']): ?>
                                <a href="download.php?file_id=<?= htmlspecialchars($message['file_id']) ?>" class="btn download-btn">Download Attachment</a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No messages found.</p>
            <?php endif; ?>
        </div>

        <div id="add-friend-send-message" class="area-tab-content">
            <h2>Add a Friend</h2>
            <form action="add_friend.php" method="post">
                <input type="text" name="friend_username" placeholder="Enter friend's username" required>
                <button type="submit">Add Friend</button>
            </form>

            <h2>Send a Message</h2>
            <form id="messageForm" enctype="multipart/form-data" action="send_messages.php" method="post">
                <input type="text" id="message" name="message" placeholder="Enter your message">
                <input type="file" id="file" name="file">
                <select name="receiver_id" id="receiver_id">
                    <?php
                    // Получаем список друзей пользователя
                    $stmt = $pdo->prepare("SELECT users.user_id, users.username FROM friends JOIN users ON friends.friend_id = users.user_id WHERE friends.user_id = ?");
                    $stmt->execute([$user_id]);
                    $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($friends as $friend) {
                        echo "<option value=\"{$friend['user_id']}\">{$friend['username']}</option>";
                    }
                    ?>
                </select>
                <input type="password" name="password" placeholder="Enter your password" required>
                <button type="submit">Send</button>
            </form>
        </div>

        <form id="logout-form" action="logout.php" method="post">
            <button type="submit" class="btn logout-btn">Logout</button>
        </form>
    </div>

    <script src="js/main.js"></script>
    <script src="js/chat.js"></script>
</body>
</html>
