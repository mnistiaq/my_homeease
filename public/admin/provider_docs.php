<?php
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_role('admin');

$provider_id = (int)($_GET['id'] ?? 0);
if ($provider_id <= 0) { http_response_code(400); echo "Bad request"; exit; }

$stmt = $pdo->prepare("
SELECT u.id, u.name, u.email, u.phone,
       p.business_name, p.city, p.is_verified,
       p.nid_file, p.trade_license_file, p.verification_status, p.verification_note
FROM users u
JOIN provider_profiles p ON p.user_id=u.id
WHERE u.id=? AND u.role='provider'
");
$stmt->execute([$provider_id]);
$prov = $stmt->fetch();
if (!$prov) { http_response_code(404); echo "Not found"; exit; }

$title = "Provider Documents - my_homeease";
require __DIR__ . '/../../app/views/header.php';

function file_url(?string $name): ?string {
  if (!$name) return null;
  return base_url('../storage/uploads/verify/' . $name);
}

$nidUrl = file_url($prov['nid_file']);
$tradeUrl = file_url($prov['trade_license_file']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_csrf();

  $action = $_POST['action'] ?? '';
  $note = trim($_POST['note'] ?? '');

  if ($action === 'approve') {
    if (!$prov['nid_file'] || !$prov['trade_license_file']) {
      flash_set("Cannot approve: documents missing.", "warning");
      redirect(base_url('admin/provider_docs.php?id='.$provider_id));
    }

    $stmt = $pdo->prepare("
      UPDATE provider_profiles
      SET is_verified=1, verification_status='approved', verification_note=?
      WHERE user_id=?
    ");
    $stmt->execute([$note ?: null, $provider_id]);

    flash_set("Provider verified.", "success");
    redirect(base_url('admin/dashboard.php'));
  }

  if ($action === 'reject') {
    $stmt = $pdo->prepare("
      UPDATE provider_profiles
      SET is_verified=0, verification_status='rejected', verification_note=?
      WHERE user_id=?
    ");
    $stmt->execute([$note ?: 'Rejected by admin', $provider_id]);

    flash_set("Provider rejected (not verified).", "warning");
    redirect(base_url('admin/dashboard.php'));
  }
}
?>

<h1 class="h4 mb-3">Provider documents</h1>

<div class="card mb-3">
  <div class="card-body">
    <div class="fw-semibold"><?= e($prov['business_name']) ?></div>
    <div class="text-muted small">
      Owner: <?= e($prov['name']) ?> • <?= e($prov['city'] ?? '—') ?> • <?= e($prov['email']) ?>
    </div>

    <div class="mt-2">
      <span class="text-muted">Status:</span>
      <span class="badge text-bg-<?=
        $prov['verification_status']==='approved'?'success':
        ($prov['verification_status']==='pending'?'warning':
        ($prov['verification_status']==='rejected'?'danger':'secondary'))
      ?>"><?= e($prov['verification_status']) ?></span>

      <?php if ((int)$prov['is_verified']===1): ?>
        <span class="badge text-bg-success ms-2">Verified</span>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="fw-semibold mb-2">National ID</div>
        <?php if (!$nidUrl): ?>
          <div class="alert alert-secondary">Not uploaded.</div>
        <?php else: ?>
          <?php if (str_ends_with($prov['nid_file'], '.pdf')): ?>
            <a class="btn btn-outline-dark" target="_blank" href="<?= e($nidUrl) ?>">Open NID PDF</a>
          <?php else: ?>
            <img src="<?= e($nidUrl) ?>" class="img-fluid rounded border" alt="NID">
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card h-100">
      <div class="card-body">
        <div class="fw-semibold mb-2">Trade License</div>
        <?php if (!$tradeUrl): ?>
          <div class="alert alert-secondary">Not uploaded.</div>
        <?php else: ?>
          <?php if (str_ends_with($prov['trade_license_file'], '.pdf')): ?>
            <a class="btn btn-outline-dark" target="_blank" href="<?= e($tradeUrl) ?>">Open Trade License PDF</a>
          <?php else: ?>
            <img src="<?= e($tradeUrl) ?>" class="img-fluid rounded border" alt="Trade License">
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="card mt-3">
  <div class="card-body">
    <form method="post">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

      <div class="mb-3">
        <label class="form-label">Admin note (optional)</label>
        <input class="form-control" name="note" value="<?= e($prov['verification_note'] ?? '') ?>" placeholder="e.g., clear documents, approved">
      </div>

      <div class="d-flex gap-2">
        <button class="btn btn-success" name="action" value="approve" type="submit">Approve & Verify</button>
        <button class="btn btn-outline-danger" name="action" value="reject" type="submit">Reject</button>
        <a class="btn btn-outline-secondary ms-auto" href="<?= e(base_url('admin/dashboard.php')) ?>">Back</a>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../../app/views/footer.php'; ?>