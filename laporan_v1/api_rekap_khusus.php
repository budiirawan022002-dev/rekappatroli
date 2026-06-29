<?php
require 'vendor/autoload.php';
require 'fungsi_proses_khusus.php';
require 'fungsi_konversi.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    // Set proper character encoding for output
    mb_internal_encoding('UTF-8');
    mb_http_output('UTF-8');

    // Clean up hasil directory before processing
    cleanupHasilDirectory();

    // Validate required fields
    if (empty($_POST['patrolReportKhusus'])) {
        throw new Exception('Input Patrol Report Khusus tidak boleh kosong.');
    }

    if (empty($_POST['inputTema'])) {
        throw new Exception('Tema laporan khusus harus diisi.');
    }

    if (empty($_POST['tanggal'])) {
        throw new Exception('Tanggal laporan harus diisi.');
    }

    echo json_encode(['progress' => 'Memulai pemrosesan laporan khusus...', 'percent' => 5]);
    @ob_flush();
    @flush();

    // Process basic data
    $tema = $_POST['inputTema'];
    $tanggalInput = $_POST['tanggal'];
    $tanggalFormatted = strtoupper(formatTanggalIndonesia($tanggalInput));
    $tanggalNamaFile = date('dmY', strtotime($tanggalInput));
    $tanggalFormattedFirst = ucfirst(formatTanggalIndonesia($tanggalInput));
    $hariFormatted = getHariIndonesia($tanggalInput);
    $bulan_romawi = bulanKeRomawi($tanggalInput);

    // Process patrol report khusus
    echo json_encode(['progress' => 'Memproses patrol report khusus...', 'percent' => 10]);
    @ob_flush();
    @flush();

    $rawReportKhusus = $_POST['patrolReportKhusus'];
    $hasilPatroli = prosesPatrolReport($rawReportKhusus);
    $groupedReports = $hasilPatroli['groupedReports'];
    $processedReports = $hasilPatroli['processedReports'];

    // Build platform counts
    $platformCounts = [];
    $totalPatroli = 0;
    foreach ($groupedReports as $platform => $reports) {
        $count = count($reports);
        $platformCounts[$platform] = $count;
        $totalPatroli += $count;
    }

    $sheetsToRead = ['FACEBOOK', 'INSTAGRAM', 'TWITTER', 'TIKTOK', 'SNACKVIDEO', 'YOUTUBE'];

    // Handle screenshot patroli khusus
    echo json_encode(['progress' => 'Memproses screenshot patroli khusus...', 'percent' => 15]);
    @ob_flush();
    @flush();

    $screenshotPaths = handleScreenshotPatroliKhusus($processedReports, $_POST, $_FILES);

    // Initialize output variables
    $narrative = "";
    $outputPathWordGeneral = "";
    $outputPathPdf = "";
    $outputPathWordPatroli = "";

    // Call main handler
    echo json_encode(['progress' => 'Membuat dokumen laporan khusus...', 'percent' => 25]);
    @ob_flush();
    @flush();

    handleLaporanKhusus(
        $platformCounts, $tanggalNamaFile, $tanggalFormatted, $tanggalFormattedFirst, $hariFormatted, $bulan_romawi,
        __DIR__ . '/hasil', $_POST, $_FILES, $sheetsToRead, "", $totalPatroli, $processedReports, $screenshotPaths,
        $narrative, $outputPathWordGeneral, $outputPathPdf, $outputPathWordPatroli, 25, 65, $tema
    );

    // Final cleanup
    echo json_encode(['progress' => 'Membersihkan file sementara...', 'percent' => 95]);
    @ob_flush();
    @flush();

    cleanImageDirectory(__DIR__ . '/foto');
    cleanImageDirectory(__DIR__ . '/template_word');
    cleanImageDirectory(__DIR__ . '/template_pdf');
    cleanImageDirectory(__DIR__ . '/ss');

    echo json_encode([
        'success' => true,
        'narrative' => $narrative,
        'outputPathWordGeneral' => $outputPathWordGeneral,
        'outputPathPdf' => $outputPathPdf,
        'outputPathWordPatroli' => $outputPathWordPatroli,
        'tema' => $tema,
        'tanggal' => $tanggalFormatted,
        'progress' => 'Laporan khusus berhasil dibuat!',
        'percent' => 100
    ]);

} catch (Exception $e) {
    error_log("Error in laporan khusus processing: " . $e->getMessage());
    
    // Cleanup on error
    try {
        cleanImageDirectory(__DIR__ . '/foto');
        cleanImageDirectory(__DIR__ . '/template_word');
        cleanImageDirectory(__DIR__ . '/template_pdf');
        cleanImageDirectory(__DIR__ . '/ss');
    } catch (Exception $cleanupError) {
        error_log("Error during cleanup: " . $cleanupError->getMessage());
    }
    
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

/**
 * Handle screenshot patroli khusus
 */
function handleScreenshotPatroliKhusus($processedReports, $post, $files)
{
    $screenshotPaths = [];
    $patroliScreenshotType = $post['patroliScreenshotTypeKhusus'] ?? 'upload';

    if ($patroliScreenshotType === 'screenshot') {
        // Get links from processed reports
        $patroliLinks = [];
        foreach ($processedReports as $platform => $reports) {
            foreach ($reports as $report) {
                if (!empty($report['link'])) {
                    $patroliLinks[] = $report['link'];
                }
            }
        }

        if (count($patroliLinks) < 1) {
            throw new Exception('Tidak ada link pada hasil patrol report untuk tangkapan layar patroli khusus.');
        }

        error_log("Starting screenshot capture for " . count($patroliLinks) . " patrol khusus links");

        // Run node script for screenshots
        $escapedLinks = array_map('escapeshellarg', $patroliLinks);
        $cmd = 'node ' . escapeshellarg(__DIR__ . '/ambil_ss.js') . ' patroli_khusus ' . implode(' ', $escapedLinks);
        exec($cmd, $output, $ret);

        error_log("Screenshot command executed: $cmd");
        error_log("Screenshot command return code: $ret");

        // Get screenshot files
        $ssDir = __DIR__ . '/ss';
        $filesArr = [];
        foreach (glob($ssDir . '/patroli_khusus_*.jpg') as $f) {
            $filesArr[$f] = filemtime($f);
        }
        arsort($filesArr);

        if (empty($filesArr)) {
            throw new Exception('Tidak ada file screenshot yang berhasil dibuat untuk patrol khusus.');
        }

        $selectedFiles = array_slice(array_keys($filesArr), 0, count($patroliLinks));
        foreach ($selectedFiles as $index => $src) {
            $timestamp = date('His') . '_' . substr(microtime(true) * 1000, -3);
            $uniqueBasename = 'patroli_khusus_' . $timestamp . '_' . ($index + 1) . '.jpg';
            $dst = __DIR__ . '/foto/' . $uniqueBasename;

            if (!is_dir(__DIR__ . '/foto')) {
                mkdir(__DIR__ . '/foto', 0755, true);
            }

            copy($src, $dst);
            $screenshotPaths[] = $dst;
            error_log("Processed patrol khusus screenshot: $src -> $dst");
        }
    } else {
        // Upload file mode
        if (!isset($files['screenshotPatroliKhusus']) || empty($files['screenshotPatroliKhusus']['name'][0])) {
            throw new Exception('Tidak ada file screenshot patroli khusus yang diupload.');
        }

        $fotoDir = __DIR__ . '/foto';
        if (!is_dir($fotoDir)) {
            mkdir($fotoDir, 0755, true);
        }

        for ($i = 0; $i < count($files['screenshotPatroliKhusus']['name']); $i++) {
            if (
                isset($files['screenshotPatroliKhusus']['tmp_name'][$i]) &&
                $files['screenshotPatroliKhusus']['error'][$i] === UPLOAD_ERR_OK &&
                file_exists($files['screenshotPatroliKhusus']['tmp_name'][$i])
            ) {
                $originalPath = $files['screenshotPatroliKhusus']['tmp_name'][$i];
                $originalName = $files['screenshotPatroliKhusus']['name'][$i];

                $timestamp = date('His') . '_' . substr(microtime(true) * 1000, -3);
                $pathInfo = pathinfo($originalName);
                $uniqueName = 'patroli_khusus_' . $timestamp . '_' . ($i + 1) . '.' . $pathInfo['extension'];
                $destinationPath = $fotoDir . '/' . $uniqueName;

                if (move_uploaded_file($originalPath, $destinationPath)) {
                    $screenshotPaths[] = $destinationPath;
                    error_log("Uploaded patrol khusus screenshot: $destinationPath");
                } else {
                    throw new Exception('Gagal menyimpan screenshot patroli khusus: ' . $originalName);
                }
            }
        }
    }

    if (empty($screenshotPaths)) {
        throw new Exception('Tidak ada screenshot patroli khusus yang berhasil diproses.');
    }

    return $screenshotPaths;
}
?>
