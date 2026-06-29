<?php

/**
 * Panduan Penggunaan Aplikasi
 * This page contains the usage guide for the Rekap Hastag application
 */

// Check authentication
require_once('auth_check.php');

// Include the header
include_once('includes/header.php');

?>

<!--start main wrapper-->
<main class="main-wrapper">
  <div class="main-content">
    <!--breadcrumb-->
    <div class="row justify-content-center mb-4">
      <div class="col-12 col-xl-10 col-xxl-9">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center shadow-sm" style="max-width: 1200px; margin: 0 auto;">
          <div class="breadcrumb-title pe-3">
            <h4 class="mb-0 fw-bold"><i class="bi bi-book-fill me-2"></i>Panduan Penggunaan</h4>
          </div>
          <div class="ps-3">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="bi bi-house-door"></i> Home</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Panduan Penggunaan</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
    <!--end breadcrumb-->

    <div class="row justify-content-center">
      <div class="col-12 col-xl-10 col-xxl-9">
        <!-- Application Usage Guide Card - Enhanced Modern Design -->
        <div class="card rounded-4 shadow-lg border-0" style="overflow: hidden; max-width: 1200px; margin: 0 auto;">
          <div class="card-header border-0 p-4" style="background: #0d6efd;">
            <div class="d-flex align-items-center">
              <div class="usage-guide-icon-wrapper me-3" style="width: 50px; height: 50px; background: rgba(255, 255, 255, 0.25); border-radius: 12px; display: flex; align-items: center; justify-content: center; border: 2px solid rgba(255, 255, 255, 0.3);">
                <i class="bi bi-book-fill text-white" style="font-size: 24px;"></i>
              </div>
              <div>
                <h4 class="mb-0 text-white fw-bold">Panduan Penggunaan Aplikasi</h4>
                <p class="mb-0 text-white small" style="opacity: 0.9;">Pelajari cara menggunakan setiap fitur dengan mudah</p>
              </div>
            </div>
          </div>
          <div class="card-body p-0">
            <!-- Usage Guides Accordion -->
            <div class="accordion accordion-flush" id="usageGuideAccordion" style="--bs-accordion-border-color: transparent;">
              <!-- Laporan KBD Guide -->
              <div class="accordion-item border-bottom">
                <h2 class="accordion-header">
                  <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#kdpGuideCollapse" aria-expanded="true" aria-controls="kdpGuideCollapse" style="background: #f0f4ff; border: none; padding: 1.25rem 1.5rem;">
                    <div class="d-flex align-items-center w-100">
                      <div class="guide-icon-box me-3" style="width: 45px; height: 45px; background: #0d6efd; border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 8px rgba(13, 110, 253, 0.3);">
                        <i class="bi bi-file-earmark-text-fill text-white" style="font-size: 20px;"></i>
                      </div>
                      <div class="flex-grow-1">
                        <h6 class="mb-0 fw-bold text-dark">Laporan KBD</h6>
                        <small class="text-muted">Format laporan standar KBD</small>
                      </div>
                      <i class="bi bi-chevron-down ms-2 text-primary"></i>
                    </div>
                  </button>
                </h2>
                <div id="kdpGuideCollapse" class="accordion-collapse collapse show" data-bs-parent="#usageGuideAccordion">
                  <div class="accordion-body p-4" style="background: #ffffff;">
                    <div class="guide-steps">
                      <div class="step-item mb-3 d-flex align-items-start">
                        <div class="step-number me-3" style="width: 32px; height: 32px; background: #0d6efd; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(13, 110, 253, 0.3);">1</div>
                        <div class="flex-grow-1">
                          <p class="mb-0 fw-medium">Pilih jenis laporan <span class="badge bg-primary">"Laporan KBD"</span> pada langkah pertama</p>
                        </div>
                      </div>
                      <div class="step-item mb-3 d-flex align-items-start">
                        <div class="step-number me-3" style="width: 32px; height: 32px; background: #0d6efd; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(13, 110, 253, 0.3);">2</div>
                        <div class="flex-grow-1">
                          <p class="mb-0 fw-medium">Masukkan tanggal laporan pada langkah kedua</p>
                        </div>
                      </div>
                      <div class="step-item mb-3 d-flex align-items-start">
                        <div class="step-number me-3" style="width: 32px; height: 32px; background: #0d6efd; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(13, 110, 253, 0.3);">3</div>
                        <div class="flex-grow-1">
                          <p class="mb-2 fw-medium">Input data patrol report pada langkah ketiga sesuai format:</p>
                          <div class="position-relative mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                              <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Isi data sesuai format, data tersimpan otomatis</small>
                              <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-primary copy-format-btn" data-target="kbd-format" style="border-radius: 8px 0 0 8px;">
                                  <i class="bi bi-clipboard me-1"></i>Copy
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary clear-format-btn" data-target="kbd-format" style="border-radius: 0 8px 8px 0;">
                                  <i class="bi bi-trash me-1"></i>Hapus
                                </button>
                              </div>
                            </div>
                            <textarea id="kbd-format" class="form-control editable-format" placeholder="Isi data sesuai format di bawah..." style="font-family: 'Courier New', monospace; font-size: 0.85rem; line-height: 1.6; background: #ffffff; border: 2px solid #0d6efd; border-radius: 8px; padding: 12px; min-height: 150px; resize: vertical; cursor: text;" data-storage-key="kbd-format-data">nama akun: 
link: 
kategori: 
narasi: </textarea>
                            <div class="copy-feedback position-absolute top-0 end-0 m-2" style="display: none; background: rgba(40, 167, 69, 0.9); color: white; padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; z-index: 10;">
                              <i class="bi bi-check-circle me-1"></i>Copied!
                            </div>
                            <div class="clear-feedback position-absolute top-0 end-0 m-2" style="display: none; background: rgba(220, 53, 69, 0.9); color: white; padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; z-index: 10;">
                              <i class="bi bi-check-circle me-1"></i>Dihapus!
                            </div>
                          </div>
                          <small class="text-muted d-block mt-2">
                            <i class="bi bi-lightbulb me-1"></i><strong>Tips:</strong> Data yang Anda isi akan tersimpan otomatis dan tidak hilang saat refresh halaman. Gunakan tombol "Hapus" untuk mengosongkan kembali. Bisa input beberapa akun sekaligus, pisahkan dengan baris kosong.
                          </small>
                        </div>
                      </div>
                      <div class="step-item mb-0 d-flex align-items-start">
                        <div class="step-number me-3" style="width: 32px; height: 32px; background: #0d6efd; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(13, 110, 253, 0.3);">4</div>
                        <div class="flex-grow-1">
                          <p class="mb-0 fw-medium">Upload file Excel CIPOP dan gambar/screenshot pada langkah keempat</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Laporan Khusus Guide -->
              <div class="accordion-item border-bottom d-none" style="display: none !important;">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#khususGuideCollapse" aria-expanded="false" aria-controls="khususGuideCollapse" style="background: linear-gradient(135deg, rgba(13, 202, 240, 0.1) 0%, rgba(0, 123, 255, 0.1) 100%); border: none; padding: 1.25rem 1.5rem;">
                    <div class="d-flex align-items-center w-100">
                      <div class="guide-icon-box me-3" style="width: 45px; height: 45px; background: linear-gradient(135deg, #0dcaf0 0%, #0d6efd 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(13, 202, 240, 0.3);">
                        <i class="bi bi-file-earmark-check-fill text-white" style="font-size: 20px;"></i>
                      </div>
                      <div class="flex-grow-1">
                        <h6 class="mb-0 fw-bold text-dark">Laporan Khusus</h6>
                        <small class="text-muted">Format KBD Jam 18:00</small>
                      </div>
                      <i class="bi bi-chevron-down ms-2 text-info"></i>
                    </div>
                  </button>
                </h2>
                <div id="khususGuideCollapse" class="accordion-collapse collapse" data-bs-parent="#usageGuideAccordion">
                  <div class="accordion-body p-4" style="background: #ffffff;">
                    <div class="guide-steps">
                      <div class="step-item mb-3 d-flex align-items-start">
                        <div class="step-number me-3" style="width: 32px; height: 32px; background: #0dcaf0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(13, 202, 240, 0.4);">1</div>
                        <div class="flex-grow-1">
                          <p class="mb-0 fw-medium">Pilih jenis laporan <span class="badge bg-info">"Laporan Khusus (Format KBD Jam 18:00)"</span> pada langkah pertama</p>
                        </div>
                      </div>
                      <div class="step-item mb-3 d-flex align-items-start">
                        <div class="step-number me-3" style="width: 32px; height: 32px; background: #0dcaf0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(13, 202, 240, 0.4);">2</div>
                        <div class="flex-grow-1">
                          <p class="mb-0 fw-medium">Masukkan tanggal laporan pada langkah kedua</p>
                        </div>
                      </div>
                      <div class="step-item mb-3 d-flex align-items-start">
                        <div class="step-number me-3" style="width: 32px; height: 32px; background: #0dcaf0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(13, 202, 240, 0.4);">3</div>
                        <div class="flex-grow-1">
                          <p class="mb-2 fw-medium">Input data patrol report dan tema pada langkah ketiga:</p>
                          <div class="code-block p-3 rounded-3 mb-2" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-left: 4px solid #0dcaf0; font-family: 'Courier New', monospace; font-size: 0.9rem;">
                            <code class="text-dark">nama akun<br>link<br>kategori<br>narasi</code>
                          </div>
                          <p class="mb-0 small"><i class="bi bi-arrow-right text-info me-1"></i>Input tema laporan yang akan digunakan dalam perihal dan judul file</p>
                        </div>
                      </div>
                      <div class="step-item mb-3 d-flex align-items-start">
                        <div class="step-number me-3" style="width: 32px; height: 32px; background: #0dcaf0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(13, 202, 240, 0.4);">4</div>
                        <div class="flex-grow-1">
                          <p class="mb-0 fw-medium">Upload file Excel CIPOP dan gambar/screenshot pada langkah keempat</p>
                        </div>
                      </div>
                      <div class="alert alert-info border-0 shadow-sm mb-0" style="background: #d1ecf1; border-left: 4px solid #0dcaf0 !important;">
                        <i class="bi bi-lightbulb-fill me-2"></i><strong>Penting:</strong> Tema yang diinput akan otomatis dimasukkan ke dalam perihal narasi dan judul file Word
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Patroli MBG dan Sore Guide -->
              <div class="accordion-item border-bottom">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#landyGuideCollapse" aria-expanded="false" aria-controls="landyGuideCollapse" style="background: #f0f9f4; border: none; padding: 1.25rem 1.5rem;">
                    <div class="d-flex align-items-center w-100">
                      <div class="guide-icon-box me-3" style="width: 45px; height: 45px; background: #198754; border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 8px rgba(25, 135, 84, 0.4);">
                        <i class="bi bi-shield-check-fill text-white" style="font-size: 20px;"></i>
                      </div>
                      <div class="flex-grow-1">
                        <h6 class="mb-0 fw-bold text-dark">Patroli MBG dan Sore</h6>
                        <small class="text-muted">Laporan patroli dengan profiling akun</small>
                      </div>
                      <i class="bi bi-chevron-down ms-2 text-success"></i>
                    </div>
                  </button>
                </h2>
                <div id="landyGuideCollapse" class="accordion-collapse collapse" data-bs-parent="#usageGuideAccordion">
                  <div class="accordion-body p-4" style="background: #ffffff;">
                    <div class="guide-steps">
                      <div class="step-item mb-3 d-flex align-items-start">
                        <div class="step-number me-3" style="width: 32px; height: 32px; background: #198754; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(25, 135, 84, 0.4);">1</div>
                        <div class="flex-grow-1">
                          <p class="mb-0 fw-medium">Pilih jenis laporan <span class="badge bg-success">"Patroli Landy"</span> pada langkah pertama</p>
                        </div>
                      </div>
                      <div class="step-item mb-3 d-flex align-items-start">
                        <div class="step-number me-3" style="width: 32px; height: 32px; background: linear-gradient(135deg, #198754 0%, #0d6efd 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; flex-shrink: 0; box-shadow: 0 2px 8px rgba(25, 135, 84, 0.3);">2</div>
                        <div class="flex-grow-1">
                          <p class="mb-0 fw-medium">Masukkan tanggal laporan pada langkah kedua</p>
                        </div>
                      </div>
                      <div class="step-item mb-3 d-flex align-items-start">
                        <div class="step-number me-3" style="width: 32px; height: 32px; background: linear-gradient(135deg, #198754 0%, #0d6efd 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; flex-shrink: 0; box-shadow: 0 2px 8px rgba(25, 135, 84, 0.3);">3</div>
                        <div class="flex-grow-1">
                          <p class="mb-2 fw-medium">Input data patrol report pada langkah ketiga sesuai format:</p>
                          <div class="position-relative mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                              <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Isi data sesuai format, data tersimpan otomatis</small>
                              <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-success copy-format-btn" data-target="landy-format" style="border-radius: 8px 0 0 8px;">
                                  <i class="bi bi-clipboard me-1"></i>Copy
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary clear-format-btn" data-target="landy-format" style="border-radius: 0 8px 8px 0;">
                                  <i class="bi bi-trash me-1"></i>Hapus
                                </button>
                              </div>
                            </div>
                            <textarea id="landy-format" class="form-control editable-format" placeholder="Isi data sesuai format di bawah..." style="font-family: 'Courier New', monospace; font-size: 0.85rem; line-height: 1.6; background: #ffffff; border: 2px solid #198754; border-radius: 8px; padding: 12px; min-height: 380px; resize: vertical; cursor: text;" data-storage-key="landy-format-data">nama akun: 
link: 
kategori: 
narasi: 
profiling:
Nama: 
Jenis kelamin: 
Golongan Darah: 
Status Nikah: 
Agama: 
Lahir: 
Umur: 
Tanggal Lahir: 
Pekerjaan: 
Provinsi: 
Kabupaten: 
Kecamatan: 
Kelurahan: 
Kode Pos: 
RT/RW: 
Alamat Lengkap: 
korelasi: (Tidak ditemukan)
afiliasi: (Tidak ditemukan)

nama akun: 
link: 
kategori: 
narasi: 
wilayah: 
korelasi: 
afiliasi: </textarea>
                            <div class="copy-feedback position-absolute top-0 end-0 m-2" style="display: none; background: rgba(40, 167, 69, 0.9); color: white; padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; z-index: 10;">
                              <i class="bi bi-check-circle me-1"></i>Copied!
                            </div>
                            <div class="clear-feedback position-absolute top-0 end-0 m-2" style="display: none; background: rgba(220, 53, 69, 0.9); color: white; padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; z-index: 10;">
                              <i class="bi bi-check-circle me-1"></i>Dihapus!
                            </div>
                          </div>
                          <small class="text-muted d-block mt-2">
                            <i class="bi bi-lightbulb me-1"></i><strong>Tips:</strong> Data yang Anda isi akan tersimpan otomatis dan tidak hilang saat refresh halaman. Gunakan tombol "Hapus" untuk mengosongkan kembali. Bisa input beberapa akun sekaligus, pisahkan dengan baris kosong.
                          </small>
                        </div>
                      </div>
                      <div class="step-item mb-3 d-flex align-items-start">
                        <div class="step-number me-3" style="width: 32px; height: 32px; background: linear-gradient(135deg, #198754 0%, #0d6efd 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; flex-shrink: 0; box-shadow: 0 2px 8px rgba(25, 135, 84, 0.3);">4</div>
                        <div class="flex-grow-1">
                          <p class="mb-0 fw-medium">Upload screenshot patroli dan tangkapan layar RAS pada langkah keempat</p>
                        </div>
                      </div>
                      <div class="alert alert-warning border-0 shadow-sm mb-0" style="background: #fff3cd; border-left: 4px solid #ffc107 !important;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Penting:</strong> Jumlah tangkapan layar RAS harus sama dengan jumlah narasi patroli. Akun tanpa profiling tetap perlu diisi dengan "wilayah: -", "korelasi: -", "afiliasi: -"
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Patroli Pagi Guide -->
              <div class="accordion-item border-bottom d-none" style="display: none !important;">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pagiGuideCollapse" aria-expanded="false" aria-controls="pagiGuideCollapse" style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 152, 0, 0.1) 100%); border: none; padding: 1.25rem 1.5rem;">
                    <div class="d-flex align-items-center w-100">
                      <div class="guide-icon-box me-3" style="width: 45px; height: 45px; background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);">
                        <i class="bi bi-sun-fill text-white" style="font-size: 20px;"></i>
                      </div>
                      <div class="flex-grow-1">
                        <h6 class="mb-0 fw-bold text-dark">Patroli Pagi</h6>
                        <small class="text-muted">Laporan patroli pagi dengan upaya takedown</small>
                      </div>
                      <i class="bi bi-chevron-down ms-2 text-warning"></i>
                    </div>
                  </button>
                </h2>
                <div id="pagiGuideCollapse" class="accordion-collapse collapse" data-bs-parent="#usageGuideAccordion">
                  <div class="accordion-body p-4" style="background: #ffffff;">
                    <div class="guide-steps">
                      <div class="step-item mb-3 d-flex align-items-start">
                        <div class="step-number me-3" style="width: 32px; height: 32px; background: #ffc107; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(255, 193, 7, 0.4);">1</div>
                        <div class="flex-grow-1">
                          <p class="mb-0 fw-medium">Pilih jenis laporan <span class="badge bg-warning text-dark">"Patroli Pagi"</span> pada langkah pertama</p>
                        </div>
                      </div>
                      <div class="step-item mb-3 d-flex align-items-start">
                        <div class="step-number me-3" style="width: 32px; height: 32px; background: #ffc107; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(255, 193, 7, 0.4);">2</div>
                        <div class="flex-grow-1">
                          <p class="mb-0 fw-medium">Masukkan tanggal laporan pada langkah kedua</p>
                        </div>
                      </div>
                      <div class="step-item mb-3 d-flex align-items-start">
                        <div class="step-number me-3" style="width: 32px; height: 32px; background: #ffc107; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(255, 193, 7, 0.4);">3</div>
                        <div class="flex-grow-1">
                          <p class="mb-2 fw-medium">Input data patrol report dan upaya pada langkah ketiga:</p>
                          <div class="mb-3">
                            <p class="mb-1 small fw-semibold text-muted">Format patrol report:</p>
                            <div class="code-block p-3 rounded-3 mb-2" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-left: 4px solid #ffc107; font-family: 'Courier New', monospace; font-size: 0.9rem;">
                              <code class="text-dark">nama akun<br>link<br>kategori<br>narasi</code>
                            </div>
                          </div>
                          <div>
                            <p class="mb-1 small fw-semibold text-muted">Format upaya patroli pagi:</p>
                            <div class="code-block p-3 rounded-3" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-left: 4px solid #ff9800; font-family: 'Courier New', monospace; font-size: 0.9rem;">
                              <code class="text-dark">nama akun<br>link<br>narasi</code>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="step-item mb-0 d-flex align-items-start">
                        <div class="step-number me-3" style="width: 32px; height: 32px; background: #ffc107; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(255, 193, 7, 0.4);">4</div>
                        <div class="flex-grow-1">
                          <p class="mb-0 fw-medium">Upload screenshot patroli dan gambar upaya takedown pada langkah keempat</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Patroli Umum Guide -->
              <div class="accordion-item border-bottom">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#bencanaGuideCollapse" aria-expanded="false" aria-controls="bencanaGuideCollapse" style="background: #fff0f0; border: none; padding: 1.25rem 1.5rem;">
                    <div class="d-flex align-items-center w-100">
                      <div class="guide-icon-box me-3" style="width: 45px; height: 45px; background: #dc3545; border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 8px rgba(220, 53, 69, 0.4);">
                        <i class="bi bi-exclamation-triangle-fill text-white" style="font-size: 20px;"></i>
                      </div>
                      <div class="flex-grow-1">
                        <h6 class="mb-0 fw-bold text-dark">Patroli Umum</h6>
                        <small class="text-muted">Laporan patroli umum dengan format khusus</small>
                      </div>
                      <i class="bi bi-chevron-down ms-2 text-danger"></i>
                    </div>
                  </button>
                </h2>
                <div id="bencanaGuideCollapse" class="accordion-collapse collapse" data-bs-parent="#usageGuideAccordion">
                  <div class="accordion-body p-4" style="background: #ffffff;">
                    <div class="guide-steps">
                      <div class="step-item mb-3 d-flex align-items-start">
                        <div class="step-number me-3" style="width: 32px; height: 32px; background: #dc3545; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(220, 53, 69, 0.4);">1</div>
                        <div class="flex-grow-1">
                          <p class="mb-0 fw-medium">Pilih jenis laporan <span class="badge bg-danger">"Patroli Umum"</span> pada langkah pertama</p>
                        </div>
                      </div>
                      <div class="step-item mb-3 d-flex align-items-start">
                        <div class="step-number me-3" style="width: 32px; height: 32px; background: #dc3545; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(220, 53, 69, 0.4);">2</div>
                        <div class="flex-grow-1">
                          <p class="mb-0 fw-medium">Masukkan tanggal laporan pada langkah kedua</p>
                        </div>
                      </div>
                      <div class="step-item mb-3 d-flex align-items-start">
                        <div class="step-number me-3" style="width: 32px; height: 32px; background: #dc3545; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(220, 53, 69, 0.4);">3</div>
                        <div class="flex-grow-1">
                          <p class="mb-2 fw-medium">Input data patrol report pada langkah ketiga sesuai format:</p>
                          <div class="position-relative mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                              <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Isi data sesuai format, data tersimpan otomatis</small>
                              <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-danger copy-format-btn" data-target="bencana-format" style="border-radius: 8px 0 0 8px;">
                                  <i class="bi bi-clipboard me-1"></i>Copy
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary clear-format-btn" data-target="bencana-format" style="border-radius: 0 8px 8px 0;">
                                  <i class="bi bi-trash me-1"></i>Hapus
                                </button>
                              </div>
                            </div>
                            <textarea id="bencana-format" class="form-control editable-format" placeholder="Isi data sesuai format di bawah..." style="font-family: 'Courier New', monospace; font-size: 0.85rem; line-height: 1.6; background: #ffffff; border: 2px solid #dc3545; border-radius: 8px; padding: 12px; min-height: 320px; resize: vertical; cursor: text;" data-storage-key="bencana-format-data">nama akun: 
link: 
kategori: 
narasi: 
profiling:
Nik:
KK:
Nama: 
Jenis kelamin: 
Golongan Darah: 
Status Nikah: 
Agama: 
Lahir: 
Umur: 
Tanggal Lahir: 
Pekerjaan: 
Provinsi: 
Kabupaten: 
Kecamatan: 
Kelurahan: 
Kode Pos: 
RT/RW: 
Alamat Lengkap: 
korelasi: (Tidak ditemukan)
afiliasi: (Tidak ditemukan)</textarea>
                            <div class="copy-feedback position-absolute top-0 end-0 m-2" style="display: none; background: rgba(40, 167, 69, 0.9); color: white; padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; z-index: 10;">
                              <i class="bi bi-check-circle me-1"></i>Copied!
                            </div>
                            <div class="clear-feedback position-absolute top-0 end-0 m-2" style="display: none; background: rgba(220, 53, 69, 0.9); color: white; padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; z-index: 10;">
                              <i class="bi bi-check-circle me-1"></i>Dihapus!
                            </div>
                          </div>
                          <small class="text-muted d-block mt-2">
                            <i class="bi bi-lightbulb me-1"></i><strong>Tips:</strong> Data yang Anda isi akan tersimpan otomatis dan tidak hilang saat refresh halaman. Gunakan tombol "Hapus" untuk mengosongkan kembali.
                          </small>
                        </div>
                      </div>
                      <div class="step-item mb-3 d-flex align-items-start">
                        <div class="step-number me-3" style="width: 32px; height: 32px; background: #dc3545; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; flex-shrink: 0; box-shadow: 0 2px 6px rgba(220, 53, 69, 0.4);">4</div>
                        <div class="flex-grow-1">
                          <p class="mb-0 fw-medium">Upload screenshot patroli dan tangkapan layar RAS pada langkah keempat</p>
                        </div>
                      </div>
                      <div class="alert alert-warning border-0 shadow-sm mb-0" style="background: #fff3cd; border-left: 4px solid #ffc107 !important;">
                        <i class="bi bi-info-circle-fill me-2"></i><strong>Penting:</strong> Format laporan WhatsApp menggunakan header khusus dengan tembusan ke Kasuari-21 hingga Kasuari-63
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Troubleshooting Tips -->
              <div class="accordion-item">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#tipsCollapse" aria-expanded="false" aria-controls="tipsCollapse" style="background: linear-gradient(135deg, rgba(255, 77, 77, 0.1) 0%, rgba(255, 152, 0, 0.1) 100%); border: none; padding: 1.25rem 1.5rem;">
                    <div class="d-flex align-items-center w-100">
                      <div class="guide-icon-box me-3" style="width: 45px; height: 45px; background: linear-gradient(135deg, #ff4d4d 0%, #ff9800 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(255, 77, 77, 0.3);">
                        <i class="bi bi-lightbulb-fill text-white" style="font-size: 20px;"></i>
                      </div>
                      <div class="flex-grow-1">
                        <h6 class="mb-0 fw-bold text-dark">Tips & Troubleshooting</h6>
                        <small class="text-muted">Panduan mengatasi masalah umum</small>
                      </div>
                      <i class="bi bi-chevron-down ms-2 text-danger"></i>
                    </div>
                  </button>
                </h2>
                <div id="tipsCollapse" class="accordion-collapse collapse" data-bs-parent="#usageGuideAccordion">
                  <div class="accordion-body p-4" style="background: #ffffff;">
                    <div class="tips-list">
                      <div class="tip-item mb-3 d-flex align-items-start p-3 rounded-3" style="background: white; border-left: 4px solid #0d6efd; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <i class="bi bi-check-circle-fill text-primary me-3 mt-1" style="font-size: 20px;"></i>
                        <div class="flex-grow-1">
                          <p class="mb-0 fw-medium">Pastikan format input teks sesuai contoh dan setiap data dipisahkan dengan baris kosong</p>
                        </div>
                      </div>
                      <div class="tip-item mb-3 d-flex align-items-start p-3 rounded-3" style="background: white; border-left: 4px solid #0dcaf0; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <i class="bi bi-info-circle-fill text-info me-3 mt-1" style="font-size: 20px;"></i>
                        <div class="flex-grow-1">
                          <p class="mb-0 fw-medium">Fitur tangkapan layar otomatis hanya berfungsi untuk link publik (tidak private/tertutup)</p>
                        </div>
                      </div>
                      <div class="tip-item mb-3 d-flex align-items-start p-3 rounded-3" style="background: white; border-left: 4px solid #198754; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <i class="bi bi-facebook text-success me-3 mt-1" style="font-size: 20px;"></i>
                        <div class="flex-grow-1">
                          <p class="mb-0 fw-medium">Untuk Facebook, gunakan link postingan yang dapat diakses publik (bukan group tertutup)</p>
                        </div>
                      </div>
                      <div class="tip-item mb-3 d-flex align-items-start p-3 rounded-3" style="background: white; border-left: 4px solid #ffc107; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-3 mt-1" style="font-size: 20px;"></i>
                        <div class="flex-grow-1">
                          <p class="mb-0 fw-medium">Platform TikTok: <span class="badge bg-warning text-dark">belum didukung</span> otomatis screenshot</p>
                        </div>
                      </div>
                      <div class="tip-item mb-3 d-flex align-items-start p-3 rounded-3" style="background: white; border-left: 4px solid #dc3545; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <i class="bi bi-shield-exclamation-fill text-danger me-3 mt-1" style="font-size: 20px;"></i>
                        <div class="flex-grow-1">
                          <p class="mb-0 fw-medium">Jika proses gagal, periksa kembali format input dan coba upload file dalam jumlah lebih sedikit</p>
                        </div>
                      </div>
                      <div class="tip-item mb-0 d-flex align-items-start p-3 rounded-3" style="background: white; border-left: 4px solid #0d6efd; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <i class="bi bi-file-earmark-check-fill text-primary me-3 mt-1" style="font-size: 20px; color: #0d6efd;"></i>
                        <div class="flex-grow-1">
                          <p class="mb-0 fw-medium">File hasil laporan akan tersedia di kolom hasil setelah proses selesai 100%</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
<!--end main wrapper-->

<?php
// Include the footer
include_once('includes/footer.php');

// Include JavaScript files
include_once('includes/js-includes.php');
?>

<script>
// Copy format functionality
function showCopyFeedback(element) {
  const feedback = element.parentElement.querySelector('.copy-feedback');
  if (feedback) {
    feedback.style.display = 'block';
    setTimeout(() => {
      feedback.style.display = 'none';
    }, 2000);
  }
}

// Clear format functionality
function showClearFeedback(element) {
  const feedback = element.parentElement.querySelector('.clear-feedback');
  if (feedback) {
    feedback.style.display = 'block';
    setTimeout(() => {
      feedback.style.display = 'none';
    }, 2000);
  }
}

// Save to localStorage
function saveToLocalStorage(key, value) {
  try {
    localStorage.setItem(key, value);
  } catch (e) {
    console.error('Failed to save to localStorage:', e);
  }
}

// Load from localStorage
function loadFromLocalStorage(key) {
  try {
    return localStorage.getItem(key);
  } catch (e) {
    console.error('Failed to load from localStorage:', e);
    return null;
  }
}

// Get default format
function getDefaultFormat(textareaId) {
  if (textareaId === 'bencana-format') {
    return `nama akun: 
link: 
kategori: 
narasi: 
profiling:
Nik:
KK:
Nama: 
Jenis kelamin: 
Golongan Darah: 
Status Nikah: 
Agama: 
Lahir: 
Umur: 
Tanggal Lahir: 
Pekerjaan: 
Provinsi: 
Kabupaten: 
Kecamatan: 
Kelurahan: 
Kode Pos: 
RT/RW: 
Alamat Lengkap: 
korelasi: (Tidak ditemukan)
afiliasi: (Tidak ditemukan)`;
  } else if (textareaId === 'landy-format') {
    return `nama akun: 
link: 
kategori: 
narasi: 
profiling:
Nama: 
Jenis kelamin: 
Golongan Darah: 
Status Nikah: 
Agama: 
Lahir: 
Umur: 
Tanggal Lahir: 
Pekerjaan: 
Provinsi: 
Kabupaten: 
Kecamatan: 
Kelurahan: 
Kode Pos: 
RT/RW: 
Alamat Lengkap: 
korelasi: (Tidak ditemukan)
afiliasi: (Tidak ditemukan)

nama akun: 
link: 
kategori: 
narasi: 
wilayah: 
korelasi: 
afiliasi: `;
  } else if (textareaId === 'kbd-format') {
    return `nama akun: 
link: 
kategori: 
narasi: `;
  }
  return '';
}

// Copy button functionality
document.addEventListener('DOMContentLoaded', function() {
  // Load saved data for editable textareas
  const editableTextareas = document.querySelectorAll('.editable-format');
  editableTextareas.forEach(textarea => {
    const storageKey = textarea.getAttribute('data-storage-key');
    if (storageKey) {
      const savedData = loadFromLocalStorage(storageKey);
      if (savedData && savedData.trim() !== '') {
        textarea.value = savedData;
      }
      
      // Auto-save on input
      textarea.addEventListener('input', function() {
        saveToLocalStorage(storageKey, this.value);
      });
    }
  });
  
  // Copy button functionality
  const copyButtons = document.querySelectorAll('.copy-format-btn');
  copyButtons.forEach(button => {
    button.addEventListener('click', function() {
      const targetId = this.getAttribute('data-target');
      const textarea = document.getElementById(targetId);
      if (textarea) {
        textarea.select();
        textarea.setSelectionRange(0, 99999); // For mobile devices
        try {
          document.execCommand('copy');
          showCopyFeedback(textarea);
          // Change button text temporarily
          const originalHTML = this.innerHTML;
          this.innerHTML = '<i class="bi bi-check-circle me-1"></i>Copied!';
          this.classList.remove('btn-outline-danger', 'btn-outline-success', 'btn-outline-primary');
          this.classList.add('btn-success');
          setTimeout(() => {
            this.innerHTML = originalHTML;
            this.classList.remove('btn-success');
            if (targetId === 'bencana-format') {
              this.classList.add('btn-outline-danger');
            } else if (targetId === 'landy-format') {
              this.classList.add('btn-outline-success');
            } else if (targetId === 'kbd-format') {
              this.classList.add('btn-outline-primary');
            }
          }, 2000);
        } catch (err) {
          console.error('Failed to copy:', err);
          // Fallback: use Clipboard API
          if (navigator.clipboard) {
            navigator.clipboard.writeText(textarea.value).then(() => {
              showCopyFeedback(textarea);
            });
          }
        }
      }
    });
  });
  
  // Clear button functionality
  const clearButtons = document.querySelectorAll('.clear-format-btn');
  clearButtons.forEach(button => {
    button.addEventListener('click', function() {
      const targetId = this.getAttribute('data-target');
      const textarea = document.getElementById(targetId);
      if (textarea) {
        const storageKey = textarea.getAttribute('data-storage-key');
        const defaultFormat = getDefaultFormat(targetId);
        
        // Clear textarea
        textarea.value = defaultFormat;
        
        // Clear localStorage
        if (storageKey) {
          saveToLocalStorage(storageKey, defaultFormat);
        }
        
        // Show feedback
        showClearFeedback(textarea);
        
        // Change button text temporarily
        const originalHTML = this.innerHTML;
        this.innerHTML = '<i class="bi bi-check-circle me-1"></i>Dihapus!';
        this.classList.remove('btn-outline-secondary');
        this.classList.add('btn-danger');
        setTimeout(() => {
          this.innerHTML = originalHTML;
          this.classList.remove('btn-danger');
          this.classList.add('btn-outline-secondary');
        }, 2000);
      }
    });
  });
});
</script>

