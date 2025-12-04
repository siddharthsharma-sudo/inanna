<?php
// account.php - all handlers inline (profile, password, addresses) + show 20 orders
require_once __DIR__ . '/includes/db.php';
session_start();

if (empty($_SESSION['user_id'])) {
    $_SESSION['after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

// CSRF token
if (empty($_SESSION['account_csrf'])) $_SESSION['account_csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['account_csrf'];

// messages
$messages = ['success'=>[], 'error'=>[]];
function add_msg(&$arr, $type, $text) { if (!isset($arr[$type])) $arr[$type]=[]; $arr[$type][] = $text; }

// --- POST handlers: profile, password, add/edit/delete/set_default addresses ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token = $_POST['csrf'] ?? '';
    if (!hash_equals($csrf, (string)$token)) {
        add_msg($messages, 'error', 'Invalid request (CSRF).');
    } else {

        // EDIT PROFILE (inline)
        if ($action === 'edit_profile') {
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            if ($name === '') {
                add_msg($messages, 'error', 'Name is required.');
            } else {
                try {
                    $upd = $pdo->prepare("UPDATE users SET name = :name, phone = :phone WHERE id = :id");
                    $upd->execute(['name'=>$name, 'phone'=>$phone, 'id'=>$userId]);
                    // update session
                    if (!empty($_SESSION['user'])) {
                        $_SESSION['user']['name'] = $name;
                        $_SESSION['user']['username'] = $_SESSION['user']['username'] ?? '';
                    }
                    $_SESSION['user_name'] = $name;
                    add_msg($messages, 'success', 'Profile updated.');
                } catch (Throwable $e) {
                    error_log("Edit profile error: ".$e->getMessage());
                    add_msg($messages, 'error', 'Unable to update profile.');
                }
            }
        }

        // CHANGE PASSWORD (inline)
        if ($action === 'change_password') {
            $current = $_POST['current_password'] ?? '';
            $new = $_POST['new_password'] ?? '';
            $new2 = $_POST['new_password2'] ?? '';
            if ($new === '' || strlen($new) < 6) {
                add_msg($messages, 'error', 'New password must be at least 6 characters.');
            } elseif ($new !== $new2) {
                add_msg($messages, 'error', 'New passwords do not match.');
            } else {
                try {
                    $s = $pdo->prepare("SELECT password_hash FROM users WHERE id = :id LIMIT 1");
                    $s->execute(['id'=>$userId]);
                    $u = $s->fetch(PDO::FETCH_ASSOC);
                    if (!$u || !password_verify($current, $u['password_hash'])) {
                        add_msg($messages, 'error', 'Current password is incorrect.');
                    } else {
                        $hash = password_hash($new, PASSWORD_DEFAULT);
                        $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id")->execute(['hash'=>$hash,'id'=>$userId]);
                        session_regenerate_id(true);
                        add_msg($messages, 'success', 'Password changed successfully.');
                    }
                } catch (Throwable $e) {
                    error_log("Change password error: ".$e->getMessage());
                    add_msg($messages, 'error', 'Unable to change password.');
                }
            }
        }

        // ADD ADDRESS
        if ($action === 'add_address') {
            $label = trim($_POST['label'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            $phone = trim($_POST['phone_addr'] ?? '');
            $line1 = trim($_POST['address_line1'] ?? '');
            $line2 = trim($_POST['address_line2'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $state = trim($_POST['state'] ?? '');
            $postal = trim($_POST['postal_code'] ?? '');
            $country = trim($_POST['country'] ?? '');
            $make_default = !empty($_POST['make_default']) ? 1 : 0;

            if ($line1 === '' || $city === '' || $postal === '') {
                add_msg($messages, 'error', 'Address line1, city and postal code are required.');
            } else {
                try {
                    if ($make_default) $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = :uid")->execute(['uid'=>$userId]);
                    $ins = $pdo->prepare("INSERT INTO addresses (user_id,label,full_name,phone,address_line1,address_line2,city,state,postal_code,country,is_default) VALUES (:uid,:label,:full_name,:phone,:line1,:line2,:city,:state,:postal,:country,:is_default)");
                    $ins->execute([
                        'uid'=>$userId,'label'=>$label ?: 'Address','full_name'=>$full_name,'phone'=>$phone,
                        'line1'=>$line1,'line2'=>$line2,'city'=>$city,'state'=>$state,'postal'=>$postal,'country'=>$country,'is_default'=>$make_default
                    ]);
                    add_msg($messages, 'success', 'Address added.');
                } catch (Throwable $e) {
                    error_log("Add address error: ".$e->getMessage());
                    add_msg($messages, 'error', 'Unable to add address.');
                }
            }
        }

        // EDIT ADDRESS
        if ($action === 'edit_address') {
            $aid = (int)($_POST['address_id'] ?? 0);
            $label = trim($_POST['label'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            $phone = trim($_POST['phone_addr'] ?? '');
            $line1 = trim($_POST['address_line1'] ?? '');
            $line2 = trim($_POST['address_line2'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $state = trim($_POST['state'] ?? '');
            $postal = trim($_POST['postal_code'] ?? '');
            $country = trim($_POST['country'] ?? '');
            $make_default = !empty($_POST['make_default']) ? 1 : 0;

            if ($aid <= 0) add_msg($messages, 'error', 'Invalid address selected.');
            elseif ($line1 === '' || $city === '' || $postal === '') add_msg($messages, 'error', 'Address line1, city and postal code are required.');
            else {
                try {
                    $check = $pdo->prepare("SELECT id FROM addresses WHERE id = :id AND user_id = :uid LIMIT 1");
                    $check->execute(['id'=>$aid,'uid'=>$userId]);
                    if (!$check->fetch()) {
                        add_msg($messages, 'error', 'Address not found.');
                    } else {
                        if ($make_default) $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = :uid")->execute(['uid'=>$userId]);
                        $upd = $pdo->prepare("UPDATE addresses SET label=:label, full_name=:full_name, phone=:phone, address_line1=:line1, address_line2=:line2, city=:city, state=:state, postal_code=:postal, country=:country, is_default=:is_default WHERE id=:id AND user_id=:uid");
                        $upd->execute([
                            'label'=>$label,'full_name'=>$full_name,'phone'=>$phone,'line1'=>$line1,'line2'=>$line2,'city'=>$city,'state'=>$state,'postal'=>$postal,'country'=>$country,'is_default'=>$make_default,'id'=>$aid,'uid'=>$userId
                        ]);
                        add_msg($messages, 'success', 'Address updated.');
                    }
                } catch (Throwable $e) {
                    error_log("Edit address error: ".$e->getMessage());
                    add_msg($messages, 'error', 'Unable to update address.');
                }
            }
        }

        // DELETE ADDRESS
        if ($action === 'delete_address') {
            $aid = (int)($_POST['address_id'] ?? 0);
            if ($aid <= 0) add_msg($messages, 'error', 'Invalid address id.');
            else {
                try {
                    $s = $pdo->prepare("SELECT is_default FROM addresses WHERE id = :id AND user_id = :uid LIMIT 1");
                    $s->execute(['id'=>$aid,'uid'=>$userId]);
                    $row = $s->fetch(PDO::FETCH_ASSOC);
                    if (!$row) add_msg($messages, 'error', 'Address not found.');
                    else {
                        $pdo->prepare("DELETE FROM addresses WHERE id = :id AND user_id = :uid")->execute(['id'=>$aid,'uid'=>$userId]);
                        if (!empty($row['is_default'])) {
                            $set = $pdo->prepare("SELECT id FROM addresses WHERE user_id = :uid ORDER BY id DESC LIMIT 1");
                            $set->execute(['uid'=>$userId]);
                            $first = $set->fetch(PDO::FETCH_ASSOC);
                            if ($first) $pdo->prepare("UPDATE addresses SET is_default = 1 WHERE id = :id")->execute(['id'=>$first['id']]);
                        }
                        add_msg($messages, 'success', 'Address deleted.');
                    }
                } catch (Throwable $e) {
                    error_log("Delete address error: ".$e->getMessage());
                    add_msg($messages, 'error', 'Unable to delete address.');
                }
            }
        }

        // SET DEFAULT
        if ($action === 'set_default') {
            $aid = (int)($_POST['address_id'] ?? 0);
            if ($aid <= 0) add_msg($messages, 'error', 'Invalid address id.');
            else {
                try {
                    $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = :uid")->execute(['uid'=>$userId]);
                    $pdo->prepare("UPDATE addresses SET is_default = 1 WHERE id = :id AND user_id = :uid")->execute(['id'=>$aid,'uid'=>$userId]);
                    add_msg($messages, 'success', 'Default address updated.');
                } catch (Throwable $e) {
                    error_log("Set default error: ".$e->getMessage());
                    add_msg($messages, 'error', 'Unable to update default address.');
                }
            }
        }

    } // csrf ok
} // POST

// --- fetch fresh data ---------------------------------------------
$stmt = $pdo->prepare("SELECT id,name,email,phone,created_at FROM users WHERE id = :id LIMIT 1");
$stmt->execute(['id'=>$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// addresses
try {
    $addrStmt = $pdo->prepare("SELECT id,label,full_name,phone,address_line1,address_line2,city,state,postal_code,country,is_default FROM addresses WHERE user_id = :uid ORDER BY is_default DESC, id DESC");
    $addrStmt->execute(['uid'=>$userId]);
    $addresses = $addrStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $addresses = [];
}

// recent orders (show 20)
$ordersStmt = $pdo->prepare("SELECT id,total,status,created_at FROM orders WHERE user_id = :uid ORDER BY created_at DESC LIMIT 20");
$ordersStmt->execute(['uid'=>$userId]);
$orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

// find default address
$defaultAddress = null;
foreach ($addresses as $a) { if (!empty($a['is_default'])) { $defaultAddress = $a; break; } }

function status_badge_class($status) {
    $s = strtolower((string)$status);
    if (strpos($s, 'pending') !== false) return 'badge bg-warning text-dark';
    if (strpos($s, 'processing') !== false) return 'badge bg-info text-dark';
    if (strpos($s, 'completed') !== false) return 'badge bg-success';
    if (strpos($s, 'cancel') !== false) return 'badge bg-danger';
    if (strpos($s, 'refunded') !== false) return 'badge bg-secondary';
    return 'badge bg-light text-dark';
}

include __DIR__ . '/includes/header.php';
?>

<style>
  body { background-color: #8B0000 !important; }
  .page-content { padding-top:120px; padding-bottom:60px; }
  .card-transparent { background: rgba(255,255,255,0.98); }
  .profile-avatar { width:84px;height:84px;border-radius:50%;display:inline-block;background:linear-gradient(135deg,#fff 0%,#f0f0f0 100%);color:#8B0000;font-weight:700;font-size:28px;line-height:84px;text-align:center;}
  .panel { margin-top:12px; padding:14px; border-radius:8px; background:#fff; box-shadow:0 1px 2px rgba(0,0,0,.06); }
  .panel-hidden { display:none; }
  .form-row { display:flex; gap:12px; }
  .form-row .form-control { flex:1; }
  /* generous spacing on inputs */
  .panel .form-control { margin-bottom:12px; padding:10px 12px; border-radius:6px; }
  label.form-label.small { font-size:0.9rem; color:#444; margin-bottom:6px; display:block; }

  /* address card list */
  .address-list { display:flex; flex-direction:column; gap:12px; margin-top:12px; }
  .address-card-select { display:flex; gap:12px; align-items:flex-start; padding:12px; border-radius:8px; border:1px solid #e6e6e6; background:#fff; }
  .address-card-select.selected { border-color:#0d6efd; box-shadow:0 6px 20px rgba(13,110,253,0.07); }
  .radio-wrap { flex:0 0 34px; display:flex; align-items:center; justify-content:center; }
  .addr-content { flex:1; }
  .addr-lines { margin-top:8px; white-space:pre-line; color:#333; }
  .panel-actions { margin-top:12px; display:flex; gap:8px; flex-wrap:wrap; }

  @media (max-width:576px) {
    .form-row { flex-direction:column; }
    .profile-actions .btn { width:100%; }
  }
</style>

<div class="page-content">
  <div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="mb-0 text-white">My Account</h2>
      <div><a href="logout.php" class="btn btn-outline-secondary text-white">Logout</a></div>
    </div>

    <!-- messages -->
    <?php if (!empty($messages['success'])): foreach ($messages['success'] as $m): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($m); ?></div>
    <?php endforeach; endif; ?>
    <?php if (!empty($messages['error'])): foreach ($messages['error'] as $m): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($m); ?></div>
    <?php endforeach; endif; ?>

    <div class="row g-4">
      <div class="col-lg-4">
        <div class="card card-transparent p-3 shadow-sm">
          <?php
            $initials = 'U';
            if (!empty($user['name'])) {
                $parts = preg_split('/\s+/', $user['name']);
                $initials = strtoupper(substr($parts[0],0,1) . (isset($parts[1]) ? substr($parts[1],0,1) : ''));
            }
          ?>
          <div class="d-flex gap-3 align-items-center">
            <div class="profile-avatar"><?php echo htmlspecialchars($initials); ?></div>
            <div>
              <h5 class="mb-0"><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></h5>
              <div class="small text-muted"><?php echo htmlspecialchars($user['email'] ?? ''); ?></div>
              <?php if (!empty($user['phone'])): ?><div class="small text-muted">📞 <?php echo htmlspecialchars($user['phone']); ?></div><?php endif; ?>

              <?php if (!empty($defaultAddress)): ?>
                <div class="mt-2">
                  <div class="small text-muted">Default address</div>
                  <div style="color:#333; margin-top:4px;">
                    <strong><?php echo htmlspecialchars($defaultAddress['label'] ?: 'Address'); ?></strong><br>
                    <?php if (!empty($defaultAddress['full_name'])) echo htmlspecialchars($defaultAddress['full_name']) . ' · '; ?>
                    <?php if (!empty($defaultAddress['phone'])) echo htmlspecialchars($defaultAddress['phone']); ?>
                    <div style="margin-top:6px;"><?php
                      $addr = htmlspecialchars($defaultAddress['address_line1']);
                      if (!empty($defaultAddress['address_line2'])) $addr .= ', ' . htmlspecialchars($defaultAddress['address_line2']);
                      $addr .= ', ' . htmlspecialchars($defaultAddress['city']);
                      if (!empty($defaultAddress['state'])) $addr .= ', ' . htmlspecialchars($defaultAddress['state']);
                      $addr .= ' - ' . htmlspecialchars($defaultAddress['postal_code']);
                      if (!empty($defaultAddress['country'])) $addr .= ', ' . htmlspecialchars($defaultAddress['country']);
                      echo $addr;
                    ?></div>
                  </div>
                </div>
              <?php endif; ?>

            </div>
          </div>

          <hr class="my-3">

          <div class="row g-2">
            <div class="col-6">
              <div class="stat-box">
                <div class="small text-muted">Orders</div>
                <div class="fw-bold"><?php echo (int)count($orders); ?></div>
              </div>
            </div>
            <div class="col-6">
              <div class="stat-box">
                <div class="small text-muted">Total spent</div>
                <?php
                  $totalSpent = 0;
                  foreach ($orders as $o) $totalSpent += (float)$o['total'];
                ?>
                <div class="fw-bold">₹<?php echo number_format($totalSpent,2); ?></div>
              </div>
            </div>
          </div>

          <hr class="my-3">
          <div class="small text-muted">Member since</div>
          <div class="mb-2 fw-medium">
            <?php if (!empty($user['created_at'])) { try { $dt=new DateTime($user['created_at']); echo $dt->format('F j, Y'); } catch(Exception $e){ echo htmlspecialchars($user['created_at']); } } else echo '—'; ?>
          </div>

          <div class="d-grid gap-2 profile-actions" role="toolbar">
            <button type="button" class="btn btn-outline-primary btn-sm" data-target="#editPanel">Edit profile</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-target="#passwordPanel">Change password</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-target="#addressesPanel">Manage addresses</button>
          </div>

          <!-- Edit panel (now posts to same page) -->
          <div id="editPanel" class="panel panel-hidden" aria-hidden="true">
            <strong>Edit profile</strong>
            <p class="small text-muted mb-2">Update your name and phone.</p>
            <form method="post" novalidate>
              <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
              <input type="hidden" name="action" value="edit_profile">
              <label class="form-label small">Full name</label>
              <input name="name" class="form-control" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>">
              <label class="form-label small">Phone</label>
              <input name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
              <div class="d-flex gap-2 mt-2">
                <button class="btn btn-primary btn-sm" type="submit">Save</button>
                <button type="button" class="btn btn-outline-secondary btn-sm panel-close">Close</button>
              </div>
            </form>
          </div>

          <!-- Change password panel (now posts to same page) -->
          <div id="passwordPanel" class="panel panel-hidden" aria-hidden="true">
            <strong>Change password</strong>
            <p class="small text-muted mb-2">Enter your current password and a new password.</p>
            <form method="post" novalidate>
              <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
              <input type="hidden" name="action" value="change_password">
              <label class="form-label small">Current password</label>
              <input name="current_password" class="form-control" type="password">
              <label class="form-label small">New password</label>
              <input name="new_password" class="form-control" type="password">
              <label class="form-label small">Confirm new</label>
              <input name="new_password2" class="form-control" type="password">
              <div class="d-flex gap-2 mt-2">
                <button class="btn btn-primary btn-sm" type="submit">Change</button>
                <button type="button" class="btn btn-outline-secondary btn-sm panel-close">Close</button>
              </div>
            </form>
          </div>

          <!-- Addresses panel kept as before -->
          <div id="addressesPanel" class="panel panel-hidden" aria-hidden="true">
            <strong>Manage addresses</strong>
            <p class="small text-muted mb-2">Choose an address to edit. The form below contains all fields.</p>

            <?php if (empty($addresses)): ?>
              <div class="text-muted">No addresses yet. Use the form below to add one.</div>
            <?php else: ?>
              <label class="form-label small">Select address</label>
              <select id="addressSelectDropdown" class="form-select" style="margin-bottom:12px;">
                <option value="">-- select address --</option>
                <?php foreach ($addresses as $a): 
                  $label = $a['label'] ?: 'Address';
                  $summary = $label . ' — ' . substr($a['address_line1'],0,40) . (empty($a['address_line2']) ? '' : ', ' . substr($a['address_line2'],0,20));
                ?>
                  <option value="<?php echo (int)$a['id']; ?>" data-full_name="<?php echo htmlspecialchars($a['full_name']); ?>" data-phone="<?php echo htmlspecialchars($a['phone']); ?>" data-line1="<?php echo htmlspecialchars($a['address_line1']); ?>" data-line2="<?php echo htmlspecialchars($a['address_line2']); ?>" data-city="<?php echo htmlspecialchars($a['city']); ?>" data-state="<?php echo htmlspecialchars($a['state']); ?>" data-postal="<?php echo htmlspecialchars($a['postal_code']); ?>" data-country="<?php echo htmlspecialchars($a['country']); ?>" data-label="<?php echo htmlspecialchars($a['label']); ?>" data-default="<?php echo !empty($a['is_default']) ? '1' : '0'; ?>">
                    <?php echo htmlspecialchars($summary); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            <?php endif; ?>

            <form id="addressEditForm" method="post" class="panel" style="display:none; padding:14px;">
              <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
              <input type="hidden" name="action" value="edit_address">
              <input type="hidden" name="address_id" id="address_id" value="">
              <label class="form-label small">Label (Home / Work)</label>
              <input name="label" id="label" class="form-control">
              <label class="form-label small">Full name (receiver)</label>
              <input name="full_name" id="full_name" class="form-control">
              <label class="form-label small">Phone (receiver)</label>
              <input name="phone_addr" id="phone_addr" class="form-control">
              <label class="form-label small">Address line 1</label>
              <input name="address_line1" id="address_line1" class="form-control" required>
              <label class="form-label small">Address line 2</label>
              <input name="address_line2" id="address_line2" class="form-control">
              <div class="form-row">
                <div>
                  <label class="form-label small">City</label>
                  <input name="city" id="city" class="form-control">
                </div>
                <div>
                  <label class="form-label small">State</label>
                  <input name="state" id="state" class="form-control">
                </div>
                <div>
                  <label class="form-label small">Postal code</label>
                  <input name="postal_code" id="postal_code" class="form-control">
                </div>
              </div>
              <label class="form-label small">Country</label>
              <input name="country" id="country" class="form-control">
              <div class="form-check" style="margin-top:8px;">
                <input class="form-check-input" type="checkbox" id="make_default_edit" name="make_default" value="1">
                <label class="form-check-label small" for="make_default_edit">Set as default</label>
              </div>
              <div class="panel-actions">
                <button type="submit" class="btn btn-primary btn-sm">Save changes</button>
                <button type="button" id="setDefaultBtn" class="btn btn-outline-secondary btn-sm">Make default</button>
                <button type="button" id="deleteAddressBtn" class="btn btn-outline-danger btn-sm">Delete</button>
                <button type="button" class="btn btn-outline-secondary btn-sm panel-close">Close</button>
              </div>
            </form>

            <div style="margin-top:12px;">
              <button id="showAddFormBtn" type="button" class="btn btn-success btn-sm">Add new address</button>
            </div>

            <form id="addAddressForm" method="post" style="display:none; margin-top:12px;" class="panel">
              <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
              <input type="hidden" name="action" value="add_address">
              <label class="form-label small">Label</label>
              <input name="label" class="form-control">
              <label class="form-label small">Full name</label>
              <input name="full_name" class="form-control">
              <label class="form-label small">Phone</label>
              <input name="phone_addr" class="form-control">
              <label class="form-label small">Address line 1</label>
              <input name="address_line1" class="form-control">
              <label class="form-label small">Address line 2</label>
              <input name="address_line2" class="form-control">
              <div class="form-row">
                <div>
                  <label class="form-label small">City</label>
                  <input name="city" class="form-control">
                </div>
                <div>
                  <label class="form-label small">State</label>
                  <input name="state" class="form-control">
                </div>
                <div>
                  <label class="form-label small">Postal code</label>
                  <input name="postal_code" class="form-control">
                </div>
              </div>
              <label class="form-label small">Country</label>
              <input name="country" class="form-control" value="India">
              <div class="form-check" style="margin-top:8px;">
                <input class="form-check-input" type="checkbox" id="make_default_add" name="make_default" value="1">
                <label class="form-check-label small" for="make_default_add">Set as default</label>
              </div>
              <div class="panel-actions">
                <button class="btn btn-success btn-sm" type="submit">Add address</button>
                <button id="hideAddFormBtn" type="button" class="btn btn-outline-secondary btn-sm">Cancel</button>
              </div>
            </form>

          </div>

        </div>
      </div>

      <!-- ORDERS / MAIN -->
      <div class="col-lg-8">
        <div class="card card-transparent p-3 shadow-sm mb-3">
          <h5 class="mb-0">Recent Orders (up to 20)</h5>
          <?php if (empty($orders)): ?>
            <div class="p-3 text-muted">You have not placed any orders yet.</div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Order</th><th>Total</th><th>Status</th><th>Date</th><th></th></tr></thead>
                <tbody>
                  <?php foreach ($orders as $o): ?>
                    <tr>
                      <td><a href="order_view.php?id=<?php echo (int)$o['id']; ?>">#<?php echo (int)$o['id']; ?></a></td>
                      <td>₹<?php echo number_format((float)$o['total'],2); ?></td>
                      <td><span class="<?php echo status_badge_class($o['status']); ?>"><?php echo htmlspecialchars($o['status']); ?></span></td>
                      <td><?php $d=@new DateTime($o['created_at']); echo $d ? $d->format('M j, Y') : htmlspecialchars($o['created_at']); ?></td>
                      <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="order_view.php?id=<?php echo (int)$o['id']; ?>">View</a></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
(function(){
  function hideAllPanels() {
    document.querySelectorAll('.panel').forEach(p=>p.classList.add('panel-hidden'));
  }

  // Toggle panels buttons (Edit/Password/Addresses)
  document.querySelectorAll('.profile-actions button[data-target]').forEach(btn=>{
    btn.addEventListener('click', function(e){
      const target = btn.getAttribute('data-target');
      const panel = document.querySelector(target);
      if (!panel) return;
      const isHidden = panel.classList.contains('panel-hidden');
      hideAllPanels();
      if (isHidden) panel.classList.remove('panel-hidden');
      e.stopPropagation();
    });
  });

  // panel close buttons
  document.querySelectorAll('.panel .panel-close').forEach(btn=>{
    btn.addEventListener('click', function(){
      const p = btn.closest('.panel');
      if (p) p.classList.add('panel-hidden');
    });
  });

  // addresses dropdown -> populate edit form
  const addrSelect = document.getElementById('addressSelectDropdown');
  const editForm = document.getElementById('addressEditForm');
  function clearEditForm() {
    ['address_id','label','full_name','phone_addr','address_line1','address_line2','city','state','postal_code','country'].forEach(id=>{
      const el = document.getElementById(id);
      if (el) el.value = '';
    });
    const chk = document.getElementById('make_default_edit');
    if (chk) chk.checked = false;
  }
  if (addrSelect) {
    addrSelect.addEventListener('change', function(){
      const val = this.value;
      if (!val) {
        editForm.style.display = 'none';
        return;
      }
      const opt = this.options[this.selectedIndex];
      // populate fields using data- attributes
      document.getElementById('address_id').value = val;
      document.getElementById('label').value = opt.getAttribute('data-label') || '';
      document.getElementById('full_name').value = opt.getAttribute('data-full_name') || '';
      document.getElementById('phone_addr').value = opt.getAttribute('data-phone') || '';
      document.getElementById('address_line1').value = opt.getAttribute('data-line1') || '';
      document.getElementById('address_line2').value = opt.getAttribute('data-line2') || '';
      document.getElementById('city').value = opt.getAttribute('data-city') || '';
      document.getElementById('state').value = opt.getAttribute('data-state') || '';
      document.getElementById('postal_code').value = opt.getAttribute('data-postal') || '';
      document.getElementById('country').value = opt.getAttribute('data-country') || '';
      document.getElementById('make_default_edit').checked = opt.getAttribute('data-default') === '1';
      // show form
      editForm.style.display = 'block';
      editForm.scrollIntoView({behavior:'smooth', block:'center'});
    });
  }

  // set default quick button - sends small POST via form
  const setDefaultBtn = document.getElementById('setDefaultBtn');
  if (setDefaultBtn) {
    setDefaultBtn.addEventListener('click', function(){
      const id = document.getElementById('address_id').value;
      if (!id) { alert('Select an address first'); return; }
      if (!confirm('Make this address default?')) return;
      const f = document.createElement('form'); f.method='post'; f.style.display='none';
      f.innerHTML = '<input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">' +
                    '<input type="hidden" name="action" value="set_default">' +
                    '<input type="hidden" name="address_id" value="'+id+'">';
      document.body.appendChild(f); f.submit();
    });
  }

  // delete address button - confirm then submit small form
  const deleteBtn = document.getElementById('deleteAddressBtn');
  if (deleteBtn) {
    deleteBtn.addEventListener('click', function(){
      const id = document.getElementById('address_id').value;
      if (!id) { alert('Select an address first'); return; }
      if (!confirm('Delete this address? This cannot be undone.')) return;
      const f = document.createElement('form'); f.method='post'; f.style.display='none';
      f.innerHTML = '<input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">' +
                    '<input type="hidden" name="action" value="delete_address">' +
                    '<input type="hidden" name="address_id" value="'+id+'">';
      document.body.appendChild(f); f.submit();
    });
  }

  // show/hide add new address form
  const showAddBtn = document.getElementById('showAddFormBtn');
  const addForm = document.getElementById('addAddressForm');
  const hideAddBtn = document.getElementById('hideAddFormBtn');
  if (showAddBtn && addForm) {
    showAddBtn.addEventListener('click', function(){ addForm.style.display = 'block'; addForm.scrollIntoView({behavior:'smooth'}); });
    hideAddBtn.addEventListener('click', function(){ addForm.style.display = 'none'; });
  }

  // clicking outside panels closes them
  document.addEventListener('click', function(e){
    if (e.target.closest('.panel') || e.target.closest('.profile-actions')) return;
    document.querySelectorAll('.panel').forEach(p=>p.classList.add('panel-hidden'));
  });

})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
