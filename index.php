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

    // Assuming your DB has a 'password' field hashed via password_hash()
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        header("Location: dashboard.php");
        exit;
    } else {
        $loginError = "Invalid email or password.";
    }
}
// Handle registration
if (isset($_POST['new_email']) && isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
    $new_email = $_POST['new_email'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        echo "<script>alert('Passwords do not match.');</script>";
    } else {
        // Check if email already exists
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->execute([$new_email]);
        if ($checkStmt->fetch()) {
            echo "<script>alert('Email already registered.');</script>";
        } else {
            // Hash password and insert new user
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
            $insertStmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $insertStmt->execute([$new_email, $hashedPassword]);
            echo "<script>alert('Registration successful. You can now log in.');</script>";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
  <title>Login & Registration Form</title>
  <link rel="icon" type="image/png" href="img/prc-logo.png" sizes="1200x1200"/>
  <link href="css/login_design.css" rel="stylesheet"/>
</head>
<body>
  <div class="container">
    <img src="img/prc-logo.png" alt="Icon representing the PRC logo" class="login_logo">
    <input type="checkbox" id="check">
    <div class="login form">
      <header>Login</header>
      <?php if ($loginError): ?>
        <p style="color:red;"><?= htmlspecialchars($loginError) ?></p>
      <?php endif; ?>
      <form method="POST" action="index.php">
        <input type="text" name="email" placeholder="Enter your email" required>
        <input type="password" name="password" placeholder="Enter your password" required>
        <a href="#">Forgot password?</a>
        <input type="submit" class="button" value="Login">
      </form>
      <div class="signup">
        <span class="signup">Don't have an account?
          <label for="check">Signup</label>
        </span>
      </div>
    </div>

    <div class="registration form">
      <header>Signup</header>
     <form method="POST" action="index.php">
        <input type="text" placeholder="Enter your email" name="new_email">
        <input type="password" placeholder="Create a password" name="new_password">
        <input type="password" placeholder="Confirm your password" name="confirm_password">
       <input type="submit" class="button" value="Signup">

      </form>
      <div class="signup">
        <span class="signup">Already have an account?
          <label for="check">Login</label>
        </span>
      </div>
    </div>
  </div>
  <footer class="app-footer">
    Property of Calica, Janlloyd & Coral, Maivy
  </footer>
</body>
</html>
