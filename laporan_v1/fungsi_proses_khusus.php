<?php
require 'vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;
use Dompdf\Dompdf;

// Ensure required functions are available
if (!function_exists('prosesPatrolReport') || !function_exists('cleanTextForWord')) {
    require_once(__DIR__ . '/fungsi_proses.php');
}

/**
 * Handle pembuatan laporan khusus dengan tema
 */
function handleLaporanKhusus(
    $platformCounts, $tanggalNamaFile, $tanggalFormatted, $tanggalFormattedFirst, $hariFormatted, $bulan_romawi,
    $outputDir, $post, $files, $sheetsToRead, $narasiPatroli, $totalPatroli, $processedReports, $screenshotPaths,
    &$narasiKhusus, &$outputPathKhusus, &$outputPathPdfKhusus, &$outputPathWordPatroliKhusus, $startProgress = 15, $progressRange = 30,
    $tema = '', &$fullNarasiKhusus = ''
) {
    $progressStep = $progressRange / 10; // Divide into 10 steps
    $currentProgress = $startProgress;

    // Validate tema parameter
    if (empty($tema)) {
        throw new Exception('Tema laporan khusus harus diisi.');
    }

    // Clean tema for filename (remove special characters)
    $temaClean = preg_replace('/[^a-zA-Z0-9_-]/', ' ', $tema);
    
    // Clean tema for display (remove underscores and extra spaces)
    $temaDisplay = str_replace('_', ' ', $tema);
    $temaDisplay = preg_replace('/\s+/', ' ', $temaDisplay); // Replace multiple spaces with single space
    $temaDisplay = trim($temaDisplay);

    error_log("=== Starting Laporan Khusus processing ===");
    error_log("Tema: " . $tema);
    error_log("Tema clean: " . $temaClean);
    error_log("Tema display: " . $temaDisplay);
    error_log("Start progress: " . $startProgress);
    error_log("Progress range: " . $progressRange);
    error_log("Output directory: " . $outputDir);
    
    // Debug input parameters
    error_log("KHUSUS DEBUG: Input POST data:");
    error_log("- reportType: " . (isset($post['reportType']) ? implode(',', $post['reportType']) : 'not set'));
    error_log("- input_tema: " . ($post['input_tema'] ?? 'not set'));
    error_log("- patrolReportKhusus: " . (isset($post['patrolReportKhusus']) ? 'present (' . strlen($post['patrolReportKhusus']) . ' chars)' : 'not set'));
    
    error_log("KHUSUS DEBUG: Input FILES data:");
    error_log("- excelFilesKhusus: " . (isset($files['excelFilesKhusus']) ? 'present' : 'not set'));
    if (isset($files['excelFilesKhusus'])) {
        error_log("  - file name: " . ($files['excelFilesKhusus']['name'][0] ?? 'empty'));
        error_log("  - file error: " . ($files['excelFilesKhusus']['error'][0] ?? 'unknown'));
    }
    error_log("- imageFilesKhusus: " . (isset($files['imageFilesKhusus']) ? 'present' : 'not set'));
    if (isset($files['imageFilesKhusus'])) {
        error_log("  - file count: " . count($files['imageFilesKhusus']['name']));
        error_log("  - first file name: " . ($files['imageFilesKhusus']['name'][0] ?? 'empty'));
    }
    error_log("- screenshotPatroliKhusus: " . (isset($files['screenshotPatroliKhusus']) ? 'present' : 'not set'));
    if (isset($files['screenshotPatroliKhusus'])) {
        error_log("  - file count: " . count($files['screenshotPatroliKhusus']['name']));
        error_log("  - first file name: " . ($files['screenshotPatroliKhusus']['name'][0] ?? 'empty'));
    }
    
    // Debug processed reports structure
    error_log("KHUSUS DEBUG: Processed reports structure:");
    error_log("- Total platforms: " . count($processedReports));
    foreach ($processedReports as $platform => $reports) {
        error_log("  - $platform: " . count($reports) . " reports");
    }
    
    // Debug screenshot paths
    error_log("KHUSUS DEBUG: Screenshot paths:");
    error_log("- Total screenshot paths: " . count($screenshotPaths));
    foreach ($screenshotPaths as $index => $path) {
        error_log("  - Screenshot $index: " . $path . " (exists: " . (file_exists($path) ? 'yes' : 'no') . ")");
    }

    // Progress update with debug
    echo json_encode(['progress' => 'Memulai pemrosesan laporan khusus...', 'percent' => (int)$currentProgress]);
    error_log("KHUSUS DEBUG: Progress sent - " . json_encode(['progress' => 'Memulai pemrosesan laporan khusus...', 'percent' => (int)$currentProgress]));
    @ob_flush();
    @flush();
    $currentProgress += $progressStep;

    // --- 1. Proses File Excel jika ada ---
    $jumlahDataPerSheet = [];
    $jumlahAkunPerSheet = [];
    $dataLink = [];
    
    error_log("KHUSUS DEBUG: Checking excel files...");
    error_log("- excelFiles isset: " . (isset($files['excelFiles']) ? 'true' : 'false'));
    error_log("- excelFilesKhusus isset: " . (isset($files['excelFilesKhusus']) ? 'true' : 'false'));
    
    // Check both possible field names for excel files
    $excelFilesField = null;
    if (isset($files['excelFilesKhusus']) && !empty($files['excelFilesKhusus']['name'][0])) {
        $excelFilesField = $files['excelFilesKhusus'];
        error_log("KHUSUS DEBUG: Using excelFilesKhusus field");
    } elseif (isset($files['excelFiles']) && !empty($files['excelFiles']['name'][0])) {
        $excelFilesField = $files['excelFiles'];
        error_log("KHUSUS DEBUG: Using excelFiles field");
    }
    
    if ($excelFilesField) {
        echo json_encode(['progress' => 'Memproses file Excel untuk laporan khusus...', 'percent' => (int)$currentProgress]);
        error_log("KHUSUS DEBUG: Excel progress sent - " . json_encode(['progress' => 'Memproses file Excel untuk laporan khusus...', 'percent' => (int)$currentProgress]));
        @ob_flush();
        @flush();
        
        error_log("KHUSUS DEBUG: Processing excel file: " . $excelFilesField['name'][0]);
        
        try {
            $hasilProses = prosesExcelFiles($excelFilesField, $sheetsToRead);
            $dataAkun = $hasilProses['dataAkun'];
            $dataLink = $hasilProses['dataLink'];
            $totalPerSheet = $hasilProses['totalPerSheet'];

            foreach ($sheetsToRead as $sheetName) {
                $jumlahDataPerSheet[$sheetName] = $totalPerSheet[$sheetName]['totalLink'] ?? 0;
                $jumlahAkunPerSheet[$sheetName] = $totalPerSheet[$sheetName]['totalAkun'] ?? 0;
            }
            
            error_log("KHUSUS DEBUG: Excel processing successful");
            error_log("KHUSUS DEBUG: Data per sheet (links): " . json_encode($jumlahDataPerSheet));
            error_log("KHUSUS DEBUG: Data per sheet (accounts): " . json_encode($jumlahAkunPerSheet));
        } catch (Exception $e) {
            error_log("KHUSUS DEBUG: Error processing excel: " . $e->getMessage());
            // Continue with patrol report data instead
            foreach ($sheetsToRead as $sheetName) {
                $jumlahDataPerSheet[$sheetName] = $platformCounts[$sheetName] ?? 0;
                $jumlahAkunPerSheet[$sheetName] = $platformCounts[$sheetName] ?? 0;
            }
        }
    } else {
        error_log("KHUSUS DEBUG: No excel file found, using platform counts from patrol report");
        // Use platform counts from patrol report
        foreach ($sheetsToRead as $sheetName) {
            $jumlahDataPerSheet[$sheetName] = $platformCounts[$sheetName] ?? 0;
            $jumlahAkunPerSheet[$sheetName] = $platformCounts[$sheetName] ?? 0;
        }
    }
    $currentProgress += $progressStep;

    // --- 2. Process Patrol Report Khusus ---
    echo json_encode(['progress' => 'Memproses data patrol report khusus...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();
    
    $processedReportsKhusus = [];
    $platformCountsKhusus = [];
    
    // Get patrol report khusus data
    $patrolReportKhusus = $post['patrolReportKhusus'] ?? '';
    
    error_log("KHUSUS DEBUG: Processing patrol report khusus");
    error_log("- Patrol report length: " . strlen($patrolReportKhusus));
    
    if (!empty($patrolReportKhusus)) {
        // Use the same function from fungsi_proses.php
        if (!function_exists('prosesPatrolReport')) {
            require_once(__DIR__ . '/fungsi_proses.php');
        }
        if (!function_exists('cleanTextForWord')) {
            require_once(__DIR__ . '/fungsi_proses.php');
        }
        $hasilPatrolKhusus = prosesPatrolReport($patrolReportKhusus, 'patroli', 4, false);
        $processedReportsKhusus = $hasilPatrolKhusus['processedReports'];
        $platformCountsKhusus = $hasilPatrolKhusus['platformCounts'];
        
        error_log("KHUSUS DEBUG: Patrol report processed");
        error_log("- Platforms found: " . implode(', ', array_keys(array_filter($processedReportsKhusus, function($reports) { return !empty($reports); }))));
        error_log("- Total reports: " . array_sum(array_map('count', $processedReportsKhusus)));
    } else {
        error_log("KHUSUS DEBUG: No patrol report khusus provided");
        // Initialize empty structure
        $platforms = ['FACEBOOK', 'INSTAGRAM', 'X', 'TIKTOK', 'SNACKVIDEO', 'YOUTUBE'];
        $processedReportsKhusus = array_fill_keys($platforms, []);
        $platformCountsKhusus = array_fill_keys($platforms, 0);
    }
    
    // --- 3. Handle Screenshot Patroli Khusus ---
    echo json_encode(['progress' => 'Memproses screenshot patroli khusus...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();
    
    $screenshotPathsKhusus = [];
    $patroliScreenshotTypeKhusus = $post['patroliScreenshotTypeKhusus'] ?? 'upload';
    
    error_log("KHUSUS DEBUG: Processing patrol screenshots");
    error_log("- Screenshot type: " . $patroliScreenshotTypeKhusus);
    
    if ($patroliScreenshotTypeKhusus === 'upload') {
        // Handle uploaded patrol screenshots
        $uploadField = null;
        if (isset($files['screenshotPatroliKhusus']) && !empty($files['screenshotPatroliKhusus']['name'][0])) {
            $uploadField = $files['screenshotPatroliKhusus'];
            error_log("KHUSUS DEBUG: Using screenshotPatroliKhusus field");
        }
        
        if ($uploadField) {
            error_log("KHUSUS DEBUG: Processing " . count($uploadField['name']) . " patrol screenshot files");
            
            for ($i = 0; $i < count($uploadField['name']); $i++) {
                if ($uploadField['error'][$i] === UPLOAD_ERR_OK) {
                    $originalPath = $uploadField['tmp_name'][$i];
                    $originalName = $uploadField['name'][$i];
                    $timestamp = date('His') . '_' . substr(microtime(true) * 1000, -3);
                    $pathInfo = pathinfo($originalName);
                    $uniqueName = 'patrol_khusus_' . $timestamp . '_' . ($i + 1) . '.' . $pathInfo['extension'];
                    $destinationPath = __DIR__ . '/foto/' . $uniqueName;

                    // Ensure foto directory exists
                    if (!is_dir(__DIR__ . '/foto')) {
                        mkdir(__DIR__ . '/foto', 0755, true);
                    }

                    if (move_uploaded_file($originalPath, $destinationPath)) {
                        $screenshotPathsKhusus[] = $destinationPath;
                        error_log("KHUSUS DEBUG: Uploaded patrol screenshot: " . $uniqueName);
                    } else {
                        error_log("KHUSUS DEBUG: Failed to upload patrol screenshot: " . $originalName);
                    }
                } else {
                    error_log("KHUSUS DEBUG: Upload error for patrol screenshot " . $i . ": " . $uploadField['error'][$i]);
                }
            }
        } else {
            error_log("KHUSUS DEBUG: No patrol screenshot files found");
        }
    } elseif ($patroliScreenshotTypeKhusus === 'screenshot') {
        // Handle automatic screenshots from patrol report links
        error_log("KHUSUS DEBUG: Using automatic screenshot mode for patrol");
        // This would use the links from the processed patrol report
        // Implementation would be similar to the existing screenshot functionality
    }
    
    error_log("KHUSUS DEBUG: Total patrol screenshot paths: " . count($screenshotPathsKhusus));
    $currentProgress += $progressStep;

    // --- 4. Create Word Patroli Khusus ---
    echo json_encode(['progress' => 'Membuat Word Patroli Khusus...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    $templatePatroliKhusus = __DIR__ . '/template_word/template_Patroli_Khusus_Format_KBD.docx';
    $outputPathWordPatroli = $outputDir . '/PATROLI SIBER DAN UPAYA KONTRA OPINI ' . strtoupper($temaClean) . ' DI WILAYAH MERPATI 14 PADA ' . $tanggalNamaFile . '.docx';

    error_log("KHUSUS DEBUG: Creating Word Patroli file");
    error_log("- Template: " . $templatePatroliKhusus);
    error_log("- Output: " . $outputPathWordPatroli);
    error_log("- Template exists: " . (file_exists($templatePatroliKhusus) ? 'true' : 'false'));
    error_log("- Output dir exists: " . (is_dir($outputDir) ? 'true' : 'false'));
    error_log("- Output dir writable: " . (is_writable($outputDir) ? 'true' : 'false'));
    error_log("- Processed reports count: " . array_sum(array_map('count', $processedReportsKhusus)));
    error_log("- Screenshot paths count: " . count($screenshotPathsKhusus));

    try {
        if (!createWordFilePatroliKhusus($templatePatroliKhusus, $outputPathWordPatroli, $tanggalFormatted, $processedReportsKhusus, $screenshotPathsKhusus, $tema, $perihalKhusus, $fullNarrativeKhusus)) {
            throw new Exception('Gagal membuat file Word Patroli Khusus. Template tidak ditemukan atau terjadi kesalahan.');
        }
        
        // Set output path untuk return (hanya nama file, bukan path lengkap)
        $outputPathWordPatroliKhusus = basename($outputPathWordPatroli);
        error_log("KHUSUS DEBUG: Word Patroli created successfully: " . $outputPathWordPatroliKhusus);
    } catch (Exception $e) {
        error_log("KHUSUS DEBUG: Error creating Word Patroli: " . $e->getMessage());
        throw $e;
    }
    $currentProgress += $progressStep * 2;

    // --- 5. Create Word Cipop Khusus ---
    echo json_encode(['progress' => 'Membuat Word Cipop Khusus...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    $templateCipopKhusus = __DIR__ . '/template_word/template_viral_khusus_format_kbd.docx';
    $outputPathWordGeneral = $outputDir . '/' . $tanggalNamaFile . ' - CIPKON DAN CIPOP MELALUI MEDIA SOSIAL DALAM RANGKA ' . strtoupper($temaClean) . ' DI WILAYAH MERPATI 14.docx';

    error_log("KHUSUS DEBUG: Creating Word Cipop file");
    error_log("- Template: " . $templateCipopKhusus);
    error_log("- Output: " . $outputPathWordGeneral);
    error_log("- Template exists: " . (file_exists($templateCipopKhusus) ? 'true' : 'false'));

    try {
        if (!createWordFileCipopKhusus($templateCipopKhusus, $outputPathWordGeneral, $tanggalFormatted, $jumlahDataPerSheet, $jumlahAkunPerSheet, $dataLink, $tema, $perihalKhusus, $fullNarrativeKhusus)) {
            throw new Exception('Gagal membuat file Word Cipop Khusus. Template tidak ditemukan atau terjadi kesalahan.');
        }
        
        // Set output path untuk return (hanya nama file, bukan path lengkap)
        $outputPathKhusus = basename($outputPathWordGeneral);
        error_log("KHUSUS DEBUG: Word Cipop created successfully: " . $outputPathKhusus);
    } catch (Exception $e) {
        error_log("KHUSUS DEBUG: Error creating Word Cipop: " . $e->getMessage());
        throw $e;
    }
    $currentProgress += $progressStep * 2;

    // --- 6. Handle Screenshot Cipop ---
    echo json_encode(['progress' => 'Memproses screenshot Cipop khusus...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    $imagePaths = handleScreenshotCipopKhusus($post, $files);
    $currentProgress += $progressStep * 2;

    // --- 7. Create PDF Lampiran Cipop Khusus ---
    echo json_encode(['progress' => 'Membuat PDF Lampiran Cipop Khusus...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    $templatePdfKhusus = __DIR__ . '/template_pdf/template_cipop_khusus_format_kbd.html';
    $outputPathPdf = $outputDir . '/' . $tanggalNamaFile . ' - PELAKSANAAN CIPKON DAN CIPOP MELALUI MEDIA SOSIAL DALAM RANGKA ' . strtoupper($temaClean) . ' DI WILAYAH MERPATI 14.pdf';

    error_log("KHUSUS DEBUG: Creating PDF file");
    error_log("- Template: " . $templatePdfKhusus);
    error_log("- Output: " . $outputPathPdf);
    error_log("- Template exists: " . (file_exists($templatePdfKhusus) ? 'true' : 'false'));
    error_log("- Image paths count: " . count($imagePaths));

    try {
        if (!createPdfFileCipopKhusus($templatePdfKhusus, $outputPathPdf, $tanggalFormatted, $hariFormatted, $tanggalFormattedFirst, $jumlahDataPerSheet, $jumlahAkunPerSheet, $imagePaths, $tema, $perihalKhusus, $fullNarrativeKhusus)) {
            throw new Exception('Gagal membuat file PDF Cipop Khusus. Template tidak ditemukan atau terjadi kesalahan.');
        }
        
        // Set output path untuk return (hanya nama file, bukan path lengkap)
        $outputPathPdfKhusus = basename($outputPathPdf);
        error_log("KHUSUS DEBUG: PDF created successfully: " . $outputPathPdfKhusus);
    } catch (Exception $e) {
        error_log("KHUSUS DEBUG: Error creating PDF: " . $e->getMessage());
        throw $e;
    }
    $currentProgress += $progressStep * 2;

    // --- 8. Build Narrative ---
    error_log("KHUSUS DEBUG: Building narrative");
    error_log("- jumlahDataPerSheet: " . json_encode($jumlahDataPerSheet));
    error_log("- tema: " . $tema);
    error_log("- tanggalFormattedFirst: " . $tanggalFormattedFirst);
    
    $narasiResult = buildNarasiKhusus($jumlahDataPerSheet, $jumlahAkunPerSheet, $tema, $tanggalFormattedFirst, $processedReportsKhusus, $platformCountsKhusus);
    $narasiKhusus = $narasiResult['narrative'];
    $perihalKhusus = $narasiResult['perihal'];
    $fullNarrativeKhusus = $narasiResult['fullNarrative'];
    $fullNarasiKhusus = $fullNarrativeKhusus; // Assign to reference parameter
    error_log("KHUSUS DEBUG: Narrative built: " . $narasiKhusus);
    error_log("KHUSUS DEBUG: Perihal built: " . $perihalKhusus);
    error_log("KHUSUS DEBUG: Full narrative length: " . strlen($fullNarrativeKhusus));

    echo json_encode(['progress' => 'Laporan khusus selesai dibuat...', 'percent' => (int)($startProgress + $progressRange)]);
    @ob_flush();
    @flush();

    error_log("=== Laporan Khusus processing completed ===");
    error_log("Output files created:");
    error_log("- Word Patroli: " . $outputPathWordPatroliKhusus);
    error_log("- Word Cipop: " . $outputPathKhusus);
    error_log("- PDF Lampiran: " . $outputPathPdfKhusus);
    error_log("- Narrative length: " . strlen($narasiKhusus));
}

/**
 * Create Word file for Patroli Khusus
 */
function createWordFilePatroliKhusus($templatePath, $outputPath, $tanggalFormatted, $processedReports, $screenshotPaths, $tema, $perihal = '', $narrative = '')
{
    if (!file_exists($templatePath)) {
        error_log("Template Patroli Khusus not found: " . $templatePath);
        return false;
    }

    try {
        $templateProcessor = new TemplateProcessor($templatePath);

        // Clean tema for display (remove underscores and extra spaces)
        $temaDisplay = str_replace('_', ' ', $tema);
        $temaDisplay = preg_replace('/\s+/', ' ', $temaDisplay); // Replace multiple spaces with single space
        $temaDisplay = trim($temaDisplay);

        // Replace basic placeholders
        $tanggalFormattedFirst = ucfirst(strtolower($tanggalFormatted));
        $templateProcessor->setValue('tanggal', $tanggalFormattedFirst);
        $templateProcessor->setValue('tanggal_judul', $tanggalFormatted);
        $templateProcessor->setValue('tema', $temaDisplay);
        
        // Set perihal and narrative if provided
        if (!empty($perihal)) {
            $templateProcessor->setValue('perihal', $perihal);
        }
        if (!empty($narrative)) {
            $templateProcessor->setValue('narrative', $narrative);
            $templateProcessor->setValue('narasi', $narrative);
            $templateProcessor->setValue('executive_summary', $narrative);
            $templateProcessor->setValue('full_narrative', $narrative);
            $templateProcessor->setValue('laporan_lengkap', $narrative);
        }

        // Prepare data for table rows grouped by platform
        $platforms = array_filter($processedReports, function ($reports) {
            return !empty($reports);
        });

        $templateProcessor->cloneBlock('platform', count($platforms), true, true);

        foreach (array_keys($platforms) as $platformIndex => $platform) {
            $reports = $processedReports[$platform];
            $templateProcessor->setValue("nama_platform#" . ($platformIndex + 1), ucwords(strtolower($platform)));

            // Clone rows for each report under the platform
            $templateProcessor->cloneRow("nama_akun#" . ($platformIndex + 1), count($reports));
            foreach ($reports as $reportIndex => $report) {
                $cleanName = $report['name'];
                $cleanLink = $report['link'];
                $cleanCategory = $report['category'];
                $cleanNarrative = cleanTextForWord($report['narrative']);

                $templateProcessor->setValue("nama_akun#" . ($platformIndex + 1) . "#" . ($reportIndex + 1), $cleanName);
                $templateProcessor->setValue("link#" . ($platformIndex + 1) . "#" . ($reportIndex + 1), $cleanLink);
                $templateProcessor->setValue("kategori#" . ($platformIndex + 1) . "#" . ($reportIndex + 1), $cleanCategory);
                $templateProcessor->setValue("narasi#" . ($platformIndex + 1) . "#" . ($reportIndex + 1), $cleanNarrative);
            }
        }

        // Add patrol screenshots
        $screenshotIndex = 0;
        foreach (array_keys($platforms) as $platformIndex => $platform) {
            $reports = $processedReports[$platform];
            foreach ($reports as $reportIndex => $report) {
                if (isset($screenshotPaths[$screenshotIndex]) && file_exists($screenshotPaths[$screenshotIndex])) {
                    $templateProcessor->setImageValue(
                        "foto_patroli#" . ($platformIndex + 1) . "#" . ($reportIndex + 1),
                        [
                            'path' => $screenshotPaths[$screenshotIndex],
                            'width' => 300,
                            'height' => 200,
                            'ratio' => true
                        ]
                    );
                }
                $screenshotIndex++;
            }
        }

        $templateProcessor->saveAs($outputPath);
        return true;
    } catch (Exception $e) {
        error_log('Error creating Word Patroli Khusus: ' . $e->getMessage());
        throw new Exception('Gagal membuat file Word Patroli Khusus: ' . $e->getMessage());
    }
}

/**
 * Create Word file for Cipop Khusus
 */
function createWordFileCipopKhusus($templatePath, $outputPath, $tanggalFormatted, $jumlahDataPerSheet, $jumlahAkunPerSheet, $dataLink, $tema, $perihal = '', $narrative = '')
{
    if (!file_exists($templatePath)) {
        error_log("Template Cipop Khusus not found: " . $templatePath);
        return false;
    }

    try {
        $templateProcessor = new TemplateProcessor($templatePath);

        // Clean tema for display (remove underscores and extra spaces)
        $temaDisplay = str_replace('_', ' ', $tema);
        $temaDisplay = preg_replace('/\s+/', ' ', $temaDisplay); // Replace multiple spaces with single space
        $temaDisplay = trim($temaDisplay);

        // Replace basic placeholders
        $templateProcessor->setValue("tanggal", $tanggalFormatted);
        $templateProcessor->setValue("tema", $temaDisplay);
        
        // Set perihal and narrative if provided
        if (!empty($perihal)) {
            $templateProcessor->setValue("perihal", $perihal);
        }
        if (!empty($narrative)) {
            $templateProcessor->setValue("narrative", $narrative);
            $templateProcessor->setValue("narasi", $narrative);
            $templateProcessor->setValue("executive_summary", $narrative);
            $templateProcessor->setValue("full_narrative", $narrative);
            $templateProcessor->setValue("laporan_lengkap", $narrative);
        }

        // Insert data counts per sheet - use separate account and link counts
        foreach ($jumlahDataPerSheet as $sheetName => $linkCount) {
            $accountCount = $jumlahAkunPerSheet[$sheetName] ?? $linkCount;
            $templateProcessor->setValue("totalAkun_$sheetName", $accountCount);
            $templateProcessor->setValue("totalLink_$sheetName", $linkCount);
            
            error_log("KHUSUS DEBUG: Sheet $sheetName - Accounts: $accountCount, Links: $linkCount");
        }

        // Insert link data per sheet
        foreach ($dataLink as $sheetName => $dataRows) {
            $linkData = "";
            foreach ($dataRows as $index => $row) {
                $cleanData = cleanTextForWord($row['kolom5']);
                $linkData .= ($index + 1) . ". " . $cleanData . "\n";
            }
            $templateProcessor->setValue("dataLink_$sheetName", $linkData);
        }

        $templateProcessor->saveAs($outputPath);
        return true;
    } catch (Exception $e) {
        error_log('Error creating Word Cipop Khusus: ' . $e->getMessage());
        throw new Exception('Gagal membuat file Word Cipop Khusus: ' . $e->getMessage());
    }
}

/**
 * Handle screenshot Cipop Khusus
 */
function handleScreenshotCipopKhusus($post, $files)
{
    $imagePaths = [];
    $cipopImageType = $post['cipopImageTypeKhusus'] ?? 'upload';
    
    error_log("KHUSUS DEBUG: Processing Cipop images");
    error_log("- cipopImageType: " . $cipopImageType);
    error_log("- Available files keys: " . implode(', ', array_keys($files)));

    if ($cipopImageType === 'screenshot') {
        // Handle screenshot links from textarea
        $screenshotLinksText = $post['cipopScreenshotLinksKhusus'] ?? '';
        $screenshotLinks = array_filter(array_map('trim', explode("\n", $screenshotLinksText)));
        
        error_log("KHUSUS DEBUG: Screenshot mode");
        error_log("- Links text: " . $screenshotLinksText);
        error_log("- Links count: " . count($screenshotLinks));

        if (!empty($screenshotLinks)) {
            // Take screenshots using Node.js script
            $escapedLinks = array_map('escapeshellarg', $screenshotLinks);
            $cmd = 'node ' . escapeshellarg(__DIR__ . '/ambil_ss.js') . ' cipop_khusus ' . implode(' ', $escapedLinks);
            error_log("KHUSUS DEBUG: Executing command: " . $cmd);
            exec($cmd, $output, $ret);
            error_log("KHUSUS DEBUG: Command output: " . implode("\n", $output));

            // Get screenshot files
            $ssDir = __DIR__ . '/ss';
            $filesArr = [];
            foreach (glob($ssDir . '/cipop_khusus_*.jpg') as $f) {
                $filesArr[$f] = filemtime($f);
            }
            arsort($filesArr);

            $selectedFiles = array_slice(array_keys($filesArr), 0, count($screenshotLinks));
            error_log("KHUSUS DEBUG: Found " . count($selectedFiles) . " screenshot files");
            
            foreach ($selectedFiles as $src) {
                $timestamp = date('His') . '_' . substr(microtime(true) * 1000, -3);
                $dst = __DIR__ . '/template_pdf/cipop_khusus_' . $timestamp . '.jpg';
                if (copy($src, $dst)) {
                    $imagePaths[] = $dst;
                    error_log("KHUSUS DEBUG: Copied screenshot: " . basename($dst));
                }
            }
        }
    } else {
        // Handle uploaded files - check both possible field names
        $uploadField = null;
        if (isset($files['imageFilesKhusus']) && !empty($files['imageFilesKhusus']['name'][0])) {
            $uploadField = $files['imageFilesKhusus'];
            error_log("KHUSUS DEBUG: Using imageFilesKhusus field");
        } elseif (isset($files['cipopUploadFileKhusus']) && !empty($files['cipopUploadFileKhusus']['name'][0])) {
            $uploadField = $files['cipopUploadFileKhusus'];
            error_log("KHUSUS DEBUG: Using cipopUploadFileKhusus field");
        }
        
        if ($uploadField) {
            error_log("KHUSUS DEBUG: Upload mode - processing " . count($uploadField['name']) . " files");
            
            for ($i = 0; $i < count($uploadField['name']); $i++) {
                if ($uploadField['error'][$i] === UPLOAD_ERR_OK) {
                    $originalPath = $uploadField['tmp_name'][$i];
                    $originalName = $uploadField['name'][$i];
                    $timestamp = date('His') . '_' . substr(microtime(true) * 1000, -3);
                    $pathInfo = pathinfo($originalName);
                    $uniqueName = 'cipop_khusus_' . $timestamp . '_' . ($i + 1) . '.' . $pathInfo['extension'];
                    $destinationPath = __DIR__ . '/template_pdf/' . $uniqueName;

                    if (move_uploaded_file($originalPath, $destinationPath)) {
                        $imagePaths[] = $destinationPath;
                        error_log("KHUSUS DEBUG: Uploaded file: " . $uniqueName);
                    } else {
                        error_log("KHUSUS DEBUG: Failed to upload file: " . $originalName);
                    }
                } else {
                    error_log("KHUSUS DEBUG: Upload error for file " . $i . ": " . $uploadField['error'][$i]);
                }
            }
        } else {
            error_log("KHUSUS DEBUG: No upload files found");
        }
    }

    error_log("KHUSUS DEBUG: Total image paths: " . count($imagePaths));
    return $imagePaths;
}

/**
 * Create PDF file for Cipop Khusus
 */
function createPdfFileCipopKhusus($templatePath, $outputPath, $tanggalFormatted, $hariFormatted, $tanggalFormattedFirst, $jumlahDataPerSheet, $jumlahAkunPerSheet, $imagePaths, $tema, $perihal = '', $narrative = '')
{
    if (!file_exists($templatePath)) {
        error_log("Template PDF Cipop Khusus not found: " . $templatePath);
        return false;
    }

    try {
        $htmlTemplate = file_get_contents($templatePath);

        // Clean tema for display (remove underscores and extra spaces)
        $temaDisplay = str_replace('_', ' ', $tema);
        $temaDisplay = preg_replace('/\s+/', ' ', $temaDisplay); // Replace multiple spaces with single space
        $temaDisplay = trim($temaDisplay);

        // Replace placeholders
        $htmlTemplate = str_replace('{{tanggal}}', $tanggalFormatted, $htmlTemplate);
        $htmlTemplate = str_replace('{{hari}}', $hariFormatted, $htmlTemplate);
        $htmlTemplate = str_replace('{{tanggal_2}}', $tanggalFormattedFirst, $htmlTemplate);
        $htmlTemplate = str_replace('{{tema}}', $temaDisplay, $htmlTemplate);
        
        // Replace perihal and narrative if provided
        if (!empty($perihal)) {
            $htmlTemplate = str_replace('{{perihal}}', $perihal, $htmlTemplate);
        }
        if (!empty($narrative)) {
            $htmlTemplate = str_replace('{{narrative}}', $narrative, $htmlTemplate);
            $htmlTemplate = str_replace('{{narasi}}', $narrative, $htmlTemplate);
            $htmlTemplate = str_replace('{{executive_summary}}', $narrative, $htmlTemplate);
            $htmlTemplate = str_replace('{{full_narrative}}', $narrative, $htmlTemplate);
            $htmlTemplate = str_replace('{{laporan_lengkap}}', $narrative, $htmlTemplate);
        }

        // Replace platform totals - use link counts for PDF (keeping original behavior)
        $platformMapping = [
            'FACEBOOK' => 'FACEBOOK',
            'INSTAGRAM' => 'INSTAGRAM', 
            'TWITTER' => 'TWITTER',
            'TIKTOK' => 'TIKTOK',
            'SNACKVIDEO' => 'SNACKVIDEO',
            'YOUTUBE' => 'YOUTUBE'
        ];

        foreach ($platformMapping as $sheetName => $placeholder) {
            $linkCount = $jumlahDataPerSheet[$sheetName] ?? 0;
            $htmlTemplate = str_replace("{{totalLink_$placeholder}}", $linkCount, $htmlTemplate);
            
            error_log("KHUSUS DEBUG: PDF - Sheet $sheetName link count: $linkCount");
        }

        // Add images
        for ($i = 0; $i < 8; $i++) {
            if (isset($imagePaths[$i]) && $imagePaths[$i]) {
                $mimeType = mime_content_type($imagePaths[$i]);
                $imageBase64 = base64_encode(file_get_contents($imagePaths[$i]));
                $imageHtml = 'data:' . $mimeType . ';base64,' . $imageBase64;
                $htmlTemplate = str_replace('{{image_' . ($i + 1) . '}}', $imageHtml, $htmlTemplate);
            } else {
                $htmlTemplate = str_replace('{{image_' . ($i + 1) . '}}', '', $htmlTemplate);
            }
        }

        // Create PDF
        $dompdf = new Dompdf();
        $dompdf->loadHtml($htmlTemplate);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        file_put_contents($outputPath, $dompdf->output());
        return true;
    } catch (Exception $e) {
        error_log('Error creating PDF Cipop Khusus: ' . $e->getMessage());
        throw new Exception('Gagal membuat file PDF Cipop Khusus: ' . $e->getMessage());
    }
}

/**
 * Build narrative for Laporan Khusus
 */
function buildNarasiKhusus($jumlahDataPerSheet, $jumlahAkunPerSheet, $tema, $tanggalFormattedFirst, $processedReports = [], $platformCounts = [])
{
    $totalLink = array_sum($jumlahDataPerSheet);
    $totalAkun = array_sum($jumlahAkunPerSheet);
    
    // Clean tema for display (remove underscores and extra spaces)
    $temaDisplay = str_replace('_', ' ', $tema);
    $temaDisplay = preg_replace('/\s+/', ' ', $temaDisplay); // Replace multiple spaces with single space
    $temaDisplay = trim($temaDisplay);
    $temaUpper = strtoupper($temaDisplay);
    
    // Create perihal/subject line in the requested format
    $perihal = "PELAKSANAAN CIPKON DAN CIPOP MELALUI MEDIA SOSIAL DALAM RANGKA $temaUpper DI WIL. MERPATI – 14";
    
    // Build platform details for action plan - showing both account and link counts
    $platformDetails = [];
    $platformMapping = [
        'FACEBOOK' => 'Facebook',
        'INSTAGRAM' => 'Instagram', 
        'X' => 'X / Twitter',
        'TWITTER' => 'X / Twitter',
        'TIKTOK' => 'Tiktok',
        'SNACKVIDEO' => 'Snackvideo',
        'YOUTUBE' => 'Youtube'
    ];
    
    foreach ($jumlahDataPerSheet as $platform => $linkCount) {
        if ($linkCount > 0) {
            $displayName = $platformMapping[$platform] ?? ucwords(strtolower($platform));
            $platformDetails[] = "$displayName $linkCount link";
        }
    }
    
    // A. EXECUTIVE SUMMARY
    $executiveSummary = "Pada $tanggalFormattedFirst, di wilayah Merpati-14 termonitor ";
    
    // Count total patrol reports and build platform breakdown
    $totalPatrolReports = 0;
    $patrolPlatformBreakdown = [];
    
    if (!empty($processedReports)) {
        foreach ($processedReports as $platform => $reports) {
            if (!empty($reports)) {
                $count = count($reports);
                $totalPatrolReports += $count;
                $displayPlatform = $platformMapping[$platform] ?? ucwords(strtolower($platform));
                $patrolPlatformBreakdown[] = "$displayPlatform ($count konten)";
            }
        }
    }
    
    if ($totalPatrolReports > 0) {
        $executiveSummary .= "sebanyak $totalPatrolReports konten provokasi di media sosial yaitu ";
        
        // Format platform breakdown with proper comma and "dan" usage
        if (count($patrolPlatformBreakdown) > 1) {
            $lastPlatform = array_pop($patrolPlatformBreakdown);
            $executiveSummary .= implode(', ', $patrolPlatformBreakdown) . ' dan ' . $lastPlatform . ". ";
        } else {
            $executiveSummary .= implode(' dan ', $patrolPlatformBreakdown) . ". ";
        }
    } else {
        $executiveSummary .= "konten provokasi di media sosial. ";
    }
    
    $executiveSummary .= "Berdasarkan temuan tersebut Tim Delta Merpati-14 telah melakukan upaya RAS dan kontra propaganda dalam rangka mengeliminir propaganda negatif. Selain itu belum ditemukan adanya seruan aksi di media sosial wilayah Merpati-14.
";
    
    // B. HASIL PATROLI CYBER
    $patrolResults = "";
    if (!empty($processedReports)) {
        foreach ($processedReports as $platform => $reports) {
            if (!empty($reports)) {
                $displayPlatform = $platformMapping[$platform] ?? ucwords(strtolower($platform));
                $patrolResults .= "\n\n$displayPlatform\n\n";
                
                foreach ($reports as $index => $report) {
                    $patrolResults .= ($index + 1) . ". Termonitor Akun " . $report['name'] . " (" . $report['link'] . ") membagikan postingan " . $report['category'] . " dengan narasi \"" . $report['narrative'] . "\"\n\n";
                }
            }
        }
    }
    
    // C. LANGKAH TINDAK
    $actionPlan = "Tim Merpati 14 memasifkan Kontra opini dengan tema konten $temaDisplay dengan total ";
    if (!empty($platformDetails)) {
        $actionPlan .= implode(', ', $platformDetails);
    } else {
        $actionPlan .= "0 link";
    }
    $actionPlan .= ".";
    
    // Create full narrative in the format from your example
    $fullNarrative = "Kepada: Yth.\n1. Rajawali\n2. Elang\n\nDari : Merpati - 14\n\nTembusan : Yth.\n1. Kasuari – 2\n2. Kasuari – 6\n3. Kasuari – 9\n\nPerihal : $perihal\n\nA. EXECUTIVE SUMMARY\n\n$executiveSummary";
    
    if (!empty($patrolResults)) {
        $fullNarrative .= "\n\nB. HASIL PATROLI CYBER$patrolResults";
    }
    
    $fullNarrative .= "\n\nC. LANGKAH TINDAK\n\n$actionPlan";
    $fullNarrative .= "\n\nNilai : Ambon-1\n\nDUMP Merpati-14";
    
    // Simple narrative for basic use
    $simpleNarrative = "Pada $tanggalFormattedFirst di Wilayah Merpati-14 telah dilakukan upaya Amplifikasi \"$temaDisplay\". ";
    $simpleNarrative .= "Dengan total ";
    
    if (!empty($platformDetails)) {
        $simpleNarrative .= implode(', ', $platformDetails);
    } else {
        $simpleNarrative .= "0 link";
    }
    
    $simpleNarrative .= ". Total keseluruhan: $totalLink link.";
    
    // Return comprehensive data structure
    return [
        'perihal' => $perihal,
        'narrative' => $simpleNarrative,
        'fullNarrative' => $fullNarrative,
        'executiveSummary' => $executiveSummary,
        'patrolResults' => $patrolResults,
        'actionPlan' => $actionPlan,
        'totalLink' => $totalLink,
        'totalAkun' => $totalAkun,
        'platformDetails' => $platformDetails
    ];
}
?>
