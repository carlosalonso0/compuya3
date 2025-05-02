<?php
/**
 * Panel de administración - Editar producto existente
 */

// Incluir archivos necesarios
require_once '../config.php';
require_once INCLUDES_PATH . '/functions.php';

// Verificar si hay una sesión activa (aquí iría la lógica de autenticación)

// Verificar que se proporciona un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$producto_id = (int)$_GET['id'];

// Título de la página
$page_title = 'Editar Producto';

// Variables para mensajes y manejo de datos
$message = isset($_GET['message']) ? $_GET['message'] : '';
$error = '';

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

// Obtener datos del producto
$sql = "SELECT * FROM productos WHERE id = ?";
$producto = get_row($sql, [$producto_id]);

if (!$producto) {
    // Si no existe el producto, redirigir a la lista de productos
    header("Location: products.php");
    exit;
}

// Obtener imágenes del producto
$sql = "SELECT * FROM imagenes_producto WHERE producto_id = ? ORDER BY orden ASC";
$imagenes = get_rows($sql, [$producto_id]);

// Separar imagen principal y adicionales
$imagen_principal = null;
$imagenes_adicionales = [];

foreach ($imagenes as $imagen) {
    if ($imagen['tipo_imagen'] === 'principal') {
        $imagen_principal = $imagen;
    } elseif ($imagen['tipo_imagen'] === 'galeria') {
        $imagenes_adicionales[] = $imagen;
    }
}

// Obtener especificaciones del producto
$sql = "SELECT * FROM especificaciones_producto WHERE producto_id = ? ORDER BY orden ASC";
$especificaciones = get_rows($sql, [$producto_id]);

// Obtener marcas para el select
$sql_marcas = "SELECT id, nombre FROM marcas WHERE activo = 1 ORDER BY nombre";
$marcas = get_rows($sql_marcas);

// Obtener categorías para el select
$sql_categorias = "SELECT id, nombre FROM categorias WHERE activo = 1 ORDER BY nombre";
$categorias = get_rows($sql_categorias);

// Procesar formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar datos básicos del producto
    $producto_actualizado = [
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
    if (empty($producto_actualizado['nombre'])) $errores[] = "El nombre del producto es obligatorio.";
    if ($producto_actualizado['precio'] <= 0) $errores[] = "El precio debe ser mayor que cero.";
    if ($producto_actualizado['en_oferta'] && $producto_actualizado['precio_oferta'] <= 0) $errores[] = "El precio de oferta debe ser mayor que cero.";
    if ($producto_actualizado['en_oferta'] && $producto_actualizado['precio_oferta'] >= $producto_actualizado['precio']) $errores[] = "El precio de oferta debe ser menor que el precio regular.";
    if ($producto_actualizado['stock'] < 0) $errores[] = "El stock no puede ser negativo.";
    if ($producto_actualizado['marca_id'] <= 0) $errores[] = "Debe seleccionar una marca.";
    if ($producto_actualizado['categoria_id'] <= 0) $errores[] = "Debe seleccionar una categoría.";
    if (empty($producto_actualizado['modelo'])) $errores[] = "El modelo es obligatorio.";

    // Si no hay errores, proceder a guardar
    if (empty($errores)) {
        try {
            // Iniciar transacción
            db()->begin_transaction();
            
            // Actualizar slug si cambió el nombre
            if ($producto_actualizado['nombre'] !== $producto['nombre']) {
                $slug = generateSlug($producto_actualizado['nombre']);
                $producto_actualizado['slug'] = $slug;
            }
            
            // Actualizar SKU si cambió la marca, modelo o categoría
            if ($producto_actualizado['marca_id'] !== $producto['marca_id'] || 
                $producto_actualizado['modelo'] !== $producto['modelo'] || 
                $producto_actualizado['categoria_id'] !== $producto['categoria_id']) {
                $sku = generateProductSKU(
                    $producto_actualizado['nombre'], 
                    $producto_actualizado['marca_id'], 
                    $producto_actualizado['modelo'], 
                    $producto_actualizado['categoria_id']
                );
                $producto_actualizado['sku'] = $sku;
            }
            
            // Preparar consulta SQL para actualizar
            $campos = [];
            $valores = [];
            
            foreach ($producto_actualizado as $campo => $valor) {
                if ($campo !== 'id' && isset($producto[$campo]) && $valor !== $producto[$campo]) {
                    $campos[] = "$campo = ?";
                    $valores[] = $valor;
                }
            }
            
            // Añadir fecha de actualización
            $campos[] = "fecha_actualizacion = NOW()";
            
            // Solo actualizar si hay cambios
            if (!empty($campos)) {
                $sql = "UPDATE productos SET " . implode(", ", $campos) . " WHERE id = ?";
                $valores[] = $producto_id;
                
                update($sql, $valores);
            }
            
            // Procesar imagen principal si se subió una nueva
            if (isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] === UPLOAD_ERR_OK) {
                $dir_producto = UPLOADS_PATH . '/productos/' . $producto_id;
                verificar_crear_ruta($dir_producto);
                
                // Crear directorios para los diferentes tipos de imágenes
                $tipos_img = ['principal', 'tarjeta', 'thumbnail', 'galeria'];
                foreach ($tipos_img as $tipo) {
                    verificar_crear_ruta($dir_producto . '/' . $tipo);
                }
                
                $imagen_principal = procesarImagen($_FILES['imagen_principal'], $dir_producto . '/principal', 'principal_1');
                
                if ($imagen_principal) {
                    // Eliminar imagen principal anterior si existe
                    $sql = "DELETE FROM imagenes_producto WHERE producto_id = ? AND tipo_imagen = 'principal'";
                    update($sql, [$producto_id]);
                    
                    // Insertar nuevo registro de imagen
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
            
            // Procesar imágenes adicionales si se subieron nuevas
            if (isset($_FILES['imagenes_adicionales']) && 
                isset($_FILES['imagenes_adicionales']['name']) && 
                is_array($_FILES['imagenes_adicionales']['name'])) {
                
                $dir_producto = UPLOADS_PATH . '/productos/' . $producto_id;
                verificar_crear_ruta($dir_producto . '/galeria');
                verificar_crear_ruta($dir_producto . '/thumbnail');
                
                $total_adicionales = count($_FILES['imagenes_adicionales']['name']);
                
                // Si se marcó la opción de reemplazar, eliminar las imágenes anteriores
                if (isset($_POST['reemplazar_adicionales']) && $_POST['reemplazar_adicionales'] == 1) {
                    $sql = "DELETE FROM imagenes_producto WHERE producto_id = ? AND tipo_imagen = 'galeria'";
                    update($sql, [$producto_id]);
                    $orden_inicial = 2; // Después de la principal
                } else {
                    // Si no se reemplazan, obtener la orden máxima actual
                    $sql = "SELECT MAX(orden) as max_orden FROM imagenes_producto WHERE producto_id = ?";
                    $resultado = get_row($sql, [$producto_id]);
                    $orden_inicial = $resultado ? ($resultado['max_orden'] + 1) : 2;
                }
                
                // Limitar a máximo 4 imágenes adicionales en total
                $imagenes_actuales = count($imagenes_adicionales);
                $max_nuevas = 4 - $imagenes_actuales;
                
                if ($max_nuevas > 0) {
                    for ($i = 0; $i < $total_adicionales && $i < $max_nuevas; $i++) {
                        if ($_FILES['imagenes_adicionales']['error'][$i] === UPLOAD_ERR_OK) {
                            // Crear array para el archivo actual
                            $file = [
                                'name' => $_FILES['imagenes_adicionales']['name'][$i],
                                'type' => $_FILES['imagenes_adicionales']['type'][$i],
                                'tmp_name' => $_FILES['imagenes_adicionales']['tmp_name'][$i],
                                'error' => $_FILES['imagenes_adicionales']['error'][$i],
                                'size' => $_FILES['imagenes_adicionales']['size'][$i]
                            ];
                            
                            $imagen_adicional = procesarImagen($file, $dir_producto . '/galeria', 'galeria_' . ($orden_inicial - 1));
                            
                            if ($imagen_adicional) {
                                // Insertar registro de imagen
                                $sql = "INSERT INTO imagenes_producto (producto_id, tipo_imagen, ruta_imagen, orden, fecha_creacion, fecha_actualizacion)
                                        VALUES (?, 'galeria', ?, ?, NOW(), NOW())";
                                insert($sql, [$producto_id, $imagen_adicional, $orden_inicial]);
                                
                                // Crear miniaturas
                                crearMiniatura($dir_producto . '/galeria/' . $imagen_adicional, 
                                              $dir_producto . '/thumbnail/producto_' . $producto_id . '_thumbnail_' . $orden_inicial . '.webp', 
                                              100, 100);
                                
                                $orden_inicial++;
                            }
                        }
                    }
                }
            }
            
            // Procesar eliminación de imágenes adicionales específicas
            if (isset($_POST['eliminar_imagen']) && is_array($_POST['eliminar_imagen'])) {
                foreach ($_POST['eliminar_imagen'] as $imagen_id) {
                    $sql = "DELETE FROM imagenes_producto WHERE id = ? AND producto_id = ?";
                    update($sql, [$imagen_id, $producto_id]);
                }
            }
            
            // Actualizar especificaciones
            // Primero eliminar especificaciones existentes
            $sql = "DELETE FROM especificaciones_producto WHERE producto_id = ?";
            update($sql, [$producto_id]);
            
            // Luego insertar las nuevas especificaciones
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
            
            $message = "Producto actualizado correctamente.";
            
            // Recargar los datos del producto
            $sql = "SELECT * FROM productos WHERE id = ?";
            $producto = get_row($sql, [$producto_id]);
            
            // Recargar imágenes
            $sql = "SELECT * FROM imagenes_producto WHERE producto_id = ? ORDER BY orden ASC";
            $imagenes = get_rows($sql, [$producto_id]);
            
            // Separar imagen principal y adicionales
            $imagen_principal = null;
            $imagenes_adicionales = [];
            
            foreach ($imagenes as $imagen) {
                if ($imagen['tipo_imagen'] === 'principal') {
                    $imagen_principal = $imagen;
                } elseif ($imagen['tipo_imagen'] === 'galeria') {
                    $imagenes_adicionales[] = $imagen;
                }
            }
            
            // Recargar especificaciones
            $sql = "SELECT * FROM especificaciones_producto WHERE producto_id = ? ORDER BY orden ASC";
            $especificaciones = get_rows($sql, [$producto_id]);
            
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            db()->rollback();
            $error = "Error al actualizar el producto: " . $e->getMessage();
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
        <h2>Editar Producto</h2>
        <div class="header-actions">
            <a href="products.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Volver a Productos
            </a>
            <a href="<?php echo SITE_URL; ?>/producto.php?id=<?php echo $producto_id; ?>" target="_blank" class="btn-view">
                <i class="fas fa-eye"></i> Ver en Tienda
            </a>
        </div>
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
        <form method="post" enctype="multipart/form-data" id="product-form">
            <div class="form-tabs">
                <button type="button" class="tab-btn active" data-tab="general">Información General</button>
                <button type="button" class="tab-btn" data-tab="imagenes">Imágenes</button>
                <button type="button" class="tab-btn" data-tab="especificaciones">Especificaciones</button>
            </div>
            
            <!-- Tab: Información General -->
            <div class="tab-content active" id="tab-general">
                <div class="product-header">
                    <div class="product-id">
                        <span>ID: <?php echo $producto_id; ?></span>
                        <?php if (!empty($producto['sku'])): ?>
                            <span>SKU: <?php echo htmlspecialchars($producto['sku']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="nombre">Nombre del Producto: <span class="required">*</span></label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="precio">Precio Regular: <span class="required">*</span></label>
                        <input type="number" id="precio" name="precio" step="0.01" min="0" value="<?php echo htmlspecialchars($producto['precio']); ?>" required>
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
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="stock">Stock: <span class="required">*</span></label>
                        <input type="number" id="stock" name="stock" min="0" value="<?php echo htmlspecialchars($producto['stock']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="marca_id">Marca: <span class="required">*</span></label>
                        <select id="marca_id" name="marca_id" required>
                            <option value="">Seleccione una marca</option>
                            <?php foreach ($marcas as $marca): ?>
                                <option value="<?php echo $marca['id']; ?>" <?php echo ($producto['marca_id'] == $marca['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($marca['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="categoria_id">Categoría: <span class="required">*</span></label>
                        <select id="categoria_id" name="categoria_id" required>
                            <option value="">Seleccione una categoría</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria['id']; ?>" <?php echo ($producto['categoria_id'] == $categoria['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="modelo">Modelo: <span class="required">*</span></label>
                        <input type="text" id="modelo" name="modelo" value="<?php echo htmlspecialchars($producto['modelo']); ?>" required>
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
            
            <!-- Tab: Imágenes -->
            <div class="tab-content" id="tab-imagenes">
                <div class="form-group">
                    <label>Imagen Principal:</label>
                    <div class="image-uploader" id="principal-uploader">
                        <input type="file" name="imagen_principal" id="imagen_principal" accept="image/*">
                        <div class="upload-placeholder">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Arrastra una nueva imagen aquí o haz clic para seleccionar</p>
                            <p class="small">Formatos permitidos: JPG, PNG, WebP, GIF. Máximo 5MB.</p>
                        </div>
                        <div class="image-preview" id="principal-preview">
                            <?php if ($imagen_principal): ?>
                                <div class="current-image">
                                    <img src="<?php echo PRODUCTOS_URL . '/' . $producto_id . '/principal/' . $imagen_principal['ruta_imagen']; ?>" alt="Imagen Principal">
                                    <p class="current-image-label">Imagen actual</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Imágenes Adicionales: <small>(Máximo 4)</small></label>
                    
                    <?php if (count($imagenes_adicionales) > 0): ?>
                        <div class="current-images">
                            <h4>Imágenes actuales:</h4>
                            <div class="current-images-grid">
                                <?php foreach ($imagenes_adicionales as $img): ?>
                                    <div class="current-image-item">
                                        <img src="<?php echo PRODUCTOS_URL . '/' . $producto_id . '/galeria/' . $img['ruta_imagen']; ?>" alt="Imagen adicional">
                                        <div class="image-actions">
                                            <label class="checkbox-label">
                                                <input type="checkbox" name="eliminar_imagen[]" value="<?php echo $img['id']; ?>">
                                                Eliminar
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (count($imagenes_adicionales) < 4): ?>
                        <div class="image-uploader-multiple" id="adicionales-uploader">
                            <input type="file" name="imagenes_adicionales[]" id="imagenes_adicionales" accept="image/*" multiple>
                            <div class="upload-placeholder">
                                <i class="fas fa-images"></i>
                                <p>Arrastra imágenes aquí o haz clic para seleccionar</p>
                                <p class="small">Puedes añadir hasta <?php echo 4 - count($imagenes_adicionales); ?> imágenes más.</p>
                            </div>
                            <div class="image-previews" id="adicionales-preview"></div>
                        </div>
                        
                        <?php if (count($imagenes_adicionales) > 0): ?>
                            <div class="checkbox-toggle">
                                <input type="checkbox" id="reemplazar_adicionales" name="reemplazar_adicionales" value="1">
                                <label for="reemplazar_adicionales">Reemplazar todas las imágenes adicionales</label>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Tab: Especificaciones -->
            <div class="tab-content" id="tab-especificaciones">
                <p class="specs-info">Las especificaciones son opcionales. Agrega especificaciones relevantes para el producto.</p>
                
                <div id="specs-container">
                    <?php if (!empty($especificaciones)): ?>
                        <?php foreach ($especificaciones as $index => $spec): ?>
                            <div class="spec-row">
                                <div class="form-group">
                                    <input type="text" name="spec_nombre[]" placeholder="Nombre" value="<?php echo htmlspecialchars($spec['nombre_especificacion']); ?>">
                                </div>
                                <div class="form-group">
                                    <input type="text" name="spec_valor[]" placeholder="Valor" value="<?php echo htmlspecialchars($spec['valor_especificacion']); ?>">
                                </div>
                                <button type="button" class="btn-remove-spec"><i class="fas fa-times"></i></button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="spec-row">
                            <div class="form-group">
                                <input type="text" name="spec_nombre[]" placeholder="Nombre">
                            </div>
                            <div class="form-group">
                                <input type="text" name="spec_valor[]" placeholder="Valor">
                            </div>
                            <button type="button" class="btn-remove-spec"><i class="fas fa-times"></i></button>
                        </div>
                    <?php endif; ?>
                </div>
                
                <button type="button" id="btn-add-spec" class="btn-add-item">
                    <i class="fas fa-plus"></i> Añadir especificación
                </button>
                
                <?php if (isset($especificaciones_sugeridas[$producto['categoria_id']])): ?>
                    <div class="spec-suggestions">
                        <h3>Especificaciones sugeridas para esta categoría</h3>
                        <p>Haz clic en una sugerencia para añadirla:</p>
                        
                        <div class="suggestions-list">
                            <?php foreach ($especificaciones_sugeridas[$producto['categoria_id']] as $sugerencia): ?>
                                <div class="suggestion-item" data-nombre="<?php echo htmlspecialchars($sugerencia['nombre']); ?>">
                                    <?php echo htmlspecialchars($sugerencia['nombre']); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-actions">
                <a href="products.php" class="btn-cancel">Cancelar</a>
                <button type="submit" class="btn-submit">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<style>
.form-tabs {
    display: flex;
    margin-bottom: 20px;
    border-bottom: 1px solid #ddd;
}

.tab-btn {
    padding: 10px 20px;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    font-weight: 500;
    margin-right: 10px;
}

.tab-btn.active {
    border-bottom-color: #e53935;
    color: #e53935;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.product-header {
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.product-id {
    display: flex;
    gap: 20px;
    font-size: 14px;
    color: #666;
}

.product-id span {
    padding: 5px 10px;
    background-color: #f5f5f5;
    border-radius: 3px;
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

.current-image {
    text-align: center;
}

.current-image img {
    max-height: 200px;
    border-radius: 5px;
    border: 1px solid #ddd;
    padding: 5px;
}

.current-image-label {
    margin-top: 10px;
    font-size: 14px;
    color: #666;
}

.current-images {
    margin-bottom: 20px;
}

.current-images h4 {
    margin-bottom: 10px;
    font-size: 16px;
    font-weight: 500;
}

.current-images-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
}

.current-image-item {
    position: relative;
    text-align: center;
}

.current-image-item img {
    width: 100%;
    height: 150px;
    object-fit: contain;
    border-radius: 5px;
    border: 1px solid #ddd;
    padding: 5px;
}

.image-actions {
    margin-top: 5px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    font-size: 13px;
    cursor: pointer;
}

.checkbox-label input {
    margin-right: 5px;
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

.header-actions {
    display: flex;
    gap: 10px;
}

.btn-view {
    background-color: #2ecc71;
    color: white;
    padding: 8px 15px;
    border-radius: 5px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    text-decoration: none;
}

.btn-view:hover {
    background-color: #27ae60;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestión de pestañas
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tab = this.getAttribute('data-tab');
            
            // Desactivar todas las pestañas
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Activar la pestaña seleccionada
            this.classList.add('active');
            document.getElementById('tab-' + tab).classList.add('active');
        });
    });
    
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
            const currentImage = principalPreview.querySelector('.current-image');
            if (currentImage) {
                // Añadir etiqueta "Imagen anterior" y reducir opacidad
                const label = currentImage.querySelector('.current-image-label');
                if (label) {
                    label.textContent = 'Imagen anterior (será reemplazada)';
                }
                currentImage.style.opacity = '0.5';
            }
            
            // Mostrar nueva imagen seleccionada
            previsualizarImagen(this.files[0], principalPreview, true);
        });
    }
    
    // Previsualización de imágenes adicionales
    const imagenesAdicionalesInput = document.getElementById('imagenes_adicionales');
    const adicionalesPreview = document.getElementById('adicionales-preview');
    
    if (imagenesAdicionalesInput && adicionalesPreview) {
        imagenesAdicionalesInput.addEventListener('change', function() {
            adicionalesPreview.innerHTML = '';
            
            // Obtener el número máximo de imágenes permitidas
            const maxAllowed = parseInt(this.getAttribute('data-max') || '4');
            
            // Limitar a máximo permitido
            const files = Array.from(this.files).slice(0, maxAllowed);
            
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
                    // Nota: Esto no elimina el archivo del input file,
                    // se necesitaría una solución más compleja para eso
                });
                
                preview.appendChild(removeBtn);
                adicionalesPreview.appendChild(preview);
            });
            
            if (this.files.length > maxAllowed) {
                alert(`Solo se permiten un máximo de ${maxAllowed} imágenes adicionales. Se han seleccionado las primeras ${maxAllowed}.`);
            }
        });
    }
    
    // Checkbox para reemplazar todas las imágenes adicionales
    const reemplazarCheck = document.getElementById('reemplazar_adicionales');
    const imagenItems = document.querySelectorAll('.current-image-item');
    
    if (reemplazarCheck && imagenItems.length > 0) {
        reemplazarCheck.addEventListener('change', function() {
            if (this.checked) {
                // Deshabilitar checkboxes individuales y reducir opacidad
                imagenItems.forEach(item => {
                    const checkbox = item.querySelector('input[type="checkbox"]');
                    if (checkbox) {
                        checkbox.checked = true;
                        checkbox.disabled = true;
                    }
                    item.style.opacity = '0.5';
                });
            } else {
                // Rehabilitar checkboxes
                imagenItems.forEach(item => {
                    const checkbox = item.querySelector('input[type="checkbox"]');
                    if (checkbox) {
                        checkbox.checked = false;
                        checkbox.disabled = false;
                    }
                    item.style.opacity = '1';
                });
            }
        });
    }
    
    // Función para previsualizar una imagen
    function previsualizarImagen(file, previewElement, asNewImage = false) {
        if (!file) return;
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
            if (asNewImage) {
                // Añadir como nueva imagen
                const newPreview = document.createElement('div');
                newPreview.className = 'current-image new-image';
                newPreview.innerHTML = `
                    <img src="${e.target.result}" alt="Nueva imagen">
                    <p class="current-image-label">Nueva imagen seleccionada</p>
                `;
                previewElement.appendChild(newPreview);
            } else {
                // Reemplazar contenido existente
                previewElement.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
            }
        };
        
        reader.readAsDataURL(file);
    }
    
    // Gestión de especificaciones
    const btnAddSpec = document.getElementById('btn-add-spec');
    const specsContainer = document.getElementById('specs-container');
    
    if (btnAddSpec && specsContainer) {
        btnAddSpec.addEventListener('click', function() {
            agregarFilaEspecificacion();
        });
        
        // Delegación de eventos para botones de eliminar
        specsContainer.addEventListener('click', function(e) {
            if (e.target.closest('.btn-remove-spec')) {
                const specRow = e.target.closest('.spec-row');
                specRow.remove();
            }
        });
    }
    
    // Gestión de sugerencias de especificaciones
    const suggestionItems = document.querySelectorAll('.suggestion-item');
    
    suggestionItems.forEach(item => {
        item.addEventListener('click', function() {
            const nombre = this.getAttribute('data-nombre');
            agregarFilaEspecificacion(nombre, '');
        });
    });
    
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
        
        // Enfocar el último input para facilitar la entrada
        if (nombre) {
            const valorInput = specRow.querySelector('input[name="spec_valor[]"]');
            if (valorInput) valorInput.focus();
        } else {
            const nombreInput = specRow.querySelector('input[name="spec_nombre[]"]');
            if (nombreInput) nombreInput.focus();
        }
    }
    
    // Detección de cambio de categoría
    const categoriaSelect = document.getElementById('categoria_id');
    
    if (categoriaSelect) {
        categoriaSelect.addEventListener('change', function() {
            const categoriaSeleccionada = this.value;
            
            // Mostrar confirmación si hay especificaciones existentes
            if (specsContainer && specsContainer.querySelectorAll('.spec-row').length > 1) {
                if (confirm('¿Desea actualizar las sugerencias de especificaciones para la nueva categoría? Esto recargará la página.')) {
                    // Enviar el formulario para actualizar las sugerencias
                    document.getElementById('product-form').submit();
                }
            } else {
                // Si no hay especificaciones, actualizar directamente
                document.getElementById('product-form').submit();
            }
        });
    }
    
    // Validación del formulario antes de enviar
    const productForm = document.getElementById('product-form');
    if (productForm) {
        productForm.addEventListener('submit', function(e) {
            let hayErrores = false;
            let mensaje = '';
            
            // Validar precio en oferta
            const enOferta = document.getElementById('en_oferta');
            const precioRegular = parseFloat(document.getElementById('precio').value || 0);
            const precioOferta = parseFloat(document.getElementById('precio_oferta').value || 0);
            
            if (enOferta && enOferta.checked) {
                if (precioOferta <= 0) {
                    mensaje += '- El precio de oferta debe ser mayor que cero.\n';
                    hayErrores = true;
                }
                
                if (precioOferta >= precioRegular) {
                    mensaje += '- El precio de oferta debe ser menor que el precio regular.\n';
                    hayErrores = true;
                }
            }
            
            if (hayErrores) {
                e.preventDefault();
                alert('Por favor corrija los siguientes errores:\n' + mensaje);
            }
        });
    }
});
</script>

<?php
// Cargar el footer del admin
include_once 'includes/footer.php';
?>