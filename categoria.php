<?php
/**
 * Página de categoría con URLs amigables
 */

// Incluir archivos necesarios
require_once 'config.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/functions_categories.php';

// Obtener slug o ID de la categoría
$categoria_id = null;
$categoria_slug = null;

if (isset($_GET['id'])) {
    $categoria_id = (int)$_GET['id'];
    $categoria = get_category($categoria_id);
    
    // Redireccionar a URL amigable si tenemos la información
    if ($categoria && !empty($categoria['slug'])) {
        header("Location: " . SITE_URL . "/categoria/" . $categoria['slug']);
        exit;
    }
} elseif (isset($_GET['slug'])) {
    $categoria_slug = $_GET['slug'];
    $categoria = get_category_by_slug($categoria_slug);
    
    if ($categoria) {
        $categoria_id = $categoria['id'];
    }
}

// Si no se encontró la categoría, mostrar página 404
if (!$categoria) {
    header("HTTP/1.0 404 Not Found");
    include_once 'error-404.php';
    exit;
}

// Página actual para paginación
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$productos_por_pagina = 12;
$offset = ($pagina_actual - 1) * $productos_por_pagina;

// Obtener productos de la categoría
$productos = [];
$total_productos = 0;

// Incluir subcategorías si existen
$categorias_ids = [$categoria_id];
$subcategorias = get_subcategories($categoria_id);

if (!empty($subcategorias)) {
    foreach ($subcategorias as $subcategoria) {
        $categorias_ids[] = $subcategoria['id'];
    }
}

// Cadena de IDs para la consulta SQL
$ids_string = implode(',', $categorias_ids);

// Orden por defecto
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'nombre-asc';

// Configurar ordenamiento
$order_by = "p.nombre ASC"; // Por defecto ordenar por nombre

switch ($orden) {
    case 'precio-asc':
        $order_by = "p.precio ASC, p.nombre ASC";
        break;
    case 'precio-desc':
        $order_by = "p.precio DESC, p.nombre ASC";
        break;
    case 'nombre-desc':
        $order_by = "p.nombre DESC";
        break;
    case 'recientes':
        $order_by = "p.fecha_creacion DESC, p.nombre ASC";
        break;
    case 'populares':
        $order_by = "p.stock ASC, p.nombre ASC"; // Como ejemplo - en un sitio real sería por ventas
        break;
}

// Consulta para obtener productos
$sql = "SELECT p.*, m.nombre as marca_nombre 
        FROM productos p
        LEFT JOIN marcas m ON p.marca_id = m.id
        WHERE p.categoria_id IN ({$ids_string}) AND p.activo = 1
        ORDER BY {$order_by}
        LIMIT ? OFFSET ?";

$productos = get_rows($sql, [$productos_por_pagina, $offset]);

// Obtener número total para paginación
$sql_count = "SELECT COUNT(*) as total 
            FROM productos p
            WHERE p.categoria_id IN ({$ids_string}) AND p.activo = 1";

$result = get_row($sql_count);
$total_productos = $result ? $result['total'] : 0;

// Calcular datos para paginación
$total_paginas = ceil($total_productos / $productos_por_pagina);

// Obtener ruta de breadcrumb
$breadcrumb_path = get_category_path($categoria_id);

// Título de la página
$page_title = $categoria['nombre'];

// Cargar el header del sitio
include_once INCLUDES_PATH . '/header.php';
?>

<div class="category-page">
    <div class="new-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="<?php echo SITE_URL; ?>">Inicio</a>
            <?php foreach ($breadcrumb_path as $item): ?>
                <span class="separator">/</span>
                <?php if ($item['id'] == $categoria_id): ?>
                    <span class="current"><?php echo htmlspecialchars($item['nombre']); ?></span>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/categoria/<?php echo $item['slug']; ?>"><?php echo htmlspecialchars($item['nombre']); ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        
        <div class="page-header">
            <h1 class="page-title"><?php echo htmlspecialchars($categoria['nombre']); ?></h1>
            
            <?php if (!empty($categoria['descripcion'])): ?>
                <div class="category-description">
                    <?php echo htmlspecialchars($categoria['descripcion']); ?>
                </div>
            <?php endif; ?>
            
            <p class="results-count">
                <?php echo $total_productos; ?> productos encontrados
            </p>
        </div>
        
        <?php if (!empty($subcategorias)): ?>
            <div class="subcategories-list">
                <?php foreach ($subcategorias as $subcategoria): ?>
                    <a href="<?php echo SITE_URL; ?>/categoria/<?php echo $subcategoria['slug']; ?>" class="subcategory-item">
                        <?php echo htmlspecialchars($subcategoria['nombre']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($productos)): ?>
            <div class="empty-category">
                <div class="empty-category-content">
                    <i class="fas fa-box-open fa-4x"></i>
                    <h2>No hay productos disponibles</h2>
                    <p>Actualmente no hay productos disponibles en esta categoría.</p>
                    <p>Por favor, vuelva a consultar más tarde o explore otras categorías.</p>
                    <a href="<?php echo SITE_URL; ?>" class="btn-back-home">Volver al inicio</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Filtros -->
            <div class="category-filters">
                <div class="filter-group">
                    <label for="sort-by">Ordenar por:</label>
                    <select id="sort-by" onchange="applySorting(this.value)">
                        <option value="nombre-asc">Nombre: A-Z</option>
                        <option value="nombre-desc">Nombre: Z-A</option>
                        <option value="precio-asc">Precio: Menor a Mayor</option>
                        <option value="precio-desc">Precio: Mayor a Menor</option>
                        <option value="recientes">Más recientes</option>
                        <option value="populares">Más populares</option>
                    </select>
                </div>
            </div>
            
            <!-- Productos -->
            <div class="products-grid">
                <?php foreach ($productos as $producto): ?>
                    <div class="product-card-wrapper">
                        <?php include INCLUDES_PATH . '/components/product-card.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Paginación -->
            <?php if ($total_paginas > 1): ?>
                <div class="pagination">
                    <?php if ($pagina_actual > 1): ?>
                        <a href="<?php echo SITE_URL; ?>/categoria/<?php echo $categoria['slug']; ?>?pagina=<?php echo ($pagina_actual - 1); ?>&orden=<?php echo $orden; ?>" class="page-link prev">
                            <i class="fas fa-chevron-left"></i> Anterior
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <?php if (
                            $i == 1 || 
                            $i == $total_paginas || 
                            ($i >= $pagina_actual - 2 && $i <= $pagina_actual + 2)
                        ): ?>
                            <a href="<?php echo SITE_URL; ?>/categoria/<?php echo $categoria['slug']; ?>?pagina=<?php echo $i; ?>&orden=<?php echo $orden; ?>" 
                               class="page-link <?php echo ($i == $pagina_actual) ? 'current' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php elseif (
                            ($i == 2 && $pagina_actual - 2 > 2) || 
                            ($i == $total_paginas - 1 && $pagina_actual + 2 < $total_paginas - 1)
                        ): ?>
                            <span class="page-ellipsis">...</span>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($pagina_actual < $total_paginas): ?>
                        <a href="<?php echo SITE_URL; ?>/categoria/<?php echo $categoria['slug']; ?>?pagina=<?php echo ($pagina_actual + 1); ?>&orden=<?php echo $orden; ?>" class="page-link next">
                            Siguiente <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Función para aplicar el orden a los resultados
function applySorting(sortValue) {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('orden', sortValue);
    
    // Si estamos en una página diferente a la 1, volver a la primera página
    if (currentUrl.searchParams.get('pagina') !== null && currentUrl.searchParams.get('pagina') !== '1') {
        currentUrl.searchParams.set('pagina', '1');
    }
    
    window.location.href = currentUrl.toString();
}

// Seleccionar el valor actual del ordenamiento
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const sortBy = urlParams.get('orden');
    
    if (sortBy) {
        document.getElementById('sort-by').value = sortBy;
    }
});
</script>

<?php
// Cargar el footer del sitio
include_once INCLUDES_PATH . '/footer.php';
?>