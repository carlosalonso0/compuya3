<?php
/**
 * Panel de administración - Gestión de pedidos
 */

// Incluir archivos necesarios
require_once '../config.php';
require_once INCLUDES_PATH . '/functions.php';

// Título de la página
$page_title = 'Gestión de Pedidos';

// Cargar el header del admin
include_once 'includes/header.php';

// Procesar acción si existe
$action = isset($_GET['action']) ? $_GET['action'] : '';
$success_message = '';
$error_message = '';

// Actualizar estado de pedido
if ($action == 'update_status' && isset($_GET['id']) && isset($_GET['status'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['status'];
    $notas = isset($_GET['notas']) ? $_GET['notas'] : '';
    
    // Verificar que sea un estado válido
    $estados_validos = ['pendiente', 'procesando', 'enviado', 'entregado', 'cancelado'];
    if (!in_array($status, $estados_validos)) {
        $error_message = 'Estado no válido.';
    } else {
        try {
            // Iniciar transacción
            begin_transaction();
            
            // Actualizar estado del pedido
            $sql = "UPDATE pedidos SET estado = ? WHERE id = ?";
            $result = update($sql, [$status, $id]);
            
            if ($result === false) {
                throw new Exception("Error al actualizar el estado del pedido");
            }
            
            // Registrar en historial de estados
            $sql = "INSERT INTO pedido_estados (pedido_id, estado, notas) VALUES (?, ?, ?)";
            $result = insert($sql, [$id, $status, $notas]);
            
            if ($result === false) {
                throw new Exception("Error al registrar el historial de estados");
            }
            
            // Confirmar transacción
            commit();
            
            $success_message = 'Estado del pedido #' . $id . ' actualizado a "' . ucfirst($status) . '"';
            
        } catch (Exception $e) {
            // Revertir cambios en caso de error
            rollback();
            $error_message = 'Error: ' . $e->getMessage();
        }
    }
}

// Página actual para paginación
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$pedidos_por_pagina = 10;
$offset = ($pagina_actual - 1) * $pedidos_por_pagina;

// Filtros
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Construir consulta SQL con filtros
$where_clauses = [];
$params = [];

if (!empty($filter_status)) {
    $where_clauses[] = "p.estado = ?";
    $params[] = $filter_status;
}

if (!empty($search)) {
    $where_clauses[] = "(p.id = ? OR p.cliente_nombre LIKE ? OR p.cliente_email LIKE ? OR p.cliente_telefono LIKE ?)";
    $params[] = (int)$search; // Buscar por ID
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Consulta para obtener el total de pedidos
$count_sql = "SELECT COUNT(*) AS total FROM pedidos p $where_sql";
$total_result = get_row($count_sql, $params);
$total_pedidos = $total_result ? $total_result['total'] : 0;

// Calcular total de páginas
$total_paginas = ceil($total_pedidos / $pedidos_por_pagina);

// Consulta para obtener pedidos
$pedidos_sql = "
    SELECT p.*, 
           (SELECT COUNT(*) FROM pedido_items WHERE pedido_id = p.id) as items_count
    FROM pedidos p
    $where_sql
    ORDER BY p.fecha_creacion DESC
    LIMIT $pedidos_por_pagina OFFSET $offset
";

$pedidos = get_rows($pedidos_sql, $params);
?>

<div class="admin-actions">
    <div class="action-header">
        <h2>Gestión de Pedidos</h2>
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
        <form method="get" action="" class="filter-form">
            <div class="filter-group">
                <label for="status">Estado:</label>
                <select id="status" name="status">
                    <option value="">Todos</option>
                    <option value="pendiente" <?php echo ($filter_status == 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                    <option value="procesando" <?php echo ($filter_status == 'procesando') ? 'selected' : ''; ?>>Procesando</option>
                    <option value="enviado" <?php echo ($filter_status == 'enviado') ? 'selected' : ''; ?>>Enviado</option>
                    <option value="entregado" <?php echo ($filter_status == 'entregado') ? 'selected' : ''; ?>>Entregado</option>
                    <option value="cancelado" <?php echo ($filter_status == 'cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="search">Buscar:</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="ID, Nombre, Email...">
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn-filter">Filtrar</button>
                <a href="pedidos.php" class="btn-reset">Reiniciar</a>
            </div>
        </form>
    </div>
    
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Método de Pago</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Items</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pedidos)): ?>
                    <tr>
                        <td colspan="8" class="text-center">No se encontraron pedidos</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td>#<?php echo $pedido['id']; ?></td>
                            <td>
                                <div class="customer-info">
                                    <div class="customer-name"><?php echo htmlspecialchars($pedido['cliente_nombre']); ?></div>
                                    <div class="customer-email"><?php echo htmlspecialchars($pedido['cliente_email']); ?></div>
                                </div>
                            </td>
                            <td><?php echo formatear_precio($pedido['total']); ?></td>
                            <td><?php echo ucfirst($pedido['metodo_pago']); ?></td>
                            <td>
                                <?php
                                $status_class = '';
                                switch ($pedido['estado']) {
                                    case 'pendiente':
                                        $status_class = 'pending';
                                        break;
                                    case 'procesando':
                                        $status_class = 'processing';
                                        break;
                                    case 'enviado':
                                        $status_class = 'shipped';
                                        break;
                                    case 'entregado':
                                        $status_class = 'active';
                                        break;
                                    case 'cancelado':
                                        $status_class = 'inactive';
                                        break;
                                }
                                ?>
                                <span class="status <?php echo $status_class; ?>">
                                    <?php echo ucfirst($pedido['estado']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_creacion'])); ?></td>
                            <td><?php echo $pedido['items_count']; ?></td>
                            <td>
                                <a href="pedido-ver.php?id=<?php echo $pedido['id']; ?>" class="btn-action view" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="btn-action edit" title="Cambiar estado" onclick="openStatusModal(<?php echo $pedido['id']; ?>, '<?php echo $pedido['estado']; ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($total_paginas > 1): ?>
        <div class="pagination">
            <?php if ($pagina_actual > 1): ?>
                <a href="pedidos.php?pagina=<?php echo ($pagina_actual - 1); ?>&status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search); ?>">&laquo;</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $pagina_actual - 2); $i <= min($pagina_actual + 2, $total_paginas); $i++): ?>
                <?php if ($i == $pagina_actual): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="pedidos.php?pagina=<?php echo $i; ?>&status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($pagina_actual < $total_paginas): ?>
                <a href="pedidos.php?pagina=<?php echo ($pagina_actual + 1); ?>&status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search); ?>">&raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Modal para cambiar estado -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Cambiar Estado del Pedido</h2>
            <form id="updateStatusForm" action="pedidos.php" method="get">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" id="pedido_id" name="id" value="">
                
                <div class="form-group">
                    <label for="status">Nuevo Estado:</label>
                    <select id="status_select" name="status" required>
                        <option value="pendiente">Pendiente</option>
                        <option value="procesando">Procesando</option>
                        <option value="enviado">Enviado</option>
                        <option value="entregado">Entregado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="notas">Notas:</label>
                    <textarea id="notas" name="notas" rows="3" placeholder="Información adicional sobre el cambio de estado"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn-submit">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Estilos para modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content {
    background-color: #fff;
    margin: 10% auto;
    padding: 30px;
    border-radius: 5px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    max-width: 500px;
    width: 90%;
    position: relative;
}

.close {
    position: absolute;
    top: 15px;
    right: 15px;
    color: #aaa;
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: #333;
}

/* Estilos para clases de estado */
.status.processing {
    background-color: #fff3e0;
    color: #f57c00;
}

.status.shipped {
    background-color: #e3f2fd;
    color: #1976d2;
}

/* Estilos para información de cliente */
.customer-info {
    line-height: 1.4;
}

.customer-name {
    font-weight: 500;
}

.customer-email {
    font-size: 13px;
    color: #666;
}
</style>

<script>
// Función para abrir modal de cambio de estado
function openStatusModal(pedidoId, currentStatus) {
    document.getElementById('pedido_id').value = pedidoId;
    document.getElementById('status_select').value = currentStatus;
    document.getElementById('statusModal').style.display = 'block';
}

// Función para cerrar modal
function closeModal() {
    document.getElementById('statusModal').style.display = 'none';
}

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('statusModal');
    if (event.target == modal) {
        closeModal();
    }
}

// Cerrar modal con la X
document.querySelector('.close').addEventListener('click', closeModal);
</script>

<?php
// Cargar el footer del admin
include_once 'includes/footer.php';
?>