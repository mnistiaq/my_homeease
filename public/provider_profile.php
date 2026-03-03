<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

$provider_id = (int)($_GET['id'] ?? 0);
if ($provider_id <= 0) { http_response_code(404); echo "Not found"; exit; }

$stmt = $pdo->prepare("
SELECT u.id, u.name, u.email, u.phone, p.business_name, p.bio, p.address, p.city, p.is_verified, p.cover_photo
FROM users u
JOIN provider_profiles p ON p.user_id = u.id
WHERE u.id=? AND u.role='provider' AND u.is_active=1
");
$stmt->execute([$provider_id]);
$provider = $stmt->fetch();
if (!$provider) { http_response_code(404); echo "Not found"; exit; }

$img = $provider['cover_photo'] ? base_url('../storage/uploads/' . $provider['cover_photo']) : 'https://placehold.co/1200x600?text=HomeEase';

$stmt = $pdo->prepare("SELECT * FROM services WHERE provider_id=? AND is_active=1 ORDER BY created_at DESC");
$stmt->execute([$provider_id]);
$services = $stmt->fetchAll();

$stmt = $pdo->prepare("
SELECT r.rating, r.comment, r.created_at, u.name AS customer_name
FROM reviews r
JOIN users u ON u.id = r.customer_id
WHERE r.provider_id=?
ORDER BY r.created_at DESC
LIMIT 20
");
$stmt->execute([$provider_id]);
$reviews = $stmt->fetchAll();

$title = e($provider['business_name']) . " - Provider";
require __DIR__ . '/../app/views/header.php';
?>
<div class="card mb-3">
  <img src="<?= e($img) ?>" alt="Cover" class="card-img-top" style="object-fit:cover;height:240px;">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-between">
      <div>
        <h1 class="h4 mb-0"><?= e($provider['business_name']) ?></h1>
        <div class="text-muted small">Owner: <?= e($provider['name']) ?> • <?= e($provider['city'] ?? '—') ?></div>
      </div>
      <div>
        <?php if ((int)$provider['is_verified']===1): ?>
          <span class="badge text-bg-success">Verified</span>
        <?php else: ?>
          <span class="badge text-bg-secondary">Not verified</span>
        <?php endif; ?>
      </div>
    </div>
    <?php if ($provider['bio']): ?>
      <p class="mt-3 mb-0"><?= e($provider['bio']) ?></p>
    <?php endif; ?>
  </div>
</div>

<h2 class="h5">Services</h2>
<div class="row g-3 mb-4">
  <?php if (!$services): ?>
    <div class="col-12"><div class="alert alert-secondary">No services listed yet.</div></div>
  <?php endif; ?>
  <?php foreach ($services as $s): ?>
    <div class="col-12 col-md-6">
      <div class="card h-100">
        <div class="card-body">
          <div class="text-muted small"><?= e($s['category']) ?></div>
          <div class="fw-semibold"><?= e($s['title']) ?></div>
          <div class="mt-2">৳<?= e(number_format((float)$s['base_price'],2)) ?></div>
          <a class="btn btn-sm btn-dark mt-3" href="<?= e(base_url('book.php?service_id='.(int)$s['id'])) ?>">Book</a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<h2 class="h5">Recent reviews</h2>
<?php if (!$reviews): ?>
  <div class="alert alert-secondary">No reviews yet.</div>
<?php else: ?>
  <div class="list-group">
    <?php foreach ($reviews as $r): ?>
      <div class="list-group-item">
        <div class="d-flex justify-content-between">
          <div class="fw-semibold"><?= e($r['customer_name']) ?></div>
          <div>⭐ <?= (int)$r['rating'] ?></div>
        </div>
        <?php if ($r['comment']): ?><div class="text-muted"><?= e($r['comment']) ?></div><?php endif; ?>
        <div class="small text-muted"><?= e($r['created_at']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/views/footer.php'; ?>