<?php
/**
 * Tarjeta de producto en oferta especial
 * 
 * @param array $producto Datos del producto
 * @param array $oferta Datos de la oferta especial
 */

// Si no se ha pasado un producto, salir
if (empty($producto) || empty($oferta)) {
    return;
}

// Obtener la URL de la imagen
$imagen_url = get_imagen_producto($producto['id'], 'principal');

// Preparar los datos del producto
$id = $producto['id'];
$nombre = $producto['nombre'];
$precio = $producto['precio'];
$precio_oferta = $producto['precio_oferta'];
$stock = $producto['stock'];
$marca = !empty($producto['marca_id']) ? obtener_marca($producto['marca_id']) : '';
$marca_nombre = !empty($marca) ? $marca['nombre'] : '';
$url_producto = SITE_URL . '/producto.php?id=' . $id;

// Datos específicos de la oferta
$fecha_fin = $oferta['fecha_fin'];
$tiempo_restante = calcular_tiempo_restante($fecha_fin);

// Calcular porcentaje de descuento
$porcentaje_descuento = 0;
if ($precio > 0 && $precio_oferta > 0) {
    $porcentaje_descuento = round(($precio - $precio_oferta) / $precio * 100);
}

// Formatear precios
$precio_formateado = formatear_precio($precio);
$precio_oferta_formateado = formatear_precio($precio_oferta);

// Determinar stock
$tiene_stock = $stock > 0;
$texto_stock = $tiene_stock ? $stock . ' en Stock' : 'Agotado';
?>

<div class="static-offer">
    <div class="static-offer-image">
        <img src="<?php echo $imagen_url; ?>" alt="<?php echo htmlspecialchars($nombre); ?>">
    </div>
    <div class="static-offer-content">
        <h4 class="static-offer-title">
            <a href="<?php echo $url_producto; ?>"><?php echo htmlspecialchars($nombre); ?></a>
        </h4>
        <div class="static-offer-price">
            <?php echo $precio_oferta_formateado; ?>
            <span style="text-decoration: line-through; color: #999; font-size: 16px;">
                <?php echo $precio_formateado; ?>
            </span>
        </div>
        <div class="static-offer-timer">
            Termina en: <?php echo $tiempo_restante; ?>
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
    
    <?php if ($porcentaje_descuento > 0): ?>
        <div class="offer-badge">-<?php echo $porcentaje_descuento; ?>%</div>
    <?php endif; ?>
</div>