<?php
require_once 'config.php';
require_once 'functions.php';

$message = '';

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
} else {
    $message = "⚠️ No file uploaded.";
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>CSV Upload</title>
  <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
  <div class="container">
    <div class="main-content">
      <h2>Upload CSV</h2>
      <?php if (!empty($message)): ?>
        <p style="font-weight: bold; color: green;"><?= htmlspecialchars($message) ?></p>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data">
        <input type="file" name="csv_file" accept=".csv" required>
        <button type="submit" name="upload_csv">Upload</button>
      </form>

      <p style="margin-top: 10px; color: #666;">Tip: You can save Excel files as <strong>.csv</strong> to upload.</p>
    </div>
  </div>
</body>
</html>
