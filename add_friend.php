<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: sign.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $friend_username = $_POST['friend_username'];

    try {
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
                if ($insert_stmt->execute([$user_id, $friend_id])) {
                    
                    echo "<script>alert('Friend added successfully.'); window.location.href = 'personal_area.php';</script>";
                    exit(); 
                } else {
                    echo "<script>alert('Failed to add friend.');window.location.href = 'personal_area.php';</script></script>";
                }
            } else {
                echo "<script>alert('You are already friends.');window.location.href = 'personal_area.php';</script></script>";
            }
        } else {
            echo "<script>alert('User not found.');window.location.href = 'personal_area.php';</script></script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Database error: " . $e->getMessage() . "');</script>";
    } catch (Exception $e) {
        echo "<script>alert('General error: " . $e->getMessage() . "');</script>";
    }
} else {
    echo "<script>alert('Invalid request.');window.location.href = 'personal_area.php';</script></script>";
}
?>
