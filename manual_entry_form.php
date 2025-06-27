<?php
session_start();
$host = 'localhost';
$db   = 'secure_library'; // Make sure this matches your DB in phpMyAdmin
$user = 'root';
$pass = ''; // If using XAMPP, password is usually empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manual_submit'])) {
    try {
        // Prepare the SQL statement
        $sql = "INSERT INTO case_logs (case_number, case_title, location, log_in_user, log_in_time, log_out_user, log_out_time, deleted) 
                VALUES (:case_number, :case_title, :location, :log_in_user, :log_in_time, :log_out_user, :log_out_time, 0)";
        
        $stmt = $pdo->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(':case_number', $_POST['case_number']);
        $stmt->bindParam(':case_title', $_POST['case_title']);
        $stmt->bindParam(':location', $_POST['location']);
        
        // Handle optional fields
        $log_in_user = !empty($_POST['log_in_user']) ? $_POST['log_in_user'] : null;
        $log_in_time = !empty($_POST['log_in_time']) ? $_POST['log_in_time'] : null;
        $log_out_user = !empty($_POST['log_out_user']) ? $_POST['log_out_user'] : null;
        $log_out_time = !empty($_POST['log_out_time']) ? $_POST['log_out_time'] : null;
        
        $stmt->bindParam(':log_in_user', $log_in_user);
        $stmt->bindParam(':log_in_time', $log_in_time);
        $stmt->bindParam(':log_out_user', $log_out_user);
        $stmt->bindParam(':log_out_time', $log_out_time);
        
        // Execute the statement
        if ($stmt->execute()) {
            $success_message = "Case added successfully!";
            // Clear form data after successful submission
            $_POST = array();
        }
    } catch (PDOException $e) {
        $error_message = "Error adding case: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sidebar Menu | LAIS</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" rel="stylesheet">

    <link rel="icon" type="image/png" href="img/prc-car.png" sizes="1200x1200"/>
     <link href="css/dashboard.css" rel="stylesheet"> <!-- DND -->
    <link rel="stylesheet" href="css/sidebar.css">  <!-- DND -->
<script src="js/sidebar.js" defer></script>

<style>
/* Enhanced Main Content Styles */
.main-content {
    padding: 30px;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 30px;
    text-align: center;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: relative;
}

.page-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 4px;
    background: linear-gradient(90deg, #3498db, #2980b9);
    border-radius: 2px;
}

.card {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.2);
    max-width: 600px;
    margin: 0 auto;
    position: relative;
    overflow: hidden;
}

.card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #3498db, #2980b9, #8e44ad, #9b59b6);
    background-size: 400% 400%;
    animation: gradient 3s ease infinite;
}

@keyframes gradient {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.manual-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    position: relative;
}

.form-group input {
    width: 100%;
    padding: 15px 20px;
    border: 2px solid #e0e6ed;
    border-radius: 12px;
    font-size: 16px;
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s ease;
    background: #f8f9fa;
    color: #2c3e50;
}

.form-group input:focus {
    outline: none;
    border-color: #3498db;
    background: white;
    box-shadow: 0 0 20px rgba(52, 152, 219, 0.2);
    transform: translateY(-2px);
}

.form-group input::placeholder {
    color: #7f8c8d;
    font-weight: 400;
}

.form-group input:hover {
    border-color: #bdc3c7;
    background: white;
}

.submit-btn {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    border: none;
    padding: 18px 40px;
    font-size: 18px;
    font-weight: 600;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: 'Poppins', sans-serif;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-top: 20px;
    position: relative;
    overflow: hidden;
}

.submit-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.submit-btn:hover::before {
    left: 100%;
}

.submit-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(52, 152, 219, 0.4);
    background: linear-gradient(135deg, #2980b9, #3498db);
}

.submit-btn:active {
    transform: translateY(-1px);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
}

/* Responsive Design */
@media (max-width: 768px) {
    .main-content {
        padding: 20px;
    }
    
    .page-title {
        font-size: 2rem;
    }
    
    .card {
        padding: 30px 20px;
        margin: 0 10px;
    }
    
    .form-group input {
        padding: 12px 15px;
        font-size: 14px;
    }
    
    .submit-btn {
        padding: 15px 30px;
        font-size: 16px;
    }
}

/* Loading animation for form submission */
.submit-btn.loading {
    pointer-events: none;
    opacity: 0.7;
}

.submit-btn.loading::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    border: 2px solid transparent;
    border-radius: 50%;
    border-top: 2px solid white;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: translateY(-50%) rotate(0deg); }
    100% { transform: translateY(-50%) rotate(360deg); }
}

/* Success message styles */
.success-message {
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    color: white;
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    text-align: center;
    font-weight: 500;
    animation: slideIn 0.5s ease;
}

/* Error message styles */
.error-message {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    text-align: center;
    font-weight: 500;
    animation: slideIn 0.5s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

  </head>
<body>
  <!-- Navbar -->
  <nav class="site-nav">
    <button class="sidebar-toggle">
      <span class="material-symbols-rounded">menu</span>
    </button>
  </nav>
  <div class="container">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    
    
<!-- Main Content -->
    <main class="main-content">
      <h1 class="page-title">Manual Case Entry</h1>
      
      <div class="card">
        <?php if ($success_message): ?>
          <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
          <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <form method="post" class="manual-form">
          <div class="form-group">
            <input type="text" name="case_number" placeholder="Case Number" value="<?php echo isset($_POST['case_number']) ? htmlspecialchars($_POST['case_number']) : ''; ?>" required>
          </div>
          
          <div class="form-group">
            <input type="text" name="case_title" placeholder="Case Title" value="<?php echo isset($_POST['case_title']) ? htmlspecialchars($_POST['case_title']) : ''; ?>" required>
          </div>
          
          <div class="form-group">
            <input type="text" name="location" placeholder="Location" value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>" required>
          </div>
          
          <div class="form-group">
            <input type="text" name="log_in_user" placeholder="Log In By" value="<?php echo isset($_POST['log_in_user']) ? htmlspecialchars($_POST['log_in_user']) : ''; ?>">
          </div>
          
          <div class="form-group">
            <input type="datetime-local" name="log_in_time" value="<?php echo isset($_POST['log_in_time']) ? htmlspecialchars($_POST['log_in_time']) : ''; ?>">
          </div>
          
          <div class="form-group">
            <input type="text" name="log_out_user" placeholder="Log Out By" value="<?php echo isset($_POST['log_out_user']) ? htmlspecialchars($_POST['log_out_user']) : ''; ?>">
          </div>
          
          <div class="form-group">
            <input type="datetime-local" name="log_out_time" value="<?php echo isset($_POST['log_out_time']) ? htmlspecialchars($_POST['log_out_time']) : ''; ?>">
          </div>
          
          <button type="submit" name="manual_submit" class="submit-btn">Add Case</button>
        </form>
      </div>
    </main>
  </div>
    

  <script>
document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.querySelector(".sidebar");
  const toggleBtn = document.querySelector(".sidebar-toggle");

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener("click", () => {
      sidebar.classList.toggle("collapsed");
      toggleBtn.querySelector("span").textContent =
        sidebar.classList.contains("collapsed") ? "chevron_right" : "chevron_left";
    });
  }

  // Add form submission animation
  const form = document.querySelector('.manual-form');
  const submitBtn = document.querySelector('.submit-btn');
  
  if (form && submitBtn) {
    form.addEventListener('submit', function(e) {
      submitBtn.classList.add('loading');
      submitBtn.textContent = 'Adding Case...';
    });
  }
});
</script>
</div>
</body>
</html>