<?php
require_once 'config.php';
require_once 'functions.php';
session_start();

$message = '';
$redirect = false;

$fieldMap = [
  'case_number'   => ['case number', 'case no', 'number', 'no.'],
  'case_title'    => ['case title', 'title', 'case name', 'case'],
  'location'      => ['location', 'area', 'place'],
  'log_in_user'   => ['login user', 'in name', 'login name', 'in | name'],
  'log_in_time'   => ['login time', 'date & time', 'in | time'],
  'log_out_user'  => ['logout user', 'out name', 'logout name', 'out | name'],
  'log_out_time'  => ['logout time', 'date out', 'out | time', 'date & time'],
];

function normalize($str) {
  return strtolower(trim(preg_replace('/[^a-z0-9]+/i', ' ', $str)));
}

function mapHeaders($headers, $fieldMap) {
  $mapped = [];
  foreach ($headers as $index => $header) {
    $normalized = normalize($header);
    foreach ($fieldMap as $dbField => $aliases) {
      if (in_array($normalized, array_map('normalize', $aliases))) {
        $mapped[$dbField] = $index;
        break;
      }
    }
  }
  return $mapped;
}

function parseDateTime($str) {
  $formats = ['m/d/Y h:i A', 'Y-m-d H:i:s', 'd-m-Y H:i', 'Y/m/d H:i:s', 'm/d/Y H:i'];
  foreach ($formats as $format) {
    $dt = DateTime::createFromFormat($format, $str);
    if ($dt && $dt->format($format) === $str) {
      return $dt->format('Y-m-d H:i:s');
    }
  }
  $fallback = strtotime($str);
  return $fallback !== false ? date('Y-m-d H:i:s', $fallback) : null;
}

if (isset($_POST['upload_csv']) && !empty($_FILES['csv_file']['tmp_name'])) {
  $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
  if ($file === false) {
    $message = "❌ Failed to open uploaded file.";
  } else {
    $headerRow = fgetcsv($file);
    $columnMap = mapHeaders($headerRow, $fieldMap);

    $requiredFields = ['case_number', 'case_title', 'location'];
    $missing = array_diff($requiredFields, array_keys($columnMap));

    if (!empty($missing)) {
      $message = "⚠️ Missing required fields: " . implode(', ', $missing);
    } else {
      $insertCount = 0;
      $skippedInvalid = 0;

      while (($row = fgetcsv($file)) !== false) {
        $case_number = trim($row[$columnMap['case_number']] ?? '');
        $case_title = trim($row[$columnMap['case_title']] ?? '');
        $location = trim($row[$columnMap['location']] ?? '');

        $log_in_user = isset($columnMap['log_in_user']) ? trim($row[$columnMap['log_in_user']] ?? '') : '';
        $log_out_user = isset($columnMap['log_out_user']) ? trim($row[$columnMap['log_out_user']] ?? '') : '';
        $log_in_time = isset($columnMap['log_in_time']) ? parseDateTime(trim($row[$columnMap['log_in_time']] ?? '')) : null;
        $log_out_time = isset($columnMap['log_out_time']) ? parseDateTime(trim($row[$columnMap['log_out_time']] ?? '')) : null;

        // Auto-fill values if missing
        $log_in_user = $log_in_user ?: 'N/A';
        $log_out_user = $log_out_user ?: 'N/A';
        $log_in_time = $log_in_time ?: date('Y-m-d H:i:s');
        $log_out_time = $log_out_time ?: date('Y-m-d H:i:s');

        if (!$case_number || !$case_title || !$location) {
          $skippedInvalid++;
          continue;
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM case_logs WHERE case_number = ?");
        $stmt->execute([$case_number]);
        if ($stmt->fetchColumn() > 0) continue;

        $insert = $pdo->prepare("INSERT INTO case_logs 
          (case_number, case_title, location, log_in_user, log_in_time, log_out_user, log_out_time)
          VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert->execute([
          $case_number, $case_title, $location,
          $log_in_user, $log_in_time, $log_out_user, $log_out_time
        ]);

        $insertCount++;
      }

      fclose($file);
      logActivity($pdo, 'Admin', "Uploaded CSV with $insertCount new case(s)", $_SERVER['REMOTE_ADDR']);
      $message = "✅ $insertCount case(s) uploaded successfully.";
      if ($skippedInvalid > 0) {
        $message .= "<br>⚠️ Skipped $skippedInvalid row(s) due to missing required fields.";
      }
      $redirect = true;
    }
  }
} else if (isset($_POST['upload_csv'])) {
  $message = "⚠️ No file uploaded.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Upload Case Logs | LAWS</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" />
  <link rel="icon" type="image/png" href="img/prc-logo.png" sizes="1200x1200"/>
  <link rel="stylesheet" href="css/sidebar.css" />
  <link rel="stylesheet" href="css/dashboard.css" />
  <style>
    .container {
      display: flex;
      min-height: 100vh;
    }

    .main-content {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 20px;
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      min-height: 100vh;
    }

    .upload-box {
      background: #fff;
      padding: 60px;
      border-radius: 20px;
      max-width: 700px;
      width: 100%;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
      border: 1px solid rgba(255, 255, 255, 0.3);
      backdrop-filter: blur(10px);
      position: relative;
      overflow: hidden;
    }

    .upload-box::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 5px;
      background: linear-gradient(90deg, #3498db, #2980b9, #8e44ad, #9b59b6);
      background-size: 400% 400%;
      animation: gradient 3s ease infinite;
    }

    @keyframes gradient {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    .upload-box h2 {
      text-align: center;
      color: #2c3e50;
      margin-bottom: 40px;
      font-size: 2.2rem;
      font-weight: 700;
      text-shadow: 0 2px 4px rgba(0,0,0,0.1);
      position: relative;
    }

    .upload-box h2::after {
      content: '';
      position: absolute;
      bottom: -15px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 4px;
      background: linear-gradient(90deg, #3498db, #2980b9);
      border-radius: 2px;
    }

    .upload-box form {
      display: flex;
      flex-direction: column;
      gap: 30px;
    }

    input[type="file"] {
      display: none;
    }

    .file-label {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 50px 30px;
      border: 3px dashed #3498db;
      border-radius: 16px;
      cursor: pointer;
      background: linear-gradient(135deg, #f8f9fa, #e9ecef);
      text-align: center;
      transition: all 0.3s ease;
      min-height: 180px;
    }

    .file-label:hover {
      background: linear-gradient(135deg, #e9ecef, #dee2e6);
      border-color: #2980b9;
      transform: translateY(-3px);
      box-shadow: 0 15px 30px rgba(52, 152, 219, 0.2);
    }

    .file-label span {
      font-size: 3.5rem;
      color: #3498db;
      margin-bottom: 15px;
      transition: transform 0.3s ease;
    }

    .file-label:hover span {
      transform: scale(1.1);
    }

    .file-label strong {
      color: #2c3e50;
      font-weight: 600;
      font-size: 1.3rem;
      margin-bottom: 8px;
    }

    .small-text {
      color: #7f8c8d;
      font-size: 1rem;
      margin-top: 5px;
    }

    .upload-box button {
      padding: 20px 40px;
      background: linear-gradient(135deg, #3498db, #2980b9);
      color: white;
      font-size: 1.1rem;
      font-weight: 600;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 1px;
      position: relative;
      overflow: hidden;
      font-family: 'Poppins', sans-serif;
    }

    .upload-box button::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.5s;
    }

    .upload-box button:hover::before {
      left: 100%;
    }

    .upload-box button:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 35px rgba(52, 152, 219, 0.4);
      background: linear-gradient(135deg, #2980b9, #3498db);
    }

    .upload-box button:active {
      transform: translateY(-1px);
    }

    .message {
      margin-top: 25px;
      padding: 20px;
      border-radius: 12px;
      text-align: center;
      font-weight: 500;
      font-size: 1.1rem;
      animation: slideIn 0.5s ease;
    }

    .success {
      background: linear-gradient(135deg, #27ae60, #2ecc71);
      color: white;
    }

    .error {
      background: linear-gradient(135deg, #e74c3c, #c0392b);
      color: white;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .tip {
      margin-top: 25px;
      font-size: 1rem;
      text-align: center;
      color: #7f8c8d;
      padding: 15px;
      background: rgba(127, 140, 141, 0.1);
      border-radius: 10px;
      border-left: 4px solid #3498db;
    }

    /* Responsive design */
    @media (max-width: 768px) {
      .upload-box {
        padding: 40px 30px;
        max-width: 90%;
      }
      
      .upload-box h2 {
        font-size: 1.8rem;
      }
      
      .file-label {
        padding: 30px 20px;
        min-height: 150px;
      }
      
      .file-label span {
        font-size: 2.5rem;
      }
      
      .file-label strong {
        font-size: 1.1rem;
      }
    }

    @media (max-width: 480px) {
      .upload-box {
        padding: 30px 20px;
      }
      
      .upload-box h2 {
        font-size: 1.5rem;
      }
      
      .file-label {
        padding: 25px 15px;
        min-height: 120px;
      }
      
      .file-label span {
        font-size: 2rem;
      }
    }
  </style>
  <script src="js/sidebar.js" defer></script>
</head>
<body>
  <div class="container">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
      <div class="upload-box">
        <h2>Upload Case Logs (.CSV)</h2>

        <?php if (!empty($message)): ?>
          <div class="message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
            <?= $message ?>
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
    setTimeout(() => {
      window.location.href = "dashboard.php";
    }, 3000);
    <?php endif; ?>
  </script>
</body>
</html>