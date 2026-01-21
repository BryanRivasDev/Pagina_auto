<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';
checkLoggedIn();

$pageTitle = 'Navegación';

// Handle Bulk Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_changes'])) {
    if (isset($_POST['links']) && is_array($_POST['links'])) {
        foreach ($_POST['links'] as $id => $data) {
            $label = $data['label'];
            $url = $data['url'];
            $order = $data['order_index'];
            // Checkbox: if present '1', else '0'
            $is_visible = isset($data['is_visible']) ? 1 : 0;

            $stmt = $pdo->prepare("UPDATE navbar_links SET label = ?, url = ?, order_index = ?, is_visible = ? WHERE id = ?");
            $stmt->execute([$label, $url, $order, $is_visible, $id]);
        }
    }
    header("Location: navbar.php");
    exit;
}

// Fetch Links
// Auto-repair: Ensure "Inicio" exists
$checkInicio = $pdo->query("SELECT COUNT(*) FROM navbar_links WHERE label = 'Inicio'")->fetchColumn();
if ($checkInicio == 0) {
    // Insert at the beginning
    $pdo->exec("INSERT INTO navbar_links (label, url, order_index, is_visible) VALUES ('Inicio', 'index.php', 0, 1)");
}

// Auto-repair: Rename "Inventario" to "Categorías" to match frontend
$pdo->exec("UPDATE navbar_links SET label = 'Categorías' WHERE label = 'Inventario' OR label = 'Inventory'");

// Auto-repair: Ensure correct URL for Vehículos/Categorías so dropdown works
$pdo->exec("UPDATE navbar_links SET url = 'inventory.php' WHERE label IN ('Vehículos', 'Categorías', 'Autos', 'Inventario')");

// Auto-repair: Ensure "Inicio" has correct URL
$pdo->exec("UPDATE navbar_links SET url = 'index.php' WHERE label IN ('Inicio', 'Home')");

$links = $pdo->query("SELECT * FROM navbar_links ORDER BY order_index ASC")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="section-title">
            <i class="fas fa-bars"></i> Menú de Navegación
        </div>
        
        <div class="alert alert-info" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2); color: #60a5fa;">
            <i class="fas fa-info-circle"></i> Arrastra los elementos para ordenarlos y haz clic en "Guardar Orden".
        </div>

        <form method="post" id="navbarForm">
            <div class="card">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border); text-align: left;">
                            <th style="padding: 1rem; width: 50px;"></th> <!-- Drag Handle -->
                            <th style="padding: 1rem;">Etiqueta</th>
                            <!-- URL Hidden as requested -->
                            <th style="padding: 1rem; text-align: center; width: 100px;">Visible</th>
                        </tr>
                    </thead>
                    <tbody id="sortableList">
                        <?php foreach ($links as $link): ?>
                            <?php $is_visible = isset($link['is_visible']) ? $link['is_visible'] : 1; ?>
                            <tr style="border-bottom: 1px solid var(--border); background: var(--surface);" data-id="<?php echo $link['id']; ?>">
                                <td style="padding: 1rem; cursor: move; text-align: center; color: #6b7280;" class="drag-handle">
                                    <i class="fas fa-grip-vertical"></i>
                                    <input type="hidden" name="links[<?php echo $link['id']; ?>][order_index]" value="<?php echo $link['order_index']; ?>" class="order-input">
                                    <input type="hidden" name="links[<?php echo $link['id']; ?>][url]" value="<?php echo htmlspecialchars($link['url']); ?>">
                                </td>
                                <td style="padding: 1rem;">
                                    <input type="text" name="links[<?php echo $link['id']; ?>][label]" value="<?php echo htmlspecialchars($link['label']); ?>" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); background: var(--bg-color); color: var(--text-main); border-radius: 4px;">
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <label class="switch-visible" style="cursor: pointer; display: flex; align-items: center; justify-content: center;">
                                        <input type="checkbox" name="links[<?php echo $link['id']; ?>][is_visible]" value="1" <?php echo ($is_visible == 1) ? 'checked' : ''; ?> style="display:none;" onchange="updateVisibilityIcon(this)">
                                        <i class="<?php echo ($is_visible == 1) ? 'fas fa-eye' : 'fas fa-eye-slash'; ?>" style="font-size: 1.2rem; color: <?php echo ($is_visible == 1) ? '#10b981' : '#6b7280'; ?>;"></i>
                                    </label>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div style="margin-top: 2rem; text-align: right;">
                    <button type="submit" name="save_changes" class="btn-modern">
                        <i class="fas fa-save" style="margin-right: 8px;"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>

<!-- SortableJS CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    // Visibility Icon Toggle
    function updateVisibilityIcon(checkbox) {
        const icon = checkbox.nextElementSibling;
        if (checkbox.checked) {
            icon.className = 'fas fa-eye';
            icon.style.color = '#10b981';
        } else {
            icon.className = 'fas fa-eye-slash';
            icon.style.color = '#6b7280';
        }
    }

    // Initialize Sortable
    new Sortable(document.getElementById('sortableList'), {
        animation: 150,
        handle: '.drag-handle',
        onEnd: function (evt) {
            // Update hidden order inputs based on new DOM position
            const rows = document.querySelectorAll('#sortableList tr');
            rows.forEach((row, index) => {
                const input = row.querySelector('.order-input');
                if(input) {
                    input.value = index + 1; // 1-based index
                }
            });
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
