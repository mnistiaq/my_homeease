<?php
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_role('admin');

$booking_id = (int)($_GET['booking_id'] ?? 0);
$to = $_GET['to'] ?? '';

$allowed = ['paid','failed','refunded','pending','unpaid'];
if ($booking_id <= 0 || !in_array($to, $allowed, true)) {
  http_response_code(400);
  echo "Bad request";
  exit;
}

$stmt = $pdo->prepare("UPDATE payments SET status=? WHERE booking_id=?");
$stmt->execute([$to, $booking_id]);

flash_set("Payment updated to: {$to}", "success");
redirect(base_url('admin/dashboard.php'));