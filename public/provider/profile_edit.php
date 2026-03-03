<?php
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_role('provider');

$u = current_user();

$stmt = $pdo->prepare("SELECT * FROM provider_profiles WHERE user_id=?");
$stmt->execute([(int)$u['id']]);
$p = $stmt->fetch();

$title = "Edit provider profile - my_homeease";
require __DIR__ . '/../../app/views/header.php';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  require_csrf();

  $business_name = trim($_POST['business_name'] ?? '');
  $bio = trim($_POST['bio'] ?? '');
  $address = trim($_POST['address'] ?? '');
  $city = trim($_POST['city'] ?? '');

  $bkash = trim($_POST['bkash_number'] ?? '');
  $nagad = trim($_POST['nagad_number'] ?? '');

  if ($business_name === '') {
    echo '<div class="alert alert-danger">Business name is required.</div>';
  } else {
    $stmt = $pdo->prepare("
      UPDATE provider_profiles
      SET business_name=?, bio=?, address=?, city=?, bkash_number=?, nagad_number=?
      WHERE user_id=?
    ");
    $stmt->execute([
      $business_name,
      $bio ?: null,
      $address ?: null,
      $city ?: null,
      $bkash ?: null,
      $nagad ?: null,
      (int)$u['id']
    ]);

    flash_set("Profile updated.", "success");
    redirect(base_url('provider/dashboard.php'));
  }
}
?>
<h1 class="h4 mb-3">Edit profile</h1>

<form method="post" class="card card-body" style="max-width:760px;">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

  <div class="mb-3">
    <label class="form-label">Business name</label>
    <input class="form-control" name="business_name" value="<?= e($p['business_name'] ?? '') ?>" required>
  </div>

  <div class="row g-3">
    <div class="col-12 col-md-6">
      <label class="form-label">City</label>
      <input class="form-control" name="city" value="<?= e($p['city'] ?? '') ?>">
    </div>
    <div class="col-12 col-md-6">
      <label class="form-label">Address</label>
      <input class="form-control" name="address" value="<?= e($p['address'] ?? '') ?>">
    </div>
  </div>

  <div class="mb-3 mt-3">
    <label class="form-label">Bio</label>
    <textarea class="form-control" name="bio" rows="4"><?= e($p['bio'] ?? '') ?></textarea>
  </div>

  <hr class="my-4">

  <h2 class="h6">Payment receiving numbers</h2>
  <div class="row g-3">
    <div class="col-12 col-md-6">
      <label class="form-label">bKash number</label>
      <input class="form-control" name="bkash_number" value="<?= e($p['bkash_number'] ?? '') ?>" placeholder="01XXXXXXXXX">
    </div>
    <div class="col-12 col-md-6">
      <label class="form-label">Nagad number</label>
      <input class="form-control" name="nagad_number" value="<?= e($p['nagad_number'] ?? '') ?>" placeholder="01XXXXXXXXX">
    </div>
  </div>

  <button class="btn btn-dark mt-4" type="submit">Save</button>
</form>

<?php require __DIR__ . '/../../app/views/footer.php'; ?>