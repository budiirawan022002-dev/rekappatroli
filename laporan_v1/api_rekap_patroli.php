<?php
require 'vendor/autoload.php';
require 'fungsi_proses.php';
require 'fungsi_konversi.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'data' => null,
    'files' => []
];

try {
    error_log("=== API START V3 CORRECT FILE ===");
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    if (!isset($_POST['reportType'])) {
        throw new Exception('reportType is required.');
    }
    
    error_log("Report types received: " . implode(', ', $_POST['reportType']));

    // Clean hasil & foto folders
    $hasilFolder = __DIR__ . '/hasil';
    $fotoFolder = __DIR__ . '/foto';
    cleanFolder($hasilFolder);
    

    // Sanitize and process patrol report input
    $rawReport = $_POST['patrolReport'] ?? '';
    error_log("Raw report length: " . strlen($rawReport));
    
    // Normalize line breaks for cross-browser compatibility (Firefox uses \r\n, Chrome uses \n)
    $rawReport = str_replace(["\r\n", "\r"], "\n", $rawReport);
    
    // Check if Patroli Landy or Patroli Bencana is selected
    $reportTypes = $_POST['reportType'] ?? [];
    $isPatroliLandy = in_array('Patroli Landy', $reportTypes);
    $isPatroliBencana = in_array('Patroli Bencana', $reportTypes);
    
    // Check if multi-line profiling format is used (for Landy or Bencana)
    // Support both old format (profiling:\nNama:) and new format (profiling:\nNik: or profiling:\nKK:)
    $hasMultiLineProfiling = false;
    if (($isPatroliLandy || $isPatroliBencana) && (
        preg_match('/profiling:\s*\n\s*(Nama|Nik|KK|Jenis\s+kelamin|Lahir|Tanggal\s+Lahir|Pekerjaan|Provinsi|Kabupaten|Kecamatan|Kelurahan|Alamat\s+Lengkap)\s*:/is', $rawReport) ||
        preg_match('/profiling:\s*\n\s*[A-Za-z\s]+:\s*/is', $rawReport)
    )) {
        $hasMultiLineProfiling = true;
        error_log("✅ DETECTED MULTI-LINE PROFILING FORMAT - Using parseLandyMultiLineProfiling()");
        
        // Use special parsing for multi-line profiling (preserves labels)
        $hasilPatroli = parseLandyMultiLineProfiling($rawReport);
        $groupedReports = $hasilPatroli['groupedReports'];
        $processedReports = $hasilPatroli['processedReports'];
        
        // Convert profiling data to text format for display
        foreach ($processedReports as $platform => &$reports) {
            foreach ($reports as &$report) {
                if (isset($report['profiling']) && is_array($report['profiling'])) {
                    // Convert array profiling to text format
                    $profilingText = '';
                    if (isset($report['profiling_text'])) {
                        $profilingText = $report['profiling_text'];
                    } else {
                        // Build profiling text from array
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
        unset($reports, $report); // Break reference
    } else {
        // Use standard parsing (strip labels first)
        // Debug: Log before stripping labels
        error_log("Before stripping labels (first 500 chars): " . substr($rawReport, 0, 500));
        
        // Strip optional field labels - support multiple variations
        $patterns = [
            '/^nama[\s_-]*akun\s*:\s*/im',
            '/^link\s*:\s*/im',
            '/^kategori\s*:\s*/im',
            '/^narasi\s*:\s*/im',
            '/^profiling\s*:\s*/im',
            '/^tanggal[\s_-]*postingan\s*:\s*/im',
            '/^wilayah\s*:\s*/im',
            '/^korelasi\s*:\s*/im',
            '/^afiliasi\s*:\s*/im'
        ];
        
        foreach ($patterns as $pattern) {
            $rawReport = preg_replace($pattern, '', $rawReport);
        }
        
        // Debug: Log after stripping labels
        error_log("After stripping labels (first 500 chars): " . substr($rawReport, 0, 500));
        
        $lines = explode("\n", $rawReport);
        $platforms = ['FACEBOOK', 'INSTAGRAM', 'X', 'TIKTOK', 'SNACKVIDEO', 'YOUTUBE'];
        $groupedReports = array_fill_keys($platforms, []);
        $currentReport = [];
        $processedReports = array_fill_keys($platforms, []);
        $expectedFields = ($isPatroliLandy || $isPatroliBencana) ? 9 : 4;
    
    // Debug: Log input data
    error_log("Patroli Landy Debug - Raw input length: " . strlen($rawReport));
    error_log("Patroli Landy Debug - Total lines: " . count($lines));
    error_log("Patroli Landy Debug - Expected fields: " . $expectedFields);
    error_log("Patroli Landy Debug - Is Patroli Landy: " . ($isPatroliLandy ? 'Yes' : 'No'));
    error_log("Patroli Landy Debug - Raw input: " . substr($rawReport, 0, 200) . "...");
    
    // Debug: Log each line
    foreach ($lines as $i => $line) {
        error_log("Patroli Landy Debug - Line $i: '" . trim($line) . "'");
    }
    
    // Debug: Log total lines and expected fields
    error_log("Patroli Landy Debug - Total lines: " . count($lines));
    error_log("Patroli Landy Debug - Expected fields: " . $expectedFields);
    
    // Debug: Check if input is empty
    if (empty($rawReport)) {
        error_log("Patroli Landy Debug - Raw input is EMPTY!");
    } else {
        error_log("Patroli Landy Debug - Raw input is NOT empty, length: " . strlen($rawReport));
    }
    
    // Debug: Check report types
    error_log("Patroli Landy Debug - Report types: " . implode(', ', $reportTypes));
    error_log("Patroli Landy Debug - Is Patroli Landy selected: " . ($isPatroliLandy ? 'YES' : 'NO'));
    error_log("Patroli Bencana Debug - Is Patroli Bencana selected: " . ($isPatroliBencana ? 'YES' : 'NO'));
    error_log("Has Multi-line Profiling: " . ($hasMultiLineProfiling ? 'YES' : 'NO'));

    // Only process standard format if not using multi-line profiling
    if (!$hasMultiLineProfiling) {
        foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) {
            // Debug: Log current report when we hit empty line
            if (count($currentReport) > 0) {
                error_log("Patroli Landy Debug - Found empty line, current report has " . count($currentReport) . " fields (expected: $expectedFields)");
                error_log("Patroli Landy Debug - Current report: " . implode(' | ', $currentReport));
            }
            
            if (count($currentReport) === $expectedFields) {
                if ($isPatroliLandy || $isPatroliBencana) {
                    [$name, $link, $category, $narrative, $profiling, $tanggal_postingan, $wilayah, $korelasi, $afiliasi] = $currentReport;
                } else {
                    [$name, $link, $category, $narrative] = $currentReport;
                    $profiling = $tanggal_postingan = $wilayah = $korelasi = $afiliasi = '';
                }
                
                if (!filter_var($link, FILTER_VALIDATE_URL)) {
                    $currentReport = [];
                    continue;
                }
                $link = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
                $name = trim($name);
                $category = trim($category);
                $narrative = trim($narrative);
                if ($isPatroliLandy || $isPatroliBencana) {
                    $profiling = trim($profiling);
                    $tanggal_postingan = trim($tanggal_postingan);
                    $wilayah = trim($wilayah);
                    $korelasi = trim($korelasi);
                    $afiliasi = trim($afiliasi);
                }
                
                $platform = 'LAINNYA';
                if (strpos($link, 'facebook.com') !== false) $platform = 'FACEBOOK';
                elseif (strpos($link, 'instagram.com') !== false) $platform = 'INSTAGRAM';
                elseif (strpos($link, 'x.com') !== false) $platform = 'X';
                elseif (strpos($link, 'tiktok.com') !== false) $platform = 'TIKTOK';
                elseif (strpos($link, 'snackvideo.com') !== false) $platform = 'SNACKVIDEO';
                elseif (strpos($link, 'youtube.com') !== false) $platform = 'YOUTUBE';
                
                $groupedReports[$platform][] = "Termonitor Akun {$name} ({$link}) membagikan postingan {$category} dengan  {$narrative}";
                $processedReports[$platform][] = [
                    'name' => $name,
                    'link' => $link,
                    'category' => $category,
                    'narrative' => $narrative,
                    'profiling' => $profiling,
                    'tanggal_postingan' => $tanggal_postingan,
                    'wilayah' => $wilayah,
                    'korelasi' => $korelasi,
                    'afiliasi' => $afiliasi
                ];
                
                // Debug: Log successful processing
                error_log("Patroli Landy Debug - Successfully processed report for platform: $platform, name: $name");
                
                $currentReport = [];
            }
            continue;
        }
        $currentReport[] = $line;
    }
    if (count($currentReport) === $expectedFields) {
        if ($isPatroliLandy || $isPatroliBencana) {
            [$name, $link, $category, $narrative, $profiling, $tanggal_postingan, $wilayah, $korelasi, $afiliasi] = $currentReport;
        } else {
            [$name, $link, $category, $narrative] = $currentReport;
            $profiling = $tanggal_postingan = $wilayah = $korelasi = $afiliasi = '';
        }
        
        if (filter_var($link, FILTER_VALIDATE_URL)) {
            $link = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
            $name = trim($name);
            $category = trim($category);
            $narrative = trim($narrative);
            if ($isPatroliLandy || $isPatroliBencana) {
                $profiling = trim($profiling);
                $tanggal_postingan = trim($tanggal_postingan);
                $wilayah = trim($wilayah);
                $korelasi = trim($korelasi);
                $afiliasi = trim($afiliasi);
            }
            
            $platform = 'OTHER';
            if (strpos($link, 'facebook.com') !== false) $platform = 'FACEBOOK';
            elseif (strpos($link, 'instagram.com') !== false) $platform = 'INSTAGRAM';
            elseif (strpos($link, 'x.com') !== false) $platform = 'X';
            elseif (strpos($link, 'tiktok.com') !== false) $platform = 'TIKTOK';
            elseif (strpos($link, 'snackvideo.com') !== false) $platform = 'SNACKVIDEO';
            elseif (strpos($link, 'youtube.com') !== false) $platform = 'YOUTUBE';
            
            $groupedReports[$platform][] = "Termonitor Akun {$name} ({$link}) membagikan postingan {$category} dengan {$narrative}";
            $processedReports[$platform][] = [
                'name' => $name,
                'link' => $link,
                'category' => $category,
                'narrative' => $narrative,
                'profiling' => $profiling,
                'tanggal_postingan' => $tanggal_postingan,
                'wilayah' => $wilayah,
                'korelasi' => $korelasi,
                'afiliasi' => $afiliasi
            ];
        }
    }
    } // End of standard parsing block
    } // End else: not multi-line profiling (pair with if/else starting ~line 49)

    // Generate narrative for each platform
    $narasiPatroli = "";
    $totalPatroli = 0;
    $platformCounts = [];
    foreach ($groupedReports as $platform => $reports) {
        if (!empty($reports)) {
            $platformFormatted = ucwords(strtolower($platform));
            $narasiPatroli .= "*{$platformFormatted}*\n\n";
            if (count($reports) === 1) {
                $narasiPatroli .= "{$reports[0]}\n\n";
            } else {
                foreach ($reports as $index => $report) {
                    $narasiPatroli .= ($index + 1) . ". {$report}\n\n";
                }
            }
            $platformCounts[$platform] = count($reports);
            $totalPatroli += count($reports);
        }
    }

    // proses cipop
    $sheetsToRead = ['FACEBOOK', 'INSTAGRAM', 'TWITTER', 'TIKTOK', 'SNACKVIDEO', 'YOUTUBE'];
    $tanggalInput = $_POST['tanggal'] ?? date('Y-m-d');
    $tanggalFormatted = strtoupper(formatTanggalIndonesia($tanggalInput));
    $tanggalNamaFile = date('dmY', strtotime($tanggalInput));
    $tanggalFormattedFirst = ucfirst(formatTanggalIndonesia($tanggalInput));
    $hariFormatted = getHariIndonesia($tanggalInput);

    // Laporan KBD
    if (isset($_POST['reportType']) && in_array('Laporan KBD', $_POST['reportType'])) {
        $totalPatroliNarrative = [];
        foreach ($platformCounts as $platform => $count) {
            $platformFormatted = ucwords(strtolower($platform));
            $totalPatroliNarrative[] = "{$platformFormatted} ({$count} konten)";
        }
        if (count($totalPatroliNarrative) > 1) {
            $lastPlatform = array_pop($totalPatroliNarrative);
            $totalPatroliNarrativeString = implode(', ', $totalPatroliNarrative) . ' dan ' . $lastPlatform;
        } else {
            $totalPatroliNarrativeString = implode('', $totalPatroliNarrative);
        }
        $fileName = "{$tanggalNamaFile} - PELAKSANAAN CIPKON DAN CIPOP MELALUI MEDIA SOSIAL DALAM RANGKA KONTER OPINI NEGATIF TERHADAP PRESIDEN PRABOWO SUBIANTO DI WILAYAH MERPATI – 14";
        $result = prosesExcelFiles($_FILES['excelFiles'], $sheetsToRead);
        $dataAkun = $result['dataAkun'];
        $dataLink = $result['dataLink'];
        $totalPerSheet = $result['totalPerSheet'];
        $jumlahAkunperSheet = [];
        $jumlahLinkperSheet = [];
        $jumlahDataPerSheet = [];
        foreach ($dataAkun as $sheetName => $groupedData) {
            $jumlahDataPerSheet[$sheetName]['totalAkun'] = count($groupedData);
            $jumlahAkunperSheet[$sheetName] = count($groupedData);
        }
        foreach ($dataLink as $sheetName => $dataRows) {
            $jumlahDataPerSheet[$sheetName]['totalLink'] = count($dataRows);
            $jumlahLinkperSheet[$sheetName] = count($dataRows);
        }
        $narrative = <<<EOD
*Kepada: Yth.*
*1. Rajawali*
*2. Elang*

*Dari : Merpati - 14*

*Tembusan : Yth.*
*1. Kasuari – 2*
*2. Kasuari – 6*
*3. Kasuari – 9*

*Perihal : PELAKSANAAN CIPKON DAN CIPOP MELALUI MEDIA SOSIAL DALAM RANGKA KONTER OPINI NEGATIF TERHADAP PRESIDEN PRABOWO SUBIANTO DI WIL. MERPATI – 14*

*A. EXECUTIVE SUMMARY*

Pada {$tanggalFormattedFirst}, di wilayah Merpati-14 termonitor sebanyak {$totalPatroli} konten provokasi terhadap Presiden Prabowo Subianto beserta keluarganya di media sosial yaitu {$totalPatroliNarrativeString}. Berdasarkan temuan tersebut Tim Delta Merpati-14 telah melakukan upaya RAS dan kontra propaganda dalam rangka mengeliminir propaganda negatif.

*B. HASIL PATROLI CYBER*

{$narasiPatroli}

*C. LANGKAH TINDAK*

Tim Merpati 14 memasifkan Kontra opini dengan tema konten *Program Pemerintahan Presiden Prabowo Subianto* dengan total Facebook {$jumlahLinkperSheet['FACEBOOK']} link, X / Twitter {$jumlahLinkperSheet['TWITTER']} link, Instagram {$jumlahLinkperSheet['INSTAGRAM']} link, Tiktok {$jumlahLinkperSheet['TIKTOK']} link, Snackvideo {$jumlahLinkperSheet['SNACKVIDEO']} link dan Youtube {$jumlahLinkperSheet['YOUTUBE']} link.

Nilai : Ambon-1

*DUMP Merpati-14*
EOD;

        $templatePathWordGeneral = __DIR__ . '/template_word/template_viral.docx';
        $outputPathWordGeneral = $hasilFolder . "/{$fileName}.docx";
        createWordFile($templatePathWordGeneral, $outputPathWordGeneral, $tanggalFormatted, $jumlahDataPerSheet, $dataLink);

        $templatePathHtml = __DIR__ . '/template_pdf/template_kbd.html';
        $outputPathPdf = $hasilFolder . "/{$fileName}.pdf";
        createPdfFile($templatePathHtml, $outputPathPdf, $tanggalFormatted, $hariFormatted, $tanggalFormattedFirst, $jumlahLinkperSheet, $imagePaths);

        $templatePathWordPatroli = __DIR__ . '/template_word/template_Patroli_kbd.docx';
        $outputPathWordPatroli = $hasilFolder . "/HASIL PATROLI SIBER TERKAIT OPINI NEGATIF TERHADAP PRESIDEN PRABOWO SUBIANTO DI WIL. MERPATI – 14 TANGGAL {$tanggalFormatted}.docx";
        createWordFilePatroli($templatePathWordPatroli, $outputPathWordPatroli, $tanggalFormatted, $processedReports, $screenshotPaths);

        $response['files'] = [
            'word_general' => basename($outputPathWordGeneral),
            'pdf' => basename($outputPathPdf),
            'word_patroli' => basename($outputPathWordPatroli)
        ];
        $response['narrative'] = $narrative;
    }

    // Patroli Landy
    if (isset($_POST['reportType']) && in_array('Patroli Landy', $_POST['reportType'])) {
        error_log("=== PATROLI LANDY PROCESSING START ===");
        
        // Get judul Landy (dari dropdown atau custom input)
        $judulLandy = '';
        $judulLandyDropdown = $_POST['judulLandy'] ?? '';
        $judulLandyCustom = $_POST['judulLandyCustom'] ?? '';
        
        // Gunakan custom input jika dropdown pilih "custom", otherwise gunakan dropdown value
        $judulLandy = ($judulLandyDropdown === 'custom') ? trim($judulLandyCustom) : $judulLandyDropdown;
        
        // Fallback jika kosong
        if (empty($judulLandy)) {
            $judulLandy = 'Temuan Akun Medsos Narasi Negatif MBG';
        }
        
        error_log("Judul Landy: " . $judulLandy);
        
        // Initialize screenshot and upaya paths
        $screenshotPaths = [];
        $upayaPaths = [];
        
        // Process screenshot files if uploaded
        if (isset($_FILES['screenshotPatroli']) && !empty($_FILES['screenshotPatroli']['name'][0])) {
            error_log("Screenshot patroli files uploaded: " . count($_FILES['screenshotPatroli']['name']));
            for ($i = 0; $i < count($_FILES['screenshotPatroli']['name']); $i++) {
                if (isset($_FILES['screenshotPatroli']['tmp_name'][$i]) && $_FILES['screenshotPatroli']['error'][$i] === UPLOAD_ERR_OK) {
                    $originalPath = $_FILES['screenshotPatroli']['tmp_name'][$i];
                    $fileName = 'patroli_landy_' . time() . '_' . $i . '_' . basename($_FILES['screenshotPatroli']['name'][$i]);
                    $destinationPath = $fotoFolder . '/' . $fileName;
                    if (move_uploaded_file($originalPath, $destinationPath)) {
                        $screenshotPaths[] = $destinationPath;
                        error_log("Screenshot saved: $destinationPath");
                    } else {
                        error_log("ERROR: Failed to save screenshot: " . $_FILES['screenshotPatroli']['name'][$i]);
                    }
                }
            }
        } else {
            error_log("WARNING: No screenshot patroli files uploaded for Landy");
        }
        
        // Process RAS/Upaya files
        if (isset($_FILES['rasFiles']) && !empty($_FILES['rasFiles']['name'][0])) {
            error_log("RAS/Upaya files uploaded: " . count($_FILES['rasFiles']['name']));
            for ($i = 0; $i < count($_FILES['rasFiles']['name']); $i++) {
                if (isset($_FILES['rasFiles']['tmp_name'][$i]) && $_FILES['rasFiles']['error'][$i] === UPLOAD_ERR_OK) {
                    $originalPath = $_FILES['rasFiles']['tmp_name'][$i];
                    $fileName = 'ras_landy_' . time() . '_' . $i . '_' . basename($_FILES['rasFiles']['name'][$i]);
                    $destinationPath = $fotoFolder . '/' . $fileName;
                    if (move_uploaded_file($originalPath, $destinationPath)) {
                        $upayaPaths[] = $destinationPath;
                        error_log("RAS/Upaya saved: $destinationPath");
                    } else {
                        error_log("ERROR: Failed to save RAS/Upaya: " . $_FILES['rasFiles']['name'][$i]);
                    }
                }
            }
        } else {
            error_log("WARNING: No RAS/Upaya files uploaded for Landy");
        }
        
        error_log("Screenshot paths count: " . count($screenshotPaths));
        error_log("Upaya paths count: " . count($upayaPaths));
        
        $isiPatroliLandy = "";
        $tanggal_formatted_first = $tanggalFormattedFirst ?? '';
        $no = 1;
        
        // Debug: Log processed reports
        error_log("Patroli Landy Debug - Total platforms: " . count($processedReports));
        foreach ($processedReports as $platform => $reports) {
            error_log("Patroli Landy Debug - Platform {$platform}: " . count($reports) . " reports");
        }
        
        foreach ($processedReports as $platform => $reports) {
            if (!empty($reports)) {
                $platformFormatted = strtoupper($platform);
                $isiPatroliLandy .= "*{$platformFormatted}*\n\n";
                
                $platformNo = 1;
                foreach ($reports as $report) {
                    $nama_akun = $report['name'];
                    $link = $report['link'];
                    $kategori = $report['category'];
                    $narasi = $report['narrative'];
                    $profiling = $report['profiling'] ?? '';
                    $tanggal_postingan = $report['tanggal_postingan'] ?? '';
                    $wilayah = $report['wilayah'] ?? '';
                    $korelasi = $report['korelasi'] ?? '';
                    $afiliasi = $report['afiliasi'] ?? '';
                    
                    // Format ringkasan
                    $isiPatroliLandy .= "{$platformNo}.\tTermonitor akun {$platformFormatted} {$nama_akun} ({$link}) memposting narasi provokatif yaitu {$narasi}\n\n";
                    
                    if (!empty($nama_akun)) {
                        $isiPatroliLandy .= "Berdasarkan pendalaman, akun tersebut dikelola oleh {$nama_akun}, dengan profil sebagai berikut:\n\n";
                    }
                    
                    // Format detail
                    $isiPatroliLandy .= "*Akun {$platform} {$nama_akun}*\n";
                    $isiPatroliLandy .= "a. Tanggal Postingan: {$tanggal_postingan}\n";
                    $isiPatroliLandy .= "b. Wilayah: {$wilayah}\n";
                    $isiPatroliLandy .= "c. Nama Akun: {$nama_akun}\n";
                    $isiPatroliLandy .= "d. Link Akun: {$link}\n";
                    $isiPatroliLandy .= "e. Resume Narasi Propaganda: {$narasi}\n";
                    $isiPatroliLandy .= "f. Profiling Singkat Akun: {$profiling}\n";
                    $isiPatroliLandy .= "g. Korelasi Dengan Akun Lainnya: {$korelasi}\n";
                    $isiPatroliLandy .= "h. Afiliasi Dengan Influencer/Tokoh Prominen/Pemilik Pasukan Buzzer: {$afiliasi}\n\n";
                    
                    $platformNo++;
                    $no++;
                }
            }
        }
        
        // Debug: Log final content
        error_log("Patroli Landy Debug - Final content length: " . strlen($isiPatroliLandy));
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
        
        $executiveSummary = "Pada {$tanggalFormattedFirst}, di wilayah Merpati-14 termonitor sebanyak {$totalPatroliCount} konten propaganda dan provokasi di media sosial {$platformBreakdownText}. Berdasarkan temuan tersebut Merpati-14 telah melakukan upaya RAS dan kontra propaganda dalam rangka mengeliminasi propaganda negatif.";

        $narasiPatroliLandy = <<<EOD
*Kepada Yth: Rajawali*

*Dari: Merpati-14*

*Tembusan : Yth.*
*1. Elang*
*2. Kasuari-6*
*3. Kasuari-9*

*Perihal : Laporan {$judulLandy} di Wilayah Prov. Jambi Update {$tanggalFormattedFirst}*

*A. EXECUTIVE SUMMARY*

{$executiveSummary}

*B. KEGIATAN PATROLI SIBER*

{$isiPatroliLandy}
*B.UPAYA*

1.Melakukan pemantauan terhadap akun yang menyebarkan berita atau isu yang menyudutkan pemerintahan.

2.Melakukan pemetaan terhadap postingan ataupun berita tendensius dan hoax serta penyebarnya yang tersebar di dunia maya.

3.Melakukan kontra dan report terhadap isu sensitif yang efeknya diperkirakan cukup besar dan nyata baik dengan tulisan maupun dengan meme yang bersifat menarik.

*DUMP. TTD: Merpati - 14*
EOD;

        $nama_akun = [];
        $kategori = [];
        $narasi = [];
        $link = [];
        $profiling = [];
        $tanggal_postingan = [];
        $wilayah = [];
        $korelasi = [];
        $afiliasi = [];
        foreach ($processedReports as $platform => $reports) {
            foreach ($reports as $report) {
                $nama_akun[] = $report['name'];
                $kategori[] = $report['category'];
                $narasi[] = $report['narrative'];
                $link[] = $report['link'];
                $profiling[] = $report['profiling'] ?? '';
                $tanggal_postingan[] = $report['tanggal_postingan'] ?? '';
                $wilayah[] = $report['wilayah'] ?? '';
                $korelasi[] = $report['korelasi'] ?? '';
                $afiliasi[] = $report['afiliasi'] ?? '';
            }
        }
        $tanggal_judul = $tanggalFormatted;
        $tanggal = $tanggalFormattedFirst;
        $foto_patroli = $screenshotPaths;
        $foto_upaya = $upayaPaths;
        
        error_log("Data arrays - nama_akun: " . count($nama_akun) . ", foto_patroli: " . count($foto_patroli) . ", foto_upaya: " . count($foto_upaya));

        try {
            $templatePathLandy = __DIR__ . '/template_word/template_patroli_landy.docx';
            $outputPathLandy = $hasilFolder . "/PATROLI SIBER DAN UPAYA KONTRA OPINI TERHADAP AKUN YANG MEMPOSTING NARASI NEGATIF MBG DI WILAYAH MERPATI-14 UPDATE TANGGAL {$tanggalFormatted}.docx";
            
            error_log("Attempting to create Word file at: $outputPathLandy");
            
            createWordFileLandy($templatePathLandy, $outputPathLandy, [
                'nama_akun' => $nama_akun,
                'tanggal_judul' => $tanggal_judul,
                'tanggal' => $tanggal,
                'kategori' => $kategori,
                'narasi' => $narasi,
                'link' => $link,
                'profiling' => $profiling,
                'tanggal_postingan' => $tanggal_postingan,
                'wilayah' => $wilayah,
                'korelasi' => $korelasi,
                'afiliasi' => $afiliasi,
                'foto_patroli' => $foto_patroli,
                'foto_upaya' => $foto_upaya
            ]);
            
            if (file_exists($outputPathLandy)) {
                error_log("Word file created successfully: $outputPathLandy");
                $response['files']['word_landy'] = basename($outputPathLandy);
                $response['outputPathLandy'] = $outputPathLandy; // Also add to root level for compatibility
            } else {
                error_log("ERROR: Word file was not created at: $outputPathLandy");
            }
        } catch (Exception $e) {
            error_log("ERROR creating Word Landy: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
        }

        try {
            $templatePathHtmlLandy = __DIR__ . '/template_pdf/template_patroli.html';
            $outputPathPdfLandy = $hasilFolder . "/LAMPIRAN PATROLI SIBER DAN UPAYA KONTRA OPINI TERHADAP AKUN YANG MENDISKREDITKAN PRESIDEN PRABOWO SUBIANTO DI WILAYAH MERPATI - 14 PADA {$tanggalFormatted}.pdf";
            
            error_log("Attempting to create PDF file at: $outputPathPdfLandy");
            
            createPdfFileLandy($templatePathHtmlLandy, $outputPathPdfLandy, $foto_patroli, $foto_upaya);
            
            if (file_exists($outputPathPdfLandy)) {
                error_log("PDF file created successfully: $outputPathPdfLandy");
                $response['files']['pdf_landy'] = basename($outputPathPdfLandy);
                $response['outputPathPdfLandy'] = $outputPathPdfLandy; // Also add to root level for compatibility
            } else {
                error_log("ERROR: PDF file was not created at: $outputPathPdfLandy");
            }
        } catch (Exception $e) {
            error_log("ERROR creating PDF Landy: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
        }
        
        $response['narasiPatroliLandy'] = $narasiPatroliLandy;
    }

    // Patroli Bencana
    if (isset($_POST['reportType']) && in_array('Patroli Bencana', $_POST['reportType'])) {
        error_log("=== PATROLI BENCANA PROCESSING START ===");
        
        // Get judul Bencana (dari dropdown atau custom input)
        $judulBencana = '';
        $judulBencanaDropdown = $_POST['judulBencana'] ?? '';
        $judulBencanaCustom = $_POST['judulBencanaCustom'] ?? '';
        
        // Gunakan custom input jika dropdown pilih "custom", otherwise gunakan dropdown value
        $judulBencana = ($judulBencanaDropdown === 'custom') ? trim($judulBencanaCustom) : $judulBencanaDropdown;
        
        // Fallback jika kosong
        if (empty($judulBencana)) {
            $judulBencana = 'Patroli Siber Konten Provokatif Mendiskreditkan Pemerintah';
        }
        
        error_log("Judul Bencana: " . $judulBencana);
        
        // Initialize screenshot, upaya, and profiling paths
        $screenshotPathsBencana = [];
        $upayaPathsBencana = [];
        $profilingPathsBencana = [];
        
        // Process screenshot files if uploaded
        if (isset($_FILES['screenshotPatroli']) && !empty($_FILES['screenshotPatroli']['name'][0])) {
            error_log("Screenshot patroli files uploaded for Bencana: " . count($_FILES['screenshotPatroli']['name']));
            for ($i = 0; $i < count($_FILES['screenshotPatroli']['name']); $i++) {
                if (isset($_FILES['screenshotPatroli']['tmp_name'][$i]) && $_FILES['screenshotPatroli']['error'][$i] === UPLOAD_ERR_OK) {
                    $originalPath = $_FILES['screenshotPatroli']['tmp_name'][$i];
                    $fileName = 'patroli_bencana_' . time() . '_' . $i . '_' . basename($_FILES['screenshotPatroli']['name'][$i]);
                    $destinationPath = $fotoFolder . '/' . $fileName;
                    if (move_uploaded_file($originalPath, $destinationPath)) {
                        $screenshotPathsBencana[] = $destinationPath;
                        error_log("Screenshot saved: $destinationPath");
                    }
                }
            }
        }
        
        // Process RAS/Upaya files
        if (isset($_FILES['rasFiles']) && !empty($_FILES['rasFiles']['name'][0])) {
            error_log("RAS/Upaya files uploaded for Bencana: " . count($_FILES['rasFiles']['name']));
            for ($i = 0; $i < count($_FILES['rasFiles']['name']); $i++) {
                if (isset($_FILES['rasFiles']['tmp_name'][$i]) && $_FILES['rasFiles']['error'][$i] === UPLOAD_ERR_OK) {
                    $originalPath = $_FILES['rasFiles']['tmp_name'][$i];
                    $fileName = 'ras_bencana_' . time() . '_' . $i . '_' . basename($_FILES['rasFiles']['name'][$i]);
                    $destinationPath = $fotoFolder . '/' . $fileName;
                    if (move_uploaded_file($originalPath, $destinationPath)) {
                        $upayaPathsBencana[] = $destinationPath;
                        error_log("RAS/Upaya saved: $destinationPath");
                    }
                }
            }
        }
        
        // Process Profiling files
        if (isset($_FILES['profilingFiles']) && !empty($_FILES['profilingFiles']['name'][0])) {
            error_log("Profiling files uploaded for Bencana: " . count($_FILES['profilingFiles']['name']));
            $timestamp = date('His') . '_' . substr(microtime(true) * 1000, -3);
            for ($i = 0; $i < count($_FILES['profilingFiles']['name']); $i++) {
                if (isset($_FILES['profilingFiles']['tmp_name'][$i]) && $_FILES['profilingFiles']['error'][$i] === UPLOAD_ERR_OK) {
                    $originalPath = $_FILES['profilingFiles']['tmp_name'][$i];
                    $originalName = $_FILES['profilingFiles']['name'][$i];
                    $pathInfo = pathinfo($originalName);
                    $fileName = 'profiling_bencana_' . $timestamp . '_' . ($i + 1) . '_' . $pathInfo['filename'] . '.' . ($pathInfo['extension'] ?? 'jpg');
                    $destinationPath = __DIR__ . '/template_word/' . $fileName;
                    
                    // Ensure template_word directory exists
                    if (!is_dir(__DIR__ . '/template_word')) {
                        mkdir(__DIR__ . '/template_word', 0755, true);
                    }
                    
                    if (copy($originalPath, $destinationPath)) {
                        $profilingPathsBencana[] = $destinationPath;
                        error_log("Profiling file saved: $destinationPath");
                    } else {
                        error_log("ERROR: Failed to copy profiling file: " . $originalName);
                    }
                }
            }
        } else {
            error_log("WARNING: No profiling files uploaded for Bencana");
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
                    
                    // Format ringkasan
                    $isiPatroliBencana .= "{$platformNo}.\tTermonitor akun {$platformFormatted} {$nama_akun} ({$link}) memposting narasi provokatif yaitu {$narasi}\n\n";
                    
                    if (!empty($nama_akun)) {
                        $isiPatroliBencana .= "Berdasarkan pendalaman, akun tersebut dikelola oleh {$nama_akun}, dengan profil sebagai berikut:\n\n";
                    }
                    
                    // Format detail profiling sesuai format baru
                    $isiPatroliBencana .= "*Akun {$platformFormatted} {$nama_akun}*\n";
                    
                    // Parse profiling data dan format sesuai kebutuhan
                    if (!empty($profiling)) {
                        // Jika profiling dalam format multi-line (Nama: ..., Jenis Kelamin: ..., dll)
                        // Konversi ke format bullet point yang diinginkan
                        $profilingLines = explode("\n", trim($profiling));
                        $profilingFormatted = "";
                        
                        // Parse setiap baris profiling (case-insensitive)
                        $profilingData = [];
                        foreach ($profilingLines as $line) {
                            $line = trim($line);
                            if (empty($line)) continue;
                            
                            // Parse format "Field: Value" (case-insensitive)
                            if (preg_match('/^([^:]+):\s*(.+)$/i', $line, $matches)) {
                                $field = trim($matches[1]);
                                $value = trim($matches[2]);
                                // Normalize field name (case-insensitive matching)
                                $fieldLower = strtolower($field);
                                $profilingData[$fieldLower] = $value;
                            }
                        }
                        
                        // Helper function untuk mendapatkan value dengan case-insensitive
                        $getProfilingValue = function($keys) use ($profilingData, $nama_akun) {
                            foreach ($keys as $key) {
                                $keyLower = strtolower($key);
                                if (isset($profilingData[$keyLower])) {
                                    return $profilingData[$keyLower];
                                }
                            }
                            return null;
                        };
                        
                        // Format sesuai contoh yang diminta
                        $isiPatroliBencana .= "•NIK : " . ($getProfilingValue(['NIK']) ?? '-') . "\n";
                        $isiPatroliBencana .= "•KK : " . ($getProfilingValue(['KK']) ?? '-') . "\n";
                        $isiPatroliBencana .= "•Nama : " . ($getProfilingValue(['Nama']) ?? $nama_akun) . "\n";
                        
                        // Format TTL dari Tanggal Lahir dan Lahir
                        $ttl = '-';
                        $lahir = $getProfilingValue(['Lahir', 'Tempat Lahir']);
                        $tanggalLahir = $getProfilingValue(['Tanggal Lahir', 'Tgl Lahir']);
                        if ($lahir && $tanggalLahir) {
                            $ttl = $lahir . ", " . $tanggalLahir;
                        } elseif ($lahir) {
                            $ttl = $lahir;
                        }
                        $isiPatroliBencana .= "•TTL : " . $ttl . "\n";
                        
                        $jenisKelamin = $getProfilingValue(['Jenis Kelamin', 'Jenis kelamin', 'Jenis Kelamin']);
                        $isiPatroliBencana .= "•J. Kelamin : " . ($jenisKelamin ?? '-') . "\n";
                        $isiPatroliBencana .= "•Status : " . ($getProfilingValue(['Status Nikah', 'Status']) ?? '-') . "\n";
                        $isiPatroliBencana .= "•Agama : " . ($getProfilingValue(['Agama']) ?? '-') . "\n";
                        $isiPatroliBencana .= "•Pendidikan : -\n"; // Tidak ada di input
                        $isiPatroliBencana .= "•Pekerjaan : " . ($getProfilingValue(['Pekerjaan']) ?? '-') . "\n";
                        $isiPatroliBencana .= "•Nama Ayah : -\n"; // Tidak ada di input
                        $isiPatroliBencana .= "•Nama Ibu : -\n"; // Tidak ada di input
                        
                        // Format Alamat KTP dari data yang ada
                        $alamatKtp = $getProfilingValue(['Alamat Lengkap', 'Alamat', 'Kelurahan']);
                        $isiPatroliBencana .= "•Alamat KTP: " . ($alamatKtp ?? '-') . "\n";
                        
                        // Format Asal dari Provinsi/Kabupaten
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
                        // Fallback jika profiling kosong
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
        
        // Get current time for update
        $executiveSummary = "Pada {$tanggalFormattedFirst}, di wilayah Merpati-14 termonitor sebanyak {$totalPatroliCount} konten propaganda dan provokasi di media sosial {$platformBreakdownText} yakni terkait Konten Provokatif Mendiskreditkan Pemerintah, Isu Deforestasi, serta Polemik penanganan bencana. Berdasarkan temuan tersebut Merpati-14 telah melakukan upaya RAS dan kontra propaganda dalam rangka mengeliminasi propaganda negatif.";

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

*Perihal : {$judulBencana} di Wilayah Merpati-14 (Update {$tanggalFormattedFirst})*

*A. EXECUTIVE SUMMARY*

{$executiveSummary}

*B. HASIL PATROLI SIBER*

{$isiPatroliBencana}*B.UPAYA*

1. Melakukan upaya RAS dan melakukan Kontra narasi melalui kolom komentar.

2. Melakukan Cipkon dan Cipop Propaganda yang menarasikan kebencian terhadap pemerintah.

3. Melakukan profiling terhadap pemilik akun, afiliasi akun, dst.

*C. DOKUMENTASI LAPORAN (MATRIK AKUN DAN PROFILLING).*

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
        
        error_log("Data arrays for Bencana - nama_akun: " . count($nama_akun_bencana) . ", foto_patroli: " . count($foto_patroli_bencana) . ", foto_upaya: " . count($foto_upaya_bencana) . ", foto_profiling: " . count($foto_profiling_bencana));

        // Create Word file for Patroli Bencana
        try {
            $templatePathBencana = __DIR__ . '/template_word/template_patroli_bencana.docx';
            $judulWordBencana = strtoupper($judulBencana);
            $outputPathBencana = $hasilFolder . "/PATROLI SIBER DAN UPAYA KONTRA OPINI TERHADAP {$judulWordBencana} UPDATE TANGGAL {$tanggalFormatted}.docx";
            
            error_log("Attempting to create Word file for Bencana at: $outputPathBencana");
            
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
            
            if (file_exists($outputPathBencana)) {
                error_log("Word file created successfully for Bencana: $outputPathBencana");
                $response['files']['word_bencana'] = basename($outputPathBencana);
                $response['outputPathBencana'] = $outputPathBencana; // Also add to root level for compatibility
            } else {
                error_log("ERROR: Word file was not created for Bencana at: $outputPathBencana");
            }
        } catch (Exception $e) {
            error_log("ERROR creating Word Bencana: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
        }

        // Create PDF file for Patroli Bencana
        try {
            $templatePathHtmlBencana = __DIR__ . '/template_pdf/template_patroli.html';
            $judulPdfBencana = strtoupper($judulBencana);
            $outputPathPdfBencana = $hasilFolder . "/LAMPIRAN {$judulPdfBencana} DI WILAYAH MERPATI - 14 PADA {$tanggalFormatted}.pdf";
            
            error_log("Attempting to create PDF file for Bencana at: $outputPathPdfBencana");
            
            createPdfFileLandy($templatePathHtmlBencana, $outputPathPdfBencana, $foto_patroli_bencana, $foto_upaya_bencana, $judulPdfBencana);
            
            if (file_exists($outputPathPdfBencana)) {
                error_log("PDF file created successfully for Bencana: $outputPathPdfBencana");
                $response['files']['pdf_bencana'] = basename($outputPathPdfBencana);
                $response['outputPathPdfBencana'] = $outputPathPdfBencana; // Also add to root level for compatibility
            } else {
                error_log("ERROR: PDF file was not created for Bencana at: $outputPathPdfBencana");
            }
        } catch (Exception $e) {
            error_log("ERROR creating PDF Bencana: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
        }
        
        // Ensure narasi is set - same as Landy
        if (!isset($narasiPatroliBencana)) {
            error_log("CRITICAL: narasiPatroliBencana not set before response!");
        } else {
            error_log("Bencana narasi exists before setting to response. Length: " . strlen($narasiPatroliBencana));
        }
        
        $response['narasiPatroliBencana'] = $narasiPatroliBencana;
        error_log("Bencana response set. Check: " . (isset($response['narasiPatroliBencana']) ? 'YES' : 'NO'));
    } else {
        error_log("WARNING: Patroli Bencana condition not met!");
        error_log("reportType: " . print_r($_POST['reportType'] ?? [], true));
        error_log("in_array check: " . (in_array('Patroli Bencana', $_POST['reportType'] ?? []) ? 'YES' : 'NO'));
    }

    $response['success'] = true;
    $response['message'] = 'Proses berhasil';
    
    // Debug info
    $response['debug'] = [
        'expectedFields' => $expectedFields ?? 0,
        'isPatroliLandy' => $isPatroliLandy ?? false,
        'totalProcessedReports' => count($processedReports ?? []),
        'platformCounts' => array_map('count', $processedReports ?? []),
        'rawReportLength' => strlen($_POST['patrolReport'] ?? ''),
        'firstReport' => isset($processedReports) ? reset($processedReports) : null
    ];

    // Cleanup all uploaded images from temporary directories
    cleanImageDirectory(__DIR__ . '/foto');
    cleanImageDirectory(__DIR__ . '/template_word');
    cleanImageDirectory(__DIR__ . '/template_pdf');
    cleanImageDirectory(__DIR__ . '/ss');

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    
    // Cleanup images even if there was an error
    try {
        cleanImageDirectory(__DIR__ . '/foto');
        cleanImageDirectory(__DIR__ . '/template_word');
        cleanImageDirectory(__DIR__ . '/template_pdf');
        cleanImageDirectory(__DIR__ . '/ss');
    } catch (Exception $cleanupError) {
        error_log("Error during cleanup: " . $cleanupError->getMessage());
    }
}

echo json_encode($response);
