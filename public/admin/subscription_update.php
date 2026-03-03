<?php
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_role('admin');

$id = (int)($_GET['id'] ?? 0);
$to = $_GET['to'] ?? '';
if ($id<=0 || !in_array($to, ['active','rejected'], true)) {
  http_response_code(400); echo "Bad request"; exit;
}

$stmt = $pdo->prepare("
  SELECT ps.*, sp.duration_days
  FROM provider_subscriptions ps
  JOIN subscription_plans sp ON sp.id = ps.plan_id
  WHERE ps.id=?
");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { http_response_code(404); echo "Not found"; exit; }

if ($to === 'rejected') {
  $stmt = $pdo->prepare("UPDATE provider_subscriptions SET status='rejected' WHERE id=?");
  $stmt->execute([$id]);
  flash_set("Subscription rejected.", "warning");
  redirect(base_url('admin/dashboard.php'));
}

// Approve → set start/end
$start = date('Y-m-d H:i:s');
$end   = date('Y-m-d H:i:s', time() + ((int)$row['duration_days'] * 86400));

$stmt = $pdo->prepare("
  UPDATE provider_subscriptions
  SET status='active', start_date=?, end_date=?
  WHERE id=?
");
$stmt->execute([$start, $end, $id]);

flash_set("Subscription activated.", "success");
redirect(base_url('admin/dashboard.php'));