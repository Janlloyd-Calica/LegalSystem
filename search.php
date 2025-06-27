<?php
$pdo = require 'config.php';

$search = $_GET['q'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM case_logs WHERE deleted = 0 AND (
    case_number LIKE ? OR 
    case_title LIKE ? OR 
    location LIKE ?
) ORDER BY id DESC");
$likeSearch = "%$search%";
$stmt->execute([$likeSearch, $likeSearch, $likeSearch]);
$cases = $stmt->fetchAll();

if (count($cases) === 0) {
    echo "<p>No matching cases found.</p>";
} else {
    echo "<table border='1' cellpadding='8' cellspacing='0' style='width: 100%; margin-top: 10px;'>";
    echo "<tr>
            <th>ID</th>
            <th>Case Number</th>
            <th>Case Title</th>
            <th>Location</th>
            <th>Log In</th>
            <th>Log In Time</th>
            <th>Log Out</th>
            <th>Log Out Time</th>
            <th>Action</th>
        </tr>";

    foreach ($cases as $case) {
        echo "<tr>
            <td>{$case['id']}</td>
            <td>" . htmlspecialchars($case['case_number']) . "</td>
            <td>" . htmlspecialchars($case['case_title']) . "</td>
            <td>" . htmlspecialchars($case['location']) . "</td>
            <td>" . htmlspecialchars($case['log_in_user']) . "</td>
            <td>{$case['log_in_time']}</td>
            <td>" . htmlspecialchars($case['log_out_user']) . "</td>
            <td>{$case['log_out_time']}</td>
            <td>
                <form method='get' onsubmit=\"return confirm('Are you sure you want to delete this case?')\">
                    <input type='hidden' name='delete' value='{$case['id']}'>
                    <button type='submit' class='delete-btn' style='background:red;color:white;border:none;padding:5px 10px;'>Delete</button>
                </form>
            </td>
        </tr>";
    }

    echo "</table>";
}
?>
