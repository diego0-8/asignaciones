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
        
        /* Responsive */
        @media (max-width: 768px) {
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
                                    <li><strong>Campos obligatorios:</strong> Cédula, Nombre Completo, Teléfono</li>
                                    <li><strong>Campos opcionales:</strong> Celular 2, Email, Ciudad</li>
                                    <li><strong>Separador:</strong> Coma (,) entre campos</li>
                                    <li><strong>Codificación:</strong> UTF-8 recomendado</li>
                                </ul>

                                <h3>Ejemplo de formato CSV:</h3>
                                <div class="format-example">
                                    <pre>cedula,nombre_completo,telefono,celular2,email,ciudad
12345678,Juan Pérez,3001234567,3009876543,juan@email.com,Bogotá
87654321,María García,3001111111,3002222222,maria@email.com,Medellín</pre>
                                </div>

                                <div class="important-note">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <p><strong>Nota importante:</strong> El sistema verifica automáticamente que no haya cédulas duplicadas. Si es la primera carga, se creará una nueva base de datos con el nombre que proporciones. Si ya existe una base, se agregarán solo los clientes nuevos a la base existente.</p>
                                </div>
                                
                                <div class="base-info" id="baseInfo" style="display: none;">
                                    <i class="fas fa-database"></i>
                                    <p><strong>Base actual:</strong> <span id="nombreBaseActual"></span></p>
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
                                <div class="form-group" id="nombreCargaGroup">
                                    <label for="nombre_carga">Nombre de la Carga:</label>
                                    <input type="text" id="nombre_carga" name="nombre_carga" 
                                           placeholder="Ej: Base de datos Enero 2024" class="form-control">
                                    <small class="form-text">Identifica esta carga de clientes (solo para la primera carga)</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="archivo_csv">Seleccionar Archivo CSV:</label>
                                    <input type="file" id="archivo_csv" name="archivo_csv" accept=".csv" required class="form-control">
                                    <small class="form-text">Solo archivos CSV hasta 5MB</small>
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
    <div id="alertContainer" class="alert-container"></div>

    <script src="assets/js/coordinador-cargar-archivo.js"></script>
</body>
</html>
