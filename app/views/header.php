<?php
require_once __DIR__ . '/../helpers.php';
$u = current_user();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'my_homeease') ?></title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    :root { --brand: #111827; }
    .navbar-brand { letter-spacing: .3px; }
    .brand-dot { width:10px; height:10px; border-radius:999px; background: #22c55e; display:inline-block; margin-left:8px; }
    .app-shell { min-height: calc(100vh - 72px); }
    .nav-pill { border-radius: 999px; padding: .4rem .75rem; }
    .glass {
      background: rgba(255,255,255,.75);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(0,0,0,.06);
    }
  </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark" style="background: var(--brand);">
  <div class="container">
    <a class="navbar-brand fw-semibold d-flex align-items-center" href="<?= e(base_url('index.php')) ?>">
      HomeEase <span class="brand-dot"></span>
      <span class="ms-2 small fw-normal text-white-50">my_homeease</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto gap-lg-1 mt-3 mt-lg-0">
        <li class="nav-item">
          <a class="nav-link nav-pill <?= basename($_SERVER['PHP_SELF'])==='index.php'?'active':'' ?>"
             href="<?= e(base_url('index.php')) ?>">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-pill <?= basename($_SERVER['PHP_SELF'])==='services.php'?'active':'' ?>"
             href="<?= e(base_url('services.php')) ?>">Services</a>
        </li>

        <?php if ($u && $u['role']==='customer'): ?>
          <li class="nav-item">
            <a class="nav-link nav-pill" href="<?= e(base_url('customer/bookings.php')) ?>">My Bookings</a>
          </li>
        <?php endif; ?>

        <?php if ($u && $u['role']==='provider'): ?>
          <li class="nav-item">
            <a class="nav-link nav-pill" href="<?= e(base_url('provider/dashboard.php')) ?>">Provider Dashboard</a>
          </li>
        <?php endif; ?>

        <?php if ($u && $u['role']==='admin'): ?>
          <li class="nav-item">
            <a class="nav-link nav-pill" href="<?= e(base_url('admin/dashboard.php')) ?>">Admin Panel</a>
          </li>
        <?php endif; ?>
      </ul>

      <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
        <?php if (!$u): ?>
          <a class="btn btn-outline-light btn-sm" href="<?= e(base_url('login.php')) ?>">Login</a>
          <a class="btn btn-success btn-sm" href="<?= e(base_url('register_customer.php')) ?>">Sign up</a>
        <?php else: ?>
          <div class="dropdown">
            <button class="btn btn-outline-light btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
              <?= e($u['name']) ?> <span class="text-white-50">• <?= e($u['role']) ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <?php if ($u['role']==='customer'): ?>
                <li><a class="dropdown-item" href="<?= e(base_url('customer/bookings.php')) ?>">My Bookings</a></li>
              <?php endif; ?>
              <?php if ($u['role']==='provider'): ?>
                <li><a class="dropdown-item" href="<?= e(base_url('provider/dashboard.php')) ?>">Provider Dashboard</a></li>
                <li><a class="dropdown-item" href="<?= e(base_url('provider/profile_edit.php')) ?>">Edit Profile</a></li>
              <?php endif; ?>
              <?php if ($u['role']==='admin'): ?>
                <li><a class="dropdown-item" href="<?= e(base_url('admin/dashboard.php')) ?>">Admin Panel</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?= e(base_url('logout.php')) ?>">Logout</a></li>
            </ul>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<!-- Top banner / breadcrumbs area -->
<div class="container mt-3">
  <?php $flash = flash_get(); if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?> glass"><?= e($flash['msg']) ?></div>
  <?php endif; ?>
</div>

<main class="container app-shell py-3">