<?php
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_role('customer');

$u = current_user();

$booking_id = (int)($_GET['booking_id'] ?? 0);
if ($booking_id<=0) { http_response_code(404); echo "Not found"; exit; }

$stmt = $pdo->prepare("
SELECT b.*, s.title AS service_title, p.business_name
FROM bookings b
JOIN services s ON s.id=b.service_id
JOIN provider_profiles p ON p.user_id=b.provider_id
WHERE b.id=? AND b.customer_id=?
");
$stmt->execute([$booking_id, (int)$u['id']]);
$b = $stmt->fetch();
if (!$b) { http_response_code(404); echo "Not found"; exit; }

$title = "Review - my_homeease";
require __DIR__ . '/../../app/views/header.php';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  require_csrf();
  $rating = (int)($_POST['rating'] ?? 0);
  $comment = trim($_POST['comment'] ?? '');

  if ($b['status'] !== 'completed') {
    echo '<div class="alert alert-warning">You can review only after completion.</div>';
  } elseif ($rating < 1 || $rating > 5) {
    echo '<div class="alert alert-danger">Rating must be 1 to 5.</div>';
  } else {
    // upsert
    $stmt = $pdo->prepare("SELECT id FROM reviews WHERE booking_id=?");
    $stmt->execute([$booking_id]);
    $exists = $stmt->fetch();

    if ($exists) {
      $stmt = $pdo->prepare("UPDATE reviews SET rating=?, comment=? WHERE booking_id=? AND customer_id=?");
      $stmt->execute([$rating, $comment ?: null, $booking_id, (int)$u['id']]);
    } else {
      $stmt = $pdo->prepare("INSERT INTO reviews (booking_id, provider_id, customer_id, rating, comment) VALUES (?,?,?,?,?)");
      $stmt->execute([$booking_id, (int)$b['provider_id'], (int)$u['id'], $rating, $comment ?: null]);
    }

    flash_set("Review saved.", "success");
    redirect(base_url('customer/bookings.php'));
  }
}
?>
<h1 class="h4">Review</h1>

<div class="card">
  <div class="card-body">
    <div class="fw-semibold"><?= e($b['service_title']) ?></div>
    <div class="text-muted small">Provider: <?= e($b['business_name']) ?> • Status: <?= e($b['status']) ?></div>
    <hr>

    <form method="post">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <div class="mb-3">
        <label class="form-label">Rating</label>
        <select class="form-select" name="rating" required>
          <option value="">Select</option>
          <?php for ($i=5;$i>=1;$i--): ?>
            <option value="<?= $i ?>"><?= $i ?> star<?= $i>1?'s':'' ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Comment</label>
        <textarea class="form-control" name="comment" rows="3"></textarea>
      </div>
      <button class="btn btn-dark" type="submit">Save review</button>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../../app/views/footer.php'; ?>