<?php
// admin/password_tokens.php - DEV TOOL
// Only for development — DO NOT keep enabled in production.

require_once __DIR__ . '/auth.php';          // admin authentication
require_once __DIR__ . '/../includes/db.php'; // adjust if needed

require_admin();

// Fetch latest tokens
$stmt = $pdo->query("
    SELECT pr.id, pr.user_id, u.email, pr.token, pr.expires_at, pr.created_at
    FROM password_resets pr
    LEFT JOIN users u ON u.id = pr.user_id
    ORDER BY pr.created_at DESC
    LIMIT 100
");
$rows = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Password Reset Tokens - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Password Reset Tokens (DEV)</h3>
    <a href="dashboard.php" class="btn btn-outline-secondary">Back</a>
  </div>

  <div class="card p-3 shadow-sm">
    <div class="table-responsive">
      <table class="table table-sm table-striped">
        <thead>
          <tr>
            <th>ID</th>
            <th>User ID</th>
            <th>Email</th>
            <th>Token</th>
            <th>Expires</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
          <tr>
            <td><?php echo (int)$r['id']; ?></td>
            <td><?php echo (int)$r['user_id']; ?></td>
            <td><?php echo htmlspecialchars($r['email']); ?></td>
            <td style="max-width:350px; overflow:auto;">
              <code><?php echo htmlspecialchars($r['token']); ?></code>
            </td>
            <td><?php echo htmlspecialchars($r['expires_at']); ?></td>
            <td><?php echo htmlspecialchars($r['created_at']); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="alert alert-warning mt-3">
    ⚠️ <strong>Security Note:</strong> Remove this file before going live.
  </div>
</div>

</body>
</html>
