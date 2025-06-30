<?php if (!empty($deleted_cases)): ?>
  <h2>Deleted Cases (Restorable)</h2>
  <table border="1" cellpadding="8" cellspacing="0">
    <tr>
      <th>ID</th>
      <th>Case Number</th>
      <th>Case Title</th>
      <th>Deleted At</th>
      <th>Action</th>
    </tr>
    <?php foreach ($deleted_cases as $case): ?>
      <tr>
        <td><?= $case['id'] ?></td>
        <td><?= htmlspecialchars($case['case_number']) ?></td>
        <td><?= htmlspecialchars($case['case_title']) ?></td>
        <td><?= $case['deleted_at'] ?></td>
        <td>
          <a href="index.php?restore=<?= $case['id'] ?>" onclick="return confirm('Restore this case?')">Restore</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php else: ?>
  <p>No deleted cases found.</p>
<?php endif; ?>