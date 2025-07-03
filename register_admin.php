<?php
session_start();

// Restrict access
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// CSRF Token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// DB connection
$host = 'localhost';
$db   = 'secure_library';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 🔐 Validate CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("⚠️ CSRF validation failed.");
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "❌ Invalid email format.";
    } elseif (strlen($password) < 8) {
        $message = "❌ Password must be at least 8 characters.";
    } elseif ($password !== $confirm) {
        $message = "❌ Passwords do not match.";
    } else {
        // Check if email already exists
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $message = "⚠️ Email is already registered.";
        } else {
            // Save new admin
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $insert->execute([$email, $hashed]);
            $message = "✅ New admin registered successfully.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Register New Admin</title>
  <link rel="stylesheet" href="css/login_design.css">
</head>
<body>
  <div class="container">
    <h2>Register New Admin</h2>
    <?php if (!empty($message)): ?>
      <p style="color:<?= strpos($message, '✅') !== false ? 'green' : 'red' ?>; font-weight:bold;">
        <?= htmlspecialchars($message) ?>
      </p>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <input type="email" name="email" placeholder="Admin Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <input type="password" name="confirm_password" placeholder="Confirm Password" required>
      <input type="submit" value="Register Admin" class="button">
    </form>
    <a href="dashboard.php">⬅ Back to Dashboard</a>
  </div>
</body>
</html>
