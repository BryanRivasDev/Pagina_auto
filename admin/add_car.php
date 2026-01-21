<?php
$pageTitle = 'Add Car';
require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $make = $_POST['make'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $price = $_POST['price'];
    $mileage = $_POST['mileage'];
    $description = $_POST['description'];
    
    // New Fields matching car_details.php
    $traction = $_POST['traction'] ?? ''; // used for Transmisión
    $engine_displacement = $_POST['engine_displacement'] ?? ''; // used for Motor
    $cylinders = $_POST['cylinders'] ?? 0;
    $fuel_type = $_POST['fuel_type'] ?? '';
    $color_exterior = $_POST['color_exterior'] ?? '';
    $color_interior = $_POST['color_interior'] ?? '';
    $passengers = $_POST['passengers'] ?? 5;
    $doors = $_POST['doors'] ?? 4; // NEW
    $steering = $_POST['steering'] ?? ''; // NEW
    
    // Features (Only keeping what is shown)
    $feature_electric_windows = isset($_POST['feature_electric_windows']) ? 1 : 0;
    $feature_ac = isset($_POST['feature_ac']) ? 1 : 0;
    
    // Removed unused features/fields

    $show_price = isset($_POST['show_price']) ? 1 : 0;
    
    // Image Upload Handling
    $image_path = '';
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
        } else {
            $error = "Invalid file type. Only JPG, PNG, WEBP allowed.";
        }
    }

    $category = trim($_POST['category']); // Trim whitespace
    $status_label = $_POST['status_label'];

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

    if (empty($error)) {
        // Updated INSERT without unused fields
        $sql = "INSERT INTO cars (make, model, year, price, mileage, description, image_path, show_price, category, status_label, 
                traction, engine_displacement, cylinders, fuel_type, color_exterior, color_interior, passengers, doors, steering,
                feature_electric_windows, feature_ac) 
                VALUES (:make, :model, :year, :price, :mileage, :description, :image_path, :show_price, :category, :status_label,
                :traction, :engine_displacement, :cylinders, :fuel_type, :color_exterior, :color_interior, :passengers, :doors, :steering,
                :feature_electric_windows, :feature_ac)";
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

            // Bind New Params
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
            
            if ($stmt->execute()) {
                $car_id = $pdo->lastInsertId();

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
                                    $stmt_g->bindParam(':car_id', $car_id);
                                    $stmt_g->bindParam(':image_path', $g_path);
                                    $stmt_g->execute();
                                }
                            }
                        }
                    }
                }

                echo "<script>window.location.href='index.php';</script>";
                exit;
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>

<div style="background: var(--surface); padding: 2rem; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.5); max-width: 800px; margin: 0 auto; color: var(--text-main);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
        <h1 style="margin:0; font-size:1.5rem;">Agregar Nuevo Vehículo</h1>
        <a href="index.php" style="color:#2563eb; text-decoration:none;"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>

    <?php if($error): ?>
        <div style="background:#fee2e2; color:#991b1b; padding:1rem; border-radius:0.5rem; margin-bottom:1rem;"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Marca</label>
                <select name="make" required style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
                    <option value="">Seleccione una marca...</option>
                    <?php 
                    $stmt_brand = $pdo->query("SELECT name FROM brands ORDER BY name ASC");
                    while($row_brand = $stmt_brand->fetch(PDO::FETCH_ASSOC)) {
                        echo '<option value="'.htmlspecialchars($row_brand['name']).'">'.htmlspecialchars($row_brand['name']).'</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Modelo</label>
                <input type="text" name="model" placeholder="e.g. Corolla" required style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
            </div>
            <div class="form-group">
                <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Categoría</label>
                <select name="category" required style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
                    <option value="">Seleccione una categoría...</option>
                    <?php 
                    $stmt_cat = $pdo->query("SELECT name FROM categories ORDER BY name ASC");
                    while($row_cat = $stmt_cat->fetch(PDO::FETCH_ASSOC)) {
                        echo '<option value="'.htmlspecialchars($row_cat['name']).'">'.htmlspecialchars($row_cat['name']).'</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Etiqueta (Opcional)</label>
                <select name="status_label" style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
                    <option value="">Ninguna</option>
                    <option value="Nuevo">Nuevo</option>
                    <option value="Oferta">Oferta</option>
                </select>
            </div>
            <div class="form-group">
                <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Año</label>
                <input type="number" name="year" value="<?php echo date("Y"); ?>" required style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
            </div>
            <div class="form-group">
                <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Kilometraje (KM)</label>
                <input type="number" name="mileage" placeholder="0" style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
            </div>
            <div class="form-group">
                <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Precio</label>
                <input type="number" step="0.01" name="price" placeholder="0.00" required style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
            </div>
            <div class="form-group" style="display:flex; align-items:center; margin-top: 2.1rem;">
                <input type="checkbox" name="show_price" id="show_price" checked style="width:20px; height:20px; margin:0 0.5rem 0 0; cursor:pointer;">
                <label for="show_price" style="margin:0; cursor:pointer;">¿Mostrar Precio en la Web?</label>
            </div>
        </div>

        <!-- Basic Info Section -->
        <h3 style="margin-top: 2rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Especificaciones</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            
            <div class="form-group">
                <label>Color Exterior</label>
                <input type="text" name="color_exterior" placeholder="Ej: Negro" style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
            </div>
            <div class="form-group">
                <label>Color Interior</label>
                <input type="text" name="color_interior" placeholder="Ej: Blanco" style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
            </div>

            <div class="form-group">
                <label>Transmisión (4x2, 4x4, etc)</label>
                <input type="text" name="traction" placeholder="Ej: 4x2" style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
            </div>
            <div class="form-group">
                <label>Dirección</label>
                <input type="text" name="steering" placeholder="Ej: Hidráulica" style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
            </div>

            <div class="form-group">
                <label>Motor (ej. 2.0)</label>
                <input type="text" name="engine_displacement" placeholder="Ej: 2.0 CC" style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
            </div>
            <div class="form-group">
                <label>Cilindros</label>
                <input type="number" name="cylinders" placeholder="Ej: 4" style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
            </div>
            
            <div class="form-group">
                <label>Combustible</label>
                <select name="fuel_type" style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
                    <option value="">Seleccione...</option>
                    <option value="Gasolina">Gasolina</option>
                    <option value="Diesel">Diesel</option>
                    <option value="Híbrido">Híbrido</option>
                    <option value="Eléctrico">Eléctrico</option>
                </select>
            </div>
            <div class="form-group">
                <label>Pasajeros</label>
                <input type="number" name="passengers" placeholder="Ej: 5" style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
            </div>
            <div class="form-group">
                <label>Puertas</label>
                <input type="number" name="doors" placeholder="Ej: 4" style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; background: var(--bg-color); color: var(--text-main);">
            </div>
        </div>

        <!-- Features Section -->
        <h3 style="margin-top: 2rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Extras</h3>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
            <label style="display:flex; align-items:center; gap:0.5rem;">
                <input type="checkbox" name="feature_ac" value="1"> Aire acondicionado
            </label>
            <label style="display:flex; align-items:center; gap:0.5rem;">
                <input type="checkbox" name="feature_electric_windows" value="1"> Vidrios eléctricos
            </label>
        </div>
        
        <div class="form-group" style="margin-top:1.5rem;">
            <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Descripción</label>
            <textarea name="description" rows="4" style="width:100%; padding:0.75rem; border:1px solid var(--border); border-radius:0.5rem; font-family:sans-serif; background: var(--bg-color); color: var(--text-main);"></textarea>
        </div>
        
        <div class="form-group" style="margin-top:1.5rem;">
            <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Foto Principal</label>
            <input type="file" name="image" accept="image/*" style="width:100%;">
        </div>

        <div class="form-group" style="margin-top:1.5rem;">
            <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Galería de Imágenes (Opcional)</label>
            <input type="file" name="gallery[]" accept="image/*" multiple style="width:100%;">
            <small style="color:#6b7280;">Puedes seleccionar varias imágenes a la vez.</small>
        </div>
        
        <div style="margin-top:2rem; text-align:right;">
            <button type="submit" style="background-color:#2563eb; color:white; padding:0.75rem 2rem; border:none; border-radius:0.5rem; font-weight:600; cursor:pointer;">Guardar Vehículo</button>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
