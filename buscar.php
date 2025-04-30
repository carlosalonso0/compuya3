<?php
/**
 * Página de resultados de búsqueda
 */

// Incluir archivos necesarios
require_once 'config.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/functions_categories.php';

// Obtener término de búsqueda
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';

// Página actual para paginación
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$productos_por_pagina = 12;
$offset = ($pagina_actual - 1) * $productos_por_pagina;

// Buscar productos que coincidan con el término
$productos = [];
$total_productos = 0;

if (!empty($busqueda)) {
    // Consulta para obtener productos que coincidan
    $sql = "SELECT p.*, m.nombre as marca_nombre 
            FROM productos p
            LEFT JOIN marcas m ON p.marca_id = m.id
            WHERE (p.nombre LIKE ? OR p.descripcion LIKE ? OR m.nombre LIKE ?) 
            AND p.activo = 1
            ORDER BY p.nombre ASC
            LIMIT ? OFFSET ?";
    
    $busqueda_param = "%$busqueda%";
    $productos = get_rows($sql, [$busqueda_param, $busqueda_param, $busqueda_param, $productos_por_pagina, $offset]);
    
    // Obtener número total para paginación
    $sql_count = "SELECT COUNT(*) as total 
                FROM productos p
                LEFT JOIN marcas m ON p.marca_id = m.id
                WHERE (p.nombre LIKE ? OR p.descripcion LIKE ? OR m.nombre LIKE ?) 
                AND p.activo = 1";
    
    $result = get_row($sql_count, [$busqueda_param, $busqueda_param, $busqueda_param]);
    $total_productos = $result ? $result['total'] : 0;
}

// Calcular datos para paginación
$total_paginas = ceil($total_productos / $productos_por_pagina);

// Título de la página
$page_title = "Resultados de búsqueda: " . htmlspecialchars($busqueda);

// Cargar el header del sitio
include_once INCLUDES_PATH . '/header.php';
?>

<div class="search-results-page">
    <div class="new-container">
        <div class="page-header">
            <h1 class="page-title">
                <?php if (!empty($busqueda)): ?>
                    Resultados para: <span class="search-term"><?php echo htmlspecialchars($busqueda); ?></span>
                <?php else: ?>
                    Búsqueda de productos
                <?php endif; ?>
            </h1>
            
            <?php if (!empty($busqueda)): ?>
                <p class="results-count">
                    <?php echo $total_productos; ?> productos encontrados
                </p>
            <?php endif; ?>
        </div>
        
        <?php if (empty($busqueda)): ?>
            <div class="empty-search">
                <div class="empty-search-content">
                    <i class="fas fa-search fa-4x"></i>
                    <h2>Ingresa un término de búsqueda</h2>
                    <p>Escribe el nombre del producto, marca o categoría que estás buscando.</p>
                    
                    <form class="search-form-main" action="<?php echo SITE_URL; ?>/buscar.php" method="get">
                        <input type="text" name="q" placeholder="¿Qué estás buscando?" required>
                        <button type="submit"><i class="fas fa-search"></i> Buscar</button>
                    </form>
                </div>
            </div>
        <?php elseif (empty($productos)): ?>
            <div class="empty-results">
                <div class="empty-results-content">
                    <i class="fas fa-search fa-4x"></i>
                    <h2>No se encontraron productos</h2>
                    <p>Lo sentimos, no hemos encontrado resultados para "<?php echo htmlspecialchars($busqueda); ?>".</p>
                    <p>Sugerencias:</p>
                    <ul>
                        <li>Revisa la ortografía de las palabras</li>
                        <li>Utiliza palabras más generales</li>
                        <li>Prueba con sinónimos</li>
                    </ul>
                    
                    <form class="search-form-main" action="<?php echo SITE_URL; ?>/buscar.php" method="get">
                        <input type="text" name="q" value="<?php echo htmlspecialchars($busqueda); ?>" required>
                        <button type="submit"><i class="fas fa-search"></i> Buscar</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Filtros - Opcional -->
            <div class="search-filters">
                <div class="filter-group">
                    <label for="sort-by">Ordenar por:</label>
                    <select id="sort-by" onchange="applySorting(this.value)">
                        <option value="relevance">Relevancia</option>
                        <option value="price-asc">Precio: Menor a Mayor</option>
                        <option value="price-desc">Precio: Mayor a Menor</option>
                        <option value="name-asc">Nombre: A-Z</option>
                        <option value="name-desc">Nombre: Z-A</option>
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
                        <a href="<?php echo SITE_URL; ?>/buscar.php?q=<?php echo urlencode($busqueda); ?>&pagina=<?php echo ($pagina_actual - 1); ?>" class="page-link prev">
                            <i class="fas fa-chevron-left"></i> Anterior
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <?php if (
                            $i == 1 || 
                            $i == $total_paginas || 
                            ($i >= $pagina_actual - 2 && $i <= $pagina_actual + 2)
                        ): ?>
                            <a href="<?php echo SITE_URL; ?>/buscar.php?q=<?php echo urlencode($busqueda); ?>&pagina=<?php echo $i; ?>" 
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
                        <a href="<?php echo SITE_URL; ?>/buscar.php?q=<?php echo urlencode($busqueda); ?>&pagina=<?php echo ($pagina_actual + 1); ?>" class="page-link next">
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