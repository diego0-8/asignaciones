<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargar Archivo - Coordinador</title>
    <link rel="stylesheet" href="assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Estilos para alerta de éxito grande */
        .alert-success-large {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1001;
            max-width: 600px;
            width: 90%;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            animation: slideDown 0.5s ease-out;
        }
        
        .alert-success-content {
            padding: 25px;
            position: relative;
        }
        
        .alert-success-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .alert-success-header i {
            font-size: 2rem;
            color: #28a745;
        }
        
        .alert-success-header h4 {
            margin: 0;
            color: #155724;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .alert-success-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            background: white;
            border-radius: 8px;
            border: 1px solid #c3e6cb;
        }
        
        .stat-label {
            font-weight: 600;
            color: #155724;
        }
        
        .stat-value {
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .stat-value.success {
            color: #28a745;
        }
        
        .stat-value.error {
            color: #dc3545;
        }
        
        .alert-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #6c757d;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .alert-close:hover {
            background: rgba(0, 0, 0, 0.1);
            color: #495057;
        }
        
        .form-actions-success {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            padding: 20px;
        }
        
        .base-info {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .base-info i {
            color: #2196f3;
            font-size: 1.2rem;
        }
        
        .base-info p {
            margin: 0;
            color: #1976d2;
            font-weight: 500;
        }
        
        .upload-icon {
            display: inline-block;
            width: 16px;
            height: 16px;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>') no-repeat center;
            background-size: contain;
        }
        
        @keyframes slideDown {
            from {
                transform: translateX(-50%) translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(-50%) translateY(0);
                opacity: 1;
            }
        }
        
        /* Estilos para la carga de archivos */
        .upload-section {
            padding: 20px;
        }
        
        .upload-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .upload-instructions-column,
        .upload-form-column {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .instructions-card,
        .upload-form-card {
            padding: 25px;
        }
        
        .instructions-header,
        .upload-form-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e1e5e9;
        }
        
        .instructions-header i,
        .upload-form-header i {
            font-size: 2rem;
            color: #007bff;
        }
        
        .instructions-header h2,
        .upload-form-header h2 {
            margin: 0;
            color: #2c3e50;
            font-size: 1.5rem;
        }
        
        .instructions-content h3 {
            color: #2c3e50;
            margin: 20px 0 10px 0;
            font-size: 1.2rem;
        }
        
        .instructions-content ul {
            margin: 15px 0;
            padding-left: 20px;
        }
        
        .instructions-content li {
            margin: 8px 0;
            color: #555;
        }
        
        .format-example {
            background: #f8f9fa;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        
        .format-example pre {
            margin: 0;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            color: #495057;
        }
        
        .important-note {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        
        .important-note i {
            color: #856404;
            font-size: 1.2rem;
            margin-top: 2px;
        }
        
        .important-note p {
            margin: 0;
            color: #856404;
            font-size: 0.95rem;
        }
        
        .base-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .base-info i {
            color: #0c5460;
            font-size: 1.2rem;
        }
        
        .base-info p {
            margin: 0;
            color: #0c5460;
            font-size: 0.95rem;
        }
        
        .base-info small {
            display: block;
            margin-top: 5px;
            color: #6c757d;
            font-size: 0.85rem;
        }
        
        .base-existente-info .info-card {
            background: #e8f5e8;
            border: 1px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .base-existente-info .info-card i {
            font-size: 2rem;
            color: #28a745;
        }
        
        .base-existente-info .info-content h4 {
            margin: 0 0 8px 0;
            color: #155724;
            font-size: 1.1rem;
        }
        
        .base-existente-info .info-content p {
            margin: 0 0 5px 0;
            color: #155724;
            font-size: 0.95rem;
        }
        
        .base-existente-info .info-content small {
            color: #6c757d;
            font-size: 0.85rem;
        }
        
        .upload-form {
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }
        
        .form-text {
            display: block;
            margin-top: 5px;
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover:not(:disabled) {
            background: #0056b3;
            transform: translateY(-2px);
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
            transform: translateY(-2px);
        }
        

        
        .file-info {
            margin-top: 10px;
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
        }
        
        .file-details {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .file-details i {
            font-size: 2rem;
            color: #28a745;
        }
        
        .file-text strong {
            display: block;
            color: #2c3e50;
            font-size: 1rem;
        }
        
        .file-text small {
            color: #6c757d;
            font-size: 0.85rem;
        }
        
        .upload-progress {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin: 20px 0;
        }
        
        .progress-header h3 {
            margin: 0 0 20px 0;
            color: #2c3e50;
            text-align: center;
        }
        
        .progress-bar {
            width: 100%;
            height: 20px;
            background: #e1e5e9;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 15px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #007bff, #0056b3);
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .progress-text {
            text-align: center;
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.1rem;
        }
        
        .upload-results {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin: 20px 0;
        }
        
        .results-header h3 {
            margin: 0 0 20px 0;
            color: #2c3e50;
            text-align: center;
        }
        
        .results-success .result-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e1e5e9;
        }
        
        .results-success .result-header i {
            font-size: 2.5rem;
            color: #28a745;
        }
        
        .results-success .result-header h4 {
            margin: 0;
            color: #28a745;
            font-size: 1.8rem;
        }
        
        .result-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .result-errors {
            margin: 25px 0;
            padding: 20px;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
        }
        
        .result-errors h5 {
            margin: 0 0 15px 0;
            color: #721c24;
        }
        
        .errors-list {
            max-height: 200px;
            overflow-y: auto;
        }
        
        .error-item {
            padding: 8px 0;
            border-bottom: 1px solid #f5c6cb;
            color: #721c24;
            font-size: 0.9rem;
        }
        
        .result-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 25px;
        }
        
        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        }
        
        .alert {
            margin-bottom: 10px;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: slideInRight 0.3s ease-out;
            position: relative;
            overflow: hidden;
        }
        
        .alert::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: currentColor;
            opacity: 0.3;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #d1ecf1, #bee5eb);
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        
        .alert .close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: inherit;
            opacity: 0.7;
            transition: opacity 0.2s;
            margin-left: 10px;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .alert .close:hover {
            opacity: 1;
            background: rgba(0,0,0,0.1);
        }
        
        .alert-content {
            flex: 1;
            margin-right: 10px;
        }
        
        .alert-title {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .alert-message {
            font-size: 14px;
            opacity: 0.9;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .alert.removing {
            animation: slideOutRight 0.3s ease-in forwards;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .upload-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .alert-success-large {
                width: 95%;
                left: 2.5%;
                transform: none;
            }
            
            .alert-success-stats {
                grid-template-columns: 1fr;
            }
            
            .form-actions-success {
                flex-direction: column;
                align-items: center;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .result-actions {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-users-cog"></i>
            </div>
            
            <nav class="sidebar-nav">
                <a href="index.php?action=coordinador_dashboard" class="nav-item" title="Dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                </a>
                <a href="index.php?action=coordinador_gestionar_bases" class="nav-item" title="Gestionar Bases">
                    <i class="fas fa-database"></i>
                </a>
                <a href="index.php?action=coordinador_cargar_archivo" class="nav-item active" title="Cargar Archivo">
                    <i class="fas fa-upload"></i>
                </a>
                <a href="index.php?action=coordinador_tareas" class="nav-item" title="Tareas">
                    <i class="fas fa-tasks"></i>
                </a>
                <a href="index.php?action=coordinador_transferir_clientes" class="nav-item" title="Transferir Clientes">
                    <i class="fas fa-exchange-alt"></i>
                </a>
                <a href="index.php?action=coordinador_descargar_archivos" class="nav-item" title="Descargar CSV">
                    <i class="fas fa-download"></i>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <a href="index.php?action=coordinador_cerrar_sesion" class="nav-item" title="Cerrar Sesión">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="top-bar-left">
                    <h1>Cargar Archivo CSV</h1>
                </div>
                <div class="top-bar-right">
                    <span class="user-info">
                        <i class="fas fa-user"></i>
                        <?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? 'Coordinador'); ?>
                    </span>
                </div>
            </div>

            <!-- Upload Section - Two Columns -->
            <div class="upload-section">
                <div class="upload-container">
                    <!-- Left Column - Instructions -->
                    <div class="upload-instructions-column">
                        <div class="instructions-card">
                            <div class="instructions-header">
                                <i class="fas fa-info-circle"></i>
                                <h2>Instrucciones de Carga</h2>
                            </div>
                            
                            <div class="instructions-content">
                                <h3>Formato Requerido:</h3>
                                <ul>
                                    <li><strong>Archivo:</strong> Solo archivos CSV (.csv)</li>
                                    <li><strong>Campos obligatorios:</strong> Nombre, Cédula, Teléfono</li>
                                    <li><strong>Separador:</strong> Coma (,) entre campos</li>
                                    <li><strong>Codificación:</strong> UTF-8 recomendado</li>
                                    <li><strong>Formato:</strong> Primera línea debe contener los nombres de las columnas</li>
                                </ul>

                                <h3>Ejemplo de formato CSV:</h3>
                                <div class="format-example">
                                    <pre>nombre,cedula,telefono
Juan Pérez,12345678,3001234567
María García,87654321,3001111111
Carlos López,11223344,3003333333</pre>
                                </div>

                                <div class="important-note">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <p><strong>Nota importante:</strong> El sistema verifica automáticamente que no haya cédulas duplicadas. Puedes crear una nueva base de datos o cargar a una existente.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Upload Form -->
                    <div class="upload-form-column">
                        <div class="upload-form-card">
                            <div class="upload-form-header">
                                <i class="fas fa-upload"></i>
                                <h2>Cargar Archivo</h2>
                            </div>

                            <form id="uploadForm" enctype="multipart/form-data" class="upload-form">
                                <!-- Selección de opción -->
                                <div class="form-group">
                                    <label>Selecciona una opción:</label>
                                    <div style="display: flex; gap: 15px; margin-top: 10px;">
                                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                            <input type="radio" name="opcion_carga" value="existente" checked onchange="toggleOpciones()">
                                            <span>Cargar a base existente</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                            <input type="radio" name="opcion_carga" value="nueva" onchange="toggleOpciones()">
                                            <span>Crear nueva base</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Selección de base existente -->
                                <div class="form-group" id="baseExistenteGroup">
                                    <label for="base_datos_id">Seleccionar Base de Datos:</label>
                                    <select id="base_datos_id" name="base_datos_id" class="form-control">
                                        <option value="">Seleccionar base de datos...</option>
                                        <?php foreach ($basesDatos as $base): ?>
                                            <option value="<?php echo $base['id']; ?>" 
                                                    <?php echo (isset($_GET['base_id']) && $_GET['base_id'] == $base['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($base['nombre_base']); ?>
                                                (<?php echo number_format($base['total_clientes_actual']); ?> clientes)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text">Selecciona la base de datos donde cargar los clientes</small>
                                </div>

                                <!-- Crear nueva base -->
                                <div class="form-group" id="nuevaBaseGroup" style="display: none;">
                                    <label for="nombre_nueva_base">Nombre de la Nueva Base:</label>
                                    <input type="text" id="nombre_nueva_base" name="nombre_nueva_base" 
                                           placeholder="Ej: Clientes Enero 2024" class="form-control">
                                    <small class="form-text">Identifica esta nueva base de datos</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="archivo_csv">Seleccionar Archivo CSV:</label>
                                    <input type="file" id="archivo_csv" name="archivo" accept=".csv" required class="form-control">
                                    <small class="form-text">Solo archivos CSV hasta 500MB</small>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="upload-icon"></i> <span id="btnText">Cargar Archivo</span>
                                    </button>
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="fas fa-undo"></i> Limpiar
                                    </button>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Progress Bar (hidden by default) -->
                <div id="uploadProgress" class="upload-progress" style="display: none;">
                    <div class="progress-header">
                        <h3>Procesando archivo...</h3>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                    <div class="progress-text" id="progressText">0%</div>
                </div>
                
                <!-- Results Section -->
                <div id="uploadResults" class="upload-results" style="display: none;">
                    <div class="results-header">
                        <h3>Resultados de la Carga</h3>
                    </div>
                    <div class="results-content" id="resultsContent">
                        <!-- Results will be populated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Container -->
    <!-- Alertas de carga -->
    <div id="alertContainer" class="alert-container">
        <!-- Las alertas se insertarán aquí dinámicamente -->
    </div>

    <script src="assets/js/coordinador-cargar-archivo.js"></script>
</body>
</html>
