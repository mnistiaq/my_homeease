<?php
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_role('admin');

$id = (int)($_GET['id'] ?? 0);
$to = (int)($_GET['to'] ?? 0);
$to = $to === 1 ? 1 : 0;

if ($to === 1) {
  // must have both documents
  $stmt = $pdo->prepare("SELECT nid_file, trade_license_file FROM provider_profiles WHERE user_id=?");
  $stmt->execute([$id]);
  $p = $stmt->fetch();

  if (!$p || empty($p['nid_file']) || empty($p['trade_license_file'])) {
    flash_set("Cannot verify: NID/Trade License not submitted.", "warning");
    redirect(base_url('admin/dashboard.php'));
  }

  $stmt = $pdo->prepare("UPDATE provider_profiles SET is_verified=1, verification_status='approved' WHERE user_id=?");
  $stmt->execute([$id]);

  flash_set("Provider verified.", "success");
  redirect(base_url('admin/dashboard.php'));
}

// unverify
$stmt = $pdo->prepare("UPDATE provider_profiles SET is_verified=0, verification_status='not_submitted' WHERE user_id=?");
$stmt->execute([$id]);

flash_set("Provider unverified.", "info");
redirect(base_url('admin/dashboard.php'));