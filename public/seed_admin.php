<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

$title = "Seed admin - my_homeease";
require __DIR__ . '/../app/views/header.php';

$exists = $pdo->query("SELECT id, email FROM users WHERE role='admin' LIMIT 1")->fetch();
if ($exists) {
  echo '<div class="alert alert-warning">Admin already exists: '.e($exists['email']).'. This page will not create another admin.</div>';
  echo '<a class="btn btn-dark" href="'.e(base_url('login.php')).'">Go to login</a>';
  require __DIR__ . '/../app/views/footer.php';
  exit;
}

$email = "admin@myhomeease.local";
$pass = "Admin@12345";
$hash = password_hash($pass, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (role, name, email, phone, password_hash) VALUES ('admin','HomeEase Admin',?,?,?)");
$stmt->execute([$email, null, $hash]);

echo '<div class="alert alert-success">Admin created.</div>';
echo '<ul>';
echo '<li>Email: <b>'.e($email).'</b></li>';
echo '<li>Password: <b>'.e($pass).'</b></li>';
echo '</ul>';
echo '<a class="btn btn-dark" href="'.e(base_url('login.php')).'">Login</a>';

require __DIR__ . '/../app/views/footer.php';