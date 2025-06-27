<?php
$pdo = require 'config.php';

if (isset($_POST["submit"])) {
    if (is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
        $filename = $_FILES['csv_file']['tmp_name'];

        if (($handle = fopen($filename, "r")) !== false) {
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
            echo "<p style='color:green;'>✔ File imported successfully!</p>";
        } else {
            echo "<p style='color:red;'>❌ Failed to open file.</p>";
        }
    } else {
        echo "<p style='color:red;'>❌ No file uploaded.</p>";
    }
}
?>

<h2>Upload Case CSV File</h2>
<form method="post" enctype="multipart/form-data">
    <input type="file" name="csv_file" accept=".csv" required>
    <br><br>
    <input type="submit" name="submit" value="Upload and Import">
</form>
