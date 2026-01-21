<?php
require_once 'config.php';

// Fetch Settings
$stmt = $pdo->query("SELECT * FROM site_settings");
$settings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

function getVal($key, $map, $default) {
    return !empty($map[$key]) ? htmlspecialchars($map[$key]) : $default;
}

$pageTitle = 'Contáctanos';
$navbar_title = getVal('navbar_title', $settings, 'AUTOSALES');
$logo_path = getVal('logo_path', $settings, '');
$contact_email = getVal('contact_email', $settings, 'info@autosales.com');
$contact_phone = getVal('contact_phone', $settings, '+504 9999-9999');
$contact_address = getVal('contact_address', $settings, 'San Pedro Sula');
$whatsapp_number = getVal('whatsapp_number', $settings, '');
$site_name = getVal('site_name', $settings, 'Venta de Autos');

// Handle Form Submission
$message_status = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!empty($name) && !empty($email) && !empty($message)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, message) VALUES (:name, :email, :phone, :msg)");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':msg' => $message
            ]);
            
            // Mailer Logic Preserved
            $to = $contact_email; 
            $subject = "Nuevo Mensaje de Contacto - " . $site_name;
            $body = "Ha recibido un nuevo mensaje desde el sitio web:\n\n" .
                    "Nombre: $name\n" .
                    "Email: $email\n" .
                    "Teléfono: $phone\n\n" .
                    "Mensaje:\n$message\n";
            $headers = "From: no-reply@autosales.com\r\n" . "Reply-To: $email\r\n" . "X-Mailer: PHP/" . phpversion();
            @mail($to, $subject, $body, $headers);

            $message_status = 'success';
        } catch (PDOException $e) {
            $message_status = 'error';
        }
    } else {
        $message_status = 'empty';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contáctenos - <?php echo $navbar_title; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php include 'includes/navbar_frontend.php'; ?>

    <!-- Contact Hero -->
    <div class="contact-hero">
        <div class="container">
            <h1 class="contact-title"><?php echo htmlspecialchars($settings['contact_page_title'] ?? 'Contáctenos'); ?></h1>
            <p style="color: var(--text-light); font-size: 1.25rem;"><?php echo htmlspecialchars($settings['contact_page_subtitle'] ?? 'Estamos aquí para ayudarle a encontrar su próximo auto.'); ?></p>
        </div>
    </div>

    <div class="contact-wrapper">
        <!-- Left: Contact Form -->
        <div class="contact-form-card">
            <h2>Envíenos un Mensaje</h2>

            <?php if ($message_status === 'success'): ?>
                <div style="background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; color: #fff; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; display: flex; align-items: center;">
                    <i class="fas fa-check-circle" style="margin-right: 0.75rem; font-size: 1.25rem;"></i>
                    <div><strong>¡Mensaje Enviado!</strong><br>Gracias por contactarnos.</div>
                </div>
            <?php elseif ($message_status === 'error'): ?>
                <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #fff; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; display: flex; align-items: center;">
                    <i class="fas fa-exclamation-circle" style="margin-right: 0.75rem; font-size: 1.25rem;"></i>
                    <div><strong>Error</strong><br>Hubo un problema. Intente de nuevo.</div>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>Nombre Completo</label>
                    <input type="text" name="name" class="form-control" placeholder="Su nombre" required>
                </div>
                <div class="form-group">
                    <label>Correo Electrónico</label>
                    <input type="email" name="email" class="form-control" placeholder="nombre@gmail.com" required>
                </div>
                <div class="form-group">
                    <label>Teléfono (Opcional)</label>
                    <input type="tel" name="phone" class="form-control" placeholder="+505 ...">
                </div>
                <div class="form-group">
                    <label>Mensaje</label>
                    <textarea name="message" class="form-control" placeholder="¿En qué podemos ayudarle?"></textarea>
                </div>
                <button type="submit" class="btn-submit">Enviar Mensaje</button>
            </form>
        </div>

        <!-- Right: Contact Info Cards -->
        <div class="contact-info-section">
            
            <div class="info-card">
                <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                <div class="info-content">
                    <h4>Nuestra Ubicación</h4>
                    <p><?php echo $contact_address; ?></p>
                </div>
            </div>

            <div class="info-card" onclick="window.open('tel:<?php echo str_replace(' ', '', $contact_phone); ?>', '_self')" style="cursor: pointer;">
                <div class="info-icon"><i class="fas fa-phone-alt"></i></div>
                <div class="info-content">
                    <h4>Llámenos</h4>
                    <p><?php echo $contact_phone; ?></p>
                    <p style="font-size: 0.9rem; margin-top: 0.25rem; color: #10b981;">Disponible Ahora</p>
                </div>
            </div>

            <div class="info-card" onclick="window.open('mailto:<?php echo $contact_email; ?>', '_self')" style="cursor: pointer;">
                <div class="info-icon"><i class="fas fa-envelope"></i></div>
                <div class="info-content">
                    <h4>Correo Electrónico</h4>
                    <p><?php echo $contact_email; ?></p>
                </div>
            </div>

            <?php if(!empty($whatsapp_number)): ?>
            <div class="info-card" onclick="window.open('https://wa.me/<?php echo $whatsapp_number; ?>', '_blank')" style="border-color: #25d366; background: rgba(37, 211, 102, 0.1); cursor: pointer;">
                <div class="info-icon" style="background: #25d366;"><i class="fab fa-whatsapp"></i></div>
                <div class="info-content">
                    <h4 style="color: #25d366;">WhatsApp</h4>

                </div>
            </div>
            <?php endif; ?>



        </div>
    </div>

    <!-- Full Width Map -->
    <div class="contact-map-section">
        <?php 
        $mapUrl = getVal('contact_map', $settings, ''); 
        if(!empty($mapUrl)):
        ?>
            <iframe src="<?php echo $mapUrl; ?>" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        <?php else: ?>
            <div style="width:100%; height:100%; background:var(--bg-color); display:flex; align-items:center; justify-content:center; color:var(--text-light); flex-direction:column; padding:2rem; text-align:center;">
                <i class="fas fa-map-marked-alt" style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.5;"></i>
                <p style="font-size: 1.2rem;">Mapa no configurado</p>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer_frontend.php'; ?>

</body>
</html>
