<?php
/**
 * Panel de administración - Ver detalles de pedido
 */

// Incluir archivos necesarios
require_once '../config.php';
require_once INCLUDES_PATH . '/functions.php';

// Verificar que se proporcione un ID
if (!isset($_GET['id'])) {
    // Redirigir si no hay ID
    header('Location: pedidos.php');
    exit;
}

$id = (int)$_GET['id'];

// Obtener información del pedido
$sql = "SELECT * FROM pedidos WHERE id = ?";
$pedido = get_row($sql, [$id]);

// Si el pedido no existe, redirigir
if (!$pedido) {
    header('Location: pedidos.php');
    exit;
}

// Obtener los items del pedido
$sql = "SELECT pi.*, p.nombre, p.sku, p.slug FROM pedido_items pi 
        INNER JOIN productos p ON pi.producto_id = p.id 
        WHERE pi.pedido_id = ?";
$pedido_items = get_rows($sql, [$id]);

// Obtener los estados del pedido
$sql = "SELECT * FROM pedido_estados WHERE pedido_id = ? ORDER BY fecha_creacion ASC";
$pedido_estados = get_rows($sql, [$id]);

// Procesar acción si existe
$action = isset($_GET['action']) ? $_GET['action'] : '';
$success_message = '';
$error_message = '';

// Actualizar código de seguimiento
if ($action == 'update_tracking' && isset($_POST['codigo_seguimiento'])) {
    $codigo_seguimiento = trim($_POST['codigo_seguimiento']);
    
    $sql = "UPDATE pedidos SET codigo_seguimiento = ? WHERE id = ?";
    $result = update($sql, [$codigo_seguimiento, $id]);
    
    if ($result !== false) {
        $success_message = 'Código de seguimiento actualizado correctamente.';
        
        // Actualizar la información del pedido
        $pedido['codigo_seguimiento'] = $codigo_seguimiento;
    } else {
        $error_message = 'Error al actualizar el código de seguimiento.';
    }
}

// Actualizar estado de pedido
if ($action == 'update_status' && isset($_POST['estado'])) {
    $status = $_POST['estado'];
    $notas = isset($_POST['notas']) ? $_POST['notas'] : '';
    
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
            
            $success_message = 'Estado del pedido actualizado a "' . ucfirst($status) . '"';
            
            // Actualizar la información del pedido
            $pedido['estado'] = $status;
            
            // Actualizar historial de estados
            $sql = "SELECT * FROM pedido_estados WHERE pedido_id = ? ORDER BY fecha_creacion ASC";
            $pedido_estados = get_rows($sql, [$id]);
            
        } catch (Exception $e) {
            // Revertir cambios en caso de error
            rollback();
            $error_message = 'Error: ' . $e->getMessage();
        }
    }
}

// Título de la página
$page_title = 'Ver Pedido #' . $id;

// Cargar el header del admin
include_once 'includes/header.php';
?>

<div class="admin-actions">
    <div class="action-header">
        <h2>Detalles del Pedido #<?php echo $id; ?></h2>
        <a href="pedidos.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Volver a Pedidos
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
    
    <div class="order-details-container">
        <div class="order-details-grid">
            <div class="order-summary-box">
                <div class="box-header">
                    <h3>Resumen</h3>
                </div>
                <div class="box-content">
                    <div class="summary-item">
                        <span class="summary-label">Estado:</span>
                        <span class="summary-value">
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
                        </span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="summary-label">Fecha:</span>
                        <span class="summary-value"><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_creacion'])); ?></span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="summary-label">Total:</span>
                        <span class="summary-value"><?php echo formatear_precio($pedido['total']); ?></span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="summary-label">Método de pago:</span>
                        <span class="summary-value"><?php echo ucfirst($pedido['metodo_pago']); ?></span>
                    </div>
                    
                    <div class="summary-item">
                        <span class="summary-label">Código de seguimiento:</span>
                        <span class="summary-value">
                            <?php if (!empty($pedido['codigo_seguimiento'])): ?>
                                <?php echo htmlspecialchars($pedido['codigo_seguimiento']); ?>
                            <?php else: ?>
                                <em>No asignado</em>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
                <div class="box-actions">
                    <button class="btn-modal-trigger" onclick="openStatusModal()">
                        <i class="fas fa-pencil-alt"></i> Cambiar Estado
                    </button>
                    <button class="btn-modal-trigger" onclick="openTrackingModal()">
                        <i class="fas fa-truck"></i> Actualizar Seguimiento
                    </button>
                </div>
            </div>
            
            <div class="customer-info-box">
                <div class="box-header">
                    <h3>Información del Cliente</h3>
                </div>
                <div class="box-content">
                    <div class="info-row">
                        <span class="info-label">Nombre:</span>
                        <span class="info-value"><?php echo htmlspecialchars($pedido['cliente_nombre']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value">
                            <a href="mailto:<?php echo htmlspecialchars($pedido['cliente_email']); ?>">
                                <?php echo htmlspecialchars($pedido['cliente_email']); ?>
                            </a>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Teléfono:</span>
                        <span class="info-value"><?php echo htmlspecialchars($pedido['cliente_telefono']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Documento:</span>
                        <span class="info-value"><?php echo htmlspecialchars($pedido['cliente_documento']); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="shipping-info-box">
                <div class="box-header">
                    <h3>Dirección de Envío</h3>
                </div>
                <div class="box-content">
                    <div class="info-row">
                        <span class="info-label">Dirección:</span>
                        <span class="info-value"><?php echo htmlspecialchars($pedido['cliente_direccion']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Ciudad:</span>
                        <span class="info-value"><?php echo htmlspecialchars($pedido['cliente_ciudad']); ?></span>
                    </div>
                    <?php if (!empty($pedido['cliente_codigo_postal'])): ?>
                        <div class="info-row">
                            <span class="info-label">Código Postal:</span>
                            <span class="info-value"><?php echo htmlspecialchars($pedido['cliente_codigo_postal']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($pedido['notas'])): ?>
                <div class="notes-box">
                    <div class="box-header">
                        <h3>Notas del Pedido</h3>
                    </div>
                    <div class="box-content">
                        <div class="order-notes">
                            <?php echo nl2br(htmlspecialchars($pedido['notas'])); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="order-items-box">
                <div class="box-header">
                    <h3>Productos del Pedido</h3>
                </div>
                <div class="box-content">
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>SKU</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedido_items as $item): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo SITE_URL; ?>/producto/<?php echo $item['slug']; ?>" target="_blank">
                                            <?php echo htmlspecialchars($item['nombre']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['sku']); ?></td>
                                    <td><?php echo formatear_precio($item['precio_unitario']); ?></td>
                                    <td><?php echo $item['cantidad']; ?></td>
                                    <td><?php echo formatear_precio($item['precio_total']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right">Subtotal:</td>
                                <td><?php echo formatear_precio($pedido['subtotal']); ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right">IGV (18%):</td>
                                <td><?php echo formatear_precio($pedido['impuestos']); ?></td>
                            </tr>
                            <tr class="total-row">
                                <td colspan="4" class="text-right">Total:</td>
                                <td><?php echo formatear_precio($pedido['total']); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <div class="order-history-box">
                <div class="box-header">
                    <h3>Historial del Pedido</h3>
                </div>
                <div class="box-content">
                    <div class="order-timeline">
                        <?php foreach ($pedido_estados as $estado): ?>
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="timeline-status"><?php echo ucfirst($estado['estado']); ?></span>
                                        <span class="timeline-date"><?php echo date('d/m/Y H:i', strtotime($estado['fecha_creacion'])); ?></span>
                                    </div>
                                    <?php if (!empty($estado['notas'])): ?>
                                        <div class="timeline-notes">
                                            <?php echo nl2br(htmlspecialchars($estado['notas'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para cambiar estado -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeStatusModal()">&times;</span>
            <h2>Cambiar Estado del Pedido</h2>
            <form action="pedido-ver.php?id=<?php echo $id; ?>&action=update_status" method="post">
                <div class="form-group">
                    <label for="estado">Nuevo Estado:</label>
                    <select id="estado" name="estado" required>
                        <option value="pendiente" <?php echo ($pedido['estado'] == 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="procesando" <?php echo ($pedido['estado'] == 'procesando') ? 'selected' : ''; ?>>Procesando</option>
                        <option value="enviado" <?php echo ($pedido['estado'] == 'enviado') ? 'selected' : ''; ?>>Enviado</option>
                        <option value="entregado" <?php echo ($pedido['estado'] == 'entregado') ? 'selected' : ''; ?>>Entregado</option>
                        <option value="cancelado" <?php echo ($pedido['estado'] == 'cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="notas">Notas:</label>
                    <textarea id="notas" name="notas" rows="3" placeholder="Información adicional sobre el cambio de estado"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeStatusModal()">Cancelar</button>
                    <button type="submit" class="btn-submit">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal para actualizar seguimiento -->
    <div id="trackingModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeTrackingModal()">&times;</span>
            <h2>Actualizar Código de Seguimiento</h2>
            <form action="pedido-ver.php?id=<?php echo $id; ?>&action=update_tracking" method="post">
                <div class="form-group">
                    <label for="codigo_seguimiento">Código de Seguimiento:</label>
                    <input type="text" id="codigo_seguimiento" name="codigo_seguimiento" value="<?php echo htmlspecialchars($pedido['codigo_seguimiento'] ?? ''); ?>" placeholder="Ingrese el código de seguimiento">
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeTrackingModal()">Cancelar</button>
                    <button type="submit" class="btn-submit">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Estilos para la página de detalles de pedido */
.order-details-container {
    margin-bottom: 30px;
}

.order-details-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.order-summary-box,
.customer-info-box,
.shipping-info-box,
.notes-box,
.order-items-box,
.order-history-box {
    background-color: #fff;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.order-items-box,
.order-history-box {
    grid-column: 1 / -1; /* Ocupar todo el ancho disponible */
}

.box-header {
    background-color: #f9f9f9;
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
}

.box-header h3 {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.box-content {
    padding: 20px;
}

.box-actions {
    padding: 15px 20px;
    background-color: #f9f9f9;
    border-top: 1px solid #eee;
    display: flex;
    gap: 10px;
}

/* Estilos para elementos de resumen */
.summary-item {
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.summary-item:last-child {
    margin-bottom: 0;
}

.summary-label {
    font-weight: 500;
    color: #666;
}

.summary-value {
    font-weight: 600;
    color: #333;
}

/* Estilos para información de cliente y envío */
.info-row {
    margin-bottom: 12px;
}

.info-row:last-child {
    margin-bottom: 0;
}

.info-label {
    display: block;
    font-weight: 500;
    color: #666;
    margin-bottom: 5px;
}

.info-value {
    color: #333;
}

.info-value a {
    color: #0049b7;
    text-decoration: none;
}

.info-value a:hover {
    text-decoration: underline;
}

/* Notas del pedido */
.order-notes {
    white-space: pre-line;
    color: #666;
    line-height: 1.5;
}

/* Tabla de items */
.items-table {
    width: 100%;
    border-collapse: collapse;
}

.items-table th,
.items-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.items-table th {
    background-color: #f9f9f9;
    font-weight: 600;
    color: #333;
}

.items-table tbody tr:hover {
    background-color: #f9f9f9;
}

.items-table tfoot td {
    padding: 15px;
    font-weight: 500;
}

.items-table tfoot .total-row {
    font-weight: 700;
    font-size: 16px;
}

.text-right {
    text-align: right;
}

/* Timeline de historial */
.order-timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 25px;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-item::before {
    content: "";
    position: absolute;
    top: 10px;
    left: -30px;
    bottom: -25px;
    width: 1px;
    background-color: #ddd;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-dot {
    position: absolute;
    top: 10px;
    left: -34px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #0049b7;
    z-index: 1;
}

.timeline-content {
    background-color: #f9f9f9;
    border-radius: 5px;
    padding: 15px;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.timeline-status {
    font-weight: 600;
    color: #333;
}

.timeline-date {
    color: #777;
    font-size: 14px;
}

.timeline-notes {
    color: #666;
    font-size: 14px;
    line-height: 1.5;
}

/* Botones de acción */
.btn-modal-trigger {
    padding: 8px 15px;
    background-color: #f5f5f5;
    color: #333;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background-color 0.3s;
}

.btn-modal-trigger:hover {
    background-color: #e0e0e0;
}

/* Modales */
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

/* Responsive */
@media (max-width: 992px) {
    .order-details-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Funciones para gestionar modales
function openStatusModal() {
    document.getElementById('statusModal').style.display = 'block';
}

function closeStatusModal() {
    document.getElementById('statusModal').style.display = 'none';
}

function openTrackingModal() {
    document.getElementById('trackingModal').style.display = 'block';
}

function closeTrackingModal() {
    document.getElementById('trackingModal').style.display = 'none';
}

// Cerrar modales al hacer clic fuera
window.onclick = function(event) {
    const statusModal = document.getElementById('statusModal');
    const trackingModal = document.getElementById('trackingModal');
    
    if (event.target == statusModal) {
        closeStatusModal();
    }
    
    if (event.target == trackingModal) {
        closeTrackingModal();
    }
};
</script>

<?php
// Cargar el footer del admin
include_once 'includes/footer.php';
?>