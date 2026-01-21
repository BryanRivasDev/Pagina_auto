<?php
$pageTitle = 'Marcas';
require_once 'includes/header.php';
require_once '../config.php';

// Handle Add/Edit Brand
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $name = trim($_POST['name']);
    $logo_path = null;

    // Handle Image Upload
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../assets/images/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); 
        }
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_ext;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $logo_path = "assets/images/" . $new_filename;
        }
    }

    if ($action === 'add' && !empty($name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO brands (name, logo_path) VALUES (:name, :logo_path)");
            $stmt->execute([':name' => $name, ':logo_path' => $logo_path]);
            echo "<script>window.location.href='brands.php';</script>";
            exit;
        } catch (PDOException $e) {
            $error = "Error al agregar marca (¿Quizás ya existe?)";
        }
    } elseif ($action === 'edit' && !empty($name) && isset($_POST['id'])) {
        $id = $_POST['id'];
        $sql = "UPDATE brands SET name = :name";
        $params = [':name' => $name, ':id' => $id];
        
        if ($logo_path) {
            $sql .= ", logo_path = :logo_path";
            $params[':logo_path'] = $logo_path;
        }
        
        $sql .= " WHERE id = :id";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo "<script>window.location.href='brands.php';</script>";
            exit;
        } catch (PDOException $e) {
             $error = "Error al actualizar marca.";
        }
    }
}

// Handle Delete Brand
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM brands WHERE id = :id");
    $stmt->execute([':id' => $id]);
    echo "<script>window.location.href='brands.php';</script>";
    exit;
}

// Fetch Brands
$stmt = $pdo->query("SELECT * FROM brands ORDER BY name ASC");
$brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
/* Modal Styles */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.6);
    z-index: 1000;
    justify-content: center;
    align-items: center;
    backdrop-filter: blur(4px);
}
.modal-content {
    background: var(--surface);
    padding: 2rem;
    border-radius: 1rem;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
    border: 1px solid var(--border);
    position: relative;
    animation: modalSlideIn 0.3s ease-out;
}
@keyframes modalSlideIn {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
.close-modal {
    position: absolute;
    top: 1rem; right: 1rem;
    background: transparent;
    border: none;
    color: var(--text-light);
    font-size: 1.5rem;
    cursor: pointer;
    transition: color 0.2s;
}
.close-modal:hover { color: var(--primary); }
</style>

<div style="margin-bottom: 2rem; display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h1 style="color: var(--text-main); margin-bottom: 0.5rem;">Gestión de Marcas</h1>
        <p style="color: var(--text-light); margin: 0;">Agrega, edita o elimina marcas de vehículos.</p>
    </div>
    <button onclick="openModal('add')" class="btn-modern">
        <i class="fas fa-plus"></i> Nueva Marca
    </button>
</div>

<!-- Modal -->
<div id="brandModal" class="modal-overlay">
    <div class="modal-content">
        <button class="close-modal" onclick="closeModal()">&times;</button>
        <div class="section-title" id="modalTitle" style="margin-bottom: 1.5rem;"><i class="fas fa-plus-circle"></i> Nueva Marca</div>
        
        <form action="" method="post" enctype="multipart/form-data" id="brandForm" style="display:flex; flex-direction:column; gap:1.5rem;">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="brandId" value="">
            
            <div>
                <label style="display:block; font-size:0.9rem; margin-bottom:0.5rem; color: var(--text-light);">Nombre de la Marca</label>
                <input type="text" name="name" id="brandName" placeholder="Ej. Toyota" class="form-control" required style="width:100%;">
            </div>
            
            <div>
                 <label style="display:block; font-size:0.9rem; margin-bottom:0.5rem; color: var(--text-light);">Logo (Opcional)</label>
                 <div style="border: 2px dashed var(--border); padding: 1rem; border-radius: 0.5rem; text-align: center; cursor: pointer;" onclick="document.getElementById('brandImage').click()">
                     <i class="fas fa-cloud-upload-alt" style="font-size: 1.5rem; color: var(--primary); margin-bottom: 0.5rem;"></i>
                     <p style="margin:0; font-size: 0.8rem; color: var(--text-light);">Click para subir logo</p>
                 </div>
                 <input type="file" name="image" id="brandImage" accept="image/*" style="display:none;" onchange="updateFileName(this)">
                 <p id="fileNameDisplay" style="margin-top:0.5rem; font-size:0.8rem; color: var(--text-main); text-align:center;"></p>
            </div>
            
            <div style="display:flex; gap: 1rem; justify-content: flex-end; margin-top: 1rem;">
                <button type="button" onclick="closeModal()" class="btn-modern" style="background: transparent; border: 1px solid var(--border); color: var(--text-main);">Cancelar</button>
                <button type="submit" id="submitBtn" class="btn-modern" style="min-width: 120px;">Guardar</button>
            </div>
        </form>
    </div>
</div>

<?php if(isset($error)): ?>
    <div style="background:#fee2e2; color:#991b1b; padding:1rem; border-radius:0.5rem; margin-bottom:2rem; border: 1px solid #fecaca;">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="section-title"><i class="fas fa-tags"></i> Marcas Existentes</div>
    
    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap:1rem;">
        <?php foreach($brands as $brand): ?>
            <div style="background:var(--bg-color); padding:1.5rem; border-radius:1rem; display:flex; flex-direction:column; align-items:center; border:1px solid var(--border); text-align: center; position: relative; overflow: hidden; transition: transform 0.2s;">
                
                <?php if($brand['logo_path']): ?>
                    <img src="../<?php echo htmlspecialchars($brand['logo_path']); ?>" alt="logo" style="width: 80px; height: 50px; object-fit: contain; margin-bottom: 1rem;">
                <?php else: ?>
                    <div style="width: 80px; height: 50px; background: #374151; border-radius: 4px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-tag" style="color: #9ca3af; font-size: 1.5rem;"></i>
                    </div>
                <?php endif; ?>
                
                <span style="font-weight:600; font-size: 1.1rem; color:var(--text-main); margin-bottom: 1rem;"><?php echo htmlspecialchars($brand['name']); ?></span>
                
                <div style="display: flex; gap: 0.8rem; width: 100%; justify-content: center;">
                    <!-- Edit Button -->
                    <button onclick="openModal('edit', <?php echo $brand['id']; ?>, '<?php echo htmlspecialchars($brand['name']); ?>')" 
                            style="background: rgba(59, 130, 246, 0.1); border: 1px solid #3b82f6; color: #3b82f6; border-radius: 6px; padding: 0.4rem 0.8rem; cursor: pointer; font-size: 0.9rem; transition: all 0.2s;" onmouseover="this.style.background='rgba(59, 130, 246, 0.2)'" onmouseout="this.style.background='rgba(59, 130, 246, 0.1)'">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                    
                    <!-- Delete Link -->
                    <a href="brands.php?delete=<?php echo $brand['id']; ?>" 
                       onclick="confirmLinkAction(event, this.href, '¿Eliminar marca?', 'Esta acción es irreversible.');" 
                       style="background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #ef4444; border-radius: 6px; padding: 0.4rem 0.8rem; text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 4px; transition: all 0.2s;" onmouseover="this.style.background='rgba(239, 68, 68, 0.2)'" onmouseout="this.style.background='rgba(239, 68, 68, 0.1)'">
                        <i class="fas fa-trash"></i> Eliminar
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function openModal(mode, id = null, name = '') {
    const modal = document.getElementById('brandModal');
    const formAction = document.getElementById('formAction');
    const brandId = document.getElementById('brandId');
    const brandName = document.getElementById('brandName');
    const modalTitle = document.getElementById('modalTitle');
    const fileNameDisplay = document.getElementById('fileNameDisplay');
    
    // Reset form
    document.getElementById('brandForm').reset();
    fileNameDisplay.innerText = '';

    if (mode === 'edit') {
        modalTitle.innerHTML = '<i class="fas fa-edit"></i> Editar Marca';
        formAction.value = 'edit';
        brandId.value = id;
        brandName.value = name;
    } else {
        modalTitle.innerHTML = '<i class="fas fa-plus-circle"></i> Nueva Marca';
        formAction.value = 'add';
        brandId.value = '';
        brandName.value = '';
    }
    
    modal.style.display = 'flex';
}

function closeModal() {
    document.getElementById('brandModal').style.display = 'none';
}

function updateFileName(input) {
    const display = document.getElementById('fileNameDisplay');
    if (input.files && input.files[0]) {
        display.innerText = 'Archivo seleccionado: ' + input.files[0].name;
    } else {
        display.innerText = '';
    }
}

// Close modal if clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('brandModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
