<?php
// Ensure settings are available
if (!isset($settings) || empty($settings)) {
    $stmt = $pdo->query("SELECT * FROM site_settings");
    $settings = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}
$footer_text = $settings['footer_text'] ?? ("© " . date("Y") . " " . ($settings['site_name'] ?? 'AutoSales') . ". Todos los derechos reservados.");

// Helper for social links
if (!function_exists('ensureAbsoluteUrl')) {
    function ensureAbsoluteUrl($url) {
        if (empty($url) || $url === '#') return $url;
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "https://" . $url;
        }
        return $url;
    }
}
?>
<footer id="contact" class="site-footer">
    <div class="container footer-content">
        <div class="footer-col footer-section">
            <h3>Contactenos</h3>
            <p><?php echo htmlspecialchars($settings['contact_subtitle'] ?? 'Estamos para servirle.'); ?></p>
            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($settings['contact_address'] ?? ''); ?></p>
            <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?></p>
            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?></p>
        </div>
        <div class="footer-col footer-section">
            <h3>Horarios</h3>
            <p>Lunes - Viernes: <?php echo htmlspecialchars($settings['hours_weekdays'] ?? '8:00 AM - 6:00 PM'); ?></p>
            <p>Sábado: <?php echo htmlspecialchars($settings['hours_saturday'] ?? '8:00 AM - 2:00 PM'); ?></p>
        </div>
        <div class="footer-col footer-section">
            <h3>Siguenos</h3>
            <div class="social-links">
                <?php if(!empty($settings['social_facebook']) && $settings['social_facebook'] !== '#'): ?>
                    <a href="<?php echo htmlspecialchars(ensureAbsoluteUrl($settings['social_facebook'])); ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                <?php endif; ?>
                <?php if(!empty($settings['social_instagram']) && $settings['social_instagram'] !== '#'): ?>
                    <a href="<?php echo htmlspecialchars(ensureAbsoluteUrl($settings['social_instagram'])); ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                <?php endif; ?>
                <?php if(!empty($settings['social_twitter']) && $settings['social_twitter'] !== '#'): ?>
                    <a href="<?php echo htmlspecialchars(ensureAbsoluteUrl($settings['social_twitter'])); ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                <?php endif; ?>
                <?php if(!empty($settings['social_tiktok']) && $settings['social_tiktok'] !== '#'): ?>
                    <a href="<?php echo htmlspecialchars(ensureAbsoluteUrl($settings['social_tiktok'])); ?>" target="_blank"><i class="fab fa-tiktok"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <?php echo nl2br(htmlspecialchars($footer_text)); ?>
    </div>
</footer>

<!-- Floating WhatsApp Button -->
<?php if(!empty($settings['whatsapp_number'])): ?>
<a href="https://wa.me/<?php echo htmlspecialchars($settings['whatsapp_number']); ?>" target="_blank" class="whatsapp-float">
    <i class="fab fa-whatsapp"></i>
</a>
<?php endif; ?>

