<?php
/**
 * Panel de administración - Añadir producto a carrusel
 */

// Incluir archivos necesarios
require_once '../config.php';
require_once INCLUDES_PATH . '/functions.php';

// Verificar que se proporcione un ID de carrusel
if (!isset($_GET['carousel_id'])) {
    // Redirigir si no hay ID de carrusel
    header('Location: carousels.php');
    exit;
}

$carrusel_id = (int)$_GET['carousel_id'];

// Obtener información del carrusel
$sql = "SELECT * FROM carruseles WHERE id = ?";
$carrusel = get_row($sql, [$carrusel_id]);

// Si el carrusel no existe, redirigir
if (!$carrusel) {
    header('Location: carousels.php');
    exit;
}

// Obtener el tipo de carrusel
$tipo_carrusel = $carrusel['tipo'];
if ($tipo_carrusel !== 'producto') {
    $error = 'Este carrusel no acepta productos.';
}

// Variables para mensajes
$success_message = '';
$error_message = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['producto_id'])) {
    $producto_id = (int)$_POST['producto_id'];
    
    // Verificar que el producto exista
    $sql = "SELECT id FROM productos WHERE id = ?";
    $producto = get_row($sql, [$producto_id]);
    
    if (!$producto) {
        $error_message = 'Producto no encontrado.';
    } else {
        // Verificar si el producto ya está en el carrusel
        $sql = "SELECT id FROM carrusel_productos WHERE carrusel_id = ? AND producto_id = ?";
        $existente = get_row($sql, [$carrusel_id, $producto_id]);
        
        if ($existente) {
            $error_message = 'Este producto ya está añadido al carrusel.';
        } else {
            // Obtener el siguiente orden
            $sql = "SELECT COUNT(*) as total FROM carrusel_productos WHERE carrusel_id = ?";
            $result = get_row($sql, [$carrusel_id]);
            $orden = $result ? ($result['total'] + 1) : 1;
            
            // Añadir producto al carrusel
            $sql = "INSERT INTO carrusel_productos (carrusel_id, producto_id, orden, activo) VALUES (?, ?, ?, 1)";
            $resultado = insert($sql, [$carrusel_id, $producto_id, $orden]);
            
            if ($resultado) {
                $success_message = 'Producto añadido correctamente al carrusel.';
            } else {
                $error_message = 'Error al añadir el producto al carrusel.';
            }
        }
    }
}

// Título de la página
$page_title = 'Añadir Producto a Carrusel: ' . $carrusel['nombre'];

// Obtener lista de productos disponibles
$productos_sql = "SELECT p.*, c.nombre as categoria_nombre, m.nombre as marca_nombre 
                FROM productos p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN marcas m ON p.marca_id = m.id
                WHERE p.activo = 1
                ORDER BY p.nombre";
$productos = get_rows($productos_sql);

// Cargar el header del admin
include_once 'includes/header.php';
?>

<div class="admin-actions">
    <div class="action-header">
        <h2>Añadir Producto al Carrusel: <?php echo htmlspecialchars($carrusel['nombre']); ?></h2>
        <a href="carousel-edit.php?id=<?php echo $carrusel_id; ?>" class="btn-back">
            <i class="fas fa-arrow-left"></i> Volver al Carrusel
        </a>
    </div>
    
    <?php if (isset($error) && $error): ?>
        <div class="alert alert-error">
            <?php echo $error; ?>
        </div>
    <?php else: ?>
    
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
        
        <div class="form-container">
            <form method="post">
                <div class="form-group">
                    <label for="producto_id">Seleccione un Producto:</label>
                    <select id="producto_id" name="producto_id" required class="select-product">
                        <option value="">-- Seleccione un producto --</option>
                        <?php foreach ($productos as $producto): ?>
                            <option value="<?php echo $producto['id']; ?>">
                                <?php echo htmlspecialchars($producto['nombre']); ?> 
                                (<?php echo htmlspecialchars($producto['marca_nombre'] ?? 'Sin marca'); ?> - 
                                <?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría'); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="product-preview" style="display: none;">
                    <h3>Vista previa del producto:</h3>
                    <div id="product-card-preview" class="preview-container">
                        <!-- Aquí se mostrará la vista previa del producto -->
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="carousel-edit.php?id=<?php echo $carrusel_id; ?>" class="btn-cancel">Cancelar</a>
                    <button type="submit" class="btn-submit">Añadir al Carrusel</button>
                </div>
            </form>
        </div>
        
        <!-- Lista de productos ya añadidos -->
        <div class="added-products">
            <h3>Productos ya añadidos a este carrusel:</h3>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Categoría</th>
                            <th>Orden</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $productos_carrusel_sql = "SELECT p.*, cp.orden, c.nombre as categoria_nombre 
                                                  FROM productos p
                                                  INNER JOIN carrusel_productos cp ON p.id = cp.producto_id
                                                  LEFT JOIN categorias c ON p.categoria_id = c.id
                                                  WHERE cp.carrusel_id = ? AND cp.activo = 1
                                                  ORDER BY cp.orden ASC";
                        $productos_carrusel = get_rows($productos_carrusel_sql, [$carrusel_id]);
                        
                        if (empty($productos_carrusel)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No hay productos añadidos a este carrusel.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($productos_carrusel as $p): ?>
                                <tr>
                                    <td><?php echo $p['id']; ?></td>
                                    <td>
                                        <img src="<?php echo get_imagen_producto($p['id']); ?>" alt="<?php echo htmlspecialchars($p['nombre']); ?>" style="width: 50px; height: 50px; object-fit: contain;">
                                    </td>
                                    <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                                    <td>
                                        <?php if ($p['en_oferta'] && $p['precio_oferta'] > 0): ?>
                                            <span class="price-offer"><?php echo formatear_precio($p['precio_oferta']); ?></span>
                                            <span class="price-original"><?php echo formatear_precio($p['precio']); ?></span>
                                        <?php else: ?>
                                            <?php echo formatear_precio($p['precio']); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($p['categoria_nombre'] ?? 'Sin categoría'); ?></td>
                                    <td><?php echo $p['orden']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectProduct = document.querySelector('.select-product');
    const previewContainer = document.querySelector('.product-preview');
    
    if (selectProduct) {
        selectProduct.addEventListener('change', function() {
            const productId = this.value;
            
            if (productId) {
                // Mostrar vista previa
                previewContainer.style.display = 'block';
                
                // Simular vista previa (en una implementación real, podrías hacer una petición AJAX)
                const productName = this.options[this.selectedIndex].text;
                const previewCard = document.getElementById('product-card-preview');
                
                previewCard.innerHTML = `
                    <div class="product-card-preview">
                        <p>Se añadirá: <strong>${productName}</strong></p>
                    </div>
                `;
            } else {
                previewContainer.style.display = 'none';
            }
        });
    }
});
</script>

<?php
// Cargar el footer del admin
include_once 'includes/footer.php';
?>