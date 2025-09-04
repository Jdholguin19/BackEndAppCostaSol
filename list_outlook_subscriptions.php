<?php
  // list_outlook_subscriptions.php
  // Este script es temporal y debe ser eliminado después de su uso.

  require_once __DIR__ . '/config/db.php'; // Asumiendo que la clase DB está en config/db.php

  $responsableId = 2; // El ID del responsable cuyas suscripciones quieres listar

  try {
      $db = DB::getDB();

      // Obtener el access token del responsable
      $stmt = $db->prepare("SELECT outlook_access_token FROM responsable WHERE id = :id");
      $stmt->execute([':id' => $responsableId]);
      $respData = $stmt->fetch(PDO::FETCH_ASSOC);
      $accessToken = $respData['outlook_access_token'] ?? null;

      if ($accessToken) {
          $graphUrl = "https://graph.microsoft.com/v1.0/subscriptions";

          $ch = curl_init($graphUrl);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_HTTPHEADER, [
              'Authorization: Bearer ' . $accessToken,
              'Content-Type: application/json'
          ]);

          $response = curl_exec($ch);
          $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          curl_close($ch);

          $responseData = json_decode($response, true);

          if ($httpCode === 200) {
              echo "Suscripciones activas para el responsable $responsableId:\n";
              if (isset($responseData['value']) && is_array($responseData['value'])) {
                  foreach ($responseData['value'] as $subscription) {
                      echo "  ID: " . $subscription['id'] . "\n";
                      echo "  ClientState: " . $subscription['clientState'] . "\n";
                      echo "  Expiration: " . $subscription['expirationDateTime'] . "\n";
                      echo "  Resource: " . $subscription['resource'] . "\n";
                      echo "  ChangeType: " . $subscription['changeType'] . "\n";
                      echo "  ---\n";
                  }
              } else {
                  echo "No se encontraron suscripciones activas.\n";
              }
          } else {
              echo "Error al listar suscripciones (HTTP $httpCode): " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
          }
      } else {
          echo "No se encontró token de acceso para el responsable $responsableId. Asegúrate de que el responsable haya conectado su calendario Outlook.\n";
      }
  } catch (Throwable $e) {
      echo "Error fatal: " . $e->getMessage() . "\n";
      error_log("Error fatal en list_outlook_subscriptions.php: " . $e->getMessage());
  }
  ?>