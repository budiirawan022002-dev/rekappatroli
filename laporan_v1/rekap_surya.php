<?php
include_once('includes/header.php');
?>

<main class="main-wrapper">
  <div class="main-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">Rekap Spreadsheet Surya</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Data Rekap</li>
          </ol>
        </nav>
      </div>
    </div>
    <!--end breadcrumb-->

    <!-- Main Content Row -->
    <div class="row">
      <!-- Left Column: Tanggal dan Filter -->
      <div class="col-12 col-lg-3">
        <!-- Tanggal dan Narasi -->
        <div class="card mb-4">
          <div class="card-body">
            <h5 class="card-title mb-3">
              <i class="bi bi-calendar-event"></i>
              <span id="tanggalLaporan">Loading...</span>
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
            <div class="mb-3">
              <label class="form-label">Topik</label>
              <select class="form-select" id="topicFilter">
                <option value="all">Semua Topik</option>
              </select>
            </div>
            <button class="btn btn-primary w-100" id="refreshData">
              <i class="bx bx-refresh me-1"></i> Refresh Data
            </button>
          </div>
        </div>
      </div>

      <!-- Middle Column: Narasi -->
      <div class="col-12 col-lg-3">
        <div class="card">
          <div class="card-body">
            <div class="narasi-container">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <ul class="nav nav-tabs flex-column w-100" id="narasiTab" role="tablist">
                  <li class="nav-item mb-2" role="presentation">
                    <button class="nav-link active w-100"
                      id="narasi-pagi-tab" data-bs-toggle="tab"
                      data-bs-target="#narasi-pagi" type="button" role="tab"
                      aria-controls="narasi-pagi" aria-selected="true">
                      <i class="bi bi-sun me-1"></i> Narasi Pagi
                    </button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link w-100"
                      id="narasi-sore-tab" data-bs-toggle="tab"
                      data-bs-target="#narasi-sore" type="button" role="tab"
                      aria-controls="narasi-sore" aria-selected="false">
                      <i class="bi bi-moon me-1"></i> Narasi Sore
                    </button>
                  </li>
                </ul>
              </div>
              <div class="tab-content" id="narasiTabContent">
                <div class="tab-pane fade show active" id="narasi-pagi" role="tabpanel"
                  aria-labelledby="narasi-pagi-tab">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="mb-0">Narasi Pagi</h6>
                    <button class="btn btn-sm btn-outline-primary copy-btn" data-narasi="pagi">
                      <i class="bi bi-clipboard me-1"></i> Salin
                    </button>
                  </div>
                  <div class="narasi-content">
                    <p id="narasiPagi" class="mb-0">Loading...</p>
                  </div>
                </div>
                <div class="tab-pane fade" id="narasi-sore" role="tabpanel"
                  aria-labelledby="narasi-sore-tab">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="mb-0">Narasi Sore</h6>
                    <button class="btn btn-sm btn-outline-primary copy-btn" data-narasi="sore">
                      <i class="bi bi-clipboard me-1"></i> Salin
                    </button>
                  </div>
                  <div class="narasi-content">
                    <p id="narasiSore" class="mb-0">Loading...</p>
                  </div>
                </div>
              </div>
              <!-- Google Spreadsheet Download Button -->
              <div class="mt-3">
                <div class="row g-2">
                  <div class="col-12">
                    <a href="https://docs.google.com/spreadsheets/d/1Usa9zoitD613w4eEHKdaayAFpk8FGqQBSkFHdWlHx44/edit?gid=486682229#gid=486682229"
                      target="_blank"
                      class="btn btn-success w-100">
                      <i class="bi bi-file-earmark-spreadsheet me-2"></i>
                      Buka Google Spreadsheet
                    </a>
                  </div>
                  <div class="col-12">
                    <button id="cleanSpreadsheetBtn" class="btn btn-danger w-100">
                      <i class="bi bi-eraser me-2"></i>
                      Bersihkan Link Spreadsheet
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Data Display Section -->
      <div class="col-12 col-lg-6">
        <!-- Statistics Cards -->
        <div class="row mb-3">
          <div class="col-12 col-md-4">
            <div class="card rounded-2">
              <div class="card-body">
                <h6 class="mb-2">Total Link Keseluruhan</h6>
                <h4 class="mb-0" id="totalLinks">0</h4>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-8">
            <div class="card rounded-2">
              <div class="card-body">
                <h6 class="mb-2">Total Link per Platform</h6>
                <div id="platformStats" class="d-flex flex-wrap gap-2"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Topic Stats -->
        <div class="card rounded-2 mb-3">
          <div class="card-body">
            <h6 class="mb-2">Total Link per Topik</h6>
            <div id="topicStats" class="d-flex flex-wrap gap-2"></div>
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

  .topic-header {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
  }

  .links-container {
    margin-left: 15px;
    max-height: 200px;
    overflow-y: auto;
    padding-right: 10px;
    border-radius: 4px;
    background-color: #fff;
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

  .topic-section:not(:last-child) {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 20px;
    margin-bottom: 20px;
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

  .topic-stat {
    background-color: #198754;
    color: white;
  }

  .narasi-container {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
  }

  .narasi-content {
    max-height: 300px;
    overflow-y: auto;
    background-color: #fff;
    padding: 15px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
    margin-bottom: 0;
  }

  .narasi-content p {
    margin: 0;
    white-space: pre-line;
    color: #495057;
    font-size: 0.9rem;
    line-height: 1.6;
    transition: opacity 0.3s ease-in-out;
  }

  .nav-tabs {
    border-bottom: none;
  }

  .nav-tabs .nav-link {
    color: #495057;
    border: 1px solid #dee2e6;
    padding: 12px 16px;
    margin-bottom: 8px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    background-color: #fff;
    transition: all 0.2s ease-in-out;
  }

  .nav-tabs .nav-link:hover {
    background-color: #f8f9fa;
    border-color: #0d6efd;
  }

  .nav-tabs .nav-link.active {
    color: #0d6efd;
    background-color: #fff;
    border-color: #0d6efd;
    font-weight: 500;
  }

  .nav-tabs .nav-link .copy-btn {
    padding: 4px 8px;
    font-size: 0.75rem;
    line-height: 1;
    opacity: 0.8;
    transition: opacity 0.2s ease-in-out;
  }

  .nav-tabs .nav-link:hover .copy-btn {
    opacity: 1;
  }

  .nav-tabs .nav-link .copy-btn {
    padding: 2px 8px;
    font-size: 0.75rem;
    margin-left: 8px;
    border-radius: 3px;
    line-height: 1;
  }

  .nav-tabs .nav-link .copy-btn:hover {
    background-color: #0d6efd;
    color: white;
  }

  .nav-tabs .nav-link.active .copy-btn {
    border-color: #0d6efd;
  }

  /* Hindari konflik dengan tab ketika tombol copy diklik */
  .nav-tabs .nav-link .copy-btn:focus {
    outline: none;
    box-shadow: none;
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
      max-height: 200px;
    }

    .nav-tabs .nav-link {
      padding: 8px 12px;
    }
  }
</style>

<?php include_once('includes/js-includes.php'); ?>

<!-- Custom JavaScript -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const platformFilter = document.getElementById('platformFilter');
    const topicFilter = document.getElementById('topicFilter');
    const dataContainer = document.getElementById('dataContainer');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const refreshButton = document.getElementById('refreshData');

    // Clean spreadsheet button handler
    const cleanSpreadsheetBtn = document.getElementById('cleanSpreadsheetBtn');
    cleanSpreadsheetBtn.addEventListener('click', async function() {
      if (!confirm('Apakah Anda yakin ingin membersihkan semua link di spreadsheet?')) {
        return;
      }

      try {
        cleanSpreadsheetBtn.disabled = true;
        cleanSpreadsheetBtn.innerHTML = `
          <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
          Membersihkan...
        `;

        const response = await fetch('api_bersihkan_spreadsheet.php');
        const result = await response.json();

        if (result.status === 'success') {
          // Hitung total link yang dihapus
          const totalCleared = Object.values(result.data)
            .reduce((sum, platform) => sum + (platform.clearedCount || 0), 0);
          
          showToast(`Berhasil membersihkan ${totalCleared} link dari spreadsheet`);
          // Reload data after cleaning
          await loadData();
        } else {
          throw new Error(result.message || 'Gagal membersihkan spreadsheet');
        }
      } catch (error) {
        console.error('Error:', error);
        showToast('Error: ' + error.message);
      } finally {
        cleanSpreadsheetBtn.disabled = false;
        cleanSpreadsheetBtn.innerHTML = `
          <i class="bi bi-eraser me-2"></i>
          Bersihkan Link Spreadsheet
        `;
      }
    });

    // Function to show toast message
    function showToast(message) {
      document.getElementById('toastMessage').textContent = message;
      copyToast.show();
    }

    // Initialize toast
    const copyToast = new bootstrap.Toast(document.getElementById('copyToast'), {
      animation: true,
      autohide: true,
      delay: 3000
    });

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

        const type = this.getAttribute('data-narasi');
        const narasiElement = document.getElementById(`narasi${type.charAt(0).toUpperCase() + type.slice(1)}`);
        const text = narasiElement.textContent;
        const buttonText = type === 'pagi' ? 'Narasi Pagi' : 'Narasi Sore';

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
        
        // Set loading state untuk narasi
        document.getElementById('narasiPagi').textContent = 'Loading...';
        document.getElementById('narasiSore').textContent = 'Loading...';

        const response = await fetch('api_rekap_surya.php');
        const data = await response.json();

        if (data.status === 'success') {
          globalData = data.data;
          updateTopicFilter(globalData);
          displayFilteredData();

          // Update tanggal laporan
          const tanggalElement = document.getElementById('tanggalLaporan');
          const tanggal = data.data.tanggal_laporan || 'Tanggal tidak tersedia';
          tanggalElement.innerHTML = `Tanggal Laporan: ${tanggal}`;

          // Update narasi pagi dan sore
          const narasiPagiElement = document.getElementById('narasiPagi');
          const narasiSoreElement = document.getElementById('narasiSore');
          const narasiPagi = data.data.narasi?.pagi || 'Narasi pagi tidak tersedia';
          const narasiSore = data.data.narasi?.sore || 'Narasi sore tidak tersedia';
          
          // Animate narasi updates with fade effect
          narasiPagiElement.style.opacity = '0';
          narasiSoreElement.style.opacity = '0';
          
          setTimeout(() => {
            narasiPagiElement.textContent = narasiPagi;
            narasiSoreElement.textContent = narasiSore;
            
            narasiPagiElement.style.transition = 'opacity 0.3s ease-in';
            narasiSoreElement.style.transition = 'opacity 0.3s ease-in';
            narasiPagiElement.style.opacity = '1';
            narasiSoreElement.style.opacity = '1';
          }, 200);
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

    // Fungsi untuk memperbarui filter topik
    function updateTopicFilter(data) {
      const topics = new Set();
      Object.values(data).forEach(platform => {
        if (typeof platform === 'object') {
          Object.keys(platform).forEach(topic => topics.add(topic));
        }
      });

      topicFilter.innerHTML = '<option value="all">Semua Topik</option>';
      [...topics].sort().forEach(topic => {
        topicFilter.innerHTML += `<option value="${topic}">${topic}</option>`;
      });
    }

    // Fungsi untuk menampilkan data berdasarkan filter
    function calculateStats(data) {
      // Filter out non-platform data like tanggal_laporan
      const platformData = Object.fromEntries(
        Object.entries(data).filter(([key, value]) =>
          typeof value === 'object' && !Array.isArray(value)
        )
      );

      const stats = {
        total: 0,
        platforms: {},
        topics: {}
      };

      // Loop through each platform
      Object.entries(data).forEach(([platform, platformData]) => {
        if (typeof platformData !== 'object') return;

        stats.platforms[platform] = 0;

        // Loop through each topic directly
        Object.entries(platformData).forEach(([topic, links]) => {
          // Make sure we're dealing with an array of links
          if (Array.isArray(links)) {
            const linkCount = links.length;

            // Update platform total
            stats.platforms[platform] += linkCount;

            // Update topic total
            if (!stats.topics[topic]) {
              stats.topics[topic] = 0;
            }
            stats.topics[topic] += linkCount;

            // Update total
            stats.total += linkCount;
          }
        });
      });

      return stats;
    }

    // Fungsi untuk menghitung duplikat link
    function countDuplicateLinks(data) {
      const linkCounts = new Map();
      // Mengumpulkan semua link dari semua platform dan topik
      Object.values(data).forEach(platform => {
        if (typeof platform === 'object') {
          Object.values(platform).forEach(links => {
            if (Array.isArray(links)) {
              links.forEach(link => {
                if (link && typeof link === 'string') {
                  linkCounts.set(link, (linkCounts.get(link) || 0) + 1);
                }
              });
            }
          });
        }
      });
      return linkCounts;
    }

    function updateStats(stats) {
      // Debug output
      console.log('Stats calculated:', {
        total: stats.total,
        platforms: stats.platforms,
        topics: stats.topics
      });

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
        .sort(([_a, countA], [_b, countB]) => countB - countA) // Sort by count descending
        .map(([platform, count]) => `
                <div class="stat-badge platform-stat">
                    <i class="bi ${getPlatformIcon(platform)}"></i>
                    <span class="fw-bold">${platform}</span>
                    <span class="ms-1">${count.toLocaleString()}</span>
                </div>
            `).join('') || '<div class="text-muted">Tidak ada data</div>';
      document.getElementById('platformStats').innerHTML = platformStatsHtml;

      // Update topic stats sorted by count
      const topicStatsHtml = Object.entries(stats.topics)
        .sort(([_a, countA], [_b, countB]) => countB - countA) // Sort by count descending
        .map(([topic, count]) => `
                <div class="stat-badge topic-stat">
                    <i class="bi bi-bookmark-fill"></i>
                    <span class="fw-bold">${topic}</span>
                    <span class="ms-1">${count.toLocaleString()}</span>
                </div>
            `).join('') || '<div class="text-muted">Tidak ada data</div>';
      document.getElementById('topicStats').innerHTML = topicStatsHtml;
    }

    function displayFilteredData() {
      if (!globalData) return;

      const selectedPlatform = platformFilter.value;
      const selectedTopic = topicFilter.value;

      // Calculate and update stats
      const stats = calculateStats(globalData);
      updateStats(stats);

      // Create accordion
      let accordionHtml = '<div class="accordion accordion-flush" id="platformAccordion">';

      Object.entries(globalData).forEach(([platform, platformData], platformIndex) => {
        if (selectedPlatform !== 'all' && platform !== selectedPlatform) return;
        if (typeof platformData !== 'object') return;

        const duplicates = countDuplicateLinks(globalData);
        const {
          hasDuplicates,
          duplicateCount
        } = checkDuplicatesInPlatform(platform, platformData, duplicates);

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
                                    ${stats.platforms[platform] || 0} link
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
            `;

        // Topics within Platform
        Object.entries(platformData).forEach(([topic, links]) => {
          if (selectedTopic !== 'all' && topic !== selectedTopic) return;

          accordionHtml += `
                    <div class="topic-section mb-4">
                        <div class="topic-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">${topic}</h5>
                            <span class="badge bg-success rounded-pill">${links.length} link</span>
                        </div>
                        <div class="links-container mt-3">
                `;

          const duplicates = countDuplicateLinks(globalData);

          // Links for each topic
          if (Array.isArray(links)) {
            links.forEach(link => {
              if (link && typeof link === 'string') {
                const duplicateCount = duplicates.get(link);
                const isDuplicate = duplicateCount > 1;
                accordionHtml += `
                                <div class="link-item ${isDuplicate ? 'duplicate-link' : ''}">
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
          }

          accordionHtml += `
                        </div>
                    </div>
                `;
        });

        accordionHtml += `
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
    function checkDuplicatesInPlatform(platform, platformData, allDuplicates) {
      let hasDuplicates = false;
      let duplicateCount = 0;

      if (typeof platformData === 'object') {
        Object.values(platformData).forEach(links => {
          if (Array.isArray(links)) {
            links.forEach(link => {
              if (link && typeof link === 'string' && allDuplicates.get(link) > 1) {
                hasDuplicates = true;
                duplicateCount++;
              }
            });
          }
        });
      }

      return {
        hasDuplicates,
        duplicateCount
      };
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

    // Fungsi untuk copy narasi
    document.querySelectorAll('.copy-btn').forEach(button => {
      button.addEventListener('click', async function(e) {
        e.preventDefault();
        e.stopPropagation();

        const type = this.getAttribute('data-narasi');
        const narasiElement = document.getElementById(`narasi${type.charAt(0).toUpperCase() + type.slice(1)}`);
        const text = narasiElement.textContent;

        if (text === 'Loading...' || text.includes('tidak tersedia')) {
          alert('Narasi belum tersedia untuk disalin');
          return;
        }

        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();

        try {
          document.execCommand('copy');
          document.body.removeChild(textarea);

          // Update button appearance
          const originalHTML = this.innerHTML;
          this.innerHTML = '<i class="bi bi-check2"></i>';
          this.classList.remove('btn-outline-primary');
          this.classList.add('btn-success');

          // Show toast notification
          const toastMessage = document.getElementById('toastMessage');
          toastMessage.textContent = `Narasi ${type} berhasil disalin!`;
          const toastElement = document.getElementById('copyToast');
          const toast = new bootstrap.Toast(toastElement);
          toast.show();

          setTimeout(() => {
            this.innerHTML = originalHTML;
            this.classList.remove('btn-success');
            this.classList.add('btn-outline-primary');
          }, 2000);
        } catch (err) {
          document.body.removeChild(textarea);
          console.error('Failed to copy text: ', err);
          alert('Gagal menyalin teks. Silakan coba lagi.');
        }
      });
    });

    // Event listeners
    platformFilter.addEventListener('change', displayFilteredData);
    topicFilter.addEventListener('change', displayFilteredData);
    refreshButton.addEventListener('click', loadData);

    // Load initial data
    loadData();
  });
</script>

<?php
include_once('includes/footer.php');
?>