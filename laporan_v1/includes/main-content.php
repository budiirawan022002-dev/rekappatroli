<?php
// Main content of the application
?>
  <!--start main wrapper-->
  <main class="main-wrapper">
    <div class="main-content ">
      <!--breadcrumb-->
      <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Rekap Hastag</div>
        <div class="ps-3">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
              <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
              </li>
              <li class="breadcrumb-item active" aria-current="page">Upload Laporan</li>
            </ol>
          </nav>
        </div>
      </div>
      <!--end breadcrumb-->

      <div class="row">
        <!-- Left Column: Input Form -->
        <div class="col-12 col-lg-4 col-xxl-3 d-flex">
          <div class="card rounded-4 w-100">
            <div class="card-body">
              <!-- Step-by-step Wizard Form -->
              <form id="wizardForm" action="index.php" target="_blank" method="post" enctype="multipart/form-data">
                <!-- Alert container for validation messages -->
                <div id="formAlerts" class="mb-3"></div>

                <!-- Step 1: Pilih Jenis Laporan -->
                <div class="wizard-step" id="step-1">
                  <h5 class="mb-3 fw-bold">Langkah 1: Pilih Jenis Laporan</h5>
                  <p class="text-secondary mb-3 font-13">Pilih satu atau lebih jenis laporan yang ingin Anda proses.</p>
                  <div class="mb-3">
                    <label class="form-label"><i class="material-icons-outlined">dashboard</i> Pilih Jenis Laporan:</label>
                    <div>
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="reportType[]" id="laporanKbd" value="Laporan KBD">
                        <label class="form-check-label" for="laporanKbd"><i class="bi bi-file-earmark-text"></i> Laporan KBD</label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="reportType[]" id="patroliLandy" value="Patroli Landy">
                        <label class="form-check-label" for="patroliLandy"><i class="bi bi-shield-check"></i> Patroli Landy</label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="reportType[]" id="patroliPagi" value="Patroli Pagi">
                        <label class="form-check-label" for="patroliPagi"><i class="bi bi-sunrise"></i> Patroli Pagi</label>
                      </div>
                    </div>
                  </div>
                  <div class="d-grid">
                    <button type="button" class="btn btn-primary" onclick="nextStep(1)">Next</button>
                  </div>
                </div>

                <!-- Step 2: Pilih Tanggal -->
                <div class="wizard-step d-none" id="step-2">
                  <h5 class="mb-3 fw-bold">Langkah 2: Pilih Tanggal Laporan</h5>
                  <p class="text-secondary mb-3 font-13">Tentukan tanggal laporan yang akan diproses.</p>
                  <div class="mb-3">
                    <label for="tanggal" class="form-label"><i class="material-icons-outlined">calendar_today</i> Pilih Tanggal:</label>
                    <input type="date" name="tanggal" id="tanggal" required class="form-control">
                  </div>
                  <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" onclick="prevStep(2)">Previous</button>
                    <button type="button" class="btn btn-primary" onclick="nextStep(2)">Next</button>
                  </div>
                </div>

                <!-- Step 3: Input Teks -->
                <div class="wizard-step d-none" id="step-3">
                  <h5 class="mb-3 fw-bold">Langkah 3: Input Data Teks</h5>
                  <p class="text-secondary mb-3 font-13">Masukkan data teks sesuai kebutuhan laporan.</p>

                  <!-- Semua Jenis: Patrol Report -->
                  <div class="card rounded-4 shadow-none border mb-3">
                    <div class="card-header bg-light border-bottom">
                      <h6 class="mb-0">Input Patrol Report</h6>
                    </div>
                    <div class="card-body">
                      <label for="patrolReport" class="form-label"><i class="material-icons-outlined">edit_note</i> Input Patrol Report:</label>
                      <textarea name="patrolReport" id="patrolReport" rows="8" required class="form-control"
                        placeholder="Format teks patroli

nama akun
link
kategori
narasi
"></textarea>
                    </div>
                  </div>

                  <!-- Patroli Pagi: Input Upaya -->
                  <div id="step3-inputUpaya" class="d-none">
                    <div class="card rounded-4 shadow-none border mb-3">
                      <div class="card-header bg-light border-bottom">
                        <h6 class="mb-0">Input Upaya Patroli Pagi</h6>
                      </div>
                      <div class="card-body">
                        <label for="inputUpaya" class="form-label"><i class="material-icons-outlined">edit</i> Input Upaya Patroli Pagi:</label>
                        <textarea name="input_upaya" id="inputUpaya" rows="8" class="form-control"
                          placeholder="Format teks upaya

nama akun
link
narasi
"></textarea>
                      </div>
                    </div>
                  </div>

                  <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" onclick="prevStep(3)">Previous</button>
                    <button type="button" class="btn btn-primary" onclick="nextStep(3)">Next</button>
                  </div>
                </div>

                <!-- Step 4: Upload File -->
                <div class="wizard-step d-none" id="step-4">
                  <h5 class="mb-3 fw-bold">Langkah 4: Upload File Pendukung</h5>
                  <p class="text-secondary mb-3 font-13">Upload file sesuai kebutuhan laporan yang dipilih.</p>

                  <!-- Laporan KBD -->
                  <div id="step4-laporanKbd" class="d-none">
                    <div class="card shadow-none border mb-3">
                      <div class="card-header bg-light border-bottom">
                        <h6 class="mb-0">Laporan KBD - File Excel</h6>
                      </div>
                      <div class="card-body">
                        <label for="excelFiles" class="form-label"><i class="material-icons-outlined">description</i> Upload Excel Cipop:</label>
                        <input type="file" name="excelFiles[]" id="excelFiles" accept=".xlsx, .xls" multiple class="form-control">
                      </div>
                    </div>

                    <div class="card shadow-none border mb-3">
                      <div class="card-header bg-light border-bottom">
                        <h6 class="mb-0">Laporan KBD - Gambar</h6>
                      </div>
                      <div class="card-body">
                        <label class="form-label"><i class="material-icons-outlined">photo_library</i> Pilih Jenis Gambar Cipop:</label>
                        <div class="mb-2">
                          <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cipopImageType" id="cipopUploadFile" value="upload" checked>
                            <label class="form-check-label" for="cipopUploadFile">Upload File</label>
                          </div>
                          <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="cipopImageType" id="cipopScreenshotLink" value="screenshot">
                            <label class="form-check-label" for="cipopScreenshotLink">Tangkapan Layar Link</label>
                          </div>
                        </div>

                        <div id="cipopUploadFileGroup">
                          <label for="imageFiles" class="form-label"><i class="material-icons-outlined">image</i> Upload Gambar (1-8):</label>
                          <input type="file" name="imageFiles[]" id="imageFiles" accept="image/*" multiple class="form-control">
                        </div>

                        <div id="cipopScreenshotLinkGroup" class="d-none">
                          <label for="cipopScreenshotLinks" class="form-label"><i class="material-icons-outlined">link</i> Masukkan Link (satu per baris):</label>
                          <textarea name="cipopScreenshotLinks" id="cipopScreenshotLinks" rows="5" class="form-control" placeholder="https://facebook.com/...
https://instagram.com/..."></textarea>
                          <div class="alert alert-warning mt-2 py-2 px-3 rounded-3 font-13">
                            <i class="material-icons-outlined">warning</i>
                            <b>Catatan penting untuk tangkapan layar link:</b><br>
                            <ul class="mb-0 ps-3">
                              <li>Pastikan link yang dimasukkan valid dan dapat diakses publik.</li>
                              <li>Khusus Facebook: pastikan postingan/grup bersifat publik.</li>
                              <li>Platform TikTok: <b>belum didukung</b> otomatis screenshot.</li>
                            </ul>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Semua Jenis: Screenshot Patroli -->
                  <div class="card shadow-none border mb-3">
                    <div class="card-header bg-light border-bottom">
                      <h6 class="mb-0">Screenshot Patroli</h6>
                    </div>
                    <div class="card-body">
                      <label class="form-label"><i class="material-icons-outlined">photo_camera</i> Pilih Jenis Screenshot Patroli:</label>
                      <div class="mb-2">
                        <div class="form-check form-check-inline">
                          <input class="form-check-input" type="radio" name="patroliScreenshotType" id="patroliScreenshotUploadFile" value="upload" checked>
                          <label class="form-check-label" for="patroliScreenshotUploadFile">Upload File</label>
                        </div>
                        <div class="form-check form-check-inline">
                          <input class="form-check-input" type="radio" name="patroliScreenshotType" id="patroliScreenshotLink" value="screenshot">
                          <label class="form-check-label" for="patroliScreenshotLink">Tangkapan Layar Otomatis</label>
                        </div>
                      </div>

                      <div id="patroliScreenshotUploadFileGroup">
                        <label for="screenshotPatroli" class="form-label"><i class="material-icons-outlined">file_upload</i> Upload Screenshot Patroli:</label>
                        <input type="file" name="screenshotPatroli[]" id="screenshotPatroli" accept="image/*" multiple class="form-control">
                      </div>

                      <div id="patroliScreenshotLinkWarning" class="d-none">
                        <div class="alert alert-warning mt-2 py-2 px-3 rounded-3 font-13">
                          <i class="material-icons-outlined">warning</i>
                          <b>Catatan penting:</b> Pastikan link pada Patrol Report valid dan dapat diakses publik.
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Patroli Landy/Pagi -->
                  <div id="step4-patroliLandyPagi" class="d-none">
                    <!-- Upaya Patroli Pagi -->
                    <div id="upayaPatroliSection" class="card shadow-none border mb-3">
                      <div class="card-header bg-light border-bottom">
                        <h6 class="mb-0">Upload Upaya Patroli Pagi</h6>
                      </div>
                      <div class="card-body">
                        <div class="mb-2" id="upayaScreenshotTypeGroup">
                          <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="upayaScreenshotType" id="upayaScreenshotUploadFile" value="upload" checked>
                            <label class="form-check-label" for="upayaScreenshotUploadFile">Upload File</label>
                          </div>
                          <div class="form-check form-check-inline" id="upayaScreenshotLinkRadio">
                            <input class="form-check-input" type="radio" name="upayaScreenshotType" id="upayaScreenshotLink" value="screenshot">
                            <label class="form-check-label" for="upayaScreenshotLink">Tangkapan Layar Otomatis</label>
                          </div>
                        </div>

                        <div id="upayaScreenshotUploadFileGroup">
                          <label for="upayaFiles" class="form-label"><i class="material-icons-outlined">file_upload</i> Upload Upaya:</label>
                          <input type="file" name="upayaFiles[]" id="upayaFiles" accept="image/*" multiple class="form-control">
                        </div>

                        <div id="upayaScreenshotLinkWarning" class="d-none">
                          <div class="alert alert-warning mt-2 py-2 px-3 rounded-3 font-13">
                            <i class="material-icons-outlined">warning</i>
                            <b>Catatan penting:</b> Pastikan link pada data Upaya valid dan dapat diakses publik.
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Screenshot RAS for Landy Report -->
                    <div id="landyRasScreenshotSection" class="card shadow-none border mb-3">
                      <div class="card-header bg-light border-bottom">
                        <h6 class="mb-0">Upload Tangkapan Layar RAS (Khusus Landy)</h6>
                      </div>
                      <div class="card-body">
                        <label for="rasFiles" class="form-label"><i class="material-icons-outlined">file_upload</i> Upload Screenshot RAS:</label>
                        <input type="file" name="rasFiles[]" id="rasFiles" accept="image/*" multiple class="form-control">
                        <small class="form-text text-secondary">Upload gambar tangkapan layar aktivitas RAS (jumlah sesuai laporan)</small>
                      </div>
                    </div>
                  </div>

                  <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" onclick="prevStep(4)">Previous</button>
                    <button type="submit" class="btn btn-primary" id="btnUploadProses">
                      <i class="material-icons-outlined">upload_file</i> Upload dan Proses
                      <span id="btnLoadingSpinner" class="loading-spinner" style="display:none;"></span>
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Right Column -->
        <div class="col-12 col-lg-8 col-xxl-9">
          <!-- Result Columns -->
          <div class="row">
            <!-- Laporan KBD Results -->
            <div class="col-12 col-md-4 mb-3">
              <div class="card rounded-4 w-100 h-100">
                <div class="card-header bg-primary text-white d-flex align-items-center">
                  <i class="material-icons-outlined me-2">description</i>
                  <h6 class="mb-0">Hasil Laporan KBD</h6>
                </div>
                <div class="card-body p-3">
                  <div class="text-center py-3 result-placeholder">
                    <i class="material-icons-outlined" style="font-size: 38px;">description</i>
                    <p class="mt-1 font-13">Hasil laporan akan ditampilkan di sini.</p>
                  </div>
                  <div class="result-content" id="laporanKbdResultContent">
                    <!-- Result content will be filled by JavaScript -->
                  </div>
                </div>
              </div>
            </div>

            <!-- Patroli Landy Results -->
            <div class="col-12 col-md-4 mb-3">
              <div class="card rounded-4 w-100 h-100">
                <div class="card-header bg-success text-white d-flex align-items-center">
                  <i class="material-icons-outlined me-2">security</i>
                  <h6 class="mb-0">Hasil Patroli Landy</h6>
                </div>
                <div class="card-body p-3">
                  <div class="text-center py-3 result-placeholder">
                    <i class="material-icons-outlined" style="font-size: 38px;">security</i>
                    <p class="mt-1 font-13">Hasil laporan akan ditampilkan di sini.</p>
                  </div>
                  <div class="result-content" id="laporanLandyResult">
                    <!-- Result content will be filled by JavaScript -->
                  </div>
                </div>
              </div>
            </div>

            <!-- Patroli Pagi Results -->
            <div class="col-12 col-md-4 mb-3">
              <div class="card rounded-4 w-100 h-100">
                <div class="card-header bg-warning text-dark d-flex align-items-center">
                  <i class="material-icons-outlined me-2">wb_sunny</i>
                  <h6 class="mb-0">Hasil Patroli Pagi</h6>
                </div>
                <div class="card-body p-3">
                  <div class="text-center py-3 result-placeholder">
                    <i class="material-icons-outlined" style="font-size: 38px;">wb_sunny</i>
                    <p class="mt-1 font-13">Hasil laporan akan ditampilkan di sini.</p>
                  </div>
                  <div class="result-content" id="laporanPagiResult">
                    <!-- Result content will be filled by JavaScript -->
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Application Usage Guide Card - Now positioned below results -->
          <div class="row mt-4">
            <div class="col-12">
              <div class="card rounded-4 shadow-sm">
                <div class="card-header bg-light d-flex align-items-center">
                  <i class="material-icons-outlined me-2">help_outline</i>
                  <h5 class="mb-0">Panduan Penggunaan Aplikasi</h5>
                </div>
                <div class="card-body">
                  <!-- Usage Guides Accordion -->
                  <div class="accordion" id="usageGuideAccordion">
                    <!-- Laporan KBD Guide -->
                    <div class="accordion-item">
                      <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#kdpGuideCollapse" aria-expanded="true" aria-controls="kdpGuideCollapse">
                          <span class="material-icons-outlined text-primary me-2">file_present</span>
                          <strong>Laporan KBD</strong>
                        </button>
                      </h2>
                      <div id="kdpGuideCollapse" class="accordion-collapse collapse show" data-bs-parent="#usageGuideAccordion">
                        <div class="accordion-body">
                          <ol class="ps-3 mb-0">
                            <li>Pilih jenis laporan "Laporan KBD" pada langkah pertama</li>
                            <li>Masukkan tanggal laporan pada langkah kedua</li>
                            <li>Input data patrol report pada langkah ketiga sesuai format:
                              <div class="bg-light rounded p-2 mt-1 mb-1" style="font-size: 0.85rem;">
                                <code>nama akun<br>link<br>kategori<br>narasi</code>
                              </div>
                              <span class="text-info small">Pisahkan setiap data dengan baris kosong</span>
                            </li>
                            <li>Upload file Excel CIPOP dan gambar/screenshot pada langkah keempat</li>
                          </ol>
                        </div>
                      </div>
                    </div>

                    <!-- Patroli Landy Guide -->
                    <div class="accordion-item">
                      <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#landyGuideCollapse" aria-expanded="false" aria-controls="landyGuideCollapse">
                          <span class="material-icons-outlined text-success me-2">security</span>
                          <strong>Patroli Landy</strong>
                        </button>
                      </h2>
                      <div id="landyGuideCollapse" class="accordion-collapse collapse" data-bs-parent="#usageGuideAccordion">
                        <div class="accordion-body">
                          <ol class="ps-3 mb-0">
                            <li>Pilih jenis laporan "Patroli Landy" pada langkah pertama</li>
                            <li>Masukkan tanggal laporan pada langkah kedua</li>
                            <li>Input data patrol report pada langkah ketiga sesuai format:
                              <div class="bg-light rounded p-2 mt-1 mb-1" style="font-size: 0.85rem;">
                                <code>nama akun<br>link<br>kategori<br>narasi</code>
                              </div>
                            </li>
                            <li>Upload screenshot patroli dan tangkapan layar RAS pada langkah keempat</li>
                            <li class="text-warning"><strong>Penting:</strong> Jumlah tangkapan layar RAS harus sama dengan jumlah narasi patroli</li>
                          </ol>
                        </div>
                      </div>
                    </div>

                    <!-- Patroli Pagi Guide -->
                    <div class="accordion-item">
                      <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pagiGuideCollapse" aria-expanded="false" aria-controls="pagiGuideCollapse">
                          <span class="material-icons-outlined text-warning me-2">wb_sunny</span>
                          <strong>Patroli Pagi</strong>
                        </button>
                      </h2>
                      <div id="pagiGuideCollapse" class="accordion-collapse collapse" data-bs-parent="#usageGuideAccordion">
                        <div class="accordion-body">
                          <ol class="ps-3 mb-0">
                            <li>Pilih jenis laporan "Patroli Pagi" pada langkah pertama</li>
                            <li>Masukkan tanggal laporan pada langkah kedua</li>
                            <li>Input data patrol report dan upaya pada langkah ketiga:
                              <ul class="ps-3 mt-1">
                                <li>Format patrol report:
                                  <div class="bg-light rounded p-2 mt-1 mb-1" style="font-size: 0.85rem;">
                                    <code>nama akun<br>link<br>kategori<br>narasi</code>
                                  </div>
                                </li>
                                <li>Format upaya patroli pagi:
                                  <div class="bg-light rounded p-2 mt-1 mb-1" style="font-size: 0.85rem;">
                                    <code>nama akun<br>link<br>narasi</code>
                                  </div>
                                </li>
                              </ul>
                            </li>
                            <li>Upload screenshot patroli dan gambar upaya takedown pada langkah keempat</li>
                          </ol>
                        </div>
                      </div>
                    </div>

                    <!-- Troubleshooting Tips -->
                    <div class="accordion-item">
                      <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#tipsCollapse" aria-expanded="false" aria-controls="tipsCollapse">
                          <span class="material-icons-outlined text-danger me-2">tips_and_updates</span>
                          <strong>Tips & Troubleshooting</strong>
                        </button>
                      </h2>
                      <div id="tipsCollapse" class="accordion-collapse collapse" data-bs-parent="#usageGuideAccordion">
                        <div class="accordion-body">
                          <ul class="ps-3 mb-0">
                            <li>Pastikan format input teks sesuai contoh dan setiap data dipisahkan dengan baris kosong</li>
                            <li>Fitur tangkapan layar otomatis hanya berfungsi untuk link publik (tidak private/tertutup)</li>
                            <li>Untuk Facebook, gunakan link postingan yang dapat diakses publik (bukan group tertutup)</li>
                            <li>Platform TikTok: <span class="fw-medium">belum didukung</span> otomatis screenshot</li>
                            <li>Jika proses gagal, periksa kembali format input dan coba upload file dalam jumlah lebih sedikit</li>
                            <li>File hasil laporan akan tersedia di kolom hasil setelah proses selesai 100%</li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div><!--end row-->

    </div>
  </main>
  <!--end main wrapper-->
