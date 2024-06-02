<?php
require 'db_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: sign.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $friend_username = $_POST['friend_username'];

    // Проверка существования пользователя с таким username
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->execute([$friend_username]);
    $friend = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($friend) {
        $friend_id = $friend['user_id'];

        // Проверка, не являются ли уже друзьями
        $check_stmt = $pdo->prepare("SELECT * FROM friends WHERE user_id = ? AND friend_id = ?");
        $check_stmt->execute([$user_id, $friend_id]);
        $already_friends = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$already_friends) {
            // Добавление в таблицу friends
            $insert_stmt = $pdo->prepare("INSERT INTO friends (user_id, friend_id) VALUES (?, ?)");
            $insert_stmt->execute([$user_id, $friend_id]);

            echo "Friend added successfully.";
        } else {
            echo "You are already friends.";
        }
    } else {
        echo "User not found.";
    }
} else {
    echo "Invalid request.";
}
?>
