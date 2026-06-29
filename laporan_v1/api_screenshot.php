<?php
/**
 * API Screenshot - Menangani permintaan pengambilan screenshot
 * 
 * File ini menangani:
 * 1. Pengambilan screenshot dari link yang diberikan
 * 2. Download semua gambar screenshot dalam bentuk ZIP
 */

// Import fungsi yang diperlukan
require 'vendor/autoload.php';
require 'fungsi_proses.php';
require 'fungsi_konversi.php';

// Set header JSON
header('Content-Type: application/json');

// Pastikan direktori ss ada
$ssDir = __DIR__ . '/ss';
if (!file_exists($ssDir)) {
    mkdir($ssDir, 0777, true);
}

// Cek metode request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Metode tidak diizinkan'
    ]);
    exit;
}

// Ambil action dari request
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'take_screenshots':
        // Ambil screenshot dari link
        takeScreenshots();
        break;
    
    case 'download_all':
        // Download semua gambar screenshot
        downloadAllScreenshots();
        break;
    
    case 'get_existing_screenshots':
        // Ambil daftar screenshot yang sudah ada
        getExistingScreenshots();
        break;
    
    default:
        echo json_encode([
            'status' => 'error',
            'message' => 'Action tidak valid'
        ]);
        break;
}

/**
 * Fungsi untuk mengambil screenshot dari link
 */
function takeScreenshots() {
    // Ambil jenis screenshot dan array link
    $jenis = $_POST['jenis'] ?? 'cipop';
    $links = $_POST['links'] ?? [];
    
    if (empty($links)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Tidak ada link yang diberikan'
        ]);
        return;
    }
    
    // Array untuk menyimpan nama file screenshot
    $screenshotFiles = [];
    $failedLinks = [];
    
    // Proses setiap link
    foreach ($links as $link) {
        if (empty(trim($link))) {
            continue;
        }
        
        try {
            // Log proses
            error_log("Taking screenshot for: {$link}");
            
            // Panggil fungsi untuk mengambil screenshot
            $filename = executeScreenshotCommand($jenis, $link);
            
            if ($filename) {
                $screenshotFiles[] = $filename;
                error_log("Screenshot success: {$filename}");
            } else {
                $failedLinks[] = $link;
                error_log("Screenshot failed for: {$link}");
            }
        } catch (Exception $e) {
            // Log error
            $failedLinks[] = $link;
            error_log('Error taking screenshot: ' . $e->getMessage() . ' for link: ' . $link);
        }
    }
    
    // Pastikan direktori hasil ada
    $hasilDir = __DIR__ . '/hasil';
    if (!file_exists($hasilDir)) {
        mkdir($hasilDir, 0777, true);
    }
    
    // Return hasil
    echo json_encode([
        'status' => 'success',
        'message' => count($screenshotFiles) > 0 ? 'Screenshot berhasil diambil' : 'Tidak ada screenshot yang berhasil diambil',
        'files' => $screenshotFiles,
        'failedLinks' => $failedLinks,
        'totalProcessed' => count($links),
        'totalSuccess' => count($screenshotFiles),
        'totalFailed' => count($failedLinks)
    ]);
}

/**
 * Fungsi untuk menjalankan perintah pengambilan screenshot
 * 
 * @param string $jenis Jenis screenshot (cipop, patroli, upaya)
 * @param string $link Link yang akan diambil screenshotnya
 * @return string|null Nama file screenshot jika berhasil, null jika gagal
 */
function executeScreenshotCommand($jenis, $link) {
    // Path ke node dan script ambil_ss.js
    $nodePath = 'node';
    $scriptPath = __DIR__ . '/ambil_ss.js';
    
    // Validasi jenis
    if (!in_array($jenis, ['cipop', 'patroli', 'upaya'])) {
        $jenis = 'cipop'; // Default jika tidak valid
    }
    
    // Escape karakter khusus pada link untuk command line
    $escapedLink = escapeshellarg($link);
    
    // Tentukan nama file output berdasarkan timestamp unik
    $timestamp = time();
    $platformName = getPlatformName($link);
    $outputName = "{$jenis}_{$platformName}_{$timestamp}.jpg";
    
    // Eksekusi perintah ambil screenshot
    $command = "$nodePath $scriptPath $jenis $escapedLink";
    exec($command, $output, $returnCode);
    
    // Cek apakah screenshot berhasil
    if ($returnCode !== 0) {
        return null;
    }
    
    // Cari file output berdasarkan pattern di folder ss
    $ssDir = __DIR__ . '/ss';
    $pattern = "{$jenis}_{$platformName}_{$timestamp}*.jpg";
    $matchingFiles = glob("{$ssDir}/{$pattern}");
    
    if (empty($matchingFiles)) {
        return null;
    }
    
    // Return nama file (tanpa path)
    return basename($matchingFiles[0]);
}

/**
 * Fungsi untuk mendeteksi platform dari link
 * 
 * @param string $link URL yang akan dideteksi platformnya
 * @return string Nama platform (facebook, instagram, xcom, tiktok, dll)
 */
function getPlatformName($link) {
    if (strpos($link, 'facebook.com') !== false) {
        return 'facebook';
    } elseif (strpos($link, 'instagram.com') !== false) {
        return 'instagram';
    } elseif (strpos($link, 'twitter.com') !== false || strpos($link, 'x.com') !== false) {
        return 'xcom';
    } elseif (strpos($link, 'tiktok.com') !== false) {
        return 'tiktok';
    } elseif (strpos($link, 'youtube.com') !== false) {
        return 'youtube';
    } else {
        return 'unknown';
    }
}

/**
 * Fungsi untuk download semua gambar screenshot
 */
function downloadAllScreenshots() {
    // Ambil array nama file
    $files = $_POST['files'] ?? [];
    
    // Log request untuk debugging
    error_log('Download request received, file count: ' . count($files));
    
    if (empty($files)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Tidak ada file yang dipilih'
        ]);
        return;
    }
    
    // Path direktori ss
    $ssDir = __DIR__ . '/ss';
    
    // Pastikan direktori hasil ada
    $hasilDir = __DIR__ . '/hasil';
    if (!file_exists($hasilDir)) {
        mkdir($hasilDir, 0777, true);
    }
    
    // Buat nama file zip
    $timestamp = date('YmdHis');
    $zipFileName = "screenshots_{$timestamp}.zip";
    $zipFilePath = $hasilDir . '/' . $zipFileName;
    
    // Buat objek ZipArchive
    $zip = new ZipArchive();
    
    // Buka file zip untuk ditulis
    if ($zip->open($zipFilePath, ZipArchive::CREATE) !== true) {
        error_log('Failed to create ZIP file: ' . $zipFilePath);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal membuat file ZIP'
        ]);
        return;
    }
    
    // Tambahkan setiap file ke zip
    $fileCount = 0;
    $missingFiles = [];
    
    foreach ($files as $file) {
        $fullPath = $ssDir . '/' . $file;
        
        if (file_exists($fullPath)) {
            $addResult = $zip->addFile($fullPath, $file);
            if ($addResult) {
                $fileCount++;
            } else {
                error_log('Failed to add file to ZIP: ' . $fullPath);
                $missingFiles[] = $file;
            }
        } else {
            error_log('File does not exist: ' . $fullPath);
            $missingFiles[] = $file;
        }
    }
    
    // Tutup file zip
    $zipResult = $zip->close();
    
    if (!$zipResult) {
        error_log('Failed to close ZIP file: ' . $zipFilePath);
        echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menyimpan file ZIP'
        ]);
        return;
    }
    
    // Log hasil untuk debugging
    error_log("ZIP created with $fileCount files. Missing files: " . implode(', ', $missingFiles));
    
    // Return URL untuk download
    echo json_encode([
        'status' => 'success',
        'message' => "File ZIP dengan {$fileCount} gambar berhasil dibuat",
        'zipFile' => 'hasil/' . $zipFileName,
        'zipFileName' => $zipFileName,
        'fileCount' => $fileCount,
        'totalFiles' => count($files),
        'missingFiles' => $missingFiles
    ]);
}

/**
 * Fungsi untuk mendapatkan daftar screenshot yang sudah ada
 */
function getExistingScreenshots() {
    // Ambil jenis screenshot jika ada
    $jenis = $_POST['jenis'] ?? '';
    
    // Path direktori ss
    $ssDir = __DIR__ . '/ss';

    // Pastikan direktori ss ada
    if (!file_exists($ssDir)) {
        mkdir($ssDir, 0777, true);
    }
    
    // Pola file untuk dicari
    $pattern = !empty($jenis) ? "{$jenis}_*.jpg" : "*.jpg";
    
    // Cari semua file yang sesuai pola
    $files = glob("{$ssDir}/{$pattern}");
    
    // Ambil nama file saja (tanpa path)
    $fileNames = array_map('basename', $files);
    
    // Urutkan berdasarkan tanggal modifikasi terbaru
    usort($fileNames, function($a, $b) use ($ssDir) {
        return filemtime("{$ssDir}/{$b}") - filemtime("{$ssDir}/{$a}");
    });
    
    // Return hasil
    echo json_encode([
        'status' => 'success',
        'message' => 'Berhasil mendapatkan daftar screenshot',
        'files' => $fileNames,
        'count' => count($fileNames)
    ]);
}
