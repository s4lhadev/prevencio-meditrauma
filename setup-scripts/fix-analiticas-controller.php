<?php
$file = __DIR__ . '/../current/src/Controller/AnaliticasController.php';
$content = file_get_contents($file);

// Reemplazar la coma incorrecta con &&
$content = str_replace(
    "if (\$parts[1] != '.' , \$parts[1] != '..')",
    "if (\$parts[1] != '.' && \$parts[1] != '..')",
    $content
);

file_put_contents($file, $content);
echo "Fixed AnaliticasController.php\n";

