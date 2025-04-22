<?php
/**
 * Panel de administración - Gestión de productos
 */

// Incluir archivos necesarios
require_once '../config.php';
require_once INCLUDES_PATH . '/functions.php';

// Verificar si hay una sesión activa (aquí iría la lógica de autenticación)

// Título de la página
$page_title = 'Gestión de Productos';

// Cargar el header del admin
include_once 'includes/header.php';

// Procesar acción si existe
$action = isset($_GET['action']) ? $_GET['action'] : '';
$success_message = '';
$error_message = '';

if ($action == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Aquí iría la lógica para eliminar el producto
    $success_message = 'Producto #' . $id . ' eliminado correctamente.';
}

// Obtener lista de productos con paginación
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Filtros
$filter_category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Construir consulta SQL con filtros
$where_clauses = [];
$params = [];

if ($filter_category > 0) {
    $where_clauses[] = "categoria_id = ?";
    $params[] = $filter_category;
}

if ($filter_status !== '') {
    $where_clauses[] = "activo = ?";
    $params[] = ($filter_status == 'active') ? 1 : 0;
}

if ($search !== '') {
    $where_clauses[] = "(nombre LIKE ? OR sku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Consulta para obtener el total de productos
$count_sql = "SELECT COUNT(*) AS total FROM productos $where_sql";
$total_result = get_row($count_sql, $params);
$total_products = $total_result ? $total_result['total'] : 0;

// Calcular total de páginas
$total_pages = ceil($total_products / $per_page);

// Consulta para obtener productos
$products_sql = "
    SELECT p.*, c.nombre AS categoria_nombre, m.nombre AS marca_nombre
    FROM productos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    LEFT JOIN marcas m ON p.marca_id = m.id
    $where_sql
    ORDER BY p.id DESC
    LIMIT $offset, $per_page
";

$productos = get_rows($products_sql, $params);

// Obtener categorías para el filtro
$categorias_sql = "SELECT id, nombre FROM categorias ORDER BY nombre";
$categorias = get_rows($categorias_sql);
?>

<div class="admin-actions">
    <div class="action-header">
        <h2>Gestión de Productos</h2>
        <div class="header-actions">
            <a href="product-add.php" class="btn-add">
                <i class="fas fa-plus"></i> Nuevo Producto
            </a>
            <a href="product-import.php" class="btn-import">
                <i class="fas fa-file-import"></i> Importar CSV
            </a>
        </div>
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
    
    <div class="filter-bar">
        <form method="get" action="" class="filter-form">
            <div class="filter-group">
                <label for="category">Categoría:</label>
                <select id="category" name="category">
                    <option value="0">Todas</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($filter_category == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="status">Estado:</label>
                <select id="status" name="status">
                    <option value="">Todos</option>
                    <option value="active" <?php echo ($filter_status == 'active') ? 'selected' : ''; ?>>Activos</option>
                    <option value="inactive" <?php echo ($filter_status == 'inactive') ? 'selected' : ''; ?>>Inactivos</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="search">Buscar:</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Nombre o SKU">
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn-filter">Filtrar</button>
                <a href="products.php" class="btn-reset">Reiniciar</a>
            </div>
        </form>
    </div>
    
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>SKU</th>
                    <th>Precio</th>
                    <th>Oferta</th>
                    <th>Stock</th>
                    <th>Categoría</th>
                    <th>Marca</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($productos)): ?>
                    <tr>
                        <td colspan="11" class="text-center">No se encontraron productos</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($productos as $producto): ?>
                        <tr>
                            <td><?php echo $producto['id']; ?></td>
                            <td class="product-image">
                                <img src="<?php echo get_imagen_producto($producto['id']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                            </td>
                            <td><?php echo htmlspecialchars(substr($producto['nombre'], 0, 30)) . (strlen($producto['nombre']) > 30 ? '...' : ''); ?></td>
                            <td><?php echo htmlspecialchars($producto['sku']); ?></td>
                            <td><?php echo formatear_precio($producto['precio']); ?></td>
                            <td>
                                <?php if ($producto['en_oferta'] && $producto['precio_oferta'] > 0): ?>
                                    <span class="price-offer"><?php echo formatear_precio($producto['precio_oferta']); ?></span>
                                <?php else: ?>
                                    <span class="no-offer">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($producto['stock'] > 0): ?>
                                    <span class="stock-available"><?php echo $producto['stock']; ?></span>
                                <?php else: ?>
                                    <span class="stock-out">0</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($producto['marca_nombre'] ?? 'N/A'); ?></td>
                            <td>
                                <?php if ($producto['activo']): ?>
                                    <span class="status active">Activo</span>
                                <?php else: ?>
                                    <span class="status inactive">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="product-edit.php?id=<?php echo $producto['id']; ?>" class="btn-action edit" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="javascript:void(0);" onclick="confirmDeleteProduct(<?php echo $producto['id']; ?>)" class="btn-action delete" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <a href="product-images.php?id=<?php echo $producto['id']; ?>" class="btn-action images" title="Gestionar imágenes">
                                    <i class="fas fa-images"></i>
                                </a>
                                <a href="<?php echo SITE_URL; ?>/producto.php?id=<?php echo $producto['id']; ?>" target="_blank" class="btn-action view" title="Ver en tienda">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="products.php?page=<?php echo ($page - 1); ?>&category=<?php echo $filter_category; ?>&status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search); ?>">&laquo;</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($page + 2, $total_pages); $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="products.php?page=<?php echo $i; ?>&category=<?php echo $filter_category; ?>&status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="products.php?page=<?php echo ($page + 1); ?>&category=<?php echo $filter_category; ?>&status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search); ?>">&raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function confirmDeleteProduct(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este producto? Esta acción no se puede deshacer.')) {
        window.location.href = 'products.php?action=delete&id=' + id;
    }
}
</script>

<?php
// Cargar el footer del admin
include_once 'includes/footer.php';
?>