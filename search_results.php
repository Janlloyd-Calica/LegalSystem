<?php
// Start session and DB connection
session_start();

$host = 'localhost';
$db   = 'secure_library';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if ($query) {
    $stmt = $pdo->prepare("
        SELECT * FROM case_logs 
        WHERE 
            case_number LIKE :search OR 
            case_title LIKE :search OR 
            location LIKE :search OR 
            log_in_user LIKE :search OR 
            log_out_user LIKE :search
    ");
    $stmt->execute(['search' => "%$query%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $results = [];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Results</title>
</head>
<body>
    <h2>Search Results for "<?php echo htmlspecialchars($query); ?>"</h2>

    <?php if (count($results) > 0): ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Case Number</th>
                    <th>Case Title</th>
                    <th>Location</th>
                    <th>Login User</th>
                    <th>Login Time</th>
                    <th>Logout User</th>
                    <th>Logout Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['case_number']) ?></td>
                    <td><?= htmlspecialchars($row['case_title']) ?></td>
                    <td><?= htmlspecialchars($row['location']) ?></td>
                    <td><?= htmlspecialchars($row['log_in_user']) ?></td>
                    <td><?= htmlspecialchars($row['log_in_time']) ?></td>
                    <td><?= htmlspecialchars($row['log_out_user']) ?></td>
                    <td><?= htmlspecialchars($row['log_out_time']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No results found.</p>
    <?php endif; ?>
</body>
</html>
