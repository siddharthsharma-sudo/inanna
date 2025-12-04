<?php
// checkout.php
require_once __DIR__ . '/includes/db.php';
session_start();

// must have cart
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// require login to checkout
if (empty($_SESSION['user_id'])) {
    $_SESSION['after_login'] = 'checkout.php';
    header('Location: login.php');
    exit;
}

$uid = (int)$_SESSION['user_id'];

/*
 * AJAX endpoint -- moved to top so it can return JSON BEFORE any HTML output.
 * URL: checkout.php?ajax_get_address=ID
 */
if (isset($_GET['ajax_get_address'])) { // << MOVED AJAX
    header('Content-Type: application/json; charset=utf-8');
    $aid = (int)($_GET['ajax_get_address'] ?? 0);
    if ($aid <= 0) {
        echo json_encode(['ok' => false, 'error' => 'invalid id']);
        exit;
    }
    try {
        $q = $pdo->prepare("SELECT * FROM addresses WHERE id = :id AND user_id = :uid LIMIT 1");
        $q->execute(['id'=>$aid, 'uid'=>$uid]);
        $a = $q->fetch(PDO::FETCH_ASSOC);
        if (!$a) {
            echo json_encode(['ok' => false, 'error' => 'not found']);
            exit;
        }
        echo json_encode(['ok' => true, 'address' => $a]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => 'server error']);
        exit;
    }
} // end MOVED AJAX

// handle POST actions: save_address (create/update), delete_address, proceed to place order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Add or update address
    if ($action === 'save_address') {
        // Basic sanitation/validation
        $id = (int)($_POST['address_id'] ?? 0);
        $label = trim($_POST['label'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address_line1 = trim($_POST['address_line1'] ?? '');
        $address_line2 = trim($_POST['address_line2'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $postal_code = trim($_POST['postal_code'] ?? '');
        $country = trim($_POST['country'] ?? 'India');
        $is_default = !empty($_POST['is_default']) ? 1 : 0;

        // minimal validation
        if ($full_name === '' || $address_line1 === '' || $city === '') {
            $_SESSION['checkout_error'] = 'Please provide full name, address line 1 and city.';
            header('Location: checkout.php');
            exit;
        }

        try {
            if ($is_default) {
                // unset other defaults for this user
                $q = $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = :uid");
                $q->execute(['uid' => $uid]);
            }

            if ($id > 0) {
                // update
                $upd = $pdo->prepare("UPDATE addresses SET label=:label, full_name=:full_name, phone=:phone, address_line1=:a1, address_line2=:a2, city=:city, state=:state, postal_code=:pc, country=:country, is_default=:is_default WHERE id = :id AND user_id = :uid");
                $upd->execute([
                    'label'=>$label,
                    'full_name'=>$full_name,
                    'phone'=>$phone,
                    'a1'=>$address_line1,
                    'a2'=>$address_line2,
                    'city'=>$city,
                    'state'=>$state,
                    'pc'=>$postal_code,
                    'country'=>$country,
                    'is_default'=>$is_default,
                    'id'=>$id,
                    'uid'=>$uid
                ]);
                $_SESSION['checkout_msg'] = 'Address updated.';
            } else {
                // insert
                $ins = $pdo->prepare("INSERT INTO addresses (user_id, label, full_name, phone, address_line1, address_line2, city, state, postal_code, country, is_default) VALUES (:uid,:label,:full_name,:phone,:a1,:a2,:city,:state,:pc,:country,:is_default)");
                $ins->execute([
                    'uid'=>$uid,
                    'label'=>$label,
                    'full_name'=>$full_name,
                    'phone'=>$phone,
                    'a1'=>$address_line1,
                    'a2'=>$address_line2,
                    'city'=>$city,
                    'state'=>$state,
                    'pc'=>$postal_code,
                    'country'=>$country,
                    'is_default'=>$is_default
                ]);
                $_SESSION['checkout_msg'] = 'Address added.';
            }
        } catch (Exception $e) {
            $_SESSION['checkout_error'] = 'Address save failed: ' . htmlspecialchars($e->getMessage());
        }

        header('Location: checkout.php');
        exit;
    }

    // Delete address
    if ($action === 'delete_address') {
        $id = (int)($_POST['address_id'] ?? 0);
        if ($id > 0) {
            try {
                $del = $pdo->prepare("DELETE FROM addresses WHERE id = :id AND user_id = :uid");
                $del->execute(['id'=>$id,'uid'=>$uid]);
                $_SESSION['checkout_msg'] = 'Address deleted.';
            } catch (Exception $e) {
                $_SESSION['checkout_error'] = 'Failed to delete address.';
            }
        }
        header('Location: checkout.php');
        exit;
    }

    // Original checkout submission - proceed to place order
    if ($action === 'place_order') {
        $address_id = (int)($_POST['address_id'] ?? 0);
        $payment_method = $_POST['payment_method'] ?? 'cod';

        if ($address_id <= 0) {
            $_SESSION['checkout_error'] = 'Please select an address or add one in your Address Book.';
            header('Location: checkout.php');
            exit;
        }

        $_SESSION['order'] = [
            'address_id' => $address_id,
            'payment_method' => $payment_method
        ];
        header('Location: place_order.php');
        exit;
    }
}

// fetch user's addresses for selection
$stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = :uid ORDER BY is_default DESC, id DESC");
$stmt->execute(['uid' => $uid]);
$addresses = $stmt->fetchAll();

// messages
$msg = $_SESSION['checkout_msg'] ?? null; if ($msg) unset($_SESSION['checkout_msg']);
$err = $_SESSION['checkout_error'] ?? null; if ($err) unset($_SESSION['checkout_error']);

include __DIR__ . '/includes/header.php';
?>
<style>
    :root { --header-offset: 90px; }
    body { background: #6b0000 !important; }
    .checkout-container { padding-top: var(--header-offset); padding-bottom: 40px; min-height: calc(100vh - var(--header-offset)); }
    .card { background: #fff; color:rgb(34, 38, 42); }
    h3, h5 { color: black; }
    .address-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); color: #fff; padding:12px; border-radius:8px; }
    .address-actions { margin-top:8px; }
    .small-muted-white { color: rgba(53, 51, 51, 0.85); }
    .btn-outline-light-trans { border-color: rgba(255,255,255,0.18); color: #fff; background: transparent; }
    .btn-outline-light-trans-add { border-color: rgba(255,255,255,0.18); color: #fff !important; background: black; }
    /* inline address form */
    #addrFormWrap { background: rgba(0,0,0,0.28); padding: 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.08); color:#fff; }
    #addrFormWrap .form-control { background: rgba(255,255,255,0.07); color: #fff; border:1px solid rgba(255,255,255,0.08); }
</style>

<div class="container checkout-container" style="max-width:980px;">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h3 class="text-white">Checkout</h3>
    <div>
      <!-- BACK-TO-CART CLICK FALLBACK: href kept but onclick ensures navigation if link is blocked by overlay -->
      <a href="cart.php" onclick="window.location.href='cart.php';" class="btn btn-outline-light-trans">Back to cart</a>
    </div>
  </div>

  <?php if ($err): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
  <?php endif; ?>
  <?php if ($msg): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
  <?php endif; ?>

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card p-3 mb-3">
        <h5>Shipping address</h5>

        <?php if (empty($addresses)): ?>
          <div class="p-3 text-muted">You have no saved addresses. Use the form to add one below.</div>
          <!-- << ALWAYS SHOW ADD: show Add New Address even when there are no saved addresses -->
          <div class="mt-3">
            <button type="button" class="btn btn-outline-light-trans-add" onclick="showAddForm();">Add New Address</button>
          </div>
        <?php else: ?>
          <!-- KEEP A SINGLE FORM for the checkout submission -->
          <form method="post" id="checkoutForm">
            <input type="hidden" name="action" value="place_order">
            <div class="mb-3">
              <?php foreach ($addresses as $a): ?>
                <div class="mb-2 p-2 address-card">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="address_id" id="addr_<?php echo $a['id']; ?>" value="<?php echo $a['id']; ?>" <?php echo $a['is_default'] ? 'checked' : ''; ?> >
                    <label class="form-check-label small-muted-white" for="addr_<?php echo $a['id']; ?>">
                      <strong><?php echo htmlspecialchars($a['label'] ?: 'Address'); ?></strong>
                      &nbsp; &nbsp; <span class="small text-muted">(<?php echo htmlspecialchars($a['full_name']); ?>)</span>
                    </label>
                  </div>
                  <div class="small-muted-white mt-1">
                    <?php echo nl2br(htmlspecialchars($a['address_line1'] . ($a['address_line2'] ? ', '.$a['address_line2'] : '') . ', ' . $a['city'] . ($a['state'] ? ', '.$a['state'] : '') . ' - ' . $a['postal_code'] . ($a['country'] ? ', '.$a['country'] : ''))); ?>
                  </div>
                  <div class="address-actions">
                    <button type="button" class="btn btn-sm btn-light" onclick="editAddress(<?php echo $a['id']; ?>)">Edit</button>

                    <!-- REPLACED nested form with JS-powered delete to avoid nested forms -->
                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDeleteAddress(<?php echo $a['id']; ?>)">Delete</button>

                    <?php if ($a['is_default']): ?>
                      <span class="badge bg-success ms-2">Default</span>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <hr>

            <h5>Payment method</h5>
            <div class="form-check mb-2">
              <input class="form-check-input" type="radio" name="payment_method" id="pm_cod" value="cod" checked>
              <label class="form-check-label" for="pm_cod">Cash on Delivery</label>
            </div>
            <div class="form-check mb-3">
              <input class="form-check-input" type="radio" name="payment_method" id="pm_online" value="online">
              <label class="form-check-label" for="pm_online">Pay Online</label>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-success">Place order</button>
              <!-- << ALWAYS SHOW ADD: keep Add New Address visible here too when addresses exist -->
              <button type="button" class="btn btn-outline-light-trans-add" onclick="showAddForm();">Add New Address</button>
            </div>
          </form>
        <?php endif; ?>
      </div>

      <!-- Inline add/edit address form (hidden by default) -->
      <div id="addrFormHolder" style="display:none;">
        <div class="card p-3 mb-3" id="addrFormWrap">
          <h4 id="addrFormTitle">Add address</h4>

          <form method="post" id="addrForm">
            <input type="hidden" name="action" value="save_address">
            <input type="hidden" name="address_id" id="address_id" value="0">

            <div class="mb-2">
              <label class="form-label small-muted">Label (Home, Office)</label>
              <input name="label" id="label" class="form-control" placeholder="Label (optional)">
            </div>

            <div class="mb-2">
              <label class="form-label small-muted">Full name</label>
              <input name="full_name" id="full_name" class="form-control" required>
            </div>

            <div class="mb-2">
              <label class="form-label small-muted">Phone</label>
              <input name="phone" id="phone" class="form-control">
            </div>

            <div class="mb-2">
              <label class="form-label small-muted">Address line 1</label>
              <input name="address_line1" id="address_line1" class="form-control" required>
            </div>

            <div class="mb-2">
              <label class="form-label small-muted">Address line 2</label>
              <input name="address_line2" id="address_line2" class="form-control">
            </div>

            <div class="row g-2">
              <div class="col-md-6 mb-2">
                <label class="form-label small-muted">City</label>
                <input name="city" id="city" class="form-control" required>
              </div>
              <div class="col-md-6 mb-2">
                <label class="form-label small-muted">State</label>
                <input name="state" id="state" class="form-control">
              </div>
              <div class="col-md-6 mb-2">
                <label class="form-label small-muted">Postal code</label>
                <input name="postal_code" id="postal_code" class="form-control">
              </div>
              <div class="col-md-6 mb-2">
                <label class="form-label small-muted">Country</label>
                <input name="country" id="country" class="form-control" value="India">
              </div>
            </div>

            <div class="form-check form-switch mt-2 mb-2">
              <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1">
              <label class="form-check-label small-muted" for="is_default">Set as default</label>
            </div>

            <div class="d-flex gap-2">
              <button class="btn btn-primary" type="submit">Save address</button>
              <button class="btn btn-outline-secondary" type="button" onclick="hideAddForm();">Cancel</button>
            </div>
          </form>
        </div>
      </div>

    </div> <!-- left column -->

    <div class="col-lg-5">
      <div class="card p-3 mb-3">
        <h5>Order summary</h5>
        <?php
          $cart = $_SESSION['cart'] ?? [];
          $total = 0;
        ?>
        <?php if (empty($cart)): ?>
          <div class="p-3 text-muted">Cart is empty.</div>
        <?php else: ?>
          <table class="table table-sm">
            <thead><tr><th>Item</th><th class="text-end">Total</th></tr></thead>
            <tbody>
              <?php foreach ($cart as $it): 
                $subtotal = ($it['price'] ?? 0) * ($it['qty'] ?? 0);
                $total += $subtotal;
              ?>
                <tr>
                  <td>
                    <div style="font-weight:600;"><?php echo htmlspecialchars($it['name']); ?></div>
                    <div class="small text-muted">Qty: <?php echo (int)$it['qty']; ?> &nbsp; Price: ₹<?php echo number_format($it['price'],2); ?></div>
                  </td>
                  <td class="text-end">₹<?php echo number_format($subtotal,2); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr>
                <td class="text-end"><strong>Total</strong></td>
                <td class="text-end"><strong>₹<?php echo number_format($total,2); ?></strong></td>
              </tr>
            </tfoot>
          </table>
        <?php endif; ?>
      </div>

      <div class="card p-3">
        <h5>Need help?</h5>
        <p class="small-muted-white">Contact us on (+91) 7456000222 or email worldofinanna@gmail.com</p>
      </div>
    </div>

  </div> <!-- row -->
</div>

<script>
  // client-side helpers for showing/hiding add/edit address form
  const addrFormHolder = document.getElementById('addrFormHolder');
  const addrForm = document.getElementById('addrForm');
  const addrFormTitle = document.getElementById('addrFormTitle');

  function showAddForm() {
    addrFormTitle.textContent = 'Add address';
    document.getElementById('address_id').value = '0';
    addrForm.reset();
    // set country default to India if present
    if (document.getElementById('country')) document.getElementById('country').value = 'India';
    addrFormHolder.style.display = 'block';
    window.scrollTo({ top: addrFormHolder.offsetTop - 80, behavior: 'smooth' });
  }

  function hideAddForm() {
    addrFormHolder.style.display = 'none';
  }

  // populate edit form using AJAX (calls the endpoint moved to top)
  function editAddress(id) {
    fetch('?ajax_get_address=' + encodeURIComponent(id))
      .then(r => r.json())
      .then(data => {
        if (data && data.ok) {
          const a = data.address;
          document.getElementById('address_id').value = a.id;
          document.getElementById('label').value = a.label || '';
          document.getElementById('full_name').value = a.full_name || '';
          document.getElementById('phone').value = a.phone || '';
          document.getElementById('address_line1').value = a.address_line1 || '';
          document.getElementById('address_line2').value = a.address_line2 || '';
          document.getElementById('city').value = a.city || '';
          document.getElementById('state').value = a.state || '';
          document.getElementById('postal_code').value = a.postal_code || '';
          document.getElementById('country').value = a.country || 'India';
          document.getElementById('is_default').checked = a.is_default == 1 ? true : false;
          addrFormTitle.textContent = 'Edit address';
          addrFormHolder.style.display = 'block';
          window.scrollTo({ top: addrFormHolder.offsetTop - 80, behavior: 'smooth' });
        } else {
          alert('Failed to load address for editing.');
        }
      })
      .catch(()=> alert('Failed to load address for editing.'));
  }

  // Confirm+Delete using a small programmatic form to avoid nested forms in HTML
  function confirmDeleteAddress(id) {
    if (!confirm('Delete this address?')) return;
    // create a tiny form and submit
    const f = document.createElement('form');
    f.method = 'post';
    f.style.display = 'none';
    const a = document.createElement('input'); a.name = 'action'; a.value = 'delete_address'; f.appendChild(a);
    const b = document.createElement('input'); b.name = 'address_id'; b.value = String(id); f.appendChild(b);
    document.body.appendChild(f);
    f.submit();
  }
</script>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
(function(){
  // helper: get selected payment method (from the form)
  function getSelectedPaymentMethod() {
    const el = document.querySelector('input[name="payment_method"]:checked');
    return el ? el.value : null;
  }

  // helper: get selected address id
  function getSelectedAddressId() {
    const el = document.querySelector('input[name="address_id"]:checked');
    return el ? el.value : null;
  }

  // Safety: small helper to safely parse JSON
  function tryParseJSON(text) {
    try { return JSON.parse(text); } catch(e) { return null; }
  }

  // intercept the checkout form
  const checkoutForm = document.getElementById('checkoutForm');
  if (!checkoutForm) return;

  checkoutForm.addEventListener('submit', function(e){
    const pm = getSelectedPaymentMethod();
    if (pm !== 'online') {
      // default submit (COD) — allow normal POST to place_order.php
      return;
    }

    // online payment path: prevent normal submit
    e.preventDefault();

    const addressId = getSelectedAddressId();
    if (!addressId) {
      alert('Please select an address before paying online.');
      return;
    }

    // disable submit button while processing
    const submitBtn = checkoutForm.querySelector('button[type="submit"]');
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.dataset.origText = submitBtn.textContent;
      submitBtn.textContent = 'Preparing payment...';
    }

    // -------------------------
    // 1) Create Razorpay order (debuggable)
    // -------------------------
    fetch('create_razorpay_order.php', { method: 'POST', credentials: 'same-origin' })
      .then(async (r) => {
        const status = r.status;
        const text = await r.text();
        console.log('create_razorpay_order HTTP status:', status);
        console.log('create_razorpay_order raw response:', text);
        let json = null;
        try { json = JSON.parse(text); } catch(e) {}
        return { status, json, text };
      })
      .then(function(resp) {
        const respJson = resp.json;
        if (!respJson || respJson.error) {
          // Show helpful message
          const message = respJson && respJson.error ? respJson.error : resp.text || 'Server error';
          alert('Error preparing payment: ' + message);
          if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = submitBtn.dataset.origText || 'Place order'; }
          return;
        }

        const respData = respJson;

        // 2) open Razorpay checkout (same as before)
        const options = {
          key: respData.key, // returned by create_razorpay_order.php (RAZORPAY_KEY_ID)
          amount: respData.amount, // in paise
          currency: respData.currency,
          name: "Your Store Name",
          description: "Order: " + (respData.receipt || respData.order_id),
          order_id: respData.order_id,
          handler: function (paymentResp) {
            // paymentResp contains: razorpay_payment_id, razorpay_order_id, razorpay_signature
            // Send these to server for verification along with address_id
            const payload = new URLSearchParams();
            payload.append('razorpay_payment_id', paymentResp.razorpay_payment_id);
            payload.append('razorpay_order_id', paymentResp.razorpay_order_id);
            payload.append('razorpay_signature', paymentResp.razorpay_signature);
            payload.append('address_id', addressId);

            // -------------------------
            // VERIFY: debug fetch that logs full status + raw response
            // -------------------------
            fetch('verify_razorpay_payment.php', {
              method: 'POST',
              credentials: 'same-origin',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: payload.toString()
            })
            .then(async (r) => {
              const status = r.status;
              const text = await r.text();
              console.log('verify_razorpay_payment HTTP status:', status);
              console.log('verify_razorpay_payment raw response:', text);
              let json = null;
              try { json = JSON.parse(text); } catch(e) {}
              return { status, json, text };
            })
            .then(function(resp) {
              if (!resp.json) {
                // Not JSON — show full text for debugging and fail-safe redirect
                alert('Server returned (not JSON): ' + resp.text);
                window.location.href = 'payment_failed.php';
                return;
              }
              const verifyResp = resp.json;
              if (verifyResp && verifyResp.success) {
                // redirect to order success (server returned order id)
                window.location.href = 'order_success.php?order_id=' + encodeURIComponent(verifyResp.order_id);
              } else {
                // show any debug message returned by server
                const msg = (verifyResp && (verifyResp.error || verifyResp.message)) ? (verifyResp.error || '') + (verifyResp.message ? '\\n' + verifyResp.message : '') : 'Unknown';
                alert('Payment verification failed: ' + msg);
                window.location.href = 'payment_failed.php';
              }
            })
            .catch(function(err){
              console.error('verify fetch error', err);
              alert('Network or JS error while verifying payment.');
              window.location.href = 'payment_failed.php';
            });
          },
          prefill: {
            name: "<?= htmlspecialchars($_SESSION['user_name'] ?? '', ENT_QUOTES) ?>",
            email: "<?= htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES) ?>"
          },
          theme: { color: "#3399cc" }
        };

        const rzp = new Razorpay(options);
        rzp.on('payment.failed', function(response){
          alert('Payment failed: ' + (response.error && response.error.description ? response.error.description : 'Unknown error'));
          if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = submitBtn.dataset.origText || 'Place order'; }
        });
        rzp.open();
        // re-enable button after opening (Razorpay handles the rest)
        if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = submitBtn.dataset.origText || 'Place order'; }
      })
      .catch(function(){
        alert('Network error while creating payment order.');
        if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = submitBtn.dataset.origText || 'Place order'; }
      });
  });
})();
</script>


<?php include __DIR__ . '/includes/footer.php'; ?>
