<?php
require 'vendor/autoload.php';
require 'fungsi_proses.php';
require 'fungsi_konversi.php';

header('Content-Type: application/json');

function processUploadedFile($file, $type = 'patrol') {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['error' => 'No file uploaded'];
    }

    // Check file type
    $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($fileType != 'txt') {
        return ['error' => 'Only .txt files are allowed'];
    }

    // Read file content
    $content = file_get_contents($file['tmp_name']);
    if ($content === false) {
        return ['error' => 'Failed to read file'];
    }

    // Process content
    $lines = explode("\n", $content);
    $processedData = [];
    $currentEntry = [];
    $lineCount = 0;
    $expectedLines = ($type === 'patrol') ? 4 : 3;

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) {
            if (!empty($currentEntry)) {
                if (count($currentEntry) === $expectedLines) {
                    $processedData[] = $currentEntry;
                }
                $currentEntry = [];
            }
            continue;
        }

        $currentEntry[] = $line;
        $lineCount++;

        if (count($currentEntry) === $expectedLines) {
            $processedData[] = $currentEntry;
            $currentEntry = [];
        }
    }

    // Check if last entry is complete
    if (!empty($currentEntry) && count($currentEntry) === $expectedLines) {
        $processedData[] = $currentEntry;
    }

    // Validate format
    if (empty($processedData)) {
        return ['error' => 'No valid data found in file'];
    }

    // Format data for response
    $formattedData = [];
    foreach ($processedData as $entry) {
        if ($type === 'patrol') {
            $formattedData[] = [
                'name' => $entry[0],
                'link' => $entry[1],
                'category' => $entry[2],
                'narrative' => $entry[3]
            ];
        } else {
            $formattedData[] = [
                'name' => $entry[0],
                'link' => $entry[1],
                'narrative' => $entry[2]
            ];
        }
    }

    return [
        'success' => true,
        'data' => $formattedData,
        'count' => count($formattedData)
    ];
}

// Handle the upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['patrolReportFile'])) {
        $result = processUploadedFile($_FILES['patrolReportFile'], 'patrol');
        echo json_encode($result);
    } elseif (isset($_FILES['upayaFile'])) {
        $result = processUploadedFile($_FILES['upayaFile'], 'upaya');
        echo json_encode($result);
    } else {
        echo json_encode(['error' => 'No file uploaded']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
} 