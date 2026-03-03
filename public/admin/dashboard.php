<?php
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_role('admin');

$title = "Admin dashboard - my_homeease";
require __DIR__ . '/../../app/views/header.php';

/* Pending subscriptions (if you already added subscription system) */
$subs = [];
try {
  $subs = $pdo->query("
    SELECT ps.id, ps.status, ps.created_at,
           u.name AS owner_name,
           pp.business_name,
           sp.name AS plan_name, sp.price, sp.duration_days
    FROM provider_subscriptions ps
    JOIN users u ON u.id = ps.provider_id
    JOIN provider_profiles pp ON pp.user_id = ps.provider_id
    JOIN subscription_plans sp ON sp.id = ps.plan_id
    WHERE ps.status='pending'
    ORDER BY ps.created_at DESC
    LIMIT 50
  ")->fetchAll();
} catch (Exception $e) {
  // If subscription tables not installed yet, ignore.
}

/* Providers list (now includes doc fields + verification status) */
$providers = $pdo->query("
SELECT u.id, u.name, u.email, u.phone, p.business_name, p.city, p.is_verified, u.is_active,
       p.nid_file, p.trade_license_file, p.verification_status
FROM users u
JOIN provider_profiles p ON p.user_id=u.id
WHERE u.role='provider'
ORDER BY p.is_verified ASC, u.created_at DESC
LIMIT 200
")->fetchAll();

/* Bookings list */
$bookings = $pdo->query("
SELECT b.*, s.title AS service_title, s.category,
       c.name AS customer_name, p.business_name
FROM bookings b
JOIN services s ON s.id=b.service_id
JOIN users c ON c.id=b.customer_id
JOIN provider_profiles p ON p.user_id=b.provider_id
ORDER BY b.created_at DESC
LIMIT 200
")->fetchAll();
?>

<h1 class="h4 mb-3">Admin dashboard</h1>

<div class="alert alert-info">
  Single-admin mode is active. Verify providers only after checking NID & Trade License.
</div>

<?php if (!empty($subs)): ?>
  <h2 class="h5 mt-4">Pending subscriptions</h2>
  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead>
        <tr>
          <th>Business</th><th>Owner</th><th>Plan</th><th class="text-end">Price</th><th>Requested</th><th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($subs as $s): ?>
          <tr>
            <td><?= e($s['business_name']) ?></td>
            <td><?= e($s['owner_name']) ?></td>
            <td><?= e($s['plan_name']) ?> (<?= (int)$s['duration_days'] ?>d)</td>
            <td class="text-end">৳<?= e(number_format((float)$s['price'],2)) ?></td>
            <td><?= e($s['created_at']) ?></td>
            <td class="text-end">
              <a class="btn btn-sm btn-success" href="<?= e(base_url('admin/subscription_update.php?id='.(int)$s['id'].'&to=active')) ?>">Approve</a>
              <a class="btn btn-sm btn-outline-danger" href="<?= e(base_url('admin/subscription_update.php?id='.(int)$s['id'].'&to=rejected')) ?>">Reject</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="mt-2">
    <a class="btn btn-sm btn-outline-dark" href="<?= e(base_url('admin/plans.php')) ?>">Manage plans</a>
  </div>
<?php endif; ?>

<h2 class="h5 mt-5">Provider verification</h2>
<div class="table-responsive">
  <table class="table table-sm align-middle">
    <thead>
      <tr>
        <th>Business</th>
        <th>Owner</th>
        <th>Docs</th>
        <th>Status</th>
        <th>Verified</th>
        <th>Active</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($providers as $p): ?>
      <?php
        $hasDocs = (!empty($p['nid_file']) && !empty($p['trade_license_file']));
      ?>
      <tr>
        <td><?= e($p['business_name']) ?></td>
        <td><?= e($p['name']) ?></td>
        <td>
          <?= $hasDocs ? '<span class="badge text-bg-success">Submitted</span>' : '<span class="badge text-bg-secondary">Missing</span>' ?>
          <a class="btn btn-sm btn-outline-dark ms-2" href="<?= e(base_url('admin/provider_docs.php?id='.(int)$p['id'])) ?>">View</a>
        </td>
        <td>
          <span class="badge text-bg-<?=
            $p['verification_status']==='approved'?'success':
            ($p['verification_status']==='pending'?'warning':
            ($p['verification_status']==='rejected'?'danger':'secondary'))
          ?>"><?= e($p['verification_status'] ?? 'not_submitted') ?></span>
        </td>
        <td>
          <?= (int)$p['is_verified']===1 ? '<span class="badge text-bg-success">Yes</span>' : '<span class="badge text-bg-secondary">No</span>' ?>
        </td>
        <td>
          <?= (int)$p['is_active']===1 ? '<span class="badge text-bg-primary">Active</span>' : '<span class="badge text-bg-danger">Disabled</span>' ?>
        </td>
        <td class="text-end">
          <a class="btn btn-sm btn-outline-success" href="<?= e(base_url('admin/provider_toggle_verify.php?id='.(int)$p['id'].'&to=1')) ?>">Verify</a>
          <a class="btn btn-sm btn-outline-secondary" href="<?= e(base_url('admin/provider_toggle_verify.php?id='.(int)$p['id'].'&to=0')) ?>">Unverify</a>
          <a class="btn btn-sm btn-outline-danger" href="<?= e(base_url('admin/user_toggle_active.php?id='.(int)$p['id'].'&to=0')) ?>">Disable</a>
          <a class="btn btn-sm btn-outline-primary" href="<?= e(base_url('admin/user_toggle_active.php?id='.(int)$p['id'].'&to=1')) ?>">Enable</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<h2 class="h5 mt-5">Recent bookings</h2>
<div class="table-responsive">
  <table class="table table-sm align-middle">
    <thead>
      <tr>
        <th>ID</th><th>Service</th><th>Customer</th><th>Provider</th><th>Scheduled</th><th>Status</th><th>Created</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($bookings as $b): ?>
      <tr>
        <td>#<?= (int)$b['id'] ?></td>
        <td><?= e($b['service_title']) ?> <span class="text-muted small">(<?= e($b['category']) ?>)</span></td>
        <td><?= e($b['customer_name']) ?></td>
        <td><?= e($b['business_name']) ?></td>
        <td><?= e($b['scheduled_at']) ?></td>
        <td><?= e($b['status']) ?></td>
        <td><?= e($b['created_at']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/../../app/views/footer.php'; ?>