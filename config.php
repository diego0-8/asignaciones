<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'citas2');

// Habilitar reporte de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'error.log');

// Configuraciones para archivos grandes (hasta 1 millón de clientes)
ini_set('upload_max_filesize', '1G');           // Aumentado a 1GB
ini_set('post_max_size', '1G');                 // Aumentado a 1GB
ini_set('max_execution_time', 1800);            // Aumentado a 30 minutos
ini_set('memory_limit', '2G');                  // Aumentado a 2GB
ini_set('max_input_vars', 1000000);            // Mantenido
ini_set('max_file_uploads', 100);               // Permitir múltiples archivos
ini_set('file_uploads', 1);                     // Habilitar uploads
ini_set('upload_tmp_dir', sys_get_temp_dir());  // Directorio temporal del sistema

?>
