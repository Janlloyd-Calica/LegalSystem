<?php
session_start();
$host = 'localhost';
$db   = 'secure_library';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST['manual_submit'])) {
        $case_number = $_POST['case_number'];
        $case_title = $_POST['case_title'];
        $location = $_POST['location'];
        $log_in_user = $_POST['log_in_user'];
        $log_in_time = $_POST['log_in_time'];
        $log_out_user = $_POST['log_out_user'];
        $log_out_time = $_POST['log_out_time'];

        $stmt = $pdo->prepare("INSERT INTO case_logs (case_number, case_title, location, log_in_user, log_in_time, log_out_user, log_out_time)
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$case_number, $case_title, $location, $log_in_user, $log_in_time, $log_out_user, $log_out_time]);

        echo "<script>alert('Case added successfully!'); window.location.href='dashboard.php';</script>";
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manual Case Entry | LAIS</title>

  <!-- Google Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" rel="stylesheet">

  <!-- Stylesheets -->
  <link href="css/dashboard.css" rel="stylesheet">
  <link href="css/manual.css" rel="stylesheet">

  <link rel="icon" type="image/png" href="img/prc-car.png" sizes="1200x1200"/>
</head>

<body>
  <!-- Navbar -->
  <nav class="site-nav">
    <button class="sidebar-toggle" id="toggleSidebar">
      <span class="material-symbols-rounded">menu</span>
    </button>
  </nav>

  <div class="container">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Form -->
    <div class="form-box">
      <h2>Manual Case Entry</h2>
      <form method="post">
        <input type="text" name="case_number" placeholder="Case Number" required>
        <input type="text" name="case_title" placeholder="Case Title" required>
        <input type="text" name="location" placeholder="Location" required>
        <input type="text" name="log_in_user" placeholder="Log In By">
        <input type="datetime-local" name="log_in_time">
        <input type="text" name="log_out_user" placeholder="Log Out By">
        <input type="datetime-local" name="log_out_time">
        <button type="submit" name="manual_submit">Add Case</button>
      </form>
    </div>
  </div>

  <!-- JavaScript -->
  <script src="js/manual.js"></script>
</body>
</html>
