<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';
$title = "Services - my_homeease";
require __DIR__ . '/../app/views/header.php';

$category = trim($_GET['category'] ?? '');
$q = trim($_GET['q'] ?? '');

$params = [];
$where = "s.is_active=1 AND u.is_active=1";

if ($category !== '') {
  $where .= " AND s.category = ?";
  $params[] = $category;
}
if ($q !== '') {
  $where .= " AND (s.title LIKE ? OR s.description LIKE ? OR p.business_name LIKE ?)";
  $like = '%' . $q . '%';
  $params[] = $like; $params[] = $like; $params[] = $like;
}

$sql = "
SELECT s.*, p.business_name, p.city, p.is_verified,
       COALESCE(AVG(r.rating),0) AS avg_rating,
       COUNT(r.id) AS review_count
FROM services s
JOIN users u ON u.id = s.provider_id
JOIN provider_profiles p ON p.user_id = s.provider_id
LEFT JOIN reviews r ON r.provider_id = s.provider_id
WHERE $where
GROUP BY s.id
ORDER BY p.is_verified DESC, avg_rating DESC, review_count DESC, s.created_at DESC
LIMIT 100";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();
?>
<h1 class="h3 mb-3">Services</h1>

<form class="row g-2 mb-3" method="get">
  <div class="col-12 col-md-4">
    <input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Search service or provider...">
  </div>
  <div class="col-12 col-md-3">
    <input class="form-control" name="category" value="<?= e($category) ?>" placeholder="Category (exact)">
  </div>
  <div class="col-12 col-md-2">
    <button class="btn btn-dark w-100" type="submit">Search</button>
  </div>
  <div class="col-12 col-md-3">
    <a class="btn btn-outline-secondary w-100" href="<?= e(base_url('services.php')) ?>">Reset</a>
  </div>
</form>

<div class="row g-3">
<?php if (!$services): ?>
  <div class="col-12"><div class="alert alert-secondary">No services found.</div></div>
<?php endif; ?>

<?php foreach ($services as $s): ?>
  <div class="col-12 col-md-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="text-muted small"><?= e($s['category']) ?></div>
            <div class="h5 mb-1"><?= e($s['title']) ?></div>
            <div class="text-muted small">by <a href="<?= e(base_url('provider_profile.php?id='.(int)$s['provider_id'])) ?>"><?= e($s['business_name']) ?></a></div>
          </div>
          <div class="text-end">
            <?php if ((int)$s['is_verified']===1): ?><span class="badge text-bg-success">Verified</span><?php endif; ?>
            <div class="mt-2 fw-semibold">৳<?= e(number_format((float)$s['base_price'],2)) ?></div>
          </div>
        </div>
        <p class="mt-2 mb-2"><?= e(mb_strimwidth((string)$s['description'], 0, 160, '...')) ?></p>
        <div class="small">⭐ <?= number_format((float)$s['avg_rating'],1) ?> (<?= (int)$s['review_count'] ?>)</div>
        <a class="btn btn-sm btn-dark mt-3" href="<?= e(base_url('book.php?service_id='.(int)$s['id'])) ?>">Book</a>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<?php require __DIR__ . '/../app/views/footer.php'; ?>