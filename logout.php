<?php
session_start();
require 'audit_log.php'; 

if (isset($_SESSION['user_id'])) {
    
    log_action($_SESSION['user_id'], 'logout', 'User logged out');
}
// Уничтожение сессии
session_unset();
session_destroy();

// Перенаправление на страницу входа
header("Location: sign.php");
exit();
?>
