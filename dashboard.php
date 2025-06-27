
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
     <link href="css/dashboard.css" rel="stylesheet">
    <link rel="stylesheet" href="css/sidebar.css">
<script src="js/sidebar.js" defer></script>

  </head>
<body>
  
  <div class="container">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    
    <!-- Site main content -->
    <div class="main-content">
  <h1 class="page-title">Dashboard Overview</h1>
  <p class="card">Welcome to your dashboard! Halu Ma'am Myro!! Nahihirapan ako sa pag pili ng colors ehe.</p>

  <!-- 🔍 Stylish Search Bar UI -->
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
      $stmt = $pdo->prepare("
          SELECT * FROM case_logs 
          WHERE deleted = 0 AND (
              case_number LIKE :search OR 
              case_title LIKE :search OR 
              location LIKE :search OR 
              log_in_user LIKE :search OR 
              log_out_user LIKE :search
          )
      ");
      $stmt->execute(['search' => $search]);
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

      echo "<h2 style='margin-top: 30px;'>Search Results:</h2>";

      if (count($results) > 0) {
          echo "<table border='1' cellpadding='8' cellspacing='0'>
              <tr>
                  <th>Case Number</th>
                  <th>Case Title</th>
                  <th>Location</th>
                  <th>Login User</th>
                  <th>Login Time</th>
                  <th>Logout User</th>
                  <th>Logout Time</th>
              </tr>";

          foreach ($results as $row) {
              echo "<tr>
                      <td>" . htmlspecialchars($row['case_number']) . "</td>
                      <td>" . htmlspecialchars($row['case_title']) . "</td>
                      <td>" . htmlspecialchars($row['location']) . "</td>
                      <td>" . htmlspecialchars($row['log_in_user']) . "</td>
                      <td>" . htmlspecialchars($row['log_in_time']) . "</td>
                      <td>" . htmlspecialchars($row['log_out_user']) . "</td>
                      <td>" . htmlspecialchars($row['log_out_time']) . "</td>
                    </tr>";
          }

          echo "</table>";
      } else {
          echo "<p>No results found.</p>";
      }
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