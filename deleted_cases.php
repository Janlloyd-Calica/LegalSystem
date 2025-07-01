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
    echo "Connection failed: " . $e->getMessage();
}

// Restore handler
if (isset($_GET['restore']) && is_numeric($_GET['restore'])) {
    $id = (int) $_GET['restore'];
    $stmt = $pdo->prepare("UPDATE case_logs SET deleted = 0, deleted_at = NULL WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['message'] = "✔ Case ID $id restored successfully.";
        header("Location: dashboard.php#deleted");
        exit;
    } else {
        $_SESSION['message'] = "❌ Failed to restore case.";
        header("Location: dashboard.php#deleted");
        exit;
    }
}

// Soft delete handler
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("UPDATE case_logs SET deleted = 1, deleted_at = NOW() WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['message'] = "❌ Case ID $id soft-deleted.";
        header("Location: dashboard.php");
        exit;
    } else {
        $_SESSION['message'] = "❌ Failed to delete case.";
        header("Location: dashboard.php");
        exit;
    }
}

// Permanent delete handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_permanent'])) {
    $id = (int) $_POST['delete_permanent'];
    $stmt = $pdo->prepare("DELETE FROM case_logs WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['message'] = "🗑 Case ID $id permanently deleted.";
    } else {
        $_SESSION['message'] = "❌ Failed to delete case permanently.";
    }
    header("Location: dashboard.php#deleted");
    exit;
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
    <link rel="icon" type="image/png" href="img/prc-car.png" sizes="1200x1200"/>
    <link href="css/dashboard.css" rel="stylesheet">
    <link rel="stylesheet" href="css/sidebar.css">
    <script src="js/sidebar.js" defer></script>
  </head>
<body>
  <div class="container">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
  <h1 class="page-title">Dashboard Overview</h1>
  <?php if (!empty($_SESSION['message'])): ?>
    <p style="color: green; font-weight: bold;"> <?= $_SESSION['message']; unset($_SESSION['message']); ?> </p>
  <?php endif; ?>

  <!-- Display all case logs -->
  <?php
  $stmt = $pdo->query("SELECT * FROM case_logs WHERE deleted = 0 ORDER BY id DESC");
  $allCases = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo "<h2 style='margin-top: 30px;'>All Case Logs</h2>";
  if (count($allCases) > 0) {
      echo "<table border='1' cellpadding='8' cellspacing='0'>
          <tr>
              <th>Case Number</th>
              <th>Case Title</th>
              <th>Location</th>
              <th>Login User</th>
              <th>Login Time</th>
              <th>Logout User</th>
              <th>Logout Time</th>
              <th>Action</th>
          </tr>";
      foreach ($allCases as $row) {
          echo "<tr>
                  <td>" . htmlspecialchars($row['case_number']) . "</td>
                  <td>" . htmlspecialchars($row['case_title']) . "</td>
                  <td>" . htmlspecialchars($row['location']) . "</td>
                  <td>" . htmlspecialchars($row['log_in_user']) . "</td>
                  <td>" . htmlspecialchars($row['log_in_time']) . "</td>
                  <td>" . htmlspecialchars($row['log_out_user']) . "</td>
                  <td>" . htmlspecialchars($row['log_out_time']) . "</td>
                  <td>
                      <form method='get' action=''>
                          <input type='hidden' name='delete' value='" . $row['id'] . "'>
                          <button type='submit' onclick=\"return confirm('Are you sure you want to delete this case?')\" style='color: red;'>Delete</button>
                      </form>
                  </td>
                </tr>";
      }
      echo "</table>";
  } else {
      echo "<p>No case logs available.</p>";
  }

  // Deleted cases section
  echo "<h2 id='deleted' style='margin-top: 50px;'>Deleted Cases (Restorable)</h2>";
  $stmt = $pdo->query("SELECT * FROM case_logs WHERE deleted = 1 ORDER BY deleted_at DESC");
  $deleted_cases = $stmt->fetchAll(PDO::FETCH_ASSOC);
  if (!empty($deleted_cases)) {
      echo "<table border='1' cellpadding='8' cellspacing='0'>
          <tr>
              <th>ID</th>
              <th>Case Number</th>
              <th>Case Title</th>
              <th>Deleted At</th>
              <th colspan='2'>Actions</th>
          </tr>";
      foreach ($deleted_cases as $case) {
          echo "<tr>
              <td>{$case['id']}</td>
              <td>" . htmlspecialchars($case['case_number']) . "</td>
              <td>" . htmlspecialchars($case['case_title']) . "</td>
              <td>{$case['deleted_at']}</td>
              <td>
                <form method='get' action=''>
                    <input type='hidden' name='restore' value='{$case['id']}'>
                    <button type='submit' onclick=\"return confirm('Restore this case?')\">Restore</button>
                </form>
              </td>
              <td>
                <form method='post' action=''>
                    <input type='hidden' name='delete_permanent' value='{$case['id']}'>
                    <button type='submit' onclick=\"return confirm('Permanently delete this case? This cannot be undone.')\" style='color: red;'>Delete</button>
                </form>
              </td>
          </tr>";
      }
      echo "</table>";
  } else {
      echo "<p>No deleted cases found.</p>";
  }
  ?>

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
});
</script>
</div>
</body>
</html>
