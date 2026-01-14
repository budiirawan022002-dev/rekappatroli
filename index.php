<?php

/**
 * Main index file for the Rekap Hastag application
 * This file uses the modular layout structure with separate header, main content, footer and JavaScript includes
 */

// Check authentication
require_once('auth_check.php');

// Include the header
include_once('includes/header.php');

// Include the main content


?>

<?php
// Main content of the application
?>
<!--start main wrapper-->
<main class="main-wrapper">
  <div class="main-content ">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4 shadow-sm">
      <div class="breadcrumb-title pe-3">
        <h4 class="mb-0 fw-bold"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h4>
      </div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="bi bi-house-door"></i> Home</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Upload Laporan</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="row g-3 g-md-4">
      <!-- Left Column: Input Form -->
      <div class="col-12 col-lg-5 col-xl-4 col-xxl-4 d-flex">
        <div class="card rounded-4 w-100 shadow-lg border-0" style="background: #ffffff; border: 1px solid #cfe2ff !important;">
          <div class="card-body p-3 p-md-4">
            <!-- Step-by-step Wizard Form -->
            <form id="wizardForm" action="" method="post" enctype="multipart/form-data">
              <!-- Alert container for validation messages -->
              <div id="formAlerts" class="mb-3"></div>

              <!-- Step 1: Pilih Jenis Laporan -->
              <div class="wizard-step" id="step-1">
                <div class="d-flex align-items-start mb-2 mb-md-3 flex-column flex-sm-row">
                  <div class="step-number-circle mb-2 mb-sm-0 me-sm-3" style="width: 40px; height: 40px; background: #0d6efd; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 16px; box-shadow: 0 3px 10px rgba(13, 110, 253, 0.3); flex-shrink: 0;">1</div>
                  <div class="flex-grow-1" style="min-width: 0;">
                    <h5 class="mb-1 fw-bold" style="font-size: 1.1rem;">Pilih Jenis Laporan</h5>
                    <p class="mb-0 small" style="color: #495057; font-weight: 400; font-size: 0.875rem;">Pilih satu atau lebih jenis laporan yang ingin Anda proses</p>
                  </div>
                </div>
                <div class="mb-3 mb-md-4">
                  <label class="form-label mb-2 mb-md-3" style="font-size: 0.9375rem;"><i class="bi bi-list-check"></i> Pilih Jenis Laporan:</label>
                  <div class="d-flex flex-column gap-1 gap-md-2">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="reportType[]" id="laporanKbd" value="Laporan KBD">
                      <label class="form-check-label" for="laporanKbd">
                        <i class="bi bi-file-earmark-text-fill text-primary"></i> 
                        <span>Laporan KBD</span>
                      </label>
                    </div>
                    <div class="form-check d-none" style="display: none !important;">
                      <input class="form-check-input" type="checkbox" name="reportType[]" id="laporanKhusus" value="Laporan Khusus">
                      <label class="form-check-label" for="laporanKhusus"><i class="bi bi-file-earmark-plus"></i> Laporan Khusus (Format KBD Jam 18:00)</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="reportType[]" id="patroliLandy" value="Patroli Landy">
                      <label class="form-check-label" for="patroliLandy">
                        <i class="bi bi-shield-check-fill text-success"></i> 
                        <span>Patroli MBG dan Sore</span>
                      </label>
                    </div>
                    <div class="form-check d-none" style="display: none !important;">
                      <input class="form-check-input" type="checkbox" name="reportType[]" id="patroliPagi" value="Patroli Pagi">
                      <label class="form-check-label" for="patroliPagi"><i class="bi bi-sunrise"></i> Patroli Pagi</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="reportType[]" id="patroliBencana" value="Patroli Bencana">
                      <label class="form-check-label" for="patroliBencana">
                        <i class="bi bi-exclamation-triangle-fill text-danger"></i> 
                        <span>Patroli Bencana</span>
                      </label>
                    </div>
                  </div>
                </div>
                <div class="d-grid mt-3 mt-md-4">
                  <button type="button" class="btn btn-primary btn-sm btn-md-normal" onclick="nextStep(1)">
                    <i class="bi bi-arrow-right me-2"></i>Next
                  </button>
                </div>
              </div>

              <!-- Step 2: Pilih Tanggal -->
              <div class="wizard-step d-none" id="step-2">
                <div class="d-flex align-items-start mb-3 flex-column flex-sm-row">
                  <div class="step-number-circle mb-2 mb-sm-0 me-sm-3" style="width: 40px; height: 40px; background: #667eea; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 16px; box-shadow: 0 3px 10px rgba(102, 126, 234, 0.4); flex-shrink: 0;">2</div>
                  <div class="flex-grow-1" style="min-width: 0;">
                    <h5 class="mb-1 fw-bold" style="font-size: 1.1rem;">Pilih Tanggal Laporan</h5>
                    <p class="mb-0 small" style="color: #495057; font-weight: 400; font-size: 0.875rem;">Tentukan tanggal laporan yang akan diproses</p>
                  </div>
                </div>
                <div class="mb-4">
                  <label for="tanggal" class="form-label"><i class="bi bi-calendar3"></i> Pilih Tanggal:</label>
                  <input type="date" name="tanggal" id="tanggal" required class="form-control">
                </div>
                <div class="d-flex justify-content-between gap-2">
                  <button type="button" class="btn btn-secondary flex-fill" onclick="prevStep(2)">
                    <i class="bi bi-arrow-left me-2"></i>Previous
                  </button>
                  <button type="button" class="btn btn-primary flex-fill" onclick="nextStep(2)">
                    <i class="bi bi-arrow-right me-2"></i>Next
                  </button>
                </div>
              </div>

              <!-- Step 3: Input Teks -->
              <div class="wizard-step d-none" id="step-3">
                <div class="d-flex align-items-start mb-3 flex-column flex-sm-row">
                  <div class="step-number-circle mb-2 mb-sm-0 me-sm-3" style="width: 40px; height: 40px; background: #667eea; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 16px; box-shadow: 0 3px 10px rgba(102, 126, 234, 0.4); flex-shrink: 0;">3</div>
                  <div class="flex-grow-1" style="min-width: 0;">
                    <h5 class="mb-1 fw-bold" style="font-size: 1.1rem;">Input Data Teks</h5>
                    <p class="mb-0 small" style="color: #495057; font-weight: 400; font-size: 0.875rem;">Masukkan data teks sesuai kebutuhan laporan</p>
                  </div>
                </div>

                <!-- Patroli Landy: Input Judul Laporan -->
                <div id="step3-judulLandy" class="card rounded-4 shadow-none border mb-3 d-none">
                  <div class="card-header bg-success text-white border-bottom">
                    <h6 class="mb-0">
                      <i class="material-icons-outlined">title</i> Judul Laporan Patroli Landy
                    </h6>
                  </div>
                  <div class="card-body">
                    <label for="judulLandy" class="form-label">
                      <i class="material-icons-outlined">text_fields</i> Pilih atau Input Judul Laporan:
                      <span class="text-danger">*</span>
                    </label>
                    <select name="judulLandy" id="judulLandy" class="form-select mb-2">
                      <option value="">-- Pilih Judul atau Input Manual di Bawah --</option>
                      <option value="Temuan Akun Medsos Narasi Negatif MBG">Temuan Akun Medsos Narasi Negatif MBG</option>
                      <option value="Monitoring Akun Media Sosial Provokasi">Monitoring Akun Media Sosial Provokasi</option>
                      <option value="Patroli Siber Media Sosial">Patroli Siber Media Sosial</option>
                      <option value="Identifikasi Akun Penyebar Hoax">Identifikasi Akun Penyebar Hoax</option>
                      <option value="Monitoring Narasi Negatif Pemerintah">Monitoring Narasi Negatif Pemerintah</option>
                      <option value="custom">✏️ Input Judul Manual</option>
                    </select>
                    
                    <div id="judulLandyCustomInput" class="d-none">
                      <input type="text" name="judulLandyCustom" id="judulLandyCustom" class="form-control" 
                        placeholder="Ketik judul laporan Anda di sini...">
                    </div>
                    
                    <small class="form-text" style="color: #495057; font-weight: 400;">
                      <i class="material-icons-outlined" style="font-size: 14px;">info</i>
                      Judul akan muncul di: <strong>*Perihal : Laporan {judul} di Wilayah Prov. Jambi Update {tanggal}*</strong>
                    </small>
                  </div>
                </div>

                <!-- Patroli Bencana: Input Judul Laporan -->
                <div id="step3-judulBencana" class="card rounded-4 shadow-none border mb-3 d-none">
                  <div class="card-header bg-danger text-white border-bottom">
                    <h6 class="mb-0">
                      <i class="material-icons-outlined">title</i> Judul Laporan Patroli Bencana
                    </h6>
                  </div>
                  <div class="card-body">
                    <label for="judulBencana" class="form-label">
                      <i class="material-icons-outlined">text_fields</i> Pilih atau Input Judul Laporan:
                      <span class="text-danger">*</span>
                    </label>
                    <select name="judulBencana" id="judulBencana" class="form-select mb-2">
                      <option value="">-- Pilih Judul atau Input Manual di Bawah --</option>
                      <option value="Patroli Siber Konten Provokatif Mendiskreditkan Pemerintah">Patroli Siber Konten Provokatif Mendiskreditkan Pemerintah</option>
                      <option value="Monitoring Konten Provokatif Penanganan Bencana">Monitoring Konten Provokatif Penanganan Bencana</option>
                      <option value="Patroli Siber Isu Bencana Alam">Patroli Siber Isu Bencana Alam</option>
                      <option value="Monitoring Narasi Negatif Penanganan Bencana">Monitoring Narasi Negatif Penanganan Bencana</option>
                      <option value="Identifikasi Konten Provokatif Isu Bencana">Identifikasi Konten Provokatif Isu Bencana</option>
                      <option value="custom">✏️ Input Judul Manual</option>
                    </select>
                    
                    <div id="judulBencanaCustomInput" class="d-none">
                      <input type="text" name="judulBencanaCustom" id="judulBencanaCustom" class="form-control" 
                        placeholder="Ketik judul laporan Anda di sini...">
                    </div>
                    
                    <small class="form-text" style="color: #495057; font-weight: 400;">
                      <i class="material-icons-outlined" style="font-size: 14px;">info</i>
                      Judul akan muncul di: <strong>*Perihal : Patroli Siber Konten Provokatif Mendiskreditkan Pemerintah di Wilayah Merpati-14 (Update {tanggal} Pukul {waktu} WIB)*</strong>
                    </small>
                  </div>
                </div>

                <!-- Laporan KBD, Patroli Landy, Patroli Pagi: Patrol Report -->
                <div id="step3-patrolReportUmum" class="card rounded-4 shadow-none border mb-3">
                  <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0">Input Patrol Report</h6>
                  </div>
                  <div class="card-body">
                    <label for="patrolReport" class="form-label">
                      <i class="material-icons-outlined">edit_note</i> Input Patrol Report:
                      <span class="text-danger">*</span>
                      <small style="color: #495057; font-weight: 400;">(Required for Laporan KBD, Patroli Landy, Patroli Pagi, Patroli Bencana)</small>
                    </label>
                    
                    <!-- Info/Help untuk format Patroli Landy -->
                    <div id="landyFormatHelp" class="alert alert-info py-2 px-3 mb-2 d-none">
                      <details>
                        <summary class="fw-bold" style="cursor: pointer;">
                          <i class="material-icons-outlined" style="font-size: 16px; vertical-align: middle;">info</i>
                          Format Patroli Landy - 2 Cara Input (Klik untuk lihat)
                        </summary>
                        <div class="mt-2">
                          <p class="mb-2"><strong>CARA 1: Multi-Line di Patrol Report (Recommended)</strong></p>
                          <small class="text-success">✅ Profiling langsung di report, tidak perlu isi form di bawah</small>
                        <div class="mt-2">
                          <p class="mb-2"><strong>Format Multi-Line Profiling Terstruktur:</strong></p>
                          <pre class="bg-white p-2 rounded border" style="font-size: 11px; line-height: 1.4;">nama akun: Akun Facebook Muhammad Sakti
link: https://www.facebook.com/muhammad.sakti
kategori: Kritik Program MBG
narasi: Komentar ini membangun narasi penolakan...
profiling:
Nama: MUHAMMAD SAKTI
Jenis Kelamin: LAKI-LAKI
Golongan Darah: -
Status Nikah: KAWIN
Agama: ISLAM
Lahir: OLAK BESAR
Umur: 37
Tanggal Lahir: 10-08-1988
Pekerjaan: WIRASWASTA
Provinsi: JAMBI
Kabupaten: BATANGHARI
Kecamatan: BATIN XXIV
Kelurahan: SIMPANG JELUTIH
Kode Pos: -
RT/RW: 004
Alamat Lengkap: SIMPANG JELUTIH
tanggal_postingan: 27/09/2025
wilayah: Jambi
korelasi: Bewinters langsung...
afiliasi: (Tidak ditemukan)</pre>
                          <small style="color: #495057; font-weight: 400;">
                            <strong>Catatan:</strong><br>
                            • Pisahkan setiap akun dengan <strong>enter kosong</strong><br>
                            • Field profiling diawali <code>profiling:</code> (kosong), lalu enter baru tulis data<br>
                            • <span class="text-success">✅ Baris kosong antara profiling terakhir dan tanggal_postingan OPSIONAL (tidak wajib)</span>
                          </small>
                          
                          <hr class="my-2">
                          
                          <p class="mb-2 mt-2"><strong>CARA 2: Form Profiling Terpisah (Optional)</strong></p>
                          <small class="text-warning">⚠️ Jika pakai CARA 1, form profiling di Step 4 akan auto-hide</small>
                        </div>
                      </details>
                    </div>
                    
                    <textarea name="patrolReport" id="patrolReport" rows="15" required class="form-control"
                      placeholder="Patroli Landy - Format dengan Profiling Terstruktur:

nama akun: [Nama Akun]
link: [URL]
kategori: [Kategori]
narasi: [Narasi]
profiling:
Nama: [NAMA LENGKAP]
Jenis Kelamin: [LAKI-LAKI/PEREMPUAN]
Umur: [ANGKA]
Pekerjaan: [PEKERJAAN]
Provinsi: [PROVINSI]
Kabupaten: [KABUPATEN]
Alamat Lengkap: [ALAMAT]
tanggal_postingan: [DD/MM/YYYY]
wilayah: [WILAYAH]
korelasi: [KORELASI]
afiliasi: [AFILIASI]

--- Atau Format Lama (4-9 baris sederhana) ---"></textarea>
                  </div>
                </div>

                <!-- Laporan Khusus: Input Patrol Report dan Tema -->
                <div id="step3-laporanKhusus" class="d-none">
                  <div class="card rounded-4 shadow-none border mb-3">
                    <div class="card-header bg-info text-white border-bottom">
                      <h6 class="mb-0">Input Patrol Report Laporan Khusus</h6>
                    </div>
                    <div class="card-body">
                      <label for="patrolReportKhusus" class="form-label">
                        <i class="material-icons-outlined">edit_note</i> Input Patrol Report Khusus:
                        <span class="text-danger">*</span>
                      </label>
                      <textarea name="patrolReportKhusus" id="patrolReportKhusus" rows="8" class="form-control"
                        placeholder="Format teks patroli khusus

nama akun
link
kategori
narasi
"></textarea>
                    </div>
                  </div>

                  <div class="card rounded-4 shadow-none border mb-3">
                    <div class="card-header bg-info text-white border-bottom">
                      <h6 class="mb-0">Input Tema Laporan Khusus</h6>
                    </div>
                    <div class="card-body">
                      <label for="inputTema" class="form-label">
                        <i class="material-icons-outlined">topic</i> Input Tema:
                        <span class="text-danger">*</span>
                      </label>
                      <input type="text" name="input_tema" id="inputTema" class="form-control" required
                        placeholder="Contoh: CIPKON DAN CIPOP MELALUI MEDIA SOSIAL DALAM RANGKA KONTER OPINI NEGATIF TERHADAP PRESIDEN PRABOWO SUBIANTO DI WILAYAH MERPATI">
                      <div id="temaValidationFeedback" class="invalid-feedback"></div>
                      <small class="form-text" style="color: #495057; font-weight: 400;">Tema ini akan digunakan untuk perihal narasi dan judul file Word (Patroli, CIPOP, dan Lampiran PDF)</small>
                    </div>
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
                <div class="d-flex align-items-start mb-3 flex-column flex-sm-row">
                  <div class="step-number-circle mb-2 mb-sm-0 me-sm-3" style="width: 40px; height: 40px; background: #667eea; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 16px; box-shadow: 0 3px 10px rgba(102, 126, 234, 0.4); flex-shrink: 0;">4</div>
                  <div class="flex-grow-1" style="min-width: 0;">
                    <h5 class="mb-1 fw-bold" style="font-size: 1.1rem;">Upload File Pendukung</h5>
                    <p class="text-secondary mb-0 small" style="font-size: 0.875rem;">Upload file sesuai kebutuhan laporan yang dipilih</p>
                  </div>
                </div>

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

                <!-- Laporan Khusus -->
                <div id="step4-laporanKhusus" class="d-none">
                  <div class="card shadow-none border mb-3">
                    <div class="card-header bg-info text-white border-bottom">
                      <h6 class="mb-0">Laporan Khusus - File Excel</h6>
                    </div>
                    <div class="card-body">
                      <label for="excelFilesKhusus" class="form-label"><i class="material-icons-outlined">description</i> Upload Excel Cipop:</label>
                      <input type="file" name="excelFilesKhusus[]" id="excelFilesKhusus" accept=".xlsx, .xls" multiple class="form-control">
                    </div>
                  </div>

                  <div class="card shadow-none border mb-3">
                    <div class="card-header bg-info text-white border-bottom">
                      <h6 class="mb-0">Laporan Khusus - Gambar</h6>
                    </div>
                    <div class="card-body">
                      <label class="form-label"><i class="material-icons-outlined">photo_library</i> Pilih Jenis Gambar Cipop:</label>
                      <div class="mb-2">
                        <div class="form-check form-check-inline">
                          <input class="form-check-input" type="radio" name="cipopImageTypeKhusus" id="cipopUploadFileKhusus" value="upload" checked>
                          <label class="form-check-label" for="cipopUploadFileKhusus">Upload File</label>
                        </div>
                        <div class="form-check form-check-inline">
                          <input class="form-check-input" type="radio" name="cipopImageTypeKhusus" id="cipopScreenshotLinkKhusus" value="screenshot">
                          <label class="form-check-label" for="cipopScreenshotLinkKhusus">Tangkapan Layar Link</label>
                        </div>
                      </div>

                      <div id="cipopUploadFileGroupKhusus">
                        <label for="imageFilesKhusus" class="form-label"><i class="material-icons-outlined">image</i> Upload Gambar (1-8):</label>
                        <input type="file" name="imageFilesKhusus[]" id="imageFilesKhusus" accept="image/*" multiple class="form-control" onchange="validateFileCount(this, 8); previewFiles(this, 'khususFilesPreview')">
                        <div id="khususFilesPreview" class="file-preview mt-2"></div>
                      </div>

                      <div id="cipopScreenshotLinkGroupKhusus" class="d-none">
                        <label for="cipopScreenshotLinksKhusus" class="form-label"><i class="material-icons-outlined">link</i> Masukkan Link (satu per baris):</label>
                        <textarea name="cipopScreenshotLinksKhusus" id="cipopScreenshotLinksKhusus" rows="5" class="form-control" placeholder="https://facebook.com/...
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

                  <!-- Screenshot Patroli Khusus -->
                  <div class="card shadow-none border mb-3">
                    <div class="card-header bg-info text-white border-bottom">
                      <h6 class="mb-0">Screenshot Patroli Laporan Khusus</h6>
                    </div>
                    <div class="card-body">
                      <label class="form-label"><i class="material-icons-outlined">photo_camera</i> Pilih Jenis Screenshot Patroli Khusus:</label>
                      <div class="mb-2">
                        <div class="form-check form-check-inline">
                          <input class="form-check-input" type="radio" name="patroliScreenshotTypeKhusus" id="patroliScreenshotUploadFileKhusus" value="upload" checked>
                          <label class="form-check-label" for="patroliScreenshotUploadFileKhusus">Upload File</label>
                        </div>
                        <div class="form-check form-check-inline">
                          <input class="form-check-input" type="radio" name="patroliScreenshotTypeKhusus" id="patroliScreenshotLinkKhusus" value="screenshot">
                          <label class="form-check-label" for="patroliScreenshotLinkKhusus">Tangkapan Layar Otomatis</label>
                        </div>
                      </div>

                      <div id="patroliScreenshotUploadFileGroupKhusus">
                        <label for="screenshotPatroliKhusus" class="form-label"><i class="material-icons-outlined">file_upload</i> Upload Screenshot Patroli Khusus (Maks. 8 gambar):</label>
                        <input type="file" name="screenshotPatroliKhusus[]" id="screenshotPatroliKhusus" accept="image/*" multiple max="8" class="form-control" onchange="validateFileCount(this, 8); previewFiles(this, 'khususPatrolPreview')">
                        <div id="khususPatrolPreview" class="file-preview mt-2"></div>
                        <small class="form-text" style="color: #495057; font-weight: 400;">Pilih maksimal 8 file gambar</small>
                      </div>

                      <div id="patroliScreenshotLinkWarningKhusus" class="d-none">
                        <div class="alert alert-warning mt-2 py-2 px-3 rounded-3 font-13">
                          <i class="material-icons-outlined">warning</i>
                          <b>Catatan penting:</b> Pastikan link pada Patrol Report Khusus valid dan dapat diakses publik.
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Laporan KBD, Patroli Landy, Patroli Pagi, Patroli Bencana: Screenshot Patroli -->
                <div id="step4-screenshotPatrolUmum" class="card shadow-none border mb-3">
                  <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0">
                      Screenshot Patroli
                      <span class="text-danger">*</span>
                      <small style="color: #495057; font-weight: 400;">(Required for Laporan KBD, Patroli Landy, Patroli Pagi, Patroli Bencana)</small>
                    </h6>
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
                      <label for="screenshotPatroli" class="form-label"><i class="material-icons-outlined">file_upload</i> Upload Screenshot Patroli (Maks. 8 gambar):</label>
                      <input type="file" name="screenshotPatroli[]" id="screenshotPatroli" accept="image/*" multiple max="8" class="form-control" onchange="validateFileCount(this, 8)">
                      <small class="form-text" style="color: #495057; font-weight: 400;">Pilih maksimal 8 file gambar</small>
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
                        <label for="upayaFiles" class="form-label"><i class="material-icons-outlined">file_upload</i> Upload Upaya (Maks. 8 gambar):</label>
                        <input type="file" name="upayaFiles[]" id="upayaFiles" accept="image/*" multiple max="8" class="form-control" onchange="validateFileCount(this, 8)">
                        <small class="form-text" style="color: #495057; font-weight: 400;">Pilih maksimal 8 file gambar</small>
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
                      <h6 class="mb-0">Upload Tangkapan Layar RAS (Khusus Landy/Bencana)</h6>
                    </div>
                    <div class="card-body">
                      <label for="rasFiles" class="form-label"><i class="material-icons-outlined">file_upload</i> Upload Screenshot RAS (Maks. 8 gambar):</label>
                      <input type="file" name="rasFiles[]" id="rasFiles" accept="image/*" multiple max="8" class="form-control" onchange="validateFileCount(this, 8)">
                      <small class="form-text" style="color: #495057; font-weight: 400;">Pilih maksimal 8 file gambar</small>
                    </div>
                  </div>

                  <!-- Screenshot Profiling for Landy/Bencana Report -->
                  <div id="landyProfilingScreenshotSection" class="card shadow-none border mb-3">
                    <div class="card-header bg-light border-bottom">
                      <h6 class="mb-0">Upload Foto Profiling (Khusus Landy/Bencana)</h6>
                    </div>
                    <div class="card-body">
                      <label for="profilingFiles" class="form-label"><i class="material-icons-outlined">file_upload</i> Upload Foto Profiling (Maks. 8 gambar):</label>
                      <input type="file" name="profilingFiles[]" id="profilingFiles" accept="image/*" multiple max="8" class="form-control" onchange="validateFileCount(this, 8)">
                      <small class="form-text" style="color: #495057; font-weight: 400;">Pilih maksimal 8 file gambar untuk profiling akun</small>
                    </div>
                  </div>

                  <!-- Form Data Profiling for Landy Report -->
                  <div id="landyProfilingDataSection" class="card shadow-none border mb-3 d-none" style="display: none !important;">
                    <div class="card-header bg-primary text-white border-bottom">
                      <h6 class="mb-0"><i class="material-icons-outlined">person</i> Data Profiling Akun (Khusus Landy)</h6>
                    </div>
                    <div class="card-body">
                      <div class="alert alert-info py-2 px-3 rounded-3 mb-3">
                        <i class="material-icons-outlined">info</i>
                        <small><b>Petunjuk:</b> Isi data profiling sesuai jumlah laporan. Pisahkan dengan koma (,) jika lebih dari 1 akun.<br>
                        <b>Contoh:</b> Jika ada 2 laporan, isi: <code>AHMAD SAKTI, BUDI SANTOSO</code></small>
                      </div>

                      <div class="row g-3">
                        <!-- Nama Lengkap -->
                        <div class="col-md-6">
                          <label for="profilingNama" class="form-label">
                            Nama Lengkap <span class="text-danger">*</span>
                          </label>
                          <input type="text" class="form-control" id="profilingNama" name="profilingNama" 
                            placeholder="Contoh: AHMAD SAKTI, BUDI SANTOSO">
                          <small style="color: #495057; font-weight: 400;">Pisahkan dengan koma jika lebih dari 1</small>
                        </div>

                        <!-- Jenis Kelamin -->
                        <div class="col-md-6">
                          <label for="profilingJenisKelamin" class="form-label">
                            Jenis Kelamin <span class="text-danger">*</span>
                          </label>
                          <input type="text" class="form-control" id="profilingJenisKelamin" name="profilingJenisKelamin" 
                            placeholder="Contoh: LAKI-LAKI, PEREMPUAN">
                          <small style="color: #495057; font-weight: 400;">LAKI-LAKI atau PEREMPUAN</small>
                        </div>

                        <!-- Golongan Darah -->
                        <div class="col-md-4">
                          <label for="profilingGolDarah" class="form-label">
                            Golongan Darah <span class="text-muted">(Optional)</span>
                          </label>
                          <input type="text" class="form-control" id="profilingGolDarah" name="profilingGolDarah" 
                            placeholder="Contoh: A, B, AB, O, -">
                        </div>

                        <!-- Status Nikah -->
                        <div class="col-md-4">
                          <label for="profilingStatusNikah" class="form-label">
                            Status Nikah
                          </label>
                          <input type="text" class="form-control" id="profilingStatusNikah" name="profilingStatusNikah" 
                            placeholder="Contoh: KAWIN, BELUM KAWIN">
                        </div>

                        <!-- Agama -->
                        <div class="col-md-4">
                          <label for="profilingAgama" class="form-label">
                            Agama
                          </label>
                          <input type="text" class="form-control" id="profilingAgama" name="profilingAgama" 
                            placeholder="Contoh: ISLAM, KRISTEN">
                        </div>

                        <!-- Tempat Lahir -->
                        <div class="col-md-4">
                          <label for="profilingTempatLahir" class="form-label">
                            Tempat Lahir <span class="text-muted">(Optional)</span>
                          </label>
                          <input type="text" class="form-control" id="profilingTempatLahir" name="profilingTempatLahir" 
                            placeholder="Contoh: JAKARTA, BANDUNG">
                        </div>

                        <!-- Umur -->
                        <div class="col-md-4">
                          <label for="profilingUmur" class="form-label">
                            Umur <span class="text-danger">*</span>
                          </label>
                          <input type="text" class="form-control" id="profilingUmur" name="profilingUmur" 
                            placeholder="Contoh: 34, 45">
                          <small style="color: #495057; font-weight: 400;">Angka saja</small>
                        </div>

                        <!-- Tanggal Lahir -->
                        <div class="col-md-4">
                          <label for="profilingTglLahir" class="form-label">
                            Tanggal Lahir <span class="text-muted">(Optional)</span>
                          </label>
                          <input type="text" class="form-control" id="profilingTglLahir" name="profilingTglLahir" 
                            placeholder="Contoh: 13-03-1991, 05-12-1980">
                        </div>

                        <!-- Pekerjaan -->
                        <div class="col-md-6">
                          <label for="profilingPekerjaan" class="form-label">
                            Pekerjaan <span class="text-danger">*</span>
                          </label>
                          <input type="text" class="form-control" id="profilingPekerjaan" name="profilingPekerjaan" 
                            placeholder="Contoh: WIRASWASTA, PNS">
                        </div>

                        <!-- Provinsi -->
                        <div class="col-md-6">
                          <label for="profilingProvinsi" class="form-label">
                            Provinsi <span class="text-danger">*</span>
                          </label>
                          <input type="text" class="form-control" id="profilingProvinsi" name="profilingProvinsi" 
                            placeholder="Contoh: JAMBI, RIAU">
                        </div>

                        <!-- Kabupaten -->
                        <div class="col-md-6">
                          <label for="profilingKabupaten" class="form-label">
                            Kabupaten/Kota <span class="text-danger">*</span>
                          </label>
                          <input type="text" class="form-control" id="profilingKabupaten" name="profilingKabupaten" 
                            placeholder="Contoh: BATANGHARI, INDRAGIRI HILIR">
                        </div>

                        <!-- Kecamatan -->
                        <div class="col-md-6">
                          <label for="profilingKecamatan" class="form-label">
                            Kecamatan <span class="text-muted">(Optional)</span>
                          </label>
                          <input type="text" class="form-control" id="profilingKecamatan" name="profilingKecamatan" 
                            placeholder="Contoh: BATIN XXIV, ENOK">
                        </div>

                        <!-- Kelurahan -->
                        <div class="col-md-4">
                          <label for="profilingKelurahan" class="form-label">
                            Kelurahan <span class="text-muted">(Optional)</span>
                          </label>
                          <input type="text" class="form-control" id="profilingKelurahan" name="profilingKelurahan" 
                            placeholder="Contoh: SIMPANG JELUTIH">
                        </div>

                        <!-- RT/RW -->
                        <div class="col-md-4">
                          <label for="profilingRTRW" class="form-label">
                            RT/RW <span class="text-muted">(Optional)</span>
                          </label>
                          <input type="text" class="form-control" id="profilingRTRW" name="profilingRTRW" 
                            placeholder="Contoh: 004, 003/002">
                        </div>

                        <!-- Kode Pos -->
                        <div class="col-md-4">
                          <label for="profilingKodePos" class="form-label">
                            Kode Pos <span class="text-muted">(Optional)</span>
                          </label>
                          <input type="text" class="form-control" id="profilingKodePos" name="profilingKodePos" 
                            placeholder="Contoh: 36613, -, (tidak diketahui)">
                        </div>

                        <!-- Alamat Lengkap -->
                        <div class="col-12">
                          <label for="profilingAlamat" class="form-label">
                            Alamat Lengkap <span class="text-danger">*</span>
                          </label>
                          <textarea class="form-control" id="profilingAlamat" name="profilingAlamat" rows="2" 
                            placeholder="Contoh: SIMPANG JELUTIH, DUSUN PERMAI JAYA"></textarea>
                          <small style="color: #495057; font-weight: 400;">Pisahkan dengan koma jika lebih dari 1 akun</small>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="d-flex justify-content-between gap-2">
                  <button type="button" class="btn btn-secondary flex-fill" onclick="prevStep(4)">
                    <i class="bi bi-arrow-left me-2"></i>Previous
                  </button>
                  <button type="submit" class="btn btn-primary flex-fill btn-lg" id="btnUploadProses">
                    <i class="bi bi-cloud-upload me-2"></i>Upload dan Proses
                    <span id="btnLoadingSpinner" class="loading-spinner" style="display:none;"></span>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Right Column -->
      <div class="col-12 col-lg-7 col-xl-8 col-xxl-8">
        <!-- Result Columns -->
        <div class="row g-3">
          <!-- Laporan KBD Results -->
          <div class="col-12 col-md-4 mb-3">
            <div class="card rounded-4 w-100 h-100 shadow-lg border-0 result-card-premium">
              <div class="card-header bg-primary text-white d-flex align-items-center" style="border-radius: 16px 16px 0 0;">
                <div class="result-icon-wrapper me-2" style="width: 40px; height: 40px; background: rgba(255, 255, 255, 0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                  <i class="bi bi-file-earmark-text-fill" style="font-size: 20px;"></i>
                </div>
                <h6 class="mb-0 fw-bold">Hasil Laporan KBD</h6>
              </div>
              <div class="card-body p-4">
                <div class="text-center py-4 result-placeholder">
                  <div class="result-icon-large mb-3" style="width: 60px; height: 60px; background: #e7f1ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                    <i class="bi bi-file-earmark-text" style="font-size: 32px; color: #0d6efd;"></i>
                  </div>
                  <p class="mt-2 text-muted fw-medium">Hasil laporan akan ditampilkan di sini.</p>
                </div>
                <div class="result-content" id="laporanKbdResultContent">
                  <!-- Result content will be filled by JavaScript -->
                </div>
              </div>
            </div>
          </div>

          <!-- Laporan Khusus Results -->
          <div class="col-12 col-md-3 mb-3 d-none" style="display: none !important;">
            <div class="card rounded-4 w-100 h-100 shadow-sm border-0">
              <div class="card-header bg-info text-white d-flex align-items-center">
                <i class="material-icons-outlined me-2">file_present</i>
                <h6 class="mb-0">Hasil Laporan Khusus</h6>
              </div>
              <div class="card-body p-3">
                <div class="text-center py-3 result-placeholder">
                  <i class="material-icons-outlined" style="font-size: 38px;">file_present</i>
                  <p class="mt-1 font-13">Hasil laporan akan ditampilkan di sini.</p>
                </div>
                <div class="result-content" id="laporanKhususResultContent">
                  <!-- Result content will be filled by JavaScript -->
                </div>
              </div>
            </div>
          </div>

          <!-- Patroli Landy Results -->
          <div class="col-12 col-md-4 mb-3">
            <div class="card rounded-4 w-100 h-100 shadow-lg border-0 result-card-premium">
              <div class="card-header bg-success text-white d-flex align-items-center" style="border-radius: 16px 16px 0 0;">
                <div class="result-icon-wrapper me-2" style="width: 40px; height: 40px; background: rgba(255, 255, 255, 0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                  <i class="bi bi-shield-check-fill" style="font-size: 20px;"></i>
                </div>
                <h6 class="mb-0 fw-bold">Hasil Patroli MBG dan Sore</h6>
              </div>
              <div class="card-body p-4">
                <div class="text-center py-4 result-placeholder">
                  <div class="result-icon-large mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, rgba(40, 167, 69, 0.1) 0%, rgba(32, 201, 151, 0.1) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                    <i class="bi bi-shield-check" style="font-size: 32px; color: #28a745;"></i>
                  </div>
                  <p class="mt-2 text-muted fw-medium">Hasil laporan akan ditampilkan di sini.</p>
                </div>
                <div class="result-content" id="laporanLandyResult">
                  <!-- Result content will be filled by JavaScript -->
                </div>
              </div>
            </div>
          </div>

          <!-- Patroli Pagi Results -->
          <div class="col-12 col-md-3 mb-3 d-none" style="display: none !important;">
            <div class="card rounded-4 w-100 h-100 shadow-sm border-0">
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

          <!-- Patroli Bencana Results -->
          <div class="col-12 col-md-4 mb-3">
            <div class="card rounded-4 w-100 h-100 shadow-lg border-0 result-card-premium">
              <div class="card-header bg-danger text-white d-flex align-items-center" style="border-radius: 16px 16px 0 0;">
                <div class="result-icon-wrapper me-2" style="width: 40px; height: 40px; background: rgba(255, 255, 255, 0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                  <i class="bi bi-exclamation-triangle-fill" style="font-size: 20px;"></i>
                </div>
                <h6 class="mb-0 fw-bold">Hasil Patroli Bencana</h6>
              </div>
              <div class="card-body p-4">
                <div class="text-center py-4 result-placeholder">
                  <div class="result-icon-large mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(255, 107, 107, 0.1) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                    <i class="bi bi-exclamation-triangle" style="font-size: 32px; color: #dc3545;"></i>
                  </div>
                  <p class="mt-2 text-muted fw-medium">Hasil laporan akan ditampilkan di sini.</p>
                </div>
                <div class="result-content" id="laporanBencanaResult">
                  <!-- Result content will be filled by JavaScript -->
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

<?php
// Include the footer
include_once('includes/footer.php');

// Include JavaScript files
include_once('includes/js-includes.php');
?>

<!-- Script untuk handle Judul Landy dan Profiling Form -->
<script>
// Tunggu sampai semua script loaded
window.addEventListener('load', function() {
    // Handle dropdown judul Landy - show custom input jika pilih "custom"
    const judulLandySelect = document.getElementById('judulLandy');
    const judulLandyCustomInput = document.getElementById('judulLandyCustomInput');
    const judulLandyCustom = document.getElementById('judulLandyCustom');
    
    if (judulLandySelect && judulLandyCustomInput) {
        judulLandySelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                judulLandyCustomInput.classList.remove('d-none');
                if (judulLandyCustom) {
                    judulLandyCustom.setAttribute('required', 'required');
                    judulLandyCustom.focus();
                }
            } else {
                judulLandyCustomInput.classList.add('d-none');
                if (judulLandyCustom) {
                    judulLandyCustom.removeAttribute('required');
                    judulLandyCustom.value = '';
                }
            }
        });
    }
    
    // Handle dropdown judul Bencana - show custom input jika pilih "custom"
    const judulBencanaSelect = document.getElementById('judulBencana');
    const judulBencanaCustomInput = document.getElementById('judulBencanaCustomInput');
    const judulBencanaCustom = document.getElementById('judulBencanaCustom');
    
    if (judulBencanaSelect && judulBencanaCustomInput) {
        judulBencanaSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                judulBencanaCustomInput.classList.remove('d-none');
                if (judulBencanaCustom) {
                    judulBencanaCustom.setAttribute('required', 'required');
                    judulBencanaCustom.focus();
                }
            } else {
                judulBencanaCustomInput.classList.add('d-none');
                if (judulBencanaCustom) {
                    judulBencanaCustom.removeAttribute('required');
                    judulBencanaCustom.value = '';
                }
            }
        });
    }
    
    // AUTO-HIDE Form Profiling Data jika format multi-line terdeteksi
    const patrolReport = document.getElementById('patrolReport');
    const profilingDataSection = document.getElementById('landyProfilingDataSection');
    
    function checkMultiLineFormat() {
        if (!patrolReport || !profilingDataSection) return;
        
        const patrolValue = patrolReport.value;
        const hasMultiLine = /profiling:\s*\n\s*Nama:/i.test(patrolValue);
        const hasProfiling = /profiling:/i.test(patrolValue); // Deteksi profiling apapun formatnya
        
        if (hasMultiLine) {
            console.log('🔵 Multi-line profiling detected - Hiding form');
            profilingDataSection.classList.add('d-none');
            
            // Remove required from all profiling form fields
            const profilingInputs = profilingDataSection.querySelectorAll('input, textarea');
            profilingInputs.forEach(input => {
                input.removeAttribute('required');
            });
            
            // Show info message
            let infoDiv = document.getElementById('profilingFormHiddenInfo');
            if (!infoDiv) {
                infoDiv = document.createElement('div');
                infoDiv.id = 'profilingFormHiddenInfo';
                infoDiv.className = 'alert alert-success mt-2';
                profilingDataSection.parentNode.insertBefore(infoDiv, profilingDataSection);
            }
            infoDiv.innerHTML = `
                <i class="material-icons-outlined" style="font-size: 16px; vertical-align: middle;">check_circle</i>
                <strong>Format Multi-Line Profiling Terdeteksi!</strong><br>
                <small>Form profiling data otomatis disembunyikan. Sistem akan gunakan data dari patrol report.</small>
            `;
            infoDiv.classList.remove('d-none');
        } else if (hasProfiling) {
            // Ada profiling tapi bukan multi-line (free-text) - form OPTIONAL
            console.log('🟡 Free-text profiling detected - Form optional');
            profilingDataSection.classList.remove('d-none');
            
            // Remove required (form jadi optional)
            const profilingInputs = profilingDataSection.querySelectorAll('input, textarea');
            profilingInputs.forEach(input => {
                input.removeAttribute('required');
            });
            
            // Show info message
            let infoDiv = document.getElementById('profilingFormHiddenInfo');
            if (!infoDiv) {
                infoDiv = document.createElement('div');
                infoDiv.id = 'profilingFormHiddenInfo';
                infoDiv.className = 'alert alert-info mt-2';
                profilingDataSection.parentNode.insertBefore(infoDiv, profilingDataSection);
            }
            infoDiv.innerHTML = `
                <i class="material-icons-outlined" style="font-size: 16px; vertical-align: middle;">info</i>
                <strong>Profiling Terdeteksi di Patrol Report!</strong><br>
                <small>Form profiling data menjadi <strong>opsional</strong>. Sistem akan gunakan data dari patrol report jika form tidak diisi.</small>
            `;
            infoDiv.classList.remove('d-none');
        } else {
            // Tidak ada profiling - check apakah Landy atau Bencana dipilih
            const selectedReports = Array.from(document.querySelectorAll('input[name="reportType[]"]:checked'))
                .map(cb => cb.value);
            const isLandySelected = selectedReports.includes('Patroli Landy');
            const isBencanaSelected = selectedReports.includes('Patroli Bencana');
            const isLandyOrBencana = isLandySelected || isBencanaSelected;
            
            if (isLandyOrBencana) {
                // Set required jika Landy atau Bencana dipilih
                console.log('⚪ No profiling - Form required (Landy selected:', isLandySelected, 'Bencana selected:', isBencanaSelected + ')');
                profilingDataSection.classList.remove('d-none');
                
                // Restore required attributes
                document.getElementById('profilingNama')?.setAttribute('required', 'required');
                document.getElementById('profilingJenisKelamin')?.setAttribute('required', 'required');
                document.getElementById('profilingUmur')?.setAttribute('required', 'required');
                document.getElementById('profilingPekerjaan')?.setAttribute('required', 'required');
                document.getElementById('profilingProvinsi')?.setAttribute('required', 'required');
                document.getElementById('profilingKabupaten')?.setAttribute('required', 'required');
                document.getElementById('profilingAlamat')?.setAttribute('required', 'required');
            } else {
                // Landy/Bencana TIDAK dipilih - jangan set required!
                console.log('⚪ No profiling but Landy/Bencana NOT selected - Form NOT required');
            }
            
            // Hide info message
            const infoDiv = document.getElementById('profilingFormHiddenInfo');
            if (infoDiv) {
                infoDiv.classList.add('d-none');
            }
        }
    }
    
    if (patrolReport) {
        patrolReport.addEventListener('blur', checkMultiLineFormat);
        patrolReport.addEventListener('input', checkMultiLineFormat);
    }
    
    // Form submission validation untuk judul Landy
    const wizardForm = document.getElementById('wizardForm');
    if (wizardForm) {
        wizardForm.addEventListener('submit', function(e) {
            const selectedReports = Array.from(document.querySelectorAll('input[name="reportType[]"]:checked'))
                .map(cb => cb.value);
            
            // Validate judul Landy jika Patroli Landy dipilih
            if (selectedReports.includes('Patroli Landy')) {
                const judulLandyValue = judulLandySelect ? judulLandySelect.value : '';
                const customJudulValue = judulLandyCustom ? judulLandyCustom.value.trim() : '';
                
                // Jika dropdown tidak dipilih atau pilih custom tapi custom input kosong
                if (!judulLandyValue || (judulLandyValue === 'custom' && !customJudulValue)) {
                    e.preventDefault();
                    alert('⚠️ Judul Laporan Patroli Landy wajib diisi!\n\nSilakan pilih judul dari dropdown atau input judul manual.');
                    
                    // Focus ke field yang bermasalah
                    if (judulLandyValue === 'custom') {
                        if (judulLandyCustom) judulLandyCustom.focus();
                    } else {
                        if (judulLandySelect) judulLandySelect.focus();
                    }
                    
                    return false;
                }
            }
            
            // Validate judul Bencana jika Patroli Bencana dipilih
            if (selectedReports.includes('Patroli Bencana')) {
                const judulBencanaValue = judulBencanaSelect ? judulBencanaSelect.value : '';
                const customJudulBencanaValue = judulBencanaCustom ? judulBencanaCustom.value.trim() : '';
                
                // Jika dropdown tidak dipilih atau pilih custom tapi custom input kosong
                if (!judulBencanaValue || (judulBencanaValue === 'custom' && !customJudulBencanaValue)) {
                    e.preventDefault();
                    alert('⚠️ Judul Laporan Patroli Bencana wajib diisi!\n\nSilakan pilih judul dari dropdown atau input judul manual.');
                    
                    // Focus ke field yang bermasalah
                    if (judulBencanaValue === 'custom') {
                        if (judulBencanaCustom) judulBencanaCustom.focus();
                    } else {
                        if (judulBencanaSelect) judulBencanaSelect.focus();
                    }
                    
                    return false;
                }
            }
        });
    }
    
    console.log('✅ Judul Landy handler initialized');
    console.log('✅ Judul Bencana handler initialized');
});
</script>
<style>
  /* Mobile Responsive Styles */
  @media (max-width: 768px) {
    .card-body {
      padding: 1rem !important;
    }
    
    .wizard-step {
      padding: 0;
    }
    
    .form-check {
      padding: 10px 12px !important;
      margin-bottom: 6px !important;
      border-radius: 10px;
    }
    
    .form-check-label {
      font-size: 0.9rem !important;
      word-wrap: break-word;
      line-height: 1.4 !important;
    }
    
    .form-check-label i {
      font-size: 1rem !important;
      margin-right: 0.5rem;
      flex-shrink: 0;
    }
    
    .form-check-input {
      width: 18px !important;
      height: 18px !important;
      margin-top: 0.2rem !important;
    }
    
    .btn {
      padding: 0.625rem 1.25rem !important;
      font-size: 0.9rem !important;
    }
    
    .btn-md-normal {
      padding: 0.75rem 1.5rem !important;
      font-size: 0.9375rem !important;
    }
    
    .form-label {
      font-size: 0.9rem !important;
      margin-bottom: 0.75rem !important;
    }
    
    .form-control, .form-select {
      font-size: 0.9rem !important;
      padding: 0.625rem 0.875rem !important;
    }
    
    .card {
      margin-bottom: 0.75rem !important;
    }
    
    .step-number-circle {
      width: 36px !important;
      height: 36px !important;
      font-size: 14px !important;
    }
    
    h5 {
      font-size: 1rem !important;
      margin-bottom: 0.5rem !important;
    }
    
    p.small {
      font-size: 0.8rem !important;
      line-height: 1.4 !important;
    }
    
    .d-flex.flex-column.flex-sm-row {
      gap: 0.75rem;
    }
    
    .mb-3 {
      margin-bottom: 0.75rem !important;
    }
    
    .mb-4 {
      margin-bottom: 1rem !important;
    }
    
    .gap-1 {
      gap: 0.5rem !important;
    }
  }
  
  @media (max-width: 576px) {
    .card-body {
      padding: 0.875rem !important;
    }
    
    .form-check {
      padding: 8px 10px !important;
      margin-bottom: 5px !important;
      border-radius: 8px;
    }
    
    .form-check-label {
      font-size: 0.875rem !important;
      line-height: 1.3 !important;
    }
    
    .form-check-label i {
      font-size: 0.95rem !important;
    }
    
    .form-check-input {
      width: 16px !important;
      height: 16px !important;
      margin-top: 0.15rem !important;
    }
    
    .step-number-circle {
      width: 32px !important;
      height: 32px !important;
      font-size: 13px !important;
    }
    
    h5 {
      font-size: 0.95rem !important;
      margin-bottom: 0.4rem !important;
    }
    
    p.small {
      font-size: 0.75rem !important;
    }
    
    .btn {
      padding: 0.5rem 1rem !important;
      font-size: 0.875rem !important;
    }
    
    .form-label {
      font-size: 0.875rem !important;
      margin-bottom: 0.5rem !important;
    }
    
    .mb-2 {
      margin-bottom: 0.5rem !important;
    }
    
    .mb-3 {
      margin-bottom: 0.75rem !important;
    }
  }
  
  @media (min-width: 769px) {
    .btn-md-normal {
      padding: 0.75rem 1.5rem;
      font-size: 0.9375rem;
    }
  }
</style>

