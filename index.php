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

// Handle forgot password message from session
$forgotPasswordMessage = '';
if (isset($_SESSION['forgot_password_message'])) {
    $forgotPasswordMessage = $_SESSION['forgot_password_message'];
    unset($_SESSION['forgot_password_message']); // Clear after use
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
  <title>Login & Registration Form | LAWS</title>
  <link rel="icon" type="image/png" href="img/prc-logo.png" sizes="1200x1200"/>
  <link href="css/login_design.css" rel="stylesheet"/>
  <style>
    body {
      margin: 0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    
    .main-content {
      flex: 1;
    }
    
    .app-footer {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background-color: #f8f9fa;
      text-align: center;
      padding: 10px 0;
      border-top: 1px solid #dee2e6;
      font-size: 14px;
      color: #6c757d;
      z-index: 1000;
    }
    
    /* Forgot Password Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1001;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.4);
    }
    
    .modal-content {
      background-color: #fefefe;
      margin: 15% auto;
      padding: 20px;
      border: none;
      border-radius: 10px;
      width: 80%;
      max-width: 400px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .close {
      color: #aaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }
    
    .close:hover,
    .close:focus {
      color: black;
      text-decoration: none;
    }
    
    .modal h2 {
      margin-top: 0;
      color: #333;
      text-align: center;
    }
    
    .modal input[type="email"] {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ddd;
      border-radius: 5px;
      box-sizing: border-box;
    }
    
    .modal .button {
      width: 100%;
      padding: 10px;
      background-color: #cc760d;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
    }
    
    .modal .button:hover {
      background-color: #cc760d;
    }
    
    .forgot-link {
      color: #cc760d;
      text-decoration: none;
      cursor: pointer;
    }
    
    .forgot-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="main-content">
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
          <a href="#" class="forgot-link" onclick="openModal()">Forgot password?</a>
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
  </div>
  
  <!-- Forgot Password Modal -->
  <div id="forgotPasswordModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal()">&times;</span>
      <h2>Forgot Password</h2>
      <?php if ($forgotPasswordMessage): ?>
        <p style="color: <?= strpos($forgotPasswordMessage, 'sent') !== false ? 'green' : 'red' ?>; text-align: center;">
          <?= htmlspecialchars($forgotPasswordMessage) ?>
        </p>
      <?php endif; ?>
      <form method="POST" action="forgot_password_page.php">
        <input type="email" name="forgot_email" placeholder="Enter your email address" required>
        <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
        <input type="submit" class="button" value="Send Reset Link">
      </form>
    </div>
  </div>
  
  <footer class="app-footer">
    @ PRC 2025 | CALICA & CORAL - Supervisor Ma'am Myro
  </footer>
  
  <script>
    function openModal() {
      document.getElementById('forgotPasswordModal').style.display = 'block';
    }
    
    function closeModal() {
      document.getElementById('forgotPasswordModal').style.display = 'none';
    }
    
    // Close modal when clicking outside of it
    window.onclick = function(event) {
      var modal = document.getElementById('forgotPasswordModal');
      if (event.target == modal) {
        modal.style.display = 'none';
      }
    }
    
    // Show modal if there's a forgot password message
    <?php if ($forgotPasswordMessage): ?>
    document.addEventListener('DOMContentLoaded', function() {
      openModal();
    });
    <?php endif; ?>
  </script>
</body>
</html>