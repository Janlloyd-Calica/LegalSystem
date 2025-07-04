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
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    $_SESSION['message'] = "❌ Invalid ID.";
    header("Location: dashboard.php");
    exit;
}

// Fetch case info
$stmt = $pdo->prepare("SELECT * FROM case_logs WHERE id = ?");
$stmt->execute([$id]);
$case = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$case) {
    $_SESSION['message'] = "❌ Case not found.";
    header("Location: dashboard.php");
    exit;
}

// Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'case_number' => $_POST['case_number'],
        'case_title' => $_POST['case_title'],
        'location' => $_POST['location'],
        'log_in_user' => $_POST['log_in_user'],
        'log_out_user' => $_POST['log_out_user'],
        'log_in_time' => $_POST['log_in_time'],
        'log_out_time' => $_POST['log_out_time']
    ];

    $update = $pdo->prepare("UPDATE case_logs SET 
        case_number = :case_number, 
        case_title = :case_title, 
        location = :location, 
        log_in_user = :log_in_user, 
        log_out_user = :log_out_user, 
        log_in_time = :log_in_time, 
        log_out_time = :log_out_time 
        WHERE id = :id");

    $fields['id'] = $id;

    if ($update->execute($fields)) {
        $_SESSION['message'] = "✔ Case updated successfully.";
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "❌ Failed to update case.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Case | LAWS</title>
  <link rel="stylesheet" href="css/dashboard.css">
  <link rel="stylesheet" href="css/sidebar.css">
</head>
<body>
<div class="container">
  <?php include 'sidebar.php'; ?>
  <div class="main-content">
    <h2>Edit Case Log</h2>

    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="post">
      <label>Case Number:</label>
      <input type="text" name="case_number" value="<?= htmlspecialchars($case['case_number']) ?>" required><br>

      <label>Case Title:</label>
      <input type="text" name="case_title" value="<?= htmlspecialchars($case['case_title']) ?>" required><br>

      <label>Location:</label>
      <input type="text" name="location" value="<?= htmlspecialchars($case['location']) ?>" required><br>

      <label>Login User:</label>
      <input type="text" name="log_in_user" value="<?= htmlspecialchars($case['log_in_user']) ?>"><br>

      <label>Login Time:</label>
      <input type="datetime-local" name="log_in_time" value="<?= date('Y-m-d\TH:i', strtotime($case['log_in_time'])) ?>"><br>

      <label>Logout User:</label>
      <input type="text" name="log_out_user" value="<?= htmlspecialchars($case['log_out_user']) ?>"><br>

      <label>Logout Time:</label>
      <input type="datetime-local" name="log_out_time" value="<?= date('Y-m-d\TH:i', strtotime($case['log_out_time'])) ?>"><br>

      <button type="submit">💾 Save Changes</button>
      <a href="dashboard.php">⬅ Cancel</a>
    </form>
  </div>
</div>
</body>
</html>
