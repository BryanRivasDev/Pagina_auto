<?php
require_once 'config.php';

// Initialize Filter Variables
$search = $_GET['search'] ?? '';
$brand_filter = $_GET['brand'] ?? [];
$category_filter = $_GET['category'] ?? [];
$price_min = $_GET['price_min'] ?? '';
$price_max = $_GET['price_max'] ?? '';
$year_min = $_GET['year_min'] ?? '';
$year_max = $_GET['year_max'] ?? '';
$mileage_min = $_GET['mileage_min'] ?? '';
$mileage_max = $_GET['mileage_max'] ?? '';
$traction = $_GET['traction'] ?? '';
$fuel_type = $_GET['fuel_type'] ?? '';
$status = $_GET['status'] ?? '';

// Build Query
$sql = "SELECT * FROM cars WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (make LIKE :search OR model LIKE :search OR description LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($brand_filter)) {
    // Check if array or single string
    if (is_array($brand_filter)) {
        $placeholders = implode(',', array_fill(0, count($brand_filter), '?'));
        // For array binding in PDO we need to be careful, simpler to loop or use indexed params if handled manually.
        // Let's stick to a simpler approach for array filters or handle multiple execution.
        // Actually, let's just use string building for IN clause if safe (strictly binding is better).
        // For simplicity in this generated file, we will handle single selection or array logic carefully.
        // To be safe and robust:
        $in_clauses = [];
        foreach ($brand_filter as $key => $brand) {
            $param_name = ":brand_$key";
            $in_clauses[] = $param_name;
            $params[$param_name] = $brand;
        }
        $sql .= " AND make IN (" . implode(',', $in_clauses) . ")";
    } else {
         $sql .= " AND make = :brand";
         $params[':brand'] = $brand_filter;
    }
}

if (!empty($category_filter)) {
    if (is_array($category_filter)) {
        $in_clauses = [];
        foreach ($category_filter as $key => $cat) {
            $param_name = ":cat_$key";
            $in_clauses[] = $param_name;
            $params[$param_name] = $cat;
        }
        $sql .= " AND category IN (" . implode(',', $in_clauses) . ")";
    } else {
        $sql .= " AND category = :category";
        $params[':category'] = $category_filter;
    }
}

if (!empty($price_min)) {
    $sql .= " AND price >= :price_min";
    $params[':price_min'] = $price_min;
}
if (!empty($price_max)) {
    $sql .= " AND price <= :price_max";
    $params[':price_max'] = $price_max;
}

if (!empty($year_min)) {
    $sql .= " AND year >= :year_min";
    $params[':year_min'] = $year_min;
}
if (!empty($year_max)) {
    $sql .= " AND year <= :year_max";
    $params[':year_max'] = $year_max;
}

if (!empty($mileage_min)) {
    $sql .= " AND mileage >= :mileage_min";
    $params[':mileage_min'] = $mileage_min;
}
if (!empty($mileage_max)) {
    $sql .= " AND mileage <= :mileage_max";
    $params[':mileage_max'] = $mileage_max;
}

if (!empty($traction)) {
    $sql .= " AND traction = :traction";
    $params[':traction'] = $traction;
}
if (!empty($fuel_type)) {
    $sql .= " AND fuel_type = :fuel_type";
    $params[':fuel_type'] = $fuel_type;
}

if (!empty($status)) {
    $sql .= " AND status_label = :status";
    $params[':status'] = $status;
}

// Order
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Data for Filters
$brands = $pdo->query("SELECT DISTINCT name FROM brands ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);
$categories = $pdo->query("SELECT DISTINCT name FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);
$years = $pdo->query("SELECT DISTINCT year FROM cars ORDER BY year DESC")->fetchAll(PDO::FETCH_COLUMN);

// Filter Settings
$stmt = $pdo->query("SELECT * FROM site_settings");
$settings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$site_name = $settings['site_name'] ?? 'Venta de Autos';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario - <?php echo htmlspecialchars($site_name); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .catalog-container {
            max-width: 1600px; /* Wider to fit 4 cols comfortably */
            margin: 0 auto;
            padding: 2rem;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2.5rem;
            min-height: 80vh;
        }

        /* Modern Filter Sidebar */
        .sidebar-filters {
            background: #111827; /* Darker, cleaner */
            padding: 2rem;
            border-radius: 1rem;
            align-self: start;
            position: sticky;
            top: 8rem;
            border: 1px solid #374151;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            max-height: calc(100vh - 120px); /* Restore max-height */
            overflow-y: auto; 
            scrollbar-width: thin; 
            scrollbar-color: #4b5563 transparent; /* Visible by default on Firefox to test */
        }
        
        /* Modern Scrollbar - Webkit */
        .filter-sidebar::-webkit-scrollbar { 
            width: 8px; /* Slightly wider */
        }
        
        .filter-sidebar::-webkit-scrollbar-track { 
            background: rgba(0,0,0,0.1); 
        }
        
        .filter-sidebar::-webkit-scrollbar-thumb { 
            background-color: #6b7280; /* Visible Gray */
            border-radius: 4px;
        }

        .filter-sidebar:hover::-webkit-scrollbar-thumb {
            background-color: #4b5563; /* Darker on hover */
        }
        
        .filter-sidebar:hover {
            scrollbar-color: #4b5563 transparent; /* Firefox: show on hover */
        }
        
        /* Modern Scrollbar - Webkit */
        .filter-sidebar::-webkit-scrollbar { 
            width: 6px; 
        }
        
        .filter-sidebar::-webkit-scrollbar-track { 
            background: transparent; 
        }
        
        .filter-sidebar::-webkit-scrollbar-thumb { 
            background-color: transparent; /* Hidden by default */
            border-radius: 3px;
        }
        
        .filter-sidebar:hover::-webkit-scrollbar-thumb {
            background-color: #4b5563; /* Show on hover */
        }

        .filter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #374151;
        }

        .filter-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
            letter-spacing: -0.5px;
        }
        
        .reset-link {
            font-size: 0.85rem;
            color: #60a5fa;
            font-weight: 500;
            text-decoration: none;
            transition: color 0.2s;
        }
        .reset-link:hover { color: #93c5fd; }

        .filter-group { margin-bottom: 2rem; }
        
        .filter-title {
            font-weight: 600;
            color: #e5e7eb;
            margin-bottom: 0.75rem;
            display: block;
            font-size: 0.95rem;
            letter-spacing: 0.025em;
        }

        /* Modern Inputs */
        .search-input, select.search-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid #374151;
            background: #1f2937;
            color: white;
            transition: all 0.2s;
            font-size: 0.95rem;
            box-sizing: border-box; /* Ensure padding doesn't affect width */
        }
        
        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
            background: #374151;
        }

        .range-inputs {
            display: flex;
            gap: 0.75rem;
        }

        .range-inputs input {
            width: 50%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid #374151;
            background: #1f2937;
            color: white;
            font-size: 0.9rem;
            text-align: center;
            transition: all 0.2s;
        }
        
        .range-inputs input:focus {
            outline: none;
            border-color: #3b82f6;
            background: #374151;
        }

        /* Custom Checkboxes */
        .checkbox-list {
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            max-height: 240px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }
        
        /* Custom Scrollbar */
        .checkbox-list::-webkit-scrollbar { width: 6px; }
        .checkbox-list::-webkit-scrollbar-track { background: #1f2937; border-radius: 3px; }
        .checkbox-list::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 3px; }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #d1d5db; /* Lighter text */
            font-size: 0.95rem;
            cursor: pointer;
            padding: 0.25rem 0;
            transition: color 0.2s;
        }
        
        .checkbox-item:hover { color: white; }
        
        .checkbox-item input[type="checkbox"], .checkbox-item input[type="radio"] {
            accent-color: #3b82f6;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .results-header {
            display: inline-flex; /* Fit content */
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            color: #94a3b8;
            font-size: 1rem;
            background: #1e293b;
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            border: 1px solid #374151;
        }

        /* 4-COLUMN GRID */
        .results-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr); /* Forced 4 columns */
            gap: 1.5rem;
        }

        /* Card refinements to match carousel */
        .car-card {
            background: #ffffff;
            border-radius: 2rem; /* Much rounder */
            overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        @media (max-width: 1400px) {
            .results-grid { grid-template-columns: repeat(3, 1fr); }
            .catalog-container { max-width: 100%; padding: 1.5rem; }
        }

        @media (max-width: 1100px) {
             .results-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 900px) {
            .catalog-container { grid-template-columns: 1fr; }
            .sidebar-filters { position: static; margin-bottom: 2rem; }
        }
        
        @media (max-width: 600px) {
            .results-grid { grid-template-columns: 1fr; }
        }

        /* New Header Section Styles */
        .inventory-header {
            text-align: center;
            margin-top: 3rem;
            margin-bottom: 2rem;
            padding: 0 1rem;
        }
        .inventory-header h1 {
            font-size: 3.5rem;
            font-weight: 800;
            color: #ffffff; /* Using white for dark theme contrast */
            margin: 0 0 0.5rem;
            letter-spacing: -1.5px;
            line-height: 1.1;
        }
        .inventory-header p {
            font-size: 1.1rem;
            color: #9ca3af; /* Slate 400 */
            max-width: 900px;
            margin: 0 auto;
            font-weight: 400;
        }
    </style>
</head>
<body>

    <?php include 'includes/navbar_frontend.php'; ?>

    <div class="inventory-header">
        <h1>Catálogo de Vehículos</h1>
        <p>Explorá nuestro inventario en línea y encontrá el vehículo perfecto para vos</p>
    </div>

    <div class="catalog-container">
        <!-- Sidebar Filters -->
        <aside class="sidebar-filters">
            <form action="inventory.php" method="GET" id="filterForm">
                <div class="filter-header">
                    <h2>Filtros</h2>
                    <a href="inventory.php" class="reset-link">Restablecer todo</a>
                </div>

                <div class="filter-group">
                    <label class="filter-title">Buscar</label>
                    <input type="text" name="search" class="search-input" placeholder="Ej. Hilux, Corolla..." value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <div class="filter-group">
                    <select name="brand" class="search-input">
                        <option value="">Marca</option>
                        <?php foreach($brands as $brand): ?>
                            <option value="<?php echo htmlspecialchars($brand); ?>" <?php echo ($brand_filter == $brand) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($brand); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <select name="category" class="search-input">
                        <option value="">Categoría</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($category_filter == $cat) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <select name="traction" class="search-input">
                        <option value="">Tracción</option>
                        <option value="4x2" <?php if($traction == '4x2') echo 'selected'; ?>>4x2</option>
                        <option value="4x4" <?php if($traction == '4x4') echo 'selected'; ?>>4x4</option>
                        <option value="AWD" <?php if($traction == 'AWD') echo 'selected'; ?>>AWD</option>
                    </select>
                </div>

                <div class="filter-group">
                    <select name="fuel_type" class="search-input">
                        <option value="">Combustible</option>
                        <option value="Gasolina" <?php if($fuel_type == 'Gasolina') echo 'selected'; ?>>Gasolina</option>
                        <option value="Diesel" <?php if($fuel_type == 'Diesel') echo 'selected'; ?>>Diesel</option>
                        <option value="Híbrido" <?php if($fuel_type == 'Híbrido') echo 'selected'; ?>>Híbrido</option>
                        <option value="Eléctrico" <?php if($fuel_type == 'Eléctrico') echo 'selected'; ?>>Eléctrico</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-title">Precio (C$)</label>
                    <div class="range-inputs">
                        <input type="number" name="price_min" placeholder="Mín" value="<?php echo htmlspecialchars($price_min); ?>">
                        <input type="number" name="price_max" placeholder="Máx" value="<?php echo htmlspecialchars($price_max); ?>">
                    </div>
                </div>

                 <div class="filter-group">
                    <label class="filter-title">Año</label>
                    <div class="range-inputs">
                        <input type="number" name="year_min" placeholder="Desde" value="<?php echo htmlspecialchars($year_min); ?>">
                        <input type="number" name="year_max" placeholder="Hasta" value="<?php echo htmlspecialchars($year_max); ?>">
                    </div>
                </div>

                 <div class="filter-group">
                    <label class="filter-title">Kilometraje (km)</label>
                    <div class="range-inputs">
                        <input type="number" name="mileage_min" placeholder="Mín" value="<?php echo htmlspecialchars($mileage_min); ?>">
                        <input type="number" name="mileage_max" placeholder="Máx" value="<?php echo htmlspecialchars($mileage_max); ?>">
                    </div>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 1rem;">Aplicar Filtros</button>
            </form>
        </aside>

        <!-- Main Content -->
        <main>
            <div class="results-header">
                <span>Mostrando <strong><?php echo count($cars); ?></strong> vehículos</span>
                <!-- Could add Sort By dropdown here later -->
            </div>

            <?php if(count($cars) > 0): ?>
                <div class="results-grid">
                    <?php foreach($cars as $car): ?>
                        <div class="car-card" onclick="location.href='car_details.php?id=<?php echo $car['id']; ?>'" style="cursor: pointer;">
                            <div class="car-image-container">
                                <?php if(!empty($car['status_label'])): ?>
                                    <div class="badge-top-right <?php echo ($car['status_label'] == 'Nuevo') ? 'badge-new' : 'badge-promo'; ?>" 
                                         style="background-color: <?php echo ($car['status_label'] == 'Nuevo') ? '#10b981' : '#ef4444'; ?>;">
                                        <?php echo htmlspecialchars($car['status_label']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <img src="<?php echo htmlspecialchars($car['image_path']); ?>" alt="Car" class="car-image">
                            </div>
                            
                            <div class="car-details">
                                <h3 class="car-title"><?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?></h3>
                                <div class="car-subtitle">
                                    <span><?php echo $car['year']; ?></span>
                                    <span>•</span>
                                    <span><?php echo number_format($car['mileage']); ?> km</span>
                                </div>
                                <div class="car-divider"></div>
                                <div style="text-align: center; margin-top: auto;">
                                    <div class="price-value" style="margin-bottom: 0.8rem;">C$<?php echo number_format($car['price']); ?></div>
                                    <a href="car_details.php?id=<?php echo $car['id']; ?>" class="btn-details" style="display: inline-block; width: 80%; text-align: center;">Ver Detalles</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 4rem; background: #1e293b; border-radius: 1rem; color: #94a3b8;">
                    <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <h3>No se encontraron vehículos</h3>
                    <p>Intenta ajustar tus filtros de búsqueda.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <?php include 'includes/footer_frontend.php'; ?>

</body>
</html>
