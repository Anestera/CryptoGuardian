<?php
require 'db_connection.php';
require 'audit_log.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $stmt = $pdo->prepare("SELECT user_id, email_verified FROM users WHERE verification_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && !$user['email_verified']) {
        $stmt = $pdo->prepare("UPDATE users SET email_verified = TRUE, verification_token = NULL WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);
        
        log_action($user['user_id'], 'email_verified', 'User verified their email');
        
        echo "<script>alert('Email successfully verified. You can now login.');window.location.href = 'sign.php'</script>";
    } else {
        echo "<script>alert('Invalid or expired token.');window.location.href = 'sign.php'</script>";
    }
} else {
    echo "<script>alert('No token provided.');window.location.href = 'sign.php'</script>";
}
?>
