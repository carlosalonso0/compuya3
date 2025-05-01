<?php
/**
 * Funciones para el manejo del carrito de compras
 */

/**
 * Inicializa el carrito de compras si no existe en la sesión
 */
function cart_initialize() {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

/**
 * Añade un producto al carrito
 * 
 * @param int $product_id ID del producto
 * @param int $quantity Cantidad a añadir
 * @return array Resultado de la operación
 */
function cart_add_product($product_id, $quantity = 1) {
    cart_initialize();
    
    // Validar que la cantidad sea positiva
    if ($quantity <= 0) {
        return [
            'success' => false,
            'message' => 'La cantidad debe ser mayor a cero.'
        ];
    }
    
    // Obtener información del producto
    $sql = "SELECT * FROM productos WHERE id = ? AND activo = 1";
    $producto = get_row($sql, [$product_id]);
    
    if (!$producto) {
        return [
            'success' => false,
            'message' => 'El producto no existe o no está disponible.'
        ];
    }
    
    // Verificar stock
    if ($producto['stock'] <= 0) {
        return [
            'success' => false,
            'message' => 'El producto está agotado.'
        ];
    }
    
    // Verificar si el producto ya está en el carrito
    if (isset($_SESSION['cart'][$product_id])) {
        // Sumar la cantidad
        $new_quantity = $_SESSION['cart'][$product_id] + $quantity;
        
        // Verificar que no exceda el stock disponible
        if ($new_quantity > $producto['stock']) {
            $new_quantity = $producto['stock'];
        }
        
        $_SESSION['cart'][$product_id] = $new_quantity;
    } else {
        // Añadir nuevo producto al carrito
        $_SESSION['cart'][$product_id] = min($quantity, $producto['stock']);
    }
    
    return [
        'success' => true,
        'message' => 'Producto añadido al carrito.',
        'cart_count' => cart_count()
    ];
}

/**
 * Actualiza la cantidad de un producto en el carrito
 * 
 * @param int $product_id ID del producto
 * @param int $quantity Nueva cantidad
 * @return array Resultado de la operación
 */
function cart_update_quantity($product_id, $quantity) {
    cart_initialize();
    
    // Validar que la cantidad sea positiva
    if ($quantity <= 0) {
        return [
            'success' => false,
            'message' => 'La cantidad debe ser mayor a cero.'
        ];
    }
    
    // Verificar que el producto exista en el carrito
    if (!isset($_SESSION['cart'][$product_id])) {
        return [
            'success' => false,
            'message' => 'El producto no está en el carrito.'
        ];
    }
    
    // Obtener información del producto
    $sql = "SELECT * FROM productos WHERE id = ? AND activo = 1";
    $producto = get_row($sql, [$product_id]);
    
    if (!$producto) {
        return [
            'success' => false,
            'message' => 'El producto no existe o no está disponible.'
        ];
    }
    
    // Verificar stock
    if ($quantity > $producto['stock']) {
        $quantity = $producto['stock'];
    }
    
    // Actualizar cantidad
    $_SESSION['cart'][$product_id] = $quantity;
    
    return [
        'success' => true,
        'message' => 'Carrito actualizado.',
        'cart_count' => cart_count()
    ];
}

/**
 * Elimina un producto del carrito
 * 
 * @param int $product_id ID del producto
 * @return array Resultado de la operación
 */
function cart_remove_product($product_id) {
    cart_initialize();
    
    // Verificar que el producto exista en el carrito
    if (!isset($_SESSION['cart'][$product_id])) {
        return [
            'success' => false,
            'message' => 'El producto no está en el carrito.'
        ];
    }
    
    // Eliminar producto
    unset($_SESSION['cart'][$product_id]);
    
    return [
        'success' => true,
        'message' => 'Producto eliminado del carrito.',
        'cart_count' => cart_count()
    ];
}

/**
 * Vacía completamente el carrito
 * 
 * @return array Resultado de la operación
 */
function cart_clear() {
    cart_initialize();
    
    $_SESSION['cart'] = [];
    
    return [
        'success' => true,
        'message' => 'Carrito vaciado correctamente.',
        'cart_count' => 0
    ];
}

/**
 * Obtiene el número total de productos en el carrito
 * 
 * @return int Número de productos
 */
function cart_count() {
    cart_initialize();
    
    $count = 0;
    foreach ($_SESSION['cart'] as $quantity) {
        $count += $quantity;
    }
    
    return $count;
}

/**
 * Obtiene todos los productos en el carrito con su información completa
 * 
 * @return array Lista de productos en el carrito
 */
function cart_get_items() {
    cart_initialize();
    
    $items = [];
    
    if (empty($_SESSION['cart'])) {
        return $items;
    }
    
    // Obtener los IDs de productos
    $product_ids = array_keys($_SESSION['cart']);
    
    if (empty($product_ids)) {
        return $items;
    }
    
    // Crear placeholders para la consulta IN
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    
    // Obtener información de los productos
    $sql = "SELECT * FROM productos WHERE id IN ($placeholders) AND activo = 1";
    $productos = get_rows($sql, $product_ids);
    
    // Formatear datos
    foreach ($productos as $producto) {
        $id = $producto['id'];
        $quantity = $_SESSION['cart'][$id];
        
        // Calcular subtotal según precio regular u oferta
        $precio_unitario = $producto['precio'];
        if ($producto['en_oferta'] && $producto['precio_oferta'] > 0 && $producto['precio_oferta'] < $producto['precio']) {
            $precio_unitario = $producto['precio_oferta'];
        }
        
        $subtotal = $precio_unitario * $quantity;
        
        $items[] = array_merge($producto, [
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ]);
    }
    
    return $items;
}

/**
 * Calcula el resumen del carrito (subtotal, impuestos, total)
 * 
 * @return array Resumen del carrito
 */
function cart_get_summary() {
    $items = cart_get_items();
    
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['subtotal'];
    }
    
    // Calcular IGV (18% en Perú)
    $tax_rate = 0.18;
    $tax = $subtotal * $tax_rate;
    
    // Calcular el total
    $total = $subtotal + $tax;
    
    return [
        'subtotal' => $subtotal,
        'tax_rate' => $tax_rate,
        'tax' => $tax,
        'total' => $total,
        'items_count' => count($items)
    ];
}