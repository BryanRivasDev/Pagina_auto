<?php
// Ensure database connection is available
if (!isset($pdo)) {
    require_once __DIR__ . '/../config.php';
}

// Fetch settings if not already fetched
if (!isset($navbar_title) || !isset($logo_path)) {
    $stmt_settings = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
    $settings = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);
    $navbar_title = $settings['navbar_title'] ?? 'AutoMarket';
    $logo_path = $settings['logo_path'] ?? '';
}
?>

<!-- Navbar -->
<nav class="navbar">
    <div class="container nav-content">
        <a href="index.php" class="brand-link" style="text-decoration: none; display: flex; align-items: center; gap: 15px;">
            <?php 
            $logoPath = $settings['site_logo'] ?? '';
            $siteName = $settings['site_name'] ?? 'AUTOIMARKET'; // Default requested by user
            
            if (!empty($logoPath) && file_exists(__DIR__ . '/../' . $logoPath)): ?>
                <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="<?php echo htmlspecialchars($siteName); ?>" style="height: 60px; width: auto; object-fit: contain;">
            <?php endif; ?>
            
            <h1 class="brand-title" style="font-size: 1.5rem; font-weight: 800; margin: 0; letter-spacing: -1px; text-transform: uppercase;">
                <?php echo htmlspecialchars($siteName); ?>
            </h1>
        </a>
        
        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" aria-label="Toggle menu">
            <i class="fas fa-bars"></i>
        </button>

        <div class="nav-links">
            <?php 
            // Dynamic Navbar
            $nav_links = $pdo->query("SELECT * FROM navbar_links WHERE is_visible = 1 ORDER BY order_index ASC")->fetchAll(PDO::FETCH_ASSOC);
            foreach($nav_links as $link): 
                // Check if this is the Inventory/Vehicles link
                // Uses partial match to be more robust against small variations
                $currentUrl = strtolower(trim($link['url']));
                $currentLabel = mb_strtolower(trim($link['label']), 'UTF-8');
                
                // Determine Icon
                $iconClass = '';
                // Debug
                echo "<!-- DEBUG: Label='{$link['label']}', URL='{$link['url']}' -->";

                // Determine Icon
                $iconClass = '';
                if (stripos($link['label'], 'inicio') !== false || stripos($link['label'], 'home') !== false) {
                    $iconClass = 'fa-home';
                } elseif (stripos($link['label'], 'veh') !== false || stripos($link['label'], 'cat') !== false || stripos($link['label'], 'inv') !== false || stripos($link['url'], 'inventory') !== false) {
                    $iconClass = 'fa-car';
                } elseif (stripos($link['label'], 'nosotros') !== false || stripos($link['label'], 'about') !== false) {
                    $iconClass = 'fa-users';
                } elseif (stripos($link['label'], 'contac') !== false) {
                    $iconClass = 'fa-envelope';
                }
                
                $iconHtml = $iconClass ? '<i class="fas ' . $iconClass . '" style="margin-right:8px;"></i>' : '';

                // ROBUST CHECK with NEGATIVE LOGIC
                // If it is NOT Home, NOT About, and NOT Contact, assume it is the Inventory/Vehicles dropdown
                // This avoids encoding issues with "Vehículos"
                $is_home = (stripos($link['label'], 'inicio') !== false || stripos($link['label'], 'home') !== false);
                $is_about = (stripos($link['label'], 'nosotros') !== false || stripos($link['label'], 'about') !== false);
                $is_contact = (stripos($link['label'], 'contac') !== false);

                if (!$is_home && !$is_about && !$is_contact):
            ?>
                <div class="dropdown">
                    <button type="button" class="dropdown-toggle">
                        <?php echo $iconHtml; ?><?php echo htmlspecialchars($link['label']); ?> 
                        <i class="fas fa-chevron-down" style="font-size:0.7em; margin-left:5px;"></i>
                    </button>
                    <div class="dropdown-content">
                        <a href="inventory.php">Ver Todo</a>
                        <?php 
                        $cats = $pdo->query("SELECT name FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
                        foreach($cats as $cat):
                        ?>
                            <a href="inventory.php?category=<?php echo urlencode($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo htmlspecialchars($link['url']); ?>">
                    <?php echo $iconHtml; ?><?php echo htmlspecialchars($link['label']); ?>
                </a>
            <?php endif; endforeach; ?>
            
            <!-- Theme Toggle (Moved Inside Nav Links for Desktop Right Alignment) -->
            <button id="themeToggleFront" style="background:none; border:none; cursor:pointer; font-size:1.2rem; margin-left:1rem; color: var(--text-main); transition: color 0.3s; display: flex; align-items: center;" aria-label="Toggle Theme">
                <i class="fas fa-sun"></i>
            </button>
        </div>
    </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileToggle = document.querySelector('.mobile-menu-toggle');
        const navLinks = document.querySelector('.nav-links');
        
        if(mobileToggle && navLinks) {
            mobileToggle.addEventListener('click', function() {
                navLinks.classList.toggle('active');
                
                // Toggle icon
                const icon = this.querySelector('i');
                if(navLinks.classList.contains('active')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
        }

        // Mobile/Touch Dropdown Toggle Logic
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const parent = this.parentElement;
                const wasActive = parent.classList.contains('active');
                
                // Close other dropdowns AND this one (clean state)
                document.querySelectorAll('.dropdown').forEach(d => {
                    d.classList.remove('active');
                });

                // If it WAS NOT active before, open it now
                // If it WAS active, we leave it closed (toggled off)
                if (!wasActive) {
                    parent.classList.add('active');
                }
            });
        });

        // Close on click outside
        document.addEventListener('click', function() {
            document.querySelectorAll('.dropdown').forEach(d => {
                d.classList.remove('active');
            });
        });
    });

    // Frontend Theme Logic
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('themeToggleFront');
        const icon = toggle.querySelector('i');
        const body = document.body;
        
        // Check Preference
        const savedTheme = localStorage.getItem('theme');
        
        // Website is Dark by default. 
        // If savedTheme is 'light', we add .light-mode class.
        if (savedTheme === 'light') {
             body.classList.add('light-mode');
             icon.classList.replace('fa-sun', 'fa-moon');
        } else {
             // Default Dark State
             icon.classList.replace('fa-moon', 'fa-sun'); 
        }

        toggle.addEventListener('click', () => {
             body.classList.toggle('light-mode');
             
             if(body.classList.contains('light-mode')) {
                 localStorage.setItem('theme', 'light');
                 icon.classList.replace('fa-sun', 'fa-moon'); // Show Moon (to switch back to dark)
             } else {
                 localStorage.setItem('theme', 'dark');
                 icon.classList.replace('fa-moon', 'fa-sun'); // Show Sun (to switch back to light)
             }
        });
    });
</script>
