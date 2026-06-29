<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

echo "Testing Excel extraction improvements...\n\n";

// Test with the template file
$testFile = 'template_excel/template_danantara.xlsx';

if (!file_exists($testFile)) {
    echo "Test file not found: $testFile\n";
    exit(1);
}

echo "Loading file: $testFile\n";

try {
    $spreadsheet = IOFactory::load($testFile);
    $sheetNames = $spreadsheet->getSheetNames();
    echo "Available sheets: " . implode(', ', $sheetNames) . "\n\n";
    
    // Test with first sheet
    $sheet = $spreadsheet->getActiveSheet();
    $sheetName = $sheet->getTitle();
    echo "Testing sheet: $sheetName\n";
    
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();
    echo "Sheet dimensions: $highestRow rows x $highestColumn columns\n\n";
    
    echo "Testing cell extraction (first 10 rows):\n";
    echo "----------------------------------------\n";
    
    $rowIndex = 0;
    foreach ($sheet->getRowIterator() as $row) {
        $rowIndex++;
        if ($rowIndex > 10) break; // Only test first 10 rows
        
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        
        $data = [];
        $cellIndex = 0;
        foreach ($cellIterator as $cell) {
            if ($cellIndex <= 6) { // Ambil kolom 0 sampai 6 (7 kolom total)
                // Get cell value safely - handle formulas, objects, and other types
                $cellValue = $cell->getValue();
                
                // Track original type for debugging
                $originalType = gettype($cellValue);
                $originalPreview = is_object($cellValue) ? get_class($cellValue) : 
                                  (is_array($cellValue) ? 'Array[' . count($cellValue) . ']' : 
                                  (is_string($cellValue) ? '"' . substr($cellValue, 0, 20) . '"' : 
                                  json_encode($cellValue)));
                
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
                        }
                    }
                } else if ($cellValue === null) {
                    $cellValue = '';
                } else {
                    // Ensure it's a string
                    $cellValue = (string)$cellValue;
                }
                
                // Final safety check
                if (is_object($cellValue) || is_array($cellValue)) {
                    $cellValue = '';
                }
                
                $data[] = $cellValue;
                
                // Log any conversions
                if ($originalType !== 'string' && $originalType !== 'NULL' && !empty($cellValue)) {
                    echo "  Row $rowIndex, Col $cellIndex: $originalType ($originalPreview) -> string (\"$cellValue\")\n";
                } else if ($originalType === 'object' || $originalType === 'array') {
                    echo "  Row $rowIndex, Col $cellIndex: $originalType ($originalPreview) -> empty string (CONVERTED)\n";
                }
            }
            $cellIndex++;
            if ($cellIndex > 6) {
                break;
            }
        }
        
        echo "Row $rowIndex: " . json_encode($data) . "\n";
    }
    
    echo "\nTest completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
