<?php
/**
 * Panel de administración - Editar carrusel
 */

// Incluir archivos necesarios
require_once '../config.php';
require_once INCLUDES_PATH . '/functions.php';

// Verificar que se proporcione un ID
if (!isset($_GET['id'])) {
    // Redirigir a la página de carruseles si no hay ID
    header('Location: carousels.php');
    exit;
}

$id = (int)$_GET['id'];

// Obtener información del carrusel
$sql = "SELECT * FROM carruseles WHERE id = ?";
$carrusel = get_row($sql, [$id]);

// Si el carrusel no existe, redirigir
if (!$carrusel) {
    header('Location: carousels.php');
    exit;
}

// Obtener las categorías disponibles
$categorias_sql = "SELECT id, nombre FROM categorias ORDER BY nombre";
$categorias = get_rows($categorias_sql);

// Procesar el formulario cuando se envía
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $tipo_contenido = isset($_POST['tipo_contenido']) ? trim($_POST['tipo_contenido']) : '';
    $categoria_id = isset($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null;
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validar datos
    if (empty($nombre)) {
        $error_message = 'El nombre del carrusel es obligatorio.';
    } elseif ($tipo_contenido == 'categoria' && empty($categoria_id)) {
        $error_message = 'Debe seleccionar una categoría.';
    } else {
        // Asegúrate que categoria_id sea NULL cuando el tipo de contenido no es categoria
        if ($tipo_contenido != 'categoria') {
            $categoria_id = NULL;
        }
        
        // Actualizar carrusel
        $sql = "UPDATE carruseles SET 
                nombre = ?, 
                tipo_contenido = ?, 
                categoria_id = ?, 
                activo = ?, 
                fecha_actualizacion = NOW() 
                WHERE id = ?";
        
        $params = [$nombre, $tipo_contenido, $categoria_id, $activo, $id];
        $result = update($sql, $params);
        
        if ($result !== false) {
            $success_message = 'Carrusel actualizado correctamente.';
            // Actualizar datos del carrusel para mostrar cambios
            $carrusel = get_row("SELECT * FROM carruseles WHERE id = ?", [$id]);
        } else {
            $error_message = 'Error al actualizar el carrusel.';
        }
    }
}

// Título de la página
$page_title = 'Editar Carrusel: ' . $carrusel['nombre'];

// Cargar el header del admin
include_once 'includes/header.php';
?>

<div class="admin-actions">
    <div class="action-header">
        <h2>Editar Carrusel #<?php echo $id; ?></h2>
        <a href="carousels.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Volver a Carruseles
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
        <form method="post" action="">
            <div class="form-group">
                <label for="nombre">Nombre del Carrusel:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($carrusel['nombre']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="tipo">Tipo de Carrusel:</label>
                <select id="tipo" name="tipo" disabled>
                    <option value="banner" <?php echo ($carrusel['tipo'] == 'banner') ? 'selected' : ''; ?>>Banner</option>
                    <option value="producto" <?php echo ($carrusel['tipo'] == 'producto') ? 'selected' : ''; ?>>Producto</option>
                </select>
                <small>El tipo de carrusel no puede ser modificado.</small>
                <input type="hidden" name="tipo" value="<?php echo $carrusel['tipo']; ?>">
            </div>
            
            <div class="form-group">
                <label for="tipo_contenido">Tipo de Contenido:</label>
                <select id="tipo_contenido" name="tipo_contenido" <?php echo ($id == 6 || $id <= 5 || $id == 7) ? 'disabled' : ''; ?>>
                    <option value="manual" <?php echo ($carrusel['tipo_contenido'] == 'manual') ? 'selected' : ''; ?>>Manual</option>
                    <option value="categoria" <?php echo ($carrusel['tipo_contenido'] == 'categoria') ? 'selected' : ''; ?>>Por Categoría</option>
                </select>
                <?php if ($id == 6): ?>
                    <small>El carrusel de ofertas destacadas solo permite contenido manual.</small>
                    <input type="hidden" name="tipo_contenido" value="manual">
                <?php elseif ($id <= 5 || $id == 7): ?>
                    <small>Los carruseles de banners solo permiten contenido manual.</small>
                    <input type="hidden" name="tipo_contenido" value="manual">
                <?php endif; ?>
            </div>
            
            <div class="form-group categoria-select" <?php echo ($carrusel['tipo_contenido'] != 'categoria') ? 'style="display: none;"' : ''; ?>>
                <label for="categoria_id">Categoría:</label>
                <select id="categoria_id" name="categoria_id">
                    <option value="">Seleccionar categoría</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($carrusel['categoria_id'] == $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="activo">Estado:</label>
                <select id="activo" name="activo">
                    <option value="1" <?php echo ($carrusel['activo'] == 1) ? 'selected' : ''; ?>>Activo</option>
                    <option value="0" <?php echo ($carrusel['activo'] == 0) ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>
            
            <div class="form-actions">
                <a href="carousels.php" class="btn-cancel">Cancelar</a>
                <button type="submit" class="btn-submit">Guardar Cambios</button>
            </div>
        </form>
    </div>
    
    <!-- Sección para gestionar elementos del carrusel -->
    <div class="carousel-manager">
        <h3>Elementos del Carrusel</h3>
        
        <?php if ($carrusel['tipo'] == 'banner'): ?>
            <!-- Administración de banners -->
            <div class="carousel-preview">
                <div class="carousel-preview-header">
                    <h4>Banners</h4>
                    <a href="banner-add.php?carousel_id=<?php echo $id; ?>" class="btn-add-small">
                        <i class="fas fa-plus"></i> Añadir Banner
                    </a>
                </div>
                <div class="carousel-preview-body">
                    <div class="carousel-items">
                        <?php
                        $banners_sql = "SELECT b.*, cb.orden 
                                        FROM banners b
                                        INNER JOIN carrusel_banners cb ON b.id = cb.banner_id
                                        WHERE cb.carrusel_id = ? AND cb.activo = 1
                                        ORDER BY cb.orden ASC";
                        $banners = get_rows($banners_sql, [$id]);
                        
                        if (!empty($banners)) {
                            foreach ($banners as $banner) {
                                ?>
                                <div class="carousel-item" data-id="<?php echo $banner['id']; ?>">
                                    <img src="<?php echo SITE_URL; ?>/uploads/banners/<?php echo $id; ?>/<?php echo $banner['imagen']; ?>" 
                                        alt="<?php echo htmlspecialchars($banner['titulo']); ?>" 
                                        class="carousel-item-image">
                                    <div class="carousel-item-actions">
                                        <button class="btn-move-item" data-direction="up" title="Mover arriba"><i class="fas fa-arrow-up"></i></button>
                                        <button class="btn-move-item" data-direction="down" title="Mover abajo"><i class="fas fa-arrow-down"></i></button>
                                        <button class="btn-edit-item" title="Editar"><i class="fas fa-edit"></i></button>
                                        <button class="btn-delete-item" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </div>
                                    <div class="carousel-item-info">
                                        <div class="carousel-item-title"><?php echo htmlspecialchars($banner['titulo'] ?: 'Banner ' . $banner['id']); ?></div>
                                        <div class="carousel-item-status"><?php echo $banner['activo'] ? 'Activo' : 'Inactivo'; ?></div>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                        
                        <a href="banner-add.php?carousel_id=<?php echo $id; ?>" class="carousel-item btn-add-item">
                            <i class="fas fa-plus"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Administración de productos -->
            <?php if ($carrusel['tipo_contenido'] == 'manual'): ?>
                <div class="carousel-preview">
                    <div class="carousel-preview-header">
                        <h4>Productos</h4>
                        <a href="product-add.php?carousel_id=<?php echo $id; ?>" class="btn-add-small">
                            <i class="fas fa-plus"></i> Añadir Producto
                        </a>
                    </div>
                    <div class="carousel-preview-body">
                        <div class="carousel-items">
                            <?php
                            $productos_sql = "SELECT p.*, cp.orden 
                                            FROM productos p
                                            INNER JOIN carrusel_productos cp ON p.id = cp.producto_id
                                            WHERE cp.carrusel_id = ? AND cp.activo = 1
                                            ORDER BY cp.orden ASC";
                            $productos = get_rows($productos_sql, [$id]);
                            
                            if (!empty($productos)) {
                                foreach ($productos as $producto) {
                                    ?>
                                    <div class="carousel-item" data-id="<?php echo $producto['id']; ?>">
                                        <img src="<?php echo get_imagen_producto($producto['id']); ?>" 
                                            alt="<?php echo htmlspecialchars($producto['nombre']); ?>" 
                                            class="carousel-item-image">
                                        <div class="carousel-item-actions">
                                            <button class="btn-move-item" data-direction="up" title="Mover arriba"><i class="fas fa-arrow-up"></i></button>
                                            <button class="btn-move-item" data-direction="down" title="Mover abajo"><i class="fas fa-arrow-down"></i></button>
                                            <button class="btn-delete-item" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        </div>
                                        <div class="carousel-item-info">
                                            <div class="carousel-item-title"><?php echo htmlspecialchars(substr($producto['nombre'], 0, 30)); ?></div>
                                            <div class="carousel-item-status">
                                                <?php echo $producto['en_oferta'] ? 'En oferta: ' . formatear_precio($producto['precio_oferta']) : formatear_precio($producto['precio']); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                            
                            <a href="product-add.php?carousel_id=<?php echo $id; ?>" class="carousel-item btn-add-item">
                                <i class="fas fa-plus"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="category-info">
                    <p>Este carrusel muestra productos de la categoría: <strong><?php 
                        $categoria_nombre = 'No seleccionada';
                        foreach ($categorias as $cat) {
                            if ($cat['id'] == $carrusel['categoria_id']) {
                                $categoria_nombre = $cat['nombre'];
                                break;
                            }
                        }
                        echo $categoria_nombre;
                    ?></strong></p>
                    <p>Los productos se muestran automáticamente según la categoría seleccionada.</p>
                    
                    <?php
                    // Mostrar productos de la categoría
                    if ($carrusel['categoria_id']) {
                        $productos_categoria_sql = "SELECT p.* FROM productos p
                                                 WHERE p.categoria_id = ? AND p.activo = 1
                                                 ORDER BY p.id DESC LIMIT 10";
                        $productos_categoria = get_rows($productos_categoria_sql, [$carrusel['categoria_id']]);
                        
                        if (!empty($productos_categoria)) {
                            echo '<div class="category-products">';
                            echo '<h4>Productos en esta categoría:</h4>';
                            echo '<div class="products-list">';
                            
                            foreach ($productos_categoria as $producto) {
                                ?>
                                <div class="category-product-item">
                                    <img src="<?php echo get_imagen_producto($producto['id']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                    <div class="product-details">
                                        <h5><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                                        <p><?php echo formatear_precio($producto['precio']); ?></p>
                                    </div>
                                </div>
                                <?php
                            }
                            
                            echo '</div></div>';
                        } else {
                            echo '<p class="no-products">No hay productos en esta categoría.</p>';
                        }
                    }
                    ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Mostrar/ocultar selector de categoría según tipo de contenido
document.addEventListener('DOMContentLoaded', function() {
    const tipoContenidoSelect = document.getElementById('tipo_contenido');
    const categoriaSelect = document.querySelector('.categoria-select');
    
    if (tipoContenidoSelect && categoriaSelect) {
        tipoContenidoSelect.addEventListener('change', function() {
            if (this.value === 'categoria') {
                categoriaSelect.style.display = 'block';
            } else {
                categoriaSelect.style.display = 'none';
            }
        });
    }
    
    // Inicializar botones de elementos
    document.querySelectorAll('.btn-delete-item').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('¿Estás seguro de que deseas eliminar este elemento?')) {
                const item = this.closest('.carousel-item');
                const itemId = item.dataset.id;
                const carouselId = <?php echo $id; ?>;
                const type = '<?php echo $carrusel['tipo']; ?>';
                
                // Realizar la petición AJAX
                fetch('delete-carousel-item.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `type=${type}&carousel_id=${carouselId}&item_id=${itemId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        item.remove();
                    } else {
                        alert('Error al eliminar el elemento: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud');
                });
            }
        });
    });
});
</script>
<?php
// Cargar el footer del admin
include_once 'includes/footer.php';
?>