<?php
// Script para convertir funciones de PostgreSQL a MySQL
// Reemplaza to_char() con DATE_FORMAT() y otras funciones

$baseDir = __DIR__ . '/../current/src/Controller';
$files = glob($baseDir . '/*.php');

$replacements = [
    // to_char() conversions
    "/to_char\(([^,]+),\s*'DD\/MM\/YYYY'\)/i" => "DATE_FORMAT($1, '%d/%m/%Y')",
    "/to_char\(([^,]+),\s*'YYYYMMDDHHmm'\)/i" => "DATE_FORMAT($1, '%Y%m%d%H%i')",
    "/to_char\(([^,]+),\s*'YYYY-MM-DD'\)/i" => "DATE_FORMAT($1, '%Y-%m-%d')",
    "/to_char\(([^,]+),\s*'DD-MM-YYYY'\)/i" => "DATE_FORMAT($1, '%d-%m-%Y')",
    "/to_char\(([^,]+),\s*'MM'\)/i" => "DATE_FORMAT($1, '%m')",
    "/to_char\(([^,]+),\s*'YYYY'\)/i" => "DATE_FORMAT($1, '%Y')",
    // PostgreSQL casting to numeric
    "/::numeric/i" => "",
    // PostgreSQL concatenation operator
    "/\|\|/i" => ",", // Will need manual fix for CONCAT()
];

$totalFiles = 0;
$totalReplacements = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    $fileReplacements = 0;
    
    foreach ($replacements as $pattern => $replacement) {
        $newContent = preg_replace($pattern, $replacement, $content);
        if ($newContent !== $content) {
            $fileReplacements += substr_count($originalContent, $pattern) - substr_count($newContent, $pattern);
            $content = $newContent;
        }
    }
    
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $totalFiles++;
        $totalReplacements += $fileReplacements;
        echo "Fixed: " . basename($file) . " ($fileReplacements replacements)\n";
    }
}

echo "\nTotal files fixed: $totalFiles\n";
echo "Total replacements: $totalReplacements\n";

