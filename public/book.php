<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

$service_id = (int)($_GET['service_id'] ?? 0);
if ($service_id <= 0) { http_response_code(404); echo "Not found"; exit; }

$stmt = $pdo->prepare("
SELECT s.*, pp.business_name, pp.is_verified, pp.bkash_number, pp.nagad_number
FROM services s
JOIN provider_profiles pp ON pp.user_id = s.provider_id
JOIN users u ON u.id = s.provider_id
WHERE s.id=? AND s.is_active=1 AND u.is_active=1
");
$stmt->execute([$service_id]);
$service = $stmt->fetch();
if (!$service) { http_response_code(404); echo "Not found"; exit; }

$title = "Book - my_homeease";
require __DIR__ . '/../app/views/header.php';

$pay_method = $_POST['pay_method'] ?? 'cash';
$trx_id = trim($_POST['trx_id'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_csrf();
  require_login();
  $u = current_user();

  if ($u['role'] !== 'customer') {
    flash_set("Only customers can book services. Please login as a customer.", "warning");
    redirect(base_url('login.php'));
  }

  $scheduled_at = trim($_POST['scheduled_at'] ?? '');
  $address = trim($_POST['customer_address'] ?? '');
  $notes = trim($_POST['notes'] ?? '');

  $errors = [];
  $dt = date_create($scheduled_at);
  if (!$dt) $errors[] = "Invalid date/time.";
  if (!$scheduled_at) $errors[] = "Scheduled date/time required.";

  $allowed_methods = ['cash','bkash','nagad'];
  if (!in_array($pay_method, $allowed_methods, true)) $errors[] = "Invalid payment method.";

  // Require trx for digital payments
  if (($pay_method === 'bkash' || $pay_method === 'nagad') && $trx_id === '') {
    $errors[] = "Transaction ID is required for bKash/Nagad.";
  }

  // Optional: ensure provider has number set
  if ($pay_method === 'bkash' && !$service['bkash_number']) $errors[] = "Provider has no bKash number set.";
  if ($pay_method === 'nagad' && !$service['nagad_number']) $errors[] = "Provider has no Nagad number set.";

  if ($errors) {
    echo '<div class="alert alert-danger">'.e(implode(" ", $errors)).'</div>';
  } else {
    $pdo->beginTransaction();
    try {
      // booking
      $stmt = $pdo->prepare("
        INSERT INTO bookings (customer_id, provider_id, service_id, scheduled_at, customer_address, notes)
        VALUES (?,?,?,?,?,?)
      ");
      $stmt->execute([
        (int)$u['id'],
        (int)$service['provider_id'],
        (int)$service['id'],
        $dt->format('Y-m-d H:i:s'),
        $address ?: null,
        $notes ?: null
      ]);
      $booking_id = (int)$pdo->lastInsertId();

      // payment
      $amount = (float)$service['base_price'];

      $customer_confirmed = 0;
      $provider_confirmed = 0;
      $status = 'unpaid';

      if ($pay_method === 'cash') {
        // customer will pay later
        $status = 'unpaid';
      } else {
        // customer claims paid and submitted trx
        $customer_confirmed = 1;
        $status = 'pending';
      }

      $stmt = $pdo->prepare("
        INSERT INTO payments (booking_id, method, amount, trx_id, status, customer_confirmed, provider_confirmed)
        VALUES (?,?,?,?,?,?,?)
      ");
      $stmt->execute([
        $booking_id,
        $pay_method,
        $amount,
        ($trx_id !== '' ? $trx_id : null),
        $status,
        $customer_confirmed,
        $provider_confirmed
      ]);

      $pdo->commit();

      flash_set("Booking request sent! Payment saved ({$pay_method}).", "success");
      redirect(base_url('customer/bookings.php'));
    } catch (Exception $ex) {
      $pdo->rollBack();
      echo '<div class="alert alert-danger">Could not create booking/payment. Try again.</div>';
    }
  }
}
?>

<h1 class="h4">Book service</h1>

<div class="card">
  <div class="card-body">
    <div class="text-muted small"><?= e($service['category']) ?></div>
    <div class="h5 mb-1"><?= e($service['title']) ?></div>
    <div class="text-muted">
      Provider: <a href="<?= e(base_url('provider_profile.php?id='.(int)$service['provider_id'])) ?>"><?= e($service['business_name']) ?></a>
      <?php if ((int)$service['is_verified']===1): ?><span class="badge text-bg-success ms-1">Verified</span><?php endif; ?>
    </div>
    <div class="mt-2 fw-semibold">Base price: ৳<?= e(number_format((float)$service['base_price'],2)) ?></div>

    <hr>

    <div class="alert alert-info">
      <div class="fw-semibold">Payment instructions</div>
      <div class="small text-muted">If you choose bKash/Nagad, send money to provider number and enter the Transaction ID.</div>
      <div class="small mt-2">
        <b>bKash:</b> <?= e($service['bkash_number'] ?? 'Not set') ?> |
        <b>Nagad:</b> <?= e($service['nagad_number'] ?? 'Not set') ?>
      </div>
    </div>

    <?php if (!is_logged_in()): ?>
      <div class="alert alert-warning">Please <a href="<?= e(base_url('login.php')) ?>">login</a> as a customer to book.</div>
    <?php endif; ?>

    <form method="post" id="bookForm">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

      <div class="mb-3">
        <label class="form-label">Preferred date & time</label>
        <input type="datetime-local" name="scheduled_at" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Your address</label>
        <input type="text" name="customer_address" class="form-control" placeholder="House/road, area">
      </div>

      <div class="mb-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="3" placeholder="Problem details..."></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Payment method</label>
        <select class="form-select" name="pay_method" id="pay_method" required>
          <option value="cash"  <?= $pay_method==='cash'?'selected':'' ?>>Cash on Service</option>
          <option value="bkash" <?= $pay_method==='bkash'?'selected':'' ?>>bKash</option>
          <option value="nagad" <?= $pay_method==='nagad'?'selected':'' ?>>Nagad</option>
        </select>
      </div>

      <div class="mb-3" id="trx_box" style="display:none;">
        <label class="form-label">Transaction ID (bKash/Nagad)</label>
        <input type="text" class="form-control" name="trx_id" id="trx_id" value="<?= e($trx_id) ?>" placeholder="e.g., 9A7B2Cxxxx">
      </div>

      <button class="btn btn-dark" type="submit">Submit booking request</button>
    </form>
  </div>
</div>

<script>
  const sel = document.getElementById('pay_method');
  const box = document.getElementById('trx_box');
  const trx = document.getElementById('trx_id');

  function toggleTrx(){
    const v = sel.value;
    const need = (v === 'bkash' || v === 'nagad');
    box.style.display = need ? 'block' : 'none';
    trx.required = need;
  }
  sel.addEventListener('change', toggleTrx);
  toggleTrx();
</script>

<?php require __DIR__ . '/../app/views/footer.php'; ?>