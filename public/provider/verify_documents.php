<?php
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_role('provider');

$u = current_user();

$stmt = $pdo->prepare("SELECT nid_file, trade_license_file, verification_status, verification_note FROM provider_profiles WHERE user_id=?");
$stmt->execute([(int)$u['id']]);
$p = $stmt->fetch();

$title = "Submit Verification Documents - my_homeease";
require __DIR__ . '/../../app/views/header.php';

function save_upload(string $field, int $providerId): ?string {
  if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;

  $file = $_FILES[$field];

  if ($file['size'] > 6 * 1024 * 1024) { // 6MB
    throw new Exception("File too large (max 6MB).");
  }

  $tmp = $file['tmp_name'];
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime = $finfo->file($tmp);

  $allowed = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'application/pdf' => 'pdf',
  ];

  if (!isset($allowed[$mime])) {
    throw new Exception("Only JPG, PNG, WEBP, or PDF allowed.");
  }

  $ext = $allowed[$mime];
  $name = $field . '_' . $providerId . '_' . bin2hex(random_bytes(6)) . '.' . $ext;

  $destDir = __DIR__ . '/../../storage/uploads/verify';
  if (!is_dir($destDir)) mkdir($destDir, 0775, true);

  $destPath = $destDir . '/' . $name;
  if (!move_uploaded_file($tmp, $destPath)) {
    throw new Exception("Could not save uploaded file.");
  }

  return $name;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_csrf();

  try {
    $nid = save_upload('nid', (int)$u['id']);
    $trade = save_upload('trade', (int)$u['id']);

    if (!$nid || !$trade) {
      throw new Exception("Both NID and Trade License are required.");
    }

    $stmt = $pdo->prepare("
      UPDATE provider_profiles
      SET nid_file=?, trade_license_file=?, verification_status='pending', verification_note=NULL, is_verified=0
      WHERE user_id=?
    ");
    $stmt->execute([$nid, $trade, (int)$u['id']]);

    flash_set("Documents submitted! Admin will review and verify you.", "success");
    redirect(base_url('provider/dashboard.php'));
  } catch (Exception $e) {
    echo '<div class="alert alert-danger">'.e($e->getMessage()).'</div>';
  }
}
?>

<h1 class="h4 mb-3">Submit verification documents</h1>

<?php if ($p): ?>
  <div class="alert alert-info">
    <div><b>Status:</b> <?= e($p['verification_status']) ?></div>
    <?php if (!empty($p['verification_note'])): ?>
      <div class="small text-muted">Note: <?= e($p['verification_note']) ?></div>
    <?php endif; ?>
    <div class="small text-muted mt-1">
      Upload clear documents. Admin verifies only after checking both files.
    </div>
  </div>
<?php endif; ?>

<div class="card card-body" style="max-width:780px;">
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

    <div class="mb-3">
      <label class="form-label">National ID (JPG/PNG/WEBP/PDF, max 6MB)</label>
      <input class="form-control" type="file" name="nid" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Trade License (JPG/PNG/WEBP/PDF, max 6MB)</label>
      <input class="form-control" type="file" name="trade" required>
    </div>

    <button class="btn btn-dark" type="submit">Submit for verification</button>
  </form>
</div>

<?php require __DIR__ . '/../../app/views/footer.php'; ?>