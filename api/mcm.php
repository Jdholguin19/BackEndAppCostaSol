<?php
// Reemplaza 'ruta/a/tu/manual.pdf' con la ruta real a tu archivo PDF
$file = '../Manual_de_uso.pdf';

if (file_exists($file)) {
    header('Content-type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($file) . '"');
    header('Content-Transfer-Encoding: binary');
    header('Accept-Ranges: bytes');
    readfile($file);
} else {
    // Manejar el caso en que el archivo no existe
    http_response_code(404);
    echo "El archivo del manual no fue encontrado.";
}
?>