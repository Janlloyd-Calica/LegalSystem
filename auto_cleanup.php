```php
<?php
$pdo->exec("DELETE FROM case_logs WHERE deleted = 1 AND deleted_at IS NOT NULL AND deleted_at < NOW() - INTERVAL 14 DAY");