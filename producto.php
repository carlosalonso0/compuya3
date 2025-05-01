<?php
/**
 * Página de producto con URLs amigables
 */

// Incluir archivos necesarios
require_once 'config.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/functions_categories.php';

// Obtener el slug o ID del producto
$producto_id = null;
$producto_slug = null;

if (isset($_GET['id'])) {
    $producto_id = (int)$_GET['id'];
    $sql = "SELECT slug FROM productos WHERE id = ?";
    $resultado = get_row($sql, [$producto_id]);
    
    // Redireccionar a URL amigable si tenemos la información
    if ($resultado && !empty($resultado['slug'])) {
        header("Location: " . SITE_URL . "/producto/" . $resultado['slug']);
        exit;
    }
} elseif (isset($_GET['slug'])) {
    $producto_slug = $_GET['slug'];
    $sql = "SELECT id FROM productos WHERE slug = ?";
    $resultado = get_row($sql, [$producto_slug]);
    
    if ($resultado) {
        $producto_id = $resultado['id'];
    }
}

// Si no se encontró el producto, mostrar página 404
if (!$producto_id) {
    header("HTTP/1.0 404 Not Found");
    include_once 'error-404.php';
    exit;
}

// Obtener información del producto
$sql = "SELECT p.*, m.nombre as marca_nombre, c.nombre as categoria_nombre, c.slug as categoria_slug 
        FROM productos p
        LEFT JOIN marcas m ON p.marca_id = m.id
        LEFT JOIN categorias c ON p.categoria_id = c.id
        WHERE p.id = ?";

$producto = get_row($sql, [$producto_id]);

// Si no hay producto o no está activo, mostrar 404
if (!$producto || !$producto['activo']) {
    header("HTTP/1.0 404 Not Found");
    include_once 'error-404.php';
    exit;
}

// Obtener especificaciones del producto
$sql = "SELECT * FROM especificaciones_producto WHERE producto_id = ? ORDER BY orden ASC";
$especificaciones = get_rows($sql, [$producto_id]);

// Obtener imágenes del producto
$sql = "SELECT * FROM imagenes_producto WHERE producto_id = ? ORDER BY orden ASC";
$imagenes = get_rows($sql, [$producto_id]);

// Obtener productos relacionados (misma categoría)
$sql = "SELECT p.*, m.nombre as marca_nombre 
        FROM productos p
        LEFT JOIN marcas m ON p.marca_id = m.id
        WHERE p.categoria_id = ? AND p.id != ? AND p.activo = 1
        ORDER BY RAND()
        LIMIT 4";
$productos_relacionados = get_rows($sql, [$producto['categoria_id'], $producto_id]);

// Título de la página
$page_title = $producto['nombre'];

// Cargar el header del sitio
include_once INCLUDES_PATH . '/header.php';
?>

<div class="product-page">
    <div class="new-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="<?php echo SITE_URL; ?>">Inicio</a>
            <span class="separator">/</span>
            <?php if (!empty($producto['categoria_slug']) && !empty($producto['categoria_nombre'])): ?>
                <a href="<?php echo SITE_URL; ?>/categoria/<?php echo $producto['categoria_slug']; ?>"><?php echo htmlspecialchars($producto['categoria_nombre']); ?></a>
                <span class="separator">/</span>
            <?php endif; ?>
            <span class="current"><?php echo htmlspecialchars($producto['nombre']); ?></span>
        </div>
        
        <div class="product-main">
            <div class="product-gallery">
                <?php if (!empty($imagenes)): ?>
                    <div class="product-main-image">
                        <?php 
                        $main_image = get_imagen_producto($producto_id, 'principal');
                        ?>
                        <img src="<?php echo $main_image; ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>" id="main-product-image">
                    </div>
                    
                    <?php if (count($imagenes) > 1): ?>
                        <div class="product-thumbnails">
                            <?php foreach ($imagenes as $imagen): ?>
                                <?php 
                                $thumb_url = get_imagen_producto($producto_id, 'thumbnail', $imagen['id']);
                                $full_url = get_imagen_producto($producto_id, 'principal', $imagen['id']);
                                ?>
                                <div class="thumbnail" data-image="<?php echo $full_url; ?>">
                                    <img src="<?php echo $thumb_url; ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="product-main-image">
                        <img src="<?php echo SITE_IMG_URL; ?>/no-image.webp" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="product-info">
                <h1 class="product-title"><?php echo htmlspecialchars($producto['nombre']); ?></h1>
                
                <?php if (!empty($producto['marca_nombre'])): ?>
                    <div class="product-brand">
                        <span>Marca:</span> <?php echo htmlspecialchars($producto['marca_nombre']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($producto['sku'])): ?>
                    <div class="product-sku">
                        <span>SKU:</span> <?php echo htmlspecialchars($producto['sku']); ?>
                    </div>
                <?php endif; ?>
                
                <div class="product-price-container">
                    <?php if ($producto['en_oferta'] && $producto['precio_oferta'] > 0 && $producto['precio_oferta'] < $producto['precio']): ?>
                        <div class="product-price-original"><?php echo formatear_precio($producto['precio']); ?></div>
                        <div class="product-price"><?php echo formatear_precio($producto['precio_oferta']); ?></div>
                        <?php
                        $descuento = round(($producto['precio'] - $producto['precio_oferta']) / $producto['precio'] * 100);
                        ?>
                        <div class="product-discount">-<?php echo $descuento; ?>%</div>
                    <?php else: ?>
                        <div class="product-price"><?php echo formatear_precio($producto['precio']); ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="product-stock">
                    <?php if ($producto['stock'] > 0): ?>
                        <span class="in-stock">
                            <i class="fas fa-check-circle"></i> En stock (<?php echo $producto['stock']; ?> unidades)
                        </span>
                    <?php else: ?>
                        <span class="out-of-stock">
                            <i class="fas fa-times-circle"></i> Sin stock
                        </span>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($producto['descripcion'])): ?>
                    <div class="product-description">
                        <?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($producto['stock'] > 0): ?>
                    <div class="product-actions">
                        <div class="quantity-selector">
                            <button class="quantity-btn minus">-</button>
                            <input type="number" value="1" min="1" max="<?php echo $producto['stock']; ?>" id="product-quantity">
                            <button class="quantity-btn plus">+</button>
                        </div>
                        <button class="btn-add-cart" data-product-id="<?php echo $producto_id; ?>">
                            <i class="fas fa-shopping-cart"></i> Añadir al carrito
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($especificaciones)): ?>
            <div class="product-specifications">
                <h2>Especificaciones</h2>
                <div class="specs-table">
                    <?php foreach ($especificaciones as $especificacion): ?>
                        <div class="spec-row">
                            <div class="spec-name"><?php echo htmlspecialchars($especificacion['nombre_especificacion']); ?>:</div>
                            <div class="spec-value"><?php echo htmlspecialchars($especificacion['valor_especificacion']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($productos_relacionados)): ?>
            <div class="related-products">
                <h2>Productos relacionados</h2>
                <div class="products-grid">
                    <?php foreach ($productos_relacionados as $producto): ?>
                        <div class="product-card-wrapper">
                            <?php include INCLUDES_PATH . '/components/product-card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Galería de imágenes
    const thumbnails = document.querySelectorAll('.thumbnail');
    const mainImage = document.getElementById('main-product-image');
    
    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            const imgUrl = this.getAttribute('data-image');
            mainImage.src = imgUrl;
            
            // Eliminar clase activa de todos los thumbnails
            thumbnails.forEach(thumb => thumb.classList.remove('active'));
            
            // Agregar clase activa al thumbnail seleccionado
            this.classList.add('active');
        });
    });
    
    // Selectores de cantidad
    const minusBtn = document.querySelector('.quantity-btn.minus');
    const plusBtn = document.querySelector('.quantity-btn.plus');
    const quantityInput = document.getElementById('product-quantity');
    
    if (minusBtn && plusBtn && quantityInput) {
        minusBtn.addEventListener('click', function() {
            let value = parseInt(quantityInput.value);
            if (value > 1) {
                quantityInput.value = value - 1;
            }
        });
        
        plusBtn.addEventListener('click', function() {
            let value = parseInt(quantityInput.value);
            let max = parseInt(quantityInput.getAttribute('max'));
            if (value < max) {
                quantityInput.value = value + 1;
            }
        });
    }
});
</script>

<?php
// Cargar el footer del sitio
include_once INCLUDES_PATH . '/footer.php';
?>