<?php
require_once 'config.php';

// Fetch Settings
$stmt = $pdo->query("SELECT * FROM site_settings");
$settings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Default Fallbacks if DB is empty or fails
$site_name = $settings['site_name'] ?? 'Venta de Autos';
$navbar_title = $settings['navbar_title'] ?? 'AUTOSALES';
$hero_title = $settings['hero_title'] ?? 'Encuentra tu Auto Ideal';
$hero_subtitle = $settings['hero_subtitle'] ?? 'Calidad, Confianza y los Mejores Precios';
$logo_path = $settings['logo_path'] ?? ''; // Path to logo
$whatsapp_number = $settings['whatsapp_number'] ?? '50599999999';
$contact_phone = $settings['contact_phone'] ?? '+505 9999-9999';
$contact_email = $settings['contact_email'] ?? 'info@gmail.com';
$contact_address = $settings['contact_address'] ?? 'Managua';


// Build Query for Cars
$where_clauses = [];
$params = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
    $where_clauses[] = "(make LIKE :search OR model LIKE :search OR CONCAT(make, ' ', model) LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if (isset($_GET['brand']) && !empty($_GET['brand'])) {
    $where_clauses[] = "make LIKE :brand"; // Assuming 'make' column in 'cars' stores the brand name
    $params[':brand'] = '%' . $_GET['brand'] . '%';
}

if (isset($_GET['year_min']) && !empty($_GET['year_min'])) {
    $where_clauses[] = "year >= :year_min";
    $params[':year_min'] = $_GET['year_min'];
}

if (isset($_GET['price_max']) && !empty($_GET['price_max'])) {
    $where_clauses[] = "price <= :price_max";
    $params[':price_max'] = $_GET['price_max'];
}

$sql = "SELECT * FROM cars WHERE is_sold = 0";
if (count($where_clauses) > 0) {
    $sql .= " AND " . implode(' AND ', $where_clauses);
}
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper for WhatsApp
function getWhatsAppLink($car, $base_number) {
    $text = "Hola, me interesa el auto " . $car['make'] . " " . $car['model'] . " (" . $car['year'] . ")";
    return "https://wa.me/" . $base_number . "?text=" . urlencode($text);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_name); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>


    <?php include 'includes/navbar_frontend.php'; ?>

    <!-- Hero -->
    <!-- Dynamic Carousel -->
    <?php
    $slides = $pdo->query("SELECT * FROM carousel_slides ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="hero-slider">
        <?php if(count($slides) > 0): ?>
            <?php foreach($slides as $index => $slide): ?>
                <?php 
                $ext = strtolower(pathinfo($slide['image_path'], PATHINFO_EXTENSION));
                $is_video = in_array($ext, ['mp4', 'webm', 'ogg']);
                ?>
                <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>" data-type="<?php echo $is_video ? 'video' : 'image'; ?>">
                    <?php if ($is_video): ?>
                        <video class="slide-video" src="<?php echo htmlspecialchars($slide['image_path']); ?>" muted playsinline style="width:100%; height:100%; object-fit:cover; position: absolute; top: 0; left: 0; z-index: 0;"></video>
                    <?php else: ?>
                        <div class="slide-bg" style="background-image: url('<?php echo htmlspecialchars($slide['image_path']); ?>'); width:100%; height:100%; background-size: cover; background-position: center; position: absolute; top:0; left:0; z-index: 0;"></div>
                    <?php endif; ?>
                    
                    <div class="slide-content" style="z-index: 2;">
                        <div class="slide-title"><?php echo htmlspecialchars($slide['title']); ?></div>
                        <div class="slide-subtitle"><?php echo htmlspecialchars($slide['subtitle']); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
            <!-- Navigation Buttons -->
            <a class="prev" onclick="moveSlide(-1)">&#10094;</a>
            <a class="next" onclick="moveSlide(1)">&#10095;</a>
        <?php else: ?>
            <!-- Fallback Static Hero if no slides -->
            <div class="slide active" style="background-image: url('assets/images/hero-bg.jpg'); background-color: #111;">
                <div class="slide-content">
                    <div class="slide-title"><?php echo htmlspecialchars($hero_title); ?></div>
                    <div class="slide-subtitle"><?php echo htmlspecialchars($hero_subtitle); ?></div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Search Section -->
    <script>
        // Enhanced Carousel Script
        let slideIndex = 0;
        let timer;
        const slides = document.querySelectorAll('.slide');

        function showSlide(n) {
            if (slides.length === 0) return;
            
            // Wrap around
            if (n >= slides.length) slideIndex = 0;
            if (n < 0) slideIndex = slides.length - 1;

            // Stop any playing videos
            const videos = document.querySelectorAll('.slide video');
            videos.forEach(v => v.pause());

            // Update UI
            slides.forEach(slide => slide.classList.remove('active'));
            slides[slideIndex].classList.add('active');
            
            // Logic handled by resetTimer called after this or by init
            // exception: if called by interval, we need resetTimer logic to run if it wasn't called externally?
            // actually resetTimer calls showSlide. 
            // If showSlide is called by interval, we need to ensure the logic for the NEW slide starts?
            // Wait, resetTimer sets the logic for the *current* slide.
            // If we use interval, it just does slideIndex++ and showSlide. 
            // The interval keeps running. 
            
            // If the NEW slide is a video, we must STOP restrictions and let video logic take over.
            // If we are in 'image mode' (interval running), and we hit a video, we need to STOP the interval.
            
            // Let's refactor: showSlide should just show. The logic controller should be separate.
            // But to keep it simple with existing code:
            
            // We need to re-evaluate the timer logic whenever the slide changes.
            // If showSlide is called, we should probably trigger a timer check.
            
            // Actually, moveSlide calls resetTimer.
            // The interval function calls showSlide but DOES NOT call resetTimer.
            // If the interval hits a video, it will just start the video (maybe) but the interval is still running!
            // That's a bug. The interval will skip the video after 5s even if it's long.
            
            // FIX: The interval shoud NOT directly call showSlide. It should call moveSlide(1).
        }

        function moveSlide(n) {
            slideIndex += n;
            showSlide(slideIndex);
            resetTimer();
        }

        function resetTimer() {
            clearInterval(timer);
            
            // Check current slide type
            const currentSlide = slides[slideIndex];
            const isVideo = currentSlide.getAttribute('data-type') === 'video';
            const video = currentSlide.querySelector('video');

            if (isVideo && video) {
                // IT IS A VIDEO: Don't set interval yet. Play video.
                video.currentTime = 0;
                video.muted = true; // start muted for autoplay policy
                video.play().then(() => {
                    // console.log("Video playing");
                }).catch(e => {
                    console.error("Autoplay failed", e);
                    // Fallback: treat as image if play fails
                    startInterval(); 
                });

                // When video ends, go next
                video.onended = function() {
                    moveSlide(1);
                };
            } else {
                // IT IS IMAGE: Start standard timer
                startInterval();
            }
        }

        function startInterval() {
             timer = setInterval(() => {
                moveSlide(1);
            }, 5000); // 5 seconds
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            if(slides.length > 0) { 
                showSlide(slideIndex);
                resetTimer(); // Trigger playback logic (play video or start timer)
            }
        });
    </script>

    <div class="container">
        <!-- Advanced Search Filter -->
        <div class="search-container-wrapper" style="margin-top: -3rem; position: relative; z-index: 20; padding: 0 1rem;">
            <form action="index.php" method="get" class="modern-search-bar">
                <div class="search-input-group">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="search" placeholder="Buscar: Hilux, Suzuki, Corolla..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </div>
                
                <div class="search-divider"></div>

                <div class="search-dropdown-group">
                    <select name="brand">
                        <option value="">MARCAS</option>
                        <?php 
                        $stmt_brand = $pdo->query("SELECT name FROM brands ORDER BY name ASC");
                        while($row_brand = $stmt_brand->fetch(PDO::FETCH_ASSOC)) {
                            $selected = (isset($_GET['brand']) && $_GET['brand'] == $row_brand['name']) ? 'selected' : '';
                            echo '<option value="'.htmlspecialchars($row_brand['name']).'" '.$selected.'>'.htmlspecialchars($row_brand['name']).'</option>';
                        }
                        ?>
                    </select>
                    <i class="fas fa-chevron-down dropdown-icon"></i>
                </div>

                <div class="search-divider"></div>

                <div class="search-dropdown-group">
                    <select name="category">
                        <option value="">TIPO</option>
                        <?php 
                        $stmt_cat = $pdo->query("SELECT name FROM categories ORDER BY name ASC");
                        while($row_cat = $stmt_cat->fetch(PDO::FETCH_ASSOC)) {
                            $selected = (isset($_GET['category']) && $_GET['category'] == $row_cat['name']) ? 'selected' : '';
                            echo '<option value="'.htmlspecialchars($row_cat['name']).'" '.$selected.'>'.htmlspecialchars($row_cat['name']).'</option>';
                        }
                        ?>
                    </select>
                    <i class="fas fa-chevron-down dropdown-icon"></i>
                </div>

                <button type="submit" class="btn-search-main">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>



<?php
// Fetch Site Settings
$stmt_settings = $pdo->query("SELECT * FROM site_settings");
$site_settings = [];
while ($row = $stmt_settings->fetch(PDO::FETCH_ASSOC)) {
    $site_settings[$row['setting_key']] = $row['setting_value'];
}

// Helper to get setting with fallback
function getSetting($key, $settings, $default) {
    return !empty($settings[$key]) ? htmlspecialchars($settings[$key]) : $default;
}
?>

        <!-- Carousels Section -->
        <div style="margin-top: 2rem;">
            
            <!-- Carousel 1: Ofertas -->
            <div class="section-header">
                <div class="header-text-group">
                    <h2 style="color: white; text-transform: uppercase; margin-bottom: 0.2rem;"><?php echo getSetting('carousel_1_title', $site_settings, '¡Encontrá tú próximo vehículo ahora!'); ?></h2>
                    <p style="color: #9ca3af; font-size: 1rem; width: 80%; text-transform: uppercase; font-weight: 500; letter-spacing: 0.5px;"><?php echo getSetting('carousel_1_subtitle', $site_settings, ''); ?></p>
                </div>
                <div style="display:flex; gap:0.5rem;">
                    <button class="btn-view-all" style="padding: 0.5rem 1rem; font-size: 0.8rem; background: transparent; border: 1px solid white; color: white; border-radius: 4px; cursor: pointer; text-transform: uppercase; white-space: nowrap;" onclick="location.href='inventory.php?status=Oferta'">VER TODAS</button>
                </div>
            </div>
            
            <div class="carousel-container">
                <button class="carousel-nav-btn nav-prev" onclick="scrollCarousel('carousel-recent', -1)"><i class="fas fa-chevron-left"></i></button>
                <div class="carousel-track" id="carousel-recent">
                    <?php 
                    // Query: Ofertas Only
                    $stmt_ofertas = $pdo->query("SELECT * FROM cars WHERE status_label = 'Oferta' ORDER BY created_at DESC LIMIT 8");
                    $cars_ofertas = $stmt_ofertas->fetchAll(PDO::FETCH_ASSOC);
                    
                    if(count($cars_ofertas) > 0) {
                        foreach($cars_ofertas as $car):
                        ?>
                            <div class="car-card" onclick="location.href='car_details.php?id=<?php echo $car['id']; ?>'" style="cursor: pointer;">
                                <div class="car-image-container">
                                    <?php if(!empty($car['status_label'])): ?>
                                        <div class="badge-top-right"><?php echo htmlspecialchars($car['status_label']); ?></div>
                                    <?php endif; ?>
                                    
                                    <img src="<?php echo htmlspecialchars($car['image_path']); ?>" alt="Car" class="car-image">
                                </div>
                                
                                <div class="car-details">
                                    <h3 class="car-title"><?php echo htmlspecialchars($car['model'] . ' ' . $car['year']); ?></h3>
                                    <div class="car-subtitle">
                                        <span><?php echo $car['year']; ?></span>
                                        <span>•</span>
                                        <span><?php echo htmlspecialchars($car['make']); ?></span>
                                        <span>•</span>
                                        <span><?php echo number_format($car['mileage']); ?> km</span>
                                    </div>
                                    
                                    <div class="car-divider"></div>
                                    
                                    <div class="car-footer">
                                        <div class="price-box">
                                            <span class="price-label">Precio C$:</span>
                                            <span class="price-value">
                                                <?php echo ($car['show_price']) ? number_format($car['price']) : 'Consultar'; ?>
                                            </span>
                                        </div>
                                        <a href="car_details.php?id=<?php echo $car['id']; ?>" class="btn-details">
                                            Ver detalles
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php 
                        endforeach; 
                    } else {
                        echo '<p style="padding: 2rem; color: #666;">No hay ofertas disponibles por el momento.</p>';
                    }
                    ?>
                </div>
                <button class="carousel-nav-btn nav-next" onclick="scrollCarousel('carousel-recent', 1)"><i class="fas fa-chevron-right"></i></button>
            </div>

            <!-- Advertising Banner -->
            <?php 
            $ads_banner = getSetting('ads_banner', $site_settings, '');
            $ads_banner_url = getSetting('ads_banner_url', $site_settings, '');
            ?>
            <div class="ads-banner-container" style="margin-top: 3rem; margin-bottom: 1rem; padding: 0 0.5rem;">
                <?php if(!empty($ads_banner)): 
                    $ext = strtolower(pathinfo($ads_banner, PATHINFO_EXTENSION));
                    $is_video = in_array($ext, ['mp4', 'webm', 'ogg']);
                ?>
                    <?php if(!empty($ads_banner_url)): ?>
                        <a href="<?php echo htmlspecialchars($ads_banner_url); ?>">
                            <?php if($is_video): ?>
                                <video src="<?php echo htmlspecialchars($ads_banner); ?>" autoplay loop muted playsinline style="width: 100%; height: 200px; object-fit: cover; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);"></video>
                            <?php else: ?>
                                <img src="<?php echo htmlspecialchars($ads_banner); ?>" alt="Publicidad" style="width: 100%; height: 200px; object-fit: cover; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                            <?php endif; ?>
                        </a>
                    <?php else: ?>
                        <?php if($is_video): ?>
                             <video src="<?php echo htmlspecialchars($ads_banner); ?>" autoplay loop muted playsinline style="width: 100%; height: 200px; object-fit: cover; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);"></video>
                        <?php else: ?>
                             <img src="<?php echo htmlspecialchars($ads_banner); ?>" alt="Publicidad" style="width: 100%; height: 200px; object-fit: cover; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                        <?php endif; ?>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Placeholder -->
                    <div style="width: 100%; height: 200px; background: linear-gradient(45deg, #1f2937, #111827); border-radius: 1rem; display: flex; align-items: center; justify-content: center; border: 2px dashed #374151; color: #9ca3af; flex-direction: column;">
                        <i class="fas fa-ad" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                        <span style="font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Espacio Publicitario</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Carousel 2: Nuevos Ingresos -->
            <div class="section-header" style="margin-top: 3rem;">
                <div class="header-text-group">
                    <h2 style="color: white; text-transform: uppercase; margin-bottom: 0.2rem;"><?php echo getSetting('carousel_2_title', $site_settings, 'Nuevos Ingresos'); ?></h2>
                    <p style="color: #9ca3af; font-size: 1rem; width: 80%; text-transform: uppercase; font-weight: 500; letter-spacing: 0.5px;"><?php echo getSetting('carousel_2_subtitle', $site_settings, ''); ?></p>
                </div>
                <div style="display:flex; gap:0.5rem;">
                    <button class="btn-view-all" style="padding: 0.5rem 1rem; font-size: 0.8rem; background: transparent; border: 1px solid white; color: white; border-radius: 4px; cursor: pointer; text-transform: uppercase; white-space: nowrap;" onclick="location.href='inventory.php?status=Nuevo'">VER TODAS</button>
                </div>
            </div>
            
            <div class="carousel-container">
                <button class="carousel-nav-btn nav-prev" onclick="scrollCarousel('carousel-featured', -1)"><i class="fas fa-chevron-left"></i></button>
                <div class="carousel-track" id="carousel-featured">
                    <?php 
                    // Query: Nuevo Only
                    $stmt_nuevos = $pdo->query("SELECT * FROM cars WHERE status_label = 'Nuevo' ORDER BY created_at DESC LIMIT 8");
                    $cars_nuevos = $stmt_nuevos->fetchAll(PDO::FETCH_ASSOC);
                    
                    if(count($cars_nuevos) > 0) {
                        foreach($cars_nuevos as $car):
                        ?>
                            <div class="car-card" onclick="location.href='car_details.php?id=<?php echo $car['id']; ?>'" style="cursor: pointer;">
                                <div class="car-image-container">
                                    <?php if(!empty($car['status_label'])): ?>
                                        <div class="badge-top-right" style="background: #10b981;"><?php echo htmlspecialchars($car['status_label']); ?></div>
                                    <?php endif; ?>
                                    <img src="<?php echo htmlspecialchars($car['image_path']); ?>" alt="Car" class="car-image">
                                </div>
                                
                                <div class="car-details">
                                    <h3 class="car-title"><?php echo htmlspecialchars($car['model'] . ' ' . $car['year']); ?></h3>
                                    <div class="car-subtitle">
                                        <span><?php echo $car['year']; ?></span>
                                        <span>•</span>
                                        <span><?php echo htmlspecialchars($car['make']); ?></span>
                                        <span>•</span>
                                        <span><?php echo number_format($car['mileage']); ?> km</span>
                                    </div>
                                    
                                    <div class="car-divider"></div>
                                    
                                    <div class="car-footer">
                                        <div class="price-box">
                                            <span class="price-label">Precio C$:</span>
                                            <span class="price-value">
                                                <?php echo ($car['show_price']) ? number_format($car['price']) : 'Consultar'; ?>
                                            </span>
                                        </div>
                                        <a href="car_details.php?id=<?php echo $car['id']; ?>" class="btn-details">
                                            Ver detalles
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php 
                        endforeach;
                    } else {
                         echo '<p style="padding: 2rem; color: #666;">No hay nuevos ingresos por el momento.</p>';
                    }
                    ?>
                </div>
                <button class="carousel-nav-btn nav-next" onclick="scrollCarousel('carousel-featured', 1)"><i class="fas fa-chevron-right"></i></button>
            </div>

        </div>

        <!-- Second Advertising Banner -->
        <?php 
        $ads_banner_2 = getSetting('ads_banner_2', $site_settings, '');
        $ads_banner_2_url = getSetting('ads_banner_2_url', $site_settings, '');
        ?>
        <div class="ads-banner-container" style="margin-top: 3rem; margin-bottom: 1rem; padding: 0 0.5rem;">
            <?php if(!empty($ads_banner_2)): 
                $ext2 = strtolower(pathinfo($ads_banner_2, PATHINFO_EXTENSION));
                $is_video2 = in_array($ext2, ['mp4', 'webm', 'ogg']);
            ?>
                <?php if(!empty($ads_banner_2_url)): ?>
                    <a href="<?php echo htmlspecialchars($ads_banner_2_url); ?>">
                         <?php if($is_video2): ?>
                            <video src="<?php echo htmlspecialchars($ads_banner_2); ?>" autoplay loop muted playsinline style="width: 100%; height: 200px; object-fit: cover; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);"></video>
                         <?php else: ?>
                            <img src="<?php echo htmlspecialchars($ads_banner_2); ?>" alt="Publicidad" style="width: 100%; height: 200px; object-fit: cover; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                         <?php endif; ?>
                    </a>
                <?php else: ?>
                    <?php if($is_video2): ?>
                        <video src="<?php echo htmlspecialchars($ads_banner_2); ?>" autoplay loop muted playsinline style="width: 100%; height: 200px; object-fit: cover; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);"></video>
                    <?php else: ?>
                        <img src="<?php echo htmlspecialchars($ads_banner_2); ?>" alt="Publicidad" style="width: 100%; height: 200px; object-fit: cover; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <?php endif; ?>
                <?php endif; ?>
            <?php else: ?>
                <!-- Placeholder -->
                <div style="width: 100%; height: 200px; background: linear-gradient(45deg, #1f2937, #111827); border-radius: 1rem; display: flex; align-items: center; justify-content: center; border: 2px dashed #374151; color: #9ca3af; flex-direction: column;">
                    <i class="fas fa-ad" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                    <span style="font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Espacio Publicitario</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Explore Sections (Moved Below) -->
        <div class="explore-container-split" style="margin-top: 4rem; display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;">
            
            <div class="section-explore">
                <h2 class="section-title-explore" style="margin-bottom: 2rem;"><?php echo getSetting('explore_category_title', $site_settings, '| Explora por categoria'); ?></h2>
                <div class="explore-grid">
                    <?php 
                    $cats = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
                    foreach($cats as $cat): 
                    ?>
                        <a href="inventory.php?category=<?php echo urlencode($cat['name']); ?>" class="explore-card">
                            <?php if($cat['image_path']): ?>
                                <img src="<?php echo htmlspecialchars($cat['image_path']); ?>" alt="<?php echo htmlspecialchars($cat['name']); ?>">
                            <?php else: ?>
                                <i class="fas fa-car-side" style="font-size: 2rem; color: #9ca3af;"></i>
                            <?php endif; ?>
                            <span><?php echo htmlspecialchars($cat['name']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="section-explore">
                <h2 class="section-title-explore" style="margin-bottom: 2rem;"><?php echo getSetting('explore_brand_title', $site_settings, '| Explora por Marca'); ?></h2>
                <div class="explore-grid-brands">
                    <?php 
                    $brands = $pdo->query("SELECT * FROM brands ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
                    foreach($brands as $brand): 
                    ?>
                        <a href="inventory.php?brand=<?php echo urlencode($brand['name']); ?>" class="explore-card-brand">
                            <?php if($brand['logo_path']): ?>
                                <img src="<?php echo htmlspecialchars($brand['logo_path']); ?>" alt="<?php echo htmlspecialchars($brand['name']); ?>">
                            <?php else: ?>
                                <span style="font-weight: 700; color: #cbd5e1;"><?php echo htmlspecialchars($brand['name']); ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <!-- Responsive CSS for split container -->
        <style>
            @media (max-width: 900px) {
                .explore-container-split {
                    grid-template-columns: 1fr !important;
                }
            }
        </style>

        <script>
            function scrollCarousel(id, direction) {
                const container = document.getElementById(id);
                // Scroll by the full visible width (group of 4, 3, 2, or 1 depending on screen)
                const scrollAmount = container.clientWidth;
                container.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
            }

            // Auto-Scroll Logic
            function startAutoScroll(id, speed) {
                const container = document.getElementById(id);
                
                setInterval(() => {
                    // Start of the container
                    if(container.scrollLeft + container.clientWidth >= container.scrollWidth - 10) { // -10 tolerance
                        // Reset to start
                         container.scrollTo({ left: 0, behavior: 'smooth' });
                    } else {
                         // Scroll by clientWidth (one view)
                         container.scrollBy({ left: container.clientWidth, behavior: 'smooth' });
                    }
                }, speed);
            }

            // Start Auto Scroll
            document.addEventListener('DOMContentLoaded', () => {
                 // Scroll every 4 seconds
                 startAutoScroll('carousel-recent', 4000);
                 // Offset second carousel so they don't move at exact same time
                 setTimeout(() => startAutoScroll('carousel-featured', 4000), 2000);
            });
        </script>


    <script>
        // Custom Glassmorphic Select Logic
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownGroups = document.querySelectorAll('.search-dropdown-group');
            
            dropdownGroups.forEach(group => {
                const select = group.querySelector('select');
                if (!select) return;

                // Create Custom Select Wrapper
                const customSelect = document.createElement('div');
                customSelect.className = 'custom-select';
                
                // Trigger (The Button)
                const trigger = document.createElement('div');
                trigger.className = 'custom-select-trigger';
                
                // Get selected option text
                const selectedOption = select.options[select.selectedIndex];
                const selectedText = selectedOption ? selectedOption.text : select.options[0].text;
                
                trigger.innerHTML = `<span>${selectedText}</span><i class="fas fa-chevron-down"></i>`;
                customSelect.appendChild(trigger);
                
                // Options List
                const optionsList = document.createElement('div');
                optionsList.className = 'custom-options';
                
                Array.from(select.options).forEach(option => {
                    // Skip placeholder/empty value options in the list
                    if (option.value === "") return;

                    const customOption = document.createElement('span');
                    customOption.className = `custom-option ${option.selected ? 'selected' : ''}`;
                    customOption.setAttribute('data-value', option.value);
                    customOption.textContent = option.text;
                    
                    // Click Event for Option
                    customOption.addEventListener('click', function(e) {
                        e.stopPropagation(); // Prevent bubbling
                        
                        // Update Original Select
                        select.value = this.getAttribute('data-value');
                        select.dispatchEvent(new Event('change')); // Trigger change for generic listeners
                        
                        // Update Trigger Text
                        trigger.querySelector('span').textContent = this.textContent;
                        
                        // Update Selection UI
                        optionsList.querySelectorAll('.custom-option').forEach(opt => opt.classList.remove('selected'));
                        this.classList.add('selected');
                        
                        // Close Dropdown
                        customSelect.classList.remove('open');
                    });
                    
                    optionsList.appendChild(customOption);
                });
                
                customSelect.appendChild(optionsList);
                
                // Toggle Dropdown
                trigger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    // Close all other dropdowns first
                    document.querySelectorAll('.custom-select').forEach(s => {
                        if (s !== customSelect) s.classList.remove('open');
                    });
                    customSelect.classList.toggle('open');
                });
                
                // Append to DOM and hide original
                group.appendChild(customSelect);
                select.style.display = 'none'; // Ensure raw select is hidden
                
                // Remove original icon if present (to avoid double icons)
                const oldIcon = group.querySelector('.dropdown-icon');
                if(oldIcon) oldIcon.style.display = 'none';
            });
            
            // Close when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.custom-select')) {
                    document.querySelectorAll('.custom-select').forEach(s => s.classList.remove('open'));
                }
            });
        });
    </script>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer_frontend.php'; ?>
</body>
</html>
