<?php
$pageTitle = 'Editable Car';
require_once 'includes/header.php';

$error = '';
$success = '';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();
$car = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$car) {
    echo "Car not found.";
    exit;
}

// Fetch Gallery
$stmt_g = $pdo->prepare("SELECT * FROM car_images WHERE car_id = :car_id");
$stmt_g->bindParam(':car_id', $id);
$stmt_g->execute();
$gallery_images = $stmt_g->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Basic Fields
    $make = $_POST['make'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $price = $_POST['price'];
    $mileage = $_POST['mileage'];
    $description = $_POST['description'];
    
    // New Fields
    $traction = $_POST['traction'] ?? '';
    $engine_displacement = $_POST['engine_displacement'] ?? '';
    $cylinders = $_POST['cylinders'] ?? 0;
    $fuel_type = $_POST['fuel_type'] ?? '';
    $color_exterior = $_POST['color_exterior'] ?? '';
    $color_interior = $_POST['color_interior'] ?? '';
    $passengers = $_POST['passengers'] ?? 5;
    $doors = $_POST['doors'] ?? 4;
    $steering = $_POST['steering'] ?? '';
    
    // Features (Only keeping displayed ones)
    $feature_electric_windows = isset($_POST['feature_electric_windows']) ? 1 : 0;
    $feature_ac = isset($_POST['feature_ac']) ? 1 : 0;
    
    $show_price = isset($_POST['show_price']) ? 1 : 0;
    $category = trim($_POST['category']);
    $status_label = $_POST['status_label'];
    $is_sold = isset($_POST['is_sold']) ? 1 : 0;

    // Auto-Create Category if not exists
    if (!empty($category)) {
        $stmt_check = $pdo->prepare("SELECT id FROM categories WHERE name = :name");
        $stmt_check->bindParam(':name', $category);
        $stmt_check->execute();
        if ($stmt_check->rowCount() == 0) {
            $stmt_new = $pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
            $stmt_new->bindParam(':name', $category);
            $stmt_new->execute();
        }
    }

    // Image Upload Handling
    $image_path = $car['image_path'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        if (in_array(strtolower($filetype), $allowed)) {
            $new_filename = uniqid() . "." . $filetype;
            $upload_dir = "../assets/images/";
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_filename)) {
                $image_path = "assets/images/" . $new_filename;
            } else {
                $error = "Failed to upload image.";
            }
        }
    }

    if (empty($error)) {
        // Updated Update SQL to include doors and steering and remove unused
        $sql = "UPDATE cars SET make=:make, model=:model, year=:year, price=:price, mileage=:mileage, description=:description, image_path=:image_path, show_price=:show_price, category=:category, status_label=:status_label, is_sold=:is_sold,
                traction=:traction, engine_displacement=:engine_displacement, cylinders=:cylinders, fuel_type=:fuel_type, color_exterior=:color_exterior, color_interior=:color_interior, passengers=:passengers, doors=:doors, steering=:steering,
                feature_electric_windows=:feature_electric_windows, feature_ac=:feature_ac
                WHERE id=:id";
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(':make', $make);
            $stmt->bindParam(':model', $model);
            $stmt->bindParam(':year', $year);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':mileage', $mileage);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':image_path', $image_path);
            $stmt->bindParam(':show_price', $show_price);
            $stmt->bindParam(':category', $category);
            $stmt->bindParam(':status_label', $status_label);
            $stmt->bindParam(':is_sold', $is_sold);
            
            // New Params Bind
            $stmt->bindParam(':traction', $traction);
            $stmt->bindParam(':engine_displacement', $engine_displacement);
            $stmt->bindParam(':cylinders', $cylinders);
            $stmt->bindParam(':fuel_type', $fuel_type);
            $stmt->bindParam(':color_exterior', $color_exterior);
            $stmt->bindParam(':color_interior', $color_interior);
            $stmt->bindParam(':passengers', $passengers);
            $stmt->bindParam(':doors', $doors);
            $stmt->bindParam(':steering', $steering);
            
            $stmt->bindParam(':feature_electric_windows', $feature_electric_windows);
            $stmt->bindParam(':feature_ac', $feature_ac);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                // Handle Gallery Uploads
                if (isset($_FILES['gallery']) && count($_FILES['gallery']['name']) > 0) {
                    $total = count($_FILES['gallery']['name']);
                    for ($i = 0; $i < $total; $i++) {
                        if ($_FILES['gallery']['error'][$i] == 0) {
                            $g_filename = $_FILES['gallery']['name'][$i];
                            $g_filetype = pathinfo($g_filename, PATHINFO_EXTENSION);
                            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                            if (in_array(strtolower($g_filetype), $allowed)) {
                                $g_newname = uniqid() . "_gallery." . $g_filetype;
                                if (move_uploaded_file($_FILES['gallery']['tmp_name'][$i], "../assets/images/" . $g_newname)) {
                                    $g_path = "assets/images/" . $g_newname;
                                    $stmt_g = $pdo->prepare("INSERT INTO car_images (car_id, image_path) VALUES (:car_id, :image_path)");
                                    $stmt_g->bindParam(':car_id', $id);
                                    $stmt_g->bindParam(':image_path', $g_path);
                                    $stmt_g->execute();
                                }
                            }
                        }
                    }
                }
                
                $success = "Car updated successfully!";
                // Refresh data
                $stmt->execute(); 
                $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $car = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Refresh Gallery
                $stmt_g = $pdo->prepare("SELECT * FROM car_images WHERE car_id = :car_id");
                $stmt_g->bindParam(':car_id', $id);
                $stmt_g->execute();
                $gallery_images = $stmt_g->fetchAll(PDO::FETCH_ASSOC);
                
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}

// Handle Image Deletion
if (isset($_GET['delete_image'])) {
    $img_id = $_GET['delete_image'];
    $stmt_del = $pdo->prepare("SELECT image_path FROM car_images WHERE id = :id");
    $stmt_del->bindParam(':id', $img_id);
    $stmt_del->execute();
    $img_del = $stmt_del->fetch(PDO::FETCH_ASSOC);
    if($img_del){
        unlink("../" . $img_del['image_path']);
        $pdo->query("DELETE FROM car_images WHERE id = $img_id");
        header("Location: edit_car.php?id=$id");
        exit;
    }
}
?>

<div style="background: var(--surface); padding: 2rem; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.5); max-width: 800px; margin: 0 auto; color: var(--text-main);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
        <h1 style="margin:0; font-size:1.5rem;">Editar Vehículo</h1>
        <div>
            <a href="index.php" style="color:#2563eb; text-decoration:none; margin-right: 1rem;"><i class="fas fa-arrow-left"></i> Volver</a>
            <a href="../car_details.php?id=<?php echo $car['id']; ?>" target="_blank" style="color:#10b981; text-decoration:none;"><i class="fas fa-eye"></i> Ver en Web</a>
        </div>
    </div>

    <?php if($error): ?>
        <div style="background:#fee2e2; color:#991b1b; padding:1rem; border-radius:0.5rem; margin-bottom:1rem;"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div style="background:#dcfce7; color:#166534; padding:1rem; border-radius:0.5rem; margin-bottom:1rem;"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <!-- Left Column: Basic Info -->
            <div>
                <h3 style="margin-top: 0; margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Datos Generales</h3>
                
                <div class="form-group">
                    <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Marca</label>
                    <select name="make" required style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
                        <?php 
                        $stmt_brand = $pdo->query("SELECT name FROM brands ORDER BY name ASC");
                        while($row_brand = $stmt_brand->fetch(PDO::FETCH_ASSOC)) {
                            $selected = ($car['make'] == $row_brand['name']) ? 'selected' : '';
                            echo '<option value="'.htmlspecialchars($row_brand['name']).'" '.$selected.'>'.htmlspecialchars($row_brand['name']).'</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Modelo</label>
                    <input type="text" name="model" value="<?php echo htmlspecialchars($car['model']); ?>" required style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
                </div>
                
                <div class="form-group">
                    <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Categoría</label>
                    <select name="category" required style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
                        <?php 
                        $stmt_cat = $pdo->query("SELECT name FROM categories ORDER BY name ASC");
                        while($row_cat = $stmt_cat->fetch(PDO::FETCH_ASSOC)) {
                            $selected = ($car['category'] == $row_cat['name']) ? 'selected' : '';
                            echo '<option value="'.htmlspecialchars($row_cat['name']).'" '.$selected.'>'.htmlspecialchars($row_cat['name']).'</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Etiqueta</label>
                    <select name="status_label" style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
                        <option value="">Ninguna</option>
                        <option value="Nuevo" <?php echo ($car['status_label'] == 'Nuevo') ? 'selected' : ''; ?>>Nuevo</option>
                        <option value="Oferta" <?php echo ($car['status_label'] == 'Oferta') ? 'selected' : ''; ?>>Oferta</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Año</label>
                    <input type="number" name="year" value="<?php echo htmlspecialchars($car['year']); ?>" required style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
                </div>
                <div class="form-group">
                    <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Kilometraje (KM)</label>
                    <input type="number" name="mileage" value="<?php echo htmlspecialchars($car['mileage']); ?>" style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
                </div>
                <div class="form-group">
                    <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Precio</label>
                    <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($car['price']); ?>" required style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
                </div>
                <div class="form-group" style="display:flex; align-items:center; margin-top: 2rem;">
                    <input type="checkbox" name="show_price" id="show_price" <?php echo ($car['show_price']) ? 'checked' : ''; ?> style="width:20px; height:20px; margin:0 0.5rem 0 0; cursor:pointer;">
                    <label for="show_price" style="margin:0; cursor:pointer;">¿Mostrar Precio en la Web?</label>
                </div>
                
                 <div class="form-group" style="display:flex; align-items:center; margin-top: 1rem;">
                    <input type="checkbox" name="is_sold" id="is_sold" <?php echo ($car['is_sold']) ? 'checked' : ''; ?> style="width:20px; height:20px; margin:0 0.5rem 0 0; cursor:pointer;">
                    <label for="is_sold" style="color:#ef4444; font-weight:700; margin:0; cursor:pointer;">¿Marcar como VENDIDO?</label>
                </div>
            </div>

            <!-- Right Column: Specs & Features -->
            <div>
                 <h3 style="margin-top: 0; margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Especificaciones</h3>
                 
                 <div class="form-group">
                    <label>Color Exterior</label>
                    <input type="text" name="color_exterior" value="<?php echo htmlspecialchars($car['color_exterior'] ?? ''); ?>" placeholder="Ej: Negro" style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
                </div>
                <div class="form-group">
                    <label>Color Interior</label>
                    <input type="text" name="color_interior" value="<?php echo htmlspecialchars($car['color_interior'] ?? ''); ?>" placeholder="Ej: Blanco" style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
                </div>

                <div class="form-group">
                    <label>Transmisión (4x2, 4x4)</label>
                    <input type="text" name="traction" value="<?php echo htmlspecialchars($car['traction'] ?? ''); ?>" placeholder="Ej: 4x2" style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
                </div>
                 <div class="form-group">
                    <label>Dirección</label>
                    <input type="text" name="steering" value="<?php echo htmlspecialchars($car['steering'] ?? ''); ?>" placeholder="Ej: Hidráulica" style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
                </div>

                <div class="form-group">
                    <label>Motor</label>
                    <input type="text" name="engine_displacement" value="<?php echo htmlspecialchars($car['engine_displacement'] ?? ''); ?>" placeholder="Ej: 2.0 CC" style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
                </div>
                
                 <div class="form-group">
                    <label>Cilindros</label>
                    <input type="number" name="cylinders" value="<?php echo htmlspecialchars($car['cylinders'] ?? ''); ?>" placeholder="Ej: 4" style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
                </div>

                <div class="form-group">
                    <label>Combustible</label>
                    <select name="fuel_type" style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
                        <option value="">Seleccione...</option>
                        <option value="Gasolina" <?php echo ($car['fuel_type'] == 'Gasolina') ? 'selected' : ''; ?>>Gasolina</option>
                        <option value="Diesel" <?php echo ($car['fuel_type'] == 'Diesel') ? 'selected' : ''; ?>>Diesel</option>
                        <option value="Híbrido" <?php echo ($car['fuel_type'] == 'Híbrido') ? 'selected' : ''; ?>>Híbrido</option>
                        <option value="Eléctrico" <?php echo ($car['fuel_type'] == 'Eléctrico') ? 'selected' : ''; ?>>Eléctrico</option>
                    </select>
                </div>
                
                 <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Pasajeros</label>
                        <input type="number" name="passengers" value="<?php echo htmlspecialchars($car['passengers'] ?? 5); ?>" placeholder="Ej: 5" style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
                    </div>
                     <div class="form-group">
                        <label>Puertas</label>
                        <input type="number" name="doors" value="<?php echo htmlspecialchars($car['doors'] ?? 4); ?>" placeholder="Ej: 4" style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
                    </div>
                </div>

                <h3 style="margin-top: 1rem; margin-bottom: 0.5rem; font-size: 1rem;">Extras</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                     <label style="display:flex; align-items:center; gap:0.5rem;">
                        <input type="checkbox" name="feature_ac" value="1" <?php echo ($car['feature_ac']) ? 'checked' : ''; ?>> Aire acondicionado
                    </label>
                    <label style="display:flex; align-items:center; gap:0.5rem;">
                        <input type="checkbox" name="feature_electric_windows" value="1" <?php echo ($car['feature_electric_windows']) ? 'checked' : ''; ?>> Vidrios eléctricos
                    </label>
                </div>

            </div>
        </div>

        <div class="form-group" style="margin-top:1.5rem;">
            <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Descripción</label>
            <textarea name="description" rows="4" style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; font-family:sans-serif; background: var(--bg-color); color: var(--text-main);"><?php echo htmlspecialchars($car['description']); ?></textarea>
        </div>
        
        <div class="form-group" style="margin-top:1.5rem;">
            <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Foto Principal (Dejar vacío para mantener actual)</label>
            <div style="display:flex; gap:1rem; align-items:center;">
                <img src="../<?php echo htmlspecialchars($car['image_path']); ?>" style="width:100px; height:60px; object-fit:cover; border-radius:0.5rem;">
                <input type="file" name="image" accept="image/*" style="width:100%;">
            </div>
        </div>

        <div class="form-group" style="margin-top:1.5rem;">
            <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Galería de Imágenes</label>
            <!-- Existing Gallery -->
            <div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-bottom:0.5rem;">
                <?php foreach($gallery_images as $img): ?>
                    <div style="position:relative;">
                        <img src="../<?php echo htmlspecialchars($img['image_path']); ?>" style="width:80px; height:60px; object-fit:cover; border-radius:0.5rem;">
                        <a href="?id=<?php echo $id; ?>&delete_image=<?php echo $img['id']; ?>" onclick="confirmLinkAction(event, this.href, '¿Eliminar imagen?', 'Esta acción no se puede deshacer.')" style="position:absolute; top:-5px; right:-5px; background:red; color:white; border-radius:50%; width:20px; height:20px; text-align:center; line-height:20px; font-size:12px; text-decoration:none;">&times;</a>
                    </div>
                <?php endforeach; ?>
            </div>
            <input type="file" name="gallery[]" accept="image/*" multiple style="width:100%;">
            <small style="color:#6b7280;">Añadir nuevas imágenes</small>
        </div>
        
        <div style="margin-top:2rem; text-align:right;">
             <button type="submit" style="background-color:#2563eb; color:white; padding:0.75rem 2rem; border:none; border-radius:0.5rem; font-weight:600; cursor:pointer;">Guardar Cambios</button>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
