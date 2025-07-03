<?php
require_once 'config.php';

$token = $_GET['token'] ?? '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $token = $_POST['token'];

    if ($newPassword !== $confirm) {
        $message = "❌ Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
            $update->execute([$hashed, $user['id']]);
            $message = "✅ Password reset successful. You can now <a href='index.php'>log in</a>.";
        } else {
            $message = "❌ Invalid or expired token.";
        }
    }
}
?>

<?php if ($token): ?>
<form method="POST">
    <h2>Set New Password</h2>
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
    <input type="password" name="password" placeholder="New password" required>
    <input type="password" name="confirm_password" placeholder="Confirm password" required>
    <button type="submit">Reset Password</button>
    <p><?= $message ?></p>
</form>
<?php else: ?>
    <p>Invalid reset link.</p>
<?php endif; ?>
