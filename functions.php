<?php
function logActivity(PDO $pdo, $username, $action, $details = '') {
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $pdo->prepare("INSERT INTO activity_log (username, action, ip_address, details) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $action, $ip, $details]);
}
?>
