<?php
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_role('provider');

$u = current_user();

$stmt = $pdo->prepare("SELECT * FROM provider_profiles WHERE user_id=?");
$stmt->execute([(int)$u['id']]);
$profile = $stmt->fetch();

$title = "Provider dashboard - my_homeease";
require __DIR__ . '/../../app/views/header.php';

$img = $profile['cover_photo']
  ? base_url('../storage/uploads/' . $profile['cover_photo'])
  : 'https://placehold.co/1200x600?text=Upload+Cover+Photo';

$stmt = $pdo->prepare("SELECT * FROM services WHERE provider_id=? ORDER BY created_at DESC");
$stmt->execute([(int)$u['id']]);
$services = $stmt->fetchAll();

$stmt = $pdo->prepare("
SELECT b.*, s.title AS service_title, s.category, c.name AS customer_name,
       pay.method AS pay_method, pay.status AS pay_status, pay.amount AS pay_amount,
       pay.customer_confirmed, pay.provider_confirmed, pay.trx_id
FROM bookings b
JOIN services s ON s.id=b.service_id
JOIN users c ON c.id=b.customer_id
LEFT JOIN payments pay ON pay.booking_id=b.id
WHERE b.provider_id=?
ORDER BY b.created_at DESC
LIMIT 200
");
$stmt->execute([(int)$u['id']]);
$bookings = $stmt->fetchAll();

$vs = $profile['verification_status'] ?? 'not_submitted';
?>
<h1 class="h4 mb-3">Provider dashboard</h1>

<div class="row g-3 mb-4">
  <div class="col-12 col-lg-5">
    <div class="card">
      <img src="<?= e($img) ?>" class="card-img-top" style="object-fit:cover;height:200px;">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="fw-semibold"><?= e($profile['business_name']) ?></div>
            <div class="small text-muted"><?= e($profile['city'] ?? '—') ?></div>
          </div>
          <div>
            <?php if ((int)$profile['is_verified']===1): ?>
              <span class="badge text-bg-success">Verified</span>
            <?php else: ?>
              <span class="badge text-bg-secondary">Not verified</span>
            <?php endif; ?>
          </div>
        </div>

        <div class="small mt-2">
          <span class="text-muted">Verification status:</span>
          <span class="badge text-bg-<?=
            $vs==='approved'?'success':
            ($vs==='pending'?'warning':
            ($vs==='rejected'?'danger':'secondary'))
          ?>"><?= e($vs) ?></span>
          <?php if (!empty($profile['verification_note'])): ?>
            <div class="text-muted small mt-1">Note: <?= e($profile['verification_note']) ?></div>
          <?php endif; ?>
        </div>

        <div class="mt-3 d-flex gap-2 flex-wrap">
          <a class="btn btn-sm btn-dark" href="<?= e(base_url('provider/profile_edit.php')) ?>">Edit profile</a>
          <a class="btn btn-sm btn-outline-dark" href="<?= e(base_url('provider/upload_cover.php')) ?>">Upload cover photo</a>
          <a class="btn btn-sm btn-outline-dark" href="<?= e(base_url('provider/service_create.php')) ?>">Add service</a>
          <a class="btn btn-sm btn-outline-dark" href="<?= e(base_url('provider/subscriptions.php')) ?>">Subscriptions</a>

          <!-- New -->
          <a class="btn btn-sm btn-outline-success" href="<?= e(base_url('provider/verify_documents.php')) ?>">
            Submit NID & Trade License
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-7">
    <div class="card">
      <div class="card-body">
        <h2 class="h6">Your services</h2>
        <?php if (!$services): ?>
          <div class="alert alert-secondary">No services yet. Add your first service.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead><tr><th>Title</th><th>Category</th><th class="text-end">Price</th><th></th></tr></thead>
              <tbody>
              <?php foreach ($services as $s): ?>
                <tr>
                  <td><?= e($s['title']) ?></td>
                  <td><?= e($s['category']) ?></td>
                  <td class="text-end">৳<?= e(number_format((float)$s['base_price'],2)) ?></td>
                  <td class="text-end">
                    <a class="btn btn-sm btn-outline-secondary" href="<?= e(base_url('provider/service_edit.php?id='.(int)$s['id'])) ?>">Edit</a>
                  </td>
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

<h2 class="h5 mb-2">Booking requests</h2>
<div class="list-group">
  <?php if (!$bookings): ?>
    <div class="alert alert-secondary">No bookings yet.</div>
  <?php endif; ?>

  <?php foreach ($bookings as $b): ?>
    <div class="list-group-item">
      <div class="d-flex justify-content-between">
        <div>
          <div class="fw-semibold">
            <?= e($b['service_title']) ?>
            <span class="text-muted small">(<?= e($b['category']) ?>)</span>
          </div>
          <div class="text-muted small">
            Customer: <?= e($b['customer_name']) ?> • Scheduled: <?= e($b['scheduled_at']) ?>
          </div>

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
              Trx: <b><?= e($b['trx_id'] ?? '—') ?></b>
              • Customer confirmed: <?= ((int)$b['customer_confirmed']===1?'Yes':'No') ?>
              • Provider confirmed: <?= ((int)$b['provider_confirmed']===1?'Yes':'No') ?>
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
        </div>
      </div>

      <div class="mt-2 d-flex gap-2 flex-wrap">
        <a class="btn btn-sm btn-outline-dark" href="<?= e(base_url('provider/booking_update.php?id='.(int)$b['id'].'&to=accepted')) ?>">Accept</a>
        <a class="btn btn-sm btn-outline-danger" href="<?= e(base_url('provider/booking_update.php?id='.(int)$b['id'].'&to=declined')) ?>">Decline</a>
        <a class="btn btn-sm btn-outline-warning" href="<?= e(base_url('provider/booking_update.php?id='.(int)$b['id'].'&to=in_progress')) ?>">In progress</a>
        <a class="btn btn-sm btn-dark" href="<?= e(base_url('provider/booking_update.php?id='.(int)$b['id'].'&to=completed')) ?>">Complete</a>

        <?php
          $canConfirm = ($b['pay_method']==='bkash' || $b['pay_method']==='nagad')
                        && (int)$b['customer_confirmed']===1
                        && (int)$b['provider_confirmed']===0;
        ?>
        <?php if ($canConfirm): ?>
          <a class="btn btn-sm btn-success" href="<?= e(base_url('provider/payment_confirm.php?booking_id='.(int)$b['id'])) ?>">
            Confirm payment received
          </a>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../../app/views/footer.php'; ?>