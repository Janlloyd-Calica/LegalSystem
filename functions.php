<?php
function logActivity($pdo, $user, $action, $ip = null, $details = null) {
    if (!$ip) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }

    $stmt = $pdo->prepare("
        INSERT INTO activity_log (user, action, ip_address, timestamp, details)
        VALUES (?, ?, ?, NOW(), ?)
    ");
    $stmt->execute([$user, $action, $ip, $details]);
}
?>
