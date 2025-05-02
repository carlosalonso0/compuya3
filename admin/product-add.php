<?php
/**
 * Panel de administración - Añadir nuevo producto
 */

// Incluir archivos necesarios
require_once '../config.php';
require_once INCLUDES_PATH . '/functions.php';

// Verificar si hay una sesión activa (aquí iría la lógica de autenticación)

// Título de la página
$page_title = 'Añadir Nuevo Producto';

// Variables para mensajes y manejo de datos
$message = '';
$error = '';
$producto = [
    'nombre' => '',
    'precio' => '',
    'precio_oferta' => '',
    'en_oferta' => 0,
    'stock' => '',
    'descripcion' => '',
    'marca_id' => '',
    'categoria_id' => '',
    'modelo' => '',
    'activo' => 1
];

// Obtener marcas para el select
$sql_marcas = "SELECT id, nombre FROM marcas WHERE activo = 1 ORDER BY nombre";
$marcas = get_rows($sql_marcas);

// Obtener categorías para el select
$sql_categorias = "SELECT id, nombre FROM categorias WHERE activo = 1 ORDER BY nombre";
$categorias = get_rows($sql_categorias);

// Sugerencias de especificaciones por categoría
$especificaciones_sugeridas = [
    2 => [ // Tarjetas Gráficas
        ['nombre' => 'Memoria', 'valor' => ''],
        ['nombre' => 'Interfaz', 'valor' => ''],
        ['nombre' => 'Núcleos', 'valor' => ''],
        ['nombre' => 'Frecuencia Base', 'valor' => ''],
        ['nombre' => 'Frecuencia Boost', 'valor' => ''],
        ['nombre' => 'TDP', 'valor' => ''],
        ['nombre' => 'Conectores', 'valor' => ''],
        ['nombre' => 'Salidas', 'valor' => '']
    ],
    3 => [ // Procesadores
        ['nombre' => 'Núcleos/Hilos', 'valor' => ''],
        ['nombre' => 'Frecuencia Base', 'valor' => ''],
        ['nombre' => 'Frecuencia Turbo', 'valor' => ''],
        ['nombre' => 'Caché', 'valor' => ''],
        ['nombre' => 'Socket', 'valor' => ''],
        ['nombre' => 'TDP', 'valor' => ''],
        ['nombre' => 'Gráficos Integrados', 'valor' => ''],
        ['nombre' => 'Tecnología', 'valor' => '']
    ],
    4 => [ // Cases
        ['nombre' => 'Formato', 'valor' => ''],
        ['nombre' => 'Bahías', 'valor' => ''],
        ['nombre' => 'Ventiladores Incluidos', 'valor' => ''],
        ['nombre' => 'Puertos Frontales', 'valor' => ''],
        ['nombre' => 'Panel Lateral', 'valor' => ''],
        ['nombre' => 'Dimensiones', 'valor' => ''],
        ['nombre' => 'Compatibilidad Refrigeración Líquida', 'valor' => ''],
        ['nombre' => 'Peso', 'valor' => '']
    ],
    5 => [ // Placas Madre
        ['nombre' => 'Socket', 'valor' => ''],
        ['nombre' => 'Chipset', 'valor' => ''],
        ['nombre' => 'Formato', 'valor' => ''],
        ['nombre' => 'RAM', 'valor' => ''],
        ['nombre' => 'Slots PCIe', 'valor' => ''],
        ['nombre' => 'Puertos SATA', 'valor' => ''],
        ['nombre' => 'WiFi', 'valor' => ''],
        ['nombre' => 'USB', 'valor' => '']
    ],
    6 => [ // Laptops
        ['nombre' => 'Procesador', 'valor' => ''],
        ['nombre' => 'RAM', 'valor' => ''],
        ['nombre' => 'Almacenamiento', 'valor' => ''],
        ['nombre' => 'Pantalla', 'valor' => ''],
        ['nombre' => 'GPU', 'valor' => ''],
        ['nombre' => 'Batería', 'valor' => ''],
        ['nombre' => 'Peso', 'valor' => ''],
        ['nombre' => 'Sistema Operativo', 'valor' => '']
    ],
    7 => [ // PC Gamers
        ['nombre' => 'Procesador', 'valor' => ''],
        ['nombre' => 'RAM', 'valor' => ''],
        ['nombre' => 'Almacenamiento', 'valor' => ''],
        ['nombre' => 'GPU', 'valor' => ''],
        ['nombre' => 'Fuente', 'valor' => ''],
        ['nombre' => 'Refrigeración', 'valor' => ''],
        ['nombre' => 'Sistema Operativo', 'valor' => ''],
        ['nombre' => 'Conectividad', 'valor' => '']
    ],
    8 => [ // Impresoras
        ['nombre' => 'Tipo', 'valor' => ''],
        ['nombre' => 'Funciones', 'valor' => ''],
        ['nombre' => 'Conectividad', 'valor' => ''],
        ['nombre' => 'Velocidad', 'valor' => ''],
        ['nombre' => 'Resolución', 'valor' => ''],
        ['nombre' => 'Capacidad bandeja', 'valor' => ''],
        ['nombre' => 'Impresión Doble Cara', 'valor' => ''],
        ['nombre' => 'Consumibles', 'valor' => '']
    ],
    9 => [ // Monitores
        ['nombre' => 'Tamaño', 'valor' => ''],
        ['nombre' => 'Resolución', 'valor' => ''],
        ['nombre' => 'Panel', 'valor' => ''],
        ['nombre' => 'Tasa de Refresco', 'valor' => ''],
        ['nombre' => 'Tiempo de Respuesta', 'valor' => ''],
        ['nombre' => 'Puertos', 'valor' => ''],
        ['nombre' => 'Ajustes', 'valor' => ''],
        ['nombre' => 'HDR', 'valor' => '']
    ]
];

// Inicializar array para especificaciones
$especificaciones = [];
$categoria_seleccionada = isset($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : 0;

// Procesar formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar datos básicos del producto
    $producto = [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'precio' => filter_var($_POST['precio'] ?? 0, FILTER_VALIDATE_FLOAT),
        'precio_oferta' => filter_var($_POST['precio_oferta'] ?? 0, FILTER_VALIDATE_FLOAT),
        'en_oferta' => isset($_POST['en_oferta']) ? 1 : 0,
        'stock' => (int)($_POST['stock'] ?? 0),
        'descripcion' => trim($_POST['descripcion'] ?? ''),
        'marca_id' => (int)($_POST['marca_id'] ?? 0),
        'categoria_id' => (int)($_POST['categoria_id'] ?? 0),
        'modelo' => trim($_POST['modelo'] ?? ''),
        'activo' => isset($_POST['activo']) ? 1 : 0
    ];

    // Validar campos obligatorios
    $errores = [];
    if (empty($producto['nombre'])) $errores[] = "El nombre del producto es obligatorio.";
    if ($producto['precio'] <= 0) $errores[] = "El precio debe ser mayor que cero.";
    if ($producto['en_oferta'] && $producto['precio_oferta'] <= 0) $errores[] = "El precio de oferta debe ser mayor que cero.";
    if ($producto['en_oferta'] && $producto['precio_oferta'] >= $producto['precio']) $errores[] = "El precio de oferta debe ser menor que el precio regular.";
    if ($producto['stock'] < 0) $errores[] = "El stock no puede ser negativo.";
    if ($producto['marca_id'] <= 0) $errores[] = "Debe seleccionar una marca.";
    if ($producto['categoria_id'] <= 0) $errores[] = "Debe seleccionar una categoría.";
    if (empty($producto['modelo'])) $errores[] = "El modelo es obligatorio.";

    // Si no hay errores, proceder a guardar
    if (empty($errores)) {
        try {
            // Iniciar transacción
            db()->begin_transaction();
            
            // Generar slug
            $slug = generateSlug($producto['nombre']);
            
            // Generar SKU
            $sku = generateProductSKU($producto['nombre'], $producto['marca_id'], $producto['modelo'], $producto['categoria_id']);
            
            // Insertar producto
            $sql = "INSERT INTO productos 
                    (nombre, slug, sku, precio, precio_oferta, en_oferta, stock, descripcion, 
                     marca_id, categoria_id, modelo, activo, fecha_creacion, fecha_actualizacion) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())";
            
            $params = [
                $producto['nombre'], $slug, $sku, $producto['precio'], $producto['precio_oferta'], 
                $producto['en_oferta'], $producto['stock'], $producto['descripcion'], 
                $producto['marca_id'], $producto['categoria_id'], $producto['modelo']
            ];
            
            $producto_id = insert($sql, $params);
            
            if (!$producto_id) {
                throw new Exception("Error al insertar el producto.");
            }

            // Procesar imágenes
            $dir_producto = UPLOADS_PATH . '/productos/' . $producto_id;
            verificar_crear_ruta($dir_producto);
            
            // Crear directorios para los diferentes tipos de imágenes
            $tipos_img = ['principal', 'tarjeta', 'thumbnail', 'galeria'];
            foreach ($tipos_img as $tipo) {
                verificar_crear_ruta($dir_producto . '/' . $tipo);
            }
            
            // Procesar imagen principal
            if (isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] === UPLOAD_ERR_OK) {
                $imagen_principal = procesarImagen($_FILES['imagen_principal'], $dir_producto . '/principal', 'principal_1');
                
                if ($imagen_principal) {
                    // Insertar registro de imagen
                    $sql = "INSERT INTO imagenes_producto (producto_id, tipo_imagen, ruta_imagen, orden, fecha_creacion, fecha_actualizacion)
                            VALUES (?, 'principal', ?, 1, NOW(), NOW())";
                    insert($sql, [$producto_id, $imagen_principal]);
                    
                    // Crear miniaturas para tarjeta y thumbnails
                    crearMiniatura($dir_producto . '/principal/' . $imagen_principal, 
                                   $dir_producto . '/tarjeta/producto_' . $producto_id . '_tarjeta_1.webp', 
                                   300, 300);
                    
                    crearMiniatura($dir_producto . '/principal/' . $imagen_principal, 
                                   $dir_producto . '/thumbnail/producto_' . $producto_id . '_thumbnail_1.webp', 
                                   100, 100);
                }
            }
            
            // Procesar imágenes adicionales (hasta 4)
            if (isset($_FILES['imagenes_adicionales'])) {
                $total_adicionales = count($_FILES['imagenes_adicionales']['name']);
                
                for ($i = 0; $i < $total_adicionales && $i < 4; $i++) {
                    if ($_FILES['imagenes_adicionales']['error'][$i] === UPLOAD_ERR_OK) {
                        // Crear array para el archivo actual
                        $file = [
                            'name' => $_FILES['imagenes_adicionales']['name'][$i],
                            'type' => $_FILES['imagenes_adicionales']['type'][$i],
                            'tmp_name' => $_FILES['imagenes_adicionales']['tmp_name'][$i],
                            'error' => $_FILES['imagenes_adicionales']['error'][$i],
                            'size' => $_FILES['imagenes_adicionales']['size'][$i]
                        ];
                        
                        $imagen_adicional = procesarImagen($file, $dir_producto . '/galeria', 'galeria_' . ($i + 1));
                        
                        if ($imagen_adicional) {
                            // Insertar registro de imagen
                            $sql = "INSERT INTO imagenes_producto (producto_id, tipo_imagen, ruta_imagen, orden, fecha_creacion, fecha_actualizacion)
                                    VALUES (?, 'galeria', ?, ?, NOW(), NOW())";
                            insert($sql, [$producto_id, $imagen_adicional, $i + 2]); // Orden: 2,3,4,5 (después de la principal)
                            
                            // Crear miniaturas
                            crearMiniatura($dir_producto . '/galeria/' . $imagen_adicional, 
                                           $dir_producto . '/thumbnail/producto_' . $producto_id . '_thumbnail_' . ($i + 2) . '.webp', 
                                           100, 100);
                        }
                    }
                }
            }
            
            // Procesar especificaciones
            if (isset($_POST['spec_nombre']) && isset($_POST['spec_valor'])) {
                $total_specs = count($_POST['spec_nombre']);
                
                for ($i = 0; $i < $total_specs; $i++) {
                    $nombre_spec = trim($_POST['spec_nombre'][$i]);
                    $valor_spec = trim($_POST['spec_valor'][$i]);
                    
                    // Solo insertar si ambos campos tienen valor
                    if (!empty($nombre_spec) && !empty($valor_spec)) {
                        $sql = "INSERT INTO especificaciones_producto 
                                (producto_id, nombre_especificacion, valor_especificacion, orden, fecha_creacion, fecha_actualizacion)
                                VALUES (?, ?, ?, ?, NOW(), NOW())";
                        insert($sql, [$producto_id, $nombre_spec, $valor_spec, $i + 1]);
                    }
                }
            }
            
            // Confirmar transacción
            db()->commit();
            
            $message = "Producto creado correctamente.";
            
            // Redireccionar a la página de edición
            header("Location: product-edit.php?id=" . $producto_id . "&message=" . urlencode($message));
            exit;
            
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            db()->rollback();
            $error = "Error al crear el producto: " . $e->getMessage();
        }
    } else {
        $error = "Por favor corrija los siguientes errores:<br>" . implode("<br>", $errores);
    }
}

/**
 * Función para procesar una imagen subida
 */
function procesarImagen($file, $destino, $nombre_base) {
    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    
    if (!in_array($file['type'], $tipos_permitidos)) {
        return false;
    }
    
    // Obtener extensión
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nombre_archivo = $nombre_base . '.' . $extension;
    $ruta_destino = $destino . '/' . $nombre_archivo;
    
    if (move_uploaded_file($file['tmp_name'], $ruta_destino)) {
        // Convertir a WebP para mejor rendimiento
        $webp_nombre = $nombre_base . '.webp';
        $webp_ruta = $destino . '/' . $webp_nombre;
        
        // Si tenemos la extensión GD, convertir a WebP
        if (function_exists('imagecreatefromjpeg')) {
            $imagen = null;
            
            switch ($file['type']) {
                case 'image/jpeg':
                    $imagen = imagecreatefromjpeg($ruta_destino);
                    break;
                case 'image/png':
                    $imagen = imagecreatefrompng($ruta_destino);
                    break;
                case 'image/gif':
                    $imagen = imagecreatefromgif($ruta_destino);
                    break;
                case 'image/webp':
                    // Ya es WebP, solo renombrar
                    rename($ruta_destino, $webp_ruta);
                    return $webp_nombre;
            }
            
            if ($imagen) {
                imagewebp($imagen, $webp_ruta, 80);
                imagedestroy($imagen);
                
                // Eliminar original si no es WebP
                if ($file['type'] !== 'image/webp') {
                    unlink($ruta_destino);
                }
                
                return $webp_nombre;
            }
        }
        
        // Si no pudimos convertir, devolver el nombre original
        return $nombre_archivo;
    }
    
    return false;
}

/**
 * Función para crear miniaturas
 */
function crearMiniatura($origen, $destino, $ancho, $alto) {
    if (!function_exists('imagecreatefromjpeg')) {
        // Sin soporte GD, no podemos crear miniaturas
        return false;
    }
    
    list($ancho_orig, $alto_orig, $tipo) = getimagesize($origen);
    
    // Calcular dimensiones manteniendo proporción
    $ratio_orig = $ancho_orig / $alto_orig;
    
    if ($ancho / $alto > $ratio_orig) {
        $ancho = $alto * $ratio_orig;
    } else {
        $alto = $ancho / $ratio_orig;
    }
    
    // Crear imagen
    $imagen_p = imagecreatetruecolor($ancho, $alto);
    
    // Determinar tipo de imagen original
    $extension = strtolower(pathinfo($origen, PATHINFO_EXTENSION));
    
    switch ($extension) {
        case 'jpg':
        case 'jpeg':
            $imagen = imagecreatefromjpeg($origen);
            break;
        case 'png':
            $imagen = imagecreatefrompng($origen);
            // Preservar transparencia
            imagealphablending($imagen_p, false);
            imagesavealpha($imagen_p, true);
            break;
        case 'gif':
            $imagen = imagecreatefromgif($origen);
            break;
        case 'webp':
            $imagen = imagecreatefromwebp($origen);
            break;
        default:
            return false;
    }
    
    // Redimensionar
    imagecopyresampled($imagen_p, $imagen, 0, 0, 0, 0, $ancho, $alto, $ancho_orig, $alto_orig);
    
    // Guardar como WebP
    imagewebp($imagen_p, $destino, 80);
    
    // Liberar memoria
    imagedestroy($imagen_p);
    imagedestroy($imagen);
    
    return true;
}

/**
 * Función para generar slug
 */
function generateSlug($text) {
    // Convertir a minúsculas
    $text = strtolower($text);
    
    // Reemplazar espacios con guiones
    $text = preg_replace('/\s+/', '-', $text);
    
    // Eliminar caracteres especiales
    $text = preg_replace('/[^a-z0-9\-]/', '', $text);
    
    // Eliminar guiones duplicados
    $text = preg_replace('/-+/', '-', $text);
    
    // Eliminar guiones al inicio y final
    $text = trim($text, '-');
    
    return $text;
}

/**
 * Función para generar SKU 
 */
function generateProductSKU($nombre, $marca_id, $modelo, $categoria_id) {
    // Obtener marca
    $marca = '';
    $sql = "SELECT nombre FROM marcas WHERE id = ?";
    $marca_result = get_row($sql, [$marca_id]);
    if ($marca_result) {
        $marca = $marca_result['nombre'];
    }
    
    // Abreviaturas de marca (2-3 letras)
    $marca2L = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $marca), 0, 2));
    $marca3L = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $marca), 0, 3));
    
    // Usar directamente el campo modelo si está disponible
    if (!empty($modelo)) {
        switch ($categoria_id) {
            case 2: // Tarjetas Gráficas
                return "TG-{$marca2L}-" . str_replace('-', '', $modelo) . "-001";
                
            case 3: // Procesadores
                return "PR-{$marca3L}-" . str_replace('-', '', $modelo) . "-001";
                
            case 4: // Cases
                return "CS-{$marca3L}-" . str_replace('-', '', $modelo) . "-001";
                
            case 5: // Placas Madre
                return "PM-{$marca3L}-" . str_replace('-', '', $modelo) . "-001";
                
            case 6: // Laptops
                return "LP-{$marca2L}-" . str_replace('-', '', $modelo) . "-001";
                
            case 7: // PC Gamers
                return "PC-" . substr($modelo, 0, 3) . "-" . substr($modelo, 4, 4) . "-001";
                
            case 8: // Impresoras
                return "IM-{$marca2L}-" . str_replace('-', '', $modelo) . "-001";
                
            case 9: // Monitores
                return "MO-{$marca2L}-" . str_replace('-', '', $modelo) . "-001";
                
            default:
                return "GEN-{$marca2L}-" . substr(md5($nombre), 0, 6) . "-001";
        }
    } else {
        // Si no hay campo modelo, extraerlo del nombre
        return "GEN-{$marca2L}-" . substr(md5($nombre), 0, 6) . "-001";
    }
}

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
    
    <?php if ($message): ?>
        <div class="alert alert-success">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <div class="form-container">
        <form method="post" enctype="multipart/form-data" id="product-form" novalidate>
            <div class="form-section">
                <h3>Información General</h3>
                
                <div class="form-group">
                    <label for="nombre">Nombre del Producto: <span class="required">*</span></label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>">
                    <div class="error-message" id="error-nombre"></div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="precio">Precio Regular: <span class="required">*</span></label>
                        <input type="number" id="precio" name="precio" step="0.01" min="0" value="<?php echo htmlspecialchars($producto['precio']); ?>">
                        <div class="error-message" id="error-precio"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="en_oferta">En Oferta:</label>
                        <div class="checkbox-toggle">
                            <input type="checkbox" id="en_oferta" name="en_oferta" <?php echo $producto['en_oferta'] ? 'checked' : ''; ?>>
                            <label for="en_oferta">Activar oferta</label>
                        </div>
                    </div>
                    
                    <div class="form-group precio-oferta-container" style="<?php echo $producto['en_oferta'] ? '' : 'display: none;'; ?>">
                        <label for="precio_oferta">Precio Oferta:</label>
                        <input type="number" id="precio_oferta" name="precio_oferta" step="0.01" min="0" value="<?php echo htmlspecialchars($producto['precio_oferta']); ?>">
                        <div class="error-message" id="error-precio-oferta"></div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="stock">Stock: <span class="required">*</span></label>
                        <input type="number" id="stock" name="stock" min="0" value="<?php echo htmlspecialchars($producto['stock']); ?>">
                        <div class="error-message" id="error-stock"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="marca_id">Marca: <span class="required">*</span></label>
                        <select id="marca_id" name="marca_id">
                            <option value="">Seleccione una marca</option>
                            <?php foreach ($marcas as $marca): ?>
                                <option value="<?php echo $marca['id']; ?>" <?php echo ($producto['marca_id'] == $marca['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($marca['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="error-message" id="error-marca"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="categoria_id">Categoría: <span class="required">*</span></label>
                        <select id="categoria_id" name="categoria_id">
                            <option value="">Seleccione una categoría</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria['id']; ?>" <?php echo ($producto['categoria_id'] == $categoria['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="error-message" id="error-categoria"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="modelo">Modelo: <span class="required">*</span></label>
                        <input type="text" id="modelo" name="modelo" value="<?php echo htmlspecialchars($producto['modelo']); ?>">
                        <div class="error-message" id="error-modelo"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" rows="6"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="activo">Estado:</label>
                    <div class="checkbox-toggle">
                        <input type="checkbox" id="activo" name="activo" <?php echo $producto['activo'] ? 'checked' : ''; ?>>
                        <label for="activo">Producto activo</label>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3>Imágenes</h3>
                
                <div class="form-group">
                    <label>Imagen Principal: <span class="required">*</span></label>
                    <div class="image-uploader" id="principal-uploader">
                        <input type="file" name="imagen_principal" id="imagen_principal" accept="image/*">
                        <div class="upload-placeholder">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Arrastra una imagen aquí o haz clic para seleccionar</p>
                            <p class="small">Formatos permitidos: JPG, PNG, WebP, GIF. Máximo 5MB.</p>
                        </div>
                        <div class="image-preview" id="principal-preview"></div>
                    </div>
                    <div class="error-message" id="error-imagen-principal"></div>
                </div>
                
                <div class="form-group">
                    <label>Imágenes Adicionales: <small>(Máximo 4)</small></label>
                    <div class="image-uploader-multiple" id="adicionales-uploader">
                        <input type="file" name="imagenes_adicionales[]" id="imagenes_adicionales" accept="image/*" multiple>
                        <div class="upload-placeholder">
                            <i class="fas fa-images"></i>
                            <p>Arrastra imágenes aquí o haz clic para seleccionar</p>
                            <p class="small">Puedes seleccionar hasta 4 imágenes</p>
                        </div>
                        <div class="image-previews" id="adicionales-preview"></div>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3>Especificaciones</h3>
                
                <p class="specs-info">Las especificaciones son opcionales. Agrega especificaciones relevantes para el producto.</p>
                
                <div id="specs-container">
                    <div class="spec-row">
                        <div class="form-group">
                            <input type="text" name="spec_nombre[]" placeholder="Nombre">
                        </div>
                        <div class="form-group">
                            <input type="text" name="spec_valor[]" placeholder="Valor">
                        </div>
                        <button type="button" class="btn-remove-spec"><i class="fas fa-times"></i></button>
                    </div>
                </div>
                
                <button type="button" id="btn-add-spec" class="btn-add-item">
                    <i class="fas fa-plus"></i> Añadir especificación
                </button>
                
                <div id="spec-suggestions-container">
                    <!-- Las sugerencias de especificaciones se cargarán aquí con JavaScript -->
                </div>
            </div>
            
            <div class="form-actions">
                <a href="products.php" class="btn-cancel">Cancelar</a>
                <button type="submit" class="btn-submit">Guardar Producto</button>
            </div>
        </form>
    </div>
</div>

<style>
.form-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.form-section h3 {
    margin-bottom: 20px;
    font-size: 18px;
    color: #2c3e50;
    font-weight: 500;
}

.form-row {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.form-row .form-group {
    flex: 1;
    min-width: 200px;
}

.checkbox-toggle {
    display: flex;
    align-items: center;
    margin-top: 5px;
}

.checkbox-toggle input[type="checkbox"] {
    margin-right: 10px;
}

.required {
    color: #e53935;
}

.error-message {
    color: #e53935;
    font-size: 13px;
    margin-top: 5px;
    display: none;
}

.image-uploader, .image-uploader-multiple {
    border: 2px dashed #ddd;
    padding: 30px;
    text-align: center;
    border-radius: 5px;
    position: relative;
    margin-bottom: 20px;
    transition: border-color 0.2s;
}

.image-uploader:hover, .image-uploader-multiple:hover {
    border-color: #3498db;
}

.upload-placeholder {
    color: #777;
}

.upload-placeholder i {
    font-size: 48px;
    color: #ccc;
    margin-bottom: 15px;
}

.upload-placeholder .small {
    font-size: 12px;
    margin-top: 10px;
    color: #999;
}

.image-preview, .image-previews {
    margin-top: 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
}

.image-preview img {
    max-height: 200px;
    max-width: 100%;
    border-radius: 5px;
}

.image-previews .preview-item {
    position: relative;
    width: 150px;
    height: 150px;
    overflow: hidden;
    border-radius: 5px;
    border: 1px solid #ddd;
}

.image-previews .preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-previews .preview-remove {
    position: absolute;
    top: 5px;
    right: 5px;
    background: rgba(0, 0, 0, 0.5);
    color: white;
    width: 25px;
    height: 25px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.spec-row {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    align-items: center;
}

.spec-row .form-group {
    flex: 1;
}

.btn-remove-spec {
    background-color: #f44336;
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.btn-add-item {
    background-color: #e8f5e9;
    color: #388e3c;
    border: 1px dashed #4caf50;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    margin-bottom: 20px;
}

.btn-add-item:hover {
    background-color: #c8e6c9;
}

.spec-suggestions {
    margin-top: 30px;
    padding: 20px;
    background-color: #f5f5f5;
    border-radius: 5px;
}

.spec-suggestions h3 {
    margin-top: 0;
    font-size: 16px;
    color: #333;
}

.suggestions-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 15px;
}

.suggestion-item {
    padding: 8px 15px;
    background-color: #e3f2fd;
    color: #1976d2;
    border-radius: 20px;
    font-size: 13px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.suggestion-item:hover {
    background-color: #bbdefb;
}

.has-error input, 
.has-error select, 
.has-error textarea {
    border-color: #e53935;
}

.has-error .error-message {
    display: block;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Definir especificaciones sugeridas por categoría
    const especificacionesSugeridas = {
        2: [ // Tarjetas Gráficas
            {nombre: 'Memoria', valor: ''},
            {nombre: 'Interfaz', valor: ''},
            {nombre: 'Núcleos', valor: ''},
            {nombre: 'Frecuencia Base', valor: ''},
            {nombre: 'Frecuencia Boost', valor: ''},
            {nombre: 'TDP', valor: ''},
            {nombre: 'Conectores', valor: ''},
            {nombre: 'Salidas', valor: ''}
        ],
        3: [ // Procesadores
            {nombre: 'Núcleos/Hilos', valor: ''},
            {nombre: 'Frecuencia Base', valor: ''},
            {nombre: 'Frecuencia Turbo', valor: ''},
            {nombre: 'Caché', valor: ''},
            {nombre: 'Socket', valor: ''},
            {nombre: 'TDP', valor: ''},
            {nombre: 'Gráficos Integrados', valor: ''},
            {nombre: 'Tecnología', valor: ''}
        ],
        4: [ // Cases
            {nombre: 'Formato', valor: ''},
            {nombre: 'Bahías', valor: ''},
            {nombre: 'Ventiladores Incluidos', valor: ''},
            {nombre: 'Puertos Frontales', valor: ''},
            {nombre: 'Panel Lateral', valor: ''},
            {nombre: 'Dimensiones', valor: ''},
            {nombre: 'Compatibilidad Refrigeración Líquida', valor: ''},
            {nombre: 'Peso', valor: ''}
        ],
        5: [ // Placas Madre
            {nombre: 'Socket', valor: ''},
            {nombre: 'Chipset', valor: ''},
            {nombre: 'Formato', valor: ''},
            {nombre: 'RAM', valor: ''},
            {nombre: 'Slots PCIe', valor: ''},
            {nombre: 'Puertos SATA', valor: ''},
            {nombre: 'WiFi', valor: ''},
            {nombre: 'USB', valor: ''}
        ],
        6: [ // Laptops
            {nombre: 'Procesador', valor: ''},
            {nombre: 'RAM', valor: ''},
            {nombre: 'Almacenamiento', valor: ''},
            {nombre: 'Pantalla', valor: ''},
            {nombre: 'GPU', valor: ''},
            {nombre: 'Batería', valor: ''},
            {nombre: 'Peso', valor: ''},
            {nombre: 'Sistema Operativo', valor: ''}
        ],
        7: [ // PC Gamers
            {nombre: 'Procesador', valor: ''},
            {nombre: 'RAM', valor: ''},
            {nombre: 'Almacenamiento', valor: ''},
            {nombre: 'GPU', valor: ''},
            {nombre: 'Fuente', valor: ''},
            {nombre: 'Refrigeración', valor: ''},
            {nombre: 'Sistema Operativo', valor: ''},
            {nombre: 'Conectividad', valor: ''}
        ],
        8: [ // Impresoras
            {nombre: 'Tipo', valor: ''},
            {nombre: 'Funciones', valor: ''},
            {nombre: 'Conectividad', valor: ''},
            {nombre: 'Velocidad', valor: ''},
            {nombre: 'Resolución', valor: ''},
            {nombre: 'Capacidad bandeja', valor: ''},
            {nombre: 'Impresión Doble Cara', valor: ''},
            {nombre: 'Consumibles', valor: ''}
        ],
        9: [ // Monitores
            {nombre: 'Tamaño', valor: ''},
            {nombre: 'Resolución', valor: ''},
            {nombre: 'Panel', valor: ''},
            {nombre: 'Tasa de Refresco', valor: ''},
            {nombre: 'Tiempo de Respuesta', valor: ''},
            {nombre: 'Puertos', valor: ''},
            {nombre: 'Ajustes', valor: ''},
            {nombre: 'HDR', valor: ''}
        ]
    };
    
    // Gestión del checkbox "En oferta"
    const enOfertaCheckbox = document.getElementById('en_oferta');
    const precioOfertaContainer = document.querySelector('.precio-oferta-container');
    
    if (enOfertaCheckbox && precioOfertaContainer) {
        enOfertaCheckbox.addEventListener('change', function() {
            precioOfertaContainer.style.display = this.checked ? 'block' : 'none';
        });
    }
    
    // Previsualización de imagen principal
    const imagenPrincipalInput = document.getElementById('imagen_principal');
    const principalPreview = document.getElementById('principal-preview');
    
    if (imagenPrincipalInput && principalPreview) {
        imagenPrincipalInput.addEventListener('change', function() {
            previsualizarImagen(this.files[0], principalPreview);
        });
    }
    
    // Previsualización de imágenes adicionales
    const imagenesAdicionalesInput = document.getElementById('imagenes_adicionales');
    const adicionalesPreview = document.getElementById('adicionales-preview');
    
    if (imagenesAdicionalesInput && adicionalesPreview) {
        imagenesAdicionalesInput.addEventListener('change', function() {
            adicionalesPreview.innerHTML = '';
            
            // Limitar a 4 imágenes
            const maxFiles = 4;
            const files = Array.from(this.files).slice(0, maxFiles);
            
            files.forEach((file, index) => {
                const preview = document.createElement('div');
                preview.className = 'preview-item';
                
                const img = document.createElement('img');
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    img.src = e.target.result;
                };
                
                reader.readAsDataURL(file);
                preview.appendChild(img);
                
                const removeBtn = document.createElement('div');
                removeBtn.className = 'preview-remove';
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                removeBtn.addEventListener('click', function() {
                    preview.remove();
                });
                
                preview.appendChild(removeBtn);
                adicionalesPreview.appendChild(preview);
            });
            
            if (this.files.length > maxFiles) {
                alert('Solo se permiten un máximo de 4 imágenes adicionales. Se han seleccionado las primeras 4.');
            }
        });
    }
    
    // Función para previsualizar una imagen
    function previsualizarImagen(file, previewElement) {
        if (!file) return;
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewElement.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        };
        
        reader.readAsDataURL(file);
    }
    
    // Gestión de especificaciones
    const btnAddSpec = document.getElementById('btn-add-spec');
    const specsContainer = document.getElementById('specs-container');
    
    if (btnAddSpec && specsContainer) {
        btnAddSpec.addEventListener('click', function() {
            agregarFilaEspecificacion('', '');
        });
        
        // Delegación de eventos para botones de eliminar
        specsContainer.addEventListener('click', function(e) {
            if (e.target.closest('.btn-remove-spec')) {
                const specRow = e.target.closest('.spec-row');
                specRow.remove();
            }
        });
    }
    
    // Función para agregar una fila de especificación
    function agregarFilaEspecificacion(nombre = '', valor = '') {
        const specRow = document.createElement('div');
        specRow.className = 'spec-row';
        specRow.innerHTML = `
            <div class="form-group">
                <input type="text" name="spec_nombre[]" placeholder="Nombre" value="${nombre}">
            </div>
            <div class="form-group">
                <input type="text" name="spec_valor[]" placeholder="Valor" value="${valor}">
            </div>
            <button type="button" class="btn-remove-spec"><i class="fas fa-times"></i></button>
        `;
        
        specsContainer.appendChild(specRow);
    }
    
    // Función para cargar sugerencias de especificaciones
    function cargarSugerencias(categoriaId) {
        const container = document.getElementById('spec-suggestions-container');
        container.innerHTML = '';
        
        if (especificacionesSugeridas[categoriaId]) {
            const html = `
                <div class="spec-suggestions">
                    <h3>Especificaciones sugeridas para esta categoría</h3>
                    <p>Haz clic en una sugerencia para añadirla:</p>
                    
                    <div class="suggestions-list">
                        ${especificacionesSugeridas[categoriaId].map(spec => 
                            `<div class="suggestion-item" data-nombre="${spec.nombre}">${spec.nombre}</div>`
                        ).join('')}
                    </div>
                </div>
            `;
            
            container.innerHTML = html;
            
            // Añadir listeners a las sugerencias
            const suggestionItems = container.querySelectorAll('.suggestion-item');
            suggestionItems.forEach(item => {
                item.addEventListener('click', function() {
                    const nombre = this.getAttribute('data-nombre');
                    agregarFilaEspecificacion(nombre, '');
                });
            });
        }
    }
    
    // Detección de cambio de categoría
    const categoriaSelect = document.getElementById('categoria_id');
    
    if (categoriaSelect) {
        // Cargar sugerencias iniciales si hay categoría seleccionada
        if (categoriaSelect.value) {
            cargarSugerencias(categoriaSelect.value);
        }
        
        // Escuchar cambios
        categoriaSelect.addEventListener('change', function() {
            cargarSugerencias(this.value);
        });
    }
    
    // Validación del formulario
    const form = document.getElementById('product-form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // Prevenir envío para validar primero
            e.preventDefault();
            
            // Limpiar errores previos
            const errorMessages = document.querySelectorAll('.error-message');
            errorMessages.forEach(msg => {
                msg.style.display = 'none';
                msg.textContent = '';
            });
            
            const formGroups = document.querySelectorAll('.form-group');
            formGroups.forEach(group => {
                group.classList.remove('has-error');
            });
            
            // Validar campos obligatorios
            let isValid = true;
            
            // Nombre
            const nombre = document.getElementById('nombre');
            if (!nombre.value.trim()) {
                showError(nombre, 'error-nombre', 'El nombre del producto es obligatorio');
                isValid = false;
            }
            
            // Precio
            const precio = document.getElementById('precio');
            if (!precio.value || parseFloat(precio.value) <= 0) {
                showError(precio, 'error-precio', 'El precio debe ser mayor que cero');
                isValid = false;
            }
            
            // Precio oferta
            const enOferta = document.getElementById('en_oferta');
            const precioOferta = document.getElementById('precio_oferta');
            
            if (enOferta.checked) {
                if (!precioOferta.value || parseFloat(precioOferta.value) <= 0) {
                    showError(precioOferta, 'error-precio-oferta', 'El precio de oferta debe ser mayor que cero');
                    isValid = false;
                } else if (parseFloat(precioOferta.value) >= parseFloat(precio.value)) {
                    showError(precioOferta, 'error-precio-oferta', 'El precio de oferta debe ser menor que el precio regular');
                    isValid = false;
                }
            }
            
            // Stock
            const stock = document.getElementById('stock');
            if (stock.value === '' || parseInt(stock.value) < 0) {
                showError(stock, 'error-stock', 'El stock no puede ser negativo');
                isValid = false;
            }
            
            // Marca
            const marca = document.getElementById('marca_id');
            if (!marca.value) {
                showError(marca, 'error-marca', 'Debe seleccionar una marca');
                isValid = false;
            }
            
            // Categoría
            const categoria = document.getElementById('categoria_id');
            if (!categoria.value) {
                showError(categoria, 'error-categoria', 'Debe seleccionar una categoría');
                isValid = false;
            }
            
            // Modelo
            const modelo = document.getElementById('modelo');
            if (!modelo.value.trim()) {
                showError(modelo, 'error-modelo', 'El modelo es obligatorio');
                isValid = false;
            }
            
            // Imagen principal
            const imagenPrincipal = document.getElementById('imagen_principal');
            if (imagenPrincipal.files.length === 0) {
                showError(imagenPrincipal, 'error-imagen-principal', 'Debe seleccionar una imagen principal');
                isValid = false;
            }
            
            // Si todo está bien, enviar el formulario
            if (isValid) {
                form.submit();
            }
        });
    }
    
    // Función para mostrar error
    function showError(element, errorId, message) {
        const errorElement = document.getElementById(errorId);
        if (errorElement) {
            errorElement.style.display = 'block';
            errorElement.textContent = message;
            
            const formGroup = element.closest('.form-group');
            if (formGroup) {
                formGroup.classList.add('has-error');
            }
        }
    }
});
</script>

<?php
// Cargar el footer del admin
include_once 'includes/footer.php';
?>