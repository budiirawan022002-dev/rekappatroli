/**
 * Screenshot Link JavaScript
 * Menangani proses pengambilan screenshot dari link
 */

document.addEventListener('DOMContentLoaded', function() {
    const screenshotForm = document.getElementById('screenshotForm');
    const btnAmbilScreenshot = document.getElementById('btnAmbilScreenshot');
    const screenshotResult = document.getElementById('screenshotResult');
    const downloadButtonContainer = document.getElementById('downloadButtonContainer');
    const btnDownloadAll = document.getElementById('btnDownloadAll');
    
    // Array untuk menyimpan nama file yang telah dibuat
    let screenshotFiles = [];

    // Handler submit form
    screenshotForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validasi input
        const linkInput = document.getElementById('linkInput').value.trim();
        if (!linkInput) {
            showAlert('error', 'Mohon masukkan minimal satu link');
            return;
        }
        
        // Ambil jenis screenshot
        const jenisScreenshot = document.getElementById('jenisScreenshot').value;
        
        // Array link dari textarea
        const links = linkInput.split('\n').filter(link => link.trim() !== '');
        
        if (links.length === 0) {
            showAlert('error', 'Mohon masukkan minimal satu link yang valid');
            return;
        }
        
        // Reset hasil dan array file
        screenshotFiles = [];
        screenshotResult.innerHTML = '';
        downloadButtonContainer.classList.add('d-none');
        
        // Tampilkan loading
        showProgressOverlay('Mempersiapkan proses screenshot...', 0);
        
        // Proses pengambilan screenshot untuk semua link
        processScreenshots(links, jenisScreenshot);
    });
    
    // Handler tombol download
    btnDownloadAll.addEventListener('click', function() {
        if (screenshotFiles.length === 0) {
            showAlert('error', 'Tidak ada file screenshot untuk didownload');
            return;
        }
        
        // Kirim permintaan ke server untuk mengompres dan mendownload semua file
        downloadAllScreenshots(screenshotFiles);
    });

    // Handler tombol tampilkan semua screenshot
    document.getElementById('btnShowAllScreenshots').addEventListener('click', function() {
        // Reset hasil dan array file
        screenshotFiles = [];
        screenshotResult.innerHTML = '';
        downloadButtonContainer.classList.add('d-none');
        
        // Ambil jenis screenshot
        const jenisScreenshot = document.getElementById('jenisScreenshot').value;
        
        // Tampilkan loading
        showProgressOverlay('Mengambil semua screenshot yang tersedia...', 30);
        
        // Dapatkan semua screenshot yang ada
        getExistingScreenshots(jenisScreenshot, []);
    });

    /**
     * Fungsi untuk memproses pengambilan screenshot
     * @param {Array} links - Array berisi link yang akan diambil screenshotnya
     * @param {String} jenis - Jenis screenshot (cipop, patroli, upaya)
     */
    function processScreenshots(links, jenis) {
        // Reset array file screenshot
        screenshotFiles = [];
        
        // Tampilkan loading dengan 0%
        showProgressOverlay('Mempersiapkan proses screenshot...', 0);
        
        // Proses screenshot satu per satu untuk menampilkan progres
        processScreenshotSequentially(links, jenis, 0);
    }
    
    /**
     * Fungsi untuk memproses screenshot satu per satu secara berurutan
     * @param {Array} links - Array link yang akan diproses
     * @param {String} jenis - Jenis screenshot
     * @param {Number} currentIndex - Index link yang sedang diproses
     */
    function processScreenshotSequentially(links, jenis, currentIndex) {        // Jika sudah selesai memproses semua link
        if (currentIndex >= links.length) {
            // Selesai, dapatkan daftar lengkap gambar dalam folder ss
            getExistingScreenshots(jenis, screenshotFiles);
            return;
        }
        
        // Ambil link saat ini
        const currentLink = links[currentIndex];
        
        // Hitung persentase progres
        const progress = Math.round((currentIndex / links.length) * 100);
        
        // Update pesan progres
        showProgressOverlay(`Mengambil screenshot (${currentIndex + 1}/${links.length}): ${currentLink.substring(0, 30)}...`, progress);
        
        // Siapkan form data untuk satu link
        const formData = new FormData();
        formData.append('action', 'take_screenshots');
        formData.append('jenis', jenis);
        formData.append('links[0]', currentLink);
        
        // Kirim request AJAX ke server
        fetch('api_screenshot.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Terjadi kesalahan pada server');
            }
            return response.json();
        })        .then(data => {
            if (data.status === 'success' && data.files && data.files.length > 0) {
                // Tambahkan file hasil ke array
                data.files.forEach(file => {
                    // Pastikan tidak ada file duplikat
                    if (!screenshotFiles.includes(file)) {
                        screenshotFiles.push(file);
                    }
                });
                
                // Log untuk debugging
                console.log(`Screenshot berhasil: ${data.files.join(', ')}`);
                console.log(`Total file sekarang: ${screenshotFiles.length}`);
            } else {
                console.log('Link tidak menghasilkan screenshot:', currentLink);
            }
            
            // Lanjut ke link berikutnya
            processScreenshotSequentially(links, jenis, currentIndex + 1);
        })
        .catch(error => {
            console.error('Error processing screenshot:', error);
            // Meskipun error, tetap lanjut ke link berikutnya
            processScreenshotSequentially(links, jenis, currentIndex + 1);
        });
    }    /**
     * Fungsi untuk menampilkan hasil screenshot
     * @param {Array} files - Array berisi path file screenshot
     */
    function displayScreenshotResults(files) {
        console.log('Menampilkan hasil screenshot, jumlah file:', files.length);
        
        if (files.length === 0) {
            screenshotResult.innerHTML = `
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill"></i> Tidak ada screenshot yang berhasil diambil
                </div>
            `;
            return;
        }
        
        // Buat gallery screenshot dengan layout grid yang lebih responsif
        let galleryHTML = '<div class="row g-2">';
        
        // Filter file unik (tanpa duplikat)
        const uniqueFiles = [...new Set(files)];
        
        uniqueFiles.forEach((file, index) => {
            galleryHTML += `
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-header p-2 bg-light d-flex justify-content-between align-items-center">
                            <span class="badge bg-info">${getPlatformLabel(file)}</span>
                            <small class="text-muted">${index + 1}/${uniqueFiles.length}</small>
                        </div>
                        <div class="position-relative">
                            <img src="ss/${file}" class="card-img-top img-fluid" alt="Screenshot ${index + 1}" onerror="this.src='css/image-not-found.png';this.classList.add('img-thumbnail','border-danger');">
                            <div class="position-absolute top-0 end-0 m-2">
                                <a href="ss/${file}" download="${file}" class="btn btn-sm btn-success">
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                        </div>
                        <div class="card-footer p-2 text-muted small">
                            <span title="${file}">${truncateFilename(file, 30)}</span>
                        </div>
                    </div>
                </div>
            `;
        });
        
        galleryHTML += '</div>';
        
        // Tambahkan informasi total screenshot
        galleryHTML = `
            <div class="alert alert-success mb-3">
                <i class="bi bi-check-circle-fill"></i> Berhasil mengambil <strong>${uniqueFiles.length}</strong> screenshot
            </div>
            ${galleryHTML}
        `;
        
        screenshotResult.innerHTML = galleryHTML;
        
        // Simpan daftar file unik ke variabel global
        screenshotFiles = uniqueFiles;
        
        // Tampilkan tombol download semua dengan jelas
        downloadButtonContainer.innerHTML = `
            <div class="alert alert-info mb-3">
                <i class="bi bi-info-circle-fill"></i> Anda dapat mengunduh semua ${uniqueFiles.length} screenshot sekaligus dengan menekan tombol di bawah ini
            </div>
            <div class="d-grid">
                <button id="btnDownloadAll" class="btn btn-success btn-lg">
                    <i class="bi bi-cloud-download-fill"></i> Download Semua Gambar (${uniqueFiles.length} file)
                </button>
            </div>
        `;
        downloadButtonContainer.classList.remove('d-none');
        
        // Tambahkan event listener untuk tombol download
        document.getElementById('btnDownloadAll').addEventListener('click', function() {
            if (screenshotFiles.length === 0) {
                showAlert('error', 'Tidak ada file screenshot untuk didownload');
                return;
            }
            
            // Kirim permintaan ke server untuk mengompres dan mendownload semua file
            downloadAllScreenshots(screenshotFiles);
        });
    }
    
    /**
     * Fungsi untuk memotong nama file yang terlalu panjang
     * @param {String} filename - Nama file screenshot
     * @param {Number} maxLength - Panjang maksimal yang diizinkan
     * @return {String} Nama file yang sudah dipotong
     */
    function truncateFilename(filename, maxLength) {
        if (filename.length <= maxLength) {
            return filename;
        }
        
        const extension = filename.split('.').pop();
        const nameWithoutExt = filename.substring(0, filename.lastIndexOf('.'));
        
        // Potong nama file dan tambahkan ekstensi
        return nameWithoutExt.substring(0, maxLength - extension.length - 4) + '...' + '.' + extension;
    }
    
    /**
     * Fungsi untuk mendapatkan semua screenshot yang ada dalam folder ss
     * @param {String} jenis - Jenis screenshot (cipop, patroli, upaya)
     * @param {Array} currentFiles - Array file yang sudah diambil
     */
    function getExistingScreenshots(jenis, currentFiles) {
        showProgressOverlay('Mengambil daftar semua gambar yang tersedia...', 80);
        
        // Siapkan form data
        const formData = new FormData();
        formData.append('action', 'get_existing_screenshots');
        formData.append('jenis', jenis);
        
        // Kirim permintaan ke server
        fetch('api_screenshot.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Terjadi kesalahan pada server');
            }
            return response.json();
        })
        .then(data => {
            hideProgressOverlay();
            
            if (data.status === 'success' && data.files) {
                let allFiles = [...currentFiles];
                
                // Tambahkan file yang belum ada dalam daftar
                data.files.forEach(file => {
                    if (!allFiles.includes(file)) {
                        allFiles.push(file);
                    }
                });
                
                console.log(`Total file dari server: ${data.files.length}`);
                console.log(`Total gabungan file: ${allFiles.length}`);
                
                if (allFiles.length > 0) {
                    // Update array screenshot files
                    screenshotFiles = allFiles;
                    
                    // Tampilkan hasil screenshot
                    displayScreenshotResults(allFiles);
                    
                    // Tampilkan tombol download
                    downloadButtonContainer.classList.remove('d-none');
                    
                    showAlert('success', 'Screenshot berhasil diambil!');
                } else {
                    showAlert('error', 'Tidak ada screenshot yang ditemukan');
                    screenshotResult.innerHTML = `
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill"></i> Tidak ada screenshot yang ditemukan
                        </div>
                    `;
                }
            } else {
                // Tampilkan hanya file yang berhasil diambil
                if (currentFiles.length > 0) {
                    displayScreenshotResults(currentFiles);
                    downloadButtonContainer.classList.remove('d-none');
                    showAlert('success', 'Screenshot berhasil diambil!');
                } else {
                    showAlert('error', 'Tidak ada screenshot yang berhasil diambil');
                    screenshotResult.innerHTML = `
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill"></i> Tidak ada screenshot yang berhasil diambil
                        </div>
                    `;
                }
            }
        })
        .catch(error => {
            hideProgressOverlay();
            console.error('Error getting existing screenshots:', error);
            
            // Tampilkan hanya file yang berhasil diambil
            if (currentFiles.length > 0) {
                displayScreenshotResults(currentFiles);
                downloadButtonContainer.classList.remove('d-none');
                showAlert('success', 'Screenshot berhasil diambil!');
            } else {
                showAlert('error', 'Tidak ada screenshot yang berhasil diambil');
                screenshotResult.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i> Tidak ada screenshot yang berhasil diambil
                    </div>
                `;
            }
        });
    }
    
    /**
     * Fungsi untuk mendapatkan label platform dari nama file
     * @param {String} filename - Nama file screenshot
     * @return {String} Label platform
     */
    function getPlatformLabel(filename) {
        if (filename.includes('_facebook_')) {
            return 'Facebook';
        } else if (filename.includes('_instagram_')) {
            return 'Instagram';
        } else if (filename.includes('_xcom_')) {
            return 'Twitter/X';
        } else if (filename.includes('_tiktok_')) {
            return 'TikTok';
        } else if (filename.includes('_youtube_')) {
            return 'YouTube';
        } else {
            return 'Unknown';
        }
    }    /**
     * Fungsi untuk mengunduh semua screenshot
     * @param {Array} files - Array berisi nama file screenshot
     */
    function downloadAllScreenshots(files) {
        // Pastikan array files tidak kosong
        if (!files || files.length === 0) {
            showAlert('error', 'Tidak ada file screenshot untuk didownload');
            return;
        }

        console.log('Mulai proses download semua gambar:', files.length, 'file');
        console.log('Files yang akan didownload:', files);
        
        // Siapkan form data
        const formData = new FormData();
        formData.append('action', 'download_all');
        
        // Tambahkan semua file ke form data
        files.forEach((file, index) => {
            formData.append(`files[${index}]`, file);
        });
        
        // Tampilkan loading
        showProgressOverlay(`Mempersiapkan ${files.length} file untuk didownload...`, 50);
        
        // Kirim request AJAX ke server
        fetch('api_screenshot.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Terjadi kesalahan pada server: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            hideProgressOverlay();
            
            console.log('Response dari server:', data);
            
            if (data.status === 'success') {
                // Buat link download dan klik otomatis
                const downloadLink = document.createElement('a');
                downloadLink.href = data.zipFile;
                downloadLink.download = data.zipFileName || 'screenshots.zip';
                document.body.appendChild(downloadLink);
                
                // Trigger download
                setTimeout(() => {
                    downloadLink.click();
                    document.body.removeChild(downloadLink);
                }, 100);
                
                showAlert('success', `Download ${data.fileCount || files.length} gambar dalam ZIP dimulai!`);
            } else {
                showAlert('error', data.message || 'Gagal mendownload screenshot');
            }
        })
        .catch(error => {
            hideProgressOverlay();
            console.error('Error downloading files:', error);
            showAlert('error', 'Error: ' + error.message);
        });
    }
    
    /**
     * Fungsi untuk menampilkan pesan alert
     * @param {String} type - Tipe alert (success, error, warning)
     * @param {String} message - Pesan yang akan ditampilkan
     */
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Tambahkan alert di atas form
        const cardBody = screenshotForm.closest('.card-body');
        cardBody.insertBefore(alertDiv, cardBody.firstChild);
        
        // Hapus alert setelah 5 detik
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
});

/**
 * Fungsi untuk menampilkan progress overlay
 * @param {String} message - Pesan yang akan ditampilkan
 * @param {Number} progress - Persentase progress (0-100)
 */
function showProgressOverlay(message, progress) {
    const progressOverlay = document.getElementById('progressOverlay');
    const progressBar = document.getElementById('progressBar');
    const progressBarStatus = document.getElementById('progressBarStatus');
    
    progressBarStatus.textContent = message;
    progressBar.style.width = `${progress}%`;
    progressBar.textContent = `${progress}%`;
    
    progressOverlay.style.display = 'flex';
}

/**
 * Fungsi untuk menyembunyikan progress overlay
 */
function hideProgressOverlay() {
    const progressOverlay = document.getElementById('progressOverlay');
    progressOverlay.style.display = 'none';
}
