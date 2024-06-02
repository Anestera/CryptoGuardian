<?php
function send_email($to, $subject, $message) {
    $headers = "From: no-reply@cryptoguardian.com\r\n";
    $headers .= "Reply-To: no-reply@cryptoguardian.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    mail($to, $subject, $message, $headers);
}
?>
