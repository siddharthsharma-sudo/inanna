<?php
// admin/users.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_admin();

// filters
$search = trim($_GET['q'] ?? '');
$role = trim($_GET['role'] ?? '');

// base query
$sql = "SELECT id, name, email, role, phone, created_at FROM users WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (name LIKE :q OR email LIKE :q OR phone LIKE :q)";
    $params['q'] = '%' . $search . '%';
}
if ($role !== '') {
    $sql .= " AND role = :role";
    $params['role'] = $role;
}

$sql .= " ORDER BY created_at DESC LIMIT 500";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

include __DIR__ . '/header.php';
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Users</h3>
    <a href="dashboard.php" class="btn btn-outline-secondary">Back</a>
  </div>

  <form class="row g-2 mb-3" method="get">
    <div class="col-auto" style="min-width:260px;">
      <input name="q" class="form-control" placeholder="Search name, email or phone" value="<?php echo htmlspecialchars($search); ?>">
    </div>
    <div class="col-auto">
      <select name="role" class="form-select">
        <option value="">All roles</option>
        <option value="admin" <?php if($role==='admin') echo 'selected'; ?>>Admin</option>
        <option value="customer" <?php if($role==='customer') echo 'selected'; ?>>Customer</option>
      </select>
    </div>
    <div class="col-auto">
      <button class="btn btn-primary">Search</button>
      <a href="users.php" class="btn btn-outline-secondary">Reset</a>
    </div>
  </form>

  <div class="card p-3">
    <div class="table-responsive">
      <table class="table table-sm table-striped">
        <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?php echo (int)$u['id']; ?></td>
              <td><?php echo htmlspecialchars($u['name']); ?></td>
              <td><?php echo htmlspecialchars($u['email']); ?></td>
              <td><?php echo htmlspecialchars($u['phone']); ?></td>
              <td><?php echo htmlspecialchars($u['role']); ?></td>
              <td><?php echo htmlspecialchars($u['created_at']); ?></td>
              <td>
                <a class="btn btn-sm btn-outline-primary" href="user_view.php?id=<?php echo (int)$u['id']; ?>">View</a>
                <a class="btn btn-sm btn-outline-danger" href="user_delete.php?id=<?php echo (int)$u['id']; ?>" onclick="return confirm('Delete user?')">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($users)): ?>
            <tr><td colspan="7" class="text-center p-4">No users found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
