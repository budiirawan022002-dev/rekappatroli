<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use Dompdf\Dompdf; // Untuk membuat file PDF

/**
 * Convert UPPERCASE text to Title Case
 * Handles Indonesian names and special cases
 * 
 * @param string $text Text to convert
 * @return string Converted text
 */
function toTitleCase($text) {
    if (empty($text)) return $text;
    
    // Jika bukan UPPERCASE semua, kembalikan apa adanya
    if ($text !== strtoupper($text)) {
        return $text;
    }
    
    // Convert to Title Case dengan mb_convert_case untuk support UTF-8
    $result = mb_convert_case(strtolower($text), MB_CASE_TITLE, "UTF-8");
    
    // Handle Roman numerals (I-XXX) - must stay uppercase
    // Match patterns like: I, II, III, IV, V, VI, VII, VIII, IX, X, XI, XII, ..., XXIV, XXX
    $result = preg_replace_callback(
        '/\b([Xx]{1,3}[Ii]{1,3}|[Xx]{1,3}[Ii]?[Vv]|[Xx]?[Ii]{1,3}[Vv]?|[Xx]{1,3}|[Ii]{1,3}[Vv]?|[Vv][Ii]{0,3})\b/',
        function($matches) {
            return strtoupper($matches[0]);
        },
        $result
    );
    
    return $result;
}

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

    error_log("=== Starting Excel file processing ===");
    error_log("Number of files to process: " . count($files['name']));
    error_log("Sheets to read: " . implode(', ', $sheetsToRead));

    // Loop melalui semua file yang diunggah
    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $uploadedFile = $files['tmp_name'][$i];
            $fileName = $files['name'][$i];
            
            error_log("Processing file #" . ($i + 1) . ": $fileName");
            error_log("File size: " . filesize($uploadedFile) . " bytes");

            try {
                // Load file Excel dengan error handling yang lebih baik
                $spreadsheet = IOFactory::load($uploadedFile);
                
                // Log informasi sheet yang tersedia
                $availableSheets = $spreadsheet->getSheetNames();
                error_log("File '$fileName' - Available sheets: " . implode(', ', $availableSheets));
                
            } catch (Exception $e) {
                error_log("Error loading Excel file '$fileName': " . $e->getMessage());
                // Coba dengan reader yang lebih toleran
                try {
                    $reader = IOFactory::createReader('Xlsx');
                    $reader->setReadDataOnly(true); // Hanya baca data, abaikan formula
                    $reader->setReadEmptyCells(false);
                    $spreadsheet = $reader->load($uploadedFile);
                    error_log("Successfully loaded '$fileName' with alternative reader");
                } catch (Exception $e2) {
                    error_log("Failed to load '$fileName' with alternative reader: " . $e2->getMessage());
                    throw new Exception("Gagal memuat file Excel '$fileName': " . $e2->getMessage());
                }
            }

            foreach ($sheetsToRead as $sheetName) {
                error_log("--- Processing sheet: '$sheetName' in file '$fileName' ---");
                
                try {
                    // Periksa apakah sheet ada
                    if ($spreadsheet->sheetNameExists($sheetName)) {
                        error_log("Sheet '$sheetName' found in file '$fileName'");
                        
                        // Ambil sheet berdasarkan nama
                        $sheet = $spreadsheet->getSheetByName($sheetName);
                        $highestRow = $sheet->getHighestRow();
                        $highestColumn = $sheet->getHighestColumn();
                        
                        error_log("Sheet dimensions: $highestRow rows x $highestColumn columns");

                        // Array untuk menyimpan data dari sheet ini
                        $dataRows = [];
                        $groupedData = []; // Array untuk mengelompokkan nilai kolom grouping
                        $rowIndex = 0; // Inisialisasi indeks baris
                        $processedRowCount = 0;
                        $linkDetectionCount = 0;
                        $skippedNoLinkCount = 0;
                        $skippedInvalidLinkCount = 0;
                        $carryForwardCount = 0;
                        
                        // Variable to store the last valid account name for carry-forward
                        $lastValidAccountName = null;

                        foreach ($sheet->getRowIterator() as $row) {
                            $rowIndex++;
                            if ($rowIndex <= 3) {
                                continue; // Lewati baris 1, 2, dan 3
                            }

                            $cellIterator = $row->getCellIterator();
                            $cellIterator->setIterateOnlyExistingCells(false); // Loop semua sel, termasuk yang kosong

                            $data = [];
                            $cellIndex = 0;
                            foreach ($cellIterator as $cell) {
                                if ($cellIndex <= 6) { // Ambil kolom 0 sampai 6 (7 kolom total)
                                    // Get cell value safely - handle formulas, objects, and other types
                                    $cellValue = $cell->getValue();
                                    
                                    // If it's an object or array, try to get string representation
                                    if (is_object($cellValue) || is_array($cellValue)) {
                                        // Try to get calculated value for formulas
                                        try {
                                            $calculatedValue = $cell->getCalculatedValue();
                                            // Additional check for calculated value
                                            if (is_object($calculatedValue) || is_array($calculatedValue)) {
                                                $cellValue = ''; // Can't convert object/array to string
                                            } else if (is_string($calculatedValue) || is_numeric($calculatedValue)) {
                                                $cellValue = (string)$calculatedValue;
                                            } else {
                                                $cellValue = ''; // Fallback to empty string
                                            }
                                        } catch (Exception $e) {
                                            // If calculated value fails, try formatted value
                                            try {
                                                $formattedValue = $cell->getFormattedValue();
                                                // Additional check for formatted value
                                                if (is_object($formattedValue) || is_array($formattedValue)) {
                                                    $cellValue = ''; // Can't convert object/array to string
                                                } else {
                                                    $cellValue = (string)$formattedValue;
                                                }
                                            } catch (Exception $e2) {
                                                $cellValue = ''; // Final fallback
                                                error_log("Row $rowIndex, Column $cellIndex: Failed to extract cell value: " . $e2->getMessage());
                                            }
                                        }
                                    } else if ($cellValue === null) {
                                        $cellValue = '';
                                    } else {
                                        // Ensure it's a string
                                        $cellValue = (string)$cellValue;
                                    }
                                    
                                    // Final safety check - if somehow we still have an object/array, convert to empty string
                                    if (is_object($cellValue) || is_array($cellValue)) {
                                        error_log("Row $rowIndex, Column $cellIndex: Final safety conversion - object/array to empty string");
                                        $cellValue = '';
                                    }
                                    
                                    $data[] = $cellValue;
                                }
                                $cellIndex++;
                                if ($cellIndex > 6) {
                                    break; // Stop setelah kolom 6
                                }
                            }
                            
                            // Pastikan array $data memiliki minimal 7 elemen (kolom 0-6)
                            while (count($data) < 7) {
                                $data[] = ''; // Isi dengan string kosong jika kolom tidak ada
                            }

                            // Log first few rows for debugging - show all 7 columns (0-6)
                            if ($rowIndex <= 25) { // Increased logging to see more rows
                                error_log("Row $rowIndex data preview (columns 0-6): " . json_encode($data));
                                
                                // Additional debugging for problematic cells
                                foreach ($data as $colIndex => $cellValue) {
                                    if ($cellValue === '{}' || (is_string($cellValue) && strpos($cellValue, '{}') !== false)) {
                                        error_log("WARNING: Row $rowIndex, Column $colIndex contains problematic value: " . json_encode($cellValue));
                                    }
                                    // Log data types for debugging
                                    if ($rowIndex <= 10) {
                                        $type = gettype($cellValue);
                                        $preview = is_string($cellValue) ? substr($cellValue, 0, 30) : json_encode($cellValue);
                                        error_log("Row $rowIndex, Column $colIndex: Type=$type, Value=" . $preview);
                                    }
                                }
                            }

                            // Helper function to detect if a value is a URL - VERY COMPREHENSIVE
                            $isUrl = function($value) {
                                if ($value === null || $value === '' || is_object($value) || is_array($value)) {
                                    return false;
                                }
                                
                                $value = trim((string)$value);
                                
                                if (empty($value)) {
                                    return false;
                                }
                                
                                // Check for any URL-like pattern
                                return (
                                    // Standard URL formats
                                    strpos($value, 'http://') === 0 || 
                                    strpos($value, 'https://') === 0 || 
                                    strpos($value, 'www.') === 0 ||
                                    // Social media domain patterns
                                    strpos($value, 'facebook.com') !== false ||
                                    strpos($value, 'instagram.com') !== false ||
                                    strpos($value, 'twitter.com') !== false ||
                                    strpos($value, 'x.com') !== false ||
                                    strpos($value, 'tiktok.com') !== false ||
                                    strpos($value, 'youtube.com') !== false ||
                                    strpos($value, 'snackvideo.com') !== false ||
                                    // Generic domain patterns
                                    strpos($value, '.com/') !== false ||
                                    strpos($value, '.org/') !== false ||
                                    strpos($value, '.net/') !== false ||
                                    // URL validation
                                    filter_var($value, FILTER_VALIDATE_URL) !== false
                                );
                            };

                            // Helper function to validate if URL is valid - VERY LENIENT
                            $isValidUrl = function($value) {
                                if ($value === null || $value === '' || is_object($value) || is_array($value)) {
                                    return false;
                                }
                                
                                $value = trim((string)$value);
                                
                                // Very basic validation - just check length and that it contains domain-like structure
                                if (strlen($value) < 8) {
                                    return false;
                                }
                                
                                // Must contain a dot (for domain) and some path or protocol indicator
                                return (strpos($value, '.') !== false);
                            };

                            // IMPROVED: Comprehensive link detection across ALL columns
                            $linkColumn = null;
                            $linkValue = '';
                            
                            // Scan all columns for URLs, prioritizing column 2 (index 1)
                            $columnsToCheck = [1, 0, 2, 3, 4, 5, 6]; // Start with column 2, then 1, then others up to column 6
                            
                            foreach ($columnsToCheck as $col) {
                                if (isset($data[$col]) && $isUrl($data[$col]) && $isValidUrl($data[$col])) {
                                    $linkColumn = $col;
                                    $linkValue = trim((string)$data[$col]);
                                    $linkDetectionCount++;
                                    error_log("Row $rowIndex: Link found in column " . ($col + 1) . ": " . substr($linkValue, 0, 50) . "...");
                                    break; // Use the first valid URL found
                                }
                            }

                            // If no link found, skip this row entirely
                            if ($linkColumn === null || empty($linkValue)) {
                                // Don't count empty rows as skipped
                                $hasAnyData = false;
                                foreach ($data as $cellValue) {
                                    if (!empty(trim((string)$cellValue))) {
                                        $hasAnyData = true;
                                        break;
                                    }
                                }
                                
                                if ($hasAnyData) {
                                    $skippedNoLinkCount++;
                                    error_log("Row $rowIndex: Skipped - no valid link found. Data: " . json_encode($data));
                                }
                                continue;
                            }

                            // IMPROVED: Account name detection - prioritize column 3 (index 2)
                            $groupColumn = 2; // Default to column 3 (index 2)
                            $groupValue = '';
                            
                            // Helper function to check if value is valid for account name
                            $isValidAccountName = function($value) {
                                return !empty(trim((string)$value)) && 
                                       !is_object($value) && 
                                       !is_array($value) && 
                                       trim((string)$value) !== '{}';
                            };
                            
                            // Try to get account name from column 3 first (index 2)
                            if (isset($data[2]) && $isValidAccountName($data[2]) && !$isUrl($data[2])) {
                                $groupValue = trim((string)$data[2]);
                                $groupColumn = 2;
                                error_log("Row $rowIndex: Account name found in column 3: '$groupValue'");
                            }
                            // If column 3 is empty or is a URL, try other columns as fallback
                            else {
                                $accountColumns = [0, 1, 3, 4, 5, 6]; // Try columns 1, 2, 4, 5, 6, 7 as fallback
                                foreach ($accountColumns as $col) {
                                    if (isset($data[$col]) && $isValidAccountName($data[$col]) && 
                                        !$isUrl($data[$col]) && $col !== $linkColumn) {
                                        $groupValue = trim((string)$data[$col]);
                                        $groupColumn = $col;
                                        error_log("Row $rowIndex: Account name found in fallback column " . ($col + 1) . ": '$groupValue'");
                                        break;
                                    }
                                }
                            }

                            // IMPROVED: Carry-forward logic
                            $isCarryForward = false;
                            if (empty($groupValue)) {
                                if ($lastValidAccountName !== null) {
                                    $groupValue = $lastValidAccountName;
                                    $isCarryForward = true;
                                    $carryForwardCount++;
                                    error_log("Row $rowIndex: Using carry-forward account name: '$groupValue'");
                                } else {
                                    error_log("Row $rowIndex: Skipped - no account name found and no previous account to carry forward");
                                    continue;
                                }
                            } else {
                                // Update the last valid account name for future carry-forward
                                $lastValidAccountName = $groupValue;
                                error_log("Row $rowIndex: Updated last valid account name to: '$groupValue'");
                            }

                            // Final validation before adding to results
                            if (!empty($groupValue) && !empty($linkValue)) {
                                $dataRows[] = [
                                    'kolom0' => $data[0] ?? '', // Kolom A (index 0)
                                    'kolom1' => $data[1] ?? '', // Kolom B (index 1)
                                    'kolom2' => $data[2] ?? '', // Kolom C (index 2) - should contain account name
                                    'kolom3' => $groupValue,    // Data untuk grouping (nama akun dari kolom 3)
                                    'kolom4' => $data[4] ?? '', // Kolom E (index 4)
                                    'kolom5' => $linkValue,     // Data link
                                    'kolom6' => $data[6] ?? '', // Kolom G (index 6)
                                    'detected_link_column' => $linkColumn + 1, // Simpan info kolom link yang terdeteksi (1-based)
                                    'detected_group_column' => $groupColumn + 1, // Simpan info kolom group yang terdeteksi (1-based)
                                    'carry_forward' => $isCarryForward, // Flag if this was carry-forward
                                    'row_number' => $rowIndex, // Track original row number for debugging
                                    'all_columns' => $data // Simpan semua data kolom 0-6 untuk referensi
                                ];

                                // Kelompokkan nilai berdasarkan kolom grouping
                                if (!isset($groupedData[$groupValue])) {
                                    $groupedData[$groupValue] = 0;
                                }
                                $groupedData[$groupValue]++;
                                $processedRowCount++;

                                // Log successful processing with more detail including all columns
                                $carryForwardFlag = $isCarryForward ? ' (carry-forward)' : '';
                                error_log("Row $rowIndex: ✓ PROCESSED - Account: '$groupValue', Link: " . substr($linkValue, 0, 40) . "...$carryForwardFlag");
                                error_log("Row $rowIndex: All columns data: " . json_encode($data));
                            } else {
                                error_log("Row $rowIndex: ✗ FINAL VALIDATION FAILED - Group: '$groupValue', Link: '$linkValue'");
                            }
                        }

                        error_log("Sheet '$sheetName' processing summary:");
                        error_log("- Total rows processed: $processedRowCount");
                        error_log("- Links detected: $linkDetectionCount");
                        error_log("- Rows skipped (no link): $skippedNoLinkCount");
                        error_log("- Rows skipped (invalid link): $skippedInvalidLinkCount");
                        error_log("- Carry-forward operations: $carryForwardCount");
                        error_log("- Unique groups found: " . count($groupedData));
                        error_log("- Group distribution: " . json_encode($groupedData));
                        error_log("- Account names primarily sourced from column 3 (index 2)");

                        // DETAILED LINK LOGGING FOR DEBUGGING
                        error_log("=== DETAILED LINK LIST FOR SHEET '$sheetName' ===");
                        foreach ($dataRows as $index => $row) {
                            $carryFlag = $row['carry_forward'] ? ' [CF]' : '';
                            error_log("Link " . ($index + 1) . ": " . $row['kolom5'] . " (Account: " . $row['kolom3'] . ")$carryFlag");
                        }
                        error_log("=== END DETAILED LINK LIST ===");

                        // Simpan data yang dikelompokkan ke array global berdasarkan sheet
                        if (!isset($dataAkun[$sheetName])) {
                            $dataAkun[$sheetName] = [];
                        }
                        foreach ($groupedData as $key => $count) {
                            if (!isset($dataAkun[$sheetName][$key])) {
                                $dataAkun[$sheetName][$key] = 0;
                            }
                            $dataAkun[$sheetName][$key] += $count;
                            error_log("Added to global dataAkun[$sheetName][$key]: $count (total: {$dataAkun[$sheetName][$key]})");
                        }

                        // Simpan data yang diurutkan ke array global berdasarkan sheet
                        if (!isset($dataLink[$sheetName])) {
                            $dataLink[$sheetName] = [];
                        }
                        $previousCount = count($dataLink[$sheetName]);
                        $dataLink[$sheetName] = array_merge($dataLink[$sheetName], $dataRows);
                        $newCount = count($dataLink[$sheetName]);
                        error_log("Added $processedRowCount rows to dataLink[$sheetName], total rows: $newCount (was $previousCount)");

                        // Hitung total data untuk setiap sheet
                        $totalPerSheet[$sheetName] = [
                            'totalAkun' => count($groupedData),
                            'totalLink' => count($dataRows)
                        ];
                        error_log("Sheet '$sheetName' totals - Accounts: {$totalPerSheet[$sheetName]['totalAkun']}, Links: {$totalPerSheet[$sheetName]['totalLink']}");
                    } else {
                        error_log("Sheet not found: '$sheetName' in file '$fileName'");
                        // Coba cari sheet dengan nama yang mirip
                        $availableSheets = $spreadsheet->getSheetNames();
                        error_log("Available sheets for comparison: " . implode(', ', $availableSheets));
                        
                        $similarSheet = null;
                        foreach ($availableSheets as $availableSheet) {
                            if (stripos($availableSheet, $sheetName) !== false || stripos($sheetName, $availableSheet) !== false) {
                                $similarSheet = $availableSheet;
                                break;
                            }
                        }
                        if ($similarSheet) {
                            error_log("Found similar sheet: '$similarSheet' for requested '$sheetName'");
                            // Process similar sheet dengan logika yang sama
                            // (kode yang sama seperti di atas untuk memproses sheet alternatif)
                        } else {
                            error_log("No similar sheet found for '$sheetName' in file '$fileName'");
                        }
                    }
                } catch (Exception $e) {
                    error_log("Error processing sheet '$sheetName' in file '$fileName': " . $e->getMessage());
                    continue; // Skip sheet yang bermasalah
                }
            }
            
            error_log("Completed processing file '$fileName'");
        } else {
            error_log("File upload error for file #" . ($i + 1) . ": " . $files['error'][$i]);
        }
    }

    // Log final summary dengan statistik detail
    error_log("=== Excel processing completed ===");
    error_log("Final results summary:");
    foreach ($sheetsToRead as $sheetName) {
        if (isset($dataAkun[$sheetName])) {
            error_log("Sheet '$sheetName':");
            error_log("  - Total unique accounts: " . count($dataAkun[$sheetName]));
            error_log("  - Total valid links: " . (isset($dataLink[$sheetName]) ? count($dataLink[$sheetName]) : 0));
            error_log("  - Account distribution: " . json_encode($dataAkun[$sheetName]));
        } else {
            error_log("Sheet '$sheetName': No data processed");
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

                // Don't delete image files here - they may be needed by other processes
                // Image cleanup will be handled at the end of the API call
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
            // Generate unique filename with timestamp and microsecond to avoid conflicts
            $timestamp = date('His') . '_' . substr(microtime(true) * 1000, -3); // HHMMSS_microseconds format
            $originalBasename = basename($src);
            $pathInfo = pathinfo($originalBasename);
            $uniqueBasename = 'patroli_kbd_' . $pathInfo['filename'] . '_' . $timestamp . '_' . $processedCount . '.' . $pathInfo['extension'];
            $dst = __DIR__ . '/foto/' . $uniqueBasename;

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

                // Generate unique filename with timestamp and microsecond to avoid collisions
                $timestamp = date('His') . '_' . substr(microtime(true) * 1000, -3); // HHMMSS_microseconds format
                $pathInfo = pathinfo($originalName);
                $uniqueName = 'patroli_kbd_' . $timestamp . '_' . ($i + 1) . '_' . $pathInfo['filename'] . '.' . $pathInfo['extension'];
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
        $templateProcessor->setValue('tanggal', $tanggalFormattedFirst);
        $templateProcessor->setValue('tanggal_judul', $tanggalFormatted);

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

        // Ensure we have at least some screenshots for patrol narratives
        $totalReports = array_reduce($processedReports, function ($carry, $reports) {
            return $carry + count($reports);
        }, 0);

        if ($totalReports > 0 && count($screenshotPaths) === 0) {
            throw new Exception('Tidak ada foto patroli yang tersedia untuk laporan patroli.');
        }

        // Log the counts for debugging
        error_log("KBD Patrol Report - Total patrol reports: " . $totalReports);
        error_log("KBD Patrol Report - Total screenshot paths: " . count($screenshotPaths));

        // List all screenshot paths for debugging
        foreach ($screenshotPaths as $index => $path) {
            error_log("Screenshot $index: " . basename($path));
        }

        // Add patrol screenshots to the Word template (use available photos, skip if not enough)
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
        $templateProcessor->setValue('tanggal_judul', $data['tanggal_judul']);
        $templateProcessor->setValue('tanggal', $data['tanggal']);

        // Clone rows for multiple accounts
        $templateProcessor->cloneRow('nama_akun', count($data['nama_akun']));
        foreach ($data['nama_akun'] as $index => $namaAkun) {
            $rowIndex = $index + 1;

            // Clean all text data using utility function
            $cleanNamaAkun = $namaAkun;
            $cleanKategori = $data['kategori'][$index];
            $cleanNarasi = cleanTextForWord($data['narasi'][$index]);
            $cleanLink = $data['link'][$index];

            $templateProcessor->setValue("nama_akun#{$rowIndex}", $cleanNamaAkun);
            $templateProcessor->setValue("kategori#{$rowIndex}", $cleanKategori);
            $templateProcessor->setValue("narasi#{$rowIndex}", $cleanNarasi);
            $templateProcessor->setValue("link#{$rowIndex}", $cleanLink);
            
            // Set profiling - gunakan data terstruktur dari FORM atau dari REPORT multi-line
            if (isset($data['profilingData']) && !empty($data['profilingData']['nama'])) {
                // Data dari FORM (array of arrays - multiple accounts)
                $profilingTerstruktur = [];
                $pd = $data['profilingData'];
                
                if (isset($pd['nama'][$index])) $profilingTerstruktur[] = "Nama: " . toTitleCase($pd['nama'][$index]);
                if (isset($pd['jenis_kelamin'][$index]) && !empty($pd['jenis_kelamin'][$index])) 
                    $profilingTerstruktur[] = "Jenis Kelamin: " . toTitleCase($pd['jenis_kelamin'][$index]);
                if (isset($pd['gol_darah'][$index]) && !empty($pd['gol_darah'][$index])) 
                    $profilingTerstruktur[] = "Golongan Darah: " . toTitleCase($pd['gol_darah'][$index]);
                if (isset($pd['status_nikah'][$index]) && !empty($pd['status_nikah'][$index])) 
                    $profilingTerstruktur[] = "Status Nikah: " . toTitleCase($pd['status_nikah'][$index]);
                if (isset($pd['agama'][$index]) && !empty($pd['agama'][$index])) 
                    $profilingTerstruktur[] = "Agama: " . toTitleCase($pd['agama'][$index]);
                if (isset($pd['tempat_lahir'][$index]) && !empty($pd['tempat_lahir'][$index])) 
                    $profilingTerstruktur[] = "Lahir: " . toTitleCase($pd['tempat_lahir'][$index]);
                if (isset($pd['umur'][$index])) 
                    $profilingTerstruktur[] = "Umur: " . $pd['umur'][$index]; // Umur tidak perlu Title Case
                if (isset($pd['tgl_lahir'][$index]) && !empty($pd['tgl_lahir'][$index])) 
                    $profilingTerstruktur[] = "Tanggal Lahir: " . $pd['tgl_lahir'][$index]; // Tanggal tidak perlu Title Case
                if (isset($pd['pekerjaan'][$index])) 
                    $profilingTerstruktur[] = "Pekerjaan: " . toTitleCase($pd['pekerjaan'][$index]);
                if (isset($pd['provinsi'][$index])) 
                    $profilingTerstruktur[] = "Provinsi: " . toTitleCase($pd['provinsi'][$index]);
                if (isset($pd['kabupaten'][$index])) 
                    $profilingTerstruktur[] = "Kabupaten: " . toTitleCase($pd['kabupaten'][$index]);
                if (isset($pd['kecamatan'][$index]) && !empty($pd['kecamatan'][$index])) 
                    $profilingTerstruktur[] = "Kecamatan: " . toTitleCase($pd['kecamatan'][$index]);
                if (isset($pd['kelurahan'][$index]) && !empty($pd['kelurahan'][$index])) 
                    $profilingTerstruktur[] = "Kelurahan: " . toTitleCase($pd['kelurahan'][$index]);
                if (isset($pd['kode_pos'][$index]) && !empty($pd['kode_pos'][$index])) 
                    $profilingTerstruktur[] = "Kode Pos: " . $pd['kode_pos'][$index]; // Kode Pos tidak perlu Title Case
                if (isset($pd['rt_rw'][$index]) && !empty($pd['rt_rw'][$index])) 
                    $profilingTerstruktur[] = "RT/RW: " . $pd['rt_rw'][$index]; // RT/RW tidak perlu Title Case
                if (isset($pd['alamat'][$index])) 
                    $profilingTerstruktur[] = "Alamat Lengkap: " . toTitleCase($pd['alamat'][$index]);
                
                $cleanProfiling = implode("\n", $profilingTerstruktur);
                error_log("Using structured profiling from FORM for row $rowIndex");
            } elseif (isset($data['profilingDataPerReport'][$index]) && !empty($data['profilingDataPerReport'][$index])) {
                // Data dari REPORT multi-line (per-report structured data)
                $profilingTerstruktur = [];
                $pd = $data['profilingDataPerReport'][$index];
                
                if (isset($pd['nama'])) $profilingTerstruktur[] = "Nama: " . toTitleCase($pd['nama']);
                if (isset($pd['jenis_kelamin']) && !empty($pd['jenis_kelamin'])) 
                    $profilingTerstruktur[] = "Jenis Kelamin: " . toTitleCase($pd['jenis_kelamin']);
                if (isset($pd['gol_darah']) && !empty($pd['gol_darah'])) 
                    $profilingTerstruktur[] = "Golongan Darah: " . toTitleCase($pd['gol_darah']);
                if (isset($pd['status_nikah']) && !empty($pd['status_nikah'])) 
                    $profilingTerstruktur[] = "Status Nikah: " . toTitleCase($pd['status_nikah']);
                if (isset($pd['agama']) && !empty($pd['agama'])) 
                    $profilingTerstruktur[] = "Agama: " . toTitleCase($pd['agama']);
                if (isset($pd['tempat_lahir']) && !empty($pd['tempat_lahir'])) 
                    $profilingTerstruktur[] = "Lahir: " . toTitleCase($pd['tempat_lahir']);
                if (isset($pd['umur'])) 
                    $profilingTerstruktur[] = "Umur: " . $pd['umur']; // Umur tidak perlu Title Case
                if (isset($pd['tgl_lahir']) && !empty($pd['tgl_lahir'])) 
                    $profilingTerstruktur[] = "Tanggal Lahir: " . $pd['tgl_lahir']; // Tanggal tidak perlu Title Case
                if (isset($pd['pekerjaan'])) 
                    $profilingTerstruktur[] = "Pekerjaan: " . toTitleCase($pd['pekerjaan']);
                if (isset($pd['provinsi'])) 
                    $profilingTerstruktur[] = "Provinsi: " . toTitleCase($pd['provinsi']);
                if (isset($pd['kabupaten'])) 
                    $profilingTerstruktur[] = "Kabupaten: " . toTitleCase($pd['kabupaten']);
                if (isset($pd['kecamatan']) && !empty($pd['kecamatan'])) 
                    $profilingTerstruktur[] = "Kecamatan: " . toTitleCase($pd['kecamatan']);
                if (isset($pd['kelurahan']) && !empty($pd['kelurahan'])) 
                    $profilingTerstruktur[] = "Kelurahan: " . toTitleCase($pd['kelurahan']);
                if (isset($pd['kode_pos']) && !empty($pd['kode_pos'])) 
                    $profilingTerstruktur[] = "Kode Pos: " . $pd['kode_pos']; // Kode Pos tidak perlu Title Case
                if (isset($pd['rt_rw']) && !empty($pd['rt_rw'])) 
                    $profilingTerstruktur[] = "RT/RW: " . $pd['rt_rw']; // RT/RW tidak perlu Title Case
                if (isset($pd['alamat'])) 
                    $profilingTerstruktur[] = "Alamat Lengkap: " . toTitleCase($pd['alamat']);
                
                $cleanProfiling = implode("\n", $profilingTerstruktur);
                error_log("Using structured profiling from MULTI-LINE REPORT for row $rowIndex");
            } else {
                // Fallback to free-text profiling
                $cleanProfiling = isset($data['profiling'][$index]) ? cleanTextForWord($data['profiling'][$index]) : '';
                error_log("Using free-text profiling for row $rowIndex");
            }
            $templateProcessor->setValue("profiling#{$rowIndex}", $cleanProfiling);

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

            if (isset($data['foto_profiling'][$index]) && file_exists($data['foto_profiling'][$index])) {
                $templateProcessor->setImageValue("foto_profiling#{$rowIndex}", [
                    'path' => $data['foto_profiling'][$index],
                    'width' => 300,
                    'height' => 200,
                    'ratio' => true
                ]);
            }
        }

        // Save the generated Word file
        $templateProcessor->saveAs($outputPath);
        
        // Release file handles immediately after save
        unset($templateProcessor);
        gc_collect_cycles();
        clearstatcache();
        
        echo json_encode(['progress' => 'File Word Patroli Landy berhasil dibuat...']);
        @ob_flush();
        @flush();

        return true;
    } catch (Exception $e) {
        throw new Exception('Gagal membuat file Word Patroli Landy: ' . $e->getMessage());
    }
}

function createPdfFileLandy($templatePath, $outputPath, $fotoPatroli, $fotoUpaya, $judulLandy = 'PATROLI SIBER DAN UPAYA KONTRA OPINI')
{
    // Debug info
    error_log("Creating PDF Landy - Template: $templatePath");
    error_log("Creating PDF Landy - Output: $outputPath");
    error_log("Creating PDF Landy - Judul: $judulLandy");
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

    // Replace judul placeholder
    $htmlContent = str_replace('{{judul_laporan}}', $judulLandy, $htmlContent);
    error_log("Replaced judul placeholder with: $judulLandy");

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

    // Release memory and file handles
    unset($dompdf);
    gc_collect_cycles();
    clearstatcache();
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
    $phpWord->setValue('tahun', $teks_laporan['tahun'] ?? '');
    $phpWord->setValue('isi_patroli', $teks_laporan['isi_patroli'] ?? '');
    $phpWord->setValue('tanggal', $teks_laporan['tanggal'] ?? '');
    $phpWord->setValue('tanggal_lampiran', $teks_laporan['tanggal_lampiran'] ?? '');
    $phpWord->setValue('bulan_romawi', $teks_laporan['bulan_romawi'] ?? '');

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
            $cleanNamaAkun = $nama_akun_patroli[$i] ?? '';
            $cleanKategori = $kategori_patroli[$i] ?? '';
            $cleanNarasi = cleanTextForWord($narasi_patroli[$i] ?? '');
            $cleanLink = $link_patroli[$i] ?? '';

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
            $cleanNamaAkunPatroli = $nama_akun_patroli[$i] ?? '';
            $cleanNamaAkunUpaya = $nama_akun_upaya[$i] ?? '';
            $cleanKategoriUpaya = $kategori_patroli[$i] ?? '';
            $cleanNarasiUpaya = cleanTextForWord($narasi_upaya[$i] ?? '');
            $cleanLinkUpaya = $link_upaya[$i] ?? '';

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

    return true;
}

/**
 * Parse Patroli Landy dengan format multi-line profiling
 */
function parseLandyMultiLineProfiling($rawReport)
{
    $platforms = ['FACEBOOK', 'INSTAGRAM', 'X', 'TIKTOK', 'SNACKVIDEO', 'YOUTUBE'];
    $groupedReports = array_fill_keys($platforms, []);
    $processedReports = array_fill_keys($platforms, []);
    $profilingDataArray = []; // Store structured profiling data
    
    // Split reports by detecting new "nama akun:" pattern (start of new report)
    // This is more reliable than double newline which can occur within profiling data
    $reportBlocks = preg_split('/(?=^nama[\s_-]*akun\s*:)/im', $rawReport, -1, PREG_SPLIT_NO_EMPTY);
    
    error_log("parseLandyMultiLineProfiling - Total report blocks found: " . count($reportBlocks));
    
    foreach ($reportBlocks as $blockIndex => $block) {
        if (empty(trim($block))) continue;
        
        error_log("Processing block #" . ($blockIndex + 1) . " - Length: " . strlen($block));
        
        $data = [
            'nama_akun' => '',
            'link' => '',
            'kategori' => '',
            'narasi' => '',
            'profiling' => [],
            'tanggal_postingan' => '',
            'wilayah' => '',
            'korelasi' => '',
            'afiliasi' => ''
        ];
        
        $lines = explode("\n", $block);
        $currentField = '';
        $profilingLines = [];
        $inProfiling = false;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Priority 1: Check main field labels FIRST (before checking profiling data)
            if (preg_match('/^nama[\s_-]*akun\s*:\s*(.+)/i', $line, $m)) {
                $inProfiling = false;
                $currentField = '';
                $data['nama_akun'] = trim($m[1]);
                error_log("Parsed nama_akun: " . $data['nama_akun']);
            } elseif (preg_match('/^link\s*:\s*(.+)/i', $line, $m)) {
                $inProfiling = false;
                $currentField = '';
                $data['link'] = trim($m[1]);
                error_log("Parsed link: " . $data['link']);
            } elseif (preg_match('/^kategori\s*:\s*(.+)/i', $line, $m)) {
                $inProfiling = false;
                $currentField = '';
                $data['kategori'] = trim($m[1]);
                error_log("Parsed kategori: " . $data['kategori']);
            } elseif (preg_match('/^narasi\s*:\s*(.+)/i', $line, $m)) {
                $inProfiling = false;
                $data['narasi'] = trim($m[1]);
                $currentField = 'narasi';
                error_log("Parsed narasi: " . substr($data['narasi'], 0, 50));
            } elseif (preg_match('/^profiling\s*:\s*$/i', $line)) {
                $inProfiling = true;
                $currentField = 'profiling';
                error_log("✅ Started profiling mode");
            } elseif (preg_match('/^tanggal[\s_-]*postingan\s*:\s*(.+)/i', $line, $m)) {
                $inProfiling = false;
                $currentField = '';
                $data['tanggal_postingan'] = trim($m[1]);
                error_log("⛔ Stopped profiling - Parsed tanggal_postingan: " . $data['tanggal_postingan']);
            } elseif (preg_match('/^wilayah\s*:\s*(.+)/i', $line, $m)) {
                $inProfiling = false;
                $currentField = '';
                $data['wilayah'] = trim($m[1]);
                error_log("Parsed wilayah: " . $data['wilayah']);
            } elseif (preg_match('/^korelasi\s*:\s*(.+)/i', $line, $m)) {
                $inProfiling = false;
                $currentField = '';
                $data['korelasi'] = trim($m[1]);
                error_log("Parsed korelasi: " . substr($data['korelasi'], 0, 50));
            } elseif (preg_match('/^afiliasi\s*:\s*(.+)/i', $line, $m)) {
                $inProfiling = false;
                $currentField = '';
                $data['afiliasi'] = trim($m[1]);
                error_log("Parsed afiliasi: " . $data['afiliasi']);
            } 
            // Priority 2: Check profiling data ONLY if in profiling mode
            // Support both with value and empty value (e.g., "Nik:" or "Nik: 123456")
            elseif ($inProfiling && preg_match('/^([^:]+)\s*:\s*(.*)$/i', $line, $m)) {
                // Parse profiling structured data (case-insensitive)
                $field = trim($m[1]);
                $value = trim($m[2]);
                $fieldLower = strtolower($field);
                
                // Handle empty values - use '-' if empty
                $displayValue = empty($value) ? '-' : $value;
                $profilingLines[] = "$field: $displayValue";
                error_log("   📋 Profiling field: $field = " . ($value ?: '(empty)'));
                
                // Map to profilingData structure (case-insensitive matching)
                $fieldMap = [
                    'nik' => 'nik',
                    'kk' => 'kk',
                    'nama' => 'nama',
                    'jenis kelamin' => 'jenis_kelamin',
                    'golongan darah' => 'gol_darah',
                    'status nikah' => 'status_nikah',
                    'agama' => 'agama',
                    'lahir' => 'tempat_lahir',
                    'umur' => 'umur',
                    'tanggal lahir' => 'tgl_lahir',
                    'pekerjaan' => 'pekerjaan',
                    'provinsi' => 'provinsi',
                    'kabupaten' => 'kabupaten',
                    'kecamatan' => 'kecamatan',
                    'kelurahan' => 'kelurahan',
                    'kode pos' => 'kode_pos',
                    'rt/rw' => 'rt_rw',
                    'alamat lengkap' => 'alamat'
                ];
                
                if (isset($fieldMap[$fieldLower])) {
                    // Store value, or '-' if empty
                    $data['profiling'][$fieldMap[$fieldLower]] = empty($value) ? '-' : $value;
                } else {
                    // Store unknown fields too (for display purposes)
                    $profilingLines[] = "$field: $displayValue";
                }
            } 
            // Priority 3: Continue multi-line narasi
            elseif ($currentField === 'narasi' && !$inProfiling) {
                $data['narasi'] .= ' ' . $line;
            }
        }
        
        // Validate link
        if (!filter_var($data['link'], FILTER_VALIDATE_URL)) {
            error_log("Invalid link in multi-line profiling: {$data['link']}");
            continue;
        }
        
        // Join profiling lines for display
        $data['profiling_text'] = implode("\n", $profilingLines);
        
        // Determine platform
        $platform = 'LAINNYA';
        if (strpos($data['link'], 'facebook.com') !== false) {
            $platform = 'FACEBOOK';
        } elseif (strpos($data['link'], 'instagram.com') !== false) {
            $platform = 'INSTAGRAM';
        } elseif (strpos($data['link'], 'x.com') !== false || strpos($data['link'], 'twitter.com') !== false) {
            $platform = 'X';
        } elseif (strpos($data['link'], 'tiktok.com') !== false) {
            $platform = 'TIKTOK';
        } elseif (strpos($data['link'], 'snackvideo.com') !== false) {
            $platform = 'SNACKVIDEO';
        } elseif (strpos($data['link'], 'youtube.com') !== false) {
            $platform = 'YOUTUBE';
        }
        
        // Add to grouped reports
        $groupedReports[$platform][] = "Termonitor Akun {$data['nama_akun']} ({$data['link']}) membagikan postingan {$data['kategori']} dengan {$data['narasi']}";
        
        // Add to processed reports
        $processedReports[$platform][] = [
            'name' => $data['nama_akun'],
            'link' => htmlspecialchars($data['link'], ENT_QUOTES, 'UTF-8'),
            'category' => cleanTextForWord($data['kategori']),
            'narrative' => cleanTextForWord($data['narasi']),
            'profiling' => $data['profiling_text'],
            'profilingData' => $data['profiling'], // Structured data
            'tanggal_postingan' => $data['tanggal_postingan'],
            'wilayah' => $data['wilayah'],
            'korelasi' => $data['korelasi'],
            'afiliasi' => $data['afiliasi']
        ];
        
        error_log("Parsed multi-line profiling for: {$data['nama_akun']} on $platform");
    }
    
    // Count total reports parsed
    $totalParsed = 0;
    foreach ($processedReports as $platform => $reports) {
        $totalParsed += count($reports);
    }
    error_log("parseLandyMultiLineProfiling - Total reports parsed: $totalParsed");
    
    return [
        'groupedReports' => $groupedReports,
        'processedReports' => $processedReports,
        'hasStructuredProfiling' => true
    ];
}

function prosesPatrolReport($rawReport, $input = 'patroli', $range = 4, $isPatroliLandy = false)
{
    $platforms = ['FACEBOOK', 'INSTAGRAM', 'X', 'TIKTOK', 'SNACKVIDEO', 'YOUTUBE'];
    $groupedReports = array_fill_keys($platforms, []);
    $processedReports = array_fill_keys($platforms, []);
    $currentReport = [];

    // Normalize line breaks for cross-browser compatibility (Firefox uses \r\n, Chrome uses \n)
    $rawReport = str_replace(["\r\n", "\r"], "\n", $rawReport);
    
    error_log("prosesPatrolReport - isPatroliLandy: " . ($isPatroliLandy ? 'YES' : 'NO'));
    error_log("prosesPatrolReport - Raw report preview (first 200 chars): " . substr($rawReport, 0, 200));
    
    // DETECT MULTI-LINE PROFILING FORMAT for Patroli Landy
    // IMPORTANT: Check BEFORE label stripping!
    // Support both old format (profiling:\nNama:) and new format (profiling:\nNik: or profiling:\nKK:)
    $hasMultiLineProfiling = false;
    if ($isPatroliLandy && (
        preg_match('/profiling:\s*\n\s*(Nama|Nik|KK|Jenis\s+kelamin|Lahir|Tanggal\s+Lahir|Pekerjaan|Provinsi|Kabupaten|Kecamatan|Kelurahan|Alamat\s+Lengkap)\s*:/is', $rawReport) ||
        preg_match('/profiling:\s*\n\s*[A-Za-z\s]+:\s*/is', $rawReport)
    )) {
        $hasMultiLineProfiling = true;
        error_log("✅ prosesPatrolReport - DETECTED MULTI-LINE PROFILING FORMAT - Calling parseLandyMultiLineProfiling()");
        
        // Use special parsing for multi-line profiling (DO NOT strip labels first!)
        return parseLandyMultiLineProfiling($rawReport);
    } else if ($isPatroliLandy) {
        error_log("❌ prosesPatrolReport - Patroli Landy but NO multi-line format detected");
        
        // Debug: Check if "profiling:" exists but format is different
        if (stripos($rawReport, 'profiling:') !== false) {
            error_log("'profiling:' found but not multi-line format");
            $pos = stripos($rawReport, 'profiling:');
            $snippet = substr($rawReport, $pos, 100);
            error_log("Snippet around 'profiling:': " . $snippet);
        } else {
            error_log("'profiling:' not found in report");
        }
    }
    
    // Strip optional field labels ONLY for Patroli Landy - support multiple variations
    if ($isPatroliLandy) {
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
        
        error_log("prosesPatrolReport - Patroli Landy: After strip labels (first 300 chars): " . substr($rawReport, 0, 300));
    } else {
        error_log("prosesPatrolReport - Non-Landy: No label stripping applied");
    }

    $lines = explode("\n", $rawReport);

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip empty lines
        if (empty($line)) {
            if (count($currentReport) === $range) {
                // Handle different field counts
                if ($range === 9 && $input === 'patroli') {
                    // Patroli Landy with 9 fields
                    [$name, $link, $category, $narrative, $profiling, $tanggal_postingan, $wilayah, $korelasi, $afiliasi] = $currentReport;
                    $category = cleanTextForWord($category);
                } elseif ($input === 'patroli') {
                    // Regular patroli with 4 fields
                    [$name, $link, $category, $narrative] = $currentReport;
                    $category = cleanTextForWord($category);
                    $profiling = $tanggal_postingan = $wilayah = $korelasi = $afiliasi = '';
                } else {
                    [$name, $link, $narrative] = $currentReport;
                    $profiling = $tanggal_postingan = $wilayah = $korelasi = $afiliasi = '';
                }

                // Validate and sanitize the link
                if (!filter_var($link, FILTER_VALIDATE_URL)) {
                    error_log("Invalid link detected: {$link}");
                    $currentReport = [];
                    continue;
                }


                // Validate and sanitize the link
                $link = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

                // Clean the data using the utility function
                $name = trim($name);
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
                        'narrative' => $narrative,
                        'profiling' => $profiling ?? '',
                        'tanggal_postingan' => $tanggal_postingan ?? '',
                        'wilayah' => $wilayah ?? '',
                        'korelasi' => $korelasi ?? '',
                        'afiliasi' => $afiliasi ?? ''
                    ];
                } else {
                    $processedReports[$platform][] = [
                        'name' => $name,
                        'link' => $link,
                        'narrative' => $narrative
                    ];
                }

                // Debug log
                error_log("Processed report for platform {$platform}: {$name}");

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
        // Handle different field counts
        if ($range === 9 && $input === 'patroli') {
            // Patroli Landy with 9 fields
            [$name, $link, $category, $narrative, $profiling, $tanggal_postingan, $wilayah, $korelasi, $afiliasi] = $currentReport;
        } elseif ($input === 'patroli') {
            // Regular patroli with 4 fields
            [$name, $link, $category, $narrative] = $currentReport;
            $profiling = $tanggal_postingan = $wilayah = $korelasi = $afiliasi = '';
        } else {
            [$name, $link, $narrative] = $currentReport;
            $profiling = $tanggal_postingan = $wilayah = $korelasi = $afiliasi = '';
        }
        
        // Validate and sanitize the link
        if (!filter_var($link, FILTER_VALIDATE_URL)) {
            error_log("Invalid link detected: {$link}");
        } else {
            $link = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
            if ($input === 'patroli') {
                $category = cleanTextForWord($category);
            }
            // Clean the data using the utility function
            $name = trim($name);
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
                    'narrative' => $narrative,
                    'profiling' => $profiling ?? '',
                    'tanggal_postingan' => $tanggal_postingan ?? '',
                    'wilayah' => $wilayah ?? '',
                    'korelasi' => $korelasi ?? '',
                    'afiliasi' => $afiliasi ?? ''
                ];
            } else {
                $processedReports[$platform][] = [
                    'name' => $name,
                    'link' => $link,
                    'narrative' => $narrative
                ];
            }
            
            // Debug log
            error_log("Processed last report for platform {$platform}: {$name}");
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

    // Clear file stat cache before scanning
    clearstatcache();
    
    $imageFiles = glob($directory . '/*.{jpg,jpeg,png,gif,webp,bmp}', GLOB_BRACE);
    $count = count($imageFiles);

    if ($count > 0) {
        error_log("Cleaning up $count images from $directory");
        $deleted = 0;
        $failed = 0;
        
        foreach ($imageFiles as $file) {
            if (is_file($file)) {
                // Try to delete with retry for locked files
                $attempts = 0;
                $maxAttempts = 3;
                $deleted_success = false;
                
                while ($attempts < $maxAttempts && !$deleted_success) {
                    clearstatcache(true, $file);
                    if (@unlink($file)) {
                        error_log("Deleted: " . basename($file));
                        $deleted++;
                        $deleted_success = true;
                    } else {
                        $attempts++;
                        if ($attempts < $maxAttempts) {
                            usleep(100000); // Wait 100ms before retry
                        } else {
                            error_log("Failed to delete after $maxAttempts attempts: " . basename($file));
                            $failed++;
                        }
                    }
                }
            }
        }
        error_log("Image cleanup completed for $directory: $deleted deleted, $failed failed out of $count total");
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
        $processedCount = 0;
        foreach ($selectedFiles as $src) {
            $processedCount++;
            // Generate unique filename with timestamp to avoid conflicts
            $timestamp = date('His'); // HHMMSS format
            $originalBasename = basename($src);
            $pathInfo = pathinfo($originalBasename);
            $uniqueBasename = $pathInfo['filename'] . '_' . $timestamp . '_' . $processedCount . '.' . $pathInfo['extension'];
            $dst = $hasilFolder . '/' . $uniqueBasename;
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
                // Generate unique filename with timestamp to avoid conflicts
                $timestamp = date('His'); // HHMMSS format
                $originalName = $files['imageFiles']['name'][$i];
                $pathInfo = pathinfo($originalName);
                $uniqueName = 'cipop_' . $timestamp . '_' . ($i + 1) . '_' . $pathInfo['filename'] . '.' . $pathInfo['extension'];
                $destinationPath = __DIR__ . '/template_pdf/' . $uniqueName;
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

    try {
        createWordFile($templatePathWordGeneral, $outputPathWordGeneral, $tanggalFormatted, $jumlahDataPerSheet, $dataLink);
        error_log("Successfully created Word file: $outputPathWordGeneral");
    } catch (Exception $e) {
        error_log("Error: Failed to create Word file: $outputPathWordGeneral - " . $e->getMessage());
        throw new Exception("Gagal membuat file Word laporan KBD umum: " . $e->getMessage());
    }

    if (!file_exists($outputPathWordGeneral)) {
        error_log("Error: Word file was not created: $outputPathWordGeneral");
        $outputPathWordGeneral = "";
    }
    // Step 5: Generate PDF
    $currentProgress += $progressStep;
    echo json_encode(['progress' => '6/7: Membuat file PDF laporan KBD...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();
    // Step 5: Generate PDF
    $currentProgress += $progressStep;
    echo json_encode(['progress' => '6/7: Membuat file PDF laporan KBD...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();
    $templatePathHtml = __DIR__ . '/template_pdf/template_kbd.html';
    $outputPathPdf = $hasilFolder . "/{$fileName}.pdf";

    try {
        createPdfFile($templatePathHtml, $outputPathPdf, $tanggalFormatted, $hariFormatted, $tanggalFormattedFirst, $jumlahLinkperSheet, $imagePaths);
        error_log("Successfully created PDF file: $outputPathPdf");
    } catch (Exception $e) {
        error_log("Error: Failed to create PDF file: $outputPathPdf - " . $e->getMessage());
        throw new Exception("Gagal membuat file PDF laporan KBD: " . $e->getMessage());
    }

    if (!file_exists($outputPathPdf)) {
        error_log("Error: PDF file was not created: $outputPathPdf");
        $outputPathPdf = "";
    }
    // Step 6: Generate Word Patrol Report
    $currentProgress += $progressStep;
    echo json_encode(['progress' => '7/7: Membuat file Word hasil patroli KBD...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    $templatePathWordPatroli = __DIR__ . '/template_word/template_Patroli_kbd.docx';
    $outputPathWordPatroli = $hasilFolder . "/HASIL PATROLI SIBER TERKAIT OPINI NEGATIF TERHADAP PRESIDEN PRABOWO SUBIANTO DI WIL. MERPATI – 14 TANGGAL {$tanggalFormatted}.docx";

    try {
        createWordFilePatroli($templatePathWordPatroli, $outputPathWordPatroli, $tanggalFormatted, $processedReports, $screenshotPaths);
        error_log("Successfully created Word Patrol file: $outputPathWordPatroli");
    } catch (Exception $e) {
        error_log("Error: Failed to create Word Patrol file: $outputPathWordPatroli - " . $e->getMessage());
        throw new Exception("Gagal membuat file Word hasil patroli KBD: " . $e->getMessage());
    }

    if (!file_exists($outputPathWordPatroli)) {
        error_log("Error: Word Patrol file was not created: $outputPathWordPatroli");
        $outputPathWordPatroli = "";
    }

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
    foreach ($processedReports as $platform => $reports) {
        if (!empty($reports)) {
            $platformFormatted = strtoupper($platform);
            $isiPatroliLandy .= "*{$platformFormatted}*\n\n";
            
            $platformNo = 1;
            foreach ($reports as $report) {
                // Sanitize data for Patroli Landy narrative
                $cleanName = $report['name'];
                $cleanLink = $report['link'];
                $cleanCategory = $report['category'];
                $cleanNarrative = $report['narrative'];
                $tanggal_postingan = $report['tanggal_postingan'] ?? '';
                $wilayah = $report['wilayah'] ?? '';
                $korelasi = $report['korelasi'] ?? '';
                $afiliasi = $report['afiliasi'] ?? '';
                
                // Format profiling - jika ada structured data, format dengan Title Case
                $profiling = '';
                if (isset($report['profilingData']) && !empty($report['profilingData'])) {
                    $pd = $report['profilingData'];
                    $profilingParts = [];
                    if (isset($pd['nama'])) $profilingParts[] = toTitleCase($pd['nama']);
                    if (isset($pd['jenis_kelamin'])) $profilingParts[] = toTitleCase($pd['jenis_kelamin']);
                    if (isset($pd['umur'])) $profilingParts[] = $pd['umur'] . " Tahun";
                    if (isset($pd['pekerjaan'])) $profilingParts[] = toTitleCase($pd['pekerjaan']);
                    if (isset($pd['provinsi']) && isset($pd['kabupaten'])) {
                        $profilingParts[] = toTitleCase($pd['kabupaten']) . ", " . toTitleCase($pd['provinsi']);
                    } elseif (isset($pd['provinsi'])) {
                        $profilingParts[] = toTitleCase($pd['provinsi']);
                    }
                    $profiling = implode(', ', $profilingParts);
                } else {
                    $profiling = $report['profiling'] ?? '';
                }
                
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

    // Check if this is "Sore" based on jumlah laporan
    // Jika 1 laporan = MBG, jika 2 atau lebih = Sore
    $isSore = $totalPatroliCount >= 2;
    
    if ($isSore) {
        // Format untuk Patroli Sore
        $narasiPatroliLandy = <<<EOD
*Kepada Yth.: Kasuari-6*

*Dari: Merpati-14*

*Tembusan : Yth.*
*1. Kasuari-9*
*2. Kasuari-63*
*3. Kasuari-75*

*Perihal : Laporan {$judulLandy} di Wilayah Prov. Jambi Update {$tanggalFormattedFirst}*

*A. EXECUTIVE SUMMARY*

{$executiveSummary}

*B. KEGIATAN PATROLI SIBER*

{$isiPatroliLandy}
*C.UPAYA*

1.Melakukan pemantauan terhadap akun yang menyebarkan berita atau isu yang menyudutkan pemerintahan.

2.Melakukan pemetaan terhadap postingan ataupun berita tendensius dan hoax serta penyebarnya yang tersebar di dunia maya.

3.Melakukan kontra dan report terhadap isu sensitif yang efeknya diperkirakan cukup besar dan nyata baik dengan tulisan maupun dengan meme yang bersifat menarik.

*DUMP. TTD: Merpati - 14*
EOD;
    } else {
        // Format untuk Patroli MBG (yang pertama)
        $narasiPatroliLandy = <<<EOD
*Kepada Yth:*
*1. Rajawali*
*2. Elang*

*Dari: Merpati-14*

*Tembusan : Yth.*
*1. Kasuari-6*
*2. Kasuari-9*

*Perihal : Laporan {$judulLandy} di Wilayah Prov. Jambi Update {$tanggalFormattedFirst}*

*A. EXECUTIVE SUMMARY*

{$executiveSummary}

*B. KEGIATAN PATROLI SIBER*

{$isiPatroliLandy}
*C.UPAYA*

1.Melakukan pemantauan terhadap akun yang menyebarkan berita atau isu yang menyudutkan pemerintahan.

2.Melakukan pemetaan terhadap postingan ataupun berita tendensius dan hoax serta penyebarnya yang tersebar di dunia maya.

3.Melakukan kontra dan report terhadap isu sensitif yang efeknya diperkirakan cukup besar dan nyata baik dengan tulisan maupun dengan meme yang bersifat menarik.

*DUMP. TTD: Merpati - 14*
EOD;
    }

    // Step 2: Process RAS/upaya files
    $currentProgress += $progressStep;
    echo json_encode(['progress' => 'Memproses upaya RAS Patroli Landy...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    // Upaya screenshot logic - handle multiple possible field names
    $foto_upaya = [];
    if (isset($files['rasFiles']) && !empty($files['rasFiles']['tmp_name'])) {
        // Process RAS files with unique naming
        $timestamp = date('His') . '_' . substr(microtime(true) * 1000, -3); // HHMMSS_microseconds format
        foreach ($files['rasFiles']['tmp_name'] as $index => $tmpPath) {
            if (!empty($tmpPath) && file_exists($tmpPath)) {
                $originalName = $files['rasFiles']['name'][$index] ?? 'ras_file.jpg';
                $pathInfo = pathinfo($originalName);
                $uniqueName = 'ras_landy_' . $timestamp . '_' . ($index + 1) . '_' . $pathInfo['filename'] . '.' . ($pathInfo['extension'] ?? 'jpg');
                $dst = __DIR__ . '/template_word/' . $uniqueName;

                // Ensure template_word directory exists
                if (!is_dir(__DIR__ . '/template_word')) {
                    mkdir(__DIR__ . '/template_word', 0755, true);
                }

                if (copy($tmpPath, $dst)) {
                    $foto_upaya[] = $dst;
                } else {
                    error_log("Failed to copy RAS file from $tmpPath to $dst");
                }
            }
        }
    } elseif (isset($files['upayaImages']) && !empty($files['upayaImages']['tmp_name'])) {
        // Process upaya images with unique naming
        $timestamp = date('His') . '_' . substr(microtime(true) * 1000, -3); // HHMMSS_microseconds format
        foreach ($files['upayaImages']['tmp_name'] as $index => $tmpPath) {
            if (!empty($tmpPath) && file_exists($tmpPath)) {
                $originalName = $files['upayaImages']['name'][$index] ?? 'upaya_file.jpg';
                $pathInfo = pathinfo($originalName);
                $uniqueName = 'upaya_landy_' . $timestamp . '_' . ($index + 1) . '_' . $pathInfo['filename'] . '.' . ($pathInfo['extension'] ?? 'jpg');
                $dst = __DIR__ . '/template_word/' . $uniqueName;

                // Ensure template_word directory exists
                if (!is_dir(__DIR__ . '/template_word')) {
                    mkdir(__DIR__ . '/template_word', 0755, true);
                }

                if (copy($tmpPath, $dst)) {
                    $foto_upaya[] = $dst;
                } else {
                    error_log("Failed to copy upaya file from $tmpPath to $dst");
                }
            }
        }
    }

    // Process Profiling files
    $foto_profiling = [];
    if (isset($files['profilingFiles']) && !empty($files['profilingFiles']['tmp_name'])) {
        // Process profiling files with unique naming
        $timestamp = date('His') . '_' . substr(microtime(true) * 1000, -3); // HHMMSS_microseconds format
        foreach ($files['profilingFiles']['tmp_name'] as $index => $tmpPath) {
            if (!empty($tmpPath) && file_exists($tmpPath)) {
                $originalName = $files['profilingFiles']['name'][$index] ?? 'profiling_file.jpg';
                $pathInfo = pathinfo($originalName);
                $uniqueName = 'profiling_landy_' . $timestamp . '_' . ($index + 1) . '_' . $pathInfo['filename'] . '.' . ($pathInfo['extension'] ?? 'jpg');
                $dst = __DIR__ . '/template_word/' . $uniqueName;

                // Ensure template_word directory exists
                if (!is_dir(__DIR__ . '/template_word')) {
                    mkdir(__DIR__ . '/template_word', 0755, true);
                }

                if (copy($tmpPath, $dst)) {
                    $foto_profiling[] = $dst;
                } else {
                    error_log("Failed to copy profiling file from $tmpPath to $dst");
                }
            }
        }
    }

    // Data untuk template
    $nama_akun = $kategori = $narasi = $link = $profiling = [];
    $profilingDataPerReport = []; // Collect profiling data from each report
    
    foreach ($processedReports as $platform => $reports) {
        foreach ($reports as $report) {
            // Sanitize data for Patroli Landy Word document
            $nama_akun[] = $report['name'];
            $kategori[] = $report['category'];
            $narasi[] = cleanTextForWord($report['narrative']);
            $link[] = $report['link'];
            $profiling[] = cleanTextForWord($report['profiling'] ?? '');
            
            // If report has structured profiling data (from multi-line format), collect it
            if (isset($report['profilingData']) && !empty($report['profilingData'])) {
                $profilingDataPerReport[] = $report['profilingData'];
                error_log("Collected profiling data from report: " . $report['name']);
            } else {
                $profilingDataPerReport[] = null;
            }
        }
    }
    $tanggal_judul = $tanggalFormatted;
    $tanggal = $tanggalFormattedFirst;

    // Extract profiling data terstruktur dari POST/FORM (jika ada)
    $profilingData = [];
    if (isset($post['profilingNama']) && !empty($post['profilingNama'])) {
        error_log("Profiling data terstruktur dari FORM ditemukan - processing...");
        
        // Helper function to split comma-separated values
        $splitData = function($data) {
            if (empty($data)) return [];
            return array_map('trim', explode(',', $data));
        };
        
        $profilingData = [
            'nama' => $splitData($post['profilingNama'] ?? ''),
            'jenis_kelamin' => $splitData($post['profilingJenisKelamin'] ?? ''),
            'gol_darah' => $splitData($post['profilingGolDarah'] ?? ''),
            'status_nikah' => $splitData($post['profilingStatusNikah'] ?? ''),
            'agama' => $splitData($post['profilingAgama'] ?? ''),
            'tempat_lahir' => $splitData($post['profilingTempatLahir'] ?? ''),
            'umur' => $splitData($post['profilingUmur'] ?? ''),
            'tgl_lahir' => $splitData($post['profilingTglLahir'] ?? ''),
            'pekerjaan' => $splitData($post['profilingPekerjaan'] ?? ''),
            'provinsi' => $splitData($post['profilingProvinsi'] ?? ''),
            'kabupaten' => $splitData($post['profilingKabupaten'] ?? ''),
            'kecamatan' => $splitData($post['profilingKecamatan'] ?? ''),
            'kelurahan' => $splitData($post['profilingKelurahan'] ?? ''),
            'rt_rw' => $splitData($post['profilingRTRW'] ?? ''),
            'kode_pos' => $splitData($post['profilingKodePos'] ?? ''),
            'alamat' => $splitData($post['profilingAlamat'] ?? '')
        ];
        
        error_log("Profiling data dari FORM count: " . count($profilingData['nama']));
    } else {
        error_log("Profiling data terstruktur dari FORM TIDAK ditemukan");
    }

    // Create unique copies of patrol screenshots for Patroli Landy to avoid conflicts
    $foto_patroli = [];
    $timestamp = date('His_') . mt_rand(1000, 9999); // More unique timestamp
    foreach ($screenshotPaths as $index => $originalPath) {
        if (file_exists($originalPath)) {
            $pathInfo = pathinfo($originalPath);
            $uniqueName = 'landy_' . $timestamp . '_' . ($index + 1) . '_' . $pathInfo['filename'] . '.' . $pathInfo['extension'];
            $newPath = __DIR__ . '/foto/' . $uniqueName;

            if (copy($originalPath, $newPath)) {
                $foto_patroli[] = $newPath;
                error_log("Created Landy-specific copy: $originalPath -> $newPath");
            } else {
                error_log("Failed to create Landy-specific copy: $originalPath -> $newPath");
                // Fallback to original path if copy fails
                $foto_patroli[] = $originalPath;
            }
        }
    }

    $totalReports = count($nama_akun);

    // Debug validation conditions
    error_log("Patroli Landy validation - Total reports: " . $totalReports);
    error_log("Patroli Landy validation - Foto patroli count: " . count($foto_patroli));
    error_log("Patroli Landy validation - Foto upaya count: " . count($foto_upaya));

    // Skip validation if no reports to process
    if ($totalReports === 0) {
        error_log("Patroli Landy: No reports to process, skipping...");
        return;
    }

    // Validate foto patroli count must match total reports
    if (count($foto_patroli) !== $totalReports) {
        $error = "Jumlah foto patroli (" . count($foto_patroli) . ") tidak sesuai dengan jumlah data patroli (" . $totalReports . "). Harap upload " . $totalReports . " foto patroli.";
        error_log("Patroli Landy error: " . $error);
        throw new Exception($error);
    }

    // Validate foto upaya count must match total reports
    if (count($foto_upaya) !== $totalReports) {
        $error = "Jumlah foto RAS/Upaya (" . count($foto_upaya) . ") tidak sesuai dengan jumlah data patroli (" . $totalReports . "). Harap upload " . $totalReports . " foto RAS/Upaya.";
        error_log("Patroli Landy error: " . $error);
        throw new Exception($error);
    }

    // Validate foto profiling count must match total reports
    if (count($foto_profiling) !== $totalReports) {
        $error = "Jumlah foto profiling (" . count($foto_profiling) . ") tidak sesuai dengan jumlah data patroli (" . $totalReports . "). Harap upload " . $totalReports . " foto profiling.";
        error_log("Patroli Landy error: " . $error);
        throw new Exception($error);
    }

    // Validation passed
    error_log("Patroli Landy: Validation passed - {$totalReports} reports, {$totalReports} patrol photos, {$totalReports} upaya photos, {$totalReports} profiling photos");

    // Step 3: Create Word document
    $currentProgress += $progressStep;
    echo json_encode(['progress' => '3/5: Membuat file Word Patroli Landy...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    // Generate unique timestamp for file naming to avoid conflicts
    $timestamp = date('His'); // Format: HHMMSS
    $templatePathLandy = __DIR__ . '/template_word/template_patroli_landy.docx';
    
    // Use dynamic title (uppercase for consistency with PDF)
    $judulWord = strtoupper($judulLandy);
    $outputPathLandy = $hasilFolder . "/PATROLI SIBER DAN UPAYA KONTRA OPINI TERHADAP {$judulWord} UPDATE TANGGAL {$tanggalFormatted}.docx";

    try {
        createWordFileLandy($templatePathLandy, $outputPathLandy, [
            'nama_akun' => $nama_akun,
            'tanggal_judul' => $tanggal_judul,
            'tanggal' => $tanggal,
            'kategori' => $kategori,
            'narasi' => $narasi,
            'link' => $link,
            'foto_patroli' => $foto_patroli,
            'foto_upaya' => $foto_upaya,
            'foto_profiling' => $foto_profiling,
            'profiling' => $profiling,
            'profilingData' => $profilingData,  // From FORM
            'profilingDataPerReport' => $profilingDataPerReport  // From REPORT multi-line
        ]);
        error_log("Successfully created Patroli Landy Word file: $outputPathLandy");
    } catch (Exception $e) {
        error_log("Error: Failed to create Patroli Landy Word file: $outputPathLandy - " . $e->getMessage());
        throw new Exception("Gagal membuat file Word Patroli Landy: " . $e->getMessage());
    }

    // Step 4: Create PDF document
    $currentProgress += $progressStep;
    echo json_encode(['progress' => '4/5: Membuat file PDF Patroli Landy...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    $templatePathHtmlLandy = __DIR__ . '/template_pdf/template_patroli.html';
    $judulPdf = strtoupper($judulLandy); // Convert to uppercase for PDF title
    $outputPathPdfLandy = $hasilFolder . "/LAMPIRAN {$judulPdf} DI WILAYAH MERPATI - 14 PADA {$tanggalFormatted}.pdf";

    try {
        createPdfFileLandy($templatePathHtmlLandy, $outputPathPdfLandy, $foto_patroli, $foto_upaya, $judulPdf);
        error_log("Successfully created Patroli Landy PDF file: $outputPathPdfLandy");
    } catch (Exception $e) {
        error_log("Error: Failed to create Patroli Landy PDF file: $outputPathPdfLandy - " . $e->getMessage());
        throw new Exception("Gagal membuat file PDF Patroli Landy: " . $e->getMessage());
    }

    // Step 5: Complete Landy report processing
    $currentProgress += $progressStep;
    echo json_encode(['progress' => '5/5: Laporan Patroli Landy selesai dibuat...', 'percent' => (int)($startProgress + $progressRange)]);
    @ob_flush();
    @flush();

    // Verify files were created successfully
    if (!file_exists($outputPathLandy)) {
        error_log("Error: Patroli Landy Word file was not created: $outputPathLandy");
        $outputPathLandy = "";
    }
    if (!file_exists($outputPathPdfLandy)) {
        error_log("Error: Patroli Landy PDF file was not created: $outputPathPdfLandy");
        $outputPathPdfLandy = "";
    }

    // === CLEANUP ALL UPLOADED IMAGES ===
    error_log("=== PATROLI LANDY CLEANUP START ===");
    
    // Release memory and file handles before cleanup
    gc_collect_cycles();
    clearstatcache();
    
    // Cleanup all uploaded images (foto_patroli, foto_upaya, foto_profiling)
    $imagesToDelete = array_merge(
        $foto_patroli ?? [],
        $foto_upaya ?? [],
        $foto_profiling ?? []
    );
    
    error_log("Patroli Landy: Images to delete count: " . count($imagesToDelete));
    error_log("Patroli Landy: foto_patroli count: " . count($foto_patroli ?? []));
    error_log("Patroli Landy: foto_upaya count: " . count($foto_upaya ?? []));
    error_log("Patroli Landy: foto_profiling count: " . count($foto_profiling ?? []));
    
    if (count($imagesToDelete) > 0) {
        error_log("Patroli Landy: Starting cleanup of " . count($imagesToDelete) . " uploaded images");
        foreach ($imagesToDelete as $imgFile) {
            error_log("Patroli Landy: Attempting to delete: " . $imgFile);
            if (file_exists($imgFile)) {
                if (@unlink($imgFile)) {
                    error_log("Patroli Landy: ✓ DELETED: " . basename($imgFile));
                } else {
                    $lastError = error_get_last();
                    error_log("Patroli Landy: ✗ FAILED to delete: " . basename($imgFile) . " - " . ($lastError['message'] ?? 'Unknown error'));
                }
            } else {
                error_log("Patroli Landy: File doesn't exist: " . basename($imgFile));
            }
        }
        error_log("Patroli Landy: Image cleanup completed");
    } else {
        error_log("Patroli Landy: WARNING - No images to delete!");
    }
    error_log("=== PATROLI LANDY CLEANUP END ===");

    // Note: $narasiPatroliLandy was already set via reference in the heredoc above
    // Variables $outputPathLandy and $outputPathPdfLandy are passed by reference and are now set
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
    $hasilUpaya = prosesPatrolReport($rawUpaya, 'upaya', 3, false);
    $processedUpaya = $hasilUpaya['processedReports'];

    $akunPatroli = [];
    foreach ($processedReports as $platform => $reports) {
        foreach ($reports as $report) {
            // Sanitize patrol account name for upaya processing
            $akunPatroli[] = $report['name'];
        }
    }

    $narasiUpayaPagi = '';
    $idxPatroli = 0;
    foreach ($processedUpaya as $platform => $upayaList) {
        foreach ($upayaList as $upaya) {
            // Sanitize all text data for upaya narratives
            $nama_akun_patroli = $akunPatroli[$idxPatroli] ?? '-';
            $nama_akun_upaya = $upaya['name'] ?? '-';
            $link = $upaya['link'] ?? '-';
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
                $cleanName = $report['name'];
                $cleanLink = $report['link'];
                $cleanCategory = $report['category'];
                $cleanNarrative = $report['narrative'];
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
            $nama_akun[] =$report['name'];
            $kategori[] =$report['category'];
            $narasi[] = cleanTextForWord($report['narrative']);
            $link[] = $report['link'];
        }
    }
    $nama_akun_upaya = $narasi_upaya = $link_upaya = [];
    foreach ($processedUpaya as $platform => $reports) {
        foreach ($reports as $report) {
            // Sanitize upaya data for Word document compatibility
            $nama_akun_upaya[] = $report['name'] ?? '-';
            $narasi_upaya[] = cleanTextForWord($report['narrative'] ?? '-');
            $link_upaya[] = $report['link'] ?? '-';
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
        $processedCount = 0;
        foreach ($selectedFiles as $src) {
            $processedCount++;
            // Generate unique filename with timestamp and microsecond to avoid conflicts
            $timestamp = date('His') . '_' . substr(microtime(true) * 1000, -3); // HHMMSS_microseconds format
            $originalBasename = basename($src);
            $pathInfo = pathinfo($originalBasename);
            $uniqueBasename = 'upaya_pagi_' . $pathInfo['filename'] . '_' . $timestamp . '_' . $processedCount . '.' . $pathInfo['extension'];
            $dst = __DIR__ . '/template_word/' . $uniqueBasename;
            copy($src, $dst);
            $foto_upaya[] = $dst;
        }
        // Don't delete screenshot files immediately as other processes might need them
        // Cleanup will be handled at the end of the API call
        // foreach ($selectedFiles as $src) {
        //     @unlink($src);
        // }
    } else {
        // Process uploaded upaya files with unique naming
        if (isset($files['upayaFiles']) && !empty($files['upayaFiles']['tmp_name'])) {
            $timestamp = date('His') . '_' . substr(microtime(true) * 1000, -3); // HHMMSS_microseconds format
            foreach ($files['upayaFiles']['tmp_name'] as $index => $tmpPath) {
                if (!empty($tmpPath) && file_exists($tmpPath)) {
                    $originalName = $files['upayaFiles']['name'][$index] ?? 'upaya_file.jpg';
                    $pathInfo = pathinfo($originalName);
                    $uniqueName = 'upaya_pagi_' . $timestamp . '_' . ($index + 1) . '_' . $pathInfo['filename'] . '.' . ($pathInfo['extension'] ?? 'jpg');
                    $dst = __DIR__ . '/template_word/' . $uniqueName;

                    // Ensure template_word directory exists
                    if (!is_dir(__DIR__ . '/template_word')) {
                        mkdir(__DIR__ . '/template_word', 0755, true);
                    }

                    if (copy($tmpPath, $dst)) {
                        $foto_upaya[] = $dst;
                    } else {
                        error_log("Failed to copy upaya file from $tmpPath to $dst");
                    }
                }
            }
        }
    }

    $tanggal_judul = $tanggalFormatted;

    // Create unique copies of patrol screenshots for Patroli Pagi to avoid conflicts
    $foto_patroli = [];
    $timestamp = date('His_') . mt_rand(1000, 9999); // More unique timestamp
    foreach ($screenshotPaths as $index => $originalPath) {
        if (file_exists($originalPath)) {
            $pathInfo = pathinfo($originalPath);
            $uniqueName = 'pagi_' . $timestamp . '_' . ($index + 1) . '_' . $pathInfo['filename'] . '.' . $pathInfo['extension'];
            $newPath = __DIR__ . '/foto/' . $uniqueName;

            if (copy($originalPath, $newPath)) {
                $foto_patroli[] = $newPath;
                error_log("Created Pagi-specific copy: $originalPath -> $newPath");
            } else {
                error_log("Failed to create Pagi-specific copy: $originalPath -> $newPath");
                // Fallback to original path if copy fails
                $foto_patroli[] = $originalPath;
            }
        }
    }

    $tahun_input = date('Y', strtotime($post['tanggal'] ?? date('Y-m-d')));
    $totalReports = count($nama_akun);
    $totalUpaya = count($nama_akun_upaya);

    // More flexible validation - ensure we have at least some photos when there are reports
    if ($totalReports > 0 && count($foto_patroli) === 0) {
        throw new Exception('Tidak ada screenshot patroli untuk laporan yang diproses.');
    }

    // Make upaya photos optional - warn but don't fail if missing
    if ($totalReports > 0 && count($foto_upaya) === 0) {
        error_log("Patroli Pagi warning: Tidak ada foto upaya. Laporan akan dibuat tanpa foto upaya.");
        // Don't throw exception, just log warning
    }

    // Log counts for debugging
    error_log("Patroli Pagi: Using " . min(count($foto_patroli), $totalReports) . " patrol photos for $totalReports reports");
    error_log("Patroli Pagi: Using " . min(count($foto_upaya), $totalReports) . " upaya photos for $totalReports reports");

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

    // Generate unique timestamp for file naming to avoid conflicts
    $timestamp = date('His'); // Format: HHMMSS
    $templatePathPagi = __DIR__ . '/template_word/template_Patroli_pagi.docx';
    $outputPathPagi = $hasilFolder . "/Laporan Patroli Siber Konten Negatif di Wilayah Merpati-14 Update {$tanggalFormatted}.docx";
    $outputPathPdfPagi = $hasilFolder . "/Lampiran Patroli Siber Merpati 14 ({$tanggalFormatted}).pdf";

    try {
        createWordFilePagi($templatePathPagi, $outputPathPagi, [
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
        ]);
        error_log("Successfully created Patroli Pagi Word file: $outputPathPagi");
    } catch (Exception $e) {
        error_log("Error: Failed to create Patroli Pagi Word file: $outputPathPagi - " . $e->getMessage());
        throw new Exception("Gagal membuat file Word Patroli Pagi: " . $e->getMessage());
    }

    // Step 6: Create PDF document for Patroli Pagi
    $currentProgress += $progressStep;
    echo json_encode(['progress' => '6/6: Membuat file PDF Patroli Pagi...', 'percent' => (int)$currentProgress]);
    @ob_flush();
    @flush();

    $templatePathHtmlPagi = __DIR__ . '/template_pdf/template_patroli.html';

    try {
        createPdfFileLandy($templatePathHtmlPagi, $outputPathPdfPagi, $foto_patroli, $foto_upaya);
        error_log("Successfully created Patroli Pagi PDF file: $outputPathPdfPagi");
    } catch (Exception $e) {
        error_log("Error: Failed to create Patroli Pagi PDF file: $outputPathPdfPagi - " . $e->getMessage());
        throw new Exception("Gagal membuat file PDF Patroli Pagi: " . $e->getMessage());
    }

    // Final step: Complete Patroli Pagi processing
    echo json_encode(['progress' => 'Laporan Patroli Pagi selesai dibuat!', 'percent' => (int)($startProgress + $progressRange)]);
    @ob_flush();
    @flush();

    // Verify files were created successfully
    if (!file_exists($outputPathPagi)) {
        error_log("Error: Patroli Pagi Word file was not created: $outputPathPagi");
        $outputPathPagi = "";
    }
    if (!file_exists($outputPathPdfPagi)) {
        error_log("Error: Patroli Pagi PDF file was not created: $outputPathPdfPagi");
        $outputPathPdfPagi = "";
    }

    // Note: $narasiPatroliPagi was already set via reference in the heredoc above
    // Variables $outputPathPagi and $outputPathPdfPagi are passed by reference and are now set
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
function cleanTextForWord($text)
{
    if (empty($text)) {
        return '';
    }

    // Trim whitespace
    $text = trim($text);

    // First, decode any existing HTML entities to prevent double encoding
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // Convert to UTF-8 if not already
    if (!mb_check_encoding($text, 'UTF-8')) {
        $text = mb_convert_encoding($text, 'UTF-8', 'auto');
    }

    // Replace smart quotes and dashes with regular ones for better Word compatibility
    $text = str_replace([
        "\u{201C}",
        "\u{201D}", // Smart double quotes
        "\u{2018}",
        "\u{2019}", // Smart single quotes
        "\u{2013}",
        "\u{2014}", // En dash, Em dash
        "\u{2026}", // Ellipsis
    ], [
        '"',
        '"',
        "'",
        "'",
        '-',
        '--',
        '...',
    ], $text);

    // Only remove dangerous control characters that can corrupt Word files
    // Keep newlines and tabs as they're important for formatting
    $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);    // Fix only problematic HTML entity remnants, but be more selective
    $text = preg_replace('/\b&quot\b/i', '"', $text);
    $text = preg_replace('/\b&amp\b/i', '&', $text);
    $text = preg_replace('/\b&lt\b/i', '<', $text);
    $text = preg_replace('/\b&gt\b/i', '>', $text);

    // Replace problematic URL characters with Word-safe alternatives
    // This preserves the readability while making it Word-compatible
    $text = str_replace([
        '://',   // Protocol separator
        '/',     // Path separator
        '?',     // Query separator
        '=',     // Parameter separator
        '&',     // Parameter joiner
        '#',     // Fragment separator
        '%',     // URL encoding character
    ], [
        '://',   // Replace :// with ___
        '/',     // Replace / with _
        '?',   // Replace ? with _q_
        '=',  // Replace = with _eq_
        '', // Replace & with _and_
        '',// Replace # with _hash_
        '', // Replace % with _pct_
    ], $text);

    // Remove any remaining problematic characters for Word
    // Note: Preserving quotes (" and ') as requested
    $text = preg_replace('/[<>|*\\\\]/', '', $text);

    return $text;
}
