<?php
/**
 * Tarjeta de producto estándar
 * 
 * @param array $producto Datos del producto
 */

// Si no se ha pasado un producto, salir
if (empty($producto)) {
    return;
}

// Obtener la URL de la imagen
$imagen_url = get_imagen_producto($producto['id']);

// Preparar los datos del producto
$id = $producto['id'];
$nombre = $producto['nombre'];
$precio = $producto['precio'];
$precio_oferta = $producto['precio_oferta'];
$en_oferta = $producto['en_oferta'];
$stock = $producto['stock'];
$marca = !empty($producto['marca_id']) ? obtener_marca($producto['marca_id']) : '';
$marca_nombre = !empty($marca) ? $marca['nombre'] : '';
$url_producto = SITE_URL . '/producto.php?id=' . $id;

// Determinar si mostrar etiqueta de promoción
$mostrar_promocion = $en_oferta && $precio_oferta > 0 && $precio_oferta < $precio;

// Formatear precios
$precio_formateado = formatear_precio($precio);
$precio_oferta_formateado = $mostrar_promocion ? formatear_precio($precio_oferta) : '';

// Determinar stock
$tiene_stock = $stock > 0;
$texto_stock = $tiene_stock ? $stock . ' en Stock' : 'Agotado';
$clase_stock = $tiene_stock ? 'stock-disponible' : 'sin-stock';
?>


<div class="product-card">
    <div class="card-header">
        <span class="reference-text">*Imagen referencial</span>
        <span class="brand-name"><?php echo htmlspecialchars($marca_nombre); ?></span>
    </div>
    
    <a href="<?php echo $url_producto; ?>" class="product-image-container">
        <img src="<?php echo $imagen_url; ?>" alt="<?php echo htmlspecialchars($nombre); ?>" class="product-image">
    </a>
    
    <div class="separator">
        <span class="social-tag">@computiendapy</span>
    </div>
    
    <div class="product-info">
        <h3 class="product-name">
            <a href="<?php echo $url_producto; ?>"><?php echo htmlspecialchars($nombre); ?></a>
        </h3>
        
        <div class="price-container">
            <?php if ($mostrar_promocion): ?>
                <span class="current-price"><?php echo $precio_oferta_formateado; ?></span>
                <span class="original-price"><?php echo $precio_formateado; ?></span>
            <?php else: ?>
                <span class="current-price"><?php echo $precio_formateado; ?></span>
            <?php endif; ?>
        </div>
        
        <?php if ($mostrar_promocion): ?>
            <div class="promo-tag">Promoción</div>
        <?php endif; ?>
        
        <div class="stock-info">
            Stock: <span class="stock-number <?php echo $clase_stock; ?>"><?php echo $texto_stock; ?></span>
        </div>
        
        <?php if ($tiene_stock): ?>
            <a href="<?php echo SITE_URL; ?>/carrito.php?action=add&id=<?php echo $id; ?>" class="btn-add-cart">
                <i class="fas fa-shopping-cart"></i> Añadir al carrito
            </a>
        <?php else: ?>
            <button class="btn-add-cart disabled" disabled>
                <i class="fas fa-times"></i> Agotado
            </button>
        <?php endif; ?>
    </div>
</div>