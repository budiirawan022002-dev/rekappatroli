<?php
include_once('includes/header.php');
?>

<main class="main-wrapper">
  <div class="main-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Rekap Spreadsheet Danantara</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Data Rekap Danantara</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <!-- Main Content Row -->
    <div class="row">
      <!-- Left Column: Filter and Topik -->
      <div class="col-12 col-lg-2">
        <!-- Topik Section -->
        <div class="card mb-4">
          <div class="card-body">
            <h5 class="card-title mb-3">
              <i class="bi bi-bookmark-fill"></i>
              <span id="topikTitle">Loading...</span>
            </h5>
          </div>
        </div>

        <!-- Filter Section -->
        <div class="card rounded-2">
          <div class="card-body">
            <h5 class="mb-3 fw-bold">Filter Data</h5>
            <div class="mb-3">
              <label class="form-label">Platform</label>
              <select class="form-select" id="platformFilter">
                <option value="all">Semua Platform</option>
                <option value="FACEBOOK">Facebook</option>
                <option value="INSTAGRAM">Instagram</option>
                <option value="TWITTER">Twitter</option>
                <option value="SNACKVIDEO">Snack Video</option>
                <option value="YOUTUBE">YouTube</option>
                <option value="TIKTOK">TikTok</option>
              </select>
            </div>
            <div class="mt-3">
              <div class="row g-2">
                <div class="col-12">
                  <button class="btn btn-outline-primary w-100" id="refreshData">
                    <i class="bx bx-refresh me-1"></i> Refresh Data
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Middle Column: Narasi dari Excel -->
      <div class="col-12 col-lg-4">
        <div class="card">
          <div class="card-body">
            <div class="narasi-container">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <h5 class="mb-0">
                  <i class="bi bi-file-earmark-text me-1"></i>
                  Narasi dari Excel
                </h5>
                <button class="btn btn-sm btn-outline-primary copy-btn" data-narasi="excel">
                  <i class="bi bi-clipboard me-1"></i> Salin
                </button>
              </div>
              <div class="narasi-content">
                <div id="narasiExcel" class="mb-0">Loading...</div>
              </div>
              <!-- Buttons moved here -->
              <div class="mt-3">
                <div class="row g-2">
                  <div class="col-12">
                    <a href="https://docs.google.com/spreadsheets/d/1ak9KQjBmNZxNxPns65c9eArWSIC84WBw5WogbIja_1o/edit"
                      target="_blank"
                      class="btn btn-success w-100">
                      <i class="bi bi-file-earmark-spreadsheet me-2"></i>
                      Buka Google Spreadsheet
                    </a>
                  </div>
                  <div class="col-12" id="excelDownloadContainer" style="display: none;">
                    <a id="excelDownloadLink" href="#" target="_blank" class="btn btn-primary w-100">
                      <i class="bi bi-download me-2"></i>
                      Download Excel File
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Data Display Section -->
      <div class="col-12 col-lg-6">
        <!-- Akun Induk Stats -->
        <div class="card rounded-2 mb-3">
          <div class="card-body">
            <h6 class="mb-3">
              <i class="bi bi-person-circle me-2"></i>
              Data Akun Induk
            </h6>
            <div id="akunIndukStats" class="row g-2"></div>
          </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-3">
          <div class="col-12 col-md-6">
            <div class="card rounded-2">
              <div class="card-body">
                <h6 class="mb-2">Total Link Keseluruhan</h6>
                <h4 class="mb-0" id="totalLinks">0</h4>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="card rounded-2">
              <div class="card-body">
                <h6 class="mb-2">Total Link per Platform</h6>
                <div id="platformStats" class="d-flex flex-wrap gap-2"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Data Display -->
        <div class="card rounded-2">
          <div class="card-body">
            <div id="loadingIndicator" class="text-center py-5" style="display: none;">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <div class="mt-2">Memuat data...</div>
            </div>
            <div id="dataContainer" class="accordion"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
  <div id="copyToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">
        <i class="bi bi-check-circle me-2"></i>
        <span id="toastMessage"></span>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

<!-- Custom CSS -->
<style>
  .link-item {
    border-left: 3px solid #0d6efd;
    margin-bottom: 10px;
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 4px;
  }

  .link-item.duplicate-link {
    border-left-color: #dc3545;
    background-color: #fff5f5;
  }

  .duplicate-info {
    font-size: 0.85rem;
    color: #dc3545;
  }

  .platform-section {
    margin-bottom: 20px;
  }

  .links-container {
    margin-left: 0px;
    max-height: 300px;
    overflow-y: auto;
    padding: 10px;
    border-radius: 4px;
    background-color: #fff;
    border: 1px solid #dee2e6;
  }

  .accordion {
    border-radius: 0;
  }

  .accordion-button {
    padding: 1rem;
    background-color: transparent;
    border: none;
  }

  .accordion-button:not(.collapsed) {
    background-color: transparent;
    color: #0d6efd;
  }

  .accordion-button:focus {
    box-shadow: none;
    background-color: transparent;
  }

  .accordion-button::after {
    margin-left: auto;
  }

  .accordion-body {
    padding: 1rem;
    background-color: transparent;
  }

  .badge {
    font-weight: 500;
    font-size: 0.85rem;
  }

  .stat-badge {
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.875rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background-color: #e9ecef;
  }

  .platform-stat {
    background-color: #0d6efd;
    color: white;
  }

  .akun-induk-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }

  .akun-induk-card h6 {
    color: white;
    font-weight: 600;
    margin-bottom: 10px;
  }

  .akun-induk-detail {
    background: rgba(255,255,255,0.1);
    border-radius: 4px;
    padding: 8px;
    margin-bottom: 5px;
    font-size: 0.85rem;
  }

  .akun-induk-detail strong {
    color: #fff;
  }

  .akun-induk-link {
    color: #e3f2fd !important;
    text-decoration: none;
  }

  .akun-induk-link:hover {
    color: #ffffff !important;
    text-decoration: underline;
  }

  .narasi-container {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
  }

  .narasi-content {
    max-height: 400px;
    overflow-y: auto;
    background-color: #fff;
    padding: 15px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
    margin-bottom: 0;
  }

  .narasi-content div {
    margin: 0;
    white-space: pre-line;
    color: #495057;
    font-size: 0.9rem;
    line-height: 1.6;
    transition: opacity 0.3s ease-in-out;
  }

  .narasi-item {
    margin-bottom: 10px;
    padding: 8px;
    background-color: #f8f9fa;
    border-radius: 4px;
    border-left: 3px solid #0d6efd;
  }

  /* Custom scrollbar untuk narasi content */
  .narasi-content::-webkit-scrollbar {
    width: 6px;
  }

  .narasi-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
  }

  .narasi-content::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
  }

  .narasi-content::-webkit-scrollbar-thumb:hover {
    background: #555;
  }

  .copy-btn {
    font-size: 0.875rem;
    padding: 6px 12px;
    transition: all 0.2s ease-in-out;
  }

  .copy-btn:hover {
    background-color: #0d6efd;
    color: white;
    transform: translateY(-1px);
  }

  .copy-btn i {
    font-size: 0.875rem;
  }

  .toast {
    opacity: 0;
    transition: opacity 0.2s ease-in-out;
  }

  .toast.show {
    opacity: 1;
  }

  .toast-container {
    z-index: 1050;
  }

  @media (max-width: 991.98px) {
    .narasi-content {
      max-height: 250px;
    }
  }
</style>

<?php include_once('includes/js-includes.php'); ?>

<!-- Custom JavaScript -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const platformFilter = document.getElementById('platformFilter');
    const dataContainer = document.getElementById('dataContainer');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const refreshButton = document.getElementById('refreshData');
    
    let globalData = null;

    // Initialize toast
    const copyToast = new bootstrap.Toast(document.getElementById('copyToast'), {
      animation: true,
      autohide: true,
      delay: 3000
    });

    // Function to show toast message
    function showToast(message) {
      document.getElementById('toastMessage').textContent = message;
      copyToast.show();
    }

    // Copy function
    async function copyText(text, buttonText) {
      try {
        await navigator.clipboard.writeText(text);
        return true;
      } catch (err) {
        console.error('Clipboard API failed:', err);
        // Fallback
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();

        try {
          document.execCommand('copy');
          document.body.removeChild(textarea);
          return true;
        } catch (err) {
          document.body.removeChild(textarea);
          console.error('Fallback failed:', err);
          return false;
        }
      }
    }

    // Copy button click handler
    document.querySelectorAll('.copy-btn').forEach(button => {
      button.addEventListener('click', async function(e) {
        e.preventDefault();
        e.stopPropagation();

        const type = this.getAttribute('data-narasi');
        const narasiElement = document.getElementById('narasiExcel');
        const text = narasiElement.textContent;
        const buttonText = 'Narasi dari Excel';

        if (text === 'Loading...' || text.includes('tidak tersedia')) {
          showToast(`${buttonText} belum tersedia untuk disalin`);
          return;
        }

        const success = await copyText(text, buttonText);
        if (success) {
          // Update button appearance
          const originalHTML = this.innerHTML;
          this.innerHTML = '<i class="bi bi-check2 me-1"></i> Tersalin!';
          this.classList.remove('btn-outline-primary');
          this.classList.add('btn-success');

          showToast(`${buttonText} berhasil disalin ke clipboard`);

          setTimeout(() => {
            this.innerHTML = originalHTML;
            this.classList.remove('btn-success');
            this.classList.add('btn-outline-primary');
          }, 2000);
        } else {
          showToast('Gagal menyalin teks. Silakan coba lagi.');
        }
      });
    });

    // Fungsi untuk memuat data
    async function loadData() {
      try {
        // Show loading state
        loadingIndicator.style.display = 'block';
        dataContainer.style.display = 'none';
        
        // Set loading state untuk topik dan narasi
        document.getElementById('topikTitle').textContent = 'Loading...';
        document.getElementById('narasiExcel').textContent = 'Loading...';

        const response = await fetch('api_rekap_danantara.php');
        const data = await response.json();

        if (data.status === 'success') {
          globalData = data.data;
          displayFilteredData();

          // Update topik
          const topikElement = document.getElementById('topikTitle');
          const topik = data.data.topik || 'Topik tidak tersedia';
          topikElement.textContent = topik;

          // Update narasi dari Excel
          const narasiElement = document.getElementById('narasiExcel');
          const narasiText = data.data.narasi_excel || '';
          
          if (narasiText && narasiText.trim() !== '') {
            narasiElement.style.opacity = '0';
            
            setTimeout(() => {
              // Display narasi as single text with preserved line breaks
              narasiElement.innerHTML = `<div style="white-space: pre-line;">${narasiText}</div>`;
              narasiElement.style.transition = 'opacity 0.3s ease-in';
              narasiElement.style.opacity = '1';
            }, 200);
          } else {
            narasiElement.textContent = 'Narasi dari Excel tidak tersedia';
          }

          // Update akun induk stats
          updateAkunIndukStats(data.data.akun_induk || {});

          // Show Excel download link if available
          if (data.excel_file && data.excel_file.status === 'success') {
            const excelContainer = document.getElementById('excelDownloadContainer');
            const excelLink = document.getElementById('excelDownloadLink');
            excelLink.href = data.excel_file.path;
            excelLink.innerHTML = `
              <i class="bi bi-download me-2"></i>
              Download ${data.excel_file.filename}
            `;
            excelContainer.style.display = 'block';
            
            showToast(`Excel file berhasil dibuat: ${data.excel_file.filename}`);
          }
        } else {
          throw new Error(data.message || 'Terjadi kesalahan saat memuat data');
        }
      } catch (error) {
        console.error('Error:', error);
        dataContainer.innerHTML = `
          <div class="alert alert-danger">
            Error: ${error.message}
          </div>
        `;
      } finally {
        loadingIndicator.style.display = 'none';
        dataContainer.style.display = 'block';
      }
    }

    // Fungsi untuk menampilkan data berdasarkan filter
    function calculateStats(data) {
      const stats = {
        total: 0,
        platforms: {}
      };

      // Loop through each platform (skip akun_induk, topik, and platform_akun data)
      Object.entries(data).forEach(([platform, links]) => {
        if (['akun_induk', 'topik'].includes(platform) || platform.endsWith('_akun')) return;
        
        if (Array.isArray(links)) {
          stats.platforms[platform] = links.length;
          stats.total += links.length;
        } else if (typeof links === 'string') {
          // Handle error messages
          stats.platforms[platform] = 0;
        }
      });

      return stats;
    }

    // Fungsi untuk menghitung duplikat link
    function countDuplicateLinks(data) {
      const linkCounts = new Map();
      
      // Mengumpulkan semua link dari semua platform
      Object.entries(data).forEach(([platform, links]) => {
        if (['akun_induk', 'topik'].includes(platform) || platform.endsWith('_akun')) return;
        
        if (Array.isArray(links)) {
          links.forEach(link => {
            if (link && typeof link === 'string') {
              linkCounts.set(link, (linkCounts.get(link) || 0) + 1);
            }
          });
        }
      });
      
      return linkCounts;
    }

    function updateStats(stats) {
      // Update total links with formatting
      const totalLinksElement = document.getElementById('totalLinks');
      totalLinksElement.innerHTML = `
        <div class="d-flex align-items-baseline">
          <i class="bi bi-link-45deg fs-3 text-primary me-2"></i>
          <span class="display-6 fw-bold text-primary">${stats.total.toLocaleString()}</span>
          <span class="ms-2 text-muted">Link</span>
        </div>
      `;

      // Update platform stats sorted by count
      const platformStatsHtml = Object.entries(stats.platforms)
        .sort(([_a, countA], [_b, countB]) => countB - countA)
        .map(([platform, count]) => `
          <div class="stat-badge platform-stat">
            <i class="bi ${getPlatformIcon(platform)}"></i>
            <span class="fw-bold">${platform}</span>
            <span class="ms-1">${count.toLocaleString()}</span>
          </div>
        `).join('') || '<div class="text-muted">Tidak ada data</div>';
      document.getElementById('platformStats').innerHTML = platformStatsHtml;
    }

    function updateAkunIndukStats(akunIndukData) {
      const akunIndukElement = document.getElementById('akunIndukStats');
      
      if (!akunIndukData || Object.keys(akunIndukData).length === 0) {
        akunIndukElement.innerHTML = '<div class="col-12"><div class="text-muted">Data akun induk tidak tersedia</div></div>';
        return;
      }

      let akunIndukHtml = '';
      
      Object.entries(akunIndukData).forEach(([platform, data]) => {
        if (data.error) {
          akunIndukHtml += `
            <div class="col-12 col-md-6 col-lg-4">
              <div class="akun-induk-card">
                <h6><i class="bi ${getPlatformIcon(platform)} me-2"></i>${platform}</h6>
                <div class="alert alert-warning mb-0">${data.error}</div>
              </div>
            </div>
          `;
        } else {
          akunIndukHtml += `
            <div class="col-12 col-md-6 col-lg-4">
              <div class="akun-induk-card">
                <h6><i class="bi ${getPlatformIcon(platform)} me-2"></i>${platform}</h6>
                <div class="akun-induk-detail">
                  <strong>Akun:</strong> ${data.nama_akun || 'N/A'}
                </div>
                ${data.link_postingan ? `
                <div class="akun-induk-detail">
                  <strong>Link:</strong> 
                  <a href="${data.link_postingan}" target="_blank" class="akun-induk-link">
                    ${data.link_postingan.length > 30 ? data.link_postingan.substring(0, 30) + '...' : data.link_postingan}
                  </a>
                </div>
                ` : ''}
                <div class="d-flex justify-content-between mt-2">
                  ${data.like ? `<small><i class="bi bi-heart-fill me-1"></i>${data.like}</small>` : ''}
                  ${data.comments ? `<small><i class="bi bi-chat-fill me-1"></i>${data.comments}</small>` : ''}
                  ${data.share ? `<small><i class="bi bi-share-fill me-1"></i>${data.share}</small>` : ''}
                  ${data.retweets ? `<small><i class="bi bi-repeat me-1"></i>${data.retweets}</small>` : ''}
                </div>
              </div>
            </div>
          `;
        }
      });
      
      akunIndukElement.innerHTML = akunIndukHtml;
    }

    function displayFilteredData() {
      if (!globalData) return;

      const selectedPlatform = platformFilter.value;

      // Calculate and update stats
      const stats = calculateStats(globalData);
      updateStats(stats);

      // Create accordion
      let accordionHtml = '<div class="accordion accordion-flush" id="platformAccordion">';

      Object.entries(globalData).forEach(([platform, links], platformIndex) => {
        if (['akun_induk', 'topik'].includes(platform) || platform.endsWith('_akun')) return;
        if (selectedPlatform !== 'all' && platform !== selectedPlatform) return;

        const duplicates = countDuplicateLinks(globalData);
        const { hasDuplicates, duplicateCount } = checkDuplicatesInPlatform(platform, links, duplicates);

        // Platform Accordion Item
        accordionHtml += `
          <div class="accordion-item">
            <h2 class="accordion-header" id="heading${platformIndex}">
              <button class="accordion-button collapsed" type="button" 
                      data-bs-toggle="collapse" 
                      data-bs-target="#flush-platform${platformIndex}" 
                      aria-expanded="false" 
                      aria-controls="flush-platform${platformIndex}">
                <div class="d-flex align-items-center gap-2 flex-grow-1">
                  <i class="bi ${getPlatformIcon(platform)} me-2"></i>
                  <strong>${platform}</strong>
                  <span class="badge bg-primary rounded-pill">
                    <i class="bi bi-link-45deg me-1"></i>
                    ${Array.isArray(links) ? links.length : 0} link
                  </span>
                  ${hasDuplicates ? `
                  <span class="badge bg-danger rounded-pill">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    ${duplicateCount} link duplikat
                  </span>
                  ` : ''}
                </div>
              </button>
            </h2>
            <div id="flush-platform${platformIndex}" 
                 class="accordion-collapse collapse" 
                 aria-labelledby="heading${platformIndex}" 
                 data-bs-parent="#platformAccordion">
              <div class="accordion-body">
                <div class="links-container">
        `;

        // Get corresponding nama akun data
        const namaAkunKey = platform + '_akun';
        const namaAkunData = globalData[namaAkunKey];

        // Display links with corresponding nama akun
        if (Array.isArray(links)) {
          links.forEach((link, index) => {
            if (link && typeof link === 'string') {
              const duplicateCount = duplicates.get(link);
              const isDuplicate = duplicateCount > 1;
              
              // Get corresponding nama akun if available
              let namaAkun = '';
              if (Array.isArray(namaAkunData) && namaAkunData[index]) {
                namaAkun = namaAkunData[index];
              }
              
              accordionHtml += `
                <div class="link-item ${isDuplicate ? 'duplicate-link' : ''}">
                  ${namaAkun ? `<div class="mb-2"><strong>Akun:</strong> ${namaAkun}</div>` : ''}
                  <a href="${link}" target="_blank" class="text-break ${isDuplicate ? 'text-danger' : ''}">
                    <i class="bi ${isDuplicate ? 'bi-exclamation-triangle-fill text-danger' : 'bi-link-45deg'} me-2"></i>
                    ${link}
                  </a>
                  ${isDuplicate ? `
                    <div class="duplicate-info mt-1">
                      <small class="text-danger">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        Link ini muncul ${duplicateCount} kali
                      </small>
                    </div>
                  ` : ''}
                </div>
              `;
            }
          });
        } else {
          accordionHtml += `<div class="alert alert-warning">${links}</div>`;
        }

        accordionHtml += `
                </div>
              </div>
            </div>
          </div>
        `;
      });

      accordionHtml += '</div>';

      // Set the HTML content
      dataContainer.innerHTML = accordionHtml || '<div class="alert alert-info">Tidak ada data yang sesuai dengan filter</div>';
    }

    // Fungsi untuk memeriksa duplikat dalam platform tertentu
    function checkDuplicatesInPlatform(platform, links, allDuplicates) {
      let hasDuplicates = false;
      let duplicateCount = 0;

      if (Array.isArray(links)) {
        links.forEach(link => {
          if (link && typeof link === 'string' && allDuplicates.get(link) > 1) {
            hasDuplicates = true;
            duplicateCount++;
          }
        });
      }

      return { hasDuplicates, duplicateCount };
    }

    // Fungsi untuk mendapatkan ikon berdasarkan platform
    function getPlatformIcon(platform) {
      const icons = {
        'FACEBOOK': 'bi-facebook',
        'INSTAGRAM': 'bi-instagram',
        'TWITTER': 'bi-twitter',
        'SNACKVIDEO': 'bi-play-circle-fill',
        'YOUTUBE': 'bi-youtube',
        'TIKTOK': 'bi-tiktok'
      };
      return icons[platform] || 'bi-globe';
    }

    // Event listeners
    platformFilter.addEventListener('change', displayFilteredData);
    refreshButton.addEventListener('click', loadData);

    // Load initial data
    loadData();
  });
</script>

<?php
include_once('includes/footer.php');
?>