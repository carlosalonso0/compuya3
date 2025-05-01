<?php
/**
 * Página de carrito de compras
 */

// Incluir archivos necesarios
require_once 'config.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/functions_cart.php';

// Procesar acciones del carrito
$action = isset($_GET['action']) ? $_GET['action'] : '';
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$quantity = isset($_GET['cantidad']) ? (int)$_GET['cantidad'] : 1;

$cart_message = '';

// Realizar acciones según petición
$is_ajax = (
    isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
) || (
    isset($_SERVER['HTTP_ACCEPT']) && 
    strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
);

if ($action == 'add' && $product_id > 0) {
    $result = cart_add_product($product_id, $quantity);
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
    $cart_message = $result['success'] ? 'Producto añadido al carrito correctamente.' : 'Error: ' . $result['message'];
} elseif ($action == 'update' && $product_id > 0) {
    $result = cart_update_quantity($product_id, $quantity);
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
    $cart_message = $result['success'] ? 'Carrito actualizado correctamente.' : 'Error: ' . $result['message'];
} elseif ($action == 'remove' && $product_id > 0) {
    $result = cart_remove_product($product_id);
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
    $cart_message = $result['success'] ? 'Producto eliminado del carrito.' : 'Error: ' . $result['message'];
} elseif ($action == 'clear') {
    $result = cart_clear();
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
    $cart_message = 'Carrito vaciado correctamente.';
} elseif ($action == 'count') {
    header('Content-Type: application/json');
    echo json_encode(['count' => cart_count()]);
    exit;
}

// Obtener contenido del carrito
$cart_items = cart_get_items();
$cart_summary = cart_get_summary();

// Título de la página
$page_title = "Carrito de Compras";

// Cargar el header del sitio
include_once INCLUDES_PATH . '/header.php';
?>

<div class="cart-page">
    <div class="new-container">
        <div class="page-header">
            <h1 class="page-title">Carrito de Compras</h1>
        </div>
        
        <?php if (!empty($cart_message)): ?>
            <div class="alert alert-info">
                <?php echo $cart_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2>Tu carrito está vacío</h2>
                <p>Parece que aún no has añadido productos a tu carrito.</p>
                <a href="<?php echo SITE_URL; ?>" class="btn-continue-shopping">Continuar comprando</a>
            </div>
        <?php else: ?>
            <div class="cart-container">
                <div class="cart-items">
                    <div class="cart-header">
                        <div class="cart-col product-col">Producto</div>
                        <div class="cart-col price-col">Precio</div>
                        <div class="cart-col quantity-col">Cantidad</div>
                        <div class="cart-col subtotal-col">Subtotal</div>
                        <div class="cart-col actions-col">Acciones</div>
                    </div>
                    
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <div class="cart-col product-col">
                                <div class="product-info">
                                    <div class="product-image">
                                        <img src="<?php echo get_imagen_producto($item['id']); ?>" alt="<?php echo htmlspecialchars($item['nombre']); ?>">
                                    </div>
                                    <div class="product-details">
                                        <h3 class="product-title">
                                            <a href="<?php echo SITE_URL; ?>/producto/<?php echo $item['slug']; ?>">
                                                <?php echo htmlspecialchars($item['nombre']); ?>
                                            </a>
                                        </h3>
                                        <?php if (!empty($item['sku'])): ?>
                                            <div class="product-sku">SKU: <?php echo htmlspecialchars($item['sku']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="cart-col price-col">
                                <?php if ($item['precio_oferta'] > 0 && $item['en_oferta']): ?>
                                    <div class="product-price">
                                        <span class="price"><?php echo formatear_precio($item['precio_oferta']); ?></span>
                                        <span class="original-price"><?php echo formatear_precio($item['precio']); ?></span>
                                    </div>
                                <?php else: ?>
                                    <div class="product-price">
                                        <span class="price"><?php echo formatear_precio($item['precio']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="cart-col quantity-col">
                                <div class="quantity-selector">
                                    <button class="quantity-btn minus" data-id="<?php echo $item['id']; ?>">-</button>
                                    <input type="number" min="1" max="<?php echo $item['stock']; ?>" value="<?php echo $item['quantity']; ?>" 
                                           id="quantity-<?php echo $item['id']; ?>" class="quantity-input" data-id="<?php echo $item['id']; ?>">
                                    <button class="quantity-btn plus" data-id="<?php echo $item['id']; ?>">+</button>
                                </div>
                                <div class="stock-info">
                                    <?php echo $item['stock']; ?> disponibles
                                </div>
                            </div>
                            
                            <div class="cart-col subtotal-col">
                                <span class="subtotal"><?php echo formatear_precio($item['subtotal']); ?></span>
                            </div>
                            
                            <div class="cart-col actions-col">
                                <a href="<?php echo SITE_URL; ?>/carrito.php?action=remove&id=<?php echo $item['id']; ?>" 
                                   class="btn-remove" title="Eliminar item">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-actions">
                    <a href="<?php echo SITE_URL; ?>" class="btn-continue-shopping">
                        <i class="fas fa-arrow-left"></i> Continuar comprando
                    </a>
                    <a href="<?php echo SITE_URL; ?>/carrito.php?action=clear" class="btn-clear-cart">
                        <i class="fas fa-trash"></i> Vaciar carrito
                    </a>
                </div>
                
                <div class="cart-summary">
                    <h2>Resumen del pedido</h2>
                    
                    <div class="summary-row">
                        <span class="summary-label">Subtotal:</span>
                        <span class="summary-value"><?php echo formatear_precio($cart_summary['subtotal']); ?></span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="summary-label">IGV (18%):</span>
                        <span class="summary-value"><?php echo formatear_precio($cart_summary['tax']); ?></span>
                    </div>
                    
                    <div class="summary-row total">
                        <span class="summary-label">Total:</span>
                        <span class="summary-value"><?php echo formatear_precio($cart_summary['total']); ?></span>
                    </div>
                    
                    <a href="<?php echo SITE_URL; ?>/checkout.php" class="btn-checkout">
                        Proceder al pago <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar botones de cantidad
    const minusBtns = document.querySelectorAll('.quantity-btn.minus');
    const plusBtns = document.querySelectorAll('.quantity-btn.plus');
    const quantityInputs = document.querySelectorAll('.quantity-input');
    
    // Actualizar cantidad al hacer clic en menos
    minusBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.id;
            const input = document.getElementById('quantity-' + productId);
            let value = parseInt(input.value);
            
            if (value > 1) {
                value--;
                input.value = value;
                updateCartItem(productId, value);
            }
        });
    });
    
    // Actualizar cantidad al hacer clic en más
    plusBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.id;
            const input = document.getElementById('quantity-' + productId);
            let value = parseInt(input.value);
            let max = parseInt(input.getAttribute('max'));
            
            if (value < max) {
                value++;
                input.value = value;
                updateCartItem(productId, value);
            }
        });
    });
    
    // Actualizar cantidad al cambiar el valor del input
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.dataset.id;
            let value = parseInt(this.value);
            let max = parseInt(this.getAttribute('max'));
            
            // Validar rango
            if (value < 1) value = 1;
            if (value > max) value = max;
            
            this.value = value;
            updateCartItem(productId, value);
        });
    });
    
    // Función para actualizar cantidad en el carrito
    function updateCartItem(productId, quantity) {
        window.location.href = '<?php echo SITE_URL; ?>/carrito.php?action=update&id=' + productId + '&cantidad=' + quantity;
    }
});
</script>

<?php
// Cargar el footer del sitio
include_once INCLUDES_PATH . '/footer.php';
?>