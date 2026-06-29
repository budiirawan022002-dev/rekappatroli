<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use Dompdf\Dompdf; // Untuk membuat file PDF

/**
 * Fungsi untuk memproses file Excel dan mengembalikan data yang dikelompokkan dan diurutkan.
 *
 * @param array $files Array file yang diunggah melalui form.
 * @param array $sheetsToRead Daftar nama sheet yang akan diproses.
 * @return array Data yang diproses, termasuk data akun, data link, dan total per sheet.
 */
function prosesExcelFiles($files, $sheetsToRead)
{
    $dataAkun = []; // Array untuk menyimpan data yang dikelompokkan berdasarkan sheet
    $dataLink = []; // Array untuk menyimpan data yang diurutkan berdasarkan sheet
    $totalPerSheet = []; // Variabel untuk menyimpan total data per sheet

    // Loop melalui semua file yang diunggah
    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $uploadedFile = $files['tmp_name'][$i];

            // Load file Excel
            $spreadsheet = IOFactory::load($uploadedFile);

            foreach ($sheetsToRead as $sheetName) {
                // Periksa apakah sheet ada
                if ($spreadsheet->sheetNameExists($sheetName)) {
                    // Ambil sheet berdasarkan nama
                    $sheet = $spreadsheet->getSheetByName($sheetName);

                    // Array untuk menyimpan data dari sheet ini
                    $dataRows = [];
                    $groupedData = []; // Array untuk mengelompokkan nilai kolom 3
                    $rowIndex = 0; // Inisialisasi indeks baris

                    foreach ($sheet->getRowIterator() as $row) {
                        $rowIndex++;
                        if ($rowIndex <= 3) {
                            continue; // Lewati baris 1, 2, dan 3
                        }

                        $cellIterator = $row->getCellIterator();
                        $cellIterator->setIterateOnlyExistingCells(false); // Loop semua sel, termasuk yang kosong

                        $data = [];
                        foreach ($cellIterator as $cell) {
                            $data[] = $cell->getValue(); // Simpan nilai sel dalam array
                        }

                        // Pastikan kolom 3 memiliki data sebelum menambahkan ke array
                        if (!empty($data[2])) { // Kolom 3 adalah indeks 2 (dimulai dari 0)
                            $dataRows[] = [
                                'kolom3' => $data[2],
                                'kolom5' => $data[4] ?? ''
                            ];

                            // Kelompokkan nilai berdasarkan kolom 3
                            if (!isset($groupedData[$data[2]])) {
                                $groupedData[$data[2]] = 0;
                            }
                            $groupedData[$data[2]]++;
                        }
                    }

                    // Simpan data yang dikelompokkan ke array global berdasarkan sheet
                    if (!isset($dataAkun[$sheetName])) {
                        $dataAkun[$sheetName] = [];
                    }
                    foreach ($groupedData as $key => $count) {
                        if (!isset($dataAkun[$sheetName][$key])) {
                            $dataAkun[$sheetName][$key] = 0;
                        }
                        $dataAkun[$sheetName][$key] += $count;
                    }

                    // Simpan data yang diurutkan ke array global berdasarkan sheet
                    if (!isset($dataLink[$sheetName])) {
                        $dataLink[$sheetName] = [];
                    }
                    $dataLink[$sheetName] = array_merge($dataLink[$sheetName], $dataRows);

                    // Hitung total data untuk setiap sheet
                    $totalPerSheet[$sheetName] = [
                        'totalAkun' => count($groupedData),
                        'totalLink' => count($dataRows)
                    ];
                }
            }
        }
    }

    return [
        'dataAkun' => $dataAkun,
        'dataLink' => $dataLink,
        'totalPerSheet' => $totalPerSheet
    ];
}

/**
 * Fungsi untuk membuat file Word berdasarkan template.
 *
 * @param string $templatePath Path template Word.
 * @param string $outputPath Path output file Word.
 * @param string $tanggalFormatted Tanggal yang diformat.
 * @param array $jumlahDataPerSheet Data jumlah per sheet.
 * @param array $dataLink Data link per sheet.
 */
function createWordFile($templatePath, $outputPath, $tanggalFormatted, $jumlahDataPerSheet, $dataLink)
{
    if (file_exists($templatePath)) {
        $templateProcessor = new TemplateProcessor($templatePath);

        // Masukkan tanggal ke dalam template Word
        $templateProcessor->setValue("tanggal", $tanggalFormatted);

        // Masukkan jumlah data ke dalam template Word berdasarkan sheet
        foreach ($jumlahDataPerSheet as $sheetName => $totals) {
            $templateProcessor->setValue("totalAkun_$sheetName", $totals['totalAkun']);
            $templateProcessor->setValue("totalLink_$sheetName", $totals['totalLink']);
        }

        // Masukkan data link ke dalam template Word
        foreach ($dataLink as $sheetName => $dataRows) {
            $linkData = "";
            foreach ($dataRows as $index => $row) {
                // Clean the data using the utility function
                $cleanData = cleanTextForWord($row['kolom5']);
                $linkData .= ($index + 1) . ". " . $cleanData . "\n";
            }
            $templateProcessor->setValue("dataLink_$sheetName", $linkData);
        }

        // Simpan file Word yang telah dimodifikasi
        $templateProcessor->saveAs($outputPath);
        return true;
    }
    return false;
}

/**
 * Fungsi untuk membuat file PDF berdasarkan template HTML.
 *
 * @param string $templatePath Path template HTML.
 * @param string $outputPath Path output file PDF.
 * @param string $tanggalFormatted Tanggal yang diformat.
 * @param string $hariFormatted Hari yang diformat.
 * @param string $tanggalFormattedFirst Tanggal dengan huruf besar di awal.
 * @param array $jumlahDataPerSheet Data jumlah per sheet.
 * @param array $imagePaths Path gambar.
 */
function createPdfFile($templatePath, $outputPath, $tanggalFormatted, $hariFormatted, $tanggalFormattedFirst, $jumlahDataPerSheet, $imagePaths)
{
    if (file_exists($templatePath)) {
        $htmlTemplate = file_get_contents($templatePath);

        // Masukkan tanggal dan hari ke dalam template HTML
        $htmlTemplate = str_replace('{{tanggal}}', $tanggalFormatted, $htmlTemplate);
        $htmlTemplate = str_replace('{{hari}}', $hariFormatted, $htmlTemplate);
        $htmlTemplate = str_replace('{{tanggal_2}}', $tanggalFormattedFirst, $htmlTemplate);

        // Masukkan total link per sheet ke dalam template HTML
        foreach ($jumlahDataPerSheet as $sheetName => $totals) {
            $htmlTemplate = str_replace("{{totalLink_$sheetName}}", $totals, $htmlTemplate);
        }

        // Tambahkan gambar ke dalam template HTML
        for ($i = 0; $i < 8; $i++) {
            if (isset($imagePaths[$i]) && $imagePaths[$i]) {
                $mimeType = mime_content_type($imagePaths[$i]);
                $imageBase64 = base64_encode(file_get_contents($imagePaths[$i]));
                $imageHtml = 'data:' . $mimeType . ';base64,' . $imageBase64;
                $htmlTemplate = str_replace('{{image_' . ($i + 1) . '}}', $imageHtml, $htmlTemplate);

                // Hapus gambar setelah dimasukkan ke dalam template
                unlink($imagePaths[$i]);
            } else {
                $htmlTemplate = str_replace('{{image_' . ($i + 1) . '}}', '', $htmlTemplate);
            }
        }

        // Buat PDF dari template HTML
        $dompdf = new Dompdf();
        $dompdf->loadHtml($htmlTemplate);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        // Simpan PDF
        file_put_contents($outputPath, $dompdf->output());
        return true;
    }
    return false;
}

/**
 * Proses screenshot patroli: upload file atau ambil otomatis dari link.
 * @param array $processedReports
 * @param array $post
 * @param array $files
 * @return array $screenshotPaths
 */
function handleScreenshotPatroli($processedReports, $post, $files)
{
    $screenshotPaths = [];
    $patroliScreenshotType = $post['patroliScreenshotType'] ?? 'upload';

    // Detailed progress reporting - Start screenshot phase
    echo json_encode(['progress' => 'Memulai fase pengolahan tangkapan layar...', 'percent' => 12]);
    @ob_flush();
    @flush();

    if ($patroliScreenshotType === 'screenshot') {
        // Ambil link dari processedReports
        $patroliLinks = [];
        foreach ($processedReports as $platform => $reports) {
            foreach ($reports as $report) {
                if (!empty($report['link'])) {
                    $patroliLinks[] = $report['link'];
                }
            }
        }
        if (count($patroliLinks) < 1) {
            throw new Exception('Tidak ada link pada hasil patrol report untuk tangkapan layar patroli.');
        }

        // Log start of screenshot capture process with link count
        error_log("Starting screenshot capture for " . count($patroliLinks) . " patrol links");
        echo json_encode(['progress' => 'Memulai pengambilan tangkapan layar patroli (' . count($patroliLinks) . ' link)...', 'percent' => 15]);
        @ob_flush();
        @flush();

        // Jalankan node ambil_ss.js patroli {link1} {link2} ...
        $escapedLinks = array_map('escapeshellarg', $patroliLinks);
        $cmd = 'node ' . escapeshellarg(__DIR__ . '/ambil_ss.js') . ' patroli ' . implode(' ', $escapedLinks);
        exec($cmd, $output, $ret);

        // Debug output
        error_log("Screenshot command executed: $cmd");
        error_log("Screenshot command output: " . implode("\n", $output));
        error_log("Screenshot command return code: $ret");

        // Ambil file hasil di folder ss/ dengan prefix patroli_
        $ssDir = __DIR__ . '/ss';
        $filesArr = [];
        foreach (glob($ssDir . '/patroli_*.jpg') as $f) {
            $filesArr[$f] = filemtime($f);
        }
        arsort($filesArr);
        // Check if we have any screenshots
        if (empty($filesArr)) {
            throw new Exception('Tidak ada file screenshot yang berhasil dibuat. Periksa koneksi dan status server.');
        }

        echo json_encode(['progress' => 'Mengolah hasil tangkapan layar patroli...', 'percent' => 20]);
        @ob_flush();
        @flush();

        $selectedFiles = array_slice(array_keys($filesArr), 0, count($patroliLinks));
        $processedCount = 0;
        $totalFiles = count($selectedFiles);

        echo json_encode(['progress' => "Memulai pengolahan $totalFiles tangkapan layar...", 'percent' => 20]);
        @ob_flush();
        @flush();

        foreach ($selectedFiles as $src) {
            $processedCount++;
            $dst = __DIR__ . '/foto/' . basename($src);

            // Calculate more detailed percentage progression without decimals
            $baseProgress = 20;
            $maxProgress = 30;
            $progressStep = ($maxProgress - $baseProgress) / $totalFiles;
            $currentPercentage = (int)($baseProgress + ($progressStep * $processedCount));

            // Send progress update for each screenshot being processed - more detailed progress info
            echo json_encode([
                'progress' => "Mengolah tangkapan layar patroli ($processedCount/$totalFiles): " . basename($src),
                'percent' => $currentPercentage
            ]);
            @ob_flush();
            @flush();

            // Ensure foto directory exists
            if (!is_dir(__DIR__ . '/foto')) {
                mkdir(__DIR__ . '/foto', 0755, true);
                error_log("Created foto directory: " . __DIR__ . '/foto');
            }

            copy($src, $dst);
            $screenshotPaths[] = $dst;
            error_log("Processed patrol screenshot: $src -> $dst");
        }

        // Hapus file screenshot patroli dari folder ss setelah diproses
        echo json_encode(['progress' => 'Membersihkan file tangkapan layar sementara...', 'percent' => 30]);
        @ob_flush();
        @flush();
        // Don't delete screenshot files immediately as other processes might need them
        // Cleanup will be handled at the end of the API call
        // foreach ($selectedFiles as $src) {
        //     @unlink($src);
        // }
    } else {
        // Upload file mode
        if (!isset($files['screenshotPatroli']) || empty($files['screenshotPatroli']['name'][0])) {
            throw new Exception('Tidak ada file screenshot patroli yang diupload. Harap pilih file gambar terlebih dahulu.');
        }

        // Ensure foto directory exists and is writable
        $fotoDir = __DIR__ . '/foto';
        if (!is_dir($fotoDir)) {
            mkdir($fotoDir, 0755, true);
            error_log("Created foto directory: $fotoDir");
        }

        if (!is_writable($fotoDir)) {
            error_log("Warning: foto directory not writable: $fotoDir");
            chmod($fotoDir, 0755);
            if (!is_writable($fotoDir)) {
                throw new Exception("Direktori foto tidak dapat ditulis. Mohon periksa izin folder.");
            }
        }

        $uploadedCount = 0;

        // Debug log uploaded files
        error_log("Screenshot Patroli files count: " . count($files['screenshotPatroli']['name']));
        foreach ($files['screenshotPatroli']['name'] as $index => $filename) {
            error_log("File $index: $filename, Error: " . $files['screenshotPatroli']['error'][$index] .
                ", Temp file exists: " . (file_exists($files['screenshotPatroli']['tmp_name'][$index]) ? 'Yes' : 'No'));
        }

        // Verify if we have any valid files
        for ($i = 0; $i < count($files['screenshotPatroli']['name']); $i++) {
            if (
                $files['screenshotPatroli']['error'][$i] === UPLOAD_ERR_OK &&
                !empty($files['screenshotPatroli']['tmp_name'][$i]) &&
                file_exists($files['screenshotPatroli']['tmp_name'][$i])
            ) {
                $uploadedCount++;
            }
        }

        if ($uploadedCount === 0) {
            throw new Exception('Tidak ada file screenshot patroli yang valid. Harap periksa file yang diupload.');
        }

        // Process valid files
        for ($i = 0; $i < count($files['screenshotPatroli']['name']); $i++) {
            if (
                isset($files['screenshotPatroli']['tmp_name'][$i]) &&
                $files['screenshotPatroli']['error'][$i] === UPLOAD_ERR_OK &&
                file_exists($files['screenshotPatroli']['tmp_name'][$i])
            ) {

                $originalPath = $files['screenshotPatroli']['tmp_name'][$i];
                $originalName = $files['screenshotPatroli']['name'][$i];

                // Generate unique filename to avoid collisions
                $uniqueName = uniqid('patroli_') . '_' . basename($originalName);
                $destinationPath = $fotoDir . '/' . $uniqueName;

                error_log("Uploading from $originalPath to $destinationPath");

                // Try direct copy first (sometimes works better than move_uploaded_file)
                if (copy($originalPath, $destinationPath)) {
                    $screenshotPaths[] = $destinationPath;
                    error_log("File copied successfully with copy(): $destinationPath");
                }
                // If copy fails, try move_uploaded_file
                elseif (move_uploaded_file($originalPath, $destinationPath)) {
                    $screenshotPaths[] = $destinationPath;
                    error_log("File moved successfully with move_uploaded_file(): $destinationPath");
                }
                // If both methods fail, report error
                else {
                    $errorMsg = 'Gagal menyimpan screenshot patroli: ' . $originalName;
                    error_log($errorMsg . ". PHP error: " . error_get_last()['message']);
                    throw new Exception($errorMsg);
                }

                // Verify file was saved properly
                if (!file_exists($destinationPath)) {
                    error_log("Warning: File wasn't saved properly: $destinationPath");
                } else {
                    error_log("Verified file exists: $destinationPath");
                }
            }
        }
    }

    // Final check to ensure we have screenshots
    if (empty($screenshotPaths)) {
        throw new Exception('Tidak ada screenshot patroli yang berhasil diproses. Silakan coba lagi dengan gambar yang valid.');
    }

    return $screenshotPaths;
}

function createWordFilePatroli($templatePath, $outputPath, $tanggalFormatted, $processedReports, $screenshotPaths)
{
    if (!file_exists($templatePath)) {
        return false;
    }

    try {
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

        // Replace date placeholder
        $tanggalFormattedFirst = ucfirst(strtolower($tanggalFormatted));
        $templateProcessor->setValue('tanggal', cleanTextForWord($tanggalFormattedFirst));
        $templateProcessor->setValue('tanggal_judul', cleanTextForWord($tanggalFormatted));

        // Prepare data for table rows grouped by platform
        $platforms = array_filter($processedReports, function ($reports) {
            return !empty($reports); // Skip platforms with empty reports
        });

        $templateProcessor->cloneBlock('platform', count($platforms), true, true);

        foreach (array_keys($platforms) as $platformIndex => $platform) {
            $reports = $processedReports[$platform];
            $templateProcessor->setValue("nama_platform#" . ($platformIndex + 1), ucwords(strtolower($platform)));

            // Clone rows for each report under the platform
            $templateProcessor->cloneRow("nama_akun#" . ($platformIndex + 1), count($reports));
            foreach ($reports as $reportIndex => $report) {
                // Clean all text data using the utility function
                $cleanName = cleanTextForWord($report['name']);
                $cleanLink = cleanTextForWord($report['link']);
                $cleanCategory = cleanTextForWord($report['category']);
                $cleanNarrative = cleanTextForWord($report['narrative']);
                
                $templateProcessor->setValue("nama_akun#" . ($platformIndex + 1) . "#" . ($reportIndex + 1), $cleanName);
                $templateProcessor->setValue("link#" . ($platformIndex + 1) . "#" . ($reportIndex + 1), $cleanLink);
                $templateProcessor->setValue("kategori#" . ($platformIndex + 1) . "#" . ($reportIndex + 1), $cleanCategory);
                $templateProcessor->setValue("narasi#" . ($platformIndex + 1) . "#" . ($reportIndex + 1), $cleanNarrative);
            }
        }

        // Ensure the number of screenshots matches the number of patrol narratives
        $totalReports = array_reduce($processedReports, function ($carry, $reports) {
            return $carry + count($reports);
        }, 0);

        if ($totalReports !== count($screenshotPaths)) {
            throw new Exception('Jumlah foto patroli tidak sesuai dengan jumlah narasi patroli.');
        }

        // Add patrol screenshots to the Word template
        $screenshotIndex = 0;
        foreach (array_keys($platforms) as $platformIndex => $platform) {
            $reports = $processedReports[$platform];
            foreach ($reports as $reportIndex => $report) {
                if (isset($screenshotPaths[$screenshotIndex]) && file_exists($screenshotPaths[$screenshotIndex])) {
                    $templateProcessor->setImageValue(
                        "foto_patroli#" . ($platformIndex + 1) . "#" . ($reportIndex + 1),
                        [
                            'path' => $screenshotPaths[$screenshotIndex],
                            'width' => 450,
                            'height' => 200,
                        ]
                    );
                }
                $screenshotIndex++;
            }
        }

        // Save the modified document
        $templateProcessor->saveAs($outputPath);

        return true;
    } catch (Exception $e) {
        throw new Exception('Gagal membuat file Word Patroli: ' . $e->getMessage());
    }
}

function createWordFileLandy($templatePath, $outputPath, $data)
{
    try {
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

        // Set single values with proper encoding
        $templateProcessor->setValue('tanggal_judul', cleanTextForWord($data['tanggal_judul']));
        $templateProcessor->setValue('tanggal', cleanTextForWord($data['tanggal']));

        // Clone rows for multiple accounts
        $templateProcessor->cloneRow('nama_akun', count($data['nama_akun']));
        foreach ($data['nama_akun'] as $index => $namaAkun) {
            $rowIndex = $index + 1;
            
            // Clean all text data using utility function
            $cleanNamaAkun = cleanTextForWord($namaAkun);
            $cleanKategori = cleanTextForWord($data['kategori'][$index]);
            $cleanNarasi = cleanTextForWord($data['narasi'][$index]);
            $cleanLink = cleanTextForWord($data['link'][$index]);
            
            $templateProcessor->setValue("nama_akun#{$rowIndex}", $cleanNamaAkun);
            $templateProcessor->setValue("kategori#{$rowIndex}", $cleanKategori);
            $templateProcessor->setValue("narasi#{$rowIndex}", $cleanNarasi);
            $templateProcessor->setValue("link#{$rowIndex}", $cleanLink);

            // Ensure the correct index is used for images
            if (isset($data['foto_patroli'][$index]) && file_exists($data['foto_patroli'][$index])) {
                $templateProcessor->setImageValue("foto_patroli#{$rowIndex}", [
                    'path' => $data['foto_patroli'][$index],
                    'width' => 300,
                    'height' => 200,
                    'ratio' => true
                ]);
            }
            
            if (isset($data['foto_upaya'][$index]) && file_exists($data['foto_upaya'][$index])) {
                $templateProcessor->setImageValue("foto_upaya#{$rowIndex}", [
                    'path' => $data['foto_upaya'][$index],
                    'width' => 300,
                    'height' => 200,
                    'ratio' => true
                ]);
            }
        }

        // Save the generated Word file
        $templateProcessor->saveAs($outputPath);
        echo json_encode(['progress' => 'File Word Patroli Landy berhasil dibuat...']);
        @ob_flush();
        @flush();
    } catch (Exception $e) {
        throw new Exception('Gagal membuat file Word Patroli Landy: ' . $e->getMessage());
    }
}

function createPdfFileLandy($templatePath, $outputPath, $fotoPatroli, $fotoUpaya)
{
    // Debug info
    error_log("Creating PDF Landy - Template: $templatePath");
    error_log("Creating PDF Landy - Output: $outputPath");
    error_log("Creating PDF Landy - Patrol photos count: " . count($fotoPatroli));
    error_log("Creating PDF Landy - Upaya photos count: " . count($fotoUpaya));

    // Load the HTML template
    $htmlContent = file_get_contents($templatePath);
    if (!$htmlContent) {
        throw new Exception("Failed to load PDF template: $templatePath");
    }
    // Process patrol images first (up to 8 images)
    for ($i = 1; $i <= 8; $i++) {
        $placeholder = "{{image_patroli_{$i}}}";

        if (isset($fotoPatroli[$i - 1])) {
            $imagePath = $fotoPatroli[$i - 1];
            error_log("Processing patrol image $i: $imagePath");

            if (file_exists($imagePath) && is_readable($imagePath)) {
                // Get proper mime type
                $mimeType = mime_content_type($imagePath);
                if (!$mimeType) {
                    $mimeType = 'image/jpeg'; // Default to JPEG if can't detect
                }

                // Read and encode image
                $imageData = file_get_contents($imagePath);
                if ($imageData) {
                    $imageBase64 = base64_encode($imageData);
                    $imageHtml = 'data:' . $mimeType . ';base64,' . $imageBase64;
                    $htmlContent = str_replace($placeholder, $imageHtml, $htmlContent);
                    error_log("Successfully encoded patrol image $i");
                } else {
                    error_log("Failed to read patrol image data from $imagePath");
                    $htmlContent = str_replace($placeholder, '', $htmlContent);
                }
            } else {
                error_log("Patrol image file doesn't exist or isn't readable: $imagePath");
                $htmlContent = str_replace($placeholder, '', $htmlContent);
            }
        } else {
            error_log("No patrol image defined for placeholder $placeholder");
            $htmlContent = str_replace($placeholder, '', $htmlContent);
        }
    }
    // Process upaya images second - similar processing (up to 8 images)
    for ($i = 1; $i <= 8; $i++) {
        $placeholder = "{{image_upaya_{$i}}}";

        if (isset($fotoUpaya[$i - 1])) {
            $imagePath = $fotoUpaya[$i - 1];
            error_log("Processing upaya image $i: $imagePath");

            if (file_exists($imagePath) && is_readable($imagePath)) {
                // Get proper mime type
                $mimeType = mime_content_type($imagePath);
                if (!$mimeType) {
                    $mimeType = 'image/jpeg'; // Default to JPEG if can't detect
                }

                // Read and encode image
                $imageData = file_get_contents($imagePath);
                if ($imageData) {
                    $imageBase64 = base64_encode($imageData);
                    $imageHtml = 'data:' . $mimeType . ';base64,' . $imageBase64;
                    $htmlContent = str_replace($placeholder, $imageHtml, $htmlContent);
                    error_log("Successfully encoded upaya image $i");
                } else {
                    error_log("Failed to read upaya image data from $imagePath");
                    $htmlContent = str_replace($placeholder, '', $htmlContent);
                }
            } else {
                error_log("Upaya image file doesn't exist or isn't readable: $imagePath");
                $htmlContent = str_replace($placeholder, '', $htmlContent);
            }
        } else {
            error_log("No upaya image defined for placeholder $placeholder");
            $htmlContent = str_replace($placeholder, '', $htmlContent);
        }
    }

    // Verify all placeholders are replaced
    if (
        strpos($htmlContent, '{{image_patroli_') !== false ||
        strpos($htmlContent, '{{image_upaya_') !== false
    ) {
        error_log("Warning: Some image placeholders were not replaced in the template");
    }

    // Generate the PDF using Dompdf
    $dompdf = new Dompdf([
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true,
        'debugPng' => true
    ]);
    $dompdf->loadHtml($htmlContent);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    // Save the PDF to the specified output path
    file_put_contents($outputPath, $dompdf->output());
    error_log("PDF Landy created successfully: $outputPath");

    echo json_encode(['progress' => 'Membuat file PDF Patroli Landy...']);
    @ob_flush();
    @flush();

    // Check if any images need to be cleaned up
    $templateWordDir = __DIR__ . '/template_word';
    $imageFiles = glob($templateWordDir . '/*.{jpg,jpeg,png}', GLOB_BRACE);
    if (count($imageFiles) > 0) {
        error_log("Cleaning up " . count($imageFiles) . " images from template_word directory");
        foreach ($imageFiles as $imgFile) {
            @unlink($imgFile);
        }
    }
}

// Fungsi untuk membuat file Word pagi
function createWordFilePagi($templatePath, $outputPath, $patroli, $upaya, $teks_laporan)
{
    // Pastikan sudah install PHPWord dan $templatePath ada
    if (!file_exists($templatePath)) {
        throw new Exception("Template Word Patroli Pagi tidak ditemukan.");
    }

    $phpWord = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

    // Set variabel dasar with proper encoding
    $phpWord->setValue('tahun', cleanTextForWord($teks_laporan['tahun'] ?? ''));
    $phpWord->setValue('isi_patroli', cleanTextForWord($teks_laporan['isi_patroli'] ?? ''));
    $phpWord->setValue('tanggal', cleanTextForWord($teks_laporan['tanggal'] ?? ''));
    $phpWord->setValue('tanggal_lampiran', cleanTextForWord($teks_laporan['tanggal_lampiran'] ?? ''));
    $phpWord->setValue('bulan_romawi', cleanTextForWord($teks_laporan['bulan_romawi'] ?? ''));

    // Siapkan data untuk clonerow patroli
    $nama_akun_patroli   = $patroli['nama_akun'] ?? [];
    $kategori_patroli    = $patroli['kategori'] ?? [];
    $narasi_patroli      = $patroli['narasi'] ?? [];
    $link_patroli        = $patroli['link'] ?? [];
    $foto_patroli        = $patroli['foto'] ?? [];
    $total               = count($nama_akun_patroli);

    // Siapkan data untuk clonerow upaya
    $nama_akun_upaya     = $upaya['nama_akun'] ?? [];
    $narasi_upaya        = $upaya['narasi'] ?? [];
    $link_upaya          = $upaya['link'] ?? [];
    $foto_upaya          = $upaya['foto'] ?? [];
    $total_upaya         = count($nama_akun_upaya);

    // Proses clonerow patroli
    if ($total > 0) {
        $phpWord->cloneRow('nama_akun_patroli', $total);
        for ($i = 0; $i < $total; $i++) {
            $no = $i + 1;
            // Clean all text data using the utility function
            $cleanNamaAkun = cleanTextForWord($nama_akun_patroli[$i] ?? '');
            $cleanKategori = cleanTextForWord($kategori_patroli[$i] ?? '');
            $cleanNarasi = cleanTextForWord($narasi_patroli[$i] ?? '');
            $cleanLink = cleanTextForWord($link_patroli[$i] ?? '');
            
            $phpWord->setValue("nama_akun_patroli#{$no}", $cleanNamaAkun);
            $phpWord->setValue("kategori#{$no}", $cleanKategori);
            $phpWord->setValue("narasi_patroli#{$no}", $cleanNarasi);
            $phpWord->setValue("link_patroli#{$no}", $cleanLink);

            // Jika ingin menyisipkan gambar screenshot patroli
            if (!empty($foto_patroli[$i]) && file_exists($foto_patroli[$i])) {
                $phpWord->setImageValue("foto_patroli#{$no}", array('path' => $foto_patroli[$i], 'width' => 350, 'height' => 250, 'ratio' => true));
            } else {
                $phpWord->setValue("foto_patroli#{$no}", '');
            }
        }
    }

    // Proses clonerow upaya
    if ($total_upaya > 0) {
        $phpWord->cloneRow('nama_akun_upaya', $total_upaya);
        for ($i = 0; $i < $total_upaya; $i++) {
            $no_upaya = $i + 1;
            // Clean all text data using the utility function
            $cleanNamaAkunPatroli = cleanTextForWord($nama_akun_patroli[$i] ?? '');
            $cleanNamaAkunUpaya = cleanTextForWord($nama_akun_upaya[$i] ?? '');
            $cleanKategoriUpaya = cleanTextForWord($kategori_patroli[$i] ?? '');
            $cleanNarasiUpaya = cleanTextForWord($narasi_upaya[$i] ?? '');
            $cleanLinkUpaya = cleanTextForWord($link_upaya[$i] ?? '');
            
            $phpWord->setValue("nama_akun_patroli_upaya#{$no_upaya}", $cleanNamaAkunPatroli);
            $phpWord->setValue("nama_akun_upaya#{$no_upaya}", $cleanNamaAkunUpaya);
            $phpWord->setValue("kategori_upaya#{$no_upaya}", $cleanKategoriUpaya);
            $phpWord->setValue("narasi_upaya#{$no_upaya}", $cleanNarasiUpaya);
            $phpWord->setValue("link_upaya#{$no_upaya}", $cleanLinkUpaya);

            // Jika ingin menyisipkan gambar upaya
            if (!empty($foto_upaya[$i]) && file_exists($foto_upaya[$i])) {
                $phpWord->setImageValue("foto_upaya#{$no_upaya}", array('path' => $foto_upaya[$i], 'width' => 350, 'height' => 250, 'ratio' => true));
            } else {
                $phpWord->setValue("foto_upaya#{$no_upaya}", '');
            }
        }
    }

    // Simpan file hasil Word
    $phpWord->saveAs($outputPath);
    echo json_encode(['progress' => 'Membuat file Word Patroli Pagi...']);
    @ob_flush();
    @flush();
}

function prosesPatrolReport($rawReport, $input = 'patroli', $range = 4)
{
    $platforms = ['FACEBOOK', 'INSTAGRAM', 'X', 'TIKTOK', 'SNACKVIDEO', 'YOUTUBE'];
    $groupedReports = array_fill_keys($platforms, []);
    $processedReports = array_fill_keys($platforms, []);
    $currentReport = [];

    $lines = explode("\n", $rawReport);

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip empty lines
        if (empty($line)) {
            if (count($currentReport) === $range) {
                if ($input === 'patroli') {
                    [$name, $link, $category, $narrative] = $currentReport;
                    $category = cleanTextForWord($category);
                } else {
                    [$name, $link, $narrative] = $currentReport;
                }

                // Validate and sanitize the link
                if (!filter_var($link, FILTER_VALIDATE_URL)) {
                    echo "<p style='color: red;'>Invalid link detected: {$link}</p>";
                    $currentReport = [];
                    continue;
                }


                // Validate and sanitize the link
                $link = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

                // Clean the data using the utility function
                $name = cleanTextForWord($name);
                $narrative = cleanTextForWord($narrative);

                // Determine the platform based on the link
                $platform = 'LAINNYA';
                if (strpos($link, 'facebook.com') !== false) {
                    $platform = 'FACEBOOK';
                } elseif (strpos($link, 'instagram.com') !== false) {
                    $platform = 'INSTAGRAM';
                } elseif (strpos($link, 'x.com') !== false) {
                    $platform = 'X';
                } elseif (strpos($link, 'tiktok.com') !== false) {
                    $platform = 'TIKTOK';
                } elseif (strpos($link, 'snackvideo.com') !== false) {
                    $platform = 'SNACKVIDEO';
                } elseif (strpos($link, 'youtube.com') !== false) {
                    $platform = 'YOUTUBE';
                }

                // Add the sanitized report to the grouped reports
                if ($input === 'patroli') {
                    $groupedReports[$platform][] = "Termonitor Akun {$name} ({$link}) membagikan postingan {$category} dengan  {$narrative}";
                }

                // Store the processed report grouped by platform
                if ($input === 'patroli') {
                    $processedReports[$platform][] = [
                        'name' => $name,
                        'link' => $link,
                        'category' => $category,
                        'narrative' => $narrative
                    ];
                } else {
                    $processedReports[$platform][] = [
                        'name' => $name,
                        'link' => $link,
                        'narrative' => $narrative
                    ];
                }

                // Reset the current report
                $currentReport = [];
            }
            continue;
        }

        // Collect lines for the current report
        $currentReport[] = $line;

        // echo "<pre>".var_dump($currentReport)."</pre>";
    }

    // Process the last report if it exists
    if (count($currentReport) === $range) {
        if ($input === 'patroli') {
            [$name, $link, $category, $narrative] = $currentReport;
        } else {
            [$name, $link, $narrative] = $currentReport;
        }
        // Validate and sanitize the link
        if (!filter_var($link, FILTER_VALIDATE_URL)) {
            echo "<p style='color: red;'>Invalid link detected: {$link}</p>";
        } else {
            $link = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
            if ($input === 'patroli') {
                $category = cleanTextForWord($category);
            }
            // Clean the data using the utility function
            $name = cleanTextForWord($name);
            $narrative = cleanTextForWord($narrative);

            // Determine the platform based on the link
            $platform = 'OTHER';
            if (strpos($link, 'facebook.com') !== false) {
                $platform = 'FACEBOOK';
            } elseif (strpos($link, 'instagram.com') !== false) {
                $platform = 'INSTAGRAM';
            } elseif (strpos($link, 'x.com') !== false) {
                $platform = 'X';
            } elseif (strpos($link, 'tiktok.com') !== false) {
                $platform = 'TIKTOK';
            } elseif (strpos($link, 'snackvideo.com') !== false) {
                $platform = 'SNACKVIDEO';
            } elseif (strpos($link, 'youtube.com') !== false) {
                $platform = 'YOUTUBE';
            }

            if ($input === 'patroli') {
                $groupedReports[$platform][] = "Termonitor Akun {$name} ({$link}) membagikan postingan {$category} dengan {$narrative}";
            }
            // Store the processed report grouped by platform
            if ($input === 'patroli') {
                $processedReports[$platform][] = [
                    'name' => $name,
                    'link' => $link,
                    'category' => $category,
                    'narrative' => $narrative
                ];
            } else {
                $processedReports[$platform][] = [
                    'name' => $name,
                    'link' => $link,
                    'narrative' => $narrative
                ];
            }
        }
    }

    return [
        'groupedReports' => $groupedReports,
        'processedReports' => $processedReports
    ];
}

/**
 * Bangun narasi patroli dan hitung total per platform.
 * @param array $groupedReports
 * @param array &$platformCounts
 * @param int &$totalPatroli
 * @return string
 */
function buildNarasiPatroli($groupedReports, &$platformCounts, &$totalPatroli)
{
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
    return $narasiPatroli;
}

// Helper function to clean image files from a directory
function cleanImageDirectory($directory)
{
    if (!is_dir($directory)) {
        return;
    }

    $imageFiles = glob($directory . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    $count = count($imageFiles);

    if ($count > 0) {
        error_log("Cleaning up $count images from $directory");
        foreach ($imageFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
}

/**
 * Proses pembuatan laporan KBD (Kabinda) dan hasilkan file Word & PDF.
 * @param array $platformCounts
 * @param string $tanggalNamaFile
 * @param string $tanggalFormatted
 * @param string $tanggalFormattedFirst
 * @param string $hariFormatted
 * @param string $bulan_romawi
 * @param string $hasilFolder
 * @param array $post
 * @param array $files
 * @param array $sheetsToRead
 * @param string $narasiPatroli
 * @param string &$narrative
 * @param string &$outputPathWordGeneral
 * @param string &$outputPathPdf
 * @param string &$outputPathWordPatroli
 */
function handleLaporanKBD(
    $platformCounts,
    $tanggalNamaFile,
    $tanggalFormatted,
    $tanggalFormattedFirst,
    $hariFormatted,
    $bulan_romawi,
    $hasilFolder,
    $post,
    $files,
    $sheetsToRead,
    $narasiPatroli,
    $totalPatroli,
    $processedReports,
    $screenshotPaths,
    &$narrative,
    &$outputPathWordGeneral,
    &$outputPathPdf,
    &$outputPathWordPatroli,
    $startProgress = 30,
    $progressRange = 20
) {
    // Calculate progress step size based on the number of operations
    $totalSteps = 7; // Total number of significant operations in this function
    $progressStep = $progressRange / $totalSteps;
    $currentProgress = $startProgress;
    // Report initial progress
    echo json_encode(['progress' => '1/7: Mempersiapkan data laporan KBD...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    $totalPatroliNarrative = [];
    foreach ($platformCounts as $platform => $count) {
        $platformFormatted = ucwords(strtolower($platform));
        $totalPatroliNarrative[] = "{$platformFormatted} ({$count} konten)";
    }
    $totalPatroliNarrativeString = (count($totalPatroliNarrative) > 1)
        ? implode(', ', array_slice($totalPatroliNarrative, 0, -1)) . ' dan ' . end($totalPatroliNarrative)
        : implode('', $totalPatroliNarrative);

    $fileName = "{$tanggalNamaFile} - PELAKSANAAN CIPKON DAN CIPOP MELALUI MEDIA SOSIAL DALAM RANGKA KONTER OPINI NEGATIF TERHADAP PRESIDEN PRABOWO SUBIANTO DI WILAYAH MERPATI – 14";

    // Step 1: Process cipop images
    $currentProgress += $progressStep;
    echo json_encode(['progress' => '2/7: Memproses gambar cipop laporan KBD...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    // Proses gambar cipop
    $cipopImageType = $post['cipopImageType'] ?? 'upload';
    $imagePaths = [];
    if ($cipopImageType === 'screenshot') {
        $cipopLinksRaw = $post['cipopScreenshotLinks'] ?? '';
        $cipopLinks = array_filter(array_map('trim', preg_split('/\r?\n/', $cipopLinksRaw)));
        if (count($cipopLinks) < 1 || count($cipopLinks) > 8) {
            throw new Exception('Masukkan minimal 1 dan maksimal 8 link untuk tangkapan layar cipop.');
        }
        $escapedLinks = array_map('escapeshellarg', $cipopLinks);
        $cmd = 'node ' . escapeshellarg(__DIR__ . '/ambil_ss.js') . ' cipop ' . implode(' ', $escapedLinks);
        exec($cmd, $output, $ret);
        $ssDir = __DIR__ . '/ss';
        $filesArr = [];
        foreach (glob($ssDir . '/cipop_*.jpg') as $f) {
            $filesArr[$f] = filemtime($f);
        }
        arsort($filesArr);
        $selectedFiles = array_slice(array_keys($filesArr), 0, count($cipopLinks));
        foreach ($selectedFiles as $src) {
            $dst = $hasilFolder . '/' . basename($src);
            copy($src, $dst);
            $imagePaths[] = $dst;
        }
        // Don't delete screenshot files immediately as other processes might need them
        // Cleanup will be handled at the end of the API call
        // foreach ($selectedFiles as $src) {
        //     @unlink($src);
        // }
    } else {
        if (!isset($files['imageFiles']) || count($files['imageFiles']['name']) < 1 || count($files['imageFiles']['name']) > 8) {
            throw new Exception('Harap unggah minimal 1 gambar dan maksimal 8 gambar.');
        }
        $currentProgress += $progressStep / 2;
        echo json_encode(['progress' => '2/7: Memproses file gambar cipop untuk laporan KBD...', 'percent' => (int)$currentProgress]);
        @ob_flush();
        @flush();

        for ($i = 0; $i < count($files['imageFiles']['name']); $i++) {
            if (isset($files['imageFiles']['tmp_name'][$i]) && $files['imageFiles']['error'][$i] === UPLOAD_ERR_OK) {
                $originalPath = $files['imageFiles']['tmp_name'][$i];
                $destinationPath = __DIR__ . '/template_pdf/' . basename($files['imageFiles']['name'][$i]);
                if (compressImage($originalPath, $destinationPath, 15)) {
                    $imagePaths[] = $destinationPath;
                } else {
                    throw new Exception('Gagal mengompresi gambar: ' . $files['imageFiles']['name'][$i]);
                }
            } else {
                $imagePaths[] = null;
            }
        }
    }    // Step 2: Process Excel files
    $currentProgress += $progressStep;
    echo json_encode(['progress' => '3/7: Memproses data Excel untuk laporan KBD...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    $result = prosesExcelFiles($files['excelFiles'], $sheetsToRead);
    $dataAkun = $result['dataAkun'];
    $dataLink = $result['dataLink'];
    $jumlahAkunperSheet = $jumlahLinkperSheet = $jumlahDataPerSheet = [];
    foreach ($dataAkun as $sheetName => $groupedData) {
        $jumlahDataPerSheet[$sheetName]['totalAkun'] = count($groupedData);
        $jumlahAkunperSheet[$sheetName] = count($groupedData);
    }
    foreach ($dataLink as $sheetName => $dataRows) {
        $jumlahDataPerSheet[$sheetName]['totalLink'] = count($dataRows);
        $jumlahLinkperSheet[$sheetName] = count($dataRows);
    }    // Step 3: Generate narrative
    $currentProgress += $progressStep;
    echo json_encode(['progress' => '4/7: Menyusun narasi laporan KBD...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

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
EOD;    // Step 4: Generate Word document
    $currentProgress += $progressStep;
    echo json_encode(['progress' => '5/7: Membuat file Word laporan KBD umum...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    // Generate Word & PDF
    $templatePathWordGeneral = __DIR__ . '/template_word/template_viral.docx';
    $outputPathWordGeneral = $hasilFolder . "/{$fileName}.docx";
    createWordFile($templatePathWordGeneral, $outputPathWordGeneral, $tanggalFormatted, $jumlahDataPerSheet, $dataLink);
    // Step 5: Generate PDF
    $currentProgress += $progressStep;
    echo json_encode(['progress' => '6/7: Membuat file PDF laporan KBD...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();
    $templatePathHtml = __DIR__ . '/template_pdf/template_kbd.html';
    $outputPathPdf = $hasilFolder . "/{$fileName}.pdf";
    createPdfFile($templatePathHtml, $outputPathPdf, $tanggalFormatted, $hariFormatted, $tanggalFormattedFirst, $jumlahLinkperSheet, $imagePaths);
    // Step 6: Generate Word Patrol Report
    $currentProgress += $progressStep;
    echo json_encode(['progress' => '7/7: Membuat file Word hasil patroli KBD...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    $templatePathWordPatroli = __DIR__ . '/template_word/template_Patroli_kbd.docx';
    $outputPathWordPatroli = $hasilFolder . "/HASIL PATROLI SIBER TERKAIT OPINI NEGATIF TERHADAP PRESIDEN PRABOWO SUBIANTO DI WIL. MERPATI – 14 TANGGAL {$tanggalFormatted}.docx";
    createWordFilePatroli($templatePathWordPatroli, $outputPathWordPatroli, $tanggalFormatted, $processedReports, $screenshotPaths);

    // Final step: Complete KBD report processing
    $currentProgress += $progressStep;
    echo json_encode(['progress' => 'Laporan KBD selesai dibuat...', 'percent' => (int)($startProgress + $progressRange)]);
    @ob_flush();
    @flush();

    // Output by reference - set the actual reference variables
    // These variables are already modified by reference in the function
}

/**
 * Proses pembuatan laporan Patroli Landy (Word & PDF).
 * @param array $processedReports
 * @param string $tanggalFormatted
 * @param string $tanggalFormattedFirst
 * @param string $hasilFolder
 * @param array $post
 * @param array $files
 * @param array $screenshotPaths
 * @param string &$narasiPatroliLandy
 * @param string &$outputPathLandy
 * @param string &$outputPathPdfLandy
 */
function handlePatroliLandy(
    $processedReports,
    $tanggalFormatted,
    $tanggalFormattedFirst,
    $hasilFolder,
    $post,
    $files,
    $screenshotPaths,
    &$narasiPatroliLandy,
    &$outputPathLandy,
    &$outputPathPdfLandy,
    $judulLandy = 'Pemetaan Akun Medsos Narasi Negatif MBG',
    $startProgress = 30,
    $progressRange = 20
) {
    // Calculate progress step size based on the number of operations
    $totalSteps = 5; // Total number of significant operations in this function
    $progressStep = $progressRange / $totalSteps;
    $currentProgress = $startProgress;
    // Report initial progress
    echo json_encode(['progress' => '1/5: Mempersiapkan laporan Patroli Landy...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    // Step 1: Build patrol narrative
    $currentProgress += $progressStep;
    echo json_encode(['progress' => '2/5: Menyusun narasi Patroli Landy...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    // Bangun narasi patroli landy
    $isiPatroliLandy = "";
    $no = 1;
    foreach ($processedReports as $platform => $reports) {
        if (!empty($reports)) {
            $platformFormatted = strtoupper($platform);
            $isiPatroliLandy .= "*{$platformFormatted}*\n\n";
            
            $platformNo = 1;
            foreach ($reports as $report) {
                // Sanitize data for Patroli Landy narrative
                $cleanName = cleanTextForWord($report['name']);
                $cleanLink = cleanTextForWord($report['link']);
                $cleanCategory = cleanTextForWord($report['category']);
                $cleanNarrative = cleanTextForWord($report['narrative']);
                $profiling = $report['profiling'] ?? '';
                $tanggal_postingan = $report['tanggal_postingan'] ?? '';
                $wilayah = $report['wilayah'] ?? '';
                $korelasi = $report['korelasi'] ?? '';
                $afiliasi = $report['afiliasi'] ?? '';
                
                // Format ringkasan
                $isiPatroliLandy .= "{$platformNo}.\tTermonitor akun {$platformFormatted} {$cleanName} ({$cleanLink}) memposting narasi provokatif yaitu {$cleanNarrative}\n\n";
                
                if (!empty($cleanName)) {
                    $isiPatroliLandy .= "Berdasarkan pendalaman, akun tersebut dikelola oleh {$cleanName}, dengan profil sebagai berikut:\n\n";
                }
                
                // Format detail
                $isiPatroliLandy .= "*Akun {$platform} {$cleanName}*\n";
                $isiPatroliLandy .= "a. Tanggal Postingan: {$tanggal_postingan}\n";
                $isiPatroliLandy .= "b. Wilayah: {$wilayah}\n";
                $isiPatroliLandy .= "c. Nama Akun: {$cleanName}\n";
                $isiPatroliLandy .= "d. Link Akun: {$cleanLink}\n";
                $isiPatroliLandy .= "e. Resume Narasi Propaganda: {$cleanNarrative}\n";
                $isiPatroliLandy .= "f. Profiling Singkat Akun: {$profiling}\n";
                $isiPatroliLandy .= "g. Korelasi Dengan Akun Lainnya: {$korelasi}\n";
                $isiPatroliLandy .= "h. Afiliasi Dengan Influencer/Tokoh Prominen/Pemilik Pasukan Buzzer: {$afiliasi}\n\n";
                
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

    // Step 2: Process RAS/upaya files
    $currentProgress += $progressStep;
    echo json_encode(['progress' => 'Memproses upaya RAS Patroli Landy...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    // Upaya screenshot logic
    $foto_upaya = isset($files['rasFiles']) ? $files['rasFiles']['tmp_name'] : [];

    // Data untuk template
    $nama_akun = $kategori = $narasi = $link = [];
    foreach ($processedReports as $platform => $reports) {
        foreach ($reports as $report) {
            // Sanitize data for Patroli Landy Word document
            $nama_akun[] = cleanTextForWord($report['name']);
            $kategori[] = cleanTextForWord($report['category']);
            $narasi[] = cleanTextForWord($report['narrative']);
            $link[] = cleanTextForWord($report['link']);
        }
    }
    $tanggal_judul = $tanggalFormatted;
    $tanggal = $tanggalFormattedFirst;
    $foto_patroli = $screenshotPaths;

    $totalReports = count($nama_akun);
    
    // Debug validation conditions
    error_log("Patroli Landy validation - Total reports: " . $totalReports);
    error_log("Patroli Landy validation - Foto patroli count: " . count($foto_patroli));
    error_log("Patroli Landy validation - Foto upaya count: " . count($foto_upaya));
    
    if (count($foto_patroli) !== $totalReports) {
        $error = "Jumlah screenshot patroli (" . count($foto_patroli) . ") harus sama dengan jumlah laporan yang diproses ($totalReports).";
        error_log("Patroli Landy error: " . $error);
        throw new Exception($error);
    }
    if (count($foto_upaya) !== $totalReports) {
        $error = "Jumlah foto upaya (" . count($foto_upaya) . ") harus sama dengan jumlah laporan yang diproses ($totalReports).";
        error_log("Patroli Landy error: " . $error);
        throw new Exception($error);
    }    // Step 3: Create Word document
    $currentProgress += $progressStep;
    echo json_encode(['progress' => '3/5: Membuat file Word Patroli Landy...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    $templatePathLandy = __DIR__ . '/template_word/template_patroli_landy.docx';
    $tempOutputPathLandy = $hasilFolder . "/PATROLI SIBER DAN UPAYA KONTRA OPINI TERHADAP AKUN YANG MENDISKREDITKAN PRESIDEN PRABOWO SUBIANTO UPDATE TANGGAL {$tanggalFormatted}.docx";
    createWordFileLandy($templatePathLandy, $tempOutputPathLandy, [
        'nama_akun' => $nama_akun,
        'tanggal_judul' => $tanggal_judul,
        'tanggal' => $tanggal,
        'kategori' => $kategori,
        'narasi' => $narasi,
        'link' => $link,
        'foto_patroli' => $foto_patroli,
        'foto_upaya' => $foto_upaya
    ]);    // Step 4: Create PDF document
    $currentProgress += $progressStep;
    echo json_encode(['progress' => '4/5: Membuat file PDF Patroli Landy...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    $templatePathHtmlLandy = __DIR__ . '/template_pdf/template_patroli.html';
    $tempOutputPathPdfLandy = $hasilFolder . "/LAMPIRAN PATROLI SIBER DAN UPAYA KONTRA OPINI TERHADAP AKUN YANG MENDISKREDITKAN PRESIDEN PRABOWO SUBIANTO DI WILAYAH MERPATI - 14 PADA {$tanggalFormatted}.pdf";
    createPdfFileLandy($templatePathHtmlLandy, $tempOutputPathPdfLandy, $foto_patroli, $foto_upaya);
    
    // Step 5: Complete Landy report processing
    $currentProgress += $progressStep;
    echo json_encode(['progress' => '5/5: Laporan Patroli Landy selesai dibuat...', 'percent' => (int)($startProgress + $progressRange)]);
    @ob_flush();
    @flush();

    // Only set the reference parameters after both files are created successfully
    // Check if files were actually created before setting the output paths
    if (file_exists($tempOutputPathLandy)) {
        $outputPathLandy = $tempOutputPathLandy;
    }
    if (file_exists($tempOutputPathPdfLandy)) {
        $outputPathPdfLandy = $tempOutputPathPdfLandy;
    }

    // Note: $narasiPatroliLandy was already set via reference in the heredoc above
}

/**
 * Proses pembuatan laporan Patroli Pagi (Word & PDF).
 * @param array $processedReports
 * @param string $tanggalFormatted
 * @param string $tanggalFormattedFirst
 * @param string $bulan_romawi
 * @param string $hasilFolder
 * @param array $post
 * @param array $files
 * @param array $screenshotPaths
 * @param string &$narasiPatroliPagi
 * @param string &$outputPathPagi
 * @param string &$outputPathPdfPagi
 */
function handlePatroliPagi(
    $processedReports,
    $tanggalFormatted,
    $tanggalFormattedFirst,
    $bulan_romawi,
    $hasilFolder,
    $post,
    $files,
    $screenshotPaths,
    &$narasiPatroliPagi,
    &$outputPathPagi,
    &$outputPathPdfPagi,
    $startProgress = 30,
    $progressRange = 20
) {
    // Calculate progress step size based on the number of operations
    $totalSteps = 6; // Total number of significant operations in this function
    $progressStep = $progressRange / $totalSteps;
    $currentProgress = $startProgress;
    // Report initial progress
    echo json_encode(['progress' => '1/6: Mempersiapkan laporan Patroli Pagi...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    // Step 1: Process upaya data
    $currentProgress += $progressStep;
    echo json_encode(['progress' => '2/6: Memproses data upaya Patroli Pagi...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    $rawUpaya = $post['input_upaya'] ?? '';
    $hasilUpaya = prosesPatrolReport($rawUpaya, 'upaya', 3);
    $processedUpaya = $hasilUpaya['processedReports'];

    $akunPatroli = [];
    foreach ($processedReports as $platform => $reports) {
        foreach ($reports as $report) {
            // Sanitize patrol account name for upaya processing
            $akunPatroli[] = cleanTextForWord($report['name']);
        }
    }

    $narasiUpayaPagi = '';
    $idxPatroli = 0;
    foreach ($processedUpaya as $platform => $upayaList) {
        foreach ($upayaList as $upaya) {
            // Sanitize all text data for upaya narratives
            $nama_akun_patroli = cleanTextForWord($akunPatroli[$idxPatroli] ?? '-');
            $nama_akun_upaya = cleanTextForWord($upaya['name'] ?? '-');
            $link = cleanTextForWord($upaya['link'] ?? '-');
            $narasi = cleanTextForWord($upaya['narrative'] ?? '-');
            $point = chr(97 + $idxPatroli) . '.';
            $narasiUpayaPagi .= "{$point} Upaya Kontra & Takedown terhadap Akun ({$nama_akun_patroli}) dengan membuat postingan melalui akun ({$nama_akun_upaya}) ({$link}) dengan {$narasi}\n\n";
            $idxPatroli++;
        }
    }    // Step 2: Generate patrol narrative
    $currentProgress += $progressStep;
    echo json_encode(['progress' => '3/6: Menyusun narasi patroli pagi...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    $isiPatroliPagi = "";
    $noPatroliPagi = 0;
    foreach ($processedReports as $platform => $reports) {
        if (!empty($reports)) {
            $platformFormatted = ucwords(strtolower($platform));
            foreach ($reports as $report) {
                $point = chr(97 + $noPatroliPagi) . '.';
                // Sanitize data for Patroli Pagi narrative
                $cleanName = cleanTextForWord($report['name']);
                $cleanLink = cleanTextForWord($report['link']);
                $cleanCategory = cleanTextForWord($report['category']);
                $cleanNarrative = cleanTextForWord($report['narrative']);
                $isiPatroliPagi .= "{$point} Termonitor Akun ({$cleanName}) ({$cleanLink}) membagikan postingan {$cleanCategory} dengan {$cleanNarrative}\n\n";
                $noPatroliPagi++;
            }
        }
    }

    // Step 3: Prepare data for document creation
    $currentProgress += $progressStep;
    echo json_encode(['progress' => 'Mempersiapkan data dokumen patroli pagi...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    $nama_akun = $kategori = $narasi = $link = [];
    foreach ($processedReports as $platform => $reports) {
        foreach ($reports as $report) {
            // Sanitize data for Patroli Pagi Word document
            $nama_akun[] = cleanTextForWord($report['name']);
            $kategori[] = cleanTextForWord($report['category']);
            $narasi[] = cleanTextForWord($report['narrative']);
            $link[] = cleanTextForWord($report['link']);
        }
    }
    $nama_akun_upaya = $narasi_upaya = $link_upaya = [];
    foreach ($processedUpaya as $platform => $reports) {
        foreach ($reports as $report) {
            // Sanitize upaya data for Word document compatibility
            $nama_akun_upaya[] = cleanTextForWord($report['name'] ?? '-');
            $narasi_upaya[] = cleanTextForWord($report['narrative'] ?? '-');
            $link_upaya[] = cleanTextForWord($report['link'] ?? '-');
        }
    }    // Step 4: Process upaya screenshots
    $currentProgress += $progressStep;
    echo json_encode(['progress' => '4/6: Memproses upaya screenshot patroli pagi...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    $upayaScreenshotType = $post['upayaScreenshotType'] ?? 'upload';
    $foto_upaya = [];
    if ($upayaScreenshotType === 'screenshot') {
        $upayaLinks = [];
        if (isset($processedUpaya)) {
            foreach ($processedUpaya as $platform => $reports) {
                foreach ($reports as $report) {
                    if (!empty($report['link'])) {
                        $upayaLinks[] = $report['link'];
                    }
                }
            }
        }
        if (count($upayaLinks) < 1) {
            throw new Exception('Tidak ada link pada hasil upaya untuk tangkapan layar upaya.');
        }
        $escapedLinks = array_map('escapeshellarg', $upayaLinks);
        $cmd = 'node ' . escapeshellarg(__DIR__ . '/ambil_ss.js') . ' upaya ' . implode(' ', $escapedLinks);
        exec($cmd, $output, $ret);
        $ssDir = __DIR__ . '/ss';
        $filesArr = [];
        foreach (glob($ssDir . '/upaya_*.jpg') as $f) {
            $filesArr[$f] = filemtime($f);
        }
        arsort($filesArr);
        $selectedFiles = array_slice(array_keys($filesArr), 0, count($upayaLinks));
        foreach ($selectedFiles as $src) {
            $dst = __DIR__ . '/template_word/' . basename($src);
            copy($src, $dst);
            $foto_upaya[] = $dst;
        }
        // Don't delete screenshot files immediately as other processes might need them
        // Cleanup will be handled at the end of the API call
        // foreach ($selectedFiles as $src) {
        //     @unlink($src);
        // }
    } else {
        $foto_upaya = isset($files['upayaFiles']) ? $files['upayaFiles']['tmp_name'] : [];
    }

    $tanggal_judul = $tanggalFormatted;
    $foto_patroli = $screenshotPaths;
    $tahun_input = date('Y', strtotime($post['tanggal'] ?? date('Y-m-d')));
    $totalReports = count($nama_akun);
    $totalUpaya = count($nama_akun_upaya);

    if (count($foto_patroli) !== $totalReports) throw new Exception('Jumlah screenshot patroli harus sama dengan jumlah laporan yang diproses.');
    if (count($foto_upaya) !== $totalReports) throw new Exception('Jumlah foto upaya harus sama dengan jumlah laporan yang diproses.');

    $narasiPatroliPagi = <<<EOD
*Kepada Yth:*

*1. Rajawali*
*2. Elang*

*Dari: Merpati-14*

*Tembusan : Yth.*
*1. Kasuari-2*
*2. Kasuari-9*
*3. Kasuari-21*
*4. Kasuari-23*

*Perihal : Patroli Siber di Wilayah Merpati-14 ({$tanggalFormattedFirst})*

*1. KEGIATAN PATROLI SIBER*

*A. EXECUTIVE SUMMARY*

Pada {$tanggalFormattedFirst} di Jambi, Provinsi Jambi, telah dilakukan giat Patroli Siber terkait keberadaan konten/akun/postingan negatif yang bersifat hoax, provokatif, ujaran kebencian, dukungan terhadap Khilafah, memecah belah NKRI, isu kebangkitan PKI maupun tanggapan negatif terhadap kebijakan Pemerintah dengan hasil {$totalReports} temuan konten dan telah dilakukan {$totalUpaya} upaya kontra kicau, serta upaya Takedown.

*B. HAL MENONJOL*

 Hasil monitoring di media sosial terhadap konten negatif telah ditemukan sebanyak {$totalReports} konten. Selanjutnya hasil monitoring konten-konten menonjol di media sosial, antara lain:

 *1. Provokasi dan Ujaran Kebencian*

{$isiPatroliPagi}*C. LANGKAH TIM SIBER WILAYAH MERPATI-14:* 

Dalam menyikapi penyebaran konten negatif di media sosial, Tim Siber Wilayah Merpati-14 melakukan Kontra Kicau, al:

*1. Kontra Kicau dan Upaya Takedown postingan negatif di Medsos :*

{$narasiUpayaPagi}
*D. CATATAN*

 Hingga saat ini konten/akun/situs/postingan negatif, menghina, dan provokatif media sosial di wilayah Merpati-14 rata-rata berada pada akun palsu dan disebarkan melalui Facebook. 

*E. LANGKAH TINDAK*

1. Melakukan profiling akun, grup, dan website, serta counter terhadap topik atau isu menonjol dengan melakukan diseminasi gambar atau konten grafis di media sosial.
2. Melakukan pemantauan terhadap akun yang menyebarkan berita atau isu yang menyudutkan pemerintahan.
3. Melakukan pemetaan terhadap postingan ataupun berita tendensius dan hoax serta penyebarnya yang tersebar di dunia maya.
4. Melakukan kontra terhadap isu sensitif yang efeknya diperkirakan cukup besar dan nyata baik dengan tulisan maupun dengan meme yang bersifat menarik.
5. Membangun jaringan di dunia maya dengan pemangku kepentingan lain guna menangkal penyebaran konten negatif.

*F. SARAN TINDAK*
 Merpati-14 menyarankan kepada Jajaran Kasuari-VI untuk membantu memblokir/ merusak Akun Provokatif yang menjadi temuan Merpati-14.

*DUMP. TTD: Merpati - 14*
EOD;    // Step 5: Create Word document for Patroli Pagi
    $currentProgress += $progressStep;
    echo json_encode(['progress' => '5/6: Membuat file Word Patroli Pagi...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    $templatePathPagi = __DIR__ . '/template_word/template_Patroli_pagi.docx';
    $tempOutputPathPagi = $hasilFolder . "/Laporan Patroli Siber Konten Negatif di Wilayah Merpati-14 Update  {$tanggalFormatted}.docx";
    $tempOutputPathPdfPagi = $hasilFolder . "/Lampiran Patroli Siber Merpati 14 ({$tanggalFormatted}).pdf";
    createWordFilePagi($templatePathPagi, $tempOutputPathPagi, [
        'nama_akun' => $nama_akun,
        'kategori' => $kategori,
        'narasi' => $narasi,
        'link' => $link,
        'foto' => $foto_patroli,
    ], [
        'nama_akun' => $nama_akun_upaya,
        'narasi' => $narasi_upaya,
        'link' => $link_upaya,
        'foto' => $foto_upaya
    ], [
        'tanggal_lampiran' => $tanggal_judul,
        'tanggal' => $tanggalFormattedFirst,
        'bulan_romawi' => $bulan_romawi,
        'isi_patroli'    => $isiPatroliPagi,
        'tahun' => $tahun_input
    ]);    // Step 6: Create PDF document for Patroli Pagi
    $currentProgress += $progressStep;
    echo json_encode(['progress' => '6/6: Membuat file PDF Patroli Pagi...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    $templatePathHtmlPagi = __DIR__ . '/template_pdf/template_patroli.html';
    createPdfFileLandy($templatePathHtmlPagi, $tempOutputPathPdfPagi, $foto_patroli, $foto_upaya);

    // Final step: Complete Patroli Pagi processing
    echo json_encode(['progress' => 'Laporan Patroli Pagi selesai dibuat!', 'percent' => (int)($startProgress + $progressRange)]);
    @ob_flush();
    @flush();

    // Only set the reference parameters after both files are created successfully
    // Check if files were actually created before setting the output paths
    if (file_exists($tempOutputPathPagi)) {
        $outputPathPagi = $tempOutputPathPagi;
    }
    if (file_exists($tempOutputPathPdfPagi)) {
        $outputPathPdfPagi = $tempOutputPathPdfPagi;
    }

    // Note: $narasiPatroliPagi was already set via reference in the heredoc above
}

/**
 * Delete a file safely
 * @param string $filePath Path to the file to delete
 * @return bool True if file was deleted or doesn't exist, false on error
 */
function deleteFile($filePath)
{
    if (!file_exists($filePath)) {
        return true;
    }

    if (is_file($filePath)) {
        return @unlink($filePath);
    }

    return false;
}

/**
 * Delete a directory and its contents recursively
 * @param string $dirPath Path to the directory to delete
 * @return bool True if directory was deleted successfully, false otherwise
 */
function deleteDirectory($dirPath)
{
    if (!is_dir($dirPath)) {
        return false;
    }

    $files = array_diff(scandir($dirPath), ['.', '..']);

    foreach ($files as $file) {
        $path = $dirPath . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            deleteFile($path);
        }
    }

    return @rmdir($dirPath);
}

/**
 * Clean up the hasil directory
 * @param string $dirPath Path to the hasil directory
 * @return array Status of the cleanup operation
 */
function cleanupHasilDirectory($dirPath = 'hasil')
{
    if (!is_dir($dirPath)) {
        return [
            'success' => false,
            'message' => 'Directory does not exist'
        ];
    }

    $result = deleteDirectory($dirPath);

    // Recreate the directory after deletion
    if ($result) {
        mkdir($dirPath, 0777, true);
    }

    return [
        'success' => $result,
        'message' => $result ? 'Directory cleaned successfully' : 'Error cleaning directory'
    ];
}

/**
 * Utility function to clean text for Word document compatibility
 * @param string $text The text to clean
 * @return string The cleaned text
 */
function cleanTextForWord($text) {
    if (empty($text)) {
        return '';
    }
    
    // Trim whitespace
    $text = trim($text);
    
    // First, decode any existing HTML entities to prevent double encoding
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Fix specific corruption issues: replace standalone "quot" with quotes
    $text = preg_replace('/\bquot\b/i', '"', $text);
    $text = preg_replace('/\bamp\b/i', '&', $text);
    $text = preg_replace('/\blt\b/i', '<', $text);
    $text = preg_replace('/\bgt\b/i', '>', $text);
    
    // Convert to UTF-8 if not already
    if (!mb_check_encoding($text, 'UTF-8')) {
        $text = mb_convert_encoding($text, 'UTF-8', 'auto');
    }
    
    // Replace smart quotes and dashes with regular ones
    $text = str_replace([
        "\u{201C}", "\u{201D}", // Smart double quotes
        "\u{2018}", "\u{2019}", // Smart single quotes
        "\u{2013}", "\u{2014}", // En dash, Em dash
        "\u{2026}", // Ellipsis
        "\u{201C}", "\u{201D}", // Additional smart quotes (left and right)
        "\u{2018}", "\u{2019}" // Additional smart apostrophes (left and right)
    ], [
        '"', '"',
        "'", "'", 
        '-', '-',
        '...',
        '"', '"',
        "'", "'"
    ], $text);
    
    // Remove control characters and other problematic characters
    $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
    
    // Keep only safe characters: letters, numbers, spaces, and basic punctuation
    // Allow quotes, commas, periods, parentheses, exclamation, question marks, and hyphens
    $text = preg_replace('/[^\w\s".,()!?\-]/u', '', $text);
    
    // Don't apply htmlspecialchars for Word documents as it causes "quot" issues
    // Word documents can handle regular quotes and basic characters fine
    
    return $text;
}
