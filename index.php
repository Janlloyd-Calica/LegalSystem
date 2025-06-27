<?php
$pdo = require 'config.php';
$message = "";

require 'auto_cleanup.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['upload_csv'])) {
        include 'upload.php';
    }
    if (isset($_POST['manual_submit'])) {
        include 'manual_entry.php';
    }
}

if (isset($_GET['delete'])) {
    include 'delete.php';
}

if (isset($_GET['restore'])) {
    include 'restore.php';
}

$deleted_cases = $pdo->query("SELECT * FROM case_logs WHERE deleted = 1 ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Legal System Dashboard</title>
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
  <aside class="sidebar collapsed">
    <div class="sidebar-header">
      <img src="logo.png" alt="Logo" class="header-logo" />
      <button class="sidebar-toggle">
        <span class="material-symbols-rounded">chevron_left</span>
      </button>
    </div>
    <div class="sidebar-content">
      <form action="#" class="search-form">
        <span class="material-symbols-rounded">search</span>
        <input type="search" placeholder="Search..." required />
      </form>
      <ul class="menu-list">
        <li class="menu-item"><a href="#" class="menu-link active"><span class="material-symbols-rounded">dashboard</span><span class="menu-label">Dashboard</span></a></li>
        <li class="menu-item"><a href="#" class="menu-link"><span class="material-symbols-rounded">upload_file</span><span class="menu-label">Upload CSV</span></a></li>
        <li class="menu-item"><a href="#" class="menu-link"><span class="material-symbols-rounded">note_add</span><span class="menu-label">Manual Entry</span></a></li>
        <li class="menu-item"><a href="#" class="menu-link"><span class="material-symbols-rounded">delete</span><span class="menu-label">Deleted Cases</span></a></li>
      </ul>
    </div>
    <div class="sidebar-footer">
      <button class="theme-toggle">
        <div class="theme-label">
          <span class="theme-icon material-symbols-rounded">dark_mode</span>
          <span class="theme-text">Dark Mode</span>
        </div>
        <div class="theme-toggle-track">
          <div class="theme-toggle-indicator"></div>
        </div>
      </button>
    </div>
  </aside>
  <div class="main-content">
    <h1 class="page-title">Dashboard Overview</h1>
    <?php if ($message): ?>
      <p class="message"><?= $message ?></p>
    <?php endif; ?>

    <?php include 'manual_entry.php'; ?>
    <?php include 'upload_csv.php'; ?>
    <?php include 'deleted.php'; ?>

    <div class="form-box">
        <input type="text" id="liveSearch" placeholder="Search cases..." style="width: 300px;">
    </div>
    <div id="results">
        <?php include 'search.php'; ?>
    </div>
  </div>
</div>
<script src="javascript/dashboard-func.js"></script>
</body>
</html>