<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit;
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $message = "⚠ Invalid CSRF token.";
  } else {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $message = "❌ Invalid email format.";
    } elseif ($password !== $confirm) {
      $message = "❌ Passwords do not match.";
    } else {
      $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
      $stmt->execute([$email]);
      if ($stmt->fetch()) {
        $message = "❌ Email already exists.";
      } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        $insert->execute([$email, $hash]);
        $message = "✅ Admin added.";
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dark Admin Panel</title>
  <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      background: #000;
      color: #0f0;
      font-family: 'Share Tech Mono', monospace;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .panel {
      background: #111;
      border: 1px solid #0f0;
      padding: 30px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 0 10px #0f0;
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
      text-shadow: 0 0 5px #0f0;
    }
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-top: 10px;
      margin-bottom: 10px;
      background: #000;
      color: #0f0;
      border: 1px solid #0f0;
    }
    .toggle-eye {
      position: relative;
      cursor: pointer;
      float: right;
      margin-top: -30px;
      margin-right: 10px;
    }
    .strength {
      height: 8px;
      background: #222;
      margin-top: 5px;
    }
    .strength-bar {
      height: 8px;
      background: red;
      width: 0%;
      transition: width 0.3s;
    }
    button {
      width: 100%;
      padding: 10px;
      background: #0f0;
      color: #000;
      border: none;
      margin-top: 10px;
      cursor: pointer;
      font-weight: bold;
    }
    .message {
      margin-top: 10px;
      font-size: 0.9rem;
    }
    a {
      color: #0f0;
      display: block;
      margin-top: 15px;
      text-align: center;
      text-decoration: none;
    }
    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="panel">
    <h2>Register Admin</h2>

    <?php if ($message): ?>
      <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <label>Email</label>
      <input type="email" name="email" required>

      <label>Password</label>
      <input type="password" name="password" id="password" required>
      <span class="toggle-eye" onclick="togglePassword('password')">👁</span>
      <div class="strength"><div id="bar" class="strength-bar"></div></div>

      <label>Confirm Password</label>
      <input type="password" name="confirm" id="confirm" required>
      <span class="toggle-eye" onclick="togglePassword('confirm')">👁</span>

      <button type="submit">Create Admin</button>
    </form>

    <a href="dashboard.php">← Back to Dashboard</a>
  </div>

  <script>
    function togglePassword(id) {
      const input = document.getElementById(id);
      input.type = input.type === "password" ? "text" : "password";
    }

    const passwordInput = document.getElementById("password");
    const bar = document.getElementById("bar");

    passwordInput.addEventListener("input", function () {
      const val = passwordInput.value;
      let score = 0;
      if (val.length > 6) score++;
      if (/[A-Z]/.test(val)) score++;
      if (/[0-9]/.test(val)) score++;
      if (/[\W]/.test(val)) score++;

      const colors = ["red", "orange", "yellow", "lime"];
      bar.style.width = (score * 25) + "%";
      bar.style.background = colors[score - 1] || "red";
    });
  </script>
</body>
</html>
