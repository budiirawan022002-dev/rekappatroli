<?php
require 'vendor/autoload.php';
require 'fungsi_proses.php';
require 'fungsi_proses_khusus.php';
require 'fungsi_konversi.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

header('Content-Type: application/json');

// Disable output buffering for streaming JSON responses
if (ob_get_level()) ob_end_clean();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_log("API_REKAP: Invalid request method: " . $_SERVER['REQUEST_METHOD']);
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }
    
    if (!isset($_POST['reportType'])) {
        error_log("API_REKAP: reportType not set in POST data");
        error_log("API_REKAP: POST data: " . print_r($_POST, true));
        echo json_encode(['success' => false, 'message' => 'Report type not specified']);
        exit;
    }
    
    error_log("API_REKAP: Processing request for report types: " . implode(', ', $_POST['reportType']));

    // Set proper character encoding for output
    mb_internal_encoding('UTF-8');
    mb_http_output('UTF-8');

    // Clean up hasil directory before processing
    cleanupHasilDirectory();

    $narrative = "";
    $outputPathWordPatroli = $outputPathWordGeneral = $outputPathPdf = "";
    $outputPathLandy = $outputPathPdfLandy = $outputPathPagi = $outputPathPdfPagi = "";
    $outputPathBencana = $outputPathPdfBencana = "";
    $outputPathKhusus = $outputPathWordPatroliKhusus = $outputPathPdfKhusus = "";
    $narasiPatroliLandy = $narasiPatroliPagi = $narasiPatroliBencana = $narasiKhusus = $fullNarasiKhusus = "";

    // --- Proses Patroli ---
    $rawReport = $_POST['patrolReport'] ?? '';
    
    // Check if Patroli Landy or Patroli Bencana is selected to determine field count
    $reportTypes = $_POST['reportType'] ?? [];
    $isPatroliLandy = in_array('Patroli Landy', $reportTypes);
    $isPatroliBencana = in_array('Patroli Bencana', $reportTypes);
    $fieldCount = ($isPatroliLandy || $isPatroliBencana) ? 9 : 4;
    
    // Get judul Landy (dari dropdown atau custom input)
    $judulLandy = '';
    if ($isPatroliLandy) {
        $judulLandyDropdown = $_POST['judulLandy'] ?? '';
        $judulLandyCustom = $_POST['judulLandyCustom'] ?? '';
        
        // Gunakan custom input jika dropdown pilih "custom", otherwise gunakan dropdown value
        $judulLandy = ($judulLandyDropdown === 'custom') ? trim($judulLandyCustom) : $judulLandyDropdown;
        
        // Fallback jika kosong
        if (empty($judulLandy)) {
            $judulLandy = 'Pemetaan Akun Medsos Narasi Negatif MBG';
        }
        
        error_log("Judul Landy: " . $judulLandy);
    }
    
    error_log("Patrol processing - Is Patroli Landy: " . ($isPatroliLandy ? 'YES' : 'NO'));
    error_log("Patrol processing - Field count: " . $fieldCount);
    error_log("Patrol processing - Raw report length: " . strlen($rawReport));
    
    // Check if multi-line profiling format is used (for Landy or Bencana)
    // Support both old format (profiling:\nNama:) and new format (profiling:\nNik: or profiling:\nKK:)
    $hasMultiLineProfiling = false;
    if (($isPatroliLandy || $isPatroliBencana) && (
        preg_match('/profiling:\s*\n\s*(Nama|Nik|KK|Jenis\s+kelamin|Lahir|Tanggal\s+Lahir|Pekerjaan|Provinsi|Kabupaten|Kecamatan|Kelurahan|Alamat\s+Lengkap)\s*:/is', $rawReport) ||
        preg_match('/profiling:\s*\n\s*[A-Za-z\s]+:\s*/is', $rawReport)
    )) {
        $hasMultiLineProfiling = true;
        error_log("✅ DETECTED MULTI-LINE PROFILING FORMAT - Using parseLandyMultiLineProfiling()");
        $hasilPatroli = parseLandyMultiLineProfiling($rawReport);
        $groupedReports = $hasilPatroli['groupedReports'];
        $processedReports = $hasilPatroli['processedReports'];
        
        // Convert profiling data to text format for display
        foreach ($processedReports as $platform => &$reports) {
            foreach ($reports as &$report) {
                if (isset($report['profiling']) && is_array($report['profiling'])) {
                    $profilingText = '';
                    if (isset($report['profiling_text'])) {
                        $profilingText = $report['profiling_text'];
                    } else {
                        $profilingParts = [];
                        if (isset($report['profiling']['nama'])) $profilingParts[] = "Nama: " . $report['profiling']['nama'];
                        if (isset($report['profiling']['jenis_kelamin'])) $profilingParts[] = "Jenis Kelamin: " . $report['profiling']['jenis_kelamin'];
                        if (isset($report['profiling']['gol_darah'])) $profilingParts[] = "Golongan Darah: " . $report['profiling']['gol_darah'];
                        if (isset($report['profiling']['status_nikah'])) $profilingParts[] = "Status Nikah: " . $report['profiling']['status_nikah'];
                        if (isset($report['profiling']['agama'])) $profilingParts[] = "Agama: " . $report['profiling']['agama'];
                        if (isset($report['profiling']['tempat_lahir'])) $profilingParts[] = "Lahir: " . $report['profiling']['tempat_lahir'];
                        if (isset($report['profiling']['umur'])) $profilingParts[] = "Umur: " . $report['profiling']['umur'];
                        if (isset($report['profiling']['tgl_lahir'])) $profilingParts[] = "Tanggal Lahir: " . $report['profiling']['tgl_lahir'];
                        if (isset($report['profiling']['pekerjaan'])) $profilingParts[] = "Pekerjaan: " . $report['profiling']['pekerjaan'];
                        if (isset($report['profiling']['provinsi'])) $profilingParts[] = "Provinsi: " . $report['profiling']['provinsi'];
                        if (isset($report['profiling']['kabupaten'])) $profilingParts[] = "Kabupaten: " . $report['profiling']['kabupaten'];
                        if (isset($report['profiling']['kecamatan'])) $profilingParts[] = "Kecamatan: " . $report['profiling']['kecamatan'];
                        if (isset($report['profiling']['kelurahan'])) $profilingParts[] = "Kelurahan: " . $report['profiling']['kelurahan'];
                        if (isset($report['profiling']['kode_pos'])) $profilingParts[] = "Kode Pos: " . $report['profiling']['kode_pos'];
                        if (isset($report['profiling']['rt_rw'])) $profilingParts[] = "RT/RW: " . $report['profiling']['rt_rw'];
                        if (isset($report['profiling']['alamat'])) $profilingParts[] = "Alamat Lengkap: " . $report['profiling']['alamat'];
                        $profilingText = implode("\n", $profilingParts);
                    }
                    $report['profiling'] = $profilingText;
                }
            }
        }
        unset($reports, $report);
    } else {
        $hasilPatroli = prosesPatrolReport($rawReport, 'patroli', $fieldCount, $isPatroliLandy);
        $groupedReports = $hasilPatroli['groupedReports'];
        $processedReports = $hasilPatroli['processedReports'];
    }

    // Narasi Patroli & platform count
    $narasiPatroli = buildNarasiPatroli($groupedReports, $platformCounts, $totalPatroli);

    $sheetsToRead = ['FACEBOOK', 'INSTAGRAM', 'TWITTER', 'TIKTOK', 'SNACKVIDEO', 'YOUTUBE'];
    $tanggalInput = $_POST['tanggal'] ?? date('Y-m-d');
    $tanggalFormatted = strtoupper(formatTanggalIndonesia($tanggalInput));
    $tanggalNamaFile = date('dmY', strtotime($tanggalInput));
    $tanggalFormattedFirst = ucfirst(formatTanggalIndonesia($tanggalInput));
    $hariFormatted = getHariIndonesia($tanggalInput);
    $bulan_romawi = bulanKeRomawi($tanggalInput);

    // --- Screenshot Patroli ---
    // Only process patrol screenshots if we have reports that need them (not for Laporan Khusus only)
    $screenshotPaths = [];
    $needsPatrolScreenshots = false;
    foreach ($_POST['reportType'] as $reportType) {
        if (in_array($reportType, ['Laporan KBD', 'Patroli Landy', 'Patroli Pagi', 'Patroli Bencana'])) {
            $needsPatrolScreenshots = true;
            break;
        }
    }
    
    if ($needsPatrolScreenshots) {
        echo json_encode(['progress' => 'Memproses screenshot patroli...', 'percent' => 10]); @ob_flush(); @flush();
        $screenshotPaths = handleScreenshotPatroli($processedReports, $_POST, $_FILES);
    } else {
        // For Laporan Khusus only, screenshot patroli is optional
        echo json_encode(['progress' => 'Screenshot patroli tidak diperlukan untuk laporan khusus...', 'percent' => 10]); @ob_flush(); @flush();
        error_log("API DEBUG: Skipping patrol screenshots for Laporan Khusus only");
    }

    // Calculate progress ranges dynamically based on selected reports
    $selectedReports = $_POST['reportType'];
    $totalReports = count($selectedReports);
    $progressRanges = [];
    $currentPercent = 15;
    
    if (in_array('Laporan KBD', $selectedReports)) {
        $progressRanges['KBD'] = ['start' => $currentPercent, 'range' => 30];
        $currentPercent += 30;
    }
    if (in_array('Patroli Landy', $selectedReports)) {
        $progressRanges['Landy'] = ['start' => $currentPercent, 'range' => 25];
        $currentPercent += 25;
    }
    if (in_array('Patroli Bencana', $selectedReports)) {
        $progressRanges['Bencana'] = ['start' => $currentPercent, 'range' => 25];
        $currentPercent += 25;
    }
    if (in_array('Patroli Pagi', $selectedReports)) {
        $progressRanges['Pagi'] = ['start' => $currentPercent, 'range' => 25];
        $currentPercent += 25;
    }
    if (in_array('Laporan Khusus', $selectedReports)) {
        $progressRanges['Khusus'] = ['start' => $currentPercent, 'range' => 30];
        $currentPercent += 30;
    }

    // --- Laporan KBD ---
    if (in_array('Laporan KBD', $_POST['reportType'])) {
        $startProgress = isset($progressRanges['KBD']) ? $progressRanges['KBD']['start'] : 15;
        $progressRange = isset($progressRanges['KBD']) ? $progressRanges['KBD']['range'] : 30;
        echo json_encode(['progress' => 'Membuat laporan KBD...', 'percent' => $startProgress]); @ob_flush(); @flush();
        try {
            handleLaporanKBD(
                $platformCounts, $tanggalNamaFile, $tanggalFormatted, $tanggalFormattedFirst, $hariFormatted, $bulan_romawi,
                __DIR__ . '/hasil', $_POST, $_FILES, $sheetsToRead, $narasiPatroli, $totalPatroli, $processedReports, $screenshotPaths,
                $narrative, $outputPathWordGeneral, $outputPathPdf, $outputPathWordPatroli, $startProgress, $progressRange
            );
        } catch (Exception $e) {
            error_log("Error creating KBD report: " . $e->getMessage());
            throw new Exception("Gagal membuat laporan KBD: " . $e->getMessage());
        }
    }

    // --- Patroli Landy ---
    if (in_array('Patroli Landy', $_POST['reportType'])) {
        $startProgress = isset($progressRanges['Landy']) ? $progressRanges['Landy']['start'] : 50;
        $progressRange = isset($progressRanges['Landy']) ? $progressRanges['Landy']['range'] : 25;
        echo json_encode(['progress' => 'Membuat laporan Patroli Landy...', 'percent' => $startProgress]); @ob_flush(); @flush();
        try {
            handlePatroliLandy(
                $processedReports, $tanggalFormatted, $tanggalFormattedFirst, __DIR__ . '/hasil', $_POST, $_FILES, $screenshotPaths,
                $narasiPatroliLandy, $outputPathLandy, $outputPathPdfLandy, $judulLandy, $startProgress, $progressRange
            );
        } catch (Exception $e) {
            error_log("Error creating Patroli Landy report: " . $e->getMessage());
            throw new Exception("Gagal membuat laporan Patroli Landy: " . $e->getMessage());
        }
    }

    // --- Patroli Bencana ---
    if (in_array('Patroli Bencana', $_POST['reportType'])) {
        error_log("=== PATROLI BENCANA PROCESSING START ===");
        
        // Get judul Bencana (dari dropdown atau custom input)
        $judulBencana = '';
        $judulBencanaDropdown = $_POST['judulBencana'] ?? '';
        $judulBencanaCustom = $_POST['judulBencanaCustom'] ?? '';
        $judulBencana = ($judulBencanaDropdown === 'custom') ? trim($judulBencanaCustom) : $judulBencanaDropdown;
        if (empty($judulBencana)) {
            $judulBencana = 'Patroli Siber Konten Provokatif Mendiskreditkan Pemerintah';
        }
        
        // Initialize screenshot, upaya, and profiling paths
        $screenshotPathsBencana = [];
        $upayaPathsBencana = [];
        $profilingPathsBencana = [];
        
        // Process screenshot files if uploaded
        if (isset($_FILES['screenshotPatroli']) && !empty($_FILES['screenshotPatroli']['name'][0])) {
            for ($i = 0; $i < count($_FILES['screenshotPatroli']['name']); $i++) {
                if (isset($_FILES['screenshotPatroli']['tmp_name'][$i]) && $_FILES['screenshotPatroli']['error'][$i] === UPLOAD_ERR_OK) {
                    $originalPath = $_FILES['screenshotPatroli']['tmp_name'][$i];
                    $fileName = 'patroli_bencana_' . time() . '_' . $i . '_' . basename($_FILES['screenshotPatroli']['name'][$i]);
                    $destinationPath = __DIR__ . '/foto/' . $fileName;
                    if (move_uploaded_file($originalPath, $destinationPath)) {
                        $screenshotPathsBencana[] = $destinationPath;
                    }
                }
            }
        }
        
        // Process RAS/Upaya files
        if (isset($_FILES['rasFiles']) && !empty($_FILES['rasFiles']['name'][0])) {
            for ($i = 0; $i < count($_FILES['rasFiles']['name']); $i++) {
                if (isset($_FILES['rasFiles']['tmp_name'][$i]) && $_FILES['rasFiles']['error'][$i] === UPLOAD_ERR_OK) {
                    $originalPath = $_FILES['rasFiles']['tmp_name'][$i];
                    $fileName = 'ras_bencana_' . time() . '_' . $i . '_' . basename($_FILES['rasFiles']['name'][$i]);
                    $destinationPath = __DIR__ . '/foto/' . $fileName;
                    if (move_uploaded_file($originalPath, $destinationPath)) {
                        $upayaPathsBencana[] = $destinationPath;
                    }
                }
            }
        }
        
        // Process Profiling files
        if (isset($_FILES['profilingFiles']) && !empty($_FILES['profilingFiles']['name'][0])) {
            $timestamp = date('His') . '_' . substr(microtime(true) * 1000, -3);
            for ($i = 0; $i < count($_FILES['profilingFiles']['name']); $i++) {
                if (isset($_FILES['profilingFiles']['tmp_name'][$i]) && $_FILES['profilingFiles']['error'][$i] === UPLOAD_ERR_OK) {
                    $originalPath = $_FILES['profilingFiles']['tmp_name'][$i];
                    $originalName = $_FILES['profilingFiles']['name'][$i];
                    $pathInfo = pathinfo($originalName);
                    $fileName = 'profiling_bencana_' . $timestamp . '_' . ($i + 1) . '_' . $pathInfo['filename'] . '.' . ($pathInfo['extension'] ?? 'jpg');
                    $destinationPath = __DIR__ . '/template_word/' . $fileName;
                    if (!is_dir(__DIR__ . '/template_word')) {
                        mkdir(__DIR__ . '/template_word', 0755, true);
                    }
                    if (copy($originalPath, $destinationPath)) {
                        $profilingPathsBencana[] = $destinationPath;
                    }
                }
            }
        }
        
        $isiPatroliBencana = "";
        $tanggal_formatted_first = $tanggalFormattedFirst ?? '';
        $no = 1;
        
        foreach ($processedReports as $platform => $reports) {
            if (!empty($reports)) {
                $platformFormatted = strtoupper($platform);
                $isiPatroliBencana .= "*{$platformFormatted}*\n\n";
                
                $platformNo = 1;
                foreach ($reports as $report) {
                    $nama_akun = $report['name'];
                    $link = $report['link'];
                    $kategori = $report['category'];
                    $narasi = $report['narrative'];
                    $profiling = $report['profiling'] ?? '';
                    
                    $isiPatroliBencana .= "{$platformNo}.\tTermonitor akun {$platformFormatted} {$nama_akun} ({$link}) memposting narasi provokatif yaitu {$narasi}\n\n";
                    
                    if (!empty($nama_akun)) {
                        $isiPatroliBencana .= "Berdasarkan pendalaman, akun tersebut dikelola oleh {$nama_akun}, dengan profil sebagai berikut:\n\n";
                    }
                    
                    $isiPatroliBencana .= "*Akun {$platformFormatted} {$nama_akun}*\n";
                    
                    if (!empty($profiling)) {
                        $profilingLines = explode("\n", trim($profiling));
                        $profilingData = [];
                        foreach ($profilingLines as $line) {
                            $line = trim($line);
                            if (empty($line)) continue;
                            if (preg_match('/^([^:]+):\s*(.+)$/i', $line, $matches)) {
                                $field = trim($matches[1]);
                                $value = trim($matches[2]);
                                $fieldLower = strtolower($field);
                                $profilingData[$fieldLower] = $value;
                            }
                        }
                        
                        $getProfilingValue = function($keys) use ($profilingData, $nama_akun) {
                            foreach ($keys as $key) {
                                $keyLower = strtolower($key);
                                if (isset($profilingData[$keyLower])) {
                                    return $profilingData[$keyLower];
                                }
                            }
                            return null;
                        };
                        
                        $isiPatroliBencana .= "•NIK : " . ($getProfilingValue(['NIK']) ?? '-') . "\n";
                        $isiPatroliBencana .= "•KK : " . ($getProfilingValue(['KK']) ?? '-') . "\n";
                        $isiPatroliBencana .= "•Nama : " . ($getProfilingValue(['Nama']) ?? $nama_akun) . "\n";
                        
                        $ttl = '-';
                        $lahir = $getProfilingValue(['Lahir', 'Tempat Lahir']);
                        $tanggalLahir = $getProfilingValue(['Tanggal Lahir', 'Tgl Lahir']);
                        if ($lahir && $tanggalLahir) {
                            $ttl = $lahir . ", " . $tanggalLahir;
                        } elseif ($lahir) {
                            $ttl = $lahir;
                        }
                        $isiPatroliBencana .= "•TTL : " . $ttl . "\n";
                        $isiPatroliBencana .= "•J. Kelamin : " . ($getProfilingValue(['Jenis Kelamin', 'Jenis kelamin']) ?? '-') . "\n";
                        $isiPatroliBencana .= "•Status : " . ($getProfilingValue(['Status Nikah', 'Status']) ?? '-') . "\n";
                        $isiPatroliBencana .= "•Agama : " . ($getProfilingValue(['Agama']) ?? '-') . "\n";
                        $isiPatroliBencana .= "•Pendidikan : -\n";
                        $isiPatroliBencana .= "•Pekerjaan : " . ($getProfilingValue(['Pekerjaan']) ?? '-') . "\n";
                        $isiPatroliBencana .= "•Nama Ayah : -\n";
                        $isiPatroliBencana .= "•Nama Ibu : -\n";
                        $alamatKtp = $getProfilingValue(['Alamat Lengkap', 'Alamat', 'Kelurahan']);
                        $isiPatroliBencana .= "•Alamat KTP: " . ($alamatKtp ?? '-') . "\n";
                        $asal = '-';
                        $kabupaten = $getProfilingValue(['Kabupaten']);
                        $provinsi = $getProfilingValue(['Provinsi']);
                        if ($kabupaten && $provinsi) {
                            $asal = $kabupaten . ", " . $provinsi;
                        } elseif ($provinsi) {
                            $asal = $provinsi;
                        } elseif ($lahir) {
                            $asal = $lahir;
                        }
                        $isiPatroliBencana .= "•Asal: " . $asal . "\n";
                        $isiPatroliBencana .= "•Catatan: Akun tersebut membuat postingan yang mengkritisi pemerintah.\n\n";
                    } else {
                        $isiPatroliBencana .= "•NIK : -\n";
                        $isiPatroliBencana .= "•KK : -\n";
                        $isiPatroliBencana .= "•Nama : {$nama_akun}\n";
                        $isiPatroliBencana .= "•TTL : -\n";
                        $isiPatroliBencana .= "•J. Kelamin : -\n";
                        $isiPatroliBencana .= "•Status : -\n";
                        $isiPatroliBencana .= "•Agama : -\n";
                        $isiPatroliBencana .= "•Pendidikan : -\n";
                        $isiPatroliBencana .= "•Pekerjaan : -\n";
                        $isiPatroliBencana .= "•Nama Ayah : -\n";
                        $isiPatroliBencana .= "•Nama Ibu : -\n";
                        $isiPatroliBencana .= "•Alamat KTP: -\n";
                        $isiPatroliBencana .= "•Asal: -\n";
                        $isiPatroliBencana .= "•Catatan: Akun tersebut membuat postingan yang mengkritisi pemerintah.\n\n";
                    }
                    
                    $platformNo++;
                    $no++;
                }
            }
        }
        
        // Generate executive summary
        $totalPatroliCount = 0;
        $platformBreakdown = [];
        foreach ($processedReports as $platform => $reports) {
            if (!empty($reports)) {
                $count = count($reports);
                $totalPatroliCount += $count;
                $platformBreakdown[] = ucwords(strtolower($platform)) . " ({$count} konten)";
            }
        }
        
        $platformBreakdownText = '';
        if (count($platformBreakdown) > 1) {
            $lastPlatform = array_pop($platformBreakdown);
            $platformBreakdownText = implode(', ', $platformBreakdown) . ' dan ' . $lastPlatform;
        } else {
            $platformBreakdownText = implode('', $platformBreakdown);
        }
        
        $currentTime = date('H:i');
        $waktuFormatted = $currentTime . ' WIB';
        
        $executiveSummary = "Pada {$tanggalFormattedFirst}, di wilayah Merpati-14 termonitor sebanyak {$totalPatroliCount} konten propaganda dan provokasi di media sosial {$platformBreakdownText} yakni terkait Penanganan Bencana Alam di Medan, Sumatera Barat dan Aceh, Isu Deforestasi, serta Polemik penanganan bencana. Berdasarkan temuan tersebut Merpati-14 telah melakukan upaya RAS dan kontra propaganda dalam rangka mengeliminasi propaganda negatif.";

        $narasiPatroliBencana = <<<EOD
*Kepada Yth.: Kasuari-6*

*Dari :Merpati-14*

*Tembusan :*
*1. Kasuari-21*
*2. Kasuari-22*
*3. Kasuari-23*
*4. Kasuari-24*
*5. Kasuari-25*
*6. Kasuari-63*

*Perihal : {$judulBencana} di Wilayah Merpati-14 (Update {$tanggalFormattedFirst} Pukul {$waktuFormatted})*

*A. EXECUTIVE SUMMARY*

{$executiveSummary}

*B. HASIL PATROLI SIBER*

{$isiPatroliBencana}*C.UPAYA*

1. Melakukan upaya RAS dan melakukan Kontra narasi melalui kolom komentar.

2. Melakukan Cipkon dan Cipop Propaganda yang menarasikan kebencian terhadap pemerintah.

3. Melakukan profiling terhadap pemilik akun, afiliasi akun, dst.

*D. DOKUMENTASI LAPORAN (MATRIK AKUN DAN PROFILLING).*

Nilai : Ambon-1
DUMP.
EOD;

        // Prepare data arrays for Word and PDF generation
        $nama_akun_bencana = [];
        $kategori_bencana = [];
        $narasi_bencana = [];
        $link_bencana = [];
        $profiling_bencana = [];
        $tanggal_postingan_bencana = [];
        $wilayah_bencana = [];
        $korelasi_bencana = [];
        $afiliasi_bencana = [];
        
        foreach ($processedReports as $platform => $reports) {
            foreach ($reports as $report) {
                $nama_akun_bencana[] = $report['name'];
                $kategori_bencana[] = $report['category'];
                $narasi_bencana[] = $report['narrative'];
                $link_bencana[] = $report['link'];
                $profiling_bencana[] = $report['profiling'] ?? '';
                $tanggal_postingan_bencana[] = $report['tanggal_postingan'] ?? '';
                $wilayah_bencana[] = $report['wilayah'] ?? '';
                $korelasi_bencana[] = $report['korelasi'] ?? '';
                $afiliasi_bencana[] = $report['afiliasi'] ?? '';
            }
        }
        
        $tanggal_judul_bencana = $tanggalFormatted;
        $tanggal_bencana = $tanggalFormattedFirst;
        $foto_patroli_bencana = $screenshotPathsBencana;
        $foto_upaya_bencana = $upayaPathsBencana;
        $foto_profiling_bencana = $profilingPathsBencana;

        // Create Word file for Patroli Bencana
        try {
            $templatePathBencana = __DIR__ . '/template_word/template_patroli_bencana.docx';
            $judulWordBencana = strtoupper($judulBencana);
            $outputPathBencana = __DIR__ . '/hasil/PATROLI SIBER DAN UPAYA KONTRA OPINI TERHADAP ' . $judulWordBencana . ' UPDATE TANGGAL ' . $tanggalFormatted . '.docx';
            
            createWordFileLandy($templatePathBencana, $outputPathBencana, [
                'nama_akun' => $nama_akun_bencana,
                'tanggal_judul' => $tanggal_judul_bencana,
                'tanggal' => $tanggal_bencana,
                'kategori' => $kategori_bencana,
                'narasi' => $narasi_bencana,
                'link' => $link_bencana,
                'profiling' => $profiling_bencana,
                'tanggal_postingan' => $tanggal_postingan_bencana,
                'wilayah' => $wilayah_bencana,
                'korelasi' => $korelasi_bencana,
                'afiliasi' => $afiliasi_bencana,
                'foto_patroli' => $foto_patroli_bencana,
                'foto_upaya' => $foto_upaya_bencana,
                'foto_profiling' => $foto_profiling_bencana
            ]);
        } catch (Exception $e) {
            error_log("ERROR creating Word Bencana: " . $e->getMessage());
        }

        // Create PDF file for Patroli Bencana
        try {
            $templatePathHtmlBencana = __DIR__ . '/template_pdf/template_patroli.html';
            $judulPdfBencana = strtoupper($judulBencana);
            $outputPathPdfBencana = __DIR__ . '/hasil/LAMPIRAN ' . $judulPdfBencana . ' DI WILAYAH MERPATI - 14 PADA ' . $tanggalFormatted . '.pdf';
            
            createPdfFileLandy($templatePathHtmlBencana, $outputPathPdfBencana, $foto_patroli_bencana, $foto_upaya_bencana, $judulPdfBencana);
        } catch (Exception $e) {
            error_log("ERROR creating PDF Bencana: " . $e->getMessage());
        }
    }

    // --- Patroli Pagi ---
    if (in_array('Patroli Pagi', $_POST['reportType'])) {
        $startProgress = isset($progressRanges['Pagi']) ? $progressRanges['Pagi']['start'] : 75;
        $progressRange = isset($progressRanges['Pagi']) ? $progressRanges['Pagi']['range'] : 25;
        echo json_encode(['progress' => 'Membuat laporan Patroli Pagi...', 'percent' => $startProgress]); @ob_flush(); @flush();
        try {
            handlePatroliPagi(
                $processedReports, $tanggalFormatted, $tanggalFormattedFirst, $bulan_romawi, __DIR__ . '/hasil', $_POST, $_FILES, $screenshotPaths,
                $narasiPatroliPagi, $outputPathPagi, $outputPathPdfPagi, $startProgress, $progressRange
            );
        } catch (Exception $e) {
            error_log("Error creating Patroli Pagi report: " . $e->getMessage());
            throw new Exception("Gagal membuat laporan Patroli Pagi: " . $e->getMessage());
        }
    }
    
    // --- Laporan Khusus ---
    if (in_array('Laporan Khusus', $_POST['reportType'])) {
        $startProgress = isset($progressRanges['Khusus']) ? $progressRanges['Khusus']['start'] : 75;
        $progressRange = isset($progressRanges['Khusus']) ? $progressRanges['Khusus']['range'] : 30;
        $tema = $_POST['input_tema'] ?? '';
        
        if (empty($tema)) {
            throw new Exception('Tema laporan khusus harus diisi.');
        }
        
        echo json_encode(['progress' => 'Membuat laporan khusus...', 'percent' => $startProgress]); @ob_flush(); @flush();
        error_log("API DEBUG: About to call handleLaporanKhusus with tema: " . $tema);
        try {
            handleLaporanKhusus(
                $platformCounts, $tanggalNamaFile, $tanggalFormatted, $tanggalFormattedFirst, $hariFormatted, $bulan_romawi,
                __DIR__ . '/hasil', $_POST, $_FILES, $sheetsToRead, $narasiPatroli, $totalPatroli, $processedReports, $screenshotPaths,
                $narasiKhusus, $outputPathKhusus, $outputPathPdfKhusus, $outputPathWordPatroliKhusus, $startProgress, $progressRange,
                $tema, $fullNarasiKhusus
            );
            error_log("API DEBUG: handleLaporanKhusus completed successfully");
        } catch (Exception $e) {
            error_log("Error creating Laporan Khusus report: " . $e->getMessage());
            throw new Exception("Gagal membuat laporan khusus: " . $e->getMessage());
        }
    }
    
    // Final cleanup - delete all temporary images after processing is complete
    echo json_encode(['progress' => 'Membersihkan file sementara...', 'percent' => 95]); @ob_flush(); @flush();
    
    error_log("=== API_REKAP.PHP FINAL CLEANUP START ===");
    
    // Force garbage collection and clear file cache before cleanup
    gc_collect_cycles();
    clearstatcache();
    
    error_log("Running cleanImageDirectory for all temp folders...");
    
    // Clean up images from all temporary directories
    cleanImageDirectory(__DIR__ . '/foto');
    cleanImageDirectory(__DIR__ . '/template_word');
    cleanImageDirectory(__DIR__ . '/template_pdf');
    cleanImageDirectory(__DIR__ . '/ss'); // Also clean the screenshot directory
    
    error_log("=== API_REKAP.PHP FINAL CLEANUP END ===");

    // Debug: Log final values before sending response
    error_log("DEBUG: Final response values:");
    error_log("  - narasiPatroliLandy: " . var_export($narasiPatroliLandy, true));
    error_log("  - outputPathLandy: " . var_export($outputPathLandy, true));
    error_log("  - outputPathPdfLandy: " . var_export($outputPathPdfLandy, true));
    error_log("  - narasiPatroliBencana: " . var_export($narasiPatroliBencana, true));
    error_log("  - outputPathBencana: " . var_export($outputPathBencana, true));
    error_log("  - outputPathPdfBencana: " . var_export($outputPathPdfBencana, true));
    error_log("  - narasiPatroliPagi: " . var_export($narasiPatroliPagi, true));
    error_log("  - outputPathPagi: " . var_export($outputPathPagi, true));
    error_log("  - outputPathPdfPagi: " . var_export($outputPathPdfPagi, true));
    error_log("  - narasiKhusus: " . var_export($narasiKhusus, true));
    error_log("  - outputPathWordKhusus: " . var_export($outputPathKhusus, true));
    error_log("  - outputPathPdfKhusus: " . var_export($outputPathPdfKhusus, true));
    error_log("  - outputPathWordPatroliKhusus: " . var_export($outputPathWordPatroliKhusus, true));

    echo json_encode([
        'success' => true,
        'narrative' => $narrative,
        'outputPathWordGeneral' => $outputPathWordGeneral,
        'outputPathPdf' => $outputPathPdf,
        'outputPathWordPatroli' => $outputPathWordPatroli,
        'outputPathLandy' => $outputPathLandy,
        'outputPathPdfLandy' => $outputPathPdfLandy,
        'narasiPatroliLandy' => $narasiPatroliLandy,
        'outputPathBencana' => $outputPathBencana,
        'outputPathPdfBencana' => $outputPathPdfBencana,
        'narasiPatroliBencana' => $narasiPatroliBencana,
        'outputPathPagi' => $outputPathPagi,
        'outputPathPdfPagi' => $outputPathPdfPagi,
        'outputPathWordGeneralKhusus' => $outputPathKhusus,
        'outputPathPdfKhusus' => $outputPathPdfKhusus,
        'outputPathWordPatroliKhusus' => $outputPathWordPatroliKhusus,
        'narasiPatroliPagi' => $narasiPatroliPagi,
        'narrativeKhusus' => $narasiKhusus,
        'fullNarrativeKhusus' => $fullNarasiKhusus
    ]);
} catch (\Exception $e) {
    // Log error and attempt to clean up even if processing failed
    error_log("Error in processing: " . $e->getMessage());
    
    // Try to clean up temporary files even if there was an error
    try {
        cleanImageDirectory(__DIR__ . '/foto');
        cleanImageDirectory(__DIR__ . '/template_word');
        cleanImageDirectory(__DIR__ . '/template_pdf');
        cleanImageDirectory(__DIR__ . '/ss');
    } catch (\Exception $cleanupError) {
        error_log("Error during cleanup: " . $cleanupError->getMessage());
    }
    
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
