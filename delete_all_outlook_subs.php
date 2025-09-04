<?php
      // delete_all_outlook_subs.php
      // Este script es temporal y debe ser eliminado después de su uso.

      require_once __DIR__ . '/api/helpers/outlook_auth_helper.php';
      require_once __DIR__ . '/config/db.php';

      $responsableId = 2; // El ID del responsable cuyas suscripciones quieres eliminar

      try {
          $db = DB::getDB();

          // Obtener el access token del responsable
          $stmt = $db->prepare("SELECT outlook_access_token FROM responsable WHERE id = :id");
          $stmt->execute([':id' => $responsableId]);
          $respData = $stmt->fetch(PDO::FETCH_ASSOC);
          $accessToken = $respData['outlook_access_token'] ?? null;

          if ($accessToken) {
              // Primero, listar todas las suscripciones activas
              $graphUrlList = "https://graph.microsoft.com/v1.0/subscriptions";
              $chList = curl_init($graphUrlList);
              curl_setopt($chList, CURLOPT_RETURNTRANSFER, true);
              curl_setopt($chList, CURLOPT_HTTPHEADER, [
                  'Authorization: Bearer ' . $accessToken,
                  'Content-Type: application/json'
              ]);
              $responseList = curl_exec($chList);
              $httpCodeList = curl_getinfo($chList, CURLINFO_HTTP_CODE);
              curl_close($chList);

              $responseDataList = json_decode($responseList, true);

              if ($httpCodeList === 200 && isset($responseDataList['value']) && is_array($responseDataList['value'])) {
                  echo "Intentando eliminar " . count($responseDataList['value']) . " suscripciones...\n";
                  foreach ($responseDataList['value'] as $subscription) {
                      $subId = $subscription['id'];
                      echo "  Eliminando suscripción ID: $subId...\n";
                      if (deleteOutlookWebhookSubscription($subId, $accessToken)) {
                          echo "  Suscripción $subId eliminada correctamente.\n";
                      } else {
                          echo "  Error al eliminar suscripción $subId. Revisa los logs del servidor.\n";
                      }
                  }
                  echo "Proceso de eliminación completado.\n";
              } else {
                  echo "No se encontraron suscripciones activas para eliminar o error al listar (HTTP $httpCodeList).\n";
              }

              // Después de intentar eliminar todas, limpiar la DB local
              $stmtClear = $db->prepare("UPDATE responsable SET outlook_subscription_id = NULL, outlook_client_state = NULL WHERE id = :id");
              $stmtClear->execute([':id' => $responsableId]);
              echo "Datos de suscripción locales limpiados en la base de datos para el responsable $responsableId.\n";

          } else {
              echo "No se encontró token de acceso para el responsable $responsableId. Asegúrate de que el responsable haya conectado su calendario Outlook.\n";
          }
      } catch (Throwable $e) {
          echo "Error fatal: " . $e->getMessage() . "\n";
          error_log("Error fatal en delete_all_outlook_subs.php: " . $e->getMessage());
      }
      ?>