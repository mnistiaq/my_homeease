<?php
require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/helpers.php';
require_role('provider');

$u = current_user();

$title = "Upload cover photo - my_homeease";
require __DIR__ . '/../../app/views/header.php';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  require_csrf();

  if (!isset($_FILES['cover']) || $_FILES['cover']['error'] !== UPLOAD_ERR_OK) {
    echo '<div class="alert alert-danger">Upload failed.</div>';
  } else {
    $file = $_FILES['cover'];

    if ($file['size'] > 5 * 1024 * 1024) {
      echo '<div class="alert alert-danger">Max size 5MB.</div>';
    } else {
      $tmp = $file['tmp_name'];
      $finfo = new finfo(FILEINFO_MIME_TYPE);
      $mime = $finfo->file($tmp);

      $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
      ];

      if (!isset($allowed[$mime])) {
        echo '<div class="alert alert-danger">Only JPG, PNG, WEBP allowed.</div>';
      } else {
        $ext = $allowed[$mime];
        $name = 'cover_' . (int)$u['id'] . '_' . bin2hex(random_bytes(6)) . '.' . $ext;

        $destDir = __DIR__ . '/../../storage/uploads';
        if (!is_dir($destDir)) mkdir($destDir, 0775, true);

        $destPath = $destDir . '/' . $name;

        if (!move_uploaded_file($tmp, $destPath)) {
          echo '<div class="alert alert-danger">Could not save file.</div>';
        } else {
          $stmt = $pdo->prepare("UPDATE provider_profiles SET cover_photo=? WHERE user_id=?");
          $stmt->execute([$name, (int)$u['id']]);

          flash_set("Cover photo uploaded.", "success");
          redirect(base_url('provider/dashboard.php'));
        }
      }
    }
  }
}
?>
<h1 class="h4 mb-3">Upload business cover photo</h1>

<div class="card card-body" style="max-width:720px;">
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <div class="mb-3">
      <label class="form-label">Choose an image (JPG/PNG/WEBP, max 5MB)</label>
      <input class="form-control" type="file" name="cover" accept="image/*" required>
    </div>
    <button class="btn btn-dark" type="submit">Upload</button>
  </form>
</div>

<?php require __DIR__ . '/../../app/views/footer.php'; ?>