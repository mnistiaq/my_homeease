<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';

$title = "Login - my_homeease";
require __DIR__ . '/../app/views/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_csrf();
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  if (auth_login($email, $password)) {
    $u = current_user();
    flash_set("Welcome back, {$u['name']}!", "success");
    if ($u['role']==='admin') redirect(base_url('admin/dashboard.php'));
    if ($u['role']==='provider') redirect(base_url('provider/dashboard.php'));
    redirect(base_url('index.php'));
  } else {
    echo '<div class="alert alert-danger">Invalid login.</div>';
  }
}
?>
<h1 class="h3 mb-3">Login</h1>
<form method="post" class="card card-body" style="max-width:520px;">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Password</label>
    <input type="password" name="password" class="form-control" required>
  </div>
  <button class="btn btn-dark" type="submit">Login</button>
  <div class="mt-3 small text-muted">
    New here? <a href="<?= e(base_url('register_customer.php')) ?>">Create customer account</a> or
    <a href="<?= e(base_url('register_provider.php')) ?>">Join as provider</a>.
  </div>
</form>
<?php require __DIR__ . '/../app/views/footer.php'; ?>