<?php
require_once 'config.php';
require_once 'functions.php';

$message = '';
$redirect = false;

// Define expected DB fields and possible header aliases
$fieldMap = [
    'case_number'   => ['case number', 'case no', 'number', 'no.'],
    'case_title'    => ['case title', 'title', 'case name', 'case'],
    'location'      => ['location', 'area', 'place'],
    'log_in_user'   => ['login user', 'in name', 'login name', 'in | name'],
    'log_in_time'   => ['login time', 'date & time', 'in | time'],
    'log_out_user'  => ['logout user', 'out name', 'logout name', 'out | name'],
    'log_out_time'  => ['logout time', 'date out', 'out | time']
];

// Normalize header names
function normalize($str) {
    return strtolower(trim(preg_replace('/[^a-z0-9]+/i', ' ', $str)));
}

// Map CSV headers to database fields
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

// Convert to MySQL datetime format
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

        $requiredFields = ['case_number', 'case_title', 'location', 'log_in_user', 'log_in_time', 'log_out_user', 'log_out_time'];
        $missing = array_diff($requiredFields, array_keys($columnMap));

        if (!empty($missing)) {
            $message = "⚠️ Missing required fields: " . implode(', ', $missing);
        } else {
            $rowCount = 0;
            $insertCount = 0;
            $skippedInvalidDate = 0;

            while (($row = fgetcsv($file)) !== false) {
                $rowCount++;

                $case_number   = trim($row[$columnMap['case_number']] ?? '');
                $case_title    = trim($row[$columnMap['case_title']] ?? '');
                $location      = trim($row[$columnMap['location']] ?? '');
                $log_in_user   = trim($row[$columnMap['log_in_user']] ?? '');
                $log_out_user  = trim($row[$columnMap['log_out_user']] ?? '');
                $log_in_time_raw = trim($row[$columnMap['log_in_time']] ?? '');
                $log_out_time_raw = trim($row[$columnMap['log_out_time']] ?? '');

                $log_in_time = parseDateTime($log_in_time_raw);
                $log_out_time = parseDateTime($log_out_time_raw);

                if (
                    !$case_number || !$case_title || !$location || !$log_in_user ||
                    !$log_in_time || !$log_out_user || !$log_out_time
                ) {
                    $skippedInvalidDate++;
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
            if ($skippedInvalidDate > 0) {
                $message .= "<br>⏳ Skipped $skippedInvalidDate row(s) due to invalid or missing datetime.";
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
  <meta charset="UTF-8">
  <title>Upload Case Logs (.CSV)</title>
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
