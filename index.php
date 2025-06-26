<?php
$pdo = require 'config.php';
$message = "";

// Auto-delete cases soft-deleted for more than 14 days
$pdo->exec("DELETE FROM case_logs WHERE deleted = 1 AND deleted_at < NOW() - INTERVAL 14 DAY");

// Soft delete with timestamp
if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);
    $stmt = $pdo->prepare("UPDATE case_logs SET deleted = 1, deleted_at = NOW() WHERE id = ?");
    $message = $stmt->execute([$id]) ? "Case with ID $id has been deleted." : "Failed to delete case.";
}

// Restore soft-deleted case
if (isset($_GET["restore"])) {
    $id = intval($_GET["restore"]);
    $stmt = $pdo->prepare("UPDATE case_logs SET deleted = 0, deleted_at = NULL WHERE id = ?");
    $message = $stmt->execute([$id]) ? "Case with ID $id has been restored." : "Failed to restore case.";
}

// Handle CSV Upload
if (isset($_POST["upload_csv"]) && is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
    $filename = $_FILES['csv_file']['tmp_name'];
    if (($handle = fopen($filename, "r")) !== false) {
        fgetcsv($handle); // skip header
        $imported = 0; $skipped = 0;
        while (($row = fgetcsv($handle, 1000, ",")) !== false) {
            $caseNumber = $row[0];
            $check = $pdo->prepare("SELECT COUNT(*) FROM case_logs WHERE case_number = ?");
            $check->execute([$caseNumber]);
            if ($check->fetchColumn() == 0) {
                $stmt = $pdo->prepare("INSERT INTO case_logs 
                    (case_number, case_title, location, log_in_user, log_in_time, log_out_user, log_out_time)
                    VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute($row);
                $imported++;
            } else {
                $skipped++;
            }
        }
        fclose($handle);
        $message = "$imported case(s) imported. $skipped duplicate(s) skipped.";
    }
}

// Handle manual entry
if (isset($_POST["manual_submit"])) {
    $caseNumber = $_POST["case_number"];
    $check = $pdo->prepare("SELECT COUNT(*) FROM case_logs WHERE case_number = ?");
    $check->execute([$caseNumber]);
    if ($check->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO case_logs 
            (case_number, case_title, location, log_in_user, log_in_time, log_out_user, log_out_time)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST["case_number"], $_POST["case_title"], $_POST["location"],
            $_POST["log_in_user"], $_POST["log_in_time"],
            $_POST["log_out_user"], $_POST["log_out_time"]
        ]);
        $message = "Case inserted successfully.";
    } else {
        $message = "Case number $caseNumber already exists. Entry skipped.";
    }
}

// Fetch deleted cases
$deleted_cases = $pdo->query("SELECT * FROM case_logs WHERE deleted = 1 ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Legal System Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="css/dashboard.css" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" />
</head>
<body>

<nav class="site-nav">
  <button class="sidebar-toggle">
    <span class="material-symbols-rounded">menu</span>
  </button>
</nav>

<div class="container">
  <!-- Sidebar -->
  <aside class="sidebar collapsed">
    <div class="sidebar-header">
      <img src="https://cdn.builder.io/api/v1/image/assets%2Ffa2701a192bc4724a7c3ede9e2d95cb2%2Fa97200a643ab4f96bbbd739487cf9465" class="header-logo" />
      <button class="sidebar-toggle">
        <span class="material-symbols-rounded">chevron_left</span>
      </button>
    </div>

    <div class="sidebar-content">
      <form action="#" class="search-form">
        <span class="material-symbols-rounded">search</span>
        <input type="search" placeholder="Search..." id="liveSearch" />
      </form>
      <ul class="menu-list">
        <li><a href="#" class="menu-link active"><span class="material-symbols-rounded">dashboard</span><span class="menu-label">Dashboard</span></a></li>
        <li><a href="#" class="menu-link"><span class="material-symbols-rounded">insert_chart</span><span class="menu-label">Database</span></a></li>
        <li><a href="#" class="menu-link"><span class="material-symbols-rounded">notifications</span><span class="menu-label">Notifications</span></a></li>
      </ul>
    </div>

    <div class="sidebar-footer">
      <button class="theme-toggle">
        <div class="theme-label">
          <span class="theme-icon material-symbols-rounded">dark_mode</span>
          <span class="theme-text">Dark Mode</span>
        </div>
        <div class="theme-toggle-track"><div class="theme-toggle-indicator"></div></div>
      </button>
    </div>
  </aside>

  <!-- Main Content -->
  <div class="main-content">
    <h1 class="page-title">Dashboard Overview</h1>

    <?php if ($message): ?>
      <p class="card"><?= $message ?></p>
    <?php endif; ?>

    <!-- CSV Upload -->
    <div class="card">
      <h2>Upload Case File (.CSV)</h2>
      <form method="post" enctype="multipart/form-data">
        <input type="file" name="csv_file" accept=".csv" required>
        <button type="submit" name="upload_csv">Upload File</button>
      </form>
    </div>

    <!-- Manual Entry -->
    <div class="card">
      <h2>Manual Case Entry</h2>
      <form method="post">
        <input type="text" name="case_number" placeholder="Case Number" required><br><br>
        <input type="text" name="case_title" placeholder="Case Title" required><br><br>
        <input type="text" name="location" placeholder="Location" required><br><br>
        <input type="text" name="log_in_user" placeholder="Log In By"><br><br>
        <input type="datetime-local" name="log_in_time"><br><br>
        <input type="text" name="log_out_user" placeholder="Log Out By"><br><br>
        <input type="datetime-local" name="log_out_time"><br><br>
        <button type="submit" name="manual_submit">Add Case</button>
      </form>
    </div>

    <!-- Live Search -->
    <div class="card">
      <h2>Search Cases</h2>
      <div id="results"><?php include 'search.php'; ?></div>
    </div>

    <!-- Deleted Cases -->
    <?php if (count($deleted_cases)): ?>
      <div class="card">
        <h2>Recently Deleted Cases</h2>
        <table>
          <tr><th>ID</th><th>Case Number</th><th>Case Title</th><th>Deleted At</th><th>Restore</th></tr>
          <?php foreach ($deleted_cases as $case): ?>
            <tr>
              <td><?= $case['id'] ?></td>
              <td><?= htmlspecialchars($case['case_number']) ?></td>
              <td><?= htmlspecialchars($case['case_title']) ?></td>
              <td><?= htmlspecialchars($case['deleted_at']) ?></td>
              <td>
                <form method="get">
                  <input type="hidden" name="restore" value="<?= $case['id'] ?>">
                  <button class="restore-btn">Restore</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<script src="javascript/dashboard-func.js"></script>
<script>
document.getElementById('liveSearch').addEventListener('input', function () {
  const query = this.value;
  fetch('search.php?q=' + encodeURIComponent(query))
    .then(response => response.text())
    .then(html => {
      document.getElementById('results').innerHTML = html;
    });
});
</script>
</body>
</html>
