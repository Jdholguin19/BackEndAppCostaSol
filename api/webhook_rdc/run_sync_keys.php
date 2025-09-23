<?php
/**
 * Script para ejecutar sincronización de keys desde Kiss Flow
 * Se ejecuta automáticamente después de webhooks para llenar keys faltantes
 */

// Incluir el script de sincronización de keys
require_once __DIR__ . '/sync_keys_from_kf.php';
