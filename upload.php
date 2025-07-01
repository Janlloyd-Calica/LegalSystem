<?php
require_once 'config.php';
require_once 'functions.php';

$message = '';
$redirect = false;

if (isset($_POST['upload_csv']) && !empty($_FILES['csv_file']['tmp_name'])) {
    $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
    $rowCount = 0;
    $insertCount = 0;

    // Skip header
    fgetcsv($file);

    while (($row = fgetcsv($file)) !== false) {
        $rowCount++;

        if (count($row) < 7) continue;

        [$case_number, $case_title, $location, $log_in_user, $log_in_time, $log_out_user, $log_out_time] = $row;

        // Require all fields
        if (empty($case_number) || empty($case_title) || empty($location) ||
            empty($log_in_user) || empty($log_in_time) || empty($log_out_user) || empty($log_out_time)) {
            continue;
        }

        // Check for duplicates
        $check = $pdo->prepare("SELECT COUNT(*) FROM case_logs WHERE case_number = ?");
        $check->execute([$case_number]);

        if ($check->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO case_logs 
                (case_number, case_title, location, log_in_user, log_in_time, log_out_user, log_out_time)
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$case_number, $case_title, $location, $log_in_user, $log_in_time, $log_out_user, $log_out_time]);
            $insertCount++;
        }
    }

    fclose($file);
    logActivity($pdo, 'Admin', "Uploaded CSV file with $insertCount new cases", $_SERVER['REMOTE_ADDR']);
    $message = "✅ $insertCount new case(s) uploaded successfully.";
    $redirect = true; // trigger JS redirect
} else if (isset($_POST['upload_csv'])) {
    $message = "⚠️ No file uploaded.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upload Case Logs (.CSV)</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; }
    body {
      font-family: 'Poppins', sans-serif;
      background: #ecf0f3;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }

    .container {
      background: #fff;
      padding: 40px;
      border-radius: 16px;
      max-width: 500px;
      width: 100%;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
      animation: fadeIn 0.4s ease;
    }

    h2 {
      text-align: center;
      color: #2c3e50;
      margin-bottom: 30px;
      font-size: 1.8rem;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    input[type="file"] {
      display: none;
    }

    .file-label {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 30px;
      border: 2px dashed #3498db;
      border-radius: 12px;
      cursor: pointer;
      transition: background 0.3s ease, border-color 0.3s ease;
      background: #f9fcff;
      text-align: center;
    }

    .file-label:hover {
      background: #eef7fd;
      border-color: #2980b9;
    }

    .file-label span {
      font-size: 2rem;
      color: #3498db;
      margin-bottom: 10px;
    }

    .file-label strong {
      color: #2c3e50;
      font-weight: 600;
    }

    button {
      padding: 14px;
      background: linear-gradient(to right, #3498db, #2980b9);
      color: white;
      font-size: 1rem;
      font-weight: 600;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      transition: transform 0.2s, box-shadow 0.3s;
    }

    button:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(52, 152, 219, 0.3);
    }

    .message {
      margin-top: 20px;
      padding: 15px;
      border-radius: 10px;
      text-align: center;
      font-weight: 500;
    }

    .success {
      background-color: #d4edda;
      color: #155724;
    }

    .error {
      background-color: #f8d7da;
      color: #721c24;
    }

    .tip {
      margin-top: 10px;
      font-size: 0.85rem;
      text-align: center;
      color: #7f8c8d;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Upload Case Logs (.CSV)</h2>

    <?php if (!empty($message)): ?>
      <div class="message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <label for="csv_file" class="file-label">
        <span>📄</span>
        <strong>Click to select your CSV file</strong>
        <div class="small-text">Accepted: .csv format only</div>
      </label>
      <input type="file" name="csv_file" accept=".csv" required id="csv_file">
      <button type="submit" name="upload_csv">📤 Upload CSV</button>
    </form>

    <div class="tip">
      💡 Save your Excel file as <strong>.csv</strong> before uploading.
    </div>
  </div>

  <script>
    const fileInput = document.getElementById('csv_file');
    const fileLabel = document.querySelector('.file-label strong');

    fileInput.addEventListener('change', function () {
      const fileName = this.files[0]?.name || 'Click to select your CSV file';
      fileLabel.textContent = fileName;
    });

    <?php if ($redirect): ?>
    // Redirect after 3 seconds
    setTimeout(() => {
      window.location.href = "dashboard.php";
    }, 3000);
    <?php endif; ?>
  </script>
</body>
</html>
