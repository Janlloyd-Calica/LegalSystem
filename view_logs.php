<?php
$pdo = require 'config.php';
$logs = $pdo->query("SELECT * FROM activity_log ORDER BY timestamp DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Activity Logs</title>
  <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
<div class="container">
  <aside class="sidebar collapsed">
    <!-- Sidebar content here -->
  </aside>
  <div class="main-content">
    <h1 class="page-title">Activity Log</h1>
    <div class="card">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>User</th>
            <th>Action</th>
            <th>IP Address</th>
            <th>Timestamp</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($logs as $log): ?>
          <tr>
            <td><?= $log['id'] ?></td>
            <td><?= htmlspecialchars($log['user']) ?></td>
            <td><?= htmlspecialchars($log['action']) ?></td>
            <td><?= htmlspecialchars($log['ip_address']) ?></td>
            <td><?= $log['timestamp'] ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>