<?php
$pdo = require 'config.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : "";

if ($q !== "") {
    $stmt = $pdo->prepare("SELECT * FROM case_logs WHERE deleted = 0 AND (case_number LIKE ? OR case_title LIKE ? OR location LIKE ?) ORDER BY id DESC");
    $stmt->execute(["%$q%", "%$q%", "%$q%"]);
    $cases = $stmt->fetchAll();
} else {
    $cases = $pdo->query("SELECT * FROM case_logs WHERE deleted = 0 ORDER BY id DESC")->fetchAll();
}
?>

<table>
    <tr>
        <th>ID</th>
        <th>Case Number</th>
        <th>Case Title</th>
        <th>Location</th>
        <th>Log In</th>
        <th>Log In Time</th>
        <th>Log Out</th>
        <th>Log Out Time</th>
        <th>Action</th>
    </tr>
    <?php foreach ($cases as $case): ?>
    <tr>
        <td><?= $case['id'] ?></td>
        <td><?= htmlspecialchars($case['case_number']) ?></td>
        <td><?= htmlspecialchars($case['case_title']) ?></td>
        <td><?= htmlspecialchars($case['location']) ?></td>
        <td><?= htmlspecialchars($case['log_in_user']) ?></td>
        <td><?= htmlspecialchars($case['log_in_time']) ?></td>
        <td><?= htmlspecialchars($case['log_out_user']) ?></td>
        <td><?= htmlspecialchars($case['log_out_time']) ?></td>
        <td>
            <form method="get" onsubmit="return confirm('Are you sure you want to delete this case?');">
                <input type="hidden" name="delete" value="<?= $case['id'] ?>">
                <button type="submit" class="delete-btn">Delete</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
