<?php
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_role('admin');

$id = (int)($_GET['id'] ?? 0);
$to = (int)($_GET['to'] ?? 1);
$to = $to === 1 ? 1 : 0;

// prevent disabling the only admin
$stmt = $pdo->prepare("SELECT role FROM users WHERE id=?");
$stmt->execute([$id]);
$u = $stmt->fetch();
if ($u && $u['role']==='admin' && $to===0) {
  flash_set("You cannot disable the admin account.", "warning");
  redirect(base_url('admin/dashboard.php'));
}

$stmt = $pdo->prepare("UPDATE users SET is_active=? WHERE id=?");
$stmt->execute([$to, $id]);

flash_set("User active status updated.", "success");
redirect(base_url('admin/dashboard.php'));