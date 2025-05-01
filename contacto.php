<?php
/**
 * Página de contacto
 */

// Incluir archivos necesarios
require_once 'config.php';
require_once INCLUDES_PATH . '/functions.php';

// Variables para mensajes
$success_message = '';
$error_message = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
    $asunto = isset($_POST['asunto']) ? trim($_POST['asunto']) : '';
    $mensaje = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';
    
    // Validación básica
    if (empty($nombre)) {
        $error_message = 'Por favor, ingresa tu nombre.';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Por favor, ingresa un correo electrónico válido.';
    } elseif (empty($asunto)) {
        $error_message = 'Por favor, ingresa un asunto.';
    } elseif (empty($mensaje)) {
        $error_message = 'Por favor, ingresa tu mensaje.';
    } else {
        // Todo está correcto, guardar en la base de datos
        $sql = "INSERT INTO mensajes_contacto (nombre, email, telefono, asunto, mensaje) 
                VALUES (?, ?, ?, ?, ?)";
        $params = [$nombre, $email, $telefono, $asunto, $mensaje];
        
        $insert_id = insert($sql, $params);
        
        if ($insert_id) {
            $success_message = 'Tu mensaje ha sido enviado correctamente. Te responderemos a la brevedad posible.';
            
            // Enviar notificación por correo al administrador (opcional)
            // mail(ADMIN_EMAIL, "Nuevo mensaje de contacto: $asunto", $mensaje, "From: $email");
            
            // Limpiar el formulario
            $nombre = $email = $telefono = $asunto = $mensaje = '';
        } else {
            $error_message = 'Ocurrió un error al enviar tu mensaje. Por favor, intenta nuevamente.';
        }
    }
}

// Título de la página
$page_title = "Contacto";

// Cargar el header del sitio
include_once INCLUDES_PATH . '/header.php';
?>

<div class="contact-page">
    <div class="new-container">
        <div class="page-header">
            <h1 class="page-title">Contacto</h1>
            <p class="page-subtitle">Estamos aquí para ayudarte. Ponte en contacto con nosotros.</p>
        </div>
        
        <div class="contact-content">
            <div class="contact-info">
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Visítanos</h3>
                    <p>Av. Tecnológica 123<br>Lima, Perú</p>
                </div>
                
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <h3>Llámanos</h3>
                    <p>+51 999 999 999<br>+51 (01) 123-4567</p>
                </div>
                
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3>Escríbenos</h3>
                    <p>info@compuya.com<br>ventas@compuya.com</p>
                </div>
                
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Horario de Atención</h3>
                    <p>Lun-Vie: 9:00am - 7:00pm<br>Sábados: 9:00am - 2:00pm</p>
                </div>
            </div>
            
            <div class="contact-form-container">
                <h2>Envíanos un mensaje</h2>
                
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
                
                <form method="post" action="" class="contact-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre <span class="required">*</span></label>
                            <input type="text" id="nombre" name="nombre" value="<?php echo isset($nombre) ? htmlspecialchars($nombre) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Correo Electrónico <span class="required">*</span></label>
                            <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="tel" id="telefono" name="telefono" value="<?php echo isset($telefono) ? htmlspecialchars($telefono) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="asunto">Asunto <span class="required">*</span></label>
                            <input type="text" id="asunto" name="asunto" value="<?php echo isset($asunto) ? htmlspecialchars($asunto) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="mensaje">Mensaje <span class="required">*</span></label>
                        <textarea id="mensaje" name="mensaje" rows="6" required><?php echo isset($mensaje) ? htmlspecialchars($mensaje) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Enviar Mensaje</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="map-container">
            <h2>Nuestra Ubicación</h2>
            <div class="google-map">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3901.964376166952!2d-77.03210492584488!3d-12.046654588118404!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9105c8b5d86070c5%3A0x3b0add95a23a34a8!2sPlaza%20Mayor%20de%20Lima!5e0!3m2!1ses!2spe!4v1682900051789!5m2!1ses!2spe" 
                    width="100%" 
                    height="450" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </div>
</div>

<?php
// Cargar el footer del sitio
include_once INCLUDES_PATH . '/footer.php';
?>