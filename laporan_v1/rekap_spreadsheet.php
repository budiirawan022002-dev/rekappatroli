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

try {
    // Inisialisasi client
    $client = getClient();
    $service = new Google\Service\Sheets($client);

    // ID Google Spreadsheet Anda (dari URL)
    $spreadsheetId = '1B7KdBTJ8XHjcLRmRgoXIVOqxW6BoQDlUeZLMkoqS5DU';

    // Sheet yang akan dibaca
    $sheetNames = ['FACEBOOK', 'INSTAGRAM', 'TWITTER', 'SNACKVIDEO', 'YOUTUBE', 'TIKTOK'];
    $allData = [];

    foreach ($sheetNames as $sheetName) {
        try {
            // Range yang akan dibaca (misalnya A1:Z1000)
            $range = $sheetName . '!E4:E';
            
            // Membaca data
            $response = $service->spreadsheets_values->get($spreadsheetId, $range);
            $values = $response->getValues();

            if (empty($values)) {
                $allData[$sheetName] = "Tidak ada data ditemukan di sheet $sheetName";
            } else {
                $allData[$sheetName] = $values;
            }
        } catch (Exception $e) {
            $allData[$sheetName] = "Error membaca sheet $sheetName: " . $e->getMessage();
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