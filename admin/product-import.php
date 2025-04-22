<?php
/**
 * Panel de administración - Importar productos desde CSV
 */

// Incluir archivos necesarios
require_once '../config.php';
require_once INCLUDES_PATH . '/functions.php';

// Verificar si hay una sesión activa (aquí iría la lógica de autenticación)

// Título de la página
$page_title = 'Importar Productos';

// Variable para mensajes
$message = '';
$error = '';

// Procesar formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    // Verificar errores
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Error al subir el archivo: ' . getUploadError($file['error']);
    } else {
        // Verificar tipo de archivo
        $mime_type = mime_content_type($file['tmp_name']);
        if ($mime_type !== 'text/csv' && $mime_type !== 'text/plain') {
            $error = 'El archivo debe ser un CSV válido.';
        } else {
            // Procesar el archivo CSV
            $result = processProductCSV($file['tmp_name']);
            if ($result['success']) {
                $message = 'Se importaron ' . $result['count'] . ' productos correctamente.';
            } else {
                $error = 'Error al importar productos: ' . $result['message'];
            }
        }
    }
}

// Función para procesar el CSV
function processProductCSV($file_path) {
    try {
        // Abrir archivo
        $handle = fopen($file_path, 'r');
        if (!$handle) {
            return ['success' => false, 'message' => 'No se pudo abrir el archivo.'];
        }
        
        // Obtener cabeceras
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return ['success' => false, 'message' => 'El archivo está vacío o no tiene el formato correcto.'];
        }
        
        // Normalizar cabeceras
        $expected_headers = ['nombre', 'precio', 'precio_oferta', 'en_oferta', 'stock', 'descripcion', 'marca_id', 'categoria_id', 'modelo'];
        $headers = array_map('trim', $headers);
        $headers = array_map('strtolower', $headers);
        
        // Verificar cabeceras
        foreach ($expected_headers as $expected) {
            if (!in_array($expected, $headers)) {
                fclose($handle);
                return ['success' => false, 'message' => 'Falta la columna: ' . $expected];
            }
        }
        
        // Mapear índices de columnas
        $column_map = [];
        foreach ($expected_headers as $header) {
            $column_map[$header] = array_search($header, $headers);
        }
        
        // Iniciar transacción manualmente con la conexión mysqli
        db()->autocommit(FALSE);
        $transaction_active = true;
        
        // Procesar filas
        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < count($headers)) {
                continue; // Saltar filas incompletas
            }
            
            // Extraer datos
            $nombre = trim($row[$column_map['nombre']]);
            $precio = floatval(trim($row[$column_map['precio']]));
            $precio_oferta = floatval(trim($row[$column_map['precio_oferta']]));
            $en_oferta = intval(trim($row[$column_map['en_oferta']]));
            $stock = intval(trim($row[$column_map['stock']]));
            $descripcion = trim($row[$column_map['descripcion']]);
            $marca_id = intval(trim($row[$column_map['marca_id']]));
            $categoria_id = intval(trim($row[$column_map['categoria_id']]));
            $modelo = trim($row[$column_map['modelo']]);
            
            // Generar slug
            $slug = generateSlug($nombre);
            
            // Generar SKU según categoría
            $sku = generateProductSKU($nombre, $marca_id, $modelo, $categoria_id);
            
            // Insertar producto
            $sql = "INSERT INTO productos 
                    (nombre, slug, sku, precio, precio_oferta, en_oferta, stock, descripcion, 
                     marca_id, categoria_id, modelo, activo, fecha_creacion, fecha_actualizacion) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())";
            
            $params = [
                $nombre, $slug, $sku, $precio, $precio_oferta, $en_oferta, $stock, 
                $descripcion, $marca_id, $categoria_id, $modelo
            ];
            
            $product_id = insert($sql, $params);
            
            if (!$product_id) {
                // Revertir transacción en caso de error
                if ($transaction_active) {
                    db()->rollback();
                    $transaction_active = false;
                }
                fclose($handle);
                return ['success' => false, 'message' => 'Error al insertar el producto: ' . $nombre];
            }
            
            $count++;
        }
        
        // Confirmar transacción
        if ($transaction_active) {
            db()->commit();
        }
        fclose($handle);
        
        return [
            'success' => true,
            'count' => $count
        ];
    } catch (Exception $e) {
        if (isset($handle) && $handle) {
            fclose($handle);
        }
        
        // Revertir transacción si está activa
        if (isset($transaction_active) && $transaction_active) {
            db()->rollback();
        }
        
        return [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

// Función para generar slug
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

// Función para generar SKU 
function generateProductSKU($nombre, $marca_id, $modelo, $categoria_id) {
    // Determinar tipo de producto por categoría
    $tipo_producto = '';
    switch ($categoria_id) {
        case 2: $tipo_producto = 'tarjeta_grafica'; break;
        case 3: $tipo_producto = 'procesador'; break; 
        case 4: $tipo_producto = 'case'; break;
        case 6: $tipo_producto = 'laptop'; break;
        case 7: $tipo_producto = 'pc_gamer'; break;
        case 8: $tipo_producto = 'impresora'; break;
        case 5: $tipo_producto = 'placa_madre'; break;
        case 9: $tipo_producto = 'monitor'; break;
        default: $tipo_producto = 'otro';
    }
    
    // Obtener marca
    $marca = '';
    $sql = "SELECT nombre FROM marcas WHERE id = ?";
    $marca_result = get_row($sql, [$marca_id]);
    if ($marca_result) {
        $marca = $marca_result['nombre'];
    }
    
    // Extraer información para el SKU
    $extra = [];
    
    // Construir extra según el tipo de producto
    switch ($tipo_producto) {
        case 'tarjeta_grafica':
            // Extraer memoria de la descripción o nombre
            preg_match('/(\d+)GB/', $nombre, $matches);
            $extra['memoria'] = isset($matches[1]) ? $matches[1].'G' : '00G';
            break;
            
        case 'pc_gamer':
            $extra['nivel'] = 'STD';
            if (stripos($nombre, 'extreme') !== false) $extra['nivel'] = 'XTR';
            if (stripos($nombre, 'gaming') !== false) $extra['nivel'] = 'GMR';
            
            // Extraer CPU
            preg_match('/(i[3579]|RYZEN|R[3579])/', $nombre, $matches);
            $extra['cpu'] = isset($matches[1]) ? $matches[1] : 'CPU';
            
            // Extraer GPU
            preg_match('/(GTX|RTX|RX)\s*(\d+)/', $nombre, $matches);
            $extra['gpu'] = isset($matches[0]) ? str_replace(' ', '', $matches[0]) : 'GPU';
            
            // Extraer RAM
            preg_match('/(\d+)GB/', $nombre, $matches);
            $extra['ram'] = isset($matches[1]) ? $matches[1].'G' : '00G';
            break;
            
        case 'monitor':
            // Extraer tamaño
            preg_match('/(\d+(\.\d+)?)\"/', $nombre, $matches);
            $extra['tamano'] = isset($matches[1]) ? $matches[1] : '00';
            break;
            
        case 'case':
            $extra['color'] = 'NE'; // Negro por defecto
            if (stripos($nombre, 'blanc') !== false) $extra['color'] = 'BL';
            if (stripos($nombre, 'roj') !== false) $extra['color'] = 'RO';
            break;
            
        // Otros tipos de producto...
    }
    
    // Generar ID único aleatorio
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $id3d = '';
    for ($i = 0; $i < 3; $i++) {
        $id3d .= $chars[rand(0, strlen($chars) - 1)];
    }
    
    // Generar SKU básico según tipo
    $marca2L = substr(preg_replace('/[^A-Za-z0-9]/', '', $marca), 0, 2);
    $modelo_clean = preg_replace('/[^A-Za-z0-9]/', '', $modelo);
    
    $sku = '';
    switch ($tipo_producto) {
        case 'tarjeta_grafica':
            $sku = "TG-{$marca2L}-{$modelo_clean}-{$extra['memoria']}-{$id3d}";
            break;
        case 'procesador':
            $sku = "CPU-" . substr($marca2L, 0, 1) . "-{$modelo_clean}-{$id3d}";
            break;
        case 'case':
            $sku = "CASE-{$marca2L}-{$modelo_clean}-{$extra['color']}-{$id3d}";
            break;
        case 'monitor':
            $sku = "MON-{$marca2L}-{$modelo_clean}-{$extra['tamano']}-{$id3d}";
            break;
        case 'pc_gamer':
            $sku = "PC-{$extra['nivel']}-{$extra['cpu']}-{$extra['gpu']}-{$extra['ram']}-{$id3d}";
            break;
        default:
            $sku = strtoupper($tipo_producto) . "-{$marca2L}-{$modelo_clean}-{$id3d}";
    }
    
    // Normalizar SKU (eliminar caracteres no deseados)
    $sku = preg_replace('/[^A-Z0-9\-]/', '', strtoupper($sku));
    
    return $sku;
}

// Función para traducir códigos de error de upload
function getUploadError($code) {
    switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
            return 'El archivo excede el tamaño máximo permitido por el servidor.';
        case UPLOAD_ERR_FORM_SIZE:
            return 'El archivo excede el tamaño máximo permitido por el formulario.';
        case UPLOAD_ERR_PARTIAL:
            return 'El archivo fue subido parcialmente.';
        case UPLOAD_ERR_NO_FILE:
            return 'No se subió ningún archivo.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Falta una carpeta temporal.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Error al escribir el archivo en el disco.';
        case UPLOAD_ERR_EXTENSION:
            return 'Una extensión de PHP detuvo la subida del archivo.';
        default:
            return 'Error desconocido al subir el archivo.';
    }
}

// Cargar el header del admin
include_once 'includes/header.php';
?>

<div class="admin-actions">
    <div class="action-header">
        <h2>Importar Productos desde CSV</h2>
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
        <div class="csv-instructions">
            <h3>Instrucciones</h3>
            <p>Sube un archivo CSV con los siguientes campos:</p>
            <ul>
                <li><strong>nombre</strong>: Nombre completo del producto</li>
                <li><strong>precio</strong>: Precio regular (usar punto como separador decimal)</li>
                <li><strong>precio_oferta</strong>: Precio en oferta (usar punto como separador decimal)</li>
                <li><strong>en_oferta</strong>: 1 si está en oferta, 0 si no</li>
                <li><strong>stock</strong>: Cantidad disponible</li>
                <li><strong>descripcion</strong>: Descripción detallada del producto</li>
                <li><strong>marca_id</strong>: ID de la marca</li>
                <li><strong>categoria_id</strong>: ID de la categoría</li>
                <li><strong>modelo</strong>: Modelo o referencia del producto</li>
            </ul>
            <p>El SKU y el slug se generarán automáticamente.</p>
            <p><a href="<?php echo SITE_URL; ?>/assets/templates/productos_template.csv" download>Descargar plantilla</a></p>
        </div>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="csv_file">Archivo CSV:</label>
                <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
            </div>
            
            <div class="form-actions">
                <a href="products.php" class="btn-cancel">Cancelar</a>
                <button type="submit" class="btn-submit">Importar Productos</button>
            </div>
        </form>
    </div>
</div>

<?php
// Cargar el footer del admin
include_once 'includes/footer.php';
?>