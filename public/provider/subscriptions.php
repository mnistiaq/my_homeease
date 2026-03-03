<?php
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_role('provider');

$u = current_user();
$title = "Subscriptions - my_homeease";
require __DIR__ . '/../../app/views/header.php';

// active plans
$plans = $pdo->query("SELECT * FROM subscription_plans WHERE is_active=1 ORDER BY boost_level DESC, price ASC")->fetchAll();

// provider latest subscription
$stmt = $pdo->prepare("
  SELECT ps.*, sp.name AS plan_name, sp.price, sp.duration_days, sp.boost_level
  FROM provider_subscriptions ps
  JOIN subscription_plans sp ON sp.id = ps.plan_id
  WHERE ps.provider_id=?
  ORDER BY ps.created_at DESC
  LIMIT 1
");
$stmt->execute([(int)$u['id']]);
$latest = $stmt->fetch();
?>

<h1 class="h4 mb-3">Subscription plans</h1>

<?php if ($latest): ?>
  <div class="alert alert-info">
    <div><b>Your latest subscription:</b> <?= e($latest['plan_name']) ?> (<?= e($latest['status']) ?>)</div>
    <?php if ($latest['status']==='active'): ?>
      <div class="small text-muted">Valid: <?= e($latest['start_date']) ?> → <?= e($latest['end_date']) ?></div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<div class="row g-3">
  <?php foreach ($plans as $p): ?>
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="h5 mb-0"><?= e($p['name']) ?></div>
              <div class="text-muted small">Visibility boost: <?= (int)$p['boost_level'] ?></div>
            </div>
            <?php if ((float)$p['price']<=0): ?>
              <span class="badge text-bg-secondary">Free</span>
            <?php else: ?>
              <span class="badge text-bg-success">Paid</span>
            <?php endif; ?>
          </div>

          <div class="mt-3">
            <div class="fw-semibold">৳<?= e(number_format((float)$p['price'],2)) ?></div>
            <div class="text-muted small">Duration: <?= (int)$p['duration_days'] ?> days</div>
          </div>

          <div class="mt-3">
            <a class="btn btn-dark w-100" href="<?= e(base_url('provider/subscribe.php?plan_id='.(int)$p['id'])) ?>">
              Choose <?= e($p['name']) ?>
            </a>
          </div>

          <div class="small text-muted mt-2">
            After choosing, admin approves and your profile gets boosted on homepage.
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../../app/views/footer.php'; ?>