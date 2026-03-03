<?php
// app/config.php
// ─── InfinityFree hosting configuration ───
// Switch between 'local' and 'production' by changing the ENVIRONMENT below.

$environment = 'production'; // Change to 'local' for XAMPP development

if ($environment === 'production') {
  // ── InfinityFree (Production) ──
  return [
    'db' => [
      'host'    => 'sql207.infinityfreeapp.com',
      'name'    => 'if0_41296449_my_homeease',
      'user'    => 'if0_41296449',
      'pass'    => 'araf1164araf',
      'charset' => 'utf8mb4',
    ],
    'app' => [
      'base_url' => '/public',
    ],
  ];
} else {
  // ── Local XAMPP (Development) ──
  return [
    'db' => [
      'host'    => '127.0.0.1',
      'name'    => 'my_homeease',
      'user'    => 'root',
      'pass'    => '',
      'charset' => 'utf8mb4',
    ],
    'app' => [
      'base_url' => '/my_homeease/public',
    ],
  ];
}