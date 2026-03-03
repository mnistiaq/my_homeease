<?php
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_role('admin');

$title = "Subscription plans - my_homeease";
require __DIR__ . '/../../app/views/header.php';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  require_csrf();

  $name = trim($_POST['name'] ?? '');
  $price = (float)($_POST['price'] ?? 0);
  $days  = (int)($_POST['duration_days'] ?? 30);
  $boost = (int)($_POST['boost_level'] ?? 0);

  if ($name !== '') {
    $stmt = $pdo->prepare("INSERT INTO subscription_plans (name, price, duration_days, boost_level, is_active) VALUES (?,?,?,?,1)");
    $stmt->execute([$name, $price, $days, $boost]);
    flash_set("Plan added.", "success");
    redirect(base_url('admin/plans.php'));
  } else {
    flash_set("Plan name required.", "danger");
  }
}

$plans = $pdo->query("SELECT * FROM subscription_plans ORDER BY boost_level DESC, price ASC")->fetchAll();
?>

<h1 class="h4 mb-3">Subscription plans</h1>

<form method="post" class="card card-body mb-4" style="max-width:900px;">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <div class="row g-3">
    <div class="col-12 col-md-3">
      <label class="form-label">Name</label>
      <input class="form-control" name="name" placeholder="Pro" required>
    </div>
    <div class="col-12 col-md-3">
      <label class="form-label">Price (BDT)</label>
      <input class="form-control" name="price" type="number" step="0.01" value="0">
    </div>
    <div class="col-12 col-md-3">
      <label class="form-label">Duration days</label>
      <input class="form-control" name="duration_days" type="number" value="30">
    </div>
    <div class="col-12 col-md-3">
      <label class="form-label">Boost level</label>
      <input class="form-control" name="boost_level" type="number" value="1">
    </div>
  </div>
  <button class="btn btn-dark mt-3">Add plan</button>
</form>

<div class="table-responsive">
  <table class="table table-sm align-middle">
    <thead><tr>
      <th>Name</th><th class="text-end">Price</th><th class="text-end">Days</th><th class="text-end">Boost</th><th>Active</th>
    </tr></thead>
    <tbody>
      <?php foreach ($plans as $p): ?>
        <tr>
          <td><?= e($p['name']) ?></td>
          <td class="text-end">৳<?= e(number_format((float)$p['price'],2)) ?></td>
          <td class="text-end"><?= (int)$p['duration_days'] ?></td>
          <td class="text-end"><?= (int)$p['boost_level'] ?></td>
          <td><?= (int)$p['is_active']===1 ? '<span class="badge text-bg-success">Yes</span>' : '<span class="badge text-bg-secondary">No</span>' ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/../../app/views/footer.php'; ?>