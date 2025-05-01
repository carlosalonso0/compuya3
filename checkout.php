<?php
/**
 * Página de checkout para finalizar la compra
 */

// Incluir archivos necesarios
require_once 'config.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/functions_cart.php';

// Verificar si hay productos en el carrito
$cart_items = cart_get_items();
if (empty($cart_items)) {
    // Redirigir al carrito si está vacío
    header('Location: ' . SITE_URL . '/carrito.php');
    exit;
}

// Variables para mensajes
$success_message = '';
$error_message = '';
$order_id = 0;

// Procesar formulario de pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $cliente_nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $cliente_email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $cliente_telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
    $cliente_documento = isset($_POST['documento']) ? trim($_POST['documento']) : '';
    $cliente_direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : '';
    $cliente_ciudad = isset($_POST['ciudad']) ? trim($_POST['ciudad']) : '';
    $cliente_codigo_postal = isset($_POST['codigo_postal']) ? trim($_POST['codigo_postal']) : '';
    $metodo_pago = isset($_POST['metodo_pago']) ? trim($_POST['metodo_pago']) : '';
    $notas = isset($_POST['notas']) ? trim($_POST['notas']) : '';
    
    // Validación básica
    if (empty($cliente_nombre)) {
        $error_message = 'Por favor, ingresa tu nombre completo.';
    } elseif (empty($cliente_email) || !filter_var($cliente_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Por favor, ingresa un correo electrónico válido.';
    } elseif (empty($cliente_telefono)) {
        $error_message = 'Por favor, ingresa tu número de teléfono.';
    } elseif (empty($cliente_documento)) {
        $error_message = 'Por favor, ingresa tu documento de identidad.';
    } elseif (empty($cliente_direccion)) {
        $error_message = 'Por favor, ingresa tu dirección de entrega.';
    } elseif (empty($cliente_ciudad)) {
        $error_message = 'Por favor, ingresa tu ciudad.';
    } elseif (empty($metodo_pago)) {
        $error_message = 'Por favor, selecciona un método de pago.';
    } else {
        // Obtener resumen del carrito
        $cart_summary = cart_get_summary();
        
        try {
            // Iniciar transacción
            begin_transaction();
            
            // Insertar datos del pedido
            $sql = "INSERT INTO pedidos (
                    cliente_nombre, cliente_email, cliente_telefono, cliente_documento, 
                    cliente_direccion, cliente_ciudad, cliente_codigo_postal, 
                    total, subtotal, impuestos, estado, metodo_pago, notas
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', ?, ?)";
                
            $params = [
                $cliente_nombre, $cliente_email, $cliente_telefono, $cliente_documento,
                $cliente_direccion, $cliente_ciudad, $cliente_codigo_postal,
                $cart_summary['total'], $cart_summary['subtotal'], $cart_summary['tax'],
                $metodo_pago, $notas
            ];
            
            $order_id = insert($sql, $params);
            
            if (!$order_id) {
                throw new Exception("Error al crear el pedido.");
            }
            
            // Insertar los productos del pedido
            foreach ($cart_items as $item) {
                $precio_unitario = $item['en_oferta'] && $item['precio_oferta'] > 0 ? $item['precio_oferta'] : $item['precio'];
                $precio_total = $precio_unitario * $item['quantity'];
                
                $sql = "INSERT INTO pedido_items (
                        pedido_id, producto_id, cantidad, precio_unitario, precio_total
                    ) VALUES (?, ?, ?, ?, ?)";
                    
                $params = [
                    $order_id, $item['id'], $item['quantity'], $precio_unitario, $precio_total
                ];
                
                $result = insert($sql, $params);
                
                if (!$result) {
                    throw new Exception("Error al guardar los productos del pedido.");
                }
                
                // Actualizar stock del producto
                $nuevo_stock = $item['stock'] - $item['quantity'];
                if ($nuevo_stock < 0) $nuevo_stock = 0;
                
                $sql = "UPDATE productos SET stock = ? WHERE id = ?";
                update($sql, [$nuevo_stock, $item['id']]);
            }
            
            // Registrar estado inicial del pedido
            $sql = "INSERT INTO pedido_estados (pedido_id, estado, notas) VALUES (?, 'pendiente', 'Pedido creado')";
            insert($sql, [$order_id]);
            
            // Confirmar transacción
            commit();
            
            // Limpiar carrito después de procesar con éxito
            cart_clear();
            
            // Redireccionar a página de confirmación
            header("Location: " . SITE_URL . "/confirmacion-pedido.php?id=" . $order_id);
            exit;
            
        } catch (Exception $e) {
            // Revertir cambios en caso de error
            rollback();
            $error_message = 'Error al procesar el pedido: ' . $e->getMessage();
        }
    }
}

// Obtener resumen del carrito
$cart_summary = cart_get_summary();

// Título de la página
$page_title = "Finalizar Compra";

// Cargar el header del sitio
include_once INCLUDES_PATH . '/header.php';
?>

<div class="checkout-page">
    <div class="new-container">
        <div class="page-header">
            <h1 class="page-title">Finalizar Compra</h1>
        </div>
        
        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="checkout-container">
            <div class="checkout-form-container">
                <form method="post" action="" class="checkout-form">
                    <div class="form-section">
                        <h2 class="section-title">Datos Personales</h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombre">Nombre Completo <span class="required">*</span></label>
                                <input type="text" id="nombre" name="nombre" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Correo Electrónico <span class="required">*</span></label>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="telefono">Teléfono <span class="required">*</span></label>
                                <input type="tel" id="telefono" name="telefono" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="documento">DNI/RUC <span class="required">*</span></label>
                                <input type="text" id="documento" name="documento" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h2 class="section-title">Datos de Envío</h2>
                        
                        <div class="form-group full-width">
                            <label for="direccion">Dirección de Entrega <span class="required">*</span></label>
                            <input type="text" id="direccion" name="direccion" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="ciudad">Ciudad <span class="required">*</span></label>
                                <input type="text" id="ciudad" name="ciudad" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="codigo_postal">Código Postal</label>
                                <input type="text" id="codigo_postal" name="codigo_postal">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h2 class="section-title">Método de Pago</h2>
                        
                        <div class="payment-methods">
                            <div class="payment-method">
                                <input type="radio" id="transferencia" name="metodo_pago" value="transferencia">
                                <label for="transferencia">
                                    <span class="payment-icon"><i class="fas fa-university"></i></span>
                                    <span class="payment-name">Transferencia Bancaria</span>
                                </label>
                            </div>
                            
                            <div class="payment-method">
                                <input type="radio" id="efectivo" name="metodo_pago" value="efectivo">
                                <label for="efectivo">
                                    <span class="payment-icon"><i class="fas fa-money-bill-wave"></i></span>
                                    <span class="payment-name">Pago en Efectivo</span>
                                </label>
                            </div>
                            
                            <div class="payment-method">
                                <input type="radio" id="yape" name="metodo_pago" value="yape">
                                <label for="yape">
                                    <span class="payment-icon"><i class="fas fa-mobile-alt"></i></span>
                                    <span class="payment-name">Yape</span>
                                </label>
                            </div>
                            
                            <div class="payment-method">
                                <input type="radio" id="plin" name="metodo_pago" value="plin">
                                <label for="plin">
                                    <span class="payment-icon"><i class="fas fa-mobile-alt"></i></span>
                                    <span class="payment-name">Plin</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h2 class="section-title">Comentarios Adicionales</h2>
                        
                        <div class="form-group full-width">
                            <label for="notas">Notas del Pedido</label>
                            <textarea id="notas" name="notas" rows="4"></textarea>
                            <small>Instrucciones especiales para la entrega, referencias, etc.</small>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="<?php echo SITE_URL; ?>/carrito.php" class="btn-back-to-cart">
                            <i class="fas fa-arrow-left"></i> Volver al Carrito
                        </a>
                        <button type="submit" class="btn-place-order">
                            Confirmar Pedido <i class="fas fa-check"></i>
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="checkout-summary">
                <h2 class="summary-title">Resumen del Pedido</h2>
                
                <div class="products-list">
                    <?php foreach($cart_items as $item): ?>
                        <div class="product-summary-item">
                            <div class="product-summary-image">
                                <img src="<?php echo get_imagen_producto($item['id']); ?>" alt="<?php echo htmlspecialchars($item['nombre']); ?>">
                            </div>
                            <div class="product-summary-details">
                                <h3 class="product-summary-name"><?php echo htmlspecialchars($item['nombre']); ?></h3>
                                <div class="product-summary-price">
                                    <?php if ($item['en_oferta'] && $item['precio_oferta'] > 0): ?>
                                        <?php echo formatear_precio($item['precio_oferta']); ?> x <?php echo $item['quantity']; ?>
                                    <?php else: ?>
                                        <?php echo formatear_precio($item['precio']); ?> x <?php echo $item['quantity']; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="product-summary-total">
                                <?php echo formatear_precio($item['subtotal']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-summary">
                    <div class="summary-row">
                        <span class="summary-label">Subtotal:</span>
                        <span class="summary-value"><?php echo formatear_precio($cart_summary['subtotal']); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="summary-label">IGV (18%):</span>
                        <span class="summary-value"><?php echo formatear_precio($cart_summary['tax']); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="summary-label">Envío:</span>
                        <span class="summary-value">Gratis</span>
                    </div>
                    
                    <div class="summary-row total">
                        <span class="summary-label">Total:</span>
                        <span class="summary-value"><?php echo formatear_precio($cart_summary['total']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Cargar el footer del sitio
include_once INCLUDES_PATH . '/footer.php';
?>