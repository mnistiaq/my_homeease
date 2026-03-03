<?php
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_role('provider');

$u = current_user();

$id = (int)($_GET['id'] ?? 0);
$to = $_GET['to'] ?? '';

$allowed = ['accepted','declined','in_progress','completed'];
if ($id<=0 || !in_array($to, $allowed, true)) {
  http_response_code(400);
  echo "Bad request";
  exit;
}

// Only update own bookings
$stmt = $pdo->prepare("SELECT status FROM bookings WHERE id=? AND provider_id=?");
$stmt->execute([$id, (int)$u['id']]);
$b = $stmt->fetch();
if (!$b) { http_response_code(404); echo "Not found"; exit; }

// Simple state rules
$cur = $b['status'];
$ok = true;
if ($to==='accepted' && $cur!=='requested') $ok=false;
if ($to==='declined' && $cur!=='requested') $ok=false;
if ($to==='in_progress' && !in_array($cur, ['accepted','in_progress'], true)) $ok=false;
if ($to==='completed' && $cur!=='in_progress') $ok=false;

if (!$ok) {
  flash_set("Invalid status change.", "warning");
  redirect(base_url('provider/dashboard.php'));
}

$stmt = $pdo->prepare("UPDATE bookings SET status=? WHERE id=? AND provider_id=?");
$stmt->execute([$to, $id, (int)$u['id']]);

flash_set("Booking updated to: $to", "success");
redirect(base_url('provider/dashboard.php'));