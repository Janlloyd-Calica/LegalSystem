<?php
// Connect to DB
$pdo = require 'config.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $caseNumber   = $_POST["case_number"];
    $caseTitle    = $_POST["case_title"];
    $location     = $_POST["location"];
    $logInUser    = $_POST["log_in_user"];
    $logInTime    = $_POST["log_in_time"];
    $logOutUser   = $_POST["log_out_user"];
    $logOutTime   = $_POST["log_out_time"];

    // Prepare and insert
    $stmt = $pdo->prepare("INSERT INTO case_logs 
        (case_number, case_title, location, log_in_user, log_in_time, log_out_user, log_out_time) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
        
    $stmt->execute([
        $caseNumber, $caseTitle, $location, 
        $logInUser, $logInTime, $logOutUser, $logOutTime
    ]);

    echo "<p style='color:green;'>✔ Case data successfully inserted!</p>";
}
?>

<!-- HTML Form -->
<h2>Log New Case</h2>
<form method="post">
    <label>Case:</label><br>
    <input type="text" name="case_number" required><br><br>

    <label>Case Title:</label><br>
    <input type="text" name="case_title" required><br><br>

    <label>Location:</label><br>
    <input type="text" name="location" required><br><br>

    <label>Log In By:</label><br>
    <input type="text" name="log_in_user"><br>

    <label>Log In Time:</label><br>
    <input type="datetime-local" name="log_in_time"><br><br>

    <label>Log Out By:</label><br>
    <input type="text" name="log_out_user"><br>

    <label>Log Out Time:</label><br>
    <input type="datetime-local" name="log_out_time"><br><br>

    <input type="submit" value="Submit">
</form>
