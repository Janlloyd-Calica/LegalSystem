<?php
// At the top of the file
session_start();

// Rate limiting: Max 3 requests per 10 minutes
if (!isset($_SESSION['reset_attempts'])) {
    $_SESSION['reset_attempts'] = 0;
    $_SESSION['reset_last_attempt'] = time();
}

if (time() - $_SESSION['reset_last_attempt'] < 600) { // 10 minutes
    if ($_SESSION['reset_attempts'] >= 3) {
        $error = "Too many reset attempts. Please try again later.";
        // Skip processing
    }
} else {
    // Reset counter if more than 10 minutes passed
    $_SESSION['reset_attempts'] = 0;
    $_SESSION['reset_last_attempt'] = time();
}

// Inside the processing block after successful request
$_SESSION['reset_attempts']++;

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

// Email functions
function sendEmail($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: noreply@yourdomain.com' . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

function getPasswordResetEmailTemplate($reset_link, $email) {
    return "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
            .container { max-width: 600px; margin: 0 auto; background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .header { text-align: center; margin-bottom: 30px; }
            .logo { max-width: 100px; height: auto; }
            .button { display: inline-block; padding: 12px 30px; background-color: #cc760d; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
            .warning { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <img src='https://yourdomain.com/img/prc-logo.png' alt='PRC Logo' class='logo'>
                <h1>Password Reset Request</h1>
            </div>
            
            <p>Hello,</p>
            <p>We received a request to reset the password for your account associated with {$email}.</p>
            
            <div style='text-align: center;'>
                <a href='{$reset_link}' class='button'>Reset Password</a>
            </div>
            
            <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
            <p style='word-break: break-all; color: #666;'>{$reset_link}</p>
            
            <div class='warning'>
                <p><strong>Important:</strong> This password reset link will expire in 1 hour.</p>
                <p>If you didn't request this, please ignore this email.</p>
            </div>
            
            <p>If you didn't request a password reset, please ignore this email. Your password will remain unchanged.</p>
            
            <div class='footer'>
                <p>© 2025 PRC | CALICA & CORAL - Supervisor Ma'am Myro</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
    $email = trim($_POST['email']);
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Check if email exists in database
        $stmt = $pdo->prepare("SELECT id, email FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Generate reset token
            $reset_token = bin2hex(random_bytes(32));
            $reset_expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Update user with reset token
            $updateStmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
            $updateStmt->execute([$reset_token, $reset_expires, $email]);
            
            // Create reset link
            $reset_link = "http://localhost/LEGALSYSTEM/reset_password.php?token=" . $reset_token;
            
            // Send email
            $subject = "Password Reset Request - PRC Library System";
            $message = getPasswordResetEmailTemplate($reset_link, $email);
            
            // For localhost testing, we'll show the reset link instead of sending email
            if (sendEmail($email, $subject, $message)) {
                $success = "Password reset link has been sent to your email address.";
            } else {
                // For localhost testing - show the reset link directly
                $success = "Password reset requested. For testing purposes, use this link: <a href='{$reset_link}' target='_blank'>Reset Password</a>";
            }
        } else {
            // Don't reveal if email exists or not for security
            $success = "If an account with that email exists, a password reset link has been sent.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - LAWS</title>
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
        
        .forgot-container {
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
        
        .description {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.5;
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
        
        input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }
        
        input[type="email"]:focus {
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
        
        .button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
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
        
        .app-footer {
            background-color: rgba(255, 255, 255, 0.9);
            text-align: center;
            padding: 10px 0;
            font-size: 14px;
            color: #666;
        }
        
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }
        
        .info-box {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="forgot-container">
            <img src="img/prc-logo.png" alt="PRC Logo" class="logo">
            <h2>Forgot Password</h2>
            <div class="description">
                Enter your email address and we'll send you a link to reset your password.
            </div>
            
            <?php if ($error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message"><?= $success ?></div>
            <?php endif; ?>
            
            <?php if (!$success): ?>
                <form method="POST" action="" id="forgotForm">
                    <div class="form-group">
                        <label for="email">Email Address:</label>
                        <input type="email" id="email" name="email" required 
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               placeholder="Enter your email address">
                    </div>
                    
                    <input type="submit" class="button" value="Send Reset Link" id="submitBtn">
                </form>
            <?php endif; ?>
            
            <a href="index.php" class="back-link">← Back to Login</a>
            
            <!-- For localhost testing -->
            <div class="info-box">
                <strong>Note:</strong> Since this is running on localhost, email might not be sent. 
                The reset link will be displayed above for testing purposes.
            </div>
        </div>
    </div>
    
    <footer class="app-footer">
        © PRC 2025 | CALICA & CORAL - Supervisor Ma'am Myro
    </footer>
    
    <script>
        document.getElementById('forgotForm')?.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const submitBtn = document.getElementById('submitBtn');
            
            if (!email) {
                e.preventDefault();
                alert('Please enter your email address.');
                return;
            }
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.value = 'Sending...';
            this.classList.add('loading');
        });
        
        // Auto-redirect after successful submission
        <?php if ($success): ?>
        setTimeout(function() {
            document.querySelector('.success-message').innerHTML += '<br><small>Redirecting to login in 10 seconds...</small>';
            setTimeout(function() {
                window.location.href = 'index.php';
            }, 10000);
        }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>