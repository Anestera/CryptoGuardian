<?php
require 'db_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT messages.id, messages.message, users.username AS sender, messages.file_id
    FROM messages
    LEFT JOIN users ON messages.sender_id = users.user_id
    WHERE messages.receiver_id = ? OR messages.sender_id = ?
    ORDER BY messages.timestamp ASC
");
$stmt->execute([$user_id, $user_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($messages);
?>
