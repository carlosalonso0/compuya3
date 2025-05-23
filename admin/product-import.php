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
// Función para generar SKU 

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
        switch ($categoria_id) {
            case 2: // Tarjetas Gráficas
                preg_match('/(RTX|GTX|GT|RX)\s*(\d+[A-Za-z]*)/', $nombre, $matches_serie);
                preg_match('/(\d+)GB/', $nombre, $matches_memoria);
                
                $serie = !empty($matches_serie[0]) ? str_replace(' ', '', $matches_serie[0]) : '';
                $memoria = !empty($matches_memoria[1]) ? $matches_memoria[1] : '';
                
                return "TG-{$marca2L}-{$serie}-{$memoria}G";
                
            case 3: // Procesadores
                if (strpos($nombre, 'Core') !== false) {
                    preg_match('/i(\d)-(\d+[A-Za-z]*)/', $nombre, $matches_modelo);
                    $modelo_cpu = !empty($matches_modelo[0]) ? str_replace(' ', '', $matches_modelo[0]) : '';
                    
                    preg_match('/(\d+)[vª]/', $nombre, $matches_gen);
                    $gen = !empty($matches_gen[1]) ? $matches_gen[1] : '';
                    
                    return "PR-{$marca3L}-{$modelo_cpu}{$gen}-001";
                } else {
                    preg_match('/Ryzen\s*(\d+)\s*(\d+[A-Za-z]*)/', $nombre, $matches_ryzen);
                    $ryzen_serie = !empty($matches_ryzen[1]) ? $matches_ryzen[1] : '';
                    $ryzen_modelo = !empty($matches_ryzen[2]) ? $matches_ryzen[2] : '';
                    
                    preg_match('/(\d+)[vª]/', $nombre, $matches_gen);
                    $gen = !empty($matches_gen[1]) ? $matches_gen[1] : '';
                    
                    return "PR-{$marca3L}-R{$ryzen_serie}-{$ryzen_modelo}{$gen}";
                }
                
            case 5: // Placas Madre
                preg_match('/(Z\d+|B\d+|H\d+|X\d+[A-Z]*|A\d+[A-Z]*)/', $nombre, $matches_chipset);
                preg_match('/(LGA\d+|AM\d+)/', $nombre, $matches_socket);
                
                $chipset = !empty($matches_chipset[1]) ? $matches_chipset[1] : '';
                $socket = !empty($matches_socket[1]) ? $matches_socket[1] : '';
                
                return "PM-{$marca3L}-{$chipset}-{$socket}";
                
            case 8: // Impresoras
                preg_match('/(SmartTank|EcoTank|DeskJet)\s*(\d+[A-Za-z]*)/', $nombre, $matches_serie);
                $serie = !empty($matches_serie[1]) ? $matches_serie[1] : '';
                $modelo_imp = !empty($matches_serie[2]) ? $matches_serie[2] : '';
                
                return "IM-{$marca2L}-{$modelo_imp}-001";
                
            default:
                return "GEN-{$marca2L}-" . substr(md5($nombre), 0, 6) . "-001";
        }
    }
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