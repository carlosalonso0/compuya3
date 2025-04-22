<?php
/**
 * Panel de administración - Editar oferta especial
 */

// Incluir archivos necesarios
require_once '../config.php';
require_once INCLUDES_PATH . '/functions.php';

// Verificar que se proporcione un ID
if (!isset($_GET['id'])) {
    // Redirigir si no hay ID
    header('Location: offers.php');
    exit;
}

$id = (int)$_GET['id'];

// Obtener información de la oferta
$sql = "SELECT * FROM ofertas_especiales WHERE id = ?";
$oferta = get_row($sql, [$id]);

// Si la oferta no existe, redirigir
if (!$oferta) {
    header('Location: offers.php');
    exit;
}

// Obtener información del producto
$sql = "SELECT * FROM productos WHERE id = ?";
$producto = get_row($sql, [$oferta['producto_id']]);

// Título de la página
$page_title = 'Editar Oferta Especial';

// Variables para mensajes
$success_message = '';
$error_message = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha_inicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : '';
    $fecha_fin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : '';
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validación básica
    if (empty($fecha_inicio) || empty($fecha_fin)) {
        $error_message = 'Las fechas de inicio y fin son obligatorias.';
    } else {
        // Formatear fechas
        $fecha_inicio_db = date('Y-m-d H:i:s', strtotime($fecha_inicio));
        $fecha_fin_db = date('Y-m-d H:i:s', strtotime($fecha_fin . ' 23:59:59'));
        
        // Actualizar oferta
        $sql = "UPDATE ofertas_especiales SET 
                fecha_inicio = ?, 
                fecha_fin = ?, 
                activo = ?,
                fecha_actualizacion = NOW()
                WHERE id = ?";
        $params = [$fecha_inicio_db, $fecha_fin_db, $activo, $id];
        
        $result = update($sql, $params);
        
        if ($result !== false) {
            $success_message = 'Oferta actualizada correctamente.';
            
            // Actualizar datos de la oferta
            $sql = "SELECT * FROM ofertas_especiales WHERE id = ?";
            $oferta = get_row($sql, [$id]);
        } else {
            $error_message = 'Error al actualizar la oferta.';
        }
    }
}

// Cargar el header del admin
include_once 'includes/header.php';
?>

<div class="admin-actions">
    <div class="action-header">
        <h2>Editar Oferta Especial #<?php echo $id; ?></h2>
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
        <div class="product-info-block">
            <h3>Producto asociado a esta oferta:</h3>
            <div class="product-display">
                <div class="product-image">
                    <img src="<?php echo get_imagen_producto($producto['id']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                </div>
                <div class="product-details">
                    <h4><?php echo htmlspecialchars($producto['nombre']); ?></h4>
                    <p>
                        <strong>SKU:</strong> <?php echo htmlspecialchars($producto['sku']); ?><br>
                        <strong>Precio original:</strong> <?php echo formatear_precio($producto['precio']); ?><br>
                        <strong>Precio oferta:</strong> <?php echo formatear_precio($producto['precio_oferta']); ?><br>
                        <strong>Posición:</strong> <?php echo $oferta['posicion']; ?>
                    </p>
                </div>
            </div>
        </div>
        
        <form method="post">
            <div class="form-group">
                <label for="fecha_inicio">Fecha de inicio:</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" required 
                       value="<?php echo date('Y-m-d', strtotime($oferta['fecha_inicio'])); ?>">
            </div>
            
            <div class="form-group">
                <label for="fecha_fin">Fecha de fin:</label>
                <input type="date" id="fecha_fin" name="fecha_fin" required 
                       value="<?php echo date('Y-m-d', strtotime($oferta['fecha_fin'])); ?>">
            </div>
            
            <div class="form-group checkbox-group">
                <label>
                    <input type="checkbox" name="activo" <?php echo $oferta['activo'] ? 'checked' : ''; ?>> 
                    Oferta activa
                </label>
            </div>
            
            <div class="form-actions">
                <a href="offers.php" class="btn-cancel">Cancelar</a>
                <button type="submit" class="btn-submit">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
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