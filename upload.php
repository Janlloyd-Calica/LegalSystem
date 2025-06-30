<?php
$pdo = require 'config.php';

// Handle form submission
$success_message = '';
$error_message = '';

if (isset($_POST["submit"])) {
    if (is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
        $filename = $_FILES['csv_file']['tmp_name'];

        if (($handle = fopen($filename, "r")) !== false) {
            try {
                fgetcsv($handle); // Skip header

                while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                    // Map CSV columns to DB fields
                    $caseNumber   = $row[0];
                    $caseTitle    = $row[1];
                    $location     = $row[2];
                    $logInUser    = $row[3];
                    $logInTime    = $row[4];
                    $logOutUser   = $row[5];
                    $logOutTime   = $row[6];

                    $stmt = $pdo->prepare("INSERT INTO case_logs 
                        (case_number, case_title, location, log_in_user, log_in_time, log_out_user, log_out_time)
                        VALUES (?, ?, ?, ?, ?, ?, ?)");
                    
                    $stmt->execute([
                        $caseNumber, $caseTitle, $location,
                        $logInUser, $logInTime, $logOutUser, $logOutTime
                    ]);
                }
                fclose($handle);
                $success_message = "File imported successfully!";
            } catch (Exception $e) {
                $error_message = "Error importing file: " . $e->getMessage();
            }
        } else {
            $error_message = "Failed to open file.";
        }
    } else {
        $error_message = "No file uploaded.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CSV Upload | LAIS</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" rel="stylesheet">

    <link rel="icon" type="image/png" href="img/prc-car.png" sizes="1200x1200"/>
    <link href="css/dashboard.css" rel="stylesheet">
    <link rel="stylesheet" href="css/sidebar.css">
    <script src="js/sidebar.js" defer></script>

<style>
/* Permanent sidebar styles */
.sidebar {
    position: fixed !important;
    left: 0 !important;
    top: 0 !important;
    width: 280px !important;
    height: 100vh !important;
    z-index: 1000;
    transition: none !important;
    transform: none !important;
}

.container {
    display: flex;
    min-height: 100vh;
    width: 100%;
    padding-left: 280px; /* Account for fixed sidebar width */
}

/* Enhanced Main Content Styles with improved responsiveness */
.main-content {
    flex: 1;
    padding: clamp(20px, 4vw, 30px);
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
    width: 100%;
    max-width: 100%;
}

.page-title {
    font-size: clamp(1.8rem, 4vw, 2.5rem);
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: clamp(20px, 3vw, 30px);
    text-align: center;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: relative;
    word-wrap: break-word;
}

.page-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: clamp(60px, 15vw, 80px);
    height: 4px;
    background: linear-gradient(90deg, #3498db, #2980b9);
    border-radius: 2px;
}

.card {
    background: white;
    border-radius: clamp(15px, 3vw, 20px);
    padding: clamp(25px, 5vw, 40px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.2);
    max-width: min(600px, 95vw);
    width: 100%;
    margin: 0 auto;
    position: relative;
    overflow: hidden;
}

.card::before {
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

.upload-form {
    display: flex;
    flex-direction: column;
    gap: clamp(15px, 3vw, 20px);
    align-items: center;
}

.file-input-wrapper {
    position: relative;
    width: 100%;
    max-width: 400px;
}

.file-input {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

.file-input-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 150px;
    border: 3px dashed #3498db;
    border-radius: 12px;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    cursor: pointer;
    transition: all 0.3s ease;
    padding: 20px;
    text-align: center;
}

.file-input-label:hover {
    background: linear-gradient(135deg, #e9ecef, #dee2e6);
    border-color: #2980b9;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(52, 152, 219, 0.2);
}

.file-input-label.dragover {
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    border-color: #1976d2;
    transform: scale(1.02);
}

.upload-icon {
    font-size: clamp(2rem, 4vw, 3rem);
    color: #3498db;
    margin-bottom: 10px;
}

.upload-text {
    color: #2c3e50;
    font-weight: 500;
    font-size: clamp(14px, 2.5vw, 16px);
    margin-bottom: 5px;
}

.upload-subtext {
    color: #7f8c8d;
    font-size: clamp(12px, 2vw, 14px);
}

.selected-file {
    color: #27ae60;
    font-weight: 600;
    margin-top: 10px;
    font-size: clamp(12px, 2vw, 14px);
    word-break: break-all;
}

.submit-btn {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    border: none;
    padding: clamp(15px, 3vw, 18px) clamp(30px, 6vw, 40px);
    font-size: clamp(16px, 2.5vw, 18px);
    font-weight: 600;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: 'Poppins', sans-serif;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-top: clamp(15px, 3vw, 20px);
    position: relative;
    overflow: hidden;
    min-width: 150px;
}

.submit-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.submit-btn:hover::before {
    left: 100%;
}

.submit-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(52, 152, 219, 0.4);
    background: linear-gradient(135deg, #2980b9, #3498db);
}

.submit-btn:active {
    transform: translateY(-1px);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
}

.submit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Success message styles */
.success-message {
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    color: white;
    padding: clamp(12px, 2.5vw, 15px) clamp(15px, 3vw, 20px);
    border-radius: 12px;
    margin-bottom: clamp(15px, 3vw, 20px);
    text-align: center;
    font-weight: 500;
    animation: slideIn 0.5s ease;
    font-size: clamp(14px, 2.5vw, 16px);
}

/* Error message styles */
.error-message {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
    padding: clamp(12px, 2.5vw, 15px) clamp(15px, 3vw, 20px);
    border-radius: 12px;
    margin-bottom: clamp(15px, 3vw, 20px);
    text-align: center;
    font-weight: 500;
    animation: slideIn 0.5s ease;
    font-size: clamp(14px, 2.5vw, 16px);
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

/* Loading animation for form submission */
.submit-btn.loading {
    pointer-events: none;
    opacity: 0.7;
}

.submit-btn.loading::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    border: 2px solid transparent;
    border-radius: 50%;
    border-top: 2px solid white;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: translateY(-50%) rotate(0deg); }
    100% { transform: translateY(-50%) rotate(360deg); }
}

/* Responsive breakpoints */
@media (max-width: 768px) {
    .container {
        padding-left: 250px; /* Smaller sidebar on tablets */
    }
    
    .sidebar {
        width: 250px !important;
    }
    
    .main-content {
        padding: 15px;
    }
    
    .card {
        margin: 0;
        border-radius: 15px;
    }
    
    .file-input-label {
        min-height: 120px;
        padding: 15px;
    }
}

@media (max-width: 480px) {
    .container {
        padding-left: 200px; /* Even smaller sidebar on mobile */
    }
    
    .sidebar {
        width: 200px !important;
    }
    
    .main-content {
        padding: 10px;
    }
    
    .card {
        padding: 20px 15px;
        border-radius: 12px;
    }
    
    .file-input-label {
        min-height: 100px;
        padding: 10px;
    }
    
    .upload-icon {
        font-size: 2rem;
    }
}

/* High DPI / Zoom adjustments */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
    .page-title {
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    
    .card {
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }
}

/* Ensure no horizontal scroll */
html, body {
    max-width: 100%;
    overflow-x: hidden;
}

.container {
    max-width: 100vw;
}
</style>

  </head>
<body>
  <!-- No navbar needed - sidebar is permanent -->
  
  <div class="container">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="main-content">
      <h1 class="page-title">Upload Case CSV File</h1>
      
      <div class="card">
        <?php if ($success_message): ?>
          <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
          <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data" class="upload-form">
          <div class="file-input-wrapper">
            <input type="file" name="csv_file" accept=".csv" required class="file-input" id="csvFile">
            <label for="csvFile" class="file-input-label">
              <span class="material-symbols-rounded upload-icon">cloud_upload</span>
              <div class="upload-text">Click to choose CSV file</div>
              <div class="upload-subtext">or drag and drop here</div>
              <div class="selected-file" id="selectedFile"></div>
            </label>
          </div>
          
          <button type="submit" name="submit" class="submit-btn">Upload and Import</button>
        </form>
      </div>
    </main>
  </div>

  <script>
document.addEventListener("DOMContentLoaded", () => {
  // File input handling
  const fileInput = document.getElementById('csvFile');
  const fileLabel = document.querySelector('.file-input-label');
  const selectedFileDiv = document.getElementById('selectedFile');
  const form = document.querySelector('.upload-form');
  const submitBtn = document.querySelector('.submit-btn');

  // File selection handler
  fileInput.addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name;
    if (fileName) {
      selectedFileDiv.textContent = `Selected: ${fileName}`;
      selectedFileDiv.style.display = 'block';
    } else {
      selectedFileDiv.style.display = 'none';
    }
  });

  // Drag and drop functionality
  fileLabel.addEventListener('dragover', function(e) {
    e.preventDefault();
    fileLabel.classList.add('dragover');
  });

  fileLabel.addEventListener('dragleave', function(e) {
    e.preventDefault();
    fileLabel.classList.remove('dragover');
  });

  fileLabel.addEventListener('drop', function(e) {
    e.preventDefault();
    fileLabel.classList.remove('dragover');
    
    const files = e.dataTransfer.files;
    if (files.length > 0 && files[0].name.toLowerCase().endsWith('.csv')) {
      fileInput.files = files;
      selectedFileDiv.textContent = `Selected: ${files[0].name}`;
      selectedFileDiv.style.display = 'block';
    }
  });

  // Form submission animation
  if (form && submitBtn) {
    form.addEventListener('submit', function(e) {
      if (fileInput.files.length > 0) {
        submitBtn.classList.add('loading');
        submitBtn.textContent = 'Uploading...';
        submitBtn.disabled = true;
      }
    });
  }
});
</script>
</body>
</html>