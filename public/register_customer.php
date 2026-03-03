<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';

$title = "Sign up (Customer) - my_homeease";
require __DIR__ . '/../app/views/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_csrf();
  $res = auth_register_customer($_POST);
  if ($res['ok']) {
    flash_set("Account created. Please login.", "success");
    redirect(base_url('login.php'));
  } else {
    echo '<div class="alert alert-danger"><ul class="mb-0">';
    foreach ($res['errors'] as $er) echo '<li>'.e($er).'</li>';
    echo '</ul></div>';
  }
}
?>
<h1 class="h3 mb-3">Create customer account</h1>
<form method="post" class="card card-body" style="max-width:620px;">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <div class="row g-3">
    <div class="col-12 col-md-6">
      <label class="form-label">Name</label>
      <input name="name" class="form-control" required>
    </div>
    <div class="col-12 col-md-6">
      <label class="form-label">Phone</label>
      <input name="phone" class="form-control" placeholder="01XXXXXXXXX">
    </div>
    <div class="col-12">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <div class="col-12 col-md-6">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <div class="col-12 col-md-6">
      <label class="form-label">Confirm Password</label>
      <input type="password" name="password2" class="form-control" required>
    </div>
  </div>
  <button class="btn btn-dark mt-3" type="submit">Create account</button>
</form>
<?php require __DIR__ . '/../app/views/footer.php'; ?>