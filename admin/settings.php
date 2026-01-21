<?php
$pageTitle = 'Settings';
require_once 'includes/header.php';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Handle File Upload (Logo)
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
        $fileName = $_FILES['site_logo']['name'];
        $fileTmp = $_FILES['site_logo']['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExt, $allowedExts)) {
            // Create uploads dir if it doesn't exist
            $uploadDir = __DIR__ . '/../uploads/'; 
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $newFileName = 'logo_' . time() . '.' . $fileExt;
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmp, $destPath)) {
                // Save relative path to DB
                $dbPath = 'uploads/' . $newFileName;
                $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('site_logo', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$dbPath, $dbPath]);
            }
        }
    }

    // 1.5 Handle Logo Deletion
    if (isset($_POST['delete_logo']) && $_POST['delete_logo'] == '1') {
        // Optional: Delete physical file if needed, but for now just clear DB ref
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('site_logo', '') ON DUPLICATE KEY UPDATE setting_value = ''");
        $stmt->execute();
    }

    // 1.6 Handle Banner Upload
    if (isset($_FILES['ads_banner']) && $_FILES['ads_banner']['error'] === UPLOAD_ERR_OK) {
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4', 'webm', 'ogg'];
        $fileName = $_FILES['ads_banner']['name'];
        $fileTmp = $_FILES['ads_banner']['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExt, $allowedExts)) {
            $uploadDir = __DIR__ . '/../uploads/'; 
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $newFileName = 'banner_' . time() . '.' . $fileExt;
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmp, $destPath)) {
                $dbPath = 'uploads/' . $newFileName;
                $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('ads_banner', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$dbPath, $dbPath]);
            }
        }
    }

    // 1.7 Handle Banner Deletion
    if (isset($_POST['delete_banner']) && $_POST['delete_banner'] == '1') {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('ads_banner', '') ON DUPLICATE KEY UPDATE setting_value = ''");
        $stmt->execute();
    }

    // 1.8 Handle Banner 2 Upload
    if (isset($_FILES['ads_banner_2']) && $_FILES['ads_banner_2']['error'] === UPLOAD_ERR_OK) {
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4', 'webm', 'ogg'];
        $fileName = $_FILES['ads_banner_2']['name'];
        $fileTmp = $_FILES['ads_banner_2']['tmp_name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExt, $allowedExts)) {
            $uploadDir = __DIR__ . '/../uploads/'; 
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $newFileName = 'banner2_' . time() . '.' . $fileExt;
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmp, $destPath)) {
                $dbPath = 'uploads/' . $newFileName;
                $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('ads_banner_2', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$dbPath, $dbPath]);
            }
        }
    }

    // 1.9 Handle Banner 2 Deletion
    if (isset($_POST['delete_banner_2']) && $_POST['delete_banner_2'] == '1') {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('ads_banner_2', '') ON DUPLICATE KEY UPDATE setting_value = ''");
        $stmt->execute();
    }

    // 2. Handle Text Settings
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'setting_') === 0) {
            $realKey = substr($key, 8); // Remove 'setting_' prefix
            
            // Special handling for Map: Extract src if iframe is pasted
            if ($realKey === 'contact_map' && strpos($value, '<iframe') !== false) {
                preg_match('/src="([^"]+)"/', $value, $match);
                if (isset($match[1])) {
                    $value = $match[1];
                }
            }

            $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$realKey, $value, $value]);
        }
    }
    echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Configuración actualizada correctamente.</div>';
}

// Fetch current settings
$stmt = $pdo->query("SELECT * FROM site_settings");
$settings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Helper function to get value safely
function getSetting($key, $settings, $default = '') {
    return isset($settings[$key]) ? htmlspecialchars($settings[$key]) : $default;
}
?>

<div style="max-width: 1000px; margin: 0 auto;">
    <div style="margin-bottom: 2rem;">
        <h1 style="margin-bottom: 0.5rem;">Configuración del Sitio Web</h1>
        <p style="color: var(--text-light);">Edita los textos y títulos principales de la página de inicio.</p>
    </div>
    <style>
        .settings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <form method="POST" action="" enctype="multipart/form-data">
        
        <!-- General Identity Section -->
        <div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
            <div class="section-title" style="margin-top: 0;">
                <i class="fas fa-id-card"></i> Identidad del Sitio
            </div>
            <div class="settings-grid">
                <!-- Site Name -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Nombre del Sitio</label>
                    <input type="text" name="setting_site_name" 
                           value="<?php echo getSetting('site_name', $settings); ?>" 
                           placeholder="Ej: AUTOIMARKET">
                    <small style="color: #64748b;">Aparece en la barra de navegación y títulos.</small>
                </div>

                <!-- Site Logo -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Logo del Sitio (Opcional)</label>
                    <input type="file" name="site_logo" accept="image/*" style="padding: 0.5rem; background: var(--surface); border: 1px dashed var(--border); width: 100%;">
                    <?php if (!empty($settings['site_logo'])): ?>
                        <div style="margin-top: 1rem;">
                            <small style="color: #64748b; display: block; margin-bottom: 5px;">Logo actual:</small>
                            <div style="position: relative; display: inline-block;">
                                <img src="../<?php echo htmlspecialchars($settings['site_logo']); ?>" alt="Logo Actual" style="height: 60px; background: var(--bg-color); padding: 5px; border-radius: 4px; border: 1px solid var(--border);">
                                <!-- Delete Button (X) -->
                                <button type="button" onclick="confirmDeleteLogo()"
                                        style="position: absolute; top: -10px; right: -10px; background: #ef4444; color: white; border: 2px solid #1f2937; border-radius: 50%; width: 26px; height: 26px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.3); transition: transform 0.2s;">
                                    <i class="fas fa-times"></i>
                                </button>
                                
                                <!-- SweetAlert2 CDN & Script -->
                                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                                <script>
                                function confirmDeleteLogo() {
                                    Swal.fire({
                                        title: '¿Eliminar logo?',
                                        text: "Esta acción no se puede deshacer.",
                                        icon: 'warning',
                                        background: '#ffffff',
                                        color: '#0f172a',
                                        showCancelButton: true,
                                        confirmButtonColor: '#ef4444',
                                        cancelButtonColor: '#64748b',
                                        confirmButtonText: 'Sí, eliminar',
                                        cancelButtonText: 'Cancelar'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            // Create hidden input and submit form
                                            var form = document.querySelector('form');
                                            var input = document.createElement('input');
                                            input.type = 'hidden';
                                            input.name = 'delete_logo';
                                            input.value = '1';
                                            form.appendChild(input);
                                            form.submit();
                                        }
                                    })
                                }
                                </script>
                            </div>
                        </div>
                    <?php endif; ?>
                    <small style="color: #64748b;">Sube una imagen (PNG, JPG, SVG) para reemplazar el texto.</small>
                </div>
            </div>
        </div>

        <!-- Home Section -->
        <div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
            <div class="section-title" style="margin-top: 0;">
                <i class="fas fa-home"></i> Textos de la Página de Inicio
            </div>
            <div class="settings-grid">
                <!-- Carousel 1 -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Título del Primer Carrusel (Ofertas)</label>
                    <input type="text" name="setting_carousel_1_title" 
                           value="<?php echo getSetting('carousel_1_title', $settings); ?>" 
                           placeholder="Ej: Ofertas Especiales">
                    <small style="color: #64748b;">Este carrusel muestra vehículos con la etiqueta "Oferta".</small>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Subtítulo del Primer Carrusel</label>
                    <input type="text" name="setting_carousel_1_subtitle" 
                           value="<?php echo getSetting('carousel_1_subtitle', $settings); ?>" 
                           placeholder="Ej: Explora nuestra selección premium">
                </div>

                <!-- Carousel 2 -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Título del Segundo Carrusel (Nuevos)</label>
                    <input type="text" name="setting_carousel_2_title" 
                           value="<?php echo getSetting('carousel_2_title', $settings); ?>" 
                           placeholder="Ej: Nuevos Ingresos">
                    <small style="color: #64748b;">Este carrusel muestra vehículos con la etiqueta "Nuevo".</small>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Subtítulo del Segundo Carrusel</label>
                    <input type="text" name="setting_carousel_2_subtitle" 
                           value="<?php echo getSetting('carousel_2_subtitle', $settings); ?>" 
                           placeholder="Ej: Recién llegados a nuestra sala">
                </div>
            </div>
        </div>

        <!-- Explore Section -->
        <div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
            <div class="section-title" style="margin-top: 0;">
                <i class="fas fa-compass"></i> Secciones de Exploración
            </div>
            <div class="settings-grid">
                <!-- Explore Category -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Título Sección Categorías</label>
                    <input type="text" name="setting_explore_category_title" 
                           value="<?php echo getSetting('explore_category_title', $settings); ?>" 
                           placeholder="Ej: | Explora por categoría">
                </div>

                <!-- Explore Brand -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Título Sección Marcas</label>
                    <input type="text" name="setting_explore_brand_title" 
                           value="<?php echo getSetting('explore_brand_title', $settings); ?>" 
                           placeholder="Ej: | Explora por Marca">
                </div>
            </div>
        </div>

        <!-- Advertising Banner Section -->
        <div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
            <div class="section-title" style="margin-top: 0;">
                <i class="fas fa-ad"></i> Banner de Publicidad
            </div>
            <div class="settings-grid">
                 <div class="form-group" style="margin-bottom: 0; grid-column: span 2;">
                    <label>Banner Publicitario (Entre Carruseles)</label>
                    <input type="file" name="ads_banner" accept="image/*" style="padding: 0.5rem; background: var(--surface); border: 1px dashed var(--border); width: 100%;">
                    <?php if (!empty($settings['ads_banner'])): ?>
                        <div style="margin-top: 1rem;">
                            <small style="color: #64748b; display: block; margin-bottom: 5px;">Banner actual:</small>
                            <div style="position: relative; display: inline-block; width: 100%; max-width: 100%;">
                                <img src="../<?php echo htmlspecialchars($settings['ads_banner']); ?>" alt="Banner Actual" style="width: 100%; max-height: 200px; object-fit: cover; background: var(--bg-color); padding: 5px; border-radius: 4px; border: 1px solid var(--border);">
                                <!-- Delete Button (X) -->
                                <button type="button" onclick="confirmDeleteBanner()"
                                        style="position: absolute; top: -10px; right: -10px; background: #ef4444; color: white; border: 2px solid #1f2937; border-radius: 50%; width: 26px; height: 26px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.3); transition: transform 0.2s;">
                                    <i class="fas fa-times"></i>
                                </button>
                                <script>
                                function confirmDeleteBanner() {
                                    Swal.fire({
                                        title: '¿Eliminar banner?',
                                        text: "Esta acción no se puede deshacer.",
                                        icon: 'warning',
                                        background: '#ffffff',
                                        color: '#0f172a',
                                        showCancelButton: true,
                                        confirmButtonColor: '#ef4444',
                                        cancelButtonColor: '#64748b',
                                        confirmButtonText: 'Sí, eliminar',
                                        cancelButtonText: 'Cancelar'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            var form = document.querySelector('form');
                                            var input = document.createElement('input');
                                            input.type = 'hidden';
                                            input.name = 'delete_banner';
                                            input.value = '1';
                                            form.appendChild(input);
                                            form.submit();
                                        }
                                    })
                                }
                                </script>
                            </div>
                        </div>
                    <?php endif; ?>
                    <small style="color: #64748b;">Sube una imagen ancha para publicidad. Se mostrará entre los dos carruseles. <strong>Medida recomendada: 1400x200 px.</strong></small>
                </div>

                <div class="form-group" style="margin-bottom: 0; grid-column: span 2; margin-top: 1rem;">
                    <label>Enlace del Banner (Opcional)</label>
                    <input type="text" name="setting_ads_banner_url" value="<?php echo htmlspecialchars($settings['ads_banner_url'] ?? ''); ?>" placeholder="https://ejemplo.com" style="width: 100%; padding: 0.5rem; background: var(--surface); border: 1px solid var(--border); color: var(--text-main);">
                    <small style="color: #64748b;">Si se deja vacío, el banner no será clickeable.</small>
                </div>

                <div class="form-group" style="margin-bottom: 0; grid-column: span 2; margin-top: 1.5rem; border-top: 1px solid #374151; padding-top: 1.5rem;">
                    <label>Segundo Banner Publicitario (Abajo)</label>
                    <input type="file" name="ads_banner_2" accept="image/*" style="padding: 0.5rem; background: var(--surface); border: 1px dashed var(--border); width: 100%;">
                    <?php if (!empty($settings['ads_banner_2'])): ?>
                        <div style="margin-top: 1rem;">
                            <small style="color: #64748b; display: block; margin-bottom: 5px;">Banner actual:</small>
                            <div style="position: relative; display: inline-block; width: 100%; max-width: 100%;">
                                <img src="../<?php echo htmlspecialchars($settings['ads_banner_2']); ?>" alt="Banner Actual" style="width: 100%; max-height: 200px; object-fit: cover; background: var(--bg-color); padding: 5px; border-radius: 4px; border: 1px solid var(--border);">
                                <!-- Delete Button (X) -->
                                <button type="button" onclick="confirmDeleteBanner2()"
                                        style="position: absolute; top: -10px; right: -10px; background: #ef4444; color: white; border: 2px solid #1f2937; border-radius: 50%; width: 26px; height: 26px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.3); transition: transform 0.2s;">
                                    <i class="fas fa-times"></i>
                                </button>
                                <script>
                                function confirmDeleteBanner2() {
                                    Swal.fire({
                                        title: '¿Eliminar segundo banner?',
                                        text: "Esta acción no se puede deshacer.",
                                        icon: 'warning',
                                        background: '#ffffff',
                                        color: '#0f172a',
                                        showCancelButton: true,
                                        confirmButtonColor: '#ef4444',
                                        cancelButtonColor: '#64748b',
                                        confirmButtonText: 'Sí, eliminar',
                                        cancelButtonText: 'Cancelar'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            var form = document.querySelector('form');
                                            var input = document.createElement('input');
                                            input.type = 'hidden';
                                            input.name = 'delete_banner_2';
                                            input.value = '1';
                                            form.appendChild(input);
                                            form.submit();
                                        }
                                    })
                                }
                                </script>
                            </div>
                        </div>
                    <?php endif; ?>
                    <small style="color: #64748b;">Este banner aparecerá debajo de los vehículos nuevos. <strong>Medida recomendada: 1400x200 px.</strong></small>
                </div>

                <div class="form-group" style="margin-bottom: 0; grid-column: span 2; margin-top: 1rem;">
                    <label>Enlace del Segundo Banner (Opcional)</label>
                    <input type="text" name="setting_ads_banner_2_url" value="<?php echo htmlspecialchars($settings['ads_banner_2_url'] ?? ''); ?>" placeholder="https://ejemplo.com" style="width: 100%; padding: 0.5rem; background: var(--surface); border: 1px solid var(--border); color: var(--text-main);">
                    <small style="color: #64748b;">Si se deja vacío, el banner no será clickeable.</small>
                </div>
            </div>
        </div>

        <!-- Contact Section -->
        <div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
            <div class="section-title" style="margin-top: 0;">
                <i class="fas fa-address-book"></i> Información de Contacto
            </div>
            <div class="settings-grid">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Dirección Física</label>
                    <input type="text" name="setting_contact_address" 
                           value="<?php echo getSetting('contact_address', $settings); ?>" 
                           placeholder="Ej: San Pedro Sula, Cortés">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Correo Electrónico</label>
                    <input type="email" name="setting_contact_email" 
                           value="<?php echo getSetting('contact_email', $settings); ?>" 
                           placeholder="Ej: contacto@ejemplo.com">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Teléfono Principal</label>
                    <input type="text" name="setting_contact_phone" 
                           value="<?php echo getSetting('contact_phone', $settings); ?>" 
                           placeholder="Ej: +504 9999-9999">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Título de la Página de Contacto</label>
                    <input type="text" name="setting_contact_page_title" 
                           value="<?php echo getSetting('contact_page_title', $settings); ?>" 
                           placeholder="Ej: Contáctenos">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Subtítulo de la Página de Contacto</label>
                    <input type="text" name="setting_contact_page_subtitle" 
                           value="<?php echo getSetting('contact_page_subtitle', $settings); ?>" 
                           placeholder="Ej: Estamos aquí para ayudarle a encontrar su próximo auto.">
                </div>
                 <div class="form-group" style="margin-bottom: 0;">
                    <!-- Empty spacer -->
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 0; margin-top: 1.5rem;">
                <label>Enlace de Mapa (Google Maps Embed URL)</label>
                <input type="text" name="setting_contact_map" 
                       value="<?php echo getSetting('contact_map', $settings); ?>" 
                       placeholder="Pega aquí el enlace 'src' del iframe de Google Maps">
                <small style="color: #64748b;">Ve a Google Maps -> Compartir -> Insertar mapa -> Copia solo el contenido de <code>src="..."</code>. Si pegas todo el código, intentaremos extraer el enlace automáticamente.</small>
            </div>
        </div>

        <!-- Social Section -->
        <div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
            <div class="section-title" style="margin-top: 0;">
                <i class="fab fa-whatsapp"></i> Redes Sociales & WhatsApp
            </div>
            <div class="settings-grid">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Número de WhatsApp</label>
                    <input type="text" name="setting_whatsapp_number" 
                           value="<?php echo getSetting('whatsapp_number', $settings); ?>" 
                           placeholder="Ej: 50499999999">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Enlace de Facebook</label>
                    <input type="text" name="setting_social_facebook" 
                           value="<?php echo getSetting('social_facebook', $settings); ?>" 
                           placeholder="Ej: https://facebook.com/tu_pagina">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Enlace de Instagram</label>
                    <input type="text" name="setting_social_instagram" 
                           value="<?php echo getSetting('social_instagram', $settings); ?>" 
                           placeholder="Ej: https://instagram.com/tu_usuario">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Enlace de Twitter (X)</label>
                    <input type="text" name="setting_social_twitter" 
                           value="<?php echo getSetting('social_twitter', $settings); ?>" 
                           placeholder="Ej: https://x.com/tu_usuario">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Enlace de TikTok</label>
                    <input type="text" name="setting_social_tiktok" 
                           value="<?php echo getSetting('social_tiktok', $settings); ?>" 
                           placeholder="Ej: https://tiktok.com/@tu_usuario">
                </div>
                 <div class="form-group" style="margin-bottom: 0;">
                </div>
            </div>
        </div>

        <!-- Hours Section -->
        <div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
            <div class="section-title" style="margin-top: 0;">
                <i class="fas fa-clock"></i> Horarios de Atención
            </div>
            <div class="settings-grid">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Lunes a Viernes</label>
                    <input type="text" name="setting_hours_weekdays" 
                           value="<?php echo getSetting('hours_weekdays', $settings); ?>" 
                           placeholder="Ej: 8:00 AM - 6:00 PM">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Sábados</label>
                    <input type="text" name="setting_hours_saturday" 
                           value="<?php echo getSetting('hours_saturday', $settings); ?>" 
                           placeholder="Ej: 8:00 AM - 2:00 PM">
                </div>
            </div>
        </div>

        <!-- Footer Section -->
        <div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
            <div class="section-title" style="margin-top: 0;">
                <i class="fas fa-Copyright"></i> Pie de Página (Footer)
            </div>
            <div class="settings-grid">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Subtítulo de Contacto (Debajo de "Contactenos")</label>
                    <input type="text" name="setting_contact_subtitle" 
                           value="<?php echo getSetting('contact_subtitle', $settings); ?>" 
                           placeholder="Ej: Estamos para servirle.">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Texto de Derechos de Autor</label>
                    <input type="text" name="setting_footer_text" 
                           value="<?php echo getSetting('footer_text', $settings); ?>" 
                           placeholder="Ej: © 2025 AutoSales. Todos los derechos reservados.">
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div style="margin-top: 2rem; text-align: right;">
            <button type="submit" class="btn-modern" style="padding: 1rem 3rem; font-size: 1.1rem;">
                <i class="fas fa-save" style="margin-right: 8px;"></i> Guardar Cambios
            </button>
        </div>

    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
