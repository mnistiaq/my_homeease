<?php
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_role('provider');

$u = current_user();
$booking_id = (int)($_GET['booking_id'] ?? 0);
if ($booking_id <= 0) { http_response_code(400); echo "Bad request"; exit; }

// ensure this booking belongs to this provider
$stmt = $pdo->prepare("SELECT id FROM bookings WHERE id=? AND provider_id=?");
$stmt->execute([$booking_id, (int)$u['id']]);
if (!$stmt->fetch()) { http_response_code(403); echo "Forbidden"; exit; }

// mark provider_confirmed
$stmt = $pdo->prepare("UPDATE payments SET provider_confirmed=1 WHERE booking_id=?");
$stmt->execute([$booking_id]);

// if both confirmed → paid
$stmt = $pdo->prepare("
  UPDATE payments
  SET status='paid'
  WHERE booking_id=? AND customer_confirmed=1 AND provider_confirmed=1
");
$stmt->execute([$booking_id]);

flash_set("Payment confirmed by provider.", "success");
redirect(base_url('provider/dashboard.php'));