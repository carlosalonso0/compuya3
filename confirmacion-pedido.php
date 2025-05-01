<?php
/**
 * Página de confirmación de pedido
 */

// Incluir archivos necesarios
require_once 'config.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/functions_cart.php';

// Verificar que se proporcione un ID
if (!isset($_GET['id'])) {
    // Redirigir si no hay ID
    header('Location: ' . SITE_URL);
    exit;
}

$pedido_id = (int)$_GET['id'];

// Obtener información del pedido
$sql = "SELECT * FROM pedidos WHERE id = ?";
$pedido = get_row($sql, [$pedido_id]);

// Si el pedido no existe, redirigir
if (!$pedido) {
    header('Location: ' . SITE_URL);
    exit;
}

// Obtener los items del pedido
$sql = "SELECT pi.*, p.nombre, p.sku FROM pedido_items pi 
        INNER JOIN productos p ON pi.producto_id = p.id 
        WHERE pi.pedido_id = ?";
$pedido_items = get_rows($sql, [$pedido_id]);

// Obtener los estados del pedido
$sql = "SELECT * FROM pedido_estados WHERE pedido_id = ? ORDER BY fecha_creacion ASC";
$pedido_estados = get_rows($sql, [$pedido_id]);

// Título de la página
$page_title = "Confirmación de Pedido #" . $pedido_id;

// Cargar el header del sitio
include_once INCLUDES_PATH . '/header.php';
?>

<div class="confirmation-page">
    <div class="new-container">
        <div class="page-header">
            <h1 class="page-title">Pedido Confirmado</h1>
        </div>
        
        <div class="confirmation-container">
            <div class="confirmation-message">
                <div class="confirmation-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>¡Gracias por tu compra!</h2>
                <p>Tu pedido #<?php echo $pedido_id; ?> ha sido recibido y está siendo procesado.</p>
                <p>Hemos enviado un correo electrónico de confirmación a <strong><?php echo htmlspecialchars($pedido['cliente_email']); ?></strong> con los detalles de tu pedido.</p>
            </div>
            
            <div class="order-details">
                <h3>Resumen del Pedido</h3>
                
                <div class="order-info">
                    <div class="order-info-row">
                        <span class="order-info-label">Número de Pedido:</span>
                        <span class="order-info-value">#<?php echo $pedido_id; ?></span>
                    </div>
                    
                    <div class="order-info-row">
                        <span class="order-info-label">Fecha:</span>
                        <span class="order-info-value"><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_creacion'])); ?></span>
                    </div>
                    
                    <div class="order-info-row">
                        <span class="order-info-label">Estado:</span>
                        <span class="order-info-value order-status"><?php echo ucfirst($pedido['estado']); ?></span>
                    </div>
                    
                    <div class="order-info-row">
                        <span class="order-info-label">Total:</span>
                        <span class="order-info-value order-total"><?php echo formatear_precio($pedido['total']); ?></span>
                    </div>
                    
                    <div class="order-info-row">
                        <span class="order-info-label">Método de Pago:</span>
                        <span class="order-info-value"><?php echo ucfirst($pedido['metodo_pago']); ?></span>
                    </div>
                </div>
                
                <div class="divider"></div>
                
                <h3>Productos</h3>
                
                <div class="order-products">
                    <?php foreach ($pedido_items as $item): ?>
                        <div class="order-product">
                            <div class="product-info">
                                <h4 class="product-name"><?php echo htmlspecialchars($item['nombre']); ?></h4>
                                <div class="product-meta">SKU: <?php echo htmlspecialchars($item['sku']); ?></div>
                            </div>
                            <div class="product-quantity">
                                <?php echo $item['cantidad']; ?> x <?php echo formatear_precio($item['precio_unitario']); ?>
                            </div>
                            <div class="product-total">
                                <?php echo formatear_precio($item['precio_total']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-summary">
                    <div class="summary-row">
                        <span class="summary-label">Subtotal:</span>
                        <span class="summary-value"><?php echo formatear_precio($pedido['subtotal']); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="summary-label">IGV (18%):</span>
                        <span class="summary-value"><?php echo formatear_precio($pedido['impuestos']); ?></span>
                    </div>
                    
                    <div class="summary-row total">
                        <span class="summary-label">Total:</span>
                        <span class="summary-value"><?php echo formatear_precio($pedido['total']); ?></span>
                    </div>
                </div>
                
                <div class="divider"></div>
                
                <h3>Información de Envío</h3>
                
                <div class="shipping-info">
                    <div class="info-column">
                        <h4>Datos de Contacto</h4>
                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($pedido['cliente_nombre']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($pedido['cliente_email']); ?></p>
                        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($pedido['cliente_telefono']); ?></p>
                        <p><strong>Documento:</strong> <?php echo htmlspecialchars($pedido['cliente_documento']); ?></p>
                    </div>
                    
                    <div class="info-column">
                        <h4>Dirección de Entrega</h4>
                        <p><?php echo htmlspecialchars($pedido['cliente_direccion']); ?></p>
                        <p><?php echo htmlspecialchars($pedido['cliente_ciudad']); ?></p>
                        <?php if (!empty($pedido['cliente_codigo_postal'])): ?>
                            <p>Código Postal: <?php echo htmlspecialchars($pedido['cliente_codigo_postal']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!empty($pedido['notas'])): ?>
                    <div class="divider"></div>
                    
                    <h3>Notas</h3>
                    <div class="order-notes">
                        <?php echo nl2br(htmlspecialchars($pedido['notas'])); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($pedido_estados)): ?>
                    <div class="divider"></div>
                    
                    <h3>Seguimiento del Pedido</h3>
                    <div class="order-timeline">
                        <?php foreach ($pedido_estados as $index => $estado): ?>
                            <div class="timeline-item <?php echo ($index == count($pedido_estados) - 1) ? 'active' : ''; ?>">
                                <div class="timeline-icon">
                                    <i class="fas fa-circle"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-date">
                                        <?php echo date('d/m/Y H:i', strtotime($estado['fecha_creacion'])); ?>
                                    </div>
                                    <div class="timeline-status">
                                        <?php echo ucfirst($estado['estado']); ?>
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
                <?php endif; ?>
                
                <div class="confirmation-actions">
                    <a href="<?php echo SITE_URL; ?>" class="btn-continue-shopping">
                        <i class="fas fa-arrow-left"></i> Volver a la Tienda
                    </a>
                    <a href="#" onclick="window.print(); return false;" class="btn-print-order">
                        <i class="fas fa-print"></i> Imprimir Pedido
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Cargar el footer del sitio
include_once INCLUDES_PATH . '/footer.php';
?>