<?php
if(!isset($_SESSION)) { session_start(); }
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../auth.php';
checkLoggedIn();

// Fetch settings for Sidebar Logo if needed, or just hardcode Admin Title
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CPanel - <?php echo $pageTitle ?? 'Admin'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #3b82f6; /* Corporate Blue */
            --primary-dark: #1d4ed8;
            --sidebar-bg: #ffffff; /* White Sidebar */
            --sidebar-text: #64748b; /* Slate 500 */
            --sidebar-active: #3b82f6;
            --sidebar-active-bg: #eff6ff; /* Blue 50 */
            --header-height: 70px;
            --sidebar-width: 260px;
            --bg-color: #f3f4f6; /* Light Gray Background */
            --surface: #ffffff; /* White Surface */
            --border: #e2e8f0; /* Slate 200 */
            --text-main: #0f172a; /* Slate 900 */
            --text-light: #64748b; /* Slate 500 */
        }
        
        body.dark-mode {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --sidebar-bg: #111827; /* Dark Sidebar */
            --sidebar-text: #9ca3af;
            --sidebar-active: #ffffff;
            --sidebar-active-bg: #1f2937;
            --bg-color: #111827; /* Dark Background */
            --surface: #1f2937; /* Dark Surface */
            --border: #374151; /* Dark Border */
            --text-main: #f3f4f6; /* Light Text */
            --text-light: #9ca3af;
        }
        
        body.dark-mode input[type="text"], 
        body.dark-mode input[type="number"], 
        body.dark-mode input[type="email"], 
        body.dark-mode input[type="password"],
        body.dark-mode textarea,
        body.dark-mode select {
             background-color: var(--surface);
             color: var(--text-main);
             border-color: var(--border);
        }
        
        body.dark-mode .nav-item a.active {
             color: #3b82f6; /* Blue text for active in dark mode */
             background: #ffffff; /* White bg for active in dark mode */
        }
        
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-color);
            margin: 0;
            display: flex;
            min-height: 100vh;
            color: var(--text-main);
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            color: white;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 20px rgba(0,0,0,0.05);
            z-index: 100;
            transition: transform 0.3s ease;
        }
        
        .logo-area {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--primary);
            border-bottom: 1px solid var(--border);
            background: var(--sidebar-bg);
            letter-spacing: -0.5px;
        }

        .nav-menu {
            list-style: none;
            padding: 1.5rem 1rem;
            margin: 0;
            flex-grow: 1;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            padding: 0.875rem 1rem;
            color: var(--sidebar-text);
            text-decoration: none;
            transition: all 0.2s ease;
            font-weight: 500;
            border-radius: 0.5rem;
            font-size: 0.95rem;
        }

        .nav-item a:hover {
            color: var(--primary);
            background-color: var(--sidebar-active-bg);
        }
        
        .nav-item a.active {
            color: white; /* White text on blue bg */
            background-color: var(--primary);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .nav-item i {
            width: 24px;
            margin-right: 12px;
            font-size: 1.1rem;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            width: calc(100% - var(--sidebar-width));
            display: flex;
            flex-direction: column;
        }

        .top-header {
            height: var(--header-height);
            background: var(--sidebar-bg);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 99px;
            background: var(--surface);
            border: 1px solid var(--border);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            color: var(--text-main);
        }

        .page-content {
            padding: 2.5rem;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            box-sizing: border-box;
        }

        /* Modern Global Elements */
        h1 {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--text-main);
            margin: 0;
            letter-spacing: -0.5px;
        }

        .card {
            background: var(--surface);
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--border);
            padding: 2rem;
            margin-bottom: 1.5rem;
        }
        
        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-main);
        }

        input[type="text"], 
        input[type="number"], 
        input[type="email"], 
        input[type="password"],
        textarea,
        select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.2s;
            background-color: #ffffff;
            color: var(--text-main);
            box-sizing: border-box;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        
        input[type="file"] {
             padding: 0.5rem;
             background: var(--bg-color);
             border: 1px dashed var(--border);
        }

        .btn-modern {
            background: var(--primary);
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-modern:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(37, 99, 235, 0.3);
        }

        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
        }
        .alert-success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

        /* Mobile Sidebar Toggle Button */
        .sidebar-toggle {
            display: none; /* Hidden on desktop */
            background: none;
            border: none;
            color: var(--text-main);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            margin-right: 1rem;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 90;
            backdrop-filter: blur(2px);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { 
                transform: translateX(-100%); 
                width: var(--sidebar-width); /* Keep width fixed */
                box-shadow: 4px 0 15px rgba(0,0,0,0.5);
            }
            
            .main-content { 
                margin-left: 0; 
                width: 100%; 
            }
            
            .sidebar.open { 
                transform: translateX(0); 
            }
            
            .sidebar-toggle {
                display: block;
            }
            
            .top-header {
                justify-content: space-between; /* Space out toggle and user menu */
                padding: 0 1rem;
            }
        }
    </style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar">
    <div class="logo-area">
        <i class="fas fa-car-side" style="margin-right: 10px;"></i> CPanel
    </div>
    <ul class="nav-menu">

        
        <div style="padding: 1rem 1rem 0.5rem; font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 0.05em;">
            Inventario
        </div>
        <li class="nav-item">
            <a href="index.php" class="<?php echo ($pageTitle == 'Dashboard') ? 'active' : ''; ?>">
                <i class="fas fa-car"></i> Ver Vehículos
            </a>
        </li>
        <li class="nav-item">
            <a href="add_car.php" class="<?php echo ($pageTitle == 'Agregar Auto') ? 'active' : ''; ?>">
                <i class="fas fa-plus-circle"></i> Nuevo Vehículo
            </a>
        </li>

        <div style="padding: 1rem 1rem 0.5rem; font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 0.05em;">
            Contenido Web
        </div>
        <li class="nav-item">
            <a href="settings.php" class="<?php echo ($pageTitle == 'Settings') ? 'active' : ''; ?>">
                <i class="fas fa-sliders-h"></i> Configuración Gral.
            </a>
        </li>
        <li class="nav-item">
            <a href="carousel.php" class="<?php echo ($pageTitle == 'Carrusel') ? 'active' : ''; ?>">
                <i class="fas fa-images"></i> Carruseles
            </a>
        </li>
        <li class="nav-item">
            <a href="navbar.php" class="<?php echo ($pageTitle == 'Navegación') ? 'active' : ''; ?>">
                <i class="fas fa-bars"></i> Menú Navegación
            </a>
        </li>
        <li class="nav-item">
            <a href="categories.php" class="<?php echo ($pageTitle == 'Categorías') ? 'active' : ''; ?>">
                <i class="fas fa-list"></i> Categorías
            </a>
        </li>
        <li class="nav-item">
            <a href="brands.php" class="<?php echo ($pageTitle == 'Marcas') ? 'active' : ''; ?>">
                <i class="fas fa-tag"></i> Marcas
            </a>
        </li>


        <li class="nav-item" style="margin-top: auto; border-top: 1px solid #374151; padding-top: 1rem;">
            <a href="../index.php" target="_blank">
                <i class="fas fa-external-link-alt"></i> Ver Sitio Web
            </a>
        </li>
        <li class="nav-item">
            <a href="logout.php" style="color: #ef4444;">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </li>
    </ul>
</aside>

<main class="main-content">
    <header class="top-header">
        <!-- Sidebar Toggle -->
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Theme Toggle -->
        <button id="themeToggle" style="background:none; border:none; cursor:pointer; font-size:1.2rem; color:var(--text-main); margin-right:1rem; padding:0.5rem; border-radius:50%; transition:all 0.2s;">
            <i class="fas fa-moon"></i>
        </button>
        
        <div class="user-menu">
            <span>Hola, <strong><?php echo htmlspecialchars($_SESSION["username"] ?? 'Admin'); ?></strong></span>
            <div style="width: 35px; height: 35px; background: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                <?php echo strtoupper(substr($_SESSION["username"] ?? 'A', 0, 1)); ?>
            </div>
        </div>
    </header>
    <div class="page-content">

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        }

        if(toggle) {
            toggle.addEventListener('click', toggleSidebar);
        }

        if(overlay) {
            overlay.addEventListener('click', toggleSidebar);
        }
        
        // Theme Toggle Logic
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;
        const icon = themeToggle.querySelector('i');
        
        // Load Preference
        if(localStorage.getItem('theme') === 'dark') {
            body.classList.add('dark-mode');
            icon.classList.replace('fa-moon', 'fa-sun');
        }
        
        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            
            if(body.classList.contains('dark-mode')) {
                localStorage.setItem('theme', 'dark');
                icon.classList.replace('fa-moon', 'fa-sun');
            } else {
                localStorage.setItem('theme', 'light');
                icon.classList.replace('fa-sun', 'fa-moon');
            }
        });
    });
</script>
