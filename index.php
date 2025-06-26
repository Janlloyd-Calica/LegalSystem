<?php
$pdo = require 'config.php';
$message = "";

// Soft delete
if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);
    $stmt = $pdo->prepare("UPDATE case_logs SET deleted = 1 WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = "Case with ID $id has been deleted.";
    } else {
        $message = "Failed to delete case.";
    }
}

// Restore deleted case
if (isset($_GET["restore"])) {
    $id = intval($_GET["restore"]);
    $stmt = $pdo->prepare("UPDATE case_logs SET deleted = 0 WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = "Case with ID $id has been restored.";
    } else {
        $message = "Failed to restore case.";
    }
}

// Handle CSV upload
if (isset($_POST["upload_csv"])) {
    if (is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
        $filename = $_FILES['csv_file']['tmp_name'];
        if (($handle = fopen($filename, "r")) !== false) {
            fgetcsv($handle); // Skip header
            $imported = 0;
            $skipped = 0;
            while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                $caseNumber = $row[0];

                $check = $pdo->prepare("SELECT COUNT(*) FROM case_logs WHERE case_number = ?");
                $check->execute([$caseNumber]);

                if ($check->fetchColumn() == 0) {
                    $stmt = $pdo->prepare("INSERT INTO case_logs 
                        (case_number, case_title, location, log_in_user, log_in_time, log_out_user, log_out_time)
                        VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute($row);
                    $imported++;
                } else {
                    $skipped++;
                }
            }
            fclose($handle);
            $message = "$imported case(s) imported. $skipped duplicate(s) skipped.";
        }
    }
}

// Handle manual entry
if (isset($_POST["manual_submit"])) {
    $caseNumber = $_POST["case_number"];

    $check = $pdo->prepare("SELECT COUNT(*) FROM case_logs WHERE case_number = ?");
    $check->execute([$caseNumber]);

    if ($check->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO case_logs 
            (case_number, case_title, location, log_in_user, log_in_time, log_out_user, log_out_time)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST["case_number"], $_POST["case_title"], $_POST["location"],
            $_POST["log_in_user"], $_POST["log_in_time"],
            $_POST["log_out_user"], $_POST["log_out_time"]
        ]);
        $message = "Case inserted successfully.";
    } else {
        $message = "Case number $caseNumber already exists. Entry skipped.";
    }
}

// Fetch deleted cases for restore option
$deleted_cases = $pdo->query("SELECT * FROM case_logs WHERE deleted = 1 ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Legal System Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #eee; }
        .form-box { margin-bottom: 40px; }
        .message { font-weight: bold; padding: 10px; color: #333; }
        .delete-btn, .restore-btn {
            padding: 4px 8px;
            border: none;
            cursor: pointer;
        }
        .delete-btn {
            background-color: red;
            color: white;
        }
        .restore-btn {
            background-color: green;
            color: white;
        }
    </style>
</head>
<body>
    <h1>Legal System Dashboard</h1>

    <?php if ($message): ?>
        <p class="message"><?= $message ?></p>
    <?php endif; ?>

    <div class="form-box">
        <h2>Upload Case File (.CSV)</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="csv_file" accept=".csv" required>
            <button type="submit" name="upload_csv">Upload File</button>
        </form>
    </div>

    <div class="form-box">
        <h2>Manual Case Entry</h2>
        <form method="post">
            <input type="text" name="case_number" placeholder="Case Number" required><br><br>
            <input type="text" name="case_title" placeholder="Case Title" required><br><br>
            <input type="text" name="location" placeholder="Location" required><br><br>
            <input type="text" name="log_in_user" placeholder="Log In By"><br><br>
            <input type="datetime-local" name="log_in_time"><br><br>
            <input type="text" name="log_out_user" placeholder="Log Out By"><br><br>
            <input type="datetime-local" name="log_out_time"><br><br>
            <button type="submit" name="manual_submit">Add Case</button>
        </form>
    </div>

    <div class="form-box">
        <input type="text" id="liveSearch" placeholder="Search cases..." style="width: 300px;">
    </div>

    <div id="results">
        <?php include 'search.php'; ?>
    </div>

    <?php if (count($deleted_cases)): ?>
        <h2>Recently Deleted Cases</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Case Number</th>
                <th>Case Title</th>
                <th>Restore</th>
            </tr>
            <?php foreach ($deleted_cases as $case): ?>
            <tr>
                <td><?= $case['id'] ?></td>
                <td><?= htmlspecialchars($case['case_number']) ?></td>
                <td><?= htmlspecialchars($case['case_title']) ?></td>
                <td>
                    <form method="get">
                        <input type="hidden" name="restore" value="<?= $case['id'] ?>">
                        <button type="submit" class="restore-btn">Restore</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <script>
    document.getElementById('liveSearch').addEventListener('input', function () {
        const query = this.value;

        fetch('search.php?q=' + encodeURIComponent(query))
            .then(response => response.text())
            .then(html => {
                document.getElementById('results').innerHTML = html;
            });
    });
    </script>
</body>
</html>