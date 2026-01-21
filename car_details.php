<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$car = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch Gallery Images
$stmt_g = $pdo->prepare("SELECT * FROM car_images WHERE car_id = :id");
$stmt_g->bindParam(':id', $id);
$stmt_g->execute();
$gallery_images = $stmt_g->fetchAll(PDO::FETCH_ASSOC);

if (!$car) {
    header("Location: index.php");
    exit;
}

// Fetch Settings for Navbar/Footer
// Standard Key-Value Fetch
$stmt = $pdo->query("SELECT * FROM site_settings");
$settings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$site_name = $settings['site_name'] ?? 'Venta de Autos';
$navbar_title = $settings['navbar_title'] ?? 'AUTOSALES';
$logo_path = $settings['logo_path'] ?? ''; 
$whatsapp_number = $settings['whatsapp_number'] ?? '50499999999';
$contact_phone = $settings['contact_phone'] ?? '+504 9999-9999';
$contact_email = $settings['contact_email'] ?? 'info@autosales.com';
$contact_address = $settings['contact_address'] ?? 'San Pedro Sula';

function getWhatsAppLink($car, $base_number) {
    $text = "Hola, estoy interesado en el auto " . $car['make'] . " " . $car['model'] . " (" . $car['year'] . ")";
    return "https://wa.me/" . $base_number . "?text=" . urlencode($text);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?> - <?php echo htmlspecialchars($site_name); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .details-container {
            padding: 4rem 1rem 0 1rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
        }
        .car-image-large {
            width: 100%;
            height: 500px;
            object-fit: cover;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transition: opacity 0.3s ease;
        }
        .placeholder-image {
            width: 100%;
            height: 400px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            color: #94a3b8;
            font-size: 5rem;
        }
        .car-info h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            color: var(--text-main);
        }
        .car-price-large {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 2rem;
        }
        .specs-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 1rem;
        }
        .spec-item {
            display: flex;
            flex-direction: column;
        }
        .spec-label {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 0.25rem;
        }
        .spec-value {
            font-weight: 600;
            color: var(--text-main);
            font-size: 1.1rem;
        }
        .btn-whatsapp-large {
            display: inline-block;
            background-color: #25d366;
            color: white;
            padding: 1rem 2rem;
            border-radius: 0.75rem;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            width: 100%;
            text-align: center;
            transition: all 0.3s;
        }
        .image-container-sticky {
            position: sticky;
            top: 7rem;
            align-self: start;
        }
        .car-title-main {
            font-size: 2.5rem;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 0.5rem;
            color: #3b82f6;
            text-transform: uppercase;
        }
        .car-price-main {
            font-size: 3rem;
            font-weight: 900;
            color: #f3f4f6;
            line-height: 1;
            margin-top: 0.25rem;
            letter-spacing: -1px;
        }

        .btn-whatsapp-large:hover {
            background-color: #1faf53;
            transform: translateY(-2px);
        }
        
        @media (max-width: 1024px) {
            .details-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            .image-container-sticky {
                position: static;
            }
            .car-image-large {
                height: auto;
                max-height: 400px;
                width: 100%;
            }
            .car-title-main {
                font-size: 1.8rem; /* Mobile Size */
            }
            .car-price-main {
                font-size: 2.25rem; /* Mobile Size */
            }
            .details-container {
                padding-top: 2rem;
            }
        }
    </style>
</head>
<body>

    <?php include 'includes/navbar_frontend.php'; ?>

    <!-- Lightbox Modal -->
    <div id="lightbox" class="lightbox" onclick="if(event.target === this) closeLightbox()">
        <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
        <div class="lightbox-nav lightbox-prev" onclick="changeSlide(-1)">&#10094;</div>
        <img id="lightbox-img" class="lightbox-content" src="">
        <div class="lightbox-nav lightbox-next" onclick="changeSlide(1)">&#10095;</div>
    </div>

    <script>
        // Gallery Data - Safe JSON encoding
        <?php
            $js_gallery = [];
            if (!empty($car['image_path'])) {
                $js_gallery[] = str_replace('\\', '/', $car['image_path']);
            }
            foreach ($gallery_images as $img) {
                if (!empty($img['image_path'])) {
                    $js_gallery[] = str_replace('\\', '/', $img['image_path']);
                }
            }
        ?>
        const galleryImages = <?php echo json_encode($js_gallery); ?>;
        
        let currentIndex = 0;
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightbox-img');

        function openLightbox(index) {
            currentIndex = index;
            lightboxImg.src = galleryImages[currentIndex];
            lightbox.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        }

        function closeLightbox() {
            lightbox.classList.remove('active');
            document.body.style.overflow = 'auto'; // Restore scrolling
        }

        function changeSlide(step) {
            currentIndex += step;
            if (currentIndex >= galleryImages.length) currentIndex = 0;
            if (currentIndex < 0) currentIndex = galleryImages.length - 1;
            
            // Fade effect
            lightboxImg.style.opacity = 0;
            setTimeout(() => {
                lightboxImg.src = galleryImages[currentIndex];
                lightboxImg.style.opacity = 1;
            }, 200);
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (!lightbox.classList.contains('active')) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') changeSlide(-1);
            if (e.key === 'ArrowRight') changeSlide(1);
        });
    </script>

    <div class="details-container">
        <div class="details-grid">
            <div class="image-container-sticky">
                <?php if($car['image_path']): ?>
                    <img src="<?php echo htmlspecialchars($car['image_path']); ?>" alt="Car" class="car-image-large" onclick="openLightbox(0)" style="cursor: pointer;">
                <?php else: ?>
                    <div class="placeholder-image">
                        <i class="fas fa-camera"></i>
                    </div>
                <?php endif; ?>

                
                <!-- Gallery Thumbnails (New Location) -->
                <?php if(count($gallery_images) > 0): ?>
                <div style="display: flex; gap: 0.5rem; margin-top: 1rem; overflow-x: auto; padding-bottom: 0.5rem;">
                    <?php 
                    $g_index = 1; // Start at 1 because 0 is main image
                    foreach($gallery_images as $img): 
                    ?>
                        <img src="<?php echo htmlspecialchars($img['image_path']); ?>" 
                             onclick="openLightbox(<?php echo $g_index; ?>)"
                             style="width: 80px; height: 60px; object-fit: cover; border-radius: 0.5rem; cursor: pointer; flex-shrink: 0; border: 2px solid transparent;"
                             onmouseover="this.style.borderColor='var(--accent-color)'"
                             onmouseout="this.style.borderColor='transparent'">
                    <?php 
                    $g_index++;
                    endforeach; 
                    ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Car Info Column -->
            <div class="car-info" style="display: flex; flex-direction: column; height: 100%;">
                
                <!-- Title Section -->
                <div>
                    <h1 class="car-title-main">
                        <?php echo htmlspecialchars($car['make'] . ' ' . $car['model'] . ' ' . $car['year']); ?>
                    </h1>
                    
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 1.5rem;">
                        <!-- Price Pill -->
                        <div style="display: flex; flex-direction: column;">
                            <span style="color: #9ca3af; font-size: 0.875rem; font-weight: 700; text-transform: uppercase;">PRECIO:</span>
                            <div class="car-price-main">
                                C$<?php echo number_format($car['price']); ?>
                            </div>
                        </div>

                        <!-- Category -->
                        <div style="text-align: right;">
                            <span style="display: block; color: #9ca3af; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.25rem;">CATEGORÍA:</span>
                            <div style="color: #3b82f6; font-size: 1.5rem; font-weight: 800; text-transform: uppercase;">
                                <?php echo htmlspecialchars($car['category']); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Specs Divider -->
                <div style="display: flex; align-items: center; gap: 1rem; margin: 2.5rem 0;">
                    <div class="specs-divider-line" style="flex-grow: 1; height: 1px; background-color: #374151;"></div>
                    <div class="specs-divider-text" style="background-color: #ffffff; color: #111827; padding: 0.75rem 2rem; font-weight: 800; text-transform: uppercase; font-size: 0.9rem; letter-spacing: 0.5px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                        ESPECIFICACIONES PRINCIPALES
                    </div>
                    <div class="specs-divider-line" style="flex-grow: 1; height: 1px; background-color: #374151;"></div>
                </div>

                <!-- Specs Grid -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem; font-size: 0.9rem;">
                    
                    <!-- Item -->
                    <div class="spec-item">
                        <i class="fas fa-tachometer-alt spec-icon"></i>
                        <span class="spec-label">KILOMETRAJE: <span class="spec-value"><?php echo number_format($car['mileage']); ?></span></span>
                    </div>
                    <!-- Item -->
                    <div class="spec-item">
                        <i class="fas fa-users spec-icon"></i>
                        <span class="spec-label">PASAJEROS: <span class="spec-value"><?php echo htmlspecialchars($car['passengers']); ?></span></span>
                    </div>

                    <!-- Item -->
                    <div class="spec-item">
                        <i class="fas fa-car spec-icon"></i>
                        <span class="spec-label">CILINDRAJE: <span class="spec-value"><?php echo htmlspecialchars($car['cylinders'] ?? 'N/A'); ?></span></span>
                    </div>
                    <!-- Item -->
                    <div class="spec-item">
                        <i class="fas fa-door-closed spec-icon"></i>
                        <span class="spec-label">PUERTAS: <span class="spec-value"><?php echo htmlspecialchars($car['doors'] ?? '4'); ?></span></span>
                    </div>

                    <!-- Item -->
                    <div class="spec-item">
                        <i class="fas fa-tint spec-icon"></i>
                        <span class="spec-label">COLOR EXT: <span class="spec-value"><?php echo htmlspecialchars($car['color_exterior'] ?? 'N/A'); ?></span></span>
                    </div>
                    <!-- Item -->
                    <div class="spec-item">
                        <i class="fas fa-swatchbook spec-icon"></i>
                        <span class="spec-label">COLOR INT: <span class="spec-value"><?php echo htmlspecialchars($car['color_interior'] ?? 'N/A'); ?></span></span>
                    </div>

                    <!-- Item -->
                    <div class="spec-item">
                        <i class="fas fa-code-branch spec-icon"></i>
                        <span class="spec-label">TRANSMISIÓN: <span class="spec-value"><?php echo htmlspecialchars($car['traction'] ?? 'N/A'); ?></span></span>
                    </div>
                    <!-- Item -->
                    <div class="spec-item">
                        <i class="fas fa-life-ring spec-icon"></i>
                        <span class="spec-label">DIRECCIÓN: <span class="spec-value"><?php echo htmlspecialchars($car['steering'] ?? 'Hidráulica'); ?></span></span>
                    </div>

                    <!-- Item -->
                    <div class="spec-item">
                        <i class="fas fa-cogs spec-icon"></i>
                        <span class="spec-label">MOTOR: <span class="spec-value"><?php echo htmlspecialchars($car['engine_displacement'] ?? 'N/A'); ?></span></span>
                    </div>
                    <!-- Item -->
                    <div class="spec-item">
                        <i class="fas fa-gas-pump spec-icon"></i>
                        <span class="spec-label">COMBUSTIBLE: <span class="spec-value"><?php echo htmlspecialchars($car['fuel_type'] ?? 'N/A'); ?></span></span>
                    </div>

                    <!-- Item -->
                    <div class="spec-item">
                        <i class="fas fa-snowflake spec-icon"></i>
                        <span class="spec-label">AIRE ACOND.: <span class="spec-value"><?php echo ($car['feature_ac']) ? 'SÍ' : 'NO'; ?></span></span>
                    </div>
                    <!-- Item -->
                    <div class="spec-item">
                        <i class="fas fa-bolt spec-icon"></i>
                        <span class="spec-label">VIDRIOS ELÉC.: <span class="spec-value"><?php echo ($car['feature_electric_windows']) ? 'SÍ' : 'NO'; ?></span></span>
                    </div>
                </div>

                <?php if(!empty($settings['whatsapp_number'])): ?>
                    <a href="https://wa.me/<?php echo $settings['whatsapp_number']; ?>?text=Hola, estoy interesado en el vehículo: <?php echo urlencode($car['make'] . ' ' . $car['model']); ?>" target="_blank" class="btn-whatsapp" style="width: 100%; justify-content: center; font-size: 1.1rem; padding: 1rem; margin-top: 2rem; border-radius: 0.5rem; background-color: #22c55e; color: #ffffff; display: flex; align-items: center; gap: 0.5rem; text-decoration: none; font-weight: 700;">
                        <i class="fab fa-whatsapp" style="font-size: 1.5rem;"></i> COTIZAR
                    </a>
                <?php endif; ?>

                <!-- Description Section (Moved) -->
                <div style="margin-top: 2rem; border-top: 1px solid #374151; padding-top: 1.5rem;">
                    <h3 style="margin-bottom: 0.5rem; font-size: 1.25rem; font-weight: 800; color: #3b82f6; text-transform: uppercase;">DESCRIPCIÓN</h3>
                    <p class="description-text" style="color: #d1d5db; line-height: 1.6; font-size: 0.95rem;">
                        <?php echo nl2br(htmlspecialchars($car['description'])); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Related Cars Section -->
        <div style="margin-top: 5rem; margin-bottom: 0;">
            <div class="section-header" style="margin-bottom: 2rem; border-left: 5px solid #3b82f6; padding-left: 1rem;">
                <h2 class="section-title-related" style="color: white; text-transform: uppercase; font-size: 1.8rem; margin: 0;">Vehículos Similares</h2>
            </div>
            
            <?php
                // Fetch Related Cars
                // Same Category, Not Current Car, Not Sold, Closest Price
                $stmt_related = $pdo->prepare("SELECT * FROM cars WHERE category = ? AND id != ? AND status_label != 'Vendido' ORDER BY ABS(price - ?) ASC LIMIT 8");
                $stmt_related->execute([$car['category'], $car['id'], $car['price']]);
                $related_cars = $stmt_related->fetchAll(PDO::FETCH_ASSOC);

                if(count($related_cars) > 0):
            ?>
            <div class="carousel-container" style="position: relative;">
                <button class="carousel-nav-btn nav-prev" onclick="scrollCarousel('carousel-related', -300)" style="position: absolute; left: 0; top: 50%; transform: translateY(-50%); z-index: 20; background: rgba(255,255,255,0.9); border: none; border-radius: 50%; width: 40px; height: 40px; color: #111827; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); cursor: pointer; display: flex; align-items: center; justify-content: center;"><i class="fas fa-chevron-left"></i></button>
                <div class="carousel-track" id="carousel-related" style="display: flex; gap: 1.5rem; overflow-x: auto; scroll-behavior: smooth; padding-bottom: 1.5rem; scrollbar-width: none;">
                    <?php foreach($related_cars as $related): ?>
                        <div class="related-car-card" onclick="location.href='car_details.php?id=<?php echo $related['id']; ?>'">
                            
                            <div style="position: relative; height: 200px;">
                                <?php if($related['status_label']): ?>
                                    <div class="car-badge <?php echo ($related['status_label'] == 'Nuevo') ? 'badge-new' : 'badge-promo'; ?>" style="position: absolute; top: 10px; left: 10px; z-index: 10; padding: 0.25rem 0.75rem; border-radius: 4px; color: white; font-weight: bold; font-size: 0.8rem; text-transform: uppercase; background-color: <?php echo ($related['status_label'] == 'Nuevo') ? '#10b981' : '#ef4444'; ?>;">
                                        <?php echo htmlspecialchars($related['status_label']); ?>
                                    </div>
                                <?php endif; ?>
                                <img src="<?php echo htmlspecialchars($related['image_path'] ?? ''); ?>" 
                                     alt="<?php echo htmlspecialchars($related['make'] ?? 'Car'); ?>" 
                                     style="width: 100%; height: 100%; object-fit: cover;">
                            </div>

                            <div class="car-info-card" style="padding: 1.25rem;">
                                <h3 class="related-car-title"><?php echo htmlspecialchars($related['make'] . ' ' . $related['model']); ?></h3>
                                <p class="related-car-price">C$<?php echo number_format($related['price']); ?></p>
                                <div class="related-car-meta">
                                    <span><i class="fas fa-calendar" style="margin-right: 0.25rem;"></i> <?php echo htmlspecialchars($related['year']); ?></span>
                                    <span><i class="fas fa-tachometer-alt" style="margin-right: 0.25rem;"></i> <?php echo number_format($related['mileage']); ?> km</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-nav-btn nav-next" onclick="scrollCarousel('carousel-related', 300)" style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); z-index: 20; background: rgba(255,255,255,0.9); border: none; border-radius: 50%; width: 40px; height: 40px; color: #111827; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); cursor: pointer; display: flex; align-items: center; justify-content: center;"><i class="fas fa-chevron-right"></i></button>
            </div>
            <?php else: ?>
                <div style="background: rgba(255,255,255,0.05); padding: 2rem; border-radius: 0.5rem; text-align: center;">
                    <p style="color: #9ca3af; font-size: 1.1rem;">No se encontraron otros vehículos similares por el momento.</p>
                </div>
            <?php endif; ?>
        </div>

    </div> <!-- End of details-container -->

    <script>
        function scrollCarousel(id, offset) {
            const container = document.getElementById(id);
            container.scrollBy({ left: offset, behavior: 'smooth' });
        }
        
        // Auto Scroll Loop
        document.addEventListener('DOMContentLoaded', () => {
             const container = document.getElementById('carousel-related');
             if(container) {
                 setInterval(() => {
                    // Check if scrolled to end
                    if(container.scrollLeft + container.clientWidth >= container.scrollWidth - 5) {
                         container.scrollTo({ left: 0, behavior: 'smooth' });
                    } else {
                         container.scrollBy({ left: 320, behavior: 'smooth' });
                    }
                 }, 5000);
             }
        });
    </script>





    <!-- Footer -->
    <?php include 'includes/footer_frontend.php'; ?>
    
    <style>
        /* Hide floating whatsapp button on this specific page as requested */
        .whatsapp-float { display: none !important; }
    </style>
</body>
</html>
