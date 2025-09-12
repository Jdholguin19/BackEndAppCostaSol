<?php
// /kiss_flow/config/db.php
declare(strict_types=1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "portalao_BDU_KissFlow"; // La base de datos donde creaste la tabla kissflow_emision_pagos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión a la base de datos fallida: " . $conn->connect_error);
}

// Opcional: Establecer el charset a utf8mb4 para soportar todos los caracteres
$conn->set_charset("utf8mb4");

?>