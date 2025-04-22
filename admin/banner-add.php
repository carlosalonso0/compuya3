<?php
/**
 * Panel de administración - Añadir banner a carrusel
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
if ($tipo_carrusel !== 'banner') {
    $error = 'Este carrusel no acepta banners.';
}

// Variables para mensajes
$success_message = '';
$error_message = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $url_destino = isset($_POST['url_destino']) ? trim($_POST['url_destino']) : '';
    $activo = isset($_POST['activo']) ? 1 : 0;
    $posicion_img = isset($_POST['posicion_img']) ? trim($_POST['posicion_img']) : 'center center';
    
    $fecha_inicio = !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] . ' 00:00:00' : NULL;
    $fecha_fin = !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] . ' 23:59:59' : NULL;
    
    // Validar archivo de imagen
    if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] != UPLOAD_ERR_OK) {
        $error_message = 'Debes subir una imagen para el banner.';
    } else {
        $imagen = $_FILES['imagen'];
        $mime_type = mime_content_type($imagen['tmp_name']);
        
        // Verificar tipo de archivo
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mime_type, $allowed_types)) {
            $error_message = 'La imagen debe ser JPG, PNG, GIF o WEBP.';
        } else {
            // Crear directorios si no existen
            $upload_dir = UPLOADS_PATH . '/banners/' . $carrusel_id;
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Insertar banner en la base de datos
            $sql = "INSERT INTO banners (titulo, url_destino, tipo_carrusel, activo, fecha_inicio, fecha_fin, posicion_img) 
                    VALUES (?, ?, 'banner', ?, ?, ?, ?)";
            $params = [$titulo, $url_destino, $activo, $fecha_inicio, $fecha_fin, $posicion_img];
            
            $banner_id = insert($sql, $params);
            
            if ($banner_id) {
                // Generar nombre de archivo
                $extension = pathinfo($imagen['name'], PATHINFO_EXTENSION);
                $filename = 'banner_' . $banner_id . '.' . $extension;
                $filepath = $upload_dir . '/' . $filename;
                
                // Mover archivo subido
                if (move_uploaded_file($imagen['tmp_name'], $filepath)) {
                    // Actualizar ruta de imagen en la base de datos
                    $sql = "UPDATE banners SET imagen = ? WHERE id = ?";
                    update($sql, [$filename, $banner_id]);
                    
                    // Asociar banner al carrusel
                    $sql = "SELECT COUNT(*) as total FROM carrusel_banners WHERE carrusel_id = ?";
                    $result = get_row($sql, [$carrusel_id]);
                    $orden = $result ? ($result['total'] + 1) : 1;
                    
                    $sql = "INSERT INTO carrusel_banners (carrusel_id, banner_id, orden, activo) 
                            VALUES (?, ?, ?, 1)";
                    insert($sql, [$carrusel_id, $banner_id, $orden]);
                    
                    $success_message = 'Banner añadido correctamente.';
                } else {
                    // Error al mover el archivo
                    $sql = "DELETE FROM banners WHERE id = ?";
                    update($sql, [$banner_id]);
                    $error_message = 'Error al guardar la imagen del banner.';
                }
            } else {
                $error_message = 'Error al añadir el banner.';
            }
        }
    }
}

// Título de la página
$page_title = 'Añadir Banner a Carrusel: ' . $carrusel['nombre'];

// Cargar el header del admin
include_once 'includes/header.php';
?>

<div class="admin-actions">
    <div class="action-header">
        <h2>Añadir Banner al Carrusel: <?php echo htmlspecialchars($carrusel['nombre']); ?></h2>
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
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="titulo">Título del Banner:</label>
                    <input type="text" id="titulo" name="titulo" placeholder="Opcional">
                    <small>Texto que se mostrará sobre la imagen (opcional)</small>
                </div>
                
                <div class="form-group">
                    <label for="imagen">Imagen del Banner: <span class="required">*</span></label>
                    <input type="file" id="imagen" name="imagen" accept="image/*" required>
                    <small>Formatos recomendados: JPG, PNG. Dimensiones recomendadas según carrusel:
                    <?php if ($carrusel_id == 1): ?>
                        1200x400px (Banner principal)
                    <?php elseif ($carrusel_id == 2): ?>
                        380x280px (Banner izquierda)
                    <?php elseif ($carrusel_id == 3): ?>
                        600x280px (Banner centro)
                    <?php elseif ($carrusel_id == 4 || $carrusel_id == 5): ?>
                        380x130px (Banner derecha)
                    <?php elseif ($carrusel_id == 7): ?>
                        600x150px (Banner inferior)
                    <?php else: ?>
                        600x300px (Banner estándar)
                    <?php endif; ?>
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="posicion_img">Posición de la imagen:</label>
                    <select id="posicion_img" name="posicion_img">
                        <option value="center center">Centrada (por defecto)</option>
                        <option value="top center">Arriba</option>
                        <option value="bottom center">Abajo</option>
                        <option value="center left">Izquierda</option>
                        <option value="center right">Derecha</option>
                        <option value="top left">Arriba-Izquierda</option>
                        <option value="top right">Arriba-Derecha</option>
                        <option value="bottom left">Abajo-Izquierda</option>
                        <option value="bottom right">Abajo-Derecha</option>
                    </select>
                    <small>Selecciona cómo se posicionará la imagen dentro del banner</small>
                </div>
                
                <div class="form-group">
                    <label for="url_destino">URL de destino:</label>
                    <input type="text" id="url_destino" name="url_destino" placeholder="https://ejemplo.com/pagina">
                    <small>Página a la que se redirigirá al hacer clic (opcional)</small>
                </div>
                
                <div class="form-group">
                    <label for="fecha_inicio">Fecha de inicio:</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio">
                    <small>Fecha desde la que se mostrará el banner (opcional)</small>
                </div>
                
                <div class="form-group">
                    <label for="fecha_fin">Fecha de fin:</label>
                    <input type="date" id="fecha_fin" name="fecha_fin">
                    <small>Fecha hasta la que se mostrará el banner (opcional)</small>
                </div>
                
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" name="activo" checked> Banner activo
                    </label>
                </div>
                
                <div class="form-actions">
                    <a href="carousel-edit.php?id=<?php echo $carrusel_id; ?>" class="btn-cancel">Cancelar</a>
                    <button type="submit" class="btn-submit">Guardar Banner</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php
// Cargar el footer del admin
include_once 'includes/footer.php';
?>