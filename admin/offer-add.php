<?php
/**
 * Panel de administración - Añadir oferta especial
 */

// Incluir archivos necesarios
require_once '../config.php';
require_once INCLUDES_PATH . '/functions.php';

// Título de la página
$page_title = 'Añadir Oferta Especial';

// Variables para mensajes
$success_message = '';
$error_message = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto_id = isset($_POST['producto_id']) ? (int)$_POST['producto_id'] : 0;
    $posicion = isset($_POST['posicion']) ? (int)$_POST['posicion'] : 0;
    $fecha_inicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : '';
    $fecha_fin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : '';
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validación básica
    if (empty($producto_id)) {
        $error_message = 'Debes seleccionar un producto.';
    } elseif (empty($posicion) || !in_array($posicion, [10, 11])) {
        $error_message = 'Debes seleccionar una posición válida (10 u 11).';
    } elseif (empty($fecha_inicio) || empty($fecha_fin)) {
        $error_message = 'Las fechas de inicio y fin son obligatorias.';
    } else {
        // Formatear fechas
        $fecha_inicio_db = date('Y-m-d H:i:s', strtotime($fecha_inicio));
        $fecha_fin_db = date('Y-m-d H:i:s', strtotime($fecha_fin . ' 23:59:59'));
        
        // Verificar si ya existe una oferta activa en esa posición
        $oferta_existente_sql = "SELECT id FROM ofertas_especiales WHERE posicion = ? AND activo = 1";
        $oferta_existente = get_row($oferta_existente_sql, [$posicion]);
        
        if ($oferta_existente) {
            // Desactivar la oferta existente
            $sql = "UPDATE ofertas_especiales SET activo = 0 WHERE posicion = ?";
            update($sql, [$posicion]);
        }
        
        // Verificar que el producto tenga un precio de oferta
        $producto_sql = "SELECT precio, precio_oferta, en_oferta FROM productos WHERE id = ?";
        $producto = get_row($producto_sql, [$producto_id]);
        
        if (!$producto) {
            $error_message = 'El producto seleccionado no existe.';
        } elseif ($producto['precio_oferta'] <= 0 || !$producto['en_oferta']) {
            // Activar precio de oferta en el producto si no lo tiene
            $sql = "UPDATE productos SET precio_oferta = precio * 0.9, en_oferta = 1 WHERE id = ?";
            update($sql, [$producto_id]);
        }
        
        // Insertar nueva oferta
        if (empty($error_message)) {
            $sql = "INSERT INTO ofertas_especiales (producto_id, posicion, fecha_inicio, fecha_fin, activo, fecha_creacion, fecha_actualizacion) 
                    VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
            $params = [$producto_id, $posicion, $fecha_inicio_db, $fecha_fin_db, $activo];
            
            $insert_id = insert($sql, $params);
            
            if ($insert_id) {
                $success_message = 'Oferta especial añadida correctamente.';
            } else {
                $error_message = 'Error al añadir la oferta especial.';
            }
        }
    }
}

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
        <h2>Añadir Oferta Especial</h2>
        <a href="offers.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Volver a Ofertas
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
    
    <div class="form-container">
        <form method="post">
            <div class="form-group">
                <label for="producto_id">Seleccione un Producto:</label>
                <select id="producto_id" name="producto_id" required class="select-product">
                    <option value="">-- Seleccione un producto --</option>
                    <?php foreach ($productos as $producto): ?>
                        <option value="<?php echo $producto['id']; ?>" data-price="<?php echo $producto['precio']; ?>" data-offer-price="<?php echo $producto['precio_oferta']; ?>" data-in-offer="<?php echo $producto['en_oferta']; ?>">
                            <?php echo htmlspecialchars($producto['nombre']); ?> 
                            (<?php echo htmlspecialchars($producto['marca_nombre'] ?? 'Sin marca'); ?> - 
                            <?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría'); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="posicion">Posición:</label>
                <select id="posicion" name="posicion" required>
                    <option value="">-- Seleccione posición --</option>
                    <option value="10">10 - Oferta Izquierda</option>
                    <option value="11">11 - Oferta Derecha</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="fecha_inicio">Fecha de inicio:</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" required value="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="form-group">
                <label for="fecha_fin">Fecha de fin:</label>
                <input type="date" id="fecha_fin" name="fecha_fin" required value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>">
            </div>
            
            <div class="form-group checkbox-group">
                <label>
                    <input type="checkbox" name="activo" checked> Oferta activa
                </label>
            </div>
            
            <div class="product-preview" style="display: none;">
                <h3>Vista previa del producto:</h3>
                <div id="product-card-preview" class="preview-container">
                    <!-- Aquí se mostrará la vista previa del producto -->
                </div>
            </div>
            
            <div class="form-actions">
                <a href="offers.php" class="btn-cancel">Cancelar</a>
                <button type="submit" class="btn-submit">Guardar Oferta</button>
            </div>
        </form>
    </div>
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
                
                // Obtener datos del producto
                const selectedOption = this.options[this.selectedIndex];
                const productName = selectedOption.text;
                const price = selectedOption.dataset.price;
                const offerPrice = selectedOption.dataset.offerPrice;
                const inOffer = selectedOption.dataset.inOffer === "1";
                
                // Construir vista previa
                const previewCard = document.getElementById('product-card-preview');
                let priceDisplay = '';
                
                if (inOffer && offerPrice > 0) {
                    priceDisplay = `<p>Precio original: <span style="text-decoration: line-through;">S/. ${parseFloat(price).toFixed(2)}</span></p>
                                 <p>Precio oferta: <span style="color: #e53935; font-weight: bold;">S/. ${parseFloat(offerPrice).toFixed(2)}</span></p>`;
                } else {
                    priceDisplay = `<p>Precio: <span>S/. ${parseFloat(price).toFixed(2)}</span></p>
                                 <p>Precio oferta (se aplicará automáticamente): <span style="color: #e53935; font-weight: bold;">S/. ${(parseFloat(price) * 0.9).toFixed(2)}</span></p>`;
                }
                
                previewCard.innerHTML = `
                    <div class="product-card-preview">
                        <h4>${productName}</h4>
                        ${priceDisplay}
                        <p>Se mostrará como oferta especial en la posición seleccionada.</p>
                    </div>
                `;
            } else {
                previewContainer.style.display = 'none';
            }
        });
    }
    
    // Validar fechas
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    
    function validarFechas() {
        if (fechaInicio.value && fechaFin.value) {
            if (fechaInicio.value > fechaFin.value) {
                fechaFin.setCustomValidity('La fecha de fin debe ser posterior a la fecha de inicio');
            } else {
                fechaFin.setCustomValidity('');
            }
        }
    }
    
    if (fechaInicio && fechaFin) {
        fechaInicio.addEventListener('change', validarFechas);
        fechaFin.addEventListener('change', validarFechas);
    }
});
</script>

<?php
// Cargar el footer del admin
include_once 'includes/footer.php';
?>