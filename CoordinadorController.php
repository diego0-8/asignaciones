<?php
/**
 * Controlador del Coordinador
 * Maneja todas las operaciones relacionadas con el rol de coordinador
 */

require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/BaseDatosController.php';

class CoordinadorController {
    private $db;
    private $baseDatosController;
    
    public function __construct() {
        $this->db = new Database();
        $this->baseDatosController = new BaseDatosController();
    }
    
    /**
     * Dashboard principal del coordinador
     */
    public function dashboard() {
        // Verificar sesión
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            header('Location: index.php?action=login');
            exit;
        }
        
        // Obtener estadísticas generales
        $estadisticasGenerales = $this->getEstadisticasCoordinador($_SESSION['user_id']);
        
        // Obtener información detallada de asesores
        $estadisticas = $this->getEstadisticasDetalladasAsesores($_SESSION['user_id']);
        
        // Obtener totales
        $totalClientesCargados = $this->getTotalClientesCargados($_SESSION['user_id']);
        $totalClientesAsignados = $this->getTotalClientesAsignados($_SESSION['user_id']);
        $totalClientesDisponibles = $this->getTotalClientesDisponibles($_SESSION['user_id']);
        
        // Incluir la vista
        include __DIR__ . '/../views/coordinador_dashboard.php';
    }
    
    /**
     * Gestión de bases de datos de clientes
     */
    public function gestionarBasesDatos() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            header('Location: index.php?action=login');
            exit;
        }
        
        $coordinadorId = $_SESSION['user_id'];
        $basesDatos = $this->baseDatosController->getBasesDatos($coordinadorId);
        $asesores = $this->baseDatosController->getAsesoresDisponibles($coordinadorId);
        
        include __DIR__ . '/../views/coordinador_gestionar_bases.php';
    }
    
    /**
     * Cargar archivo de clientes
     */
    public function cargarArchivo() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            header('Location: index.php?action=login');
            exit;
        }
        
        $coordinadorId = $_SESSION['user_id'];
        $basesDatos = $this->baseDatosController->getBasesDatos($coordinadorId);
        $asesores = $this->baseDatosController->getAsesoresDisponibles($coordinadorId);
        
        include __DIR__ . '/../views/coordinador_cargar_archivo.php';
    }
    
    /**
     * Crear nueva base de datos
     */
    public function crearBaseDatos() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'Método no permitido'], 405);
            return;
        }
        
        $coordinadorId = $_SESSION['user_id'];
        $nombreBase = trim($_POST['nombre_base'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $asesorId = $_POST['asesor_id'] ?? null;
        
        if (empty($nombreBase)) {
            $this->jsonResponse(['success' => false, 'error' => 'El nombre de la base es obligatorio'], 400);
            return;
        }
        
        try {
            // Verificar que el nombre sea único
            if (!$this->baseDatosController->verificarNombreUnico($nombreBase, $coordinadorId)) {
                $this->jsonResponse(['success' => false, 'error' => 'Ya existe una base de datos con ese nombre'], 400);
                return;
            }
            
            // Crear base de datos
            $data = [
                'nombre_base' => $nombreBase,
                'descripcion' => $descripcion,
                'coordinador_id' => $coordinadorId,
                'asesor_id' => $asesorId
            ];
            
            $baseId = $this->baseDatosController->crearBaseDatos($data);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Base de datos creada exitosamente',
                'base_id' => $baseId
            ]);
            
        } catch (Exception $e) {
            error_log("Error en crearBaseDatos: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'Error interno del servidor'], 500);
        }
    }
    
    /**
     * Asignar asesor a base de datos
     */
    public function asignarAsesorBaseDatos() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'Método no permitido'], 405);
            return;
        }
        
        $coordinadorId = $_SESSION['user_id'];
        $baseDatosId = $_POST['base_datos_id'] ?? null;
        $asesorId = $_POST['asesor_id'] ?? null;
        $asignarClientes = $_POST['asignar_clientes'] ?? '0';
        
        if (!$baseDatosId) {
            $this->jsonResponse(['success' => false, 'error' => 'ID de base de datos requerido'], 400);
            return;
        }
        
        try {
            if ($asesorId) {
                // Verificar que la base pertenece al coordinador
                $base = $this->baseDatosController->getBaseDatosById($baseDatosId, $coordinadorId);
                if (!$base) {
                    $this->jsonResponse(['success' => false, 'error' => 'Base de datos no encontrada'], 404);
                    return;
                }
                
                $this->baseDatosController->asignarAsesor($baseDatosId, $asesorId, $coordinadorId);
                
                // Si se solicita, asignar todos los clientes de la base al asesor
                if ($asignarClientes === '1') {
                    $this->asignarClientesDeBase($baseDatosId, $asesorId, $coordinadorId);
                }
                
                $message = 'Asesor asignado exitosamente';
            } else {
                $this->baseDatosController->liberarAsesor($baseDatosId, $coordinadorId);
                $message = 'Asesor liberado exitosamente';
            }
            
            $this->jsonResponse([
                'success' => true,
                'message' => $message
            ]);
            
        } catch (Exception $e) {
            error_log("Error en asignarAsesorBaseDatos: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Obtener bases de datos del coordinador
     */
    public function getBasesDatos() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 401);
            return;
        }
        
        $coordinadorId = $_SESSION['user_id'];
        $basesDatos = $this->baseDatosController->getBasesDatos($coordinadorId);
        
        $this->jsonResponse([
            'success' => true,
            'bases_datos' => $basesDatos
        ]);
    }
    
    /**
     * Editar base de datos
     */
    public function editarBaseDatos() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'Método no permitido'], 405);
            return;
        }
        
        $coordinadorId = $_SESSION['user_id'];
        $baseDatosId = $_POST['base_datos_id'] ?? null;
        $nombreBase = trim($_POST['nombre_base'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        
        if (!$baseDatosId || empty($nombreBase)) {
            $this->jsonResponse(['success' => false, 'error' => 'Datos requeridos faltantes'], 400);
            return;
        }
        
        try {
            // Verificar que el nombre sea único (excluyendo la base actual)
            if (!$this->baseDatosController->verificarNombreUnico($nombreBase, $coordinadorId, $baseDatosId)) {
                $this->jsonResponse(['success' => false, 'error' => 'Ya existe una base de datos con ese nombre'], 400);
                return;
            }
            
            // Actualizar base de datos
            $data = [
                'nombre_base' => $nombreBase,
                'descripcion' => $descripcion
            ];
            
            $this->baseDatosController->actualizarBaseDatos($baseDatosId, $data);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Base de datos actualizada exitosamente'
            ]);
            
        } catch (Exception $e) {
            error_log("Error en editarBaseDatos: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'Error interno del servidor'], 500);
        }
    }
    
    /**
     * Cambiar estado de base de datos
     */
    public function cambiarEstadoBaseDatos() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'Método no permitido'], 405);
            return;
        }
        
        $coordinadorId = $_SESSION['user_id'];
        $baseDatosId = $_POST['base_datos_id'] ?? null;
        $estado = $_POST['estado'] ?? null;
        
        if (!$baseDatosId || !$estado) {
            $this->jsonResponse(['success' => false, 'error' => 'Datos requeridos faltantes'], 400);
            return;
        }
        
        try {
            $this->baseDatosController->cambiarEstado($baseDatosId, $estado, $coordinadorId);
            
            $mensaje = $estado === 'Activa' ? 'Base de datos activada' : 'Base de datos desactivada';
            
            $this->jsonResponse([
                'success' => true,
                'message' => $mensaje
            ]);
            
        } catch (Exception $e) {
            error_log("Error en cambiarEstadoBaseDatos: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'Error interno del servidor'], 500);
        }
    }
    
    /**
     * Procesar archivo CSV de clientes
     */
    public function procesarArchivo() {
        // Log de inicio del proceso
        error_log("Iniciando procesamiento de archivo para coordinador: " . ($_SESSION['user_id'] ?? 'NO_SESSION'));
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            error_log("Error de autorización en procesarArchivo");
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("Método no permitido en procesarArchivo: " . $_SERVER['REQUEST_METHOD']);
            $this->jsonResponse(['success' => false, 'error' => 'Método no permitido'], 405);
            return;
        }
        
        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            $errorCode = $_FILES['archivo']['error'] ?? 'NO_FILE';
            error_log("Error al subir archivo: " . $errorCode);
            $this->jsonResponse(['success' => false, 'error' => 'Error al subir archivo: ' . $errorCode], 400);
            return;
        }
        
        $archivo = $_FILES['archivo'];
        $coordinadorId = $_SESSION['user_id'];
        $baseDatosId = $_POST['base_datos_id'] ?? null;
        $crearNuevaBase = $_POST['crear_nueva_base'] ?? false;
        $nombreNuevaBase = trim($_POST['nombre_nueva_base'] ?? '');
        
        // Verificar tipo de archivo
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if ($extension !== 'csv') {
            $this->jsonResponse(['success' => false, 'error' => 'Solo se permiten archivos CSV'], 400);
            return;
        }
        
        // Verificar tamaño (máximo 500MB)
        if ($archivo['size'] > 500 * 1024 * 1024) {
            $this->jsonResponse(['success' => false, 'error' => 'El archivo es demasiado grande. Máximo 500MB'], 400);
            return;
        }
        
        // Determinar base de datos destino
        if ($crearNuevaBase) {
            if (empty($nombreNuevaBase)) {
                $this->jsonResponse(['success' => false, 'error' => 'El nombre de la nueva base es obligatorio'], 400);
                return;
            }
            
            // Verificar que el nombre sea único
            if (!$this->baseDatosController->verificarNombreUnico($nombreNuevaBase, $coordinadorId)) {
                $this->jsonResponse(['success' => false, 'error' => 'Ya existe una base de datos con ese nombre'], 400);
                return;
            }
            
            // Crear nueva base de datos
            $data = [
                'nombre_base' => $nombreNuevaBase,
                'descripcion' => 'Creada desde carga de archivo CSV',
                'coordinador_id' => $coordinadorId,
                'asesor_id' => null
            ];
            
            $baseDatosId = $this->baseDatosController->crearBaseDatos($data);
        } else {
            if (!$baseDatosId) {
                $this->jsonResponse(['success' => false, 'error' => 'Debe seleccionar una base de datos existente'], 400);
                return;
            }
            
            // Verificar que la base de datos pertenezca al coordinador
            $baseDatos = $this->baseDatosController->getBaseDatosById($baseDatosId, $coordinadorId);
            if (!$baseDatos) {
                $this->jsonResponse(['success' => false, 'error' => 'Base de datos no encontrada'], 400);
                return;
            }
        }
        
        try {
            error_log("Iniciando procesamiento de archivo grande: " . number_format($archivo['size']) . " bytes");
            
            // Para archivos grandes, usar lotes más pequeños y commit frecuente
            $batchSize = 500; // Reducido para archivos grandes
            $commitFrequency = 2000; // Hacer commit cada 2000 registros
            
            $this->db->beginTransaction();
            
            $clientesProcesados = 0;
            $errores = [];
            $batch = [];
            $totalLineas = 0;
            
            // Verificar que el archivo temporal existe y es legible
            if (!file_exists($archivo['tmp_name'])) {
                throw new Exception('Archivo temporal no encontrado: ' . $archivo['tmp_name']);
            }
            
            if (!is_readable($archivo['tmp_name'])) {
                throw new Exception('Archivo temporal no es legible: ' . $archivo['tmp_name']);
            }
            
            error_log("Abriendo archivo CSV: " . $archivo['tmp_name']);
            // Abrir archivo CSV
            $handle = fopen($archivo['tmp_name'], 'r');
            if (!$handle) {
                throw new Exception('No se pudo abrir el archivo: ' . error_get_last()['message'] ?? 'Error desconocido');
            }
            
            // Leer encabezados
            error_log("Leyendo encabezados del CSV");
            $encabezados = fgetcsv($handle);
            if (!$encabezados) {
                throw new Exception('Archivo CSV vacío o inválido');
            }
            
            error_log("Encabezados encontrados: " . implode(', ', $encabezados));
            
            // Mapear columnas (buscar por nombre flexible, ignorando mayúsculas, acentos y espacios)
            $columnas = array_map(function($col) {
                // Normalizar: minúsculas, sin acentos, sin espacios extra
                $col = strtolower(trim($col));
                $col = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $col);
                $col = preg_replace('/\s+/', '', $col); // Eliminar espacios
                return $col;
            }, $encabezados);
            
            // Buscar columnas con nombres flexibles
            $nombreIndex = false;
            $cedulaIndex = false;
            $telefonoIndex = false;
            
            foreach ($columnas as $index => $col) {
                if (strpos($col, 'nombre') !== false) $nombreIndex = $index;
                if (strpos($col, 'cedula') !== false) $cedulaIndex = $index;
                if (strpos($col, 'telefono') !== false) $telefonoIndex = $index;
            }
            
            error_log("Índices de columnas - Nombre: $nombreIndex, Cédula: $cedulaIndex, Teléfono: $telefonoIndex");
            
            if ($nombreIndex === false || $cedulaIndex === false || $telefonoIndex === false) {
                throw new Exception('El archivo debe contener las columnas: nombre, cedula, telefono. Columnas encontradas: ' . implode(', ', $encabezados));
            }
            
            // Buscar columnas adicionales (opcionales)
            $emailIndex = array_search('email', $columnas);
            $ciudadIndex = array_search('ciudad', $columnas);
            
            error_log("Columnas adicionales - Email: $emailIndex, Ciudad: $ciudadIndex");
            
            // Mostrar información sobre las columnas encontradas
            error_log("Columnas obligatorias encontradas:");
            error_log("- Nombre (índice $nombreIndex): " . $encabezados[$nombreIndex]);
            error_log("- Cédula (índice $cedulaIndex): " . $encabezados[$cedulaIndex]);
            error_log("- Teléfono (índice $telefonoIndex): " . $encabezados[$telefonoIndex]);
            
            if ($emailIndex !== false) {
                error_log("- Email (índice $emailIndex): " . $encabezados[$emailIndex]);
            }
            if ($ciudadIndex !== false) {
                error_log("- Ciudad (índice $ciudadIndex): " . $encabezados[$ciudadIndex]);
            }
            
            // Procesar archivo línea por línea
            $linea = 2; // Empezar desde la línea 2 (después de encabezados)
            while (($datos = fgetcsv($handle)) !== false) {
                try {
                    $nombre = trim($datos[$nombreIndex] ?? '');
                    $cedula = trim($datos[$cedulaIndex] ?? '');
                    $telefono = trim($datos[$telefonoIndex] ?? '');
                    
                    // Validaciones básicas
                    if (empty($nombre) || empty($cedula)) {
                        $errores[] = "Línea $linea: Nombre y cédula son obligatorios";
                        $linea++;
                        continue;
                    }
                    
                    // Verificar si la cédula ya existe (a nivel global)
                    $sql = "SELECT id FROM clientes WHERE cedula = ?";
                    $existente = $this->db->fetch($sql, [$cedula]);
                    
                    if ($existente) {
                        $errores[] = "Línea $linea: Cédula $cedula ya existe en el sistema";
                        $linea++;
                        continue;
                    }
                    
                    // Obtener campos opcionales si existen
                    $email = ($emailIndex !== false) ? trim($datos[$emailIndex] ?? '') : '';
                    $ciudad = ($ciudadIndex !== false) ? trim($datos[$ciudadIndex] ?? '') : '';
                    
                    // Agregar a lote
                    $cliente = [
                        'nombre_completo' => $nombre,
                        'cedula' => $cedula,
                        'telefono' => $telefono,
                        'coordinador_id' => $coordinadorId,
                        'base_datos_id' => $baseDatosId,
                        'estado_gestion' => 'Disponible',
                        'fecha_creacion' => date('Y-m-d H:i:s')
                    ];
                    
                    // Agregar campos opcionales solo si tienen valor
                    if (!empty($email)) {
                        $cliente['email'] = $email;
                    }
                    if (!empty($ciudad)) {
                        $cliente['ciudad'] = $ciudad;
                    }
                    
                    $batch[] = $cliente;
                    
                    // Procesar lote cuando alcance el tamaño
                    if (count($batch) >= $batchSize) {
                        $this->procesarLoteClientes($batch);
                        $clientesProcesados += count($batch);
                        $batch = [];
                        
                        // Para archivos grandes, hacer commit frecuente
                        if ($clientesProcesados % $commitFrequency === 0) {
                            $this->db->commit();
                            $this->db->beginTransaction();
                            error_log("Commit intermedio realizado: $clientesProcesados clientes procesados");
                        }
                        
                        // Liberar memoria
                        gc_collect_cycles();
                        
                        // Mostrar progreso cada 1000 registros
                        if ($clientesProcesados % 1000 === 0) {
                            error_log("Progreso: $clientesProcesados clientes procesados");
                        }
                    }
                    
                } catch (Exception $e) {
                    $errores[] = "Línea $linea: " . $e->getMessage();
                }
                
                $linea++;
            }
            
            // Procesar lote restante
            if (!empty($batch)) {
                $this->procesarLoteClientes($batch);
                $clientesProcesados += count($batch);
            }
            
            fclose($handle);
            
            // Confirmar transacción
            $this->db->commit();
            
            $mensaje = "Se procesaron exitosamente $clientesProcesados cliente(s)";
            if (!empty($errores)) {
                $mensaje .= ". Errores: " . count($errores);
            }
            
            $this->jsonResponse([
                'success' => true,
                'message' => $mensaje,
                'clientes_procesados' => $clientesProcesados,
                'errores' => $errores
            ]);
            
        } catch (Exception $e) {
            error_log("Error en procesarArchivo: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            try {
                $this->db->rollback();
                error_log("Rollback de transacción completado");
            } catch (Exception $rollbackError) {
                error_log("Error en rollback: " . $rollbackError->getMessage());
            }
            
            if (isset($handle)) {
                fclose($handle);
                error_log("Archivo CSV cerrado");
            }
            
            $this->jsonResponse(['success' => false, 'error' => 'Error interno: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Procesar lote de clientes para optimizar memoria
     */
    private function procesarLoteClientes($batch) {
        if (empty($batch)) return;
        
        $placeholders = str_repeat('(?, ?, ?, ?, ?, ?, ?),', count($batch));
        $placeholders = rtrim($placeholders, ',');
        
        $sql = "INSERT INTO clientes (nombre_completo, cedula, telefono, coordinador_id, base_datos_id, estado_gestion, fecha_creacion) VALUES $placeholders";
        
        $valores = [];
        foreach ($batch as $cliente) {
            $valores[] = $cliente['nombre_completo'];
            $valores[] = $cliente['cedula'];
            $valores[] = $cliente['telefono'];
            $valores[] = $cliente['coordinador_id'];
            $valores[] = $cliente['base_datos_id'];
            $valores[] = $cliente['estado_gestion'];
            $valores[] = $cliente['fecha_creacion'];
        }
        
        $this->db->query($sql, $valores);
    }
    
    /**
     * Gestionar tareas
     */
    public function tareas() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            header('Location: index.php?action=login');
            exit;
        }
        
        // Obtener estadísticas y datos necesarios para la vista
        $totalClientesCargados = $this->getTotalClientesCargados($_SESSION['user_id']);
        $totalClientesAsignados = $this->getTotalClientesAsignados($_SESSION['user_id']);
        $totalClientesDisponibles = $this->getTotalClientesDisponibles($_SESSION['user_id']);
        
        // Obtener asesores del coordinador
        $asesores = $this->getAsesoresDelCoordinador($_SESSION['user_id']);
        
        // Obtener clientes disponibles
        $clientesDisponibles = $this->getClientesDisponibles($_SESSION['user_id']);
        
        include __DIR__ . '/../views/coordinador_tareas_mejorada.php';
    }
    
    /**
     * Transferir clientes
     */
    public function transferirClientes() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            header('Location: index.php?action=login');
            exit;
        }
        
        // Obtener asesores del coordinador
        $asesores = $this->getAsesoresDelCoordinador($_SESSION['user_id']);
        
        // Obtener estadísticas para mostrar
        $totalClientesCargados = $this->getTotalClientesCargados($_SESSION['user_id']);
        $totalClientesAsignados = $this->getTotalClientesAsignados($_SESSION['user_id']);
        $totalClientesDisponibles = $this->getTotalClientesDisponibles($_SESSION['user_id']);
        
        include __DIR__ . '/../views/coordinador_transferir_clientes.php';
    }
    
    /**
     * Descargar archivos
     */
    public function descargarArchivos() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            header('Location: index.php?action=login');
            exit;
        }
        
        include __DIR__ . '/../views/coordinador_descargar_archivos.php';
    }
    
    /**
     * Obtener detalles completos de un asesor
     */
    public function obtenerDetallesAsesor() {
        // Verificar sesión
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 401);
            return;
        }
        
        $coordinadorId = $_SESSION['user_id'];
        $asesorId = $_GET['asesor_id'] ?? null;
        
        if (!$asesorId) {
            $this->jsonResponse(['success' => false, 'error' => 'ID de asesor requerido'], 400);
            return;
        }
        
        try {
            error_log("=== INICIO obtenerDetallesAsesor ===");
            error_log("Asesor ID: " . $asesorId);
            error_log("Coordinador ID: " . $coordinadorId);
            
            // Verificar que el asesor pertenece al coordinador
            $sql = "SELECT u.id, u.nombre_completo, u.usuario, u.estado
                    FROM usuarios u
                    WHERE u.id = ? AND u.coordinador_id = ? AND u.rol = 'asesor'";
            
            error_log("SQL Asesor: " . $sql);
            error_log("Parámetros: [" . $asesorId . ", " . $coordinadorId . "]");
            
            $asesor = $this->db->fetch($sql, [$asesorId, $coordinadorId]);
            
            error_log("Resultado asesor: " . print_r($asesor, true));
            
            if (!$asesor) {
                error_log("Asesor no encontrado o no autorizado");
                $this->jsonResponse(['success' => false, 'error' => 'Asesor no encontrado o no autorizado'], 404);
                return;
            }
            
            // Obtener estadísticas del asesor
            error_log("Obteniendo estadísticas del asesor...");
            $estadisticas = $this->getEstadisticasAsesor($asesorId);
            error_log("Estadísticas obtenidas: " . print_r($estadisticas, true));
            
            // Obtener clientes del asesor con información detallada
            error_log("Obteniendo clientes del asesor...");
            $clientes = $this->getClientesDelAsesor($asesorId);
            error_log("Clientes obtenidos: " . print_r($clientes, true));
            
            // Combinar información
            $asesorCompleto = array_merge($asesor, $estadisticas);
            error_log("Asesor completo: " . print_r($asesorCompleto, true));
            
            $this->jsonResponse([
                'success' => true,
                'asesor' => $asesorCompleto,
                'clientes' => $clientes
            ]);
            
        } catch (Exception $e) {
            error_log("Error en obtenerDetallesAsesor: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->jsonResponse(['success' => false, 'error' => 'Error interno del servidor'], 500);
        }
    }
    
    /**
     * Obtener asesores disponibles para transferencia
     */
    public function obtenerAsesoresDisponibles() {
        // Verificar sesión
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 401);
            return;
        }
        
        $coordinadorId = $_SESSION['user_id'];
        
        try {
            // Obtener asesores del coordinador (excluyendo el asesor actual si se especifica)
            $sql = "SELECT u.id, u.nombre_completo, u.estado
                    FROM usuarios u
                    WHERE u.coordinador_id = ? AND u.rol = 'asesor' AND u.estado = 'Activo'
                    ORDER BY u.nombre_completo";
            
            $asesores = $this->db->fetchAll($sql, [$coordinadorId]);
            
            $this->jsonResponse([
                'success' => true,
                'asesores' => $asesores
            ]);
            
        } catch (Exception $e) {
            error_log("Error en obtenerAsesoresDisponibles: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'Error interno del servidor'], 500);
        }
    }
    
    /**
     * Transferir cliente a otro asesor
     */
    public function transferirCliente() {
        // Verificar sesión
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 401);
            return;
        }
        
        $coordinadorId = $_SESSION['user_id'];
        $clienteId = $_POST['cliente_id'] ?? null;
        $nuevoAsesorId = $_POST['nuevo_asesor_id'] ?? null;
        $motivo = $_POST['motivo'] ?? '';
        
        if (!$clienteId || !$nuevoAsesorId) {
            $this->jsonResponse(['success' => false, 'error' => 'Datos incompletos'], 400);
            return;
        }
        
        try {
            // Verificar que el cliente pertenece al coordinador
            $sql = "SELECT c.id, c.nombre_completo, c.asesor_id
                    FROM clientes c
                    INNER JOIN usuarios u ON c.asesor_id = u.id
                    WHERE c.id = ? AND u.coordinador_id = ?";
            
            $cliente = $this->db->fetch($sql, [$clienteId, $coordinadorId]);
            
            if (!$cliente) {
                $this->jsonResponse(['success' => false, 'error' => 'Cliente no encontrado o no autorizado'], 404);
                return;
            }
            
            // Verificar que el nuevo asesor pertenece al coordinador
            $sql = "SELECT u.id, u.nombre_completo
                    FROM usuarios u
                    WHERE u.id = ? AND u.coordinador_id = ? AND u.rol = 'asesor'";
            
            $nuevoAsesor = $this->db->fetch($sql, [$nuevoAsesorId, $coordinadorId]);
            
            if (!$nuevoAsesor) {
                $this->jsonResponse(['success' => false, 'error' => 'Nuevo asesor no válido'], 400);
                return;
            }
            
            // Iniciar transacción
            $this->db->beginTransaction();
            
            try {
                // Actualizar asesor del cliente
                $sql = "UPDATE clientes SET asesor_id = ? WHERE id = ?";
                $this->db->query($sql, [$nuevoAsesorId, $clienteId]);
                
                // Registrar la transferencia en historial_asignaciones
                $sql = "INSERT INTO historial_asignaciones (
                    cliente_id, asesor_id, coordinador_id, usuario_admin_id, fecha_asignacion, estado, observaciones
                ) VALUES (?, ?, ?, NULL, NOW(), 'Reasignado', ?)";

                $this->db->query($sql, [
                    $clienteId,
                    $nuevoAsesorId,
                    $coordinadorId,
                    $motivo
                ]);
                
                // Confirmar transacción
                $this->db->commit();
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Cliente transferido exitosamente',
                    'cliente' => $cliente['nombre_completo'],
                    'asesor_anterior' => $cliente['asesor_id'],
                    'asesor_nuevo' => $nuevoAsesor['nombre_completo']
                ]);
                
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("Error en transferirCliente: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'Error interno del servidor'], 500);
        }
    }
    
    /**
     * Cerrar sesión del coordinador
     */
    public function cerrarSesion() {
        session_destroy();
        header('Location: index.php?action=login');
        exit;
    }
    
    /**
     * Liberar un cliente específico
     */
    public function liberarCliente() {
        // Verificar autorización
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 403);
        }
        
        // Verificar método
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'Método no permitido'], 405);
        }
        
        $clienteId = $_POST['cliente_id'] ?? null;
        
        if (!$clienteId) {
            $this->jsonResponse(['success' => false, 'error' => 'ID de cliente requerido']);
        }
        
        try {
            // Liberar cliente (establecer estado_gestion a 'Disponible' y asesor_id a null)
            $sql = "UPDATE clientes SET estado_gestion = 'Disponible', asesor_id = NULL WHERE id = ?";
            $this->db->query($sql, [$clienteId]);
            
            $this->jsonResponse(['success' => true, 'message' => 'Cliente liberado exitosamente']);
            
        } catch (Exception $e) {
            error_log("Error en liberarCliente: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'Error interno del servidor']);
        }
    }
    
    /**
     * Liberar todos los clientes de un asesor
     */
    public function liberarTodosClientesAsesor() {
        // Verificar autorización
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 403);
        }
        
        // Verificar método
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'Método no permitido'], 405);
        }
        
        $asesorId = $_POST['asesor_id'] ?? null;
        
        if (!$asesorId) {
            $this->jsonResponse(['success' => false, 'error' => 'ID de asesor requerido']);
        }
        
        try {
            // Liberar todos los clientes del asesor
            $sql = "UPDATE clientes SET estado_gestion = 'Disponible', asesor_id = NULL WHERE asesor_id = ?";
            $this->db->query($sql, [$asesorId]);
            
            $this->jsonResponse(['success' => true, 'message' => 'Todos los clientes del asesor han sido liberados']);
            
        } catch (Exception $e) {
            error_log("Error en liberarTodosClientesAsesor: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'Error interno del servidor']);
        }
    }
    
    /**
     * Obtener clientes de un asesor específico
     */
    public function getClientesAsesor() {
        // Verificar autorización
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 403);
        }
        
        $asesorId = $_GET['asesor_id'] ?? null;
        
        if (!$asesorId) {
            $this->jsonResponse(['success' => false, 'error' => 'ID de asesor requerido']);
        }
        
        try {
            $sql = "SELECT id, nombre_completo, cedula, telefono, estado_gestion FROM clientes WHERE asesor_id = ?";
            $clientes = $this->db->fetchAll($sql, [$asesorId]);
            
            $this->jsonResponse(['success' => true, 'clientes' => $clientes]);
            
        } catch (Exception $e) {
            error_log("Error en getClientesAsesor: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'Error interno del servidor']);
        }
    }
    
    /**
     * Obtener estadísticas del coordinador
     */
    private function getEstadisticasCoordinador($coordinadorId) {
        $sql = "SELECT 
                    COUNT(DISTINCT c.id) as total_clientes,
                    COUNT(DISTINCT CASE WHEN c.estado_gestion = 'Asignado' THEN c.id END) as clientes_asignados,
                    COUNT(DISTINCT CASE WHEN c.estado_gestion = 'Disponible' THEN c.id END) as clientes_disponibles,
                    COUNT(DISTINCT u.id) as total_asesores
                FROM usuarios u
                LEFT JOIN clientes c ON u.id = c.asesor_id
                WHERE u.coordinador_id = ? AND u.rol = 'asesor' AND u.estado = 'Activo'";
        
        return $this->db->fetch($sql, [$coordinadorId]);
    }
    
    /**
     * Obtener total de clientes cargados por el coordinador
     */
    private function getTotalClientesCargados($coordinadorId) {
        $sql = "SELECT COUNT(*) as total FROM clientes WHERE coordinador_id = ?";
        $result = $this->db->fetch($sql, [$coordinadorId]);
        return $result['total'] ?? 0;
    }
    
    /**
     * Obtener total de clientes asignados
     */
    private function getTotalClientesAsignados($coordinadorId) {
        $sql = "SELECT COUNT(*) as total FROM clientes c 
                INNER JOIN usuarios u ON c.asesor_id = u.id 
                WHERE u.coordinador_id = ? AND c.estado_gestion = 'Asignado'";
        $result = $this->db->fetch($sql, [$coordinadorId]);
        return $result['total'] ?? 0;
    }
    
    /**
     * Obtener total de clientes disponibles
     */
    private function getTotalClientesDisponibles($coordinadorId) {
        $sql = "SELECT COUNT(*) as total FROM clientes WHERE coordinador_id = ? AND estado_gestion = 'Disponible'";
        $result = $this->db->fetch($sql, [$coordinadorId]);
        return $result['total'] ?? 0;
    }
    
    /**
     * Obtener estadísticas detalladas de asesores
     * Lógica corregida:
     * - Total Clientes: Solo clientes asignados actualmente
     * - Llamados: Solo clientes asignados actualmente que han sido llamados
     * - Pendientes: Solo clientes asignados actualmente sin llamadas
     */
    private function getEstadisticasDetalladasAsesores($coordinadorId) {
        $sql = "SELECT 
                    u.id as asesor_id,
                    u.nombre_completo as asesor_nombre,
                    -- Total de clientes (SOLO asignados actualmente)
                    COUNT(DISTINCT c.id) as total_clientes,
                    -- Clientes llamados (SOLO asignados actualmente con historial)
                    COUNT(DISTINCT CASE WHEN hg.cliente_id IS NOT NULL THEN c.id END) as clientes_llamados,
                    -- Clientes pendientes (asignados actualmente sin historial)
                    COUNT(DISTINCT CASE WHEN hg.cliente_id IS NULL THEN c.id END) as clientes_pendientes,
                    -- Porcentaje de progreso basado en clientes asignados actualmente
                    CASE 
                        WHEN COUNT(DISTINCT c.id) > 0 THEN 
                            ROUND((COUNT(DISTINCT CASE WHEN hg.cliente_id IS NOT NULL THEN c.id END) * 100.0 / COUNT(DISTINCT c.id)), 1)
                        ELSE 0 
                    END as porcentaje_progreso
                FROM usuarios u
                LEFT JOIN clientes c ON u.id = c.asesor_id
                LEFT JOIN historial_gestion hg ON c.id = hg.cliente_id AND hg.asesor_id = u.id
                WHERE u.coordinador_id = ? AND u.rol = 'asesor' AND u.estado = 'Activo'
                GROUP BY u.id, u.nombre_completo
                ORDER BY u.nombre_completo";
        
        return $this->db->fetchAll($sql, [$coordinadorId]);
    }
    
    /**
     * Obtener asesores del coordinador
     */
    private function getAsesoresDelCoordinador($coordinadorId) {
        $sql = "SELECT id, nombre_completo FROM usuarios 
                WHERE coordinador_id = ? AND rol = 'asesor' AND estado = 'Activo' 
                ORDER BY nombre_completo";
        return $this->db->fetchAll($sql, [$coordinadorId]);
    }
    
    /**
     * Obtener clientes disponibles del coordinador
     */
    private function getClientesDisponibles($coordinadorId) {
        $sql = "SELECT * FROM clientes 
                WHERE coordinador_id = ? AND estado_gestion = 'Disponible' 
                ORDER BY fecha_creacion DESC";
        return $this->db->fetchAll($sql, [$coordinadorId]);
    }
    
    /**
     * Obtener clientes disponibles para asignación (limitado por cantidad)
     */
    private function getClientesDisponiblesParaAsignacion($coordinadorId, $cantidad) {
        $sql = "SELECT * FROM clientes 
                WHERE coordinador_id = ? AND estado_gestion = 'Disponible' 
                ORDER BY fecha_creacion DESC 
                LIMIT ?";
        return $this->db->fetchAll($sql, [$coordinadorId, $cantidad]);
    }
    
    /**
     * Asignar clientes a un asesor
     */
    public function asignarClientes() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'Método no permitido'], 405);
            return;
        }
        
        $asesorId = $_POST['asesor_id'] ?? null;
        $cantidad = $_POST['cantidad'] ?? null;
        
        if (!$asesorId || !$cantidad) {
            $this->jsonResponse(['success' => false, 'error' => 'Datos incompletos'], 400);
            return;
        }
        
        $cantidad = (int)$cantidad;
        if ($cantidad <= 0) {
            $this->jsonResponse(['success' => false, 'error' => 'Cantidad debe ser mayor a 0'], 400);
            return;
        }
        
        // Verificar que el asesor pertenezca al coordinador
        $asesor = $this->verificarAsesorDelCoordinador($asesorId, $_SESSION['user_id']);
        if (!$asesor) {
            $this->jsonResponse(['success' => false, 'error' => 'Asesor no válido'], 400);
            return;
        }
        
        // Verificar que hay suficientes clientes disponibles
        $clientesDisponibles = $this->getClientesDisponiblesParaAsignacion($_SESSION['user_id'], $cantidad);
        if (count($clientesDisponibles) < $cantidad) {
            $this->jsonResponse(['success' => false, 'error' => 'No hay suficientes clientes disponibles'], 400);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            $asignacionesExitosas = 0;
            $errores = [];
            
            // Tomar solo la cantidad solicitada
            $clientesAAsignar = array_slice($clientesDisponibles, 0, $cantidad);
            
            foreach ($clientesAAsignar as $cliente) {
                // Asignar cliente al asesor
                $sql = "UPDATE clientes SET asesor_id = ?, estado_gestion = 'Asignado' WHERE id = ?";
                $resultado = $this->db->query($sql, [$asesorId, $cliente['id']]);
                
                if ($resultado) {
                    $asignacionesExitosas++;
                    
                    // Registrar en historial
                    $this->registrarAsignacion($cliente['id'], $asesorId, $_SESSION['user_id']);
                } else {
                    $errores[] = "Error al asignar cliente ID {$cliente['id']}";
                }
            }
            
            if ($asignacionesExitosas > 0) {
                $this->db->commit();
                
                $mensaje = "Se asignaron exitosamente $asignacionesExitosas cliente(s) al asesor.";
                if (!empty($errores)) {
                    $mensaje .= " Errores: " . implode(', ', $errores);
                }
                
                $this->jsonResponse([
                    'success' => true, 
                    'message' => $mensaje,
                    'asignaciones' => $asignacionesExitosas
                ]);
            } else {
                $this->db->rollback();
                $this->jsonResponse(['success' => false, 'error' => 'No se pudo asignar ningún cliente'], 400);
            }
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error en asignarClientes: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'Error interno del servidor'], 500);
        }
    }
    
    /**
     * Verificar que el asesor pertenezca al coordinador
     */
    private function verificarAsesorDelCoordinador($asesorId, $coordinadorId) {
        $sql = "SELECT id FROM usuarios WHERE id = ? AND coordinador_id = ? AND rol = 'asesor' AND estado = 'Activo'";
        return $this->db->fetch($sql, [$asesorId, $coordinadorId]);
    }
    
    /**
     * Verificar si ya existe una base de datos para el coordinador
     */
    private function verificarBaseExistente($coordinadorId) {
        $sql = "SELECT COUNT(*) as total FROM clientes WHERE coordinador_id = ?";
        $result = $this->db->fetch($sql, [$coordinadorId]);
        return $result['total'] > 0;
    }
    
    /**
     * Obtener información de la base existente
     */
    private function obtenerInfoBaseExistente($coordinadorId) {
        $sql = "SELECT 
                    COUNT(*) as total_clientes,
                    MIN(fecha_creacion) as fecha_primera_carga,
                    MAX(fecha_creacion) as fecha_ultima_carga
                FROM clientes 
                WHERE coordinador_id = ?";
        return $this->db->fetch($sql, [$coordinadorId]);
    }
    
    /**
     * Verificar que el cliente esté disponible y pertenezca al coordinador
     */
    private function verificarClienteDisponible($clienteId, $coordinadorId) {
        $sql = "SELECT id FROM clientes WHERE id = ? AND coordinador_id = ? AND estado_gestion = 'Disponible'";
        return $this->db->fetch($sql, [$clienteId, $coordinadorId]);
    }
    
    /**
     * Registrar asignación en historial
     */
    private function registrarAsignacion($clienteId, $asesorId, $coordinadorId) {
        $sql = "INSERT INTO historial_asignaciones (cliente_id, asesor_id, coordinador_id, fecha_asignacion, estado) 
                VALUES (?, ?, ?, NOW(), 'Asignado')";
        $this->db->query($sql, [$clienteId, $asesorId, $coordinadorId]);
    }
    
    /**
     * Obtener estadísticas específicas de un asesor
     * Lógica corregida para el modal:
     * - Total Clientes: Solo asignados actualmente
     * - Gestionados: Solo asignados actualmente con historial
     * - Pendientes: Solo asignados actualmente sin historial
     */
    private function getEstadisticasAsesor($asesorId) {
        try {
            error_log("=== INICIO getEstadisticasAsesor ===");
            error_log("Asesor ID: " . $asesorId);
            
            // Total de clientes asignados actualmente
            $sql = "SELECT COUNT(*) as total FROM clientes WHERE asesor_id = ?";
            error_log("SQL Total: " . $sql);
            $resultTotal = $this->db->fetch($sql, [$asesorId]);
            $totalClientes = $resultTotal['total'];
            error_log("Total clientes asignados: " . $totalClientes);
            
            // Clientes gestionados (asignados actualmente con historial)
            $sql = "SELECT COUNT(DISTINCT c.id) as total
                    FROM clientes c
                    INNER JOIN historial_gestion hg ON c.id = hg.cliente_id
                    WHERE c.asesor_id = ? AND hg.asesor_id = ?";
            error_log("SQL Gestionados: " . $sql);
            $resultGestionados = $this->db->fetch($sql, [$asesorId, $asesorId]);
            $clientesGestionados = $resultGestionados['total'];
            error_log("Clientes gestionados: " . $clientesGestionados);
            
            // Clientes pendientes (asignados actualmente sin historial)
            $clientesPendientes = $totalClientes - $clientesGestionados;
            error_log("Clientes pendientes: " . $clientesPendientes);
            
            $estadisticas = [
                'total_clientes' => $totalClientes,
                'clientes_gestionados' => $clientesGestionados,
                'clientes_pendientes' => $clientesPendientes
            ];
            
            error_log("Estadísticas finales: " . print_r($estadisticas, true));
            return $estadisticas;
            
        } catch (Exception $e) {
            error_log("Error en getEstadisticasAsesor: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [
                'total_clientes' => 0,
                'clientes_gestionados' => 0,
                'clientes_pendientes' => 0
            ];
        }
    }
    
    /**
     * Obtener clientes de un asesor con información detallada
     * Incluye tanto clientes asignados como clientes con historial de gestión
     */
    private function getClientesDelAsesor($asesorId) {
        try {
            error_log("=== INICIO getClientesDelAsesor ===");
            error_log("Asesor ID: " . $asesorId);
            
            // Consulta mejorada que incluye:
            // 1. Clientes actualmente asignados al asesor
            // 2. Clientes con historial de gestión del asesor (aunque no estén asignados actualmente)
            $sql = "SELECT DISTINCT
                        c.id,
                        c.nombre_completo,
                        c.cedula,
                        c.telefono,
                        c.estado_gestion,
                        c.fecha_asignacion,
                        hg.tipo_gestion as ultima_tipificacion,
                        hg.fecha_gestion as ultima_gestion,
                        hg.observaciones as ultima_observacion,
                        CASE 
                            WHEN c.asesor_id = ? THEN 'Asignado'
                            ELSE 'Con Historial'
                        END as estado_asignacion
                    FROM clientes c
                    LEFT JOIN (
                        SELECT 
                            hg1.cliente_id,
                            hg1.tipo_gestion,
                            hg1.fecha_gestion,
                            hg1.observaciones
                        FROM historial_gestion hg1
                        INNER JOIN (
                            SELECT 
                                cliente_id,
                                MAX(fecha_gestion) as max_fecha
                            FROM historial_gestion
                            GROUP BY cliente_id
                        ) hg2 ON hg1.cliente_id = hg2.cliente_id 
                               AND hg1.fecha_gestion = hg2.max_fecha
                    ) hg ON c.id = hg.cliente_id
                    WHERE c.asesor_id = ? 
                       OR c.id IN (
                           SELECT DISTINCT cliente_id 
                           FROM historial_gestion 
                           WHERE asesor_id = ?
                       )
                    ORDER BY c.fecha_asignacion DESC, hg.fecha_gestion DESC";
            
            error_log("SQL Clientes Mejorado: " . $sql);
            error_log("Parámetros: [" . $asesorId . ", " . $asesorId . ", " . $asesorId . "]");
            
            $clientes = $this->db->fetchAll($sql, [$asesorId, $asesorId, $asesorId]);
            error_log("Clientes obtenidos: " . print_r($clientes, true));
            
            return $clientes;
            
        } catch (Exception $e) {
            error_log("Error en getClientesDelAsesor: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [];
        }
    }
    
    /**
     * Exportar gestión de asesores a CSV
     */
    public function exportarGestionCSV() {
        // Verificar sesión
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'Método no permitido'], 405);
            return;
        }
        
        $coordinadorId = $_SESSION['user_id'];
        $fechaInicio = $_POST['fecha_inicio'] ?? null;
        $fechaFin = $_POST['fecha_fin'] ?? null;
        
        if (!$fechaInicio || !$fechaFin) {
            $this->jsonResponse(['success' => false, 'error' => 'Fechas requeridas'], 400);
            return;
        }
        
        try {
            // Obtener TODAS las gestiones de asesores en el rango de fechas
            // Incluyendo múltiples gestiones por cliente
            $sql = "SELECT 
                        u.nombre_completo as asesor,
                        c.nombre_completo as cliente,
                        c.cedula,
                        c.telefono,
                        c.email,
                        c.ciudad,
                        COALESCE(bd.nombre_base, 'Sin Base de Datos') as base_datos,
                        'gestionado' AS tipo_gestion_export,
                        hg.tipo_contacto,
                        CASE 
                            WHEN hg.tipo_contacto = 'no_contactado' THEN 
                                CASE 
                                    WHEN hg.observaciones LIKE '%buzon%' OR hg.observaciones LIKE '%buzón%' THEN 'Buzón de voz'
                                    WHEN hg.observaciones LIKE '%no contest%' OR hg.observaciones LIKE '%no contesta%' OR hg.observaciones LIKE '%no contesto%' THEN 'No contesta'
                                    WHEN hg.observaciones LIKE '%ocupad%' THEN 'Ocupado'
                                    WHEN hg.observaciones LIKE '%fuera de servicio%' OR hg.observaciones LIKE '%fuera servicio%' THEN 'Fuera de servicio'
                                    ELSE 'No Contactado'
                                END
                            ELSE 
                                CASE 
                                    WHEN hg.tipo_gestion = 'asignacion_cita' THEN 'Cita Asignada'
                                    WHEN hg.tipo_gestion = 'volver_llamar' THEN 'Volver a Llamar'
                                    WHEN hg.tipo_gestion = 'fuera_ciudad' THEN 'Fuera de Ciudad'
                                    WHEN hg.tipo_gestion = 'no_interesa' THEN 'No Interesa'
                                    WHEN hg.tipo_gestion = 'contactado' THEN 'Contactado'
                                    ELSE hg.resultado
                                END
                        END AS resultado_export,
                        hg.observaciones,
                        hg.fecha_gestion,
                        hg.fecha_proximo_contacto,
                        hg.hora_proximo_contacto,
                        c.estado_gestion,
                        c.fecha_asignacion,
                        c.fecha_creacion
                    FROM historial_gestion hg
                    INNER JOIN usuarios u ON hg.asesor_id = u.id
                    INNER JOIN clientes c ON hg.cliente_id = c.id
                    LEFT JOIN base_datos_clientes bd ON c.base_datos_id = bd.id
                    WHERE u.coordinador_id = ? 
                      AND u.rol = 'asesor'
                      AND DATE(hg.fecha_gestion) BETWEEN ? AND ?
                    ORDER BY hg.fecha_gestion DESC, u.nombre_completo, c.nombre_completo";
            
            $datos = $this->db->fetchAll($sql, [$coordinadorId, $fechaInicio, $fechaFin]);
            
            if (empty($datos)) {
                // Redirigir a la vista con mensaje de error en lugar de JSON
                header('Location: index.php?action=coordinador_descargar_archivos&error=no_data&fecha_inicio=' . urlencode($fechaInicio) . '&fecha_fin=' . urlencode($fechaFin));
                exit;
            }
            
            // Configurar headers para descarga de CSV
            $filename = 'gestion_completa_asesores_' . $fechaInicio . '_' . $fechaFin . '.csv';
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
            
            // Crear archivo CSV
            $output = fopen('php://output', 'w');
            
            // BOM para UTF-8 (evita problemas con caracteres especiales en Excel)
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Encabezados del CSV (solo columnas con datos)
            $headers = [
                'Asesor',
                'Cliente',
                'Cédula',
                'Teléfono',
                'Email',
                'Ciudad',
                'Base de Datos',
                'Tipo de Gestión',
                'Tipo de Contacto',
                'Resultado',
                'Observaciones',
                'Fecha de Gestión',
                'Fecha Próximo Contacto',
                'Hora Próximo Contacto',
                'Estado del Cliente',
                'Fecha de Asignación',
                'Fecha de Creación del Cliente'
            ];
            
            fputcsv($output, $headers);
            
            // Datos del CSV
            foreach ($datos as $row) {
                $csvRow = [
                    $row['asesor'],
                    $row['cliente'],
                    $row['cedula'],
                    $row['telefono'],
                    $row['email'],
                    $row['ciudad'],
                    $row['base_datos'],
                    $row['tipo_gestion_export'],
                    $row['tipo_contacto'],
                    $row['resultado_export'],
                    $row['observaciones'],
                    $row['fecha_gestion'],
                    $row['fecha_proximo_contacto'],
                    $row['hora_proximo_contacto'],
                    $row['estado_gestion'],
                    $row['fecha_asignacion'],
                    $row['fecha_creacion']
                ];
                
                fputcsv($output, $csvRow);
            }
            
            fclose($output);
            exit;
            
        } catch (Exception $e) {
            error_log("Error en exportarGestionCSV: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'Error interno del servidor'], 500);
        }
    }
    
    /**
     * Vista de gestión de bases de datos
     */
    public function gestion() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            header('Location: index.php?action=login');
            exit;
        }
        
        $coordinadorId = $_SESSION['user_id'];
        
        // Obtener bases de datos del coordinador
        $basesDatos = $this->baseDatosController->getBasesDatos($coordinadorId);
        
        // Obtener asesores disponibles
        $asesores = $this->getAsesoresDisponibles($coordinadorId);
        
        // Calcular estadísticas
        $basesAsignadas = 0;
        $basesDisponibles = 0;
        $totalClientes = 0;
        
        foreach ($basesDatos as $base) {
            if ($base['asesor_id']) {
                $basesAsignadas++;
            } else {
                $basesDisponibles++;
            }
            $totalClientes += $base['total_clientes_actual'];
        }
        
        // Incluir la vista
        include __DIR__ . '/../views/coordinador_gestion.php';
    }
    
    /**
     * Asignar asesor a múltiples bases de datos
     */
    public function asignarMultipleBases() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'Método no permitido'], 405);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $coordinadorId = $_SESSION['user_id'];
        $baseIds = $input['base_ids'] ?? [];
        $asesorId = $input['asesor_id'] ?? null;
        $asignarClientes = $input['asignar_clientes'] ?? false;
        
        if (empty($baseIds) || !$asesorId) {
            $this->jsonResponse(['success' => false, 'error' => 'Datos requeridos faltantes'], 400);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            $asignacionesExitosas = 0;
            $errores = [];
            
            foreach ($baseIds as $baseId) {
                try {
                    // Verificar que la base pertenece al coordinador
                    $base = $this->baseDatosController->getBaseDatosById($baseId, $coordinadorId);
                    if (!$base) {
                        $errores[] = "Base ID $baseId no encontrada";
                        continue;
                    }
                    
                    // Asignar asesor a la base
                    $this->baseDatosController->asignarAsesor($baseId, $asesorId, $coordinadorId);
                    
                    // Si se solicita, asignar todos los clientes de la base al asesor
                    if ($asignarClientes) {
                        $this->asignarClientesDeBase($baseId, $asesorId, $coordinadorId);
                    }
                    
                    $asignacionesExitosas++;
                    
                } catch (Exception $e) {
                    $errores[] = "Error en base ID $baseId: " . $e->getMessage();
                }
            }
            
            if ($asignacionesExitosas > 0) {
                $this->db->commit();
                $mensaje = "Se asignaron exitosamente $asignacionesExitosas base(s) de datos al asesor.";
                if (!empty($errores)) {
                    $mensaje .= " Errores: " . implode(', ', $errores);
                }
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => $mensaje,
                    'asignaciones' => $asignacionesExitosas
                ]);
            } else {
                $this->db->rollback();
                $this->jsonResponse(['success' => false, 'error' => 'No se pudo asignar ninguna base de datos'], 400);
            }
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error en asignarMultipleBases: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'Error interno del servidor'], 500);
        }
    }
    
    /**
     * Liberar asesor de base de datos
     */
    public function liberarAsesorBase() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 401);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'error' => 'Método no permitido'], 405);
            return;
        }
        
        $coordinadorId = $_SESSION['user_id'];
        $baseDatosId = $_POST['base_datos_id'] ?? null;
        
        if (!$baseDatosId) {
            $this->jsonResponse(['success' => false, 'error' => 'ID de base de datos requerido'], 400);
            return;
        }
        
        try {
            // Verificar que la base pertenece al coordinador
            $base = $this->baseDatosController->getBaseDatosById($baseDatosId, $coordinadorId);
            if (!$base) {
                $this->jsonResponse(['success' => false, 'error' => 'Base de datos no encontrada'], 404);
                return;
            }
            
            // Liberar asesor de la base
            $this->baseDatosController->liberarAsesor($baseDatosId, $coordinadorId);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Asesor liberado exitosamente'
            ]);
            
        } catch (Exception $e) {
            error_log("Error en liberarAsesorBase: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Ver detalles de una base de datos
     */
    public function verDetallesBase() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
            $this->jsonResponse(['success' => false, 'error' => 'No autorizado'], 401);
            return;
        }
        
        $coordinadorId = $_SESSION['user_id'];
        $baseId = $_GET['base_id'] ?? null;
        
        if (!$baseId) {
            $this->jsonResponse(['success' => false, 'error' => 'ID de base requerido'], 400);
            return;
        }
        
        try {
            // Obtener información de la base
            $base = $this->baseDatosController->getBaseDatosById($baseId, $coordinadorId);
            if (!$base) {
                $this->jsonResponse(['success' => false, 'error' => 'Base de datos no encontrada'], 404);
                return;
            }
            
            // Obtener clientes de la base
            $sql = "SELECT c.*, u.nombre_completo as asesor_nombre 
                    FROM clientes c 
                    LEFT JOIN usuarios u ON c.asesor_id = u.id 
                    WHERE c.base_datos_id = ? 
                    ORDER BY c.fecha_creacion DESC";
            $clientes = $this->db->fetchAll($sql, [$baseId]);
            
            $this->jsonResponse([
                'success' => true,
                'base' => $base,
                'clientes' => $clientes
            ]);
            
        } catch (Exception $e) {
            error_log("Error en verDetallesBase: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'Error interno del servidor'], 500);
        }
    }
    
    /**
     * Asignar clientes de una base a un asesor
     */
    private function asignarClientesDeBase($baseId, $asesorId, $coordinadorId) {
        $sql = "UPDATE clientes 
                SET asesor_id = ?, estado_gestion = 'Asignado', estado = 'Asignado' 
                WHERE base_datos_id = ? AND coordinador_id = ?";
        
        $result = $this->db->query($sql, [$asesorId, $baseId, $coordinadorId]);
        
        if ($result) {
            // Registrar en historial de asignaciones
            $sqlHistorial = "INSERT INTO historial_asignaciones (cliente_id, asesor_id, coordinador_id, fecha_asignacion, estado) 
                            SELECT id, ?, ?, NOW(), 'Asignado' 
                            FROM clientes 
                            WHERE base_datos_id = ? AND coordinador_id = ?";
            $this->db->query($sqlHistorial, [$asesorId, $coordinadorId, $baseId, $coordinadorId]);
        }
        
        return $result;
    }
    
    /**
     * Obtener asesores disponibles del coordinador
     */
    private function getAsesoresDisponibles($coordinadorId) {
        $sql = "SELECT id, nombre_completo 
                FROM usuarios 
                WHERE coordinador_id = ? AND rol = 'asesor' AND estado = 'Activo' 
                ORDER BY nombre_completo";
        
        return $this->db->fetchAll($sql, [$coordinadorId]);
    }
    
    /**
     * Enviar respuesta JSON
     */
    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
?>
