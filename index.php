<?php
session_start();

// Database config
$host = 'localhost';
$db   = 'secure_library';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Connect to DB
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

// Handle login
$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Only allow login if password is correct
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        header("Location: dashboard.php");
        exit;
    } else {
        $loginError = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
  <title>Admin Login | LAIS</title>
  <link rel="icon" type="image/png" href="img/prc-logo.png" sizes="1200x1200"/>
  <link href="css/login_design.css" rel="stylesheet"/>
</head>
<body>
  <div class="container">
    <img src="img/prc-logo.png" alt="PRC Logo" class="login_logo">
    <div class="login form">
      <header>Admin Login</header>
      <?php if ($loginError): ?>
        <p style="color:red;"><?= htmlspecialchars($loginError) ?></p>
      <?php endif; ?>
      <form method="POST" action="index.php">
        <input type="email" name="email" placeholder="Enter your email" required>
        <input type="password" name="password" placeholder="Enter your password" required>
        <a href="#">Forgot password?</a>
        <input type="submit" class="button" value="Login">
      </form>
    </div>
  </div>
  <footer class="app-footer">
    Property of Calica, Janlloyd & Coral, Maivy
  </footer>
</body>
</html>
