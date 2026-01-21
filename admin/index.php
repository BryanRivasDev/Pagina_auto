<?php
$pageTitle = 'Dashboard';
require_once 'includes/header.php';

// Fetch categories for filter
$cats_stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $cats_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch cars with filters
$search = $_GET['search'] ?? '';
$filter_cat = $_GET['category'] ?? '';
$filter_stat = $_GET['status'] ?? '';

$sql = "SELECT * FROM cars WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (make LIKE :s1 OR model LIKE :s2 OR year LIKE :s3)";
    $params[':s1'] = "%$search%";
    $params[':s2'] = "%$search%";
    $params[':s3'] = "%$search%";
}

if ($filter_cat) {
    $sql .= " AND category = :cat";
    $params[':cat'] = $filter_cat;
}

if ($filter_stat) {
    $sql .= " AND status_label = :stat";
    $params[':stat'] = $filter_stat;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
    <div>
        <h1 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($pageTitle); ?></h1>
        <p style="margin:0; color:var(--text-light);">Gestiona el inventario de vehículos de tu sitio web.</p>
    </div>
    <a href="add_car.php" class="btn-modern">
        <i class="fas fa-plus-circle" style="margin-right: 8px;"></i> Agregar Vehículo
    </a>
</div>

<div class="search-container" style="margin-bottom: 2rem;">
    <form method="GET" action="" style="display: flex; gap: 1rem; width: 100%; position: relative; align-items: center; flex-wrap: wrap;">
        
        <!-- Search Input -->
        <div class="search-input-wrapper" style="position: relative; flex: 1; min-width: 250px;">
            <i class="fas fa-search" style="position: absolute; left: 1.2rem; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 1rem; pointer-events: none;"></i>
            <input type="text" name="search" placeholder="Buscar por marca, modelo o año..." value="<?php echo htmlspecialchars($search); ?>" 
                style="width: 100%; padding: 0.8rem 1rem 0.8rem 3rem; border: 1px solid var(--border); border-radius: 9999px; background: var(--surface); color: var(--text-main); outline: none; transition: all 0.3s ease; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); font-size: 0.95rem;">
        </div>

        <!-- Filter: Category -->
        <div class="filter-wrapper" style="position: relative; min-width: 150px;">
            <select name="category" onchange="this.form.submit()" style="width: 100%; padding: 0.8rem 2rem 0.8rem 1rem; border: 1px solid var(--border); border-radius: 9999px; background: var(--surface); color: var(--text-main); outline: none; cursor: pointer; appearance: none; font-size: 0.9rem;">
                <option value="">Todas las Categorías</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo ($filter_cat == $cat['name']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <i class="fas fa-chevron-down" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; font-size: 0.8rem;"></i>
        </div>

        <!-- Filter: Status -->
        <div class="filter-wrapper" style="position: relative; min-width: 150px;">
            <select name="status" onchange="this.form.submit()" style="width: 100%; padding: 0.8rem 2rem 0.8rem 1rem; border: 1px solid var(--border); border-radius: 9999px; background: var(--surface); color: var(--text-main); outline: none; cursor: pointer; appearance: none; font-size: 0.9rem;">
                <option value="">Todos los Estados</option>
                <option value="Nuevo" <?php echo ($filter_stat == 'Nuevo') ? 'selected' : ''; ?>>Nuevo</option>
                <option value="Usado" <?php echo ($filter_stat == 'Usado') ? 'selected' : ''; ?>>Usado</option>
                <option value="Vendido" <?php echo ($filter_stat == 'Vendido') ? 'selected' : ''; ?>>Vendido</option>
            </select>
            <i class="fas fa-chevron-down" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; font-size: 0.8rem;"></i>
        </div>
        
        <!-- Clear Button (Inline) -->
        <?php if($search || $filter_cat || $filter_stat): ?>
            <a href="index.php" class="btn-reset" title="Limpiar filtros">
                <i class="fas fa-times"></i>
            </a>
            <style>
                .btn-reset {
                    width: 42px;
                    height: 42px;
                    min-width: 42px;
                    border-radius: 50%;
                    background: var(--surface);
                    color: var(--text-light);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    text-decoration: none;
                    transition: all 0.2s;
                    border: 1px solid var(--border);
                }
                .btn-reset:hover {
                    background: #ef4444; 
                    border-color: #ef4444;
                    transform: rotate(90deg);
                }
                @media (max-width: 768px) {
                    .btn-reset {
                        width: 100%; /* Full width button on mobile */
                        height: 40px;
                        border-radius: 9999px;
                        margin-top: 0.5rem;
                        background: #7f1d1d; /* Dark Red */
                    }
                    .btn-reset::after {
                        content: " Limpiar Filtros";
                        margin-left: 0.5rem;
                        font-weight: 600;
                        font-size: 0.9rem;
                    }
                }
            </style>
        <?php endif; ?>

        <style>
            input[name="search"]:focus, select:focus {
                background: var(--surface) !important;
                border-color: var(--primary) !important;
                box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1) !important;
            }
            input[name="search"]::placeholder {
                color: #6b7280;
            }

            /* Custom Scrollbar for Select */
            select option {
                background: var(--surface);
                color: var(--text-main);
            }
            
            /* Responsive Mobile Styles */
            @media (max-width: 768px) {
                form {
                    display: flex !important;
                    flex-direction: row !important; /* Allow wrapping */
                    flex-wrap: wrap !important;
                    align-items: stretch !important;
                    gap: 0.5rem !important;
                }
                
                /* Search Input: Full Width */
                .search-input-wrapper {
                    flex: 0 0 100% !important;
                    min-width: 100% !important;
                }
                
                /* Filters: Side by Side (50% - gap) */
                .filter-wrapper {
                    flex: 1 1 calc(50% - 0.25rem) !important;
                    min-width: 0 !important;
                }

                input[name="search"] {
                    font-size: 16px !important; /* Prevent zoom on iOS */
                    padding-left: 3rem !important;
                }
                
                select {
                    font-size: 14px !important;
                    padding-left: 0.75rem !important;
                    padding-right: 1.5rem !important;
                    text-overflow: ellipsis;
                }
            }
        </style>
    </form>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: var(--surface); border-bottom: 1px solid var(--border);">
                    <th style="padding: 1rem 1.5rem; text-align: left; color: var(--text-light); font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Imagen</th>
                    <th style="padding: 1rem 1.5rem; text-align: left; color: var(--text-light); font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Vehículo</th>
                    <th style="padding: 1rem 1.5rem; text-align: left; color: var(--text-light); font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Año</th>
                    <th style="padding: 1rem 1.5rem; text-align: left; color: var(--text-light); font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Precio</th>
                    <th style="padding: 1rem 1.5rem; text-align: left; color: var(--text-light); font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Estado</th>
                    <th style="padding: 1rem 1.5rem; text-align: right; color: var(--text-light); font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($cars) > 0): ?>
                    <?php foreach ($cars as $car): ?>
                        <tr style="border-bottom: 1px solid var(--border); transition: background-color 0.2s;">
                            <td style="padding: 1rem 1.5rem;">
                                <?php if($car['image_path']): ?>
                                    <img src="../<?php echo htmlspecialchars($car['image_path']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                <?php else: ?>
                                    <div style="width:50px; height:50px; background:var(--bg-color); border-radius:0.5rem; display:flex; align-items:center; justify-content:center; color:var(--text-light);">
                                        <i class="fas fa-car"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 1rem 1.5rem;">
                                <div style="font-weight:600; color:var(--text-main); font-size: 1rem;"><?php echo htmlspecialchars($car['make']); ?></div>
                                <div style="color:var(--text-light); font-size:0.875rem;"><?php echo htmlspecialchars($car['model']); ?></div>
                            </td>
                            <td style="padding: 1rem 1.5rem; color: var(--text-main);"><?php echo $car['year']; ?></td>
                            <td style="padding: 1rem 1.5rem;">
                                <?php if($car['show_price']): ?>
                                    <span style="font-weight:700; color:#059669;">C$ <?php echo number_format($car['price']); ?></span>
                                <?php else: ?>
                                    <span style="color:#94a3b8; font-style:italic; font-size: 0.9rem;">Oculto</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 1rem 1.5rem;">
                                <?php if($car['show_price']): ?>
                                    <span style="background-color: #ecfdf5; color: #047857; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; border: 1px solid #a7f3d0;">Visible</span>
                                <?php else: ?>
                                    <span style="background-color: #fffbeb; color: #b45309; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; border: 1px solid #fcd34d;">Consulta</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 1rem 1.5rem; text-align: right;">
                                <a href="edit_car.php?id=<?php echo $car['id']; ?>" style="display: inline-block; padding: 0.5rem; background: var(--bg-color); color: #3b82f6; border-radius: 0.5rem; margin-right: 0.5rem; transition: all 0.2s; border: 1px solid var(--border);" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete_car.php?id=<?php echo $car['id']; ?>" onclick="confirmLinkAction(event, this.href, '¿Eliminar este auto?', 'Se eliminará permanentemente del inventario.')" style="display: inline-block; padding: 0.5rem; background: #fef2f2; color: #ef4444; border-radius: 0.5rem; transition: all 0.2s;" title="Eliminar">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 4rem; color: #64748b;">
                            <div style="margin-bottom: 1rem; width: 64px; height: 64px; background: var(--bg-color); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">
                                <i class="fas fa-car" style="font-size: 2rem; color: var(--text-light);"></i>
                            </div>
                            <h3 style="margin: 0 0 0.5rem 0; color: var(--text-main);">No hay vehículos</h3>
                            <p style="margin: 0; font-size: 0.95rem;">Comienza agregando tu primer auto al inventario.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
