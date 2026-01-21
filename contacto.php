<?php
require_once 'config.php';

// Fetch Settings
$stmt = $pdo->query("SELECT * FROM site_settings");
$settings_map = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings_map[$row['setting_key']] = $row['setting_value'];
}

function getVal($key, $map, $default) {
    return !empty($map[$key]) ? htmlspecialchars($map[$key]) : $default;
}

$pageTitle = 'Contáctanos';
$navbar_title = getVal('navbar_title', $settings_map, 'AUTOSALES');
$logo_path = getVal('logo_path', $settings_map, '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - <?php echo $navbar_title; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<?php include 'includes/navbar_frontend.php'; ?>

<div class="container" style="padding: 4rem 1rem;">
    <h1 class="contact-title" style="text-align: center; margin-bottom: 1rem; color: #1e293b;">Contáctanos</h1>
    <p style="text-align: center; color: #64748b; margin-bottom: 3rem;">Estamos aquí para ayudarte a encontrar tu próximo vehículo.</p>

    <div class="contact-grid">
        
        <!-- Contact Info -->
        <div>
            <div style="margin-bottom: 2rem;">
                <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem; color: #1e293b;">Información</h3>
                
                <div style="display: flex; align-items: start; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="width: 40px; height: 40px; background: #eff6ff; color: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; color: #334155;">Dirección</div>
                        <div style="color: #64748b;"><?php echo getVal('contact_address', $settings_map, 'San Pedro Sula, Honduras'); ?></div>
                    </div>
                </div>

                <div style="display: flex; align-items: start; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="width: 40px; height: 40px; background: #eff6ff; color: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; color: #334155;">Teléfono</div>
                        <div style="color: #64748b;"><?php echo getVal('contact_phone', $settings_map, '+504 9999-9999'); ?></div>
                    </div>
                </div>

                <div style="display: flex; align-items: start; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="width: 40px; height: 40px; background: #eff6ff; color: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; color: #334155;">Email</div>
                        <div style="color: #64748b;"><?php echo getVal('contact_email', $settings_map, 'info@autosales.com'); ?></div>
                    </div>
                </div>

                <!-- Socials & WhatsApp -->
                <div style="margin-top: 2rem; border-top: 1px solid #e2e8f0; padding-top: 1.5rem;">
                    <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #1e293b;">Síguenos</h4>
                    <div style="display: flex; gap: 1rem;">
                        <?php if(!empty(getVal('social_facebook', $settings_map, ''))): ?>
                            <a href="<?php echo getVal('social_facebook', $settings_map, ''); ?>" target="_blank" style="width: 40px; height: 40px; background: #3b5998; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: transform 0.2s;">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if(!empty(getVal('social_instagram', $settings_map, ''))): ?>
                            <a href="<?php echo getVal('social_instagram', $settings_map, ''); ?>" target="_blank" style="width: 40px; height: 40px; background: #E1306C; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: transform 0.2s;">
                                <i class="fab fa-instagram"></i>
                            </a>
                        <?php endif; ?>

                        <?php if(!empty(getVal('whatsapp_number', $settings_map, ''))): ?>
                            <a href="https://wa.me/<?php echo getVal('whatsapp_number', $settings_map, ''); ?>" target="_blank" style="width: 40px; height: 40px; background: #25D366; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: transform 0.2s;">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Map -->
            <div style="border-radius: 1rem; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); height: 300px; background: #eee;">
                <?php 
                $mapUrl = getVal('contact_map', $settings_map, ''); 
                if(!empty($mapUrl)):
                ?>
                    <iframe src="<?php echo $mapUrl; ?>" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                <?php else: ?>
                    <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #94a3b8;">
                        <i class="fas fa-map-marked-alt" style="font-size: 3rem;"></i>
                        <span style="margin-left: 1rem;">Mapa no configurado</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="card" style="padding: 2rem;">
            <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem; color: #1e293b;">Envíanos un mensaje</h3>
            <form action="#" method="POST"> <!-- Placeholder action -->
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; font-size: 0.9rem;">Nombre Completo</label>
                    <input type="text" class="form-control" placeholder="Tu nombre" style="width:100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 0.5rem;">
                </div>
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; font-size: 0.9rem;">Correo Electrónico</label>
                    <input type="email" class="form-control" placeholder="tucorreo@ejemplo.com" style="width:100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 0.5rem;">
                </div>
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem; font-size: 0.9rem;">Mensaje</label>
                    <textarea class="form-control" rows="4" placeholder="¿En qué podemos ayudarte?" style="width:100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 0.5rem; font-family: inherit;"></textarea>
                </div>
                <button type="button" onclick="alert('Funcionalidad de envío pronto disponible')" style="width: 100%; padding: 0.75rem; background: #0f172a; color: white; border: none; border-radius: 0.5rem; font-weight: 600; cursor: pointer;">Enviar Mensaje</button>
            </form>
        </div>

    </div>
</div>

<style>
.contact-grid {
    display: grid; 
    grid-template-columns: 1fr 1fr; 
    gap: 4rem; 
    max-width: 1000px; 
    margin: 0 auto;
}
@media (max-width: 768px) {
    .contact-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
}
</style>
</body>
</html>
