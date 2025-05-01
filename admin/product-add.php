<?php
/**
 * Panel de administración - Añadir nuevo producto
 */

// Incluir archivos necesarios
require_once '../config.php';
require_once INCLUDES_PATH . '/functions.php';

// Variables para mensajes
$success_message = '';
$error_message = '';

// Obtener lista de marcas
$marcas_sql = "SELECT * FROM marcas WHERE activo = 1 ORDER BY nombre";
$marcas = get_rows($marcas_sql);

// Obtener lista de categorías
$categorias_sql = "SELECT * FROM categorias WHERE activo = 1 ORDER BY nombre";
$categorias = get_rows($categorias_sql);

// Definir especificaciones recomendadas por categoría
$especificaciones_recomendadas = array(
    2 => [ // Tarjetas Gráficas
        'Memoria' => '[capacidad]GB [tipo]',
        'Interfaz' => 'PCIe [versión]',
        'Núcleos' => '[cantidad]',
        'Frecuencia Base' => '[MHz]MHz',
        'Frecuencia Boost' => '[MHz]MHz',
        'TDP' => '[watts]W',
        'Conectores' => '[tipo]',
        'Salidas' => '[HDMI/DP/DVI/VGA]'
    ],
    3 => [ // Procesadores
        'Núcleos/Hilos' => '[núcleos]C/[hilos]T',
        'Frecuencia Base' => '[GHz]GHz',
        'Frecuencia Turbo' => '[GHz]GHz',
        'Caché' => '[MB]MB',
        'Socket' => '[tipo]',
        'TDP' => '[watts]W',
        'Gráficos Integrados' => '[Si/No]',
        'Tecnología' => '[nm]nm'
    ],
    4 => [ // Cases
        'Formato' => '[ATX/mATX/ITX]',
        'Bahías' => '[cantidad]',
        'Ventiladores Incluidos' => '[cantidad]',
        'Puertos Frontales' => '[USB/Audio]',
        'Panel Lateral' => '[Material]',
        'Dimensiones' => '[alto]x[ancho]x[largo]mm',
        'Compatibilidad Refrigeración Líquida' => '[Si/No]',
        'Peso' => '[kg]kg'
    ],
    5 => [ // Placas Madre
        'Socket' => '[tipo]',
        'Chipset' => '[nombre]',
        'Formato' => '[ATX/micro-ATX/mini-ITX]',
        'RAM' => '[tipo] [capacidad máxima]GB [velocidad]MHz',
        'Slots PCIe' => '[cantidad]x [versión]',
        'Puertos SATA' => '[cantidad]',
        'WiFi' => '[Si/No]',
        'USB' => '[cantidad]x [versión]'
    ],
    6 => [ // Laptops
        'Procesador' => '[marca] [modelo]',
        'RAM' => '[capacidad]GB [tipo]',
        'Almacenamiento' => '[tipo] [capacidad]GB/TB',
        'Pantalla' => '[tamaño]" [resolución] [tipo]',
        'GPU' => '[marca] [modelo]',
        'Batería' => '[capacidad]Wh',
        'Peso' => '[kg]kg',
        'Sistema Operativo' => '[nombre]'
    ],
    7 => [ // PC Gamers
        'Procesador' => '[marca] [modelo]',
        'RAM' => '[capacidad]GB [tipo] [velocidad]MHz',
        'Almacenamiento' => '[tipo] [capacidad]GB/TB',
        'GPU' => '[marca] [modelo]',
        'Fuente' => '[potencia]W [certificación]',
        'Refrigeración' => '[tipo]',
        'Sistema Operativo' => '[nombre]',
        'Conectividad' => '[WiFi/Bluetooth]'
    ],
    8 => [ // Impresoras
        'Tipo' => '[inyección/láser/tanque]',
        'Funciones' => '[impresión/copia/escaneo/fax]',
        'Conectividad' => '[USB/WiFi/Ethernet]',
        'Velocidad' => '[ppm]ppm',
        'Resolución' => '[dpi]dpi',
        'Capacidad bandeja' => '[hojas]hojas',
        'Impresión Doble Cara' => '[Si/No]',
        'Consumibles' => '[tipo]'
    ],
    9 => [ // Monitores
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
            
            // Generar slug a partir del nombre
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nombre)));
            
            // Generar SKU
            $sku = generateProductSKU($nombre, $marca_id, $modelo, $categoria_id);
            
            // Insertar producto en la base de datos
            $sql = "INSERT INTO productos (
                    nombre, slug, sku, precio, precio_oferta, en_oferta, 
                    stock, descripcion, marca_id, categoria_id, modelo, activo
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
            $params = [
                $nombre, $slug, $sku, $precio, $precio_oferta, $en_oferta,
                $stock, $descripcion, $marca_id, $categoria_id, $modelo, $activo
            ];
            
            $producto_id = insert($sql, $params);
            
            if (!$producto_id) {
                throw new Exception("Error al insertar el producto.");
            }
            
            // Procesar especificaciones
            if (isset($_POST['especificaciones']) && is_array($_POST['especificaciones'])) {
                $especificaciones = $_POST['especificaciones'];
                $i = 0;
                
                foreach ($especificaciones as $nombre_espec => $valor_espec) {
                    if (!empty($nombre_espec) && !empty($valor_espec)) {
                        $sql = "INSERT INTO especificaciones_producto (
                                producto_id, nombre_especificacion, valor_especificacion, orden
                            ) VALUES (?, ?, ?, ?)";
                            
                        insert($sql, [$producto_id, $nombre_espec, $valor_espec, $i]);
                        $i++;
                    }
                }
            }
            
            // Procesar imágenes
            $imagen_principal = $_FILES['imagen_principal'] ?? null;
            $imagenes_adicionales = $_FILES['imagenes_adicionales'] ?? null;
            
            // Crear directorio para las imágenes del producto
            $producto_dir = UPLOADS_PATH . '/productos/' . $producto_id;
            if (!file_exists($producto_dir)) {
                mkdir($producto_dir, 0755, true);
            }
            
            // Procesar imagen principal
            if ($imagen_principal && $imagen_principal['error'] == UPLOAD_ERR_OK) {
                $extension = pathinfo($imagen_principal['name'], PATHINFO_EXTENSION);
                $nuevo_nombre = 'principal.' . $extension;
                $ruta_destino = $producto_dir . '/' . $nuevo_nombre;
                
                if (move_uploaded_file($imagen_principal['tmp_name'], $ruta_destino)) {
                    // Registrar imagen en la base de datos
                    $sql = "INSERT INTO imagenes_producto (
                            producto_id, tipo_imagen, ruta_imagen, orden
                        ) VALUES (?, 'principal', ?, 0)";
                        
                    insert($sql, [$producto_id, $nuevo_nombre]);
                }
            }
            
            // Procesar imágenes adicionales
            if ($imagenes_adicionales) {
                $total_files = count($imagenes_adicionales['name']);
                
                for ($i = 0; $i < $total_files; $i++) {
                    if ($imagenes_adicionales['error'][$i] == UPLOAD_ERR_OK) {
                        $extension = pathinfo($imagenes_adicionales['name'][$i], PATHINFO_EXTENSION);
                        $nuevo_nombre = 'adicional_' . ($i + 1) . '.' . $extension;
                        $ruta_destino = $producto_dir . '/' . $nuevo_nombre;
                        
                        if (move_uploaded_file($imagenes_adicionales['tmp_name'][$i], $ruta_destino)) {
                            // Registrar imagen en la base de datos
                            $sql = "INSERT INTO imagenes_producto (
                                    producto_id, tipo_imagen, ruta_imagen, orden
                                ) VALUES (?, 'adicional', ?, ?)";
                                
                            insert($sql, [$producto_id, $nuevo_nombre, $i + 1]);
                        }
                    }
                }
            }
            
            // Confirmar transacción
            commit();
            
            $success_message = 'Producto añadido correctamente.';
            
            // Limpiar variables del formulario para que se muestre vacío
            $nombre = $descripcion = $modelo = '';
            $precio = $precio_oferta = 0;
            $stock = 0;
            $marca_id = $categoria_id = null;
            $en_oferta = $activo = 0;
            
        } catch (Exception $e) {
            // Revertir cambios en caso de error
            rollback();
            $error_message = 'Error: ' . $e->getMessage();
        }
    }
}

// Título de la página
$page_title = 'Añadir Nuevo Producto';

// Cargar el header del admin
include_once 'includes/header.php';
?>

<div class="admin-actions">
    <div class="action-header">
        <h2>Añadir Nuevo Producto</h2>
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
                        <input type="text" id="nombre" name="nombre" value="<?php echo isset($nombre) ? htmlspecialchars($nombre) : ''; ?>" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="precio">Precio Regular <span class="required">*</span></label>
                            <input type="number" id="precio" name="precio" step="0.01" min="0" value="<?php echo isset($precio) ? $precio : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="precio_oferta">Precio Oferta</label>
                            <input type="number" id="precio_oferta" name="precio_oferta" step="0.01" min="0" value="<?php echo isset($precio_oferta) ? $precio_oferta : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group checkbox-group">
                            <label>
                                <input type="checkbox" name="en_oferta" id="en_oferta" <?php echo isset($en_oferta) && $en_oferta ? 'checked' : ''; ?>>
                                Producto en oferta
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label for="stock">Stock Disponible</label>
                            <input type="number" id="stock" name="stock" min="0" value="<?php echo isset($stock) ? $stock : '0'; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción del Producto</label>
                        <textarea id="descripcion" name="descripcion" rows="6"><?php echo isset($descripcion) ? htmlspecialchars($descripcion) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="marca_id">Marca <span class="required">*</span></label>
                            <select id="marca_id" name="marca_id" required>
                                <option value="">Seleccionar Marca</option>
                                <?php foreach ($marcas as $marca): ?>
                                    <option value="<?php echo $marca['id']; ?>" <?php echo isset($marca_id) && $marca_id == $marca['id'] ? 'selected' : ''; ?>>
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
                                    <option value="<?php echo $categoria['id']; ?>" <?php echo isset($categoria_id) && $categoria_id == $categoria['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="modelo">Modelo</label>
                        <input type="text" id="modelo" name="modelo" value="<?php echo isset($modelo) ? htmlspecialchars($modelo) : ''; ?>">
                        <small>El modelo se utilizará para generar el SKU del producto.</small>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" name="activo" <?php echo !isset($activo) || $activo ? 'checked' : ''; ?>>
                            Producto activo (visible en tienda)
                        </label>
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
                            <!-- Las especificaciones se cargarán dinámicamente aquí -->
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
                        <label for="imagen_principal">Imagen Principal <span class="required">*</span></label>
                        <div class="image-upload-container">
                            <div class="image-preview" id="imagen-principal-preview"></div>
                            <div class="image-upload-box">
                                <input type="file" id="imagen_principal" name="imagen_principal" accept="image/*">
                                <div class="upload-placeholder">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Arrastrar y soltar o hacer clic para seleccionar</span>
                                </div>
                            </div>
                        </div>
                        <small>Formato recomendado: JPG, PNG o WEBP. Dimensiones recomendadas: 800x800px.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="imagenes_adicionales">Imágenes Adicionales (máximo 4)</label>
                        <div class="image-upload-container">
                            <div class="image-preview-gallery" id="imagenes-adicionales-preview"></div>
                            <div class="image-upload-box">
                                <input type="file" id="imagenes_adicionales" name="imagenes_adicionales[]" accept="image/*" multiple>
                                <div class="upload-placeholder">
                                    <i class="fas fa-images"></i>
                                    <span>Arrastrar y soltar o hacer clic para seleccionar</span>
                                </div>
                            </div>
                        </div>
                        <small>Formato recomendado: JPG, PNG o WEBP. Dimensiones recomendadas: 800x800px.</small>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <a href="products.php" class="btn-cancel">Cancelar</a>
                <button type="submit" class="btn-submit">Guardar Producto</button>
            </div>
        </form>
    </div>
</div>

<script>
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
            const recomendadas = <?php echo json_encode($especificaciones_recomendadas); ?>;
            
            if (recomendadas[categoriaId]) {
                // Crear elementos para cada especificación recomendada
                Object.entries(recomendadas[categoriaId]).forEach(([nombre, valor]) => {
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
                
                // Mostrar el contenedor de sugerencias
                specSuggestionsContainer.style.display = 'block';
                
                // Configurar eventos para añadir sugerencias
                const addButtons = document.querySelectorAll('.btn-add-suggestion');
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
                    Object.entries(recomendadas[categoriaId]).forEach(([nombre, valor]) => {
                        addNewSpecification(nombre, valor);
                    });
                    
                    // Marcar todas las sugerencias como añadidas
                    document.querySelectorAll('.spec-suggestion-item').forEach(item => {
                        item.classList.add('added');
                        item.querySelector('.btn-add-suggestion').disabled = true;
                    });
                });
            } else {
                // Ocultar contenedor si no hay sugerencias
                specSuggestionsContainer.style.display = 'none';
            }
        } else {
            // Ocultar contenedor si no hay categoría seleccionada
            specSuggestionsContainer.style.display = 'none';
        }
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
    }
    
    // Botón para añadir una nueva especificación manual
    document.querySelector('.btn-add-spec').addEventListener('click', function() {
        addNewSpecification();
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
        container.innerHTML = '';
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                container.appendChild(img);
                
                // Mostrar el contenedor de previsualización
                container.style.display = 'block';
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    // Función para previsualizar múltiples imágenes
    function previewMultipleImages(input, container) {
        container.innerHTML = '';
        
        if (input.files) {
            const fileNum = Math.min(input.files.length, 4); // Máximo 4 imágenes
            
            for (let i = 0; i < fileNum; i++) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const imgContainer = document.createElement('div');
                    imgContainer.className = 'gallery-img-container';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    
                    imgContainer.appendChild(img);
                    container.appendChild(imgContainer);
                }
                
                reader.readAsDataURL(input.files[i]);
            }
            
            // Mostrar el contenedor de previsualización
            container.style.display = 'flex';
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
    
    // Función para generar un slug a partir del nombre del producto
    const nombreInput = document.getElementById('nombre');
    
    nombreInput.addEventListener('blur', function() {
        const nombre = this.value;
        // Esto es solo para mostrar cómo se vería el slug, no se envía al servidor
        const slug = nombre.toLowerCase()
            .replace(/[^\w ]+/g, '')
            .replace(/ +/g, '-');
            
        // Se podría mostrar en un elemento para que el usuario vea cómo quedaría
        // document.getElementById('slug-preview').textContent = slug;
    });
    
    // Inicializar validación del formulario
    const form = document.getElementById('product-form');
    
    form.addEventListener('submit', function(e) {
        // Se podría añadir validación adicional aquí
        
        // Ejemplo: verificar que haya al menos una imagen principal
        const imagenPrincipal = document.getElementById('imagen_principal');
        if (!imagenPrincipal.files || imagenPrincipal.files.length === 0) {
            e.preventDefault();
            alert('Debes seleccionar una imagen principal para el producto.');
            return false;
        }
        
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