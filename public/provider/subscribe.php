<?php
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_role('provider');

$u = current_user();
$plan_id = (int)($_GET['plan_id'] ?? 0);
if ($plan_id<=0) { http_response_code(400); echo "Bad request"; exit; }

$stmt = $pdo->prepare("SELECT * FROM subscription_plans WHERE id=? AND is_active=1");
$stmt->execute([$plan_id]);
$plan = $stmt->fetch();
if (!$plan) { http_response_code(404); echo "Plan not found"; exit; }

// Create pending subscription
$stmt = $pdo->prepare("
  INSERT INTO provider_subscriptions (provider_id, plan_id, status)
  VALUES (?,?, 'pending')
");
$stmt->execute([(int)$u['id'], $plan_id]);

flash_set("Subscription request sent (pending admin approval).", "success");
redirect(base_url('provider/subscriptions.php'));