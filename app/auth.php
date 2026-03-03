<?php
// app/auth.php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function auth_login(string $email, string $password): bool {
  global $pdo;
  $stmt = $pdo->prepare("SELECT id, role, name, email, phone, password_hash, is_active FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $u = $stmt->fetch();
  if (!$u) return false;
  if ((int)$u['is_active'] !== 1) return false;
  if (!password_verify($password, $u['password_hash'])) return false;

  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  unset($u['password_hash'], $u['is_active']);
  $_SESSION['user'] = $u;
  return true;
}

function auth_logout(): void {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  $_SESSION = [];
  session_destroy();
}

function auth_register_customer(array $data): array {
  return auth_register_user('customer', $data);
}

function auth_register_provider(array $data): array {
  return auth_register_user('provider', $data);
}

function auth_register_user(string $role, array $data): array {
  global $pdo;

  $name = trim($data['name'] ?? '');
  $email = strtolower(trim($data['email'] ?? ''));
  $phone = trim($data['phone'] ?? '');
  $pass = $data['password'] ?? '';
  $pass2 = $data['password2'] ?? '';

  $errors = [];
  if ($name === '' || strlen($name) < 2) $errors[] = "Name is required.";
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email required.";
  if (strlen($pass) < 8) $errors[] = "Password must be at least 8 characters.";
  if ($pass !== $pass2) $errors[] = "Passwords do not match.";
  if (!in_array($role, ['customer','provider'], true)) $errors[] = "Invalid role.";

  if ($errors) return ['ok'=>false,'errors'=>$errors];

  $stmt = $pdo->prepare("SELECT id FROM users WHERE email=?");
  $stmt->execute([$email]);
  if ($stmt->fetch()) return ['ok'=>false,'errors'=>["Email already in use."]];

  $hash = password_hash($pass, PASSWORD_DEFAULT);
  $stmt = $pdo->prepare("INSERT INTO users (role, name, email, phone, password_hash) VALUES (?,?,?,?,?)");
  $stmt->execute([$role, $name, $email, $phone ?: null, $hash]);
  $uid = (int)$pdo->lastInsertId();

  if ($role === 'provider') {
    $business = trim($data['business_name'] ?? '');
    if ($business === '') $business = $name . "'s Services";
    $stmt = $pdo->prepare("INSERT INTO provider_profiles (user_id, business_name) VALUES (?,?)");
    $stmt->execute([$uid, $business]);
  }

  return ['ok'=>true,'user_id'=>$uid];
}