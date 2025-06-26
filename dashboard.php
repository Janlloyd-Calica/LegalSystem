<?php
include 'db.php';
?>

<!DOCTYPE html>
<!-- Coding By CodingNepal - youtube.com/@codingnepal -->
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sidebar Menu | CodingNepal</title>
    <link rel="stylesheet" href="/css/dashboard.css"/>
    <!-- Linking Google fonts for icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="icon" type="image/png" href="https://cdn.builder.io/api/v1/image/assets%2Ffa2701a192bc4724a7c3ede9e2d95cb2%2Fa97200a643ab4f96bbbd739487cf9465" sizes="1200x1200"/>

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
      <aside class="sidebar collapsed">
        <!-- Sidebar header -->
        <div class="sidebar-header">
          <img src="https://cdn.builder.io/api/v1/image/assets%2Ffa2701a192bc4724a7c3ede9e2d95cb2%2Fa97200a643ab4f96bbbd739487cf9465" alt="CodingNepal" class="header-logo" />
          <button class="sidebar-toggle">
            <span class="material-symbols-rounded">chevron_left</span>
          </button>
        </div>
        <div class="sidebar-content">
          <!-- Search Form -->
          <form action="#" class="search-form">
            <span class="material-symbols-rounded">search</span>
            <input type="search" placeholder="Search..." required />
          </form>
          <!-- Sidebar Menu -->
          <ul class="menu-list">
            <li class="menu-item">
              <a href="#" class="menu-link active">
                <span class="material-symbols-rounded">dashboard</span>
                <span class="menu-label">Dashboard</span>
              </a>
            </li>
            <li class="menu-item">
              <a href="#" class="menu-link">
                <span class="material-symbols-rounded">insert_chart</span>
                <span class="menu-label">Database</span>
              </a>
            </li>
            <li class="menu-item">
              <a href="#" class="menu-link">
                <span class="material-symbols-rounded">notifications</span>
                <span class="menu-label">Notifications</span>
              </a>
            </li>
            <li class="menu-item">
              <a href="#" class="menu-link">
                <span class="material-symbols-rounded">star</span>
                <span class="menu-label">Favourites</span>
              </a>
            </li>
            <li class="menu-item">
              <a href="#" class="menu-link">
                <span class="material-symbols-rounded">storefront</span>
                <span class="menu-label">Products</span>
              </a>
            </li>
            <li class="menu-item">
              <a href="#" class="menu-link">
                <span class="material-symbols-rounded">group</span>
                <span class="menu-label">Log In Access</span>
              </a>
            </li>
            <li class="menu-item">
              <a href="#" class="menu-link">
                <span class="material-symbols-rounded">settings</span>
                <span class="menu-label">Settings</span>
              </a>
            </li>
          </ul>
        </div>
        <!-- Sidebar Footer -->
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
      <!-- Site main content -->
      <div class="main-content">
        <h1 class="page-title">Dashboard Overview</h1>
        <p class="card">Welcome to your dashboard! Halu Ma'am Myro!! Nahihirapan ako sa pag pili ng colors ehe.</p>
      </div>
    </div>
    <script src="/javascript/dashboard-func.js"></script>
  </body>
</html>