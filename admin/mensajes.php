<?php
/**
 * Panel de administración - Gestión de mensajes de contacto
 */

// Incluir archivos necesarios
require_once '../config.php';
require_once INCLUDES_PATH . '/functions.php';

// Título de la página
$page_title = 'Gestión de Mensajes de Contacto';

// Cargar el header del admin
include_once 'includes/header.php';

// Procesar acción si existe
$action = isset($_GET['action']) ? $_GET['action'] : '';
$success_message = '';
$error_message = '';

// Marcar mensaje como leído
if ($action == 'marcar_leido' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $sql = "UPDATE mensajes_contacto SET leido = 1 WHERE id = ?";
    $result = update($sql, [$id]);
    
    if ($result) {
        $success_message = 'Mensaje #' . $id . ' marcado como leído.';
    } else {
        $error_message = 'Error al actualizar el estado del mensaje.';
    }
}

// Marcar mensaje como respondido
if ($action == 'marcar_respondido' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $sql = "UPDATE mensajes_contacto SET respondido = 1 WHERE id = ?";
    $result = update($sql, [$id]);
    
    if ($result) {
        $success_message = 'Mensaje #' . $id . ' marcado como respondido.';
    } else {
        $error_message = 'Error al actualizar el estado del mensaje.';
    }
}

// Eliminar mensaje
if ($action == 'eliminar' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $sql = "DELETE FROM mensajes_contacto WHERE id = ?";
    $result = update($sql, [$id]);
    
    if ($result) {
        $success_message = 'Mensaje #' . $id . ' eliminado correctamente.';
    } else {
        $error_message = 'Error al eliminar el mensaje.';
    }
}

// Página actual para paginación
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$mensajes_por_pagina = 10;
$offset = ($pagina_actual - 1) * $mensajes_por_pagina;

// Filtros
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Construir consulta SQL con filtros
$where_clauses = [];
$params = [];

if ($filter_status === 'no_leidos') {
    $where_clauses[] = "leido = 0";
} elseif ($filter_status === 'leidos') {
    $where_clauses[] = "leido = 1";
} elseif ($filter_status === 'respondidos') {
    $where_clauses[] = "respondido = 1";
} elseif ($filter_status === 'no_respondidos') {
    $where_clauses[] = "respondido = 0";
}

if ($search !== '') {
    $where_clauses[] = "(nombre LIKE ? OR email LIKE ? OR asunto LIKE ? OR mensaje LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Consulta para obtener el total de mensajes
$count_sql = "SELECT COUNT(*) AS total FROM mensajes_contacto $where_sql";
$total_result = get_row($count_sql, $params);
$total_mensajes = $total_result ? $total_result['total'] : 0;

// Calcular total de páginas
$total_paginas = ceil($total_mensajes / $mensajes_por_pagina);

// Consulta para obtener mensajes
$mensajes_sql = "
    SELECT * FROM mensajes_contacto
    $where_sql
    ORDER BY fecha_creacion DESC
    LIMIT $mensajes_por_pagina OFFSET $offset
";

$mensajes = get_rows($mensajes_sql, $params);
?>

<div class="admin-actions">
    <div class="action-header">
        <h2>Gestión de Mensajes de Contacto</h2>
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
                <label for="status">Estado:</label>
                <select id="status" name="status">
                    <option value="">Todos</option>
                    <option value="no_leidos" <?php echo ($filter_status == 'no_leidos') ? 'selected' : ''; ?>>No leídos</option>
                    <option value="leidos" <?php echo ($filter_status == 'leidos') ? 'selected' : ''; ?>>Leídos</option>
                    <option value="respondidos" <?php echo ($filter_status == 'respondidos') ? 'selected' : ''; ?>>Respondidos</option>
                    <option value="no_respondidos" <?php echo ($filter_status == 'no_respondidos') ? 'selected' : ''; ?>>No respondidos</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="search">Buscar:</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Nombre, Email, Asunto...">
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn-filter">Filtrar</button>
                <a href="mensajes.php" class="btn-reset">Reiniciar</a>
            </div>
        </form>
    </div>
    
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Asunto</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($mensajes)): ?>
                    <tr>
                        <td colspan="7" class="text-center">No se encontraron mensajes</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($mensajes as $mensaje): ?>
                        <tr class="<?php echo (!$mensaje['leido']) ? 'no-leido' : ''; ?>">
                            <td><?php echo $mensaje['id']; ?></td>
                            <td><?php echo htmlspecialchars($mensaje['nombre']); ?></td>
                            <td><a href="mailto:<?php echo $mensaje['email']; ?>"><?php echo htmlspecialchars($mensaje['email']); ?></a></td>
                            <td><?php echo htmlspecialchars($mensaje['asunto']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($mensaje['fecha_creacion'])); ?></td>
                            <td>
                                <?php if (!$mensaje['leido']): ?>
                                    <span class="status inactive">No leído</span>
                                <?php elseif ($mensaje['respondido']): ?>
                                    <span class="status active">Respondido</span>
                                <?php else: ?>
                                    <span class="status pending">Leído</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="mensaje-ver.php?id=<?php echo $mensaje['id']; ?>" class="btn-action view" title="Ver mensaje">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if (!$mensaje['leido']): ?>
                                    <a href="mensajes.php?action=marcar_leido&id=<?php echo $mensaje['id']; ?>" class="btn-action edit" title="Marcar como leído">
                                        <i class="fas fa-check"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (!$mensaje['respondido'] && $mensaje['leido']): ?>
                                    <a href="mensajes.php?action=marcar_respondido&id=<?php echo $mensaje['id']; ?>" class="btn-action edit" title="Marcar como respondido">
                                        <i class="fas fa-reply"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $mensaje['id']; ?>)" class="btn-action delete" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($total_paginas > 1): ?>
        <div class="pagination">
            <?php if ($pagina_actual > 1): ?>
                <a href="mensajes.php?pagina=<?php echo ($pagina_actual - 1); ?>&status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search); ?>">&laquo;</a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $pagina_actual - 2); $i <= min($pagina_actual + 2, $total_paginas); $i++): ?>
                <?php if ($i == $pagina_actual): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="mensajes.php?pagina=<?php echo $i; ?>&status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($pagina_actual < $total_paginas): ?>
                <a href="mensajes.php?pagina=<?php echo ($pagina_actual + 1); ?>&status=<?php echo $filter_status; ?>&search=<?php echo urlencode($search); ?>">&raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function confirmDelete(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este mensaje? Esta acción no se puede deshacer.')) {
        window.location.href = 'mensajes.php?action=eliminar&id=' + id;
    }
}
</script>

<style>
.no-leido {
    font-weight: bold;
    background-color: #f5f5f5;
}
</style>

<?php
// Cargar el footer del admin
include_once 'includes/footer.php';
?>