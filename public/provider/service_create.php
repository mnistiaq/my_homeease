<?php
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_role('provider');

$u = current_user();

$title = "Add service - my_homeease";
require __DIR__ . '/../../app/views/header.php';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  require_csrf();

  $category = trim($_POST['category'] ?? '');
  $titleS = trim($_POST['title'] ?? '');
  $desc = trim($_POST['description'] ?? '');
  $price = (float)($_POST['base_price'] ?? 0);

  if ($category==='' || $titleS==='') {
    echo '<div class="alert alert-danger">Category and title are required.</div>';
  } else {
    $stmt = $pdo->prepare("INSERT INTO services (provider_id, category, title, description, base_price) VALUES (?,?,?,?,?)");
    $stmt->execute([(int)$u['id'], $category, $titleS, $desc ?: null, $price]);

    flash_set("Service added.", "success");
    redirect(base_url('provider/dashboard.php'));
  }
}
?>
<h1 class="h4 mb-3">Add service</h1>

<form method="post" class="card card-body" style="max-width:760px;">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

  <div class="row g-3">
    <div class="col-12 col-md-6">
      <label class="form-label">Category</label>
      <input class="form-control" name="category" placeholder="Plumbing" required>
    </div>
    <div class="col-12 col-md-6">
      <label class="form-label">Base price (BDT)</label>
      <input class="form-control" name="base_price" type="number" step="0.01" value="0">
    </div>
    <div class="col-12">
      <label class="form-label">Title</label>
      <input class="form-control" name="title" placeholder="Pipe leak fixing" required>
    </div>
    <div class="col-12">
      <label class="form-label">Description</label>
      <textarea class="form-control" name="description" rows="4"></textarea>
    </div>
  </div>

  <button class="btn btn-dark mt-3" type="submit">Save</button>
</form>

<?php require __DIR__ . '/../../app/views/footer.php'; ?>