<?php
// public/logout.php

ob_start(); // prevents "headers already sent" from accidental output/BOM

require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/helpers.php';

auth_logout();

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}
flash_set("Logged out.", "info");

// Use an absolute path based on your base_url helper
redirect(base_url('index.php'));