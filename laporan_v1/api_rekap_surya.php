<?php
require_once 'vendor/autoload.php';

function getClient() {
    $client = new Google\Client();
    $client->setApplicationName('Google Sheets API PHP');
    $client->setScopes(Google\Service\Sheets::SPREADSHEETS_READONLY);
    
    // Ganti dengan path ke credentials.json Anda
    $client->setAuthConfig('credentials.json');
    $client->setAccessType('online');

    return $client;
}

function cleanTopicString($topic) {
    // Menghapus nomor dan titik di awal topik (contoh: "1. TOPIC" menjadi "TOPIC")
    return trim(preg_replace('/^\d+\.\s*/', '', $topic));
}

try {
    // Inisialisasi client
    $client = getClient();
    $service = new Google\Service\Sheets($client);

    // ID Google Spreadsheet Anda
    $spreadsheetId = '1B7KdBTJ8XHjcLRmRgoXIVOqxW6BoQDlUeZLMkoqS5DU';

    // Ambil tanggal laporan dari sheet INPUT TOPIK
    try {
        $rangeTanggal = 'INPUT TOPIK!C2';
        $responseTanggal = $service->spreadsheets_values->get($spreadsheetId, $rangeTanggal);
        $valuesTanggal = $responseTanggal->getValues();
        $tanggalLaporan = !empty($valuesTanggal) ? $valuesTanggal[0][0] : 'Tanggal tidak ditemukan';
    } catch (Exception $e) {
        $tanggalLaporan = 'Error membaca tanggal: ' . $e->getMessage();
    }

    // Ambil narasi pagi dan sore dari sheet lap_surya
    try {
        // Ambil narasi pagi dari cell A4
        $rangeNarasiPagi = 'TES!E3';
        $responseNarasiPagi = $service->spreadsheets_values->get($spreadsheetId, $rangeNarasiPagi);
        $valuesNarasiPagi = $responseNarasiPagi->getValues();
        $narasiPagi = !empty($valuesNarasiPagi) ? $valuesNarasiPagi[0][0] : '';

        // Ambil narasi sore dari cell C4
        $rangeNarasiSore = 'TES!F3';
        $responseNarasiSore = $service->spreadsheets_values->get($spreadsheetId, $rangeNarasiSore);
        $valuesNarasiSore = $responseNarasiSore->getValues();
        $narasiSore = !empty($valuesNarasiSore) ? $valuesNarasiSore[0][0] : '';
    } catch (Exception $e) {
        $narasiPagi = '';
        $narasiSore = 'Error membaca narasi: ' . $e->getMessage();
    }

    // Sheet yang akan dibaca
    $sheetNames = ['FACEBOOK', 'INSTAGRAM', 'TWITTER', 'SNACKVIDEO', 'YOUTUBE', 'TIKTOK'];
    $allData = [
        'tanggal_laporan' => $tanggalLaporan,
        'narasi' => [
            'pagi' => $narasiPagi,
            'sore' => $narasiSore
        ]
    ];

    foreach ($sheetNames as $sheetName) {
        try {
            // Membaca kolom E (link) dan L (topik) mulai dari baris 4
            $rangeLinkAndTopic = $sheetName . '!E4:L';
            
            // Membaca data
            $response = $service->spreadsheets_values->get($spreadsheetId, $rangeLinkAndTopic);
            $values = $response->getValues();

            if (!empty($values)) {
                $groupedByTopic = [];
                $currentTopic = null;
                $currentLinks = [];
                
                foreach ($values as $row) {
                    $link = isset($row[0]) ? $row[0] : null; // Kolom E (index 0)
                    $topic = isset($row[7]) ? $row[7] : null; // Kolom L (index 7)
                    
                    // Jika ada topik baru yang tidak kosong
                    if ($topic && strlen(cleanTopicString($topic)) > 0) {
                        // Simpan links dari topik sebelumnya jika ada
                        if ($currentTopic && !empty($currentLinks)) {
                            $groupedByTopic[$currentTopic] = $currentLinks;
                        }
                        
                        // Set topik baru
                        $currentTopic = cleanTopicString($topic);
                        $currentLinks = [];
                        
                        // Tambahkan link saat ini jika ada
                        if ($link) {
                            $currentLinks[] = $link;
                        }
                    }
                    // Jika ada link dan sudah ada topik sebelumnya
                    elseif ($link && $currentTopic) {
                        $currentLinks[] = $link;
                    }
                }
                
                // Simpan links dari topik terakhir
                if ($currentTopic && !empty($currentLinks)) {
                    $groupedByTopic[$currentTopic] = $currentLinks;
                }

                if (!empty($groupedByTopic)) {
                    $allData[$sheetName] = $groupedByTopic;
                } else {
                    $allData[$sheetName] = "Tidak ada data dengan topik yang valid di sheet $sheetName";
                }
            } else {
                $allData[$sheetName] = "Tidak ada data ditemukan di sheet $sheetName";
            }
        } catch (Exception $e) {
            $allData[$sheetName] = "Error membaca sheet $sheetName: " . $e->getMessage();
        }
    }

    // Format tanggal jika diperlukan
    if (isset($allData['tanggal_laporan']) && $allData['tanggal_laporan'] !== 'Tanggal tidak ditemukan' && !strstr($allData['tanggal_laporan'], 'Error')) {
        // Coba format tanggal jika formatnya valid
        try {
            $date = new DateTime($allData['tanggal_laporan']);
            $allData['tanggal_laporan'] = $date->format('d F Y');
        } catch (Exception $e) {
            // Biarkan format asli jika tidak bisa diparse
        }
    }

    // Output data dalam format JSON
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'data' => $allData
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>