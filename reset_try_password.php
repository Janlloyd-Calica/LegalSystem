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

$error = '';
$success = '';
$tokenValid = false;

// Validate token
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $stmt = $pdo->prepare("SELECT id, reset_expires FROM users WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        if (strtotime($user['reset_expires']) > time()) {
            $tokenValid = true;
            $_SESSION['reset_user_id'] = $user['id'];
        } else {
            $error = "The reset link has expired.";
        }
    } else {
        $error = "Invalid reset token.";
    }
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['reset_user_id'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Update password
        $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $updateStmt->execute([$hashedPassword, $_SESSION['reset_user_id']]);
        
        // Clear session
        unset($_SESSION['reset_user_id']);
        
        $success = "Password has been reset successfully! You can now <a href='index.php'>log in</a>.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - LAWS</title>
    <link rel="icon" type="image/png" href="img/prc-logo.png" sizes="1200x1200"/>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: Arial, sans-serif;
        }
        
        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .reset-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }
        
        .logo {
            display: block;
            margin: 0 auto 30px;
            max-width: 100px;
            height: auto;
        }
        
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }
        
        input[type="password"]:focus {
            outline: none;
            border-color: #cc760d;
            box-shadow: 0 0 5px rgba(204, 118, 13, 0.3);
        }
        
        .button {
            width: 100%;
            padding: 12px;
            background-color: #cc760d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .button:hover {
            background-color: #b86b0c;
        }
        
        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #fee;
            border: 1px solid #fcc;
            border-radius: 5px;
        }
        
        .success-message {
            color: green;
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #efe;
            border: 1px solid #cfc;
            border-radius: 5px;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #cc760d;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="reset-container">
            <img src="img/prc-logo.png" alt="PRC Logo" class="logo">
            <h2>Reset Password</h2>
            
            <?php if ($error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if ($tokenValid && !$success): ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password" required 
                               placeholder="Enter new password">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               placeholder="Confirm new password">
                    </div>
                    
                    <input type="submit" class="button" value="Reset Password">
                </form>
                // Inside the form after password fields
<div style="font-size: 12px; color: #666; margin-bottom: 15px;">
    Password must contain:
    <ul>
        <li>At least 8 characters</li>
        <li>One uppercase letter</li>
        <li>One lowercase letter</li>
        <li>One number</li>
    </ul>
</div>
            <?php endif; ?>
            
            <a href="index.php" class="back-link">← Back to Login</a>
        </div>
    </div>
</body>
</html>