<?php
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_role('customer');

$u = current_user();
$title = "My bookings - my_homeease";
require __DIR__ . '/../../app/views/header.php';

$stmt = $pdo->prepare("
SELECT b.*, s.title AS service_title, s.category, pp.business_name,
       pay.method AS pay_method, pay.status AS pay_status, pay.amount AS pay_amount,
       pay.customer_confirmed, pay.provider_confirmed, pay.trx_id
FROM bookings b
JOIN services s ON s.id=b.service_id
JOIN provider_profiles pp ON pp.user_id=b.provider_id
LEFT JOIN payments pay ON pay.booking_id=b.id
WHERE b.customer_id=?
ORDER BY b.created_at DESC
LIMIT 200
");
$stmt->execute([(int)$u['id']]);
$bookings = $stmt->fetchAll();
?>
<h1 class="h4 mb-3">My bookings</h1>

<div class="list-group">
  <?php if (!$bookings): ?>
    <div class="alert alert-secondary">No bookings yet. <a href="<?= e(base_url('services.php')) ?>">Browse services</a>.</div>
  <?php endif; ?>

  <?php foreach ($bookings as $b): ?>
    <div class="list-group-item">
      <div class="d-flex justify-content-between">
        <div>
          <div class="fw-semibold">
            <?= e($b['service_title']) ?>
            <span class="text-muted small">(<?= e($b['category']) ?>)</span>
          </div>
          <div class="text-muted small">Provider: <?= e($b['business_name']) ?></div>
          <div class="small">Scheduled: <?= e($b['scheduled_at']) ?></div>

          <div class="small mt-2">
            <span class="text-muted">Payment:</span>
            <span class="fw-semibold"><?= e($b['pay_method'] ?? '—') ?></span>
            • ৳<?= e(number_format((float)($b['pay_amount'] ?? 0),2)) ?>
            • <span class="badge text-bg-<?=
              ($b['pay_status']==='paid')?'success':
              (($b['pay_status']==='pending')?'warning':
              (($b['pay_status']==='unpaid')?'secondary':'danger'))
            ?>"><?= e($b['pay_status'] ?? '—') ?></span>
          </div>

          <?php if ($b['pay_method'] === 'bkash' || $b['pay_method'] === 'nagad'): ?>
            <div class="small text-muted">
              Trx: <b><?= e($b['trx_id'] ?? '—') ?></b> • You confirmed: <?= ((int)$b['customer_confirmed']===1?'Yes':'No') ?> • Provider confirmed: <?= ((int)$b['provider_confirmed']===1?'Yes':'No') ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="text-end">
          <span class="badge text-bg-<?=
            $b['status']==='completed'?'success':
            ($b['status']==='accepted'?'primary':
            ($b['status']==='declined'?'danger':
            ($b['status']==='in_progress'?'warning':'secondary')))
          ?>"><?= e($b['status']) ?></span>

          <div class="mt-2">
            <a class="btn btn-sm btn-outline-dark" href="<?= e(base_url('customer/review.php?booking_id='.(int)$b['id'])) ?>">Review</a>
          </div>
        </div>
      </div>

      <?php if ($b['notes']): ?><div class="text-muted mt-2"><?= e($b['notes']) ?></div><?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../../app/views/footer.php'; ?>