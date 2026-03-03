<?php
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_role('provider');

$u = current_user();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM services WHERE id=? AND provider_id=?");
$stmt->execute([$id, (int)$u['id']]);
$s = $stmt->fetch();
if (!$s) { http_response_code(404); echo "Not found"; exit; }

$title = "Edit service - my_homeease";
require __DIR__ . '/../../app/views/header.php';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  require_csrf();

  $category = trim($_POST['category'] ?? '');
  $titleS = trim($_POST['title'] ?? '');
  $desc = trim($_POST['description'] ?? '');
  $price = (float)($_POST['base_price'] ?? 0);
  $is_active = isset($_POST['is_active']) ? 1 : 0;

  if ($category==='' || $titleS==='') {
    echo '<div class="alert alert-danger">Category and title are required.</div>';
  } else {
    $stmt = $pdo->prepare("UPDATE services SET category=?, title=?, description=?, base_price=?, is_active=? WHERE id=? AND provider_id=?");
    $stmt->execute([$category, $titleS, $desc ?: null, $price, $is_active, $id, (int)$u['id']]);

    flash_set("Service updated.", "success");
    redirect(base_url('provider/dashboard.php'));
  }
}
?>
<h1 class="h4 mb-3">Edit service</h1>

<form method="post" class="card card-body" style="max-width:760px;">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

  <div class="row g-3">
    <div class="col-12 col-md-6">
      <label class="form-label">Category</label>
      <input class="form-control" name="category" value="<?= e($s['category']) ?>" required>
    </div>
    <div class="col-12 col-md-6">
      <label class="form-label">Base price (BDT)</label>
      <input class="form-control" name="base_price" type="number" step="0.01" value="<?= e((string)$s['base_price']) ?>">
    </div>
    <div class="col-12">
      <label class="form-label">Title</label>
      <input class="form-control" name="title" value="<?= e($s['title']) ?>" required>
    </div>
    <div class="col-12">
      <label class="form-label">Description</label>
      <textarea class="form-control" name="description" rows="4"><?= e($s['description'] ?? '') ?></textarea>
    </div>
    <div class="col-12">
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= (int)$s['is_active']===1?'checked':'' ?>>
        <label class="form-check-label" for="is_active">Active</label>
      </div>
    </div>
  </div>

  <button class="btn btn-dark mt-3" type="submit">Save</button>
</form>

<?php require __DIR__ . '/../../app/views/footer.php'; ?>