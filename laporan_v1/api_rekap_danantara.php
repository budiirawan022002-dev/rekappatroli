<?php
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;

function getClient()
{
  $client = new Google\Client();
  $client->setApplicationName('Google Sheets API PHP');
  $client->setScopes(Google\Service\Sheets::SPREADSHEETS_READONLY);

  // Ganti dengan path ke credentials.json Anda
  $client->setAuthConfig('credentials.json');
  $client->setAccessType('online');

  return $client;
}

function generateExcelFile($allData, $akunIndukData, $topik, $sheetNames)
{
  try {
    // Load template
    $templatePath = 'template_excel/template_danantara.xlsx';
    $spreadsheet = IOFactory::load($templatePath);

    // Generate filename with current date and topik
    $currentDate = date('dmY');
    $cleanTopik = preg_replace('/[^a-zA-Z0-9\s]/', '', $topik);
    $filename = "{$currentDate} - {$cleanTopik} - M-14.xlsx";
    $outputPath = "hasil/{$filename}";

    // Fill AKUN INDUK sheet with data
    try {
      $akunIndukSheet = $spreadsheet->getSheetByName('AKUN INDUK');
      $topikLaporanSheet = $spreadsheet->getSheetByName('LAPORAN');

      // Fill akun induk data
      $akunIndukMapping = [
        'FACEBOOK' => ['row' => 4, 'fields' => ['nama_akun' => 'B', 'link_postingan' => 'C', 'like' => 'D', 'comments' => 'E', 'share' => 'F']],
        'INSTAGRAM' => ['row' => 7, 'fields' => ['nama_akun' => 'B', 'link_postingan' => 'C', 'like' => 'D', 'comments' => 'E']],
        'TWITTER' => ['row' => 10, 'fields' => ['nama_akun' => 'B', 'link_postingan' => 'C', 'like' => 'D', 'comments' => 'E', 'retweets' => 'F']],
        'TIKTOK' => ['row' => 13, 'fields' => ['nama_akun' => 'B', 'link_postingan' => 'C', 'like' => 'D', 'comments' => 'E', 'share' => 'F']],
        'SNACKVIDEO' => ['row' => 16, 'fields' => ['nama_akun' => 'B', 'link_postingan' => 'C', 'like' => 'D', 'comments' => 'E', 'share' => 'F']],
        'YOUTUBE' => ['row' => 19, 'fields' => ['nama_akun' => 'B', 'link_postingan' => 'C', 'like' => 'D', 'comments' => 'E', 'retweets' => 'F']]
      ];

      foreach ($akunIndukMapping as $platform => $config) {
        if (isset($akunIndukData[$platform]) && !isset($akunIndukData[$platform]['error'])) {
          $platformData = $akunIndukData[$platform];

          foreach ($config['fields'] as $fieldName => $column) {
            if (isset($platformData[$fieldName])) {
              $cellAddress = $column . $config['row'];
              $akunIndukSheet->setCellValue($cellAddress, $platformData[$fieldName]);
            }
          }
        }
      }

      // Fill topik in E11
      $topikLaporanSheet->setCellValue('E11', $topik);
    } catch (Exception $e) {
      // AKUN INDUK sheet not found or error filling data
    }

    // Fill platform sheets with links
    foreach ($sheetNames as $sheetName) {
      try {
        $platformSheet = $spreadsheet->getSheetByName($sheetName);

        // Fill links in column D starting from D4
        if (isset($allData[$sheetName]) && is_array($allData[$sheetName])) {
          $row = 4; // Start from row 4 as specified
          foreach ($allData[$sheetName] as $link) {
            $platformSheet->setCellValue('D' . $row, $link);
            $row++;
          }
        }

        // Fill nama akun in column C starting from C4
        if (isset($allData[$sheetName . '_akun']) && is_array($allData[$sheetName . '_akun'])) {
          $row = 4; // Start from row 4 as specified
          foreach ($allData[$sheetName . '_akun'] as $namaAkun) {
            $platformSheet->setCellValue('C' . $row, $namaAkun);
            $row++;
          }
        }
      } catch (Exception $e) {
        // Sheet not found, continue with next sheet
        continue;
      }
    }

    // Save the file
    $writer = new Xlsx($spreadsheet);
    $writer->save($outputPath);

    // Read narasi from the current spreadsheet (before saving) to avoid reloading
    $narasiFromExcel = '';
    try {
      $laporanSheet = $spreadsheet->getSheetByName('LAPORAN');
      
      // Enable calculation engine and calculate all formulas
      Calculation::getInstance($spreadsheet)->clearCalculationCache();
      
      // Read range A2:A71 in one go instead of cell by cell
      $narasiRange = $laporanSheet->rangeToArray('A2:A71', null, true, true, false);
      
      $narasiArray = [];
      foreach ($narasiRange as $row) {
        // Add each cell content, even if empty, to maintain structure
        $cellValue = isset($row[0]) && $row[0] !== null ? trim((string)$row[0]) : '';
        $narasiArray[] = $cellValue; // Include empty cells to maintain row structure
      }
      
      // Combine all narasi with newlines (each cell gets its own line)
      $narasiFromExcel = implode("\n", $narasiArray);
    } catch (Exception $e) {
      $narasiFromExcel = 'Error membaca narasi dari Excel: ' . $e->getMessage();
    }

    return [
      'filename' => $filename,
      'path' => $outputPath,
      'status' => 'success',
      'narasi_excel' => $narasiFromExcel
    ];
  } catch (Exception $e) {
    return [
      'status' => 'error',
      'message' => 'Error generating Excel file: ' . $e->getMessage()
    ];
  }
}

try {
  // Inisialisasi client
  $client = getClient();
  $service = new Google\Service\Sheets($client);

  // ID Google Spreadsheet Anda
  $spreadsheetId = '1ak9KQjBmNZxNxPns65c9eArWSIC84WBw5WogbIja_1o';

  // Ambil data akun induk dari sheet AKUN INDUK dengan batch reading
  $akunIndukData = [];
  $akunIndukMapping = [
    'FACEBOOK' => ['row' => 4, 'fields' => ['nama_akun' => 'B', 'link_postingan' => 'C', 'like' => 'D', 'comments' => 'E', 'share' => 'F']],
    'INSTAGRAM' => ['row' => 7, 'fields' => ['nama_akun' => 'B', 'link_postingan' => 'C', 'like' => 'D', 'comments' => 'E']],
    'TWITTER' => ['row' => 10, 'fields' => ['nama_akun' => 'B', 'link_postingan' => 'C', 'like' => 'D', 'comments' => 'E', 'retweets' => 'F']],
    'TIKTOK' => ['row' => 13, 'fields' => ['nama_akun' => 'B', 'link_postingan' => 'C', 'like' => 'D', 'comments' => 'E', 'share' => 'F']],
    'SNACKVIDEO' => ['row' => 16, 'fields' => ['nama_akun' => 'B', 'link_postingan' => 'C', 'like' => 'D', 'comments' => 'E', 'share' => 'F']],
    'YOUTUBE' => ['row' => 19, 'fields' => ['nama_akun' => 'B', 'link_postingan' => 'C', 'like' => 'D', 'comments' => 'E', 'retweets' => 'F']]
  ];

  try {
    // Batch read semua data AKUN INDUK sekaligus (B4:F19)
    $rangeAkunInduk = 'AKUN INDUK!B4:F19';
    $responseAkunInduk = $service->spreadsheets_values->get($spreadsheetId, $rangeAkunInduk);
    $valuesAkunInduk = $responseAkunInduk->getValues();

    foreach ($akunIndukMapping as $platform => $config) {
      $platformData = [];
      $rowIndex = $config['row'] - 4; // Convert to 0-based index

      if (isset($valuesAkunInduk[$rowIndex])) {
        $rowData = $valuesAkunInduk[$rowIndex];
        foreach ($config['fields'] as $fieldName => $column) {
          $colIndex = ord($column) - ord('B'); // Convert column letter to index
          $value = isset($rowData[$colIndex]) ? trim($rowData[$colIndex]) : '';
          $platformData[$fieldName] = $value;
        }
      } else {
        // Set empty values if row not found
        foreach ($config['fields'] as $fieldName => $column) {
          $platformData[$fieldName] = '';
        }
      }

      $akunIndukData[$platform] = $platformData;
    }
  } catch (Exception $e) {
    // Fallback to individual calls if batch fails
    foreach ($akunIndukMapping as $platform => $config) {
      $akunIndukData[$platform] = ['error' => 'Error membaca data akun induk: ' . $e->getMessage()];
    }
  }

  // Ambil data topik dari sheet LAPORAN
  try {
    $rangeTopik = 'LAPORAN!E11';
    $responseTopik = $service->spreadsheets_values->get($spreadsheetId, $rangeTopik);
    $valuesTopik = $responseTopik->getValues();
    $topik = !empty($valuesTopik) && isset($valuesTopik[0][0]) ? trim($valuesTopik[0][0]) : '';
  } catch (Exception $e) {
    $topik = 'Error membaca topik: ' . $e->getMessage();
  }

  // Sheet yang akan dibaca
  $sheetNames = ['FACEBOOK', 'INSTAGRAM', 'TWITTER', 'SNACKVIDEO', 'YOUTUBE', 'TIKTOK'];
  $allData = [
    'akun_induk' => $akunIndukData,
    'topik' => $topik
  ];

  // Batch request untuk semua sheet sekaligus
  try {
    $batchRequests = [];
    foreach ($sheetNames as $sheetName) {
      $batchRequests[] = $sheetName . '!C4:D';
    }

    // Gunakan batchGet untuk membaca semua sheet sekaligus
    $batchResponse = $service->spreadsheets_values->batchGet($spreadsheetId, [
      'ranges' => $batchRequests
    ]);

    $batchValues = $batchResponse->getValueRanges();

    foreach ($sheetNames as $index => $sheetName) {
      try {
        $values = isset($batchValues[$index]) ? $batchValues[$index]->getValues() : [];

        $links = [];
        $namaAkun = [];

        if (!empty($values)) {
          foreach ($values as $row) {
            $akun = isset($row[0]) ? trim($row[0]) : '';
            $link = isset($row[1]) ? trim($row[1]) : '';

            // Tambahkan link dan nama akun jika link tidak kosong
            if ($link && strlen($link) > 0) {
              $links[] = $link;
              $namaAkun[] = $akun; // Tambahkan nama akun yang sesuai (bisa kosong)
            }
          }

          if (!empty($links)) {
            $allData[$sheetName] = $links;
            $allData[$sheetName . '_akun'] = $namaAkun;
          } else {
            $allData[$sheetName] = "Tidak ada link ditemukan di sheet $sheetName";
            $allData[$sheetName . '_akun'] = "Tidak ada nama akun ditemukan di sheet $sheetName";
          }
        } else {
          $allData[$sheetName] = "Tidak ada data ditemukan di sheet $sheetName";
          $allData[$sheetName . '_akun'] = "Tidak ada data nama akun ditemukan di sheet $sheetName";
        }
      } catch (Exception $e) {
        $allData[$sheetName] = "Error membaca sheet $sheetName: " . $e->getMessage();
        $allData[$sheetName . '_akun'] = "Error membaca nama akun sheet $sheetName: " . $e->getMessage();
      }
    }
  } catch (Exception $e) {
    // Fallback to individual requests if batch fails
    foreach ($sheetNames as $sheetName) {
      try {
        // Membaca kolom C dan D secara bersamaan mulai dari baris 4
        $rangeData = $sheetName . '!C4:D';

        // Membaca data
        $response = $service->spreadsheets_values->get($spreadsheetId, $rangeData);
        $values = $response->getValues();

        $links = [];
        $namaAkun = [];

        if (!empty($values)) {
          foreach ($values as $row) {
            $akun = isset($row[0]) ? trim($row[0]) : '';
            $link = isset($row[1]) ? trim($row[1]) : '';

            // Tambahkan link dan nama akun jika link tidak kosong
            if ($link && strlen($link) > 0) {
              $links[] = $link;
              $namaAkun[] = $akun; // Tambahkan nama akun yang sesuai (bisa kosong)
            }
          }

          if (!empty($links)) {
            $allData[$sheetName] = $links;
            $allData[$sheetName . '_akun'] = $namaAkun;
          } else {
            $allData[$sheetName] = "Tidak ada link ditemukan di sheet $sheetName";
            $allData[$sheetName . '_akun'] = "Tidak ada nama akun ditemukan di sheet $sheetName";
          }
        } else {
          $allData[$sheetName] = "Tidak ada data ditemukan di sheet $sheetName";
          $allData[$sheetName . '_akun'] = "Tidak ada data nama akun ditemukan di sheet $sheetName";
        }
      } catch (Exception $e) {
        $allData[$sheetName] = "Error membaca sheet $sheetName: " . $e->getMessage();
        $allData[$sheetName . '_akun'] = "Error membaca nama akun sheet $sheetName: " . $e->getMessage();
      }
    }
  }

  // Generate Excel file
  $excelFile = generateExcelFile($allData, $akunIndukData, $topik, $sheetNames);

  // Add narasi from Excel to main data if available
  if (isset($excelFile['narasi_excel'])) {
    $allData['narasi_excel'] = $excelFile['narasi_excel'];
  }

  // Output data dalam format JSON
  header('Content-Type: application/json');
  echo json_encode([
    'status' => 'success',
    'data' => $allData,
    'excel_file' => $excelFile
  ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
  header('Content-Type: application/json');
  echo json_encode([
    'status' => 'error',
    'message' => 'Error: ' . $e->getMessage()
  ]);
}
