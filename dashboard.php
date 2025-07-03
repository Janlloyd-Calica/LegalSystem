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
    exit;
}

// Restore handler
if (isset($_GET['restore']) && is_numeric($_GET['restore'])) {
    $id = (int) $_GET['restore'];
    $stmt = $pdo->prepare("UPDATE case_logs SET deleted = 0, deleted_at = NULL WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['message'] = "✔ Case ID $id restored successfully.";
    } else {
        $_SESSION['message'] = "❌ Failed to restore case.";
    }
    header("Location: dashboard.php");
    exit;
}

// Soft delete handler
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("UPDATE case_logs SET deleted = 1, deleted_at = NOW() WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['message'] = "❌ Case ID $id deleted.";
    } else {
        $_SESSION['message'] = "❌ Failed to delete case.";
    }
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard | LAIS</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" />
  <link rel="icon" href="img/prc-car.png" type="image/png" />
  <link rel="stylesheet" href="css/sidebar.css" />
  <link rel="stylesheet" href="css/dashboard.css" />
  <script src="js/sidebar.js" defer></script>
</head>
<body>
<div class="container">
  <?php include 'sidebar.php'; ?>
  <div class="main-content">
    <h1 class="page-title">Dashboard Overview</h1>

    <?php if (!empty($_SESSION['message'])): ?>
      <p style="color: green; font-weight: bold;">
        <?= $_SESSION['message']; unset($_SESSION['message']); ?>
      </p>
    <?php endif; ?>

    <!-- 🔍 Search Bar -->
    <div class="search-bar-container">
      <form method="GET" action="">
        <div class="search-wrapper">
          <span class="material-symbols-rounded search-icon">search</span>
          <input type="text" name="search" placeholder="Search case logs..." required />
          <button type="submit">Search</button>
        </div>
      </form>
    </div>

    <?php
    if (isset($_GET['search'])) {
        $search = '%' . $_GET['search'] . '%';
        $stmt = $pdo->prepare("SELECT * FROM case_logs WHERE deleted = 0 AND (
            case_number LIKE :search OR 
            case_title LIKE :search OR 
            location LIKE :search OR 
            log_in_user LIKE :search OR 
            log_out_user LIKE :search)
            ORDER BY LENGTH(case_number), case_number ASC");
        $stmt->execute(['search' => $search]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<h2 style='margin-top: 30px;'>Search Results:</h2>";
        if ($results) {
            echo "<table border='1' cellpadding='8' cellspacing='0'>
                <tr>
                    <th>#</th>
                    <th>Case Number</th>
                    <th>Case Title</th>
                    <th>Location</th>
                    <th>Login User</th>
                    <th>Login Time</th>
                    <th>Logout User</th>
                    <th>Logout Time</th>
                    <th>Action</th>
                </tr>";
            $counter = 1;
            foreach ($results as $row) {
                echo "<tr>
                        <td>" . $counter++ . "</td>
                        <td>" . htmlspecialchars($row['case_number']) . "</td>
                        <td>" . htmlspecialchars($row['case_title']) . "</td>
                        <td>" . htmlspecialchars($row['location']) . "</td>
                        <td>" . htmlspecialchars($row['log_in_user']) . "</td>
                        <td>" . htmlspecialchars($row['log_in_time']) . "</td>
                        <td>" . htmlspecialchars($row['log_out_user']) . "</td>
                        <td>" . htmlspecialchars($row['log_out_time']) . "</td>
                        <td>
                          <a href='edit_case.php?id=" . $row['id'] . "' style='margin-right: 10px; color: blue;'>Edit</a>
                          <form method='get' action='' style='display:inline;'>
                            <input type='hidden' name='delete' value='" . $row['id'] . "'>
                            <button type='submit' onclick=\"return confirm('Are you sure you want to delete this case?')\" style='color: red;'>Delete</button>
                          </form>
                        </td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No results found.</p>";
        }
    }
    ?>

    <!-- 📄 All Case Logs -->
    <?php
    $stmt = $pdo->query("SELECT * FROM case_logs WHERE deleted = 0 ORDER BY LENGTH(case_number), case_number ASC");
    $allCases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h2 style='margin-top: 30px;'>All Case Logs</h2>";
    if (count($allCases) > 0) {
        echo "<table border='1' cellpadding='8' cellspacing='0'>
            <tr>
                <th>#</th>
                <th>Case Number</th>
                <th>Case Title</th>
                <th>Location</th>
                <th>Login User</th>
                <th>Login Time</th>
                <th>Logout User</th>
                <th>Logout Time</th>
                <th>Action</th>
            </tr>";
        $counter = 1;
        foreach ($allCases as $row) {
            echo "<tr>
                    <td>" . $counter++ . "</td>
                    <td>" . htmlspecialchars($row['case_number']) . "</td>
                    <td>" . htmlspecialchars($row['case_title']) . "</td>
                    <td>" . htmlspecialchars($row['location']) . "</td>
                    <td>" . htmlspecialchars($row['log_in_user']) . "</td>
                    <td>" . htmlspecialchars($row['log_in_time']) . "</td>
                    <td>" . htmlspecialchars($row['log_out_user']) . "</td>
                    <td>" . htmlspecialchars($row['log_out_time']) . "</td>
                    <td>
                      <a href='edit_case.php?id=" . $row['id'] . "' style='margin-right: 10px; color: blue;'>Edit</a>
                      <form method='get' action='' style='display:inline;'>
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
    ?>
  </div>
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
});
</script>
</body>
</html>
