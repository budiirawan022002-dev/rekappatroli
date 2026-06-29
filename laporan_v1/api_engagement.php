<?php
header('Content-Type: application/json');
require_once 'vendor/autoload.php';
require_once 'fungsi_konversi.php';

use PhpOffice\PhpWord\TemplateProcessor;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_POST['action']) || $_POST['action'] !== 'generate_engagement_report') {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

try {
    $tanggal = $_POST['tanggal'] ?? '';
    $judul = $_POST['judul'] ?? '';
    $namaAkun = $_POST['namaAkun'] ?? [];
    $narasi = $_POST['narasi'] ?? [];
    $links = $_POST['links'] ?? [];
    $platforms = $_POST['platforms'] ?? [];
    $linkIndexes = $_POST['linkIndexes'] ?? [];
    $komentarTotal = isset($_POST['komentarTotal']) ? (int)$_POST['komentarTotal'] : 0;

    // Validation
    if (empty($tanggal) || empty($judul)) {
        throw new Exception('Tanggal dan judul wajib diisi');
    }

    if (empty($namaAkun) || !is_array($namaAkun) || count($namaAkun) === 0) {
        throw new Exception('Minimal harus ada satu akun');
    }
    
    // Debug logging (can be removed in production)
    error_log("API Engagement - Total accounts: " . count($namaAkun));
    error_log("API Engagement - Total links: " . count($links));
    error_log("API Engagement - Total linkIndexes: " . count($linkIndexes));

    // Prepare directories
    $hasilDir = 'hasil';
    $evidenceDir = $hasilDir . '/evidence';
    
    if (!file_exists($hasilDir)) {
        mkdir($hasilDir, 0777, true);
    }
    if (!file_exists($evidenceDir)) {
        mkdir($evidenceDir, 0777, true);
    }

    // Process evidence uploads
    // Get unique links dengan index asli (berdasarkan urutan pertama muncul di input)
    $uniqueLinks = [];
    $linkIndexMap = []; // Map dari unique link ke index asli di input
    $seenLinks = [];
    foreach ($links as $idx => $link) {
        if (!in_array($link, $seenLinks)) {
            $uniqueLinks[] = $link;
            $linkIndexMap[count($uniqueLinks) - 1] = array_search($link, $links); // Index asli di input
            $seenLinks[] = $link;
        }
    }
    
    // Process evidence - linkIdx adalah index dari input links (0, 1, 2, dst)
    $evidenceByLink = [];
    // Cari max linkIdx dari linkIndexes untuk menentukan berapa banyak link yang ada
    $maxLinkIdx = !empty($linkIndexes) ? max($linkIndexes) : 0;
    
    // Process evidence untuk setiap link index (berdasarkan input links)
    for ($linkIdx = 0; $linkIdx <= $maxLinkIdx; $linkIdx++) {
        $evidenceByLink[$linkIdx] = [];
        
        // Get evidence for this link
        $evidenceKey = "evidence_link_{$linkIdx}";
        if (isset($_FILES[$evidenceKey])) {
            $files = $_FILES[$evidenceKey];
            
            if (is_array($files['name'])) {
                // Multiple files
                for ($i = 0; $i < count($files['name']); $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $tmpName = $files['tmp_name'][$i];
                        $fileName = $files['name'][$i];
                        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        
                        if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) {
                            $newFileName = 'evidence_' . time() . '_' . $linkIdx . '_' . $i . '.' . $fileExt;
                            $destPath = $evidenceDir . '/' . $newFileName;
                            
                            if (move_uploaded_file($tmpName, $destPath)) {
                                $evidenceByLink[$linkIdx][] = $destPath;
                            }
                        }
                    }
                }
            } else {
                // Single file
                if ($files['error'] === UPLOAD_ERR_OK) {
                    $tmpName = $files['tmp_name'];
                    $fileName = $files['name'];
                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    
                    if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) {
                        $newFileName = 'evidence_' . time() . '_' . $linkIdx . '.' . $fileExt;
                        $destPath = $evidenceDir . '/' . $newFileName;
                        
                        if (move_uploaded_file($tmpName, $destPath)) {
                            $evidenceByLink[$linkIdx][] = $destPath;
                        }
                    }
                }
            }
        }
    }

    // Get actual links dari input (unique berdasarkan index asli)
    $actualLinks = [];
    $seenLinkUrls = [];
    foreach ($linkIndexes as $idx => $linkIdx) {
        $link = isset($links[$idx]) ? $links[$idx] : '';
        if ($link && !in_array($link, $seenLinkUrls)) {
            $actualLinks[] = $link;
            $seenLinkUrls[] = $link;
        }
    }
    // Jika tidak ada, ambil dari unique links
    if (empty($actualLinks)) {
        $actualLinks = $uniqueLinks;
    }

    // Assign evidence from link to accounts - each account gets different evidence
    // Group accounts by link index (index dari input links) to distribute evidence properly
    $accountsByLink = [];
    $totalAccounts = count($namaAkun);
    for ($i = 0; $i < $totalAccounts; $i++) {
        $linkIdx = isset($linkIndexes[$i]) ? (int)$linkIndexes[$i] : 0;
        if (!isset($accountsByLink[$linkIdx])) {
            $accountsByLink[$linkIdx] = [];
        }
        $accountsByLink[$linkIdx][] = $i; // Store account index with original order
    }
    
    // Assign evidence to each account based on their position in the link
    // Each account gets ONE evidence image based on their order (first account = first evidence, etc.)
    $evidenceImages = [];
    foreach ($accountsByLink as $linkIdx => $accountIndices) {
        $linkEvidence = $evidenceByLink[$linkIdx] ?? [];
        $evidenceCount = count($linkEvidence);
        
        if ($evidenceCount > 0) {
            // Distribute evidence: each account gets one evidence based on their position
            // Account at position 0 gets evidence[0], position 1 gets evidence[1], etc.
            // If more accounts than evidence, cycle through evidence (account gets evidence[position % evidenceCount])
            foreach ($accountIndices as $position => $accountIndex) {
                // Each account gets ONE evidence image based on their position
                $evidenceIndex = $position % $evidenceCount; // Cycle if more accounts than evidence
                $evidenceImages[$accountIndex] = [$linkEvidence[$evidenceIndex]];
            }
        } else {
            // No evidence for this link
            foreach ($accountIndices as $accountIndex) {
                $evidenceImages[$accountIndex] = [];
            }
        }
    }
    
    // Ensure all accounts have evidence array (even if empty)
    for ($i = 0; $i < $totalAccounts; $i++) {
        if (!isset($evidenceImages[$i])) {
            $evidenceImages[$i] = [];
        }
    }

    // Prepare data for report
    $reportData = [];
    for ($i = 0; $i < $totalAccounts; $i++) {
        $linkIdx = isset($linkIndexes[$i]) ? (int)$linkIndexes[$i] : 0;
        // Get actual link from links array at current index
        $actualLink = isset($links[$i]) ? $links[$i] : (isset($uniqueLinks[0]) ? $uniqueLinks[0] : '');
        $platform = isset($platforms[$i]) ? $platforms[$i] : 'Unknown';
        
        $reportData[] = [
            'no' => $i + 1,
            'link' => $actualLink,
            'nama_akun' => $namaAkun[$i],
            'narasi' => $narasi[$i],
            'platform' => $platform,
            'evidence' => $evidenceImages[$i] ?? []
        ];
    }

    // Generate WhatsApp format
    $whatsappFormat = generateWhatsAppFormat($tanggal, $judul, $reportData, $komentarTotal);

    // Generate Word document
    $templatePath = 'template_word/template_engagement.docx';
    if (!file_exists($templatePath)) {
        throw new Exception('Template file tidak ditemukan: ' . $templatePath);
    }

    $templateProcessor = new TemplateProcessor($templatePath);

    // Count unique accounts per platform (based on nama_akun)
    $platformAccountCounts = [];
    $seenAccounts = [];
    foreach ($reportData as $data) {
        $platform = $data['platform'];
        $akun = $data['nama_akun'];
        $key = $platform . '_' . $akun;
        if (!in_array($key, $seenAccounts)) {
            if (!isset($platformAccountCounts[$platform])) {
                $platformAccountCounts[$platform] = 0;
            }
            $platformAccountCounts[$platform]++;
            $seenAccounts[] = $key;
        }
    }
    
    $platformSummary = [];
    foreach ($platformAccountCounts as $platform => $count) {
        $platformSummary[] = "$count akun $platform";
    }
    $totalAccounts = count(array_unique(array_column($reportData, 'nama_akun')));
    $platformText = implode(', ', $platformSummary);

    // Set template variables
    $templateProcessor->setValue('tanggal', formatTanggalIndonesia($tanggal));
    $templateProcessor->setValue('judul', $judul);
    $templateProcessor->setValue('total_akun', $totalAccounts); // Use unique accounts count
    $templateProcessor->setValue('platform_summary', $platformText);

    // Group accounts by link for Word document
    $accountsByLinkForWord = [];
    foreach ($reportData as $data) {
        $link = $data['link'];
        if (!isset($accountsByLinkForWord[$link])) {
            $accountsByLinkForWord[$link] = [];
        }
        $accountsByLinkForWord[$link][] = $data;
    }

    // Clone rows for each account FIRST (before setting values)
    if (count($reportData) > 0) {
        $templateProcessor->cloneRow('no', count($reportData));
    }
    
    // Set all values for each row
    foreach ($reportData as $index => $data) {
        $rowNum = $index + 1;
        
        // Set text values - ensure all data is present
        $no = isset($data['no']) ? (string)$data['no'] : (string)($index + 1);
        $link = isset($data['link']) ? $data['link'] : '';
        $namaAkun = isset($data['nama_akun']) ? $data['nama_akun'] : '';
        $narasi = isset($data['narasi']) ? $data['narasi'] : '';
        $platform = isset($data['platform']) ? $data['platform'] : 'Unknown';
        
        $templateProcessor->setValue("no#{$rowNum}", $no);
        // Use tautan_konten instead of link to match template
        $templateProcessor->setValue("tautan_konten#{$rowNum}", $link);
        $templateProcessor->setValue("nama_akun#{$rowNum}", $namaAkun);
        $templateProcessor->setValue("narasi#{$rowNum}", $narasi);
        $templateProcessor->setValue("platform#{$rowNum}", $platform);
        
        // Set evidence image AFTER setting text values
        if (!empty($data['evidence']) && isset($data['evidence'][0])) {
            $evidencePath = $data['evidence'][0];
            if (file_exists($evidencePath)) {
                try {
                    // Set image - square, 2x2 inch (144 points), force exact size
                    // Use eviden#rowNum format to match template
                    $templateProcessor->setImageValue("eviden#{$rowNum}", [
                        'path' => $evidencePath,
                        'width' => 144,
                        'height' => 144,
                        'ratio' => false // Force exact size (square)
                    ]);
                } catch (Exception $e) {
                    // Log error but continue processing
                    error_log("Error setting evidence image for row {$rowNum}: " . $e->getMessage());
                }
            }
        }
    }

    // Save document
    // Clean judul: keep alphanumeric, spaces, and dash (-)
    // Hanya hapus karakter khusus, pertahankan semua huruf, angka, spasi, dan dash
    // PASTIKAN TIDAK ADA KARAKTER YANG HILANG - hanya hapus karakter yang benar-benar tidak diinginkan
    $judulClean = preg_replace('/[^a-zA-Z0-9\s\-]/u', '', $judul);
    
    // Convert to uppercase - PASTIKAN SEMUA KARAKTER TETAP ADA
    $judulClean = mb_strtoupper($judulClean, 'UTF-8');
    
    // Normalize spaces (multiple spaces to single space)
    $judulClean = preg_replace('/\s+/u', ' ', $judulClean);
    $judulClean = trim($judulClean);
    
    // Remove duplicate "ENGAGEMENT" di awal judul jika ada (setelah di-uppercase)
    // PASTIKAN TIDAK MENGHAPUS KARAKTER LAIN - hanya hapus jika benar-benar dimulai dengan "ENGAGEMENT "
    // Gunakan cara yang lebih eksplisit dan aman
    $prefixToRemove = 'ENGAGEMENT ';
    $prefixLength = mb_strlen($prefixToRemove, 'UTF-8');
    if (mb_substr($judulClean, 0, $prefixLength, 'UTF-8') === $prefixToRemove) {
        $judulClean = mb_substr($judulClean, $prefixLength, null, 'UTF-8');
        $judulClean = trim($judulClean);
    }
    
    // Limit length - pastikan tidak memotong di tengah kata jika mungkin
    if (mb_strlen($judulClean, 'UTF-8') > 100) {
        $judulClean = mb_substr($judulClean, 0, 100, 'UTF-8');
        $judulClean = trim($judulClean);
    }

    // Format tanggal untuk UPDATE (format: "23 DESEMBER 2025" - semua kapital)
    $tanggalUpdate = strtoupper(formatTanggalIndonesia($tanggal));
    
    // Nama file: LAPORAN ENGAGEMENT [JUDUL] UPDATE [TANGGAL INDONESIA KAPITAL].docx
    // Tanpa underscore, tanpa tanggal format 23122025, semua uppercase, dengan UPDATE di akhir
    $outputFileName = 'LAPORAN ENGAGEMENT ' . $judulClean . ' UPDATE ' . $tanggalUpdate . '.docx';
    $outputPath = $hasilDir . '/' . $outputFileName;
    
    $templateProcessor->saveAs($outputPath);

    // Return response
    echo json_encode([
        'success' => true,
        'message' => 'Laporan berhasil dibuat',
        'whatsapp_format' => $whatsappFormat,
        'file_path' => $outputPath
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

/**
 * Generate WhatsApp format text
 */
function generateWhatsAppFormat($tanggal, $judul, $reportData, $komentarTotal = 0) {
    // Format date and totals
    $tanggalFormatted = formatTanggalIndonesia($tanggal);
    $totalKomentar = $komentarTotal > 0 ? $komentarTotal : count($reportData);

    // Build WhatsApp format
    $output = "Kepada Yth.: Kasuari-6\n\n";
    $output .= "Tembusan :\n";
    $output .= "1. Kasuari-21\n";
    $output .= "2. Kasuari-22\n";
    $output .= "3. Kasuari-23\n";
    $output .= "4. Kasuari-24\n";
    $output .= "5. Kasuari-25\n";
    $output .= "6. Kasuari-63\n\n";
    $output .= "Dari : Merpati-14\n\n";
    $output .= "Perihal : Upaya Pembanjiran Komentar Terhadap Konten Positif terkait isu {$judul}, Periode {$tanggalFormatted}\n\n";
    $output .= "Izin melaporkan pada {$tanggalFormatted}, Merpati-14 telah melakukan upaya pembanjiran komentar terhadap konten Positif terkait isu {$judul} dengan total {$totalKomentar} komentar. Adapun rincian kegiatan sebagai berikut:\n\n";

    foreach ($reportData as $index => $row) {
        $platform = $row['platform'] ?? 'Unknown';
        if (strtolower($platform) === 'twitter/x' || strtolower($platform) === 'x') {
            $platform = 'X/Twitter';
        }
        $akun = $row['nama_akun'] ?? '-';
        $link = $row['link'] ?? '-';
        $komen = $row['narasi'] ?? '-';
        $output .= ($index + 1) . ". Akun {$platform} {$akun} {$link} Komen: {$komen}\n\n";
    }

    $output .= "Selanjutnya lampiran pelaksanaan telah terkirim pada google form.\n\n";
    $output .= "DUMP";

    return trim($output);
}
?>
