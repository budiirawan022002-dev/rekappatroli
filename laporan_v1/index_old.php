<?php
require 'vendor/autoload.php';
require 'fungsi_proses.php';
require 'fungsi_konversi.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Excel File</title>
    <!-- Use local Bootstrap CSS and Bootstrap Icons from node_modules -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="node_modules/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/custom.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="bi bi-hash"></i> Rekap Hastag</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php"><i class="bi bi-house-door"></i> Patroli</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="screenshot_link.php"><i class="bi bi-camera"></i> Ambil Screenshot Link</a>
                    </li>
                </ul>
                <!-- Petunjuk Penggunaan di pojok kanan navbar -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-primary fw-bold" href="#" id="petunjukDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-info-circle"></i> Petunjuk Penggunaan
                        </a>
                        <div class="dropdown-menu dropdown-menu-end p-3" aria-labelledby="petunjukDropdown" style="min-width:350px;max-width:400px;">
                            <div style="font-size:0.97rem;">
                                <ol class="mb-3 ps-3" style="font-size: 1rem;">
                                    <li>
                                        <b>Pilih Jenis Laporan</b> (Laporan KBD, Patroli Landy, atau Patroli Pagi) pada langkah pertama.
                                        <ul>
                                            <li><b>Laporan KBD</b>: Rekap laporan Cipop (Excel) dan hasil patroli (Word & PDF).</li>
                                            <li><b>Patroli Landy</b>: Laporan patroli dan tangkapan layar RAS (Word & PDF).</li>
                                            <li><b>Patroli Pagi</b>: Laporan patroli pagi dan upaya takedown (Word & PDF).</li>
                                        </ul>
                                    </li>
                                    <li>
                                        <b>Pilih Tanggal Laporan</b> pada langkah kedua.
                                        <ul>
                                            <li>Tanggal digunakan sebagai penanda laporan dan nama file hasil.</li>
                                        </ul>
                                    </li>
                                    <li>
                                        <b>Input Data Teks</b> pada langkah ketiga:
                                        <ul>
                                            <li>
                                                <b>Input Patrol Report</b> wajib diisi untuk semua jenis laporan.<br>
                                                <span class="text-warning">Format:</span>
                                                <pre style="background:#f8f9fa;color:#212529;padding:6px 10px;border-radius:5px;font-size:0.97em;">
nama akun
link
kategori
narasi
                                                </pre>
                                                <span class="text-info">Pisahkan setiap laporan dengan baris kosong.</span>
                                            </li>
                                            <li>
                                                <b>Input Upaya Patroli Pagi</b> (khusus jika memilih Patroli Pagi):<br>
                                                Format:
                                                <pre style="background:#f8f9fa;color:#212529;padding:6px 10px;border-radius:5px;font-size:0.97em;">
nama akun
link
narasi
                                                </pre>
                                            </li>
                                        </ul>
                                    </li>
                                    <li>
                                        <b>Upload File Pendukung</b> pada langkah keempat:
                                        <ul>
                                            <li>
                                                <b>Screenshot Patroli</b>: Untuk semua jenis laporan, unggah screenshot patroli sesuai jumlah narasi atau gunakan fitur otomatis tangkapan layar dari link.
                                            </li>
                                            <li>
                                                <b>Laporan KBD</b>: Upload file Excel Cipop (.xlsx/.xls) dan 1-8 gambar (jpg/png) atau masukkan link untuk tangkapan layar otomatis.
                                            </li>
                                            <li>
                                                <b>Patroli Landy</b>: Upload tangkapan layar RAS (jumlah harus sesuai dengan jumlah narasi patroli).
                                            </li>
                                            <li>
                                                <b>Patroli Pagi</b>: Upload file gambar upaya (jumlah sesuai laporan) atau gunakan tangkapan layar otomatis dari link.
                                            </li>
                                        </ul>
                                    </li>
                                    <li>
                                        Klik <b>Upload dan Proses</b> untuk memulai pembuatan laporan. Tunggu hingga proses selesai dan progress mencapai 100%.
                                    </li>
                                    <li>
                                        Setelah proses selesai, <b>download file hasil laporan</b> pada kolom yang tersedia di sebelah kanan form.
                                    </li>
                                </ol>
                                <div class="alert alert-warning py-2 px-3 mb-2" style="font-size:0.95rem;">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <b>Catatan untuk Patroli Landy:</b> File tangkapan layar RAS wajib diupload dan jumlahnya harus sama dengan jumlah narasi patroli. Fitur ini digunakan untuk melampirkan bukti aktivitas RAS dalam dokumen laporan.
                                </div>
                                <div class="alert alert-secondary py-2 px-3 mb-0" style="font-size:0.95rem;">
                                    <i class="bi bi-bug-fill"></i>
                                    Jika terjadi error, cek kembali format input, jumlah file, dan pastikan tidak ada file yang rusak. Untuk tangkapan layar otomatis, pastikan link dapat diakses publik dan bukan private.
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Progress bar: letakkan tepat setelah navbar -->
    <div id="progressOverlay" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:2000;background:rgba(255,255,255,0.85);backdrop-filter:blur(2px);align-items:center;justify-content:center;">
        <div style="min-width:340px;max-width:90vw;padding:2rem 2.5rem;background:#ffffff;border-radius:18px;box-shadow:0 8px 32px rgba(0,0,0,0.15);display:flex;flex-direction:column;align-items:center;border:1px solid #dee2e6;">
            <div class="mb-3">
                <div class="spinner-border text-primary" role="status" style="width:2.5rem;height:2.5rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <div id="progressBarStatus" class="text-primary mb-2" style="font-size:1.1rem;"></div>
            <div class="progress w-100 mb-2" style="height: 24px;">
                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%">0%</div>
            </div>
            <div id="progressBarServerMsg" class="text-warning mt-2" style="font-size:0.98rem;"></div>
            <!-- Debug button untuk development -->
            <button id="debugInfoToggle" class="btn btn-sm btn-outline-secondary mt-2" style="font-size:0.8rem;" type="button">Show Debug Info</button>
            <div id="debugInfo" class="mt-2 p-2 bg-light" style="display:none; width:100%; max-height:200px; overflow-y:auto; font-size:0.8rem; font-family:monospace; color:#212529;border:1px solid #dee2e6;"></div>
        </div>
    </div>

    <div class="container-fluid py-4">
        <div class="row mb-4">
            <!-- Left Column: Input Form -->
            <div class="col-md-3 mb-4">
                <!-- Step-by-step Wizard Form -->
                <form id="wizardForm" action="index.php" target="_blank" method="post" enctype="multipart/form-data" class="bg-light p-4 rounded shadow-sm border">
                    <!-- Alert container for validation messages -->
                    <div id="formAlerts" class="mb-3"></div>

                    <!-- Step 1: Pilih Jenis Laporan -->
                    <div class="wizard-step" id="step-1">
                        <h5 class="fw-bold mb-2">Langkah 1: Pilih Jenis Laporan</h5>
                        <p class="text-secondary mb-3" style="font-size:0.95rem;">Pilih satu atau lebih jenis laporan yang ingin Anda proses.</p>
                        <div class="mb-3">
                            <label class="form-label"><i class="bi bi-ui-checks-grid"></i> Pilih Jenis Laporan:</label>
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
                        <h5 class="fw-bold mb-2">Langkah 2: Pilih Tanggal Laporan</h5>
                        <p class="text-secondary mb-3" style="font-size:0.95rem;">Tentukan tanggal laporan yang akan diproses.</p>
                        <div class="mb-3">
                            <label for="tanggal" class="form-label"><i class="bi bi-calendar-event"></i> Pilih Tanggal:</label>
                            <input type="date" name="tanggal" id="tanggal" required class="form-control">
                        </div>
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="prevStep(2)">Previous</button>
                            <button type="button" class="btn btn-primary" onclick="nextStep(2)">Next</button>
                        </div>
                    </div>
                    <!-- Step 3: Input Teks -->
                    <div class="wizard-step d-none" id="step-3">
                        <h5 class="fw-bold mb-2">Langkah 3: Input Data Teks</h5>
                        <p class="text-secondary mb-3" style="font-size:0.95rem;">Masukkan data teks sesuai kebutuhan laporan.</p>
                        <!-- Semua Jenis: Patrol Report -->
                        <div class="card bg-light mb-2 border">
                            <div class="card-header text-dark">
                                <strong>Input Patrol Report</strong>
                            </div>
                            <div class="card-body p-2">
                                <label for="patrolReport" class="form-label"><i class="bi bi-pencil-square"></i> Input Patrol Report:</label>
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
                            <div class="card bg-light mb-2 border">
                                <div class="card-header text-dark">
                                    <strong>Input Upaya Patroli Pagi</strong>
                                </div>
                                <div class="card-body p-2">
                                    <label for="inputUpaya" class="form-label"><i class="bi bi-pencil"></i> Input Upaya Patroli Pagi:</label>
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
                        <h5 class="fw-bold mb-2">Langkah 4: Upload File Pendukung</h5>
                        <p class="text-secondary mb-3" style="font-size:0.95rem;">Upload file sesuai kebutuhan laporan yang dipilih.</p>
                        <!-- Laporan KBD -->
                        <div id="step4-laporanKbd" class="d-none">
                            <div class="mb-3">
                                <label class="form-label fw-bold" style="color:#0dcaf0;"><i class="bi bi-file-earmark-excel"></i> Laporan KBD</label>
                                <label for="excelFiles" class="form-label"><i class="bi bi-file-earmark-excel"></i> Upload Excel Cipop:</label>
                                <input type="file" name="excelFiles[]" id="excelFiles" accept=".xlsx, .xls" multiple class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold" style="color:#0dcaf0;"><i class="bi bi-images"></i> Laporan KBD</label>
                                <label class="form-label"><i class="bi bi-image"></i> Pilih Jenis Gambar Cipop:</label>
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
                                    <label for="imageFiles" class="form-label"><i class="bi bi-image"></i> Upload Gambar (1-8):</label>
                                    <input type="file" name="imageFiles[]" id="imageFiles" accept="image/*" multiple class="form-control">
                                </div>
                                <div id="cipopScreenshotLinkGroup" class="d-none">
                                    <label for="cipopScreenshotLinks" class="form-label"><i class="bi bi-link"></i> Masukkan Link (satu per baris):</label>
                                    <textarea name="cipopScreenshotLinks" id="cipopScreenshotLinks" rows="5" class="form-control" placeholder="https://facebook.com/...
https://instagram.com/..."></textarea>
                                    <div class="alert alert-warning mt-2 py-2 px-3" style="font-size:0.95rem;">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        <b>Catatan penting untuk tangkapan layar link:</b><br>
                                        <ul class="mb-0 ps-3" style="font-size:0.97em;">
                                            <li>Pastikan link yang dimasukkan valid dan dapat diakses publik (bukan link bodong).</li>
                                            <li>Khusus Facebook: pastikan postingan/grup bersifat publik (terbuka), bukan private/tertutup.</li>
                                            <li>Platform TikTok: <b>belum didukung</b> otomatis screenshot, karena halaman TikTok sering gagal diambil otomatis.</li>
                                            <li>Hasil screenshot tergantung akses publik dan status login browser server.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Semua Jenis: Screenshot Patroli -->
                        <div class="mb-3">
                            <label class="form-label"><i class="bi bi-image"></i> Pilih Jenis Screenshot Patroli:</label>
                            <div class="mb-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="patroliScreenshotType" id="patroliScreenshotUploadFile" value="upload" checked>
                                    <label class="form-check-label" for="patroliScreenshotUploadFile">Upload File</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="patroliScreenshotType" id="patroliScreenshotLink" value="screenshot">
                                    <label class="form-check-label" for="patroliScreenshotLink">Tangkapan Layar Link (otomatis dari link pada Patrol Report)</label>
                                </div>
                            </div>
                            <div id="patroliScreenshotUploadFileGroup">
                                <label for="screenshotPatroli" class="form-label"><i class="bi bi-image"></i> Upload Screenshot Patroli:</label>
                                <input type="file" name="screenshotPatroli[]" id="screenshotPatroli" accept="image/*" multiple class="form-control">
                            </div>
                            <div id="patroliScreenshotLinkWarning" class="d-none">
                                <div class="alert alert-warning mt-2 py-2 px-3" style="font-size:0.95rem;">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <b>Catatan penting untuk tangkapan layar link:</b><br>
                                    <ul class="mb-0 ps-3" style="font-size:0.97em;">
                                        <li>Pastikan link pada Patrol Report valid dan dapat diakses publik (bukan link bodong).</li>
                                        <li>Khusus Facebook: pastikan postingan/grup bersifat publik (terbuka), bukan private/tertutup.</li>
                                        <li>Platform TikTok: <b>belum didukung</b> otomatis screenshot, karena halaman TikTok sering gagal diambil otomatis.</li>
                                        <li>Hasil screenshot tergantung akses publik dan status login browser server.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Patroli Landy/Pagi - Moved below screenshot patroli section -->
                        <div id="step4-patroliLandyPagi" class="d-none">
                            <!-- Only show this div for Patroli Pagi, not for Landy -->
                            <div id="upayaPatroliSection" class="card bg-light mb-3 border">
                                <div class="card-header text-dark">
                                    <strong>Upload Upaya Patroli Pagi</strong>
                                </div>
                                <div class="card-body p-2">
                                    <label class="form-label fw-bold" style="color:#ffc107;"><i class="bi bi-shield-check"></i> Laporan Patroli Pagi</label>
                                    <div class="mb-2" id="upayaScreenshotTypeGroup">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="upayaScreenshotType" id="upayaScreenshotUploadFile" value="upload" checked>
                                            <label class="form-check-label" for="upayaScreenshotUploadFile">Upload File</label>
                                        </div>
                                        <div class="form-check form-check-inline" id="upayaScreenshotLinkRadio">
                                            <input class="form-check-input" type="radio" name="upayaScreenshotType" id="upayaScreenshotLink" value="screenshot">
                                            <label class="form-check-label" for="upayaScreenshotLink">Tangkapan Layar Link (otomatis dari link pada Upaya)</label>
                                        </div>
                                    </div>
                                    <div id="upayaScreenshotUploadFileGroup">
                                        <label for="upayaFiles" class="form-label"><i class="bi bi-upload"></i> Upload Upaya:</label>
                                        <input type="file" name="upayaFiles[]" id="upayaFiles" accept="image/*" multiple class="form-control">
                                    </div>
                                    <div id="upayaScreenshotLinkWarning" class="d-none">
                                        <div class="alert alert-warning mt-2 py-2 px-3" style="font-size:0.95rem;">
                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                            <b>Catatan penting untuk tangkapan layar link:</b><br>
                                            <ul class="mb-0 ps-3" style="font-size:0.97em;">
                                                <li>Pastikan link pada data Upaya valid dan dapat diakses publik (bukan link bodong).</li>
                                                <li>Khusus Facebook: pastikan postingan/grup bersifat publik (terbuka), bukan private/tertutup.</li>
                                                <li>Platform TikTok: <b>belum didukung</b> otomatis screenshot, karena halaman TikTok sering gagal diambil otomatis.</li>
                                                <li>Hasil screenshot tergantung akses publik dan status login browser server.</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- New Section: RAS Screenshot for Landy Report -->
                            <div id="landyRasScreenshotSection" class="card bg-light mb-3 border">
                                <div class="card-header text-dark">
                                    <strong>Upload Tangkapan Layar RAS (Khusus Landy)</strong>
                                </div>
                                <div class="card-body p-2">
                                    <label class="form-label fw-bold" style="color:#0dcaf0;"><i class="bi bi-file-earmark-image"></i> Screenshot RAS Landy</label>
                                    <!-- Removed radio buttons for screenshot type, only showing file upload -->
                                    <div>
                                        <label for="rasFiles" class="form-label"><i class="bi bi-upload"></i> Upload Screenshot RAS:</label>
                                        <input type="file" name="rasFiles[]" id="rasFiles" accept="image/*" multiple class="form-control">
                                        <small class="form-text text-dark">Upload gambar tangkapan layar aktivitas RAS (jumlah sesuai laporan)</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="prevStep(4)">Previous</button>
                            <button type="submit" class="btn btn-primary" id="btnUploadProses">
                                <i class="bi bi-upload"></i> Upload dan Proses
                                <span id="btnLoadingSpinner" class="loading-spinner" style="display:none;"></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <!-- Middle Column: Laporan KBD -->
            <div class="col-md-3" id="laporanKbdResult"></div>
            <!-- Right Column: Patroli Landy -->
            <div class="col-md-3" id="laporanLandyResult"></div>
            <!-- Far Right Column: Patroli Pagi -->
            <div class="col-md-3" id="laporanPagiResult"></div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <!-- Kosong atau bisa diisi info tambahan -->
            </div>
        </div>
    </div>

    <!-- JavaScript dependencies -->
    <script src="js/bootstrap.bundle.min.js"></script>

    <!-- Application specific scripts -->
    <script src="js/progress.js"></script>
    <script src="js/ui-init.js"></script>
    <!-- Removed form-validation.js as its functionality is in custom.js -->
    <script src="js/ajax-handler.js"></script>
    <script src="js/custom/custom.js"></script>
</body>

</html>