<?php
$pdo = require 'config.php';

$stmt = $pdo->query("SELECT * FROM case_logs ORDER BY id DESC");
$cases = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Case Records</title>
  <link rel="stylesheet" href="css/dashboard.css">
  <style>
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      border: 1px solid #ccc;
      padding: 10px;
      text-align: left;
    }
    tr.deleted {
      background-color: #ffe6e6;
    }
  </style>
</head>
<body>
  <h1>All Case Records</h1>

  <?php if (empty($cases)): ?>
    <p>No case records found.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Case Number</th>
          <th>Case Title</th>
          <th>Location</th>
          <th>Log In</th>
          <th>Log In Time</th>
          <th>Log Out</th>
          <th>Log Out Time</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cases as $case): ?>
          <tr class="<?= $case['deleted'] ? 'deleted' : '' ?>">
            <td><?= $case['id'] ?></td>
            <td><?= htmlspecialchars($case['case_number']) ?></td>
            <td><?= htmlspecialchars($case['case_title']) ?></td>
            <td><?= htmlspecialchars($case['location']) ?></td>
            <td><?= htmlspecialchars($case['log_in_user']) ?></td>
            <td><?= $case['log_in_time'] ?></td>
            <td><?= htmlspecialchars($case['log_out_user']) ?></td>
            <td><?= $case['log_out_time'] ?></td>
            <td><?= $case['deleted'] ? 'Deleted' : 'Active' ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</body>
</html>
