<?php
/**
 * Panel de administración - Editar producto existente
 */

// Incluir archivos necesarios
require_once '../config.php';
require_once INCLUDES_PATH . '/functions.php';

// Verificar que se proporcione un ID
if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$id = (int)$_GET['id'];

// Obtener información del producto
$sql = "SELECT * FROM productos WHERE id = ?";
$producto = get_row($sql, [$id]);

// Si el producto no existe, redirigir
if (!$producto) {
    header('Location: products.php');
    exit;
}

// Variables para mensajes
$success_message = '';
$error_message = '';

// Obtener lista de marcas
$marcas_sql = "SELECT * FROM marcas WHERE activo = 1 ORDER BY nombre";
$marcas = get_rows($marcas_sql);

// Obtener lista de categorías
$categorias_sql = "SELECT * FROM categorias WHERE activo = 1 ORDER BY nombre";
$categorias = get_rows($categorias_sql);

// Obtener especificaciones del producto
$specs_sql = "SELECT * FROM especificaciones_producto WHERE producto_id = ? ORDER BY orden ASC";
$especificaciones = get_rows($specs_sql, [$id]);

// Obtener imágenes del producto
$images_sql = "SELECT * FROM imagenes_producto WHERE producto_id = ? ORDER BY tipo_imagen, orden ASC";
$imagenes = get_rows($images_sql, [$id]);

// Definir especificaciones recomendadas por categoría
$especificaciones_recomendadas = array(
    1 => [ // Tarjetas Gráficas
        'Memoria' => '[capacidad]GB [tipo]',
        'Interfaz' => 'PCIe [versión]',
        'Núcleos' => '[cantidad]',
        'Frecuencia Base' => '[MHz]MHz',
        'Frecuencia Boost' => '[MHz]MHz',
        'TDP' => '[watts]W',
        'Conectores' => '[tipo]',
        'Salidas' => '[HDMI/DP/DVI/VGA]'
    ],
    2 => [ // Procesadores
        'Núcleos/Hilos' => '[núcleos]C/[hilos]T',
        'Frecuencia Base' => '[GHz]GHz',
        'Frecuencia Turbo' => '[GHz]GHz',
        'Caché' => '[MB]MB',
        'Socket' => '[tipo]',
        'TDP' => '[watts]W',
        'Gráficos Integrados' => '[Si/No]',
        'Tecnología' => '[nm]nm'
    ],
    3 => [ // Cases
        'Formato' => '[ATX/mATX/ITX]',
        'Bahías' => '[cantidad]',
        'Ventiladores Incluidos' => '[cantidad]',
        'Puertos Frontales' => '[USB/Audio]',
        'Panel Lateral' => '[Material]',
        'Dimensiones' => '[alto]x[ancho]x[largo]mm',
        'Compatibilidad Refrigeración Líquida' => '[Si/No]',
        'Peso' => '[kg]kg'
    ],
    7 => [ // Placas Madre
        'Socket' => '[tipo]',
        'Chipset' => '[nombre]',
        'Formato' => '[ATX/micro-ATX/mini-ITX]',
        'RAM' => '[tipo] [capacidad máxima]GB [velocidad]MHz',
        'Slots PCIe' => '[cantidad]x [versión]',
        'Puertos SATA' => '[cantidad]',
        'WiFi' => '[Si/No]',
        'USB' => '[cantidad]x [versión]'
    ],
    4 => [ // Laptops
        'Procesador' => '[marca] [modelo]',
        'RAM' => '[capacidad]GB [tipo]',
        'Almacenamiento' => '[tipo] [capacidad]GB/TB',
        'Pantalla' => '[tamaño]" [resolución] [tipo]',
        'GPU' => '[marca] [modelo]',
        'Batería' => '[capacidad]Wh',
        'Peso' => '[kg]kg',
        'Sistema Operativo' => '[nombre]'
    ],
    5 => [ // PC Gamers
        'Procesador' => '[marca] [modelo]',
        'RAM' => '[capacidad]GB [tipo] [velocidad]MHz',
        'Almacenamiento' => '[tipo] [capacidad]GB/TB',
        'GPU' => '[marca] [modelo]',
        'Fuente' => '[potencia]W [certificación]',
        'Refrigeración' => '[tipo]',
        'Sistema Operativo' => '[nombre]',
        'Conectividad' => '[WiFi/Bluetooth]'
    ],
    6 => [ // Impresoras
        'Tipo' => '[inyección/láser/tanque]',
        'Funciones' => '[impresión/copia/escaneo/fax]',
        'Conectividad' => '[USB/WiFi/Ethernet]',
        'Velocidad' => '[ppm]ppm',
        'Resolución' => '[dpi]dpi',
        'Capacidad bandeja' => '[hojas]hojas',
        'Impresión Doble Cara' => '[Si/No]',
        'Consumibles' => '[tipo]'
    ],
    8 => [ // Monitores
        'Tamaño' => '[pulgadas]"',
        'Resolución' => '[tipo]',
        'Panel' => '[IPS/VA/TN/OLED]',
        'Tasa de Refresco' => '[Hz]Hz',
        'Tiempo de Respuesta' => '[ms]ms',
        'Puertos' => '[HDMI/DP/USB]',
        'Ajustes' => '[altura/inclinación/rotación]',
        'HDR' => '[Si/No]'
    ]
);

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos básicos del producto
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $precio = isset($_POST['precio']) ? floatval($_POST['precio']) : 0;
    $precio_oferta = isset($_POST['precio_oferta']) ? floatval($_POST['precio_oferta']) : 0;
    $en_oferta = isset($_POST['en_oferta']) ? 1 : 0;
    $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $marca_id = isset($_POST['marca_id']) ? intval($_POST['marca_id']) : null;
    $categoria_id = isset($_POST['categoria_id']) ? intval($_POST['categoria_id']) : null;
    $modelo = isset($_POST['modelo']) ? trim($_POST['modelo']) : '';
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validar datos básicos
    if (empty($nombre)) {
        $error_message = 'El nombre del producto es obligatorio.';
    } elseif ($precio <= 0) {
        $error_message = 'El precio debe ser mayor que cero.';
    } elseif ($en_oferta && ($precio_oferta <= 0 || $precio_oferta >= $precio)) {
        $error_message = 'El precio de oferta debe ser mayor que cero y menor que el precio regular.';
    } elseif (empty($marca_id)) {
        $error_message = 'Debe seleccionar una marca.';
    } elseif (empty($categoria_id)) {
        $error_message = 'Debe seleccionar una categoría.';
    } else {
        try {
            // Comenzar transacción
            begin_transaction();
            
            // Generar slug a partir del nombre (solo si ha cambiado el nombre)
            $slug = $producto['slug'];
            if ($nombre !== $producto['nombre']) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nombre)));
                
                // Verificar que el slug no exista para otro producto
                $sql = "SELECT id FROM productos WHERE slug = ? AND id != ?";
                $existe = get_row($sql, [$slug, $id]);
                
                if ($existe) {
                    // Si ya existe, agregar un sufijo único
                    $slug .= '-' . uniqid();
                }
            }
            
            // Actualizar producto en la base de datos
            $sql = "UPDATE productos SET 
                    nombre = ?, 
                    slug = ?, 
                    precio = ?, 
                    precio_oferta = ?, 
                    en_oferta = ?, 
                    stock = ?, 
                    descripcion = ?, 
                    marca_id = ?, 
                    categoria_id = ?, 
                    modelo = ?, 
                    activo = ?,
                    fecha_actualizacion = NOW()
                    WHERE id = ?";
                    
            $params = [
                $nombre, $slug, $precio, $precio_oferta, $en_oferta,
                $stock, $descripcion, $marca_id, $categoria_id, $modelo, 
                $activo, $id
            ];
            
            $result = update($sql, $params);
            
            if ($result === false) {
                throw new Exception("Error al actualizar el producto.");
            }
            
            // Procesar especificaciones
            if (isset($_POST['especificaciones']) && is_array($_POST['especificaciones'])) {
                // Primero eliminar todas las especificaciones existentes
                $sql = "DELETE FROM especificaciones_producto WHERE producto_id = ?";
                update($sql, [$id]);
                
                // Insertar las nuevas especificaciones
                $especificaciones = $_POST['especificaciones'];
                $i = 0;
                
                foreach ($especificaciones as $nombre_espec => $valor_espec) {
                    if (!empty($nombre_espec) && !empty($valor_espec)) {
                        $sql = "INSERT INTO especificaciones_producto (
                                producto_id, nombre_especificacion, valor_especificacion, orden
                            ) VALUES (?, ?, ?, ?)";
                            
                        insert($sql, [$id, $nombre_espec, $valor_espec, $i]);
                        $i++;
                    }
                }
            }
            
            // Imágenes a eliminar
            if (isset($_POST['eliminar_imagen']) && is_array($_POST['eliminar_imagen'])) {
                foreach ($_POST['eliminar_imagen'] as $img_id) {
                    // Obtener información de la imagen
                    $sql = "SELECT * FROM imagenes_producto WHERE id = ? AND producto_id = ?";
                    $imagen = get_row($sql, [$img_id, $id]);
                    
                    if ($imagen) {
                        // Eliminar de la base de datos
                        $sql = "DELETE FROM imagenes_producto WHERE id = ?";
                        update($sql, [$img_id]);
                        
                        // Eliminar el archivo físico
                        $ruta_archivo = UPLOADS_PATH . '/productos/' . $id . '/' . $imagen['ruta_imagen'];
                        if (file_exists($ruta_archivo)) {
                            unlink($ruta_archivo);
                        }
                    }
                }
            }
            
            // Procesar imagen principal (si se ha subido una nueva)
            $imagen_principal = $_FILES['imagen_principal'] ?? null;
            if ($imagen_principal && $imagen_principal['error'] == UPLOAD_ERR_OK && $imagen_principal['size'] > 0) {
                // Crear directorio para las imágenes del producto si no existe
                $producto_dir = UPLOADS_PATH . '/productos/' . $id;
                if (!file_exists($producto_dir)) {
                    mkdir($producto_dir, 0755, true);
                }
                
                $extension = pathinfo($imagen_principal['name'], PATHINFO_EXTENSION);
                $nuevo_nombre = 'principal.' . $extension;
                $ruta_destino = $producto_dir . '/' . $nuevo_nombre;
                
                if (move_uploaded_file($imagen_principal['tmp_name'], $ruta_destino)) {
                    // Eliminar imagen principal anterior si existe
                    $sql = "DELETE FROM imagenes_producto WHERE producto_id = ? AND tipo_imagen = 'principal'";
                    update($sql, [$id]);
                    
                    // Registrar nueva imagen en la base de datos
                    $sql = "INSERT INTO imagenes_producto (
                            producto_id, tipo_imagen, ruta_imagen, orden
                        ) VALUES (?, 'principal', ?, 0)";
                        
                    insert($sql, [$id, $nuevo_nombre]);
                }
            }
            
            // Procesar imágenes adicionales (si se han subido)
            $imagenes_adicionales = $_FILES['imagenes_adicionales'] ?? null;
            if ($imagenes_adicionales && is_array($imagenes_adicionales['name'])) {
                $total_files = count($imagenes_adicionales['name']);
                
                // Crear directorio para las imágenes del producto si no existe
                $producto_dir = UPLOADS_PATH . '/productos/' . $id;
                if (!file_exists($producto_dir)) {
                    mkdir($producto_dir, 0755, true);
                }
                
                // Obtener el último orden de las imágenes adicionales
                $sql = "SELECT MAX(orden) as max_orden FROM imagenes_producto WHERE producto_id = ? AND tipo_imagen = 'adicional'";
                $result = get_row($sql, [$id]);
                $orden_inicial = $result && isset($result['max_orden']) ? $result['max_orden'] + 1 : 1;
                
                for ($i = 0; $i < $total_files; $i++) {
                    if ($imagenes_adicionales['error'][$i] == UPLOAD_ERR_OK && $imagenes_adicionales['size'][$i] > 0) {
                        $extension = pathinfo($imagenes_adicionales['name'][$i], PATHINFO_EXTENSION);
                        $nuevo_nombre = 'adicional_' . ($orden_inicial + $i) . '.' . $extension;
                        $ruta_destino = $producto_dir . '/' . $nuevo_nombre;
                        
                        if (move_uploaded_file($imagenes_adicionales['tmp_name'][$i], $ruta_destino)) {
                            // Registrar imagen en la base de datos
                            $sql = "INSERT INTO imagenes_producto (
                                    producto_id, tipo_imagen, ruta_imagen, orden
                                ) VALUES (?, 'adicional', ?, ?)";
                                
                            insert($sql, [$id, $nuevo_nombre, $orden_inicial + $i]);
                        }
                    }
                }
            }
            
            // Confirmar transacción
            commit();
            
            $success_message = 'Producto actualizado correctamente.';
            
            // Actualizar información del producto y sus relaciones
            $sql = "SELECT * FROM productos WHERE id = ?";
            $producto = get_row($sql, [$id]);
            
            $specs_sql = "SELECT * FROM especificaciones_producto WHERE producto_id = ? ORDER BY orden ASC";
            $especificaciones = get_rows($specs_sql, [$id]);
            
            $images_sql = "SELECT * FROM imagenes_producto WHERE producto_id = ? ORDER BY tipo_imagen, orden ASC";
            $imagenes = get_rows($images_sql, [$id]);
            
        } catch (Exception $e) {
            // Revertir cambios en caso de error
            rollback();
            $error_message = 'Error: ' . $e->getMessage();
        }
    }
}

// Título de la página
$page_title = 'Editar Producto: ' . $producto['nombre'];

// Cargar el header del admin
include_once 'includes/header.php';
?>

<div class="admin-actions">
    <div class="action-header">
        <h2>Editar Producto: <?php echo htmlspecialchars($producto['nombre']); ?></h2>
        <a href="products.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Volver a Productos
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
        <form method="post" enctype="multipart/form-data" id="product-form">
            <div class="form-tabs">
                <div class="tab-nav">
                    <button type="button" class="tab-btn active" data-tab="tab-general">Información General</button>
                    <button type="button" class="tab-btn" data-tab="tab-specs">Especificaciones</button>
                    <button type="button" class="tab-btn" data-tab="tab-images">Imágenes</button>
                </div>
                
                <!-- Tab: Información General -->
                <div class="tab-content active" id="tab-general">
                    <div class="form-group">
                        <label for="nombre">Nombre del Producto <span class="required">*</span></label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="precio">Precio Regular <span class="required">*</span></label>
                            <input type="number" id="precio" name="precio" step="0.01" min="0" value="<?php echo $producto['precio']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="precio_oferta">Precio Oferta</label>
                            <input type="number" id="precio_oferta" name="precio_oferta" step="0.01" min="0" value="<?php echo $producto['precio_oferta']; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group checkbox-group">
                            <label>
                                <input type="checkbox" name="en_oferta" id="en_oferta" <?php echo $producto['en_oferta'] ? 'checked' : ''; ?>>
                                Producto en oferta
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label for="stock">Stock Disponible</label>
                            <input type="number" id="stock" name="stock" min="0" value="<?php echo $producto['stock']; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción del Producto</label>
                        <textarea id="descripcion" name="descripcion" rows="6"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="marca_id">Marca <span class="required">*</span></label>
                            <select id="marca_id" name="marca_id" required>
                                <option value="">Seleccionar Marca</option>
                                <?php foreach ($marcas as $marca): ?>
                                    <option value="<?php echo $marca['id']; ?>" <?php echo ($producto['marca_id'] == $marca['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($marca['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="categoria_id">Categoría <span class="required">*</span></label>
                            <select id="categoria_id" name="categoria_id" required>
                                <option value="">Seleccionar Categoría</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo $categoria['id']; ?>" <?php echo ($producto['categoria_id'] == $categoria['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="modelo">Modelo</label>
                        <input type="text" id="modelo" name="modelo" value="<?php echo htmlspecialchars($producto['modelo']); ?>">
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" name="activo" <?php echo $producto['activo'] ? 'checked' : ''; ?>>
                            Producto activo (visible en tienda)
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label>SKU:</label>
                        <div class="static-field"><?php echo htmlspecialchars($producto['sku']); ?></div>
                        <small>El SKU se generó automáticamente y no puede ser modificado.</small>
                    </div>
                </div>
                
                <!-- Tab: Especificaciones -->
                <div class="tab-content" id="tab-specs">
                    <div class="specs-container">
                        <div class="specs-header">
                            <h3>Especificaciones del Producto</h3>
                            <button type="button" class="btn-add-spec">
                                <i class="fas fa-plus"></i> Añadir Especificación
                            </button>
                        </div>
                        
                        <div class="specs-list" id="specs-list">
                            <?php if (!empty($especificaciones)): ?>
                                <?php foreach ($especificaciones as $spec): ?>
                                    <div class="spec-row">
                                        <div class="spec-input-group">
                                            <input type="text" name="especificaciones[<?php echo htmlspecialchars($spec['nombre_especificacion']); ?>]" 
                                                   placeholder="Nombre de la especificación" 
                                                   value="<?php echo htmlspecialchars($spec['nombre_especificacion']); ?>"
                                                   data-original-name="<?php echo htmlspecialchars($spec['nombre_especificacion']); ?>">
                                            <input type="text" name="especificaciones[<?php echo htmlspecialchars($spec['nombre_especificacion']); ?>]" 
                                                   placeholder="Valor de la especificación" 
                                                   value="<?php echo htmlspecialchars($spec['valor_especificacion']); ?>">
                                            <button type="button" class="btn-remove-spec">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="spec-suggestions-container" style="display: none;">
                            <h4>Especificaciones Recomendadas</h4>
                            <div class="spec-suggestions">
                                <!-- Las sugerencias se cargarán dinámicamente aquí -->
                            </div>
                            <button type="button" class="btn-add-all-specs">Añadir Todas las Recomendadas</button>
                        </div>
                    </div>
                </div>
                
                <!-- Tab: Imágenes -->
                <div class="tab-content" id="tab-images">
                    <div class="form-group">
                        <label for="imagen_principal">Imagen Principal</label>
                        <div class="image-upload-container">
                            <div class="image-preview" id="imagen-principal-preview">
                                <?php
                                // Buscar imagen principal
                                $imagen_principal = '';
                                foreach ($imagenes as $img) {
                                    if ($img['tipo_imagen'] == 'principal') {
                                        $imagen_principal = PRODUCTOS_URL . '/' . $id . '/' . $img['ruta_imagen'];
                                        break;
                                    }
                                }
                                
                                if (!empty($imagen_principal)) {
                                    echo '<img src="' . $imagen_principal . '" alt="Imagen principal">';
                                }
                                ?>
                            </div>
                            <div class="image-upload-box">
                                <input type="file" id="imagen_principal" name="imagen_principal" accept="image/*">
                                <div class="upload-placeholder">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Arrastrar y soltar o hacer clic para seleccionar</span>
                                </div>
                            </div>
                        </div>
                        <small>Formato recomendado: JPG, PNG o WEBP. Dimensiones recomendadas: 800x800px.</small>
                        <small>Deja en blanco para mantener la imagen actual.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="imagenes_adicionales">Imágenes Adicionales (máximo 4)</label>
                        <div class="image-upload-container">
                            <div class="image-preview-gallery" id="imagenes-adicionales-preview">
                                <?php
                                // Mostrar imágenes adicionales existentes
                                $imagenes_adicionales = [];
                                foreach ($imagenes as $img) {
                                    if ($img['tipo_imagen'] == 'adicional') {
                                        echo '<div class="gallery-img-container">';
                                        echo '<img src="' . PRODUCTOS_URL . '/' . $id . '/' . $img['ruta_imagen'] . '" alt="Imagen adicional">';
                                        echo '<button type="button" class="btn-remove-image" data-image-id="' . $img['id'] . '"><i class="fas fa-times"></i></button>';
                                        echo '</div>';
                                    }
                                }
                                ?>
                            </div>
                            <div class="image-upload-box">
                                <input type="file" id="imagenes_adicionales" name="imagenes_adicionales[]" accept="image/*" multiple>
                                <div class="upload-placeholder">
                                    <i class="fas fa-images"></i>
                                    <span>Arrastrar y soltar o hacer clic para seleccionar</span>
                                </div>
                            </div>
                        </div>
                        <small>Formato recomendado: JPG, PNG o WEBP. Dimensiones recomendadas: 800x800px.</small>
                        <small>Puedes añadir más imágenes o dejar en blanco para mantener las imágenes actuales.</small>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <a href="products.php" class="btn-cancel">Cancelar</a>
                <button type="submit" class="btn-submit">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<script>
// Pasar las especificaciones recomendadas a JavaScript
const especificacionesRecomendadas = <?php echo json_encode($especificaciones_recomendadas); ?>;

document.addEventListener('DOMContentLoaded', function() {
    // Gestión de pestañas
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remover clase activa de todos los botones y contenidos
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Agregar clase activa al botón y contenido seleccionado
            button.classList.add('active');
            document.getElementById(button.dataset.tab).classList.add('active');
        });
    });
    
    // Función para añadir una nueva especificación
    function addNewSpecification(nombre = '', valor = '') {
        const specsList = document.getElementById('specs-list');
        const specCount = specsList.children.length;
        
        const specRow = document.createElement('div');
        specRow.className = 'spec-row';
        specRow.innerHTML = `
            <div class="spec-input-group">
                <input type="text" name="especificaciones[${nombre}]" placeholder="Nombre de la especificación" value="${nombre}">
                <input type="text" name="especificaciones[${nombre}]" placeholder="Valor de la especificación" value="${valor}">
                <button type="button" class="btn-remove-spec">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        // Agregar al listado de especificaciones
        specsList.appendChild(specRow);
        
        // Configurar evento para eliminar especificación
        specRow.querySelector('.btn-remove-spec').addEventListener('click', function() {
            specRow.remove();
        });
        
        // Configurar eventos para actualizar el nombre en ambos campos
        const nombreInput = specRow.querySelector('input:first-child');
        const valorInput = specRow.querySelector('input:last-of-type');
        
        nombreInput.addEventListener('input', function() {
            // Actualizar el atributo name del campo valor para mantener la asociación clave-valor
            valorInput.name = `especificaciones[${this.value}]`;
        });
    }
    
    // Botón para añadir una nueva especificación manual
    document.querySelector('.btn-add-spec').addEventListener('click', function() {
        addNewSpecification();
    });
    
    // Configurar eventos para actualizar nombres de campos existentes
    document.querySelectorAll('.spec-row input:first-child').forEach(input => {
        const originalName = input.getAttribute('data-original-name');
        const valorInput = input.nextElementSibling;
        
        input.addEventListener('input', function() {
            // Actualizar el atributo name del campo valor para mantener la asociación clave-valor
            valorInput.name = `especificaciones[${this.value}]`;
        });
    });

    // Toggle para mostrar/ocultar las especificaciones recomendadas
    const categoriaSelect = document.getElementById('categoria_id');
    const specSuggestionsContainer = document.querySelector('.spec-suggestions-container');
    const specSuggestions = document.querySelector('.spec-suggestions');
    
    categoriaSelect.addEventListener('change', function() {
        const categoriaId = this.value;
        
        // Si hay una categoría seleccionada, mostrar sugerencias
        if (categoriaId) {
            // Limpiar sugerencias anteriores
            specSuggestions.innerHTML = '';
            
            // Obtener especificaciones recomendadas para la categoría
            if (especificacionesRecomendadas[categoriaId]) {
                // Verificar especificaciones existentes
                const especsExistentes = {};
                document.querySelectorAll('#specs-list input:first-child').forEach(input => {
                    if (input.value.trim() !== '') {
                        especsExistentes[input.value.trim()] = true;
                    }
                });
                
                // Crear elementos para cada especificación recomendada
                let countSuggestions = 0;
                Object.entries(especificacionesRecomendadas[categoriaId]).forEach(([nombre, valor]) => {
                    // Omitir si ya existe esta especificación
                    if (especsExistentes[nombre]) return;
                    
                    countSuggestions++;
                    const suggestionItem = document.createElement('div');
                    suggestionItem.className = 'spec-suggestion-item';
                    suggestionItem.innerHTML = `
                        <span class="spec-name">${nombre}</span>
                        <span class="spec-value">${valor}</span>
                        <button type="button" class="btn-add-suggestion" data-name="${nombre}" data-value="${valor}">
                            <i class="fas fa-plus"></i>
                        </button>
                    `;
                    specSuggestions.appendChild(suggestionItem);
                });
                
                // Solo mostrar si hay sugerencias disponibles
                if (countSuggestions > 0) {
                    // Mostrar el contenedor de sugerencias
                    specSuggestionsContainer.style.display = 'block';
                    
                    // Configurar eventos para añadir sugerencias
                    const addButtons = specSuggestions.querySelectorAll('.btn-add-suggestion');
                    addButtons.forEach(button => {
                        button.addEventListener('click', function() {
                            const nombreEspec = this.dataset.name;
                            const valorEspec = this.dataset.value;
                            
                            addNewSpecification(nombreEspec, valorEspec);
                            
                            // Marcar la sugerencia como añadida
                            this.closest('.spec-suggestion-item').classList.add('added');
                            this.disabled = true;
                        });
                    });
                    
                    // Botón para añadir todas las recomendadas
                    const addAllButton = document.querySelector('.btn-add-all-specs');
                    addAllButton.addEventListener('click', function() {
                        Object.entries(especificacionesRecomendadas[categoriaId]).forEach(([nombre, valor]) => {
                            // Verificar si ya existe
                            const existe = Array.from(document.querySelectorAll('#specs-list input:first-child')).some(input => input.value === nombre);
                            if (!existe) {
                                addNewSpecification(nombre, valor);
                            }
                        });
                        
                        // Marcar todas las sugerencias como añadidas
                        document.querySelectorAll('.spec-suggestion-item').forEach(item => {
                            item.classList.add('added');
                            item.querySelector('.btn-add-suggestion').disabled = true;
                        });
                    });
                } else {
                    // Ocultar contenedor si no hay sugerencias disponibles
                    specSuggestionsContainer.style.display = 'none';
                }
            } else {
                // Ocultar contenedor si no hay recomendaciones para esta categoría
                specSuggestionsContainer.style.display = 'none';
            }
        } else {
            // Ocultar contenedor si no hay categoría seleccionada
            specSuggestionsContainer.style.display = 'none';
        }
    });
    
    // Previsualización de imágenes
    const imagenPrincipal = document.getElementById('imagen_principal');
    const principalPreview = document.getElementById('imagen-principal-preview');
    
    imagenPrincipal.addEventListener('change', function() {
        previewImage(this, principalPreview);
    });
    
    const imagenesAdicionales = document.getElementById('imagenes_adicionales');
    const adicionalesPreview = document.getElementById('imagenes-adicionales-preview');
    
    imagenesAdicionales.addEventListener('change', function() {
        previewMultipleImages(this, adicionalesPreview);
    });
    
    // Función para previsualizar una imagen
    function previewImage(input, container) {
        // Mantener imágenes existentes si es el caso de edición
        if (!container.querySelector('.preview-new-image')) {
            // Crear un contenedor específico para la nueva imagen
            const newImageContainer = document.createElement('div');
            newImageContainer.className = 'preview-new-image';
            container.appendChild(newImageContainer);
            container = newImageContainer;
        } else {
            container = container.querySelector('.preview-new-image');
            container.innerHTML = '';
        }
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'preview-img';
                container.innerHTML = '';
                container.appendChild(img);
                
                // Mostrar el contenedor de previsualización
                container.style.display = 'block';
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    // Función para previsualizar múltiples imágenes
    function previewMultipleImages(input, container) {
        if (input.files) {
            const fileNum = Math.min(input.files.length, 4); // Máximo 4 imágenes
            
            // Crear un contenedor específico para nuevas imágenes
            let newImagesContainer = container.querySelector('.preview-new-images');
            if (!newImagesContainer) {
                newImagesContainer = document.createElement('div');
                newImagesContainer.className = 'preview-new-images';
                container.appendChild(newImagesContainer);
            } else {
                newImagesContainer.innerHTML = '';
            }
            
            for (let i = 0; i < fileNum; i++) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const imgContainer = document.createElement('div');
                    imgContainer.className = 'gallery-img-container';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    
                    imgContainer.appendChild(img);
                    newImagesContainer.appendChild(imgContainer);
                }
                
                reader.readAsDataURL(input.files[i]);
            }
            
            // Mostrar el contenedor de previsualización
            newImagesContainer.style.display = 'flex';
        }
    }
    
    // Drag and drop para imágenes
    const uploadBoxes = document.querySelectorAll('.image-upload-box');
    
    uploadBoxes.forEach(box => {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            box.addEventListener(eventName, preventDefaults, false);
        });
        
        ['dragenter', 'dragover'].forEach(eventName => {
            box.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            box.addEventListener(eventName, unhighlight, false);
        });
        
        box.addEventListener('drop', handleDrop, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    function highlight() {
        this.classList.add('highlight');
    }
    
    function unhighlight() {
        this.classList.remove('highlight');
    }
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (this.closest('.form-group').querySelector('input[type="file"]').multiple) {
            // Para imágenes adicionales (múltiples)
            imagenesAdicionales.files = files;
            previewMultipleImages(imagenesAdicionales, adicionalesPreview);
        } else {
            // Para imagen principal (única)
            const fileInput = this.closest('.form-group').querySelector('input[type="file"]');
            
            // Crear un nuevo objeto DataTransfer
            const dataTransfer = new DataTransfer();
            
            // Añadir solo el primer archivo
            if (files.length > 0) {
                dataTransfer.items.add(files[0]);
            }
            
            // Asignar el archivo al input
            fileInput.files = dataTransfer.files;
            
            // Actualizar la previsualización
            previewImage(fileInput, principalPreview);
        }
    }
    
    // Validación de precio oferta
    const precioInput = document.getElementById('precio');
    const precioOfertaInput = document.getElementById('precio_oferta');
    const enOfertaCheckbox = document.getElementById('en_oferta');
    
    function validarPrecioOferta() {
        if (enOfertaCheckbox.checked) {
            const precio = parseFloat(precioInput.value);
            const precioOferta = parseFloat(precioOfertaInput.value);
            
            if (precioOferta >= precio) {
                precioOfertaInput.setCustomValidity('El precio de oferta debe ser menor que el precio regular');
            } else {
                precioOfertaInput.setCustomValidity('');
            }
        } else {
            precioOfertaInput.setCustomValidity('');
        }
    }
    
    precioInput.addEventListener('change', validarPrecioOferta);
    precioOfertaInput.addEventListener('change', validarPrecioOferta);
    enOfertaCheckbox.addEventListener('change', validarPrecioOferta);
    
    // Inicializar validación del formulario
    const form = document.getElementById('product-form');
    
    // Botones para eliminar imágenes existentes
    const btnRemoveImages = document.querySelectorAll('.btn-remove-image');
    
    btnRemoveImages.forEach(btn => {
        btn.addEventListener('click', function() {
            const imageId = this.dataset.imageId;
            const container = this.closest('.gallery-img-container');
            
            if (confirm('¿Estás seguro de que deseas eliminar esta imagen?')) {
                // Crear un campo hidden para indicar que se debe eliminar esta imagen
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'eliminar_imagen[]';
                hiddenInput.value = imageId;
                form.appendChild(hiddenInput);
                
                // Ocultar la imagen eliminada
                container.style.display = 'none';
            }
        });
    });
    
    // Inicializar sugerencias de especificaciones si estamos en edición
    if (categoriaSelect && categoriaSelect.value) {
        // Disparar el evento change para mostrar sugerencias
        const changeEvent = new Event('change');
        categoriaSelect.dispatchEvent(changeEvent);
    }
    
    // Validación del formulario al enviar
    form.addEventListener('submit', function(e) {
        // Verificar precio oferta si está en oferta
        if (enOfertaCheckbox.checked) {
            validarPrecioOferta();
            if (precioOfertaInput.validity.customError) {
                e.preventDefault();
                alert('El precio de oferta debe ser menor que el precio regular.');
                return false;
            }
        }
    });
});
</script>

<?php
// Cargar el footer del admin
include_once 'includes/footer.php';
?>