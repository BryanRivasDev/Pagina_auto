<?php
$pageTitle = 'Categorías';
require_once 'includes/header.php';
require_once '../config.php';

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name']);
    $image_path = null;

    // Handle Image Upload
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/categories/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_ext;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = "uploads/categories/" . $new_filename;
        }
    }

    if (!empty($name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, image_path) VALUES (:name, :image_path)");
            $stmt->execute([':name' => $name, ':image_path' => $image_path]);
            echo "<script>window.location.href='categories.php';</script>";
            exit;
        } catch (PDOException $e) {
            $error = "Error al agregar categoría (¿Quizás ya existe?)";
        }
    }
}

// Handle Delete Category
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // Optional: Delete image file if exists
    // $stmt = $pdo->prepare("SELECT image_path FROM categories WHERE id = :id");
    // ... unlink file ...
    
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
    $stmt->execute([':id' => $id]);
    echo "<script>window.location.href='categories.php';</script>";
    exit;
}

// Fetch Categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="margin-bottom: 2rem; display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h1 style="color: var(--text-main); margin-bottom: 0.5rem;">Gestión de Categorías</h1>
        <p style="color: var(--text-light); margin: 0;">Agrega o elimina categorías para los vehículos (con íconos).</p>
    </div>
</div>

<div class="card" style="margin-bottom: 2rem;">
    <div class="section-title"><i class="fas fa-plus-circle"></i> Nueva Categoría</div>
    <form action="" method="post" enctype="multipart/form-data" style="display:flex; gap:1rem; align-items:center; flex-wrap: wrap;">
        <input type="hidden" name="action" value="add">
        <input type="text" name="name" placeholder="Nombre (ej. Eléctrico)" class="form-control" required style="flex:1; min-width: 200px;">
        <div style="flex:1; min-width: 200px;">
             <label style="display:block; font-size:0.8rem; margin-bottom:0.2rem; color: #9ca3af;">Ícono/Imagen (Opcional)</label>
             <input type="file" name="image" class="form-control" accept="image/*" style="padding: 0.5rem;">
        </div>
        <button type="submit" class="btn-modern">Agregar</button>
    </form>
    <?php if(isset($error)): ?>
        <p style="color:red; margin-top:0.5rem;"><?php echo $error; ?></p>
    <?php endif; ?>
</div>

<div class="card">
    <div class="section-title"><i class="fas fa-list"></i> Categorías Existentes</div>
    
    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap:1rem;">
        <?php foreach($categories as $cat): ?>
            <div style="background:var(--bg-color); padding:1rem; border-radius:0.5rem; display:flex; flex-direction:column; align-items:center; border:1px solid var(--border); text-align: center;">
                <?php if($cat['image_path']): ?>
                    <img src="../<?php echo htmlspecialchars($cat['image_path']); ?>" alt="icon" style="width: 50px; height: 50px; object-fit: contain; margin-bottom: 0.5rem;">
                <?php else: ?>
                    <div style="width: 50px; height: 50px; background: #374151; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 0.5rem;">
                        <i class="fas fa-car" style="color: #9ca3af;"></i>
                    </div>
                <?php endif; ?>
                <span style="font-weight:600; color:var(--text-main); margin-bottom: 0.5rem;"><?php echo htmlspecialchars($cat['name']); ?></span>
                <a href="categories.php?delete=<?php echo $cat['id']; ?>" onclick="confirmLinkAction(event, this.href, '¿Eliminar categoría?', 'Los vehículos en esta categoría podrían verse afectados.')" style="color:#ef4444; font-size: 0.9rem; text-decoration: none;">
                    <i class="fas fa-trash"></i> Eliminar
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
