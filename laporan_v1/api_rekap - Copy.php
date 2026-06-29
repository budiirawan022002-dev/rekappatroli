<?php
require 'vendor/autoload.php';
require 'fungsi_proses.php';
require 'fungsi_konversi.php';

header('Content-Type: application/json');



try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['reportType'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }

    // Clean up hasil directory before processing
    cleanupHasilDirectory();

    $narrative = "";
    $outputPathWordPatroli = $outputPathWordGeneral = $outputPathPdf = "";
    $outputPathLandy = $outputPathPdfLandy = $outputPathPagi = $outputPathPdfPagi = "";
    $narasiPatroliLandy = $narasiPatroliPagi = "";

    // --- Proses Patroli ---
    $rawReport = $_POST['patrolReport'] ?? '';
    $hasilPatroli = prosesPatrolReport($rawReport);
    $groupedReports = $hasilPatroli['groupedReports'];
    $processedReports = $hasilPatroli['processedReports'];

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
    echo json_encode(['progress' => 'Memproses screenshot patroli...', 'percent' => 10]); @ob_flush(); @flush();
    $screenshotPaths = handleScreenshotPatroli($processedReports, $_POST, $_FILES);

    // --- Laporan KBD ---
    if (in_array('Laporan KBD', $_POST['reportType'])) {
        echo json_encode(['progress' => 'Membuat laporan KBD...', 'percent' => 15]); @ob_flush(); @flush();
        handleLaporanKBD(
            $platformCounts, $tanggalNamaFile, $tanggalFormatted, $tanggalFormattedFirst, $hariFormatted, $bulan_romawi,
            __DIR__ . '/hasil', $_POST, $_FILES, $sheetsToRead, $narasiPatroli,
            $narrative, $outputPathWordGeneral, $outputPathPdf, $outputPathWordPatroli
        );
    }

    // --- Patroli Landy ---
    if (in_array('Patroli Landy', $_POST['reportType'])) {
        echo json_encode(['progress' => 'Membuat laporan Patroli Landy...', 'percent' => 50]); @ob_flush(); @flush();
        handlePatroliLandy(
            $processedReports, $tanggalFormatted, $tanggalFormattedFirst, __DIR__ . '/hasil', $_POST, $_FILES, $screenshotPaths,
            $narasiPatroliLandy, $outputPathLandy, $outputPathPdfLandy
        );
    }

    // --- Patroli Pagi ---
    if (in_array('Patroli Pagi', $_POST['reportType'])) {
        echo json_encode(['progress' => 'Membuat laporan Patroli Pagi...', 'percent' => 75]); @ob_flush(); @flush();
        handlePatroliPagi(
            $processedReports, $tanggalFormatted, $tanggalFormattedFirst, $bulan_romawi, __DIR__ . '/hasil', $_POST, $_FILES, $screenshotPaths,
            $narasiPatroliPagi, $outputPathPagi, $outputPathPdfPagi
        );
    }
    
    // Final cleanup - delete all temporary images after processing is complete
    echo json_encode(['progress' => 'Membersihkan file sementara...', 'percent' => 95]); @ob_flush(); @flush();
    
    // Clean up images from all temporary directories
    cleanImageDirectory(__DIR__ . '/foto');
    cleanImageDirectory(__DIR__ . '/template_word');
    cleanImageDirectory(__DIR__ . '/template_pdf');
    cleanImageDirectory(__DIR__ . '/ss'); // Also clean the screenshot directory

    echo json_encode([
        'success' => true,
        'narrative' => $narrative,
        'outputPathWordGeneral' => $outputPathWordGeneral,
        'outputPathPdf' => $outputPathPdf,
        'outputPathWordPatroli' => $outputPathWordPatroli,
        'outputPathLandy' => $outputPathLandy,
        'outputPathPdfLandy' => $outputPathPdfLandy,
        'outputPathPagi' => $outputPathPagi,
        'outputPathPdfPagi' => $outputPathPdfPagi,
        'narasiPatroliLandy' => $narasiPatroliLandy,
        'narasiPatroliPagi' => $narasiPatroliPagi
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
