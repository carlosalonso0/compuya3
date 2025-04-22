<?php
/**
 * Panel de administración - Gestión de ofertas especiales
 */

// Incluir archivos necesarios
require_once '../config.php';
require_once INCLUDES_PATH . '/functions.php';

// Título de la página
$page_title = 'Gestión de Ofertas Especiales';

// Procesar acción si existe
$action = isset($_GET['action']) ? $_GET['action'] : '';
$success_message = '';
$error_message = '';

// Procesar eliminación de oferta
if ($action == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $sql = "DELETE FROM ofertas_especiales WHERE id = ?";
    $result = update($sql, [$id]);
    
    if ($result) {
        $success_message = 'Oferta #' . $id . ' eliminada correctamente.';
    } else {
        $error_message = 'Error al eliminar la oferta.';
    }
}

// Obtener ofertas activas
$ofertas_sql = "SELECT oe.*, p.nombre as producto_nombre, p.precio, p.precio_oferta, p.en_oferta
               FROM ofertas_especiales oe
               INNER JOIN productos p ON oe.producto_id = p.id
               ORDER BY oe.posicion ASC, oe.fecha_fin DESC";
$ofertas = get_rows($ofertas_sql);

// Cargar el header del admin
include_once 'includes/header.php';
?>

<div class="admin-actions">
    <div class="action-header">
        <h2>Gestión de Ofertas Especiales</h2>
        <a href="offer-add.php" class="btn-add">
            <i class="fas fa-plus"></i> Nueva Oferta Especial
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
    
    <div class="info-block">
        <p>Las ofertas especiales se muestran en dos posiciones fijas en la página de inicio (secciones 10 y 11). Solo puede haber una oferta activa por posición.</p>
    </div>
    
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Posición</th>
                    <th>Producto</th>
                    <th>Precio Original</th>
                    <th>Precio Oferta</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ofertas)): ?>
                    <tr>
                        <td colspan="9" class="text-center">No hay ofertas especiales configuradas.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ofertas as $oferta): 
                        $fecha_actual = date('Y-m-d H:i:s');
                        $estado = 'inactiva';
                        $estado_texto = 'Inactiva';
                        
                        if ($oferta['activo']) {
                            if ($fecha_actual >= $oferta['fecha_inicio'] && $fecha_actual <= $oferta['fecha_fin']) {
                                $estado = 'active';
                                $estado_texto = 'Activa';
                            } elseif ($fecha_actual < $oferta['fecha_inicio']) {
                                $estado = 'pending';
                                $estado_texto = 'Pendiente';
                            } elseif ($fecha_actual > $oferta['fecha_fin']) {
                                $estado = 'inactive';
                                $estado_texto = 'Finalizada';
                            }
                        }
                    ?>
                        <tr>
                            <td><?php echo $oferta['id']; ?></td>
                            <td><?php echo $oferta['posicion']; ?></td>
                            <td><?php echo htmlspecialchars($oferta['producto_nombre']); ?></td>
                            <td><?php echo formatear_precio($oferta['precio']); ?></td>
                            <td><?php echo formatear_precio($oferta['precio_oferta']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($oferta['fecha_inicio'])); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($oferta['fecha_fin'])); ?></td>
                            <td><span class="status <?php echo $estado; ?>"><?php echo $estado_texto; ?></span></td>
                            <td>
                                <a href="offer-edit.php?id=<?php echo $oferta['id']; ?>" class="btn-action edit" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $oferta['id']; ?>)" class="btn-action delete" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm('¿Estás seguro de que deseas eliminar esta oferta? Esta acción no se puede deshacer.')) {
        window.location.href = 'offers.php?action=delete&id=' + id;
    }
}
</script>

<?php
// Cargar el footer del admin
include_once 'includes/footer.php';
?>