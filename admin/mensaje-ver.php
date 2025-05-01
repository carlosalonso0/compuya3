<?php
/**
 * Panel de administración - Ver mensaje de contacto
 */

// Incluir archivos necesarios
require_once '../config.php';
require_once INCLUDES_PATH . '/functions.php';

// Verificar que se proporcione un ID
if (!isset($_GET['id'])) {
    // Redirigir si no hay ID
    header('Location: mensajes.php');
    exit;
}

$id = (int)$_GET['id'];

// Obtener información del mensaje
$sql = "SELECT * FROM mensajes_contacto WHERE id = ?";
$mensaje = get_row($sql, [$id]);

// Si el mensaje no existe, redirigir
if (!$mensaje) {
    header('Location: mensajes.php');
    exit;
}

// Marcar como leído si no lo está
if (!$mensaje['leido']) {
    $sql = "UPDATE mensajes_contacto SET leido = 1 WHERE id = ?";
    update($sql, [$id]);
    
    // Actualizar el estado en la variable local
    $mensaje['leido'] = 1;
}

// Procesar acción si existe
$action = isset($_GET['action']) ? $_GET['action'] : '';
$success_message = '';
$error_message = '';

// Marcar como respondido
if ($action == 'marcar_respondido') {
    $sql = "UPDATE mensajes_contacto SET respondido = 1 WHERE id = ?";
    $result = update($sql, [$id]);
    
    if ($result) {
        $success_message = 'Mensaje marcado como respondido.';
        // Actualizar el estado en la variable local
        $mensaje['respondido'] = 1;
    } else {
        $error_message = 'Error al actualizar el estado del mensaje.';
    }
}

// Título de la página
$page_title = 'Ver Mensaje #' . $id;

// Cargar el header del admin
include_once 'includes/header.php';
?>

<div class="admin-actions">
    <div class="action-header">
        <h2>Ver Mensaje #<?php echo $id; ?></h2>
        <a href="mensajes.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Volver a Mensajes
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
    
    <div class="message-details">
        <div class="message-header">
            <div class="message-meta">
                <div class="message-status">
                    <?php if (!$mensaje['leido']): ?>
                        <span class="status inactive">No leído</span>
                    <?php elseif ($mensaje['respondido']): ?>
                        <span class="status active">Respondido</span>
                    <?php else: ?>
                        <span class="status pending">Leído</span>
                    <?php endif; ?>
                </div>
                <div class="message-date">
                    Recibido: <?php echo date('d/m/Y H:i', strtotime($mensaje['fecha_creacion'])); ?>
                </div>
            </div>

            <div class="message-info">
                <div class="info-group">
                    <span class="info-label">De:</span>
                    <span class="info-value"><?php echo htmlspecialchars($mensaje['nombre']); ?> &lt;<?php echo htmlspecialchars($mensaje['email']); ?>&gt;</span>
                </div>
                
                <?php if (!empty($mensaje['telefono'])): ?>
                    <div class="info-group">
                        <span class="info-label">Teléfono:</span>
                        <span class="info-value"><?php echo htmlspecialchars($mensaje['telefono']); ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="info-group">
                    <span class="info-label">Asunto:</span>
                    <span class="info-value"><?php echo htmlspecialchars($mensaje['asunto']); ?></span>
                </div>
            </div>
        </div>
        
        <div class="message-content">
            <div class="message-body">
                <?php echo nl2br(htmlspecialchars($mensaje['mensaje'])); ?>
            </div>
        </div>
        
        <div class="message-actions">
            <?php if (!$mensaje['respondido']): ?>
                <a href="mensaje-ver.php?id=<?php echo $id; ?>&action=marcar_respondido" class="btn-respond">
                    <i class="fas fa-reply"></i> Marcar como Respondido
                </a>
            <?php endif; ?>
            
            <a href="mailto:<?php echo $mensaje['email']; ?>?subject=Re: <?php echo urlencode($mensaje['asunto']); ?>" class="btn-email">
                <i class="fas fa-envelope"></i> Responder por Email
            </a>
            
            <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $id; ?>)" class="btn-delete">
                <i class="fas fa-trash"></i> Eliminar Mensaje
            </a>
        </div>
    </div>
</div>

<style>
.message-details {
    background-color: #fff;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    margin-bottom: 30px;
}

.message-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.message-meta {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
}

.message-status {
    margin-bottom: 10px;
}

.message-date {
    color: #777;
    font-size: 14px;
}

.message-info {
    margin-bottom: 10px;
}

.info-group {
    margin-bottom: 8px;
}

.info-label {
    font-weight: bold;
    margin-right: 10px;
    color: #555;
}

.info-value {
    color: #333;
}

.message-content {
    padding: 20px;
    background-color: #f9f9f9;
    min-height: 200px;
}

.message-body {
    line-height: 1.6;
    color: #333;
    white-space: pre-line;
}

.message-actions {
    padding: 20px;
    display: flex;
    gap: 15px;
    border-top: 1px solid #eee;
}

.btn-respond,
.btn-email,
.btn-delete {
    padding: 10px 15px;
    border-radius: 4px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    transition: background-color 0.3s;
}

.btn-respond {
    background-color: #4caf50;
    color: white;
}

.btn-respond:hover {
    background-color: #388e3c;
}

.btn-email {
    background-color: #2196f3;
    color: white;
}

.btn-email:hover {
    background-color: #1976d2;
}

.btn-delete {
    background-color: #f44336;
    color: white;
}

.btn-delete:hover {
    background-color: #d32f2f;
}

@media (max-width: 768px) {
    .message-actions {
        flex-direction: column;
    }
    
    .btn-respond,
    .btn-email,
    .btn-delete {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
function confirmDelete(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este mensaje? Esta acción no se puede deshacer.')) {
        window.location.href = 'mensajes.php?action=eliminar&id=' + id;
    }
}
</script>

<?php
// Cargar el footer del admin
include_once 'includes/footer.php';
?>