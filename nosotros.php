<?php
require_once 'config.php';
// Re-use site settings fetch logic from index.php (simplified for now)
// Standard Key-Value Fetch
$stmt = $pdo->query("SELECT * FROM site_settings");
$settings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$site_name = $settings['site_name'] ?? 'AutoSales';
$logo_path = $settings['logo_path'] ?? '';
$navbar_title = $settings['navbar_title'] ?? 'AUTOSALES';
$contact_phone = $settings['contact_phone'] ?? '';
$contact_email = $settings['contact_email'] ?? '';
$contact_address = $settings['contact_address'] ?? '';
$whatsapp_number = $settings['whatsapp_number'] ?? '';

// Fetch Navbar Links
$nav_links = $pdo->query("SELECT * FROM navbar_links ORDER BY order_index ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nosotros - <?php echo htmlspecialchars($site_name); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php include 'includes/navbar_frontend.php'; ?>

    <div class="container" style="padding-top: 4rem; padding-bottom: 4rem;">
        <h1 style="text-align: center; margin-bottom: 2rem; color: var(--primary);">Sobre Nosotros</h1>
        
        <div class="about-card">
            <p style="font-size: 1.1rem; line-height: 1.8; margin-bottom: 1.5rem;">
                Bienvenido a <strong><?php echo htmlspecialchars($site_name); ?></strong>, su destino confiable para encontrar el vehículo perfecto. Nos dedicamos a ofrecer una selección premium de autos de calidad, garantizando transparencia y confianza en cada transacción.
            </p>
            <p style="font-size: 1.1rem; line-height: 1.8;">
                Nuestro equipo de expertos está comprometido en brindarle la mejor experiencia de compra, con asesoría personalizada y opciones que se adaptan a sus necesidades y presupuesto. Visítenos hoy y descubra por qué somos líderes en el mercado automotriz.
            </p>
        </div>
    </div>

    <!-- Footer (Simplified Copy) -->
    <?php include 'includes/footer_frontend.php'; ?>

    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/<?php echo htmlspecialchars($whatsapp_number); ?>" target="_blank" class="whatsapp-float">
        <i class="fab fa-whatsapp"></i>
    </a>
</body>
</html>
