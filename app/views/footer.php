</main>

<footer class="mt-5 bg-white border-top">
  <style>
    .footer-wrap{
      background:
        radial-gradient(1200px 400px at 20% -10%, rgba(34,197,94,.12), transparent 60%),
        radial-gradient(900px 350px at 90% 10%, rgba(59,130,246,.10), transparent 55%),
        linear-gradient(#ffffff, #fbfbfb);
    }
    .footer-logo-dot{
      width:10px;height:10px;border-radius:999px;background:#22c55e;display:inline-block;
      box-shadow: 0 0 0 6px rgba(34,197,94,.10);
      margin-left:8px;
    }
    .footer-link{
      color:#374151; text-decoration:none;
    }
    .footer-link:hover{
      color:#111827; text-decoration:underline;
    }
    .footer-badge{
      border:1px solid rgba(17,24,39,.12);
      border-radius:999px;
      padding:.35rem .6rem;
      font-size:.82rem;
      color:#374151;
      background: rgba(255,255,255,.7);
    }
    .footer-card{
      border:1px solid rgba(17,24,39,.08);
      border-radius: 18px;
      box-shadow: 0 12px 30px rgba(17,24,39,.06);
      background: rgba(255,255,255,.75);
      backdrop-filter: blur(10px);
    }
  </style>

  <div class="footer-wrap">
    <div class="container py-5">

      <!-- Top CTA -->
      <div class="footer-card p-4 p-md-5 mb-4">
        <div class="row g-4 align-items-center">
          <div class="col-12 col-lg-8">
            <div class="d-flex align-items-center mb-2">
              <div class="fw-semibold fs-5">HomeEase</div>
              <span class="footer-logo-dot"></span>
              <span class="ms-2 text-muted small">Trusted home services</span>
            </div>
            <div class="text-muted">
              Book verified professionals, track job progress, and rate your experience — all in one place.
            </div>

            <div class="d-flex flex-wrap gap-2 mt-3">
              <span class="footer-badge">Verified providers</span>
              <span class="footer-badge">Fast booking</span>
              <span class="footer-badge">Transparent tracking</span>
              <span class="footer-badge">Ratings & reviews</span>
            </div>
          </div>

          <div class="col-12 col-lg-4 text-lg-end">
            <div class="d-grid d-lg-inline-flex gap-2">
              <a class="btn btn-dark" href="<?= e(base_url('services.php')) ?>">Browse services</a>
              <?php if (!is_logged_in()): ?>
                <a class="btn btn-outline-dark" href="<?= e(base_url('register_provider.php')) ?>">Join as provider</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Links + Info -->
      <div class="row g-4">
        <div class="col-12 col-lg-4">
          <div class="fw-semibold mb-2">About</div>
          <div class="text-muted">
            HomeEase connects customers with trusted home-service professionals.
            Built for speed, safety, and great customer experience.
          </div>
        </div>

        <div class="col-6 col-lg-2">
          <div class="fw-semibold mb-2">Explore</div>
          <div class="d-flex flex-column gap-2">
            <a class="footer-link" href="<?= e(base_url('index.php')) ?>">Home</a>
            <a class="footer-link" href="<?= e(base_url('services.php')) ?>">Services</a>
            <?php if (is_logged_in() && current_user()['role']==='provider'): ?>
              <a class="footer-link" href="<?= e(base_url('provider/dashboard.php')) ?>">Provider dashboard</a>
            <?php endif; ?>
            <?php if (is_logged_in() && current_user()['role']==='customer'): ?>
              <a class="footer-link" href="<?= e(base_url('customer/bookings.php')) ?>">My bookings</a>
            <?php endif; ?>
          </div>
        </div>

        <div class="col-6 col-lg-2">
          <div class="fw-semibold mb-2">Account</div>
          <div class="d-flex flex-column gap-2">
            <?php if (!is_logged_in()): ?>
              <a class="footer-link" href="<?= e(base_url('login.php')) ?>">Login</a>
              <a class="footer-link" href="<?= e(base_url('register_customer.php')) ?>">Sign up</a>
              <a class="footer-link" href="<?= e(base_url('register_provider.php')) ?>">Join as provider</a>
            <?php else: ?>
              <span class="text-muted small">Signed in as</span>
              <span class="fw-semibold"><?= e(current_user()['name']) ?></span>
              <a class="footer-link text-danger" href="<?= e(base_url('logout.php')) ?>">Logout</a>
            <?php endif; ?>
          </div>
        </div>

        <div class="col-12 col-lg-4">
          <div class="fw-semibold mb-2">Contact</div>
          <div class="text-muted">For demo purposes, add your contact here.</div>

          <div class="mt-3 d-flex flex-wrap gap-2">
            <span class="footer-badge">Email: araf@gmail.com.local</span>
            <span class="footer-badge">Phone: +88001818309895</span>
          </div>
        </div>
      </div>

      <hr class="my-4">

      <!-- Bottom bar -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <div class="text-muted small">
          © <?= date('Y') ?> HomeEase • <span class="text-muted">my_homeease</span>
        </div>
        <div class="text-muted small">
          Built with PHP • MySQL • Bootstrap
        </div>
      </div>

    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>