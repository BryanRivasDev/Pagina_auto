<?php
$pageTitle = 'Carrusel';
require_once 'includes/header.php';
require_once '../config.php';

$message = '';
$error = '';

// Handle Add Slide
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'mp4', 'webm', 'ogg'];
        $filename = $_FILES['image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            $new_name = "slide_" . uniqid() . "." . $filetype;
            if (move_uploaded_file($_FILES['image']['tmp_name'], "../assets/images/" . $new_name)) {
                $image_path = "assets/images/" . $new_name;
                $title = trim($_POST['title']);
                $subtitle = trim($_POST['subtitle']);
                
                $stmt = $pdo->prepare("INSERT INTO carousel_slides (image_path, title, subtitle) VALUES (:img, :title, :sub)");
                $stmt->execute([':img' => $image_path, ':title' => $title, ':sub' => $subtitle]);
                echo "<script>window.location.href='carousel.php';</script>";
                exit;
            } else {
                $error = "Error al subir la imagen.";
            }
        } else {
            $error = "Formato de imagen no válido.";
        }
    } else {
        $error = "Selecciona una imagen.";
    }
}

// Handle Delete Slide
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("SELECT image_path FROM carousel_slides WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $slide = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($slide) {
        if (file_exists("../" . $slide['image_path'])) {
            unlink("../" . $slide['image_path']);
        }
        $pdo->prepare("DELETE FROM carousel_slides WHERE id = :id")->execute([':id' => $id]);
    }
    echo "<script>window.location.href='carousel.php';</script>";
    exit;
}

// Fetch Slides
$slides = $pdo->query("SELECT * FROM carousel_slides ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="margin-bottom: 2rem;">
    <h1 style="color: var(--text-main); margin-bottom: 0.5rem;">Gestor del Carrusel</h1>
    <p style="color: var(--text-light); margin: 0;">Administra las imágenes que aparecen en el inicio.</p>
</div>

<div class="card">
    <div class="section-title"><i class="fas fa-plus-circle"></i> Nuevo Slide</div>
    <?php if($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
    
    <style>
        .form-grid-responsive {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }
        @media (max-width: 768px) {
            .form-grid-responsive {
                grid-template-columns: 1fr;
                gap: 1rem;
                align-items: stretch;
            }
            .form-grid-responsive button {
                width: 100%;
                margin-top: 0.5rem;
            }
        }
    </style>
    <form action="" method="post" enctype="multipart/form-data" class="form-grid-responsive">
        <input type="hidden" name="action" value="add">
        
        <div class="form-group">
            <label style="margin-bottom:0;">Archivo (Imagen o Video corto)</label>
            <small style="color: #64748b; display: block; margin-bottom: 0.5rem;">Medida recomendada: 1400x400 px</small>
            <input type="file" name="image" accept="image/*,video/*" required>
        </div>
        
        <div class="form-group">
            <label>Título (Opcional)</label>
            <input type="text" name="title" placeholder="Ej. Nuevos Modelos 2024">
        </div>
        
        <div class="form-group">
            <label>Subtítulo (Opcional)</label>
            <input type="text" name="subtitle" placeholder="Ej. Encuentra tu auto ideal hoy">
        </div>
        
        <button type="submit" class="btn-modern">Subir</button>
    </form>
</div>

<div class="card">
    <div class="section-title"><i class="fas fa-images"></i> Slides Actuales</div>
    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap:1.5rem;">
        <?php foreach($slides as $slide): ?>
            <div style="position:relative; group;">
                <?php 
                $ext = strtolower(pathinfo($slide['image_path'], PATHINFO_EXTENSION));
                if(in_array($ext, ['mp4', 'webm', 'ogg'])): 
                ?>
                    <video src="../<?php echo htmlspecialchars($slide['image_path']); ?>" style="width:100%; height:180px; object-fit:cover; border-radius:0.5rem;" controls muted></video>
                <?php else: ?>
                    <img src="../<?php echo htmlspecialchars($slide['image_path']); ?>" style="width:100%; height:180px; object-fit:cover; border-radius:0.5rem;">
                <?php endif; ?>
                <div style="position:absolute; bottom:0; left:0; right:0; background:rgba(0,0,0,0.7); color:white; padding:0.5rem; border-bottom-left-radius:0.5rem; border-bottom-right-radius:0.5rem;">
                    <div style="font-weight:bold; font-size:0.9rem;"><?php echo htmlspecialchars($slide['title']); ?></div>
                    <div style="font-size:0.8rem; opacity:0.8;"><?php echo htmlspecialchars($slide['subtitle']); ?></div>
                </div>
                <a href="carousel.php?delete=<?php echo $slide['id']; ?>" onclick="confirmLinkAction(event, this.href, '¿Eliminar slide?', 'Se eliminará del carrusel de inicio.')" 
                   style="position:absolute; top:10px; right:10px; background:red; color:white; width:30px; height:30px; display:flex; align-items:center; justify-content:center; border-radius:50%; text-decoration:none;">
                    <i class="fas fa-trash"></i>
                </a>
            </div>
        <?php endforeach; ?>
        
        <?php if(empty($slides)): ?>
            <p style="color:var(--text-light); font-style:italic;">No hay slides. Sube uno arriba.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
