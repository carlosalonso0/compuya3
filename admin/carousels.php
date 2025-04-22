<?php
/**
 * Panel de administración - Gestión de carruseles
 */

// Incluir archivos necesarios
require_once '../config.php';
require_once INCLUDES_PATH . '/functions.php';

// Verificar si hay una sesión activa (aquí iría la lógica de autenticación)
// Por ahora, simplemente asumimos que el admin está autenticado

// Título de la página
$page_title = 'Gestión de Carruseles';

// Cargar el header del admin
include_once 'includes/header.php';

// Procesar acción si existe
$action = isset($_GET['action']) ? $_GET['action'] : '';
$success_message = '';
$error_message = '';

if ($action == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Aquí iría la lógica para eliminar el carrusel
    // Por ahora, simulamos una respuesta exitosa
    $success_message = 'Carrusel #' . $id . ' eliminado correctamente.';
}

// Obtener lista de carruseles
// En una implementación real, esto vendría de la base de datos
$carruseles = [
    [
        'id' => 1,
        'nombre' => 'Banner Principal',
        'tipo' => 'banner',
        'tipo_contenido' => 'manual',
        'categoria_id' => null,
        'activo' => 1,
        'elementos' => 3,
        'fecha_creacion' => '2025-01-15 10:30:00'
    ],
    [
        'id' => 2,
        'nombre' => 'Banner Izquierda',
        'tipo' => 'banner',
        'tipo_contenido' => 'manual',
        'categoria_id' => null,
        'activo' => 1,
        'elementos' => 2,
        'fecha_creacion' => '2025-01-15 11:00:00'
    ],
    [
        'id' => 3,
        'nombre' => 'Banner Centro',
        'tipo' => 'banner',
        'tipo_contenido' => 'manual',
        'categoria_id' => null,
        'activo' => 1,
        'elementos' => 2,
        'fecha_creacion' => '2025-01-15 11:15:00'
    ],
    [
        'id' => 4,
        'nombre' => 'Banner Superior Derecha',
        'tipo' => 'banner',
        'tipo_contenido' => 'manual',
        'categoria_id' => null,
        'activo' => 1,
        'elementos' => 1,
        'fecha_creacion' => '2025-01-15 11:30:00'
    ],
    [
        'id' => 5,
        'nombre' => 'Banner Inferior Derecha',
        'tipo' => 'banner',
        'tipo_contenido' => 'manual',
        'categoria_id' => null,
        'activo' => 1,
        'elementos' => 1,
        'fecha_creacion' => '2025-01-15 11:45:00'
    ],
    [
        'id' => 6,
        'nombre' => 'Ofertas Destacadas',
        'tipo' => 'producto',
        'tipo_contenido' => 'manual',
        'categoria_id' => null,
        'activo' => 1,
        'elementos' => 5,
        'fecha_creacion' => '2025-01-16 09:00:00'
    ],
    [
        'id' => 7,
        'nombre' => 'Banner Inferior',
        'tipo' => 'banner',
        'tipo_contenido' => 'manual',
        'categoria_id' => null,
        'activo' => 1,
        'elementos' => 2,
        'fecha_creacion' => '2025-01-16 09:30:00'
    ],
    [
        'id' => 8,
        'nombre' => 'Tarjetas Gráficas',
        'tipo' => 'producto',
        'tipo_contenido' => 'categoria',
        'categoria_id' => 1,
        'activo' => 1,
        'elementos' => 8,
        'fecha_creacion' => '2025-01-16 10:00:00'
    ],
    [
        'id' => 9,
        'nombre' => 'Procesadores',
        'tipo' => 'producto',
        'tipo_contenido' => 'categoria',
        'categoria_id' => 2,
        'activo' => 1,
        'elementos' => 8,
        'fecha_creacion' => '2025-01-16 10:30:00'
    ]
];
?>

<div class="admin-actions">
    <div class="action-header">
        <h2>Gestión de Carruseles</h2>
        <a href="carousel-add.php" class="btn-add">
            <i class="fas fa-plus"></i> Nuevo Carrusel
        </a>
    </div>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-error">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>
    
    <div class="filter-bar">
        <div class="filter-group">
            <label for="filter-type">Filtrar por tipo:</label>
            <select id="filter-type">
                <option value="">Todos</option>
                <option value="banner">Banners</option>
                <option value="producto">Productos</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label for="filter-status">Estado:</label>
            <select id="filter-status">
                <option value="">Todos</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label for="search">Buscar:</label>
            <input type="text" id="search" placeholder="Nombre del carrusel...">
        </div>
    </div>
    
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Contenido</th>
                    <th>Elementos</th>
                    <th>Estado</th>
                    <th>Fecha Creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($carruseles as $carrusel): ?>
                    <tr>
                        <td><?php echo $carrusel['id']; ?></td>
                        <td><?php echo $carrusel['nombre']; ?></td>
                        <td>
                            <?php if ($carrusel['tipo'] == 'banner'): ?>
                                <span class="badge badge-blue">Banner</span>
                            <?php else: ?>
                                <span class="badge badge-green">Producto</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($carrusel['tipo_contenido'] == 'manual'): ?>
                                Manual
                            <?php else: ?>
                                Categoría: <?php echo $carrusel['categoria_id']; ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $carrusel['elementos']; ?></td>
                        <td>
                            <?php if ($carrusel['activo']): ?>
                                <span class="status active">Activo</span>
                            <?php else: ?>
                                <span class="status inactive">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($carrusel['fecha_creacion'])); ?></td>
                        <td>
                            <a href="carousel-edit.php?id=<?php echo $carrusel['id']; ?>" class="btn-action edit" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($carrusel['id'] > 9): ?>
                                <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $carrusel['id']; ?>)" class="btn-action delete" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php endif; ?>
                            <a href="carousel-view.php?id=<?php echo $carrusel['id']; ?>" class="btn-action view" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="pagination">
        <a href="#">&laquo;</a>
        <span class="current">1</span>
        <a href="#">2</a>
        <a href="#">3</a>
        <a href="#">&raquo;</a>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este carrusel? Esta acción no se puede deshacer.')) {
        window.location.href = 'carousels.php?action=delete&id=' + id;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Filtrado por tipo
    document.getElementById('filter-type').addEventListener('change', function() {
        filterTable();
    });
    
    // Filtrado por estado
    document.getElementById('filter-status').addEventListener('change', function() {
        filterTable();
    });
    
    // Búsqueda
    document.getElementById('search').addEventListener('input', function() {
        filterTable();
    });
    
    function filterTable() {
        const filterType = document.getElementById('filter-type').value.toLowerCase();
        const filterStatus = document.getElementById('filter-status').value;
        const searchText = document.getElementById('search').value.toLowerCase();
        
        const rows = document.querySelectorAll('.admin-table tbody tr');
        
        rows.forEach(row => {
            const type = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            const status = row.querySelector('td:nth-child(6) .status').classList.contains('active') ? '1' : '0';
            const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            
            const matchesType = filterType === '' || type.includes(filterType);
            const matchesStatus = filterStatus === '' || status === filterStatus;
            const matchesSearch = searchText === '' || name.includes(searchText);
            
            if (matchesType && matchesStatus && matchesSearch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
});
</script>

<?php
// Cargar el footer del admin
include_once 'includes/footer.php';
?>