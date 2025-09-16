<?php
      // db.php - Configuración de la base de datos para el proyecto Gantt Standalone

      define('DB_HOST', 'localhost');
      define('DB_USER', 'portalao_gcoello');
      define('DB_PASS', 'guiCTV321!');
      define('DB_NAME', 'portalao_appCostaSol'); // Asegúrate de que coincida con el nombre de la base de datos que creaste

      $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

      if ($conn->connect_error) {
          die("Conexión fallida: " . $conn->connect_error);
      }

      // Establecer el conjunto de caracteres a utf8
      $conn->set_charset("utf8");

      ?>