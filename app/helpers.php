<?php
// app/helpers.php

function e(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
  header("Location: $url");
  exit;
}

function base_url(string $path = ''): string {
  $config = require __DIR__ . '/config.php';
  $base = rtrim($config['app']['base_url'], '/');
  $path = ltrim($path, '/');
  return $base . ($path ? '/' . $path : '');
}

function csrf_token(): string {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
  }
  return $_SESSION['csrf'];
}

function require_csrf(): void {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  $token = $_POST['csrf'] ?? '';
  if (!$token || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
    http_response_code(400);
    echo "Bad request (CSRF).";
    exit;
  }
}

function current_user(): ?array {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool {
  return current_user() !== null;
}

function require_login(): void {
  if (!is_logged_in()) redirect(base_url('login.php'));
}

function require_role(string $role): void {
  require_login();
  $u = current_user();
  if (($u['role'] ?? '') !== $role) {
    http_response_code(403);
    echo "Forbidden";
    exit;
  }
}

function flash_set(string $msg, string $type='info'): void {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  $_SESSION['flash'] = ['msg'=>$msg,'type'=>$type];
}

function flash_get(): ?array {
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  $f = $_SESSION['flash'] ?? null;
  unset($_SESSION['flash']);
  return $f;
}