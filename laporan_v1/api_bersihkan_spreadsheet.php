<?php
require_once 'vendor/autoload.php';

function getClient() {
    $client = new Google\Client();
    $client->setApplicationName('Google Sheets API PHP');
    $client->setScopes([Google\Service\Sheets::SPREADSHEETS]);
    
    $client->setAuthConfig('credentials.json');
    $client->setAccessType('online');

    return $client;
}

function cleanSpreadsheet() {
    try {
        // Inisialisasi client
        $client = getClient();
        $service = new Google\Service\Sheets($client);

        // ID Google Spreadsheet
        $spreadsheetId = '1B7KdBTJ8XHjcLRmRgoXIVOqxW6BoQDlUeZLMkoqS5DU';

        // Sheet yang akan dibersihkan
        $sheetNames = ['FACEBOOK', 'INSTAGRAM', 'TWITTER', 'SNACKVIDEO', 'YOUTUBE', 'TIKTOK'];
        $cleanedData = [];

        foreach ($sheetNames as $sheetName) {
            try {
                // Range untuk kolom E (link)
                $rangeLinkColumn = $sheetName . '!E4:E';
                
                // Membuat request untuk menghapus konten kolom link
                $clearRequest = new Google\Service\Sheets\ClearValuesRequest();
                
                try {
                    // Ambil data sebelum dibersihkan untuk menghitung jumlah sel
                    $beforeClear = $service->spreadsheets_values->get(
                        $spreadsheetId,
                        $rangeLinkColumn
                    );
                    $cellCount = 0;
                    if ($beforeClear->getValues()) {
                        foreach ($beforeClear->getValues() as $row) {
                            if (!empty($row[0])) {
                                $cellCount++;
                            }
                        }
                    }

                    // Menghapus konten kolom link
                    $response = $service->spreadsheets_values->clear(
                        $spreadsheetId,
                        $rangeLinkColumn,
                        $clearRequest
                    );
                    
                    $cleanedData[$sheetName] = [
                        'status' => 'success',
                        'message' => "Kolom link berhasil dibersihkan ($cellCount link dihapus)",
                        'clearedCount' => $cellCount
                    ];
                } catch (Exception $e) {
                    $cleanedData[$sheetName] = [
                        'status' => 'error',
                        'message' => 'Gagal membersihkan kolom link: ' . $e->getMessage()
                    ];
                }
            } catch (Exception $e) {
                $cleanedData[$sheetName] = [
                    'status' => 'error',
                    'message' => 'Error mengakses sheet: ' . $e->getMessage()
                ];
            }
        }

        // Output hasil dalam format JSON
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => $cleanedData
        ], JSON_PRETTY_PRINT);

    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

// Jalankan fungsi pembersihan
cleanSpreadsheet();
?>