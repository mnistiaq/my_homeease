<?php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

$title = "Home - HomeEase";
require __DIR__ . '/../app/views/header.php';

// Featured providers (verified first, then rating)
$sql = "
SELECT u.id, p.business_name, p.city, p.cover_photo, p.is_verified,
       COALESCE(AVG(r.rating), 0) AS avg_rating,
       COUNT(r.id) AS review_count
FROM users u
JOIN provider_profiles p ON p.user_id = u.id
LEFT JOIN reviews r ON r.provider_id = u.id
WHERE u.role='provider' AND u.is_active=1
GROUP BY u.id
ORDER BY p.is_verified DESC, avg_rating DESC, review_count DESC
LIMIT 8";
$providers = $pdo->query($sql)->fetchAll();

// Stats
$stats = [
  'providers' => (int)$pdo->query("SELECT COUNT(*) c FROM users WHERE role='provider' AND is_active=1")->fetch()['c'],
  'services'  => (int)$pdo->query("SELECT COUNT(*) c FROM services WHERE is_active=1")->fetch()['c'],
  'bookings'  => (int)$pdo->query("SELECT COUNT(*) c FROM bookings")->fetch()['c'],
];

$cats = [
  ['Plumbing','Fix leaks, pipes, fittings'],
  ['Electrical','Wiring, sockets, fan, light'],
  ['Cleaning','Home, kitchen, office cleaning'],
  ['Painting','Wall & interior painting'],
  ['Appliance Repair','Fridge, TV, washing machine'],
  ['AC Servicing','AC clean, gas, maintenance'],
  ['Carpentry','Furniture repair & fitting'],
  ['Pest Control','Cockroach, termite, etc.'],
];

$q = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');
?>

<style>
  /* Scoped styles for index only (no header/footer changes needed) */
  .hx-hero{
    border-radius: 26px;
    border: 1px solid rgba(15,23,42,.08);
    background:
      radial-gradient(900px 380px at 10% 0%, rgba(34,197,94,.35), transparent 60%),
      radial-gradient(900px 420px at 90% 10%, rgba(59,130,246,.26), transparent 55%),
      linear-gradient(180deg, #ffffff, #f3f6ff);
    box-shadow: 0 30px 80px rgba(15,23,42,.10);
    overflow: hidden;
  }
  .hx-pill{
    display:inline-flex; align-items:center; gap:.45rem;
    border-radius: 999px;
    padding:.42rem .7rem;
    font-size:.88rem;
    border:1px solid rgba(15,23,42,.10);
    background: rgba(255,255,255,.85);
    box-shadow: 0 10px 24px rgba(15,23,42,.06);
  }
  .hx-card{
    border: 1px solid rgba(15,23,42,.08);
    border-radius: 20px;
    background: #fff;
    box-shadow: 0 18px 55px rgba(15,23,42,.08);
    transition: .18s ease;
  }
  .hx-card:hover{
    transform: translateY(-2px);
    box-shadow: 0 24px 70px rgba(15,23,42,.12);
  }
  .hx-input{
    border-radius: 16px !important;
    border: 1px solid rgba(15,23,42,.12) !important;
    padding: .95rem 1rem !important;
    box-shadow: 0 12px 22px rgba(15,23,42,.05);
  }
  .hx-input:focus{
    border-color: #22c55e !important;
    box-shadow: 0 0 0 .25rem rgba(34,197,94,.22) !important;
  }
  .hx-btn{
    border-radius: 14px !important;
    padding: .85rem 1.05rem !important;
    font-weight: 700 !important;
  }
  .hx-btn-brand{
    background: linear-gradient(135deg, #22c55e, #16a34a) !important;
    border: none !important;
    box-shadow: 0 12px 30px rgba(34,197,94,.25);
    color: #fff !important;
  }
  .hx-btn-dark{
    background: #0f172a !important;
    border-color: #0f172a !important;
    color: #fff !important;
  }
  .hx-title{ font-weight: 850; letter-spacing: -.03em; }
  .hx-sub{ color:#6b7280; }
  .hx-provider-img{ height: 175px; object-fit: cover; }
  .hx-tag{ color:#16a34a; font-weight: 700; }
</style>

<!-- HERO -->
<div class="hx-hero p-4 p-md-5 mb-4">
  <div class="row g-4 align-items-center">
    <div class="col-12 col-lg-7">
      <div class="d-flex flex-wrap gap-2 mb-3">
        <span class="hx-pill">✅ Verified providers</span>
        <span class="hx-pill">⚡ Fast booking</span>
        <span class="hx-pill">📍 Local services</span>
        <span class="hx-pill">⭐ Reviews</span>
      </div>

      <h1 class="display-5 hx-title mb-2">
        Reliable <span class="hx-tag">home services</span>, booked in minutes.
      </h1>
      <p class="lead hx-sub mb-4">
        Compare providers, pick a time, track job progress, and rate your experience — all from one platform.
      </p>

      <form class="row g-2" action="<?= e(base_url('services.php')) ?>" method="get">
        <div class="col-12 col-md-6">
          <input class="form-control hx-input" name="q" value="<?= e($q) ?>" placeholder="Search: plumber, AC, cleaning...">
        </div>
        <div class="col-12 col-md-4">
          <input class="form-control hx-input" name="category" value="<?= e($category) ?>" placeholder="Category (optional)">
        </div>
        <div class="col-12 col-md-2 d-grid">
          <button class="btn hx-btn hx-btn-dark" type="submit">Search</button>
        </div>
      </form>

      <div class="d-flex flex-wrap gap-2 mt-3">
        <a class="btn hx-btn btn-outline-dark" href="<?= e(base_url('services.php')) ?>">Browse all</a>

        <?php if (!is_logged_in()): ?>
          <a class="btn hx-btn hx-btn-brand" href="<?= e(base_url('register_provider.php')) ?>">Join as provider</a>
        <?php else: ?>
          <?php $u = current_user(); ?>
          <?php if ($u['role']==='provider'): ?>
            <a class="btn hx-btn hx-btn-brand" href="<?= e(base_url('provider/dashboard.php')) ?>">Provider dashboard</a>
          <?php elseif ($u['role']==='customer'): ?>
            <a class="btn hx-btn hx-btn-brand" href="<?= e(base_url('customer/bookings.php')) ?>">My bookings</a>
          <?php elseif ($u['role']==='admin'): ?>
            <a class="btn hx-btn hx-btn-brand" href="<?= e(base_url('admin/dashboard.php')) ?>">Admin panel</a>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Stats card -->
    <div class="col-12 col-lg-5">
      <div class="hx-card p-4">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="fw-semibold">Marketplace snapshot</div>
            <div class="hx-sub small">Live numbers from your database</div>
          </div>
          <span class="badge text-bg-success">Live</span>
        </div>

        <div class="row g-3 mt-2">
          <div class="col-4">
            <div class="fw-bold fs-3"><?= (int)$stats['providers'] ?></div>
            <div class="hx-sub small">Providers</div>
          </div>
          <div class="col-4">
            <div class="fw-bold fs-3"><?= (int)$stats['services'] ?></div>
            <div class="hx-sub small">Services</div>
          </div>
          <div class="col-4">
            <div class="fw-bold fs-3"><?= (int)$stats['bookings'] ?></div>
            <div class="hx-sub small">Bookings</div>
          </div>
        </div>

        <hr class="my-4">

        <div class="row g-3">
          <div class="col-12">
            <div class="fw-semibold">For customers</div>
            <div class="hx-sub small">Book confidently with ratings, verification, and status tracking.</div>
          </div>
          <div class="col-12">
            <div class="fw-semibold">For providers</div>
            <div class="hx-sub small">Upload a business photo, list services, get bookings, build reputation.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- CATEGORIES -->
<div class="d-flex justify-content-between align-items-end mb-2">
  <div>
    <h2 class="h4 hx-title mb-0">Popular categories</h2>
    <div class="hx-sub">Pick a category and book instantly</div>
  </div>
  <a class="btn btn-sm btn-outline-secondary" href="<?= e(base_url('services.php')) ?>">View all</a>
</div>

<div class="row g-3 mb-5">
  <?php foreach ($cats as $c): ?>
    <div class="col-12 col-md-6 col-lg-3">
      <a class="text-decoration-none" href="<?= e(base_url('services.php?category=' . urlencode($c[0]))) ?>">
        <div class="hx-card p-3 h-100">
          <div class="fw-semibold"><?= e($c[0]) ?></div>
          <div class="hx-sub small"><?= e($c[1]) ?></div>
          <div class="mt-3 small hx-tag">Explore →</div>
        </div>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<!-- FEATURED PROVIDERS -->
<div class="mb-2">
  <h2 class="h4 hx-title mb-0">Featured providers</h2>
  <div class="hx-sub">Verified and top-rated professionals</div>
</div>

<div class="row g-3">
  <?php if (!$providers): ?>
    <div class="col-12"><div class="alert alert-secondary">No providers yet. Register a provider and upload a business photo to appear here.</div></div>
  <?php endif; ?>

  <?php foreach ($providers as $p):
    $img = $p['cover_photo'] ? base_url('../storage/uploads/' . $p['cover_photo']) : 'https://placehold.co/900x540?text=HomeEase';
  ?>
    <div class="col-12 col-md-6 col-lg-3">
      <div class="hx-card h-100 overflow-hidden">
        <img src="<?= e($img) ?>" class="w-100 hx-provider-img" alt="Business photo">
        <div class="p-3">
          <div class="d-flex justify-content-between align-items-start">
            <div class="fw-semibold"><?= e($p['business_name']) ?></div>
            <?php if ((int)$p['is_verified']===1): ?><span class="badge text-bg-success">Verified</span><?php endif; ?>
          </div>
          <div class="hx-sub small"><?= e($p['city'] ?? '—') ?></div>
          <div class="mt-2 small">
            ⭐ <?= number_format((float)$p['avg_rating'], 1) ?>
            <span class="hx-sub">(<?= (int)$p['review_count'] ?>)</span>
          </div>
          <a class="btn hx-btn hx-btn-dark btn-sm mt-3 w-100" href="<?= e(base_url('provider_profile.php?id=' . (int)$p['id'])) ?>">
            View profile
          </a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php if (!is_logged_in()): ?>
  <div class="hx-card p-4 p-md-5 mt-5">
    <div class="row g-3 align-items-center">
      <div class="col-12 col-lg-8">
        <h3 class="h4 hx-title mb-1">Grow your business with HomeEase</h3>
        <div class="hx-sub">Create your profile, upload a business photo, list services, and start receiving bookings.</div>
      </div>
      <div class="col-12 col-lg-4 text-lg-end">
        <a class="btn hx-btn hx-btn-brand btn-lg" href="<?= e(base_url('register_provider.php')) ?>">Become a provider</a>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../app/views/footer.php'; ?>