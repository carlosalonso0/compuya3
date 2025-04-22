<?php
/**
 * Panel de administración - Dashboard principal
 */

// Incluir archivos necesarios
require_once '../config.php';
require_once INCLUDES_PATH . '/functions.php';

// Verificar si hay una sesión activa (aquí iría la lógica de autenticación)
// Por ahora, simplemente asumimos que el admin está autenticado

// Título de la página
$page_title = 'Dashboard';

// Cargar el header del admin
include_once 'includes/header.php';
?>

<div class="dashboard-container">
    <div class="dashboard-summary">
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="summary-data">
                <h3>8 Carruseles</h3>
                <p>Configurados en la página de inicio</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-tag"></i>
            </div>
            <div class="summary-data">
                <h3>2 Ofertas Especiales</h3>
                <p>Activas en la página de inicio</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-image"></i>
            </div>
            <div class="summary-data">
                <h3>12 Banners</h3>
                <p>Configurados en los carruseles</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="summary-data">
                <h3>24 Productos</h3>
                <p>Visibles en la página de inicio</p>
            </div>
        </div>
    </div>
    
    <div class="dashboard-actions">
        <h2>Acciones Rápidas</h2>
        <div class="action-buttons">
        <a href="carousels.php" class="btn-admin">
            <i class="fas fa-sliders-h"></i> Gestionar Carruseles
        </a>
        <a href="banners.php" class="btn-admin">
            <i class="fas fa-image"></i> Gestionar Banners
        </a>
        <a href="products.php" class="btn-admin">
            <i class="fas fa-box"></i> Gestionar Productos
        </a>
        <a href="product-import.php" class="btn-admin">
            <i class="fas fa-file-import"></i> Importar Productos CSV
        </a>
        <a href="offers.php" class="btn-admin">
            <i class="fas fa-tags"></i> Gestionar Ofertas
        </a>
    </div>
    </div>
    
    <div class="dashboard-sections">
        <div class="dashboard-section">
            <h2>Carruseles de Página Inicio</h2>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Nombre</th>
                            <th>Contenido</th>
                            <th>Elementos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Banner</td>
                            <td>Banner Principal</td>
                            <td>Manual</td>
                            <td>3 banners</td>
                            <td>
                                <a href="carousel-edit.php?id=1" class="btn-action edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>Producto</td>
                            <td>Ofertas Destacadas</td>
                            <td>Automático</td>
                            <td>5 productos</td>
                            <td>
                                <a href="carousel-edit.php?id=6" class="btn-action edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>8</td>
                            <td>Producto</td>
                            <td>Tarjetas Gráficas</td>
                            <td>Por categoría</td>
                            <td>8 productos</td>
                            <td>
                                <a href="carousel-edit.php?id=8" class="btn-action edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        <!-- Más filas... -->
                    </tbody>
                </table>
            </div>
            <a href="carousels.php" class="btn-view-all">Ver todos los carruseles</a>
        </div>
        
        <div class="dashboard-section">
            <h2>Ofertas Especiales</h2>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Posición</th>
                            <th>Producto</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>10</td>
                            <td>CORSAIR 4000D AIRFLOW...</td>
                            <td>2025-04-15</td>
                            <td>2025-04-25</td>
                            <td><span class="status active">Activa</span></td>
                            <td>
                                <a href="offer-edit.php?id=1" class="btn-action edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>11</td>
                            <td>ASUS ROG STRIX G15...</td>
                            <td>2025-04-10</td>
                            <td>2025-04-30</td>
                            <td><span class="status active">Activa</span></td>
                            <td>
                                <a href="offer-edit.php?id=2" class="btn-action edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <a href="offers.php" class="btn-view-all">Ver todas las ofertas</a>
        </div>
    </div>
</div>

<?php
// Cargar el footer del admin
include_once 'includes/footer.php';
?>