<?php
  require 'vendor/autoload.php';
  require 'fungsi_proses.php';
  require 'fungsi_konversi.php';
  
  // Set the page title for the header
  $pageTitle = "Ambil Screenshot Link";
  $activePage = "screenshot_link";
  
  // Include header template
  include 'includes/header.php';
?>

<!-- Progress bar overlay -->
<div id="progressOverlay" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:2000;background:rgba(255,255,255,0.85);backdrop-filter:blur(2px);align-items:center;justify-content:center;">
  <div style="min-width:340px;max-width:90vw;padding:2rem 2.5rem;background:#ffffff;border-radius:18px;box-shadow:0 8px 32px rgba(0,0,0,0.15);display:flex;flex-direction:column;align-items:center;border:1px solid #dee2e6;">
    <div class="mb-3">
      <div class="spinner-border text-primary" role="status" style="width:2.5rem;height:2.5rem;">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>
    <div id="progressBarStatus" class="text-primary mb-2" style="font-size:1.1rem;text-align:center;"></div>
    <div class="progress w-100 mb-2" style="height: 24px;">
      <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%">0%</div>
    </div>
    <div id="progressBarServerMsg" class="text-warning mt-2" style="font-size:0.98rem;"></div>
  </div>
</div>

<!-- Main Content -->
<div class="container py-4 flex-grow-1">
  <!-- Page Header -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card bg-light">
        <div class="card-body">
          <h3 class="mb-2"><i class="bi bi-camera"></i> Ambil Screenshot Link</h3>
          <p class="text-muted mb-0">Fitur ini memungkinkan Anda mengambil screenshot dari berbagai link sosial media.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Form Input Link -->
    <div class="col-lg-5 mb-4">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><i class="bi bi-link"></i> Form Input Link</h5>
        </div>
        <div class="card-body">
          <form id="screenshotForm" method="post">
            <div class="mb-3">
              <label for="linkInput" class="form-label fw-bold"><i class="bi bi-link-45deg"></i> Masukkan Link (satu per baris):</label>
              <textarea id="linkInput" name="linkInput" class="form-control" rows="8" placeholder="https://facebook.com/...
https://instagram.com/...
https://x.com/...
https://twitter.com/..."></textarea>
              <div class="form-text mt-2">
                <i class="bi bi-check-circle-fill text-success"></i> Platform yang didukung: Facebook, Instagram, Twitter/X, Youtube<br>
                <i class="bi bi-exclamation-triangle-fill text-warning"></i> Pastikan link dapat diakses publik (bukan private)
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold"><i class="bi bi-tag"></i> Jenis Screenshot:</label>
              <select id="jenisScreenshot" name="jenisScreenshot" class="form-select">
                <option value="cipop">Cipop</option>
                <option value="patroli">Patroli</option>
                <option value="upaya">Upaya</option>
              </select>
            </div>
            <div class="d-grid gap-2">
              <button type="submit" id="btnAmbilScreenshot" class="btn btn-primary btn-lg">
                <i class="bi bi-camera"></i> Ambil Screenshot
              </button>
              <button type="button" id="btnShowAllScreenshots" class="btn btn-outline-secondary">
                <i class="bi bi-images"></i> Tampilkan Semua Screenshot
              </button>
              <small class="text-muted mt-1">
                <i class="bi bi-info-circle"></i> Klik "Tampilkan Semua Screenshot" untuk melihat semua screenshot yang sudah ada tanpa mengambil screenshot baru.
              </small>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Hasil Screenshot -->
    <div class="col-lg-7">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0"><i class="bi bi-images"></i> Hasil Screenshot</h5>
        </div>
        <div class="card-body">
          <div id="downloadButtonContainer" class="d-none mb-3">
            <!-- Tombol download akan ditampilkan di sini -->
          </div>

          <div id="screenshotResult" class="mb-3">
            <div class="text-center py-5 text-muted">
              <i class="bi bi-image" style="font-size: 3rem;"></i>
              <p class="mt-3">Hasil screenshot akan ditampilkan di sini</p>
              <p class="text-muted small">Klik tombol "Ambil Screenshot" untuk memulai proses</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
// Include custom JavaScript for this page
include 'includes/js-includes.php';

// Include footer template (which should include the JS template at the end)
include 'includes/footer.php';
?>