<?php
session_start();
require_once '../conexion.php';

// Verificar si el usuario es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
    header('Location: ../login.php');
    exit;
}

$error = '';
$success = '';

if (!isset($_GET['id'])) {
    header('Location: admin_gestion_peliculas.php');
    exit;
}

$id = $_GET['id'];

// Obtener los datos del contenido
$stmt = $conn->prepare("SELECT * FROM contenidos WHERE id = ?");
$stmt->execute([$id]);
$contenido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contenido) {
    header('Location: admin_gestion_peliculas.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $tipo = $_POST['tipo'];
    $fecha_lanzamiento = $_POST['fecha_lanzamiento'];
    $imagen = $_FILES['imagen']['name'];
    $video_url = $_FILES['video_url']['name'];

    if (empty($titulo) || empty($descripcion) || empty($tipo) || empty($fecha_lanzamiento)) {
        $error = 'Todos los campos son obligatorios';
    } else {
        try {
            // Si se sube una nueva imagen, actualizarla
            if (!empty($imagen)) {
                move_uploaded_file($_FILES['imagen']['tmp_name'], "../img/$imagen");
                $contenido['imagen'] = $imagen;
            }

            // Si se sube un nuevo video, actualizarlo
            if (!empty($video_url)) {
                move_uploaded_file($_FILES['video_url']['tmp_name'], "../vd/$video_url");
                $contenido['video_url'] = $video_url;
            }

            // Actualizar el contenido
            $stmt = $conn->prepare("UPDATE contenidos SET titulo = ?, descripcion = ?, tipo = ?, fecha_lanzamiento = ?, imagen = ?, video_url = ? WHERE id = ?");
            $stmt->execute([$titulo, $descripcion, $tipo, $fecha_lanzamiento, $contenido['imagen'], $contenido['video_url'], $id]);

            $success = 'Contenido actualizado correctamente';
        } catch (PDOException $e) {
            $error = 'Error al actualizar el contenido: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Película/Serie - Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Editar Película/Serie</h1>
        
        <?php if ($error): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="titulo">Título:</label>
                <input type="text" id="titulo" name="titulo" value="<?= htmlspecialchars($contenido['titulo']) ?>" required>
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" required><?= htmlspecialchars($contenido['descripcion']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="tipo">Tipo:</label>
                <select id="tipo" name="tipo" required>
                    <option value="pelicula" <?= $contenido['tipo'] == 'pelicula' ? 'selected' : '' ?>>Película</option>
                    <option value="serie" <?= $contenido['tipo'] == 'serie' ? 'selected' : '' ?>>Serie</option>
                </select>
            </div>

            <div class="form-group">
                <label for="fecha_lanzamiento">Fecha de Lanzamiento:</label>
                <input type="date" id="fecha_lanzamiento" name="fecha_lanzamiento" value="<?= $contenido['fecha_lanzamiento'] ?>" required>
            </div>

            <div class="form-group">
                <label for="imagen">Imagen (Poster):</label>
                <input type="file" id="imagen" name="imagen" accept="image/*">
                <small>Dejar en blanco para mantener la imagen actual.</small>
            </div>

            <div class="form-group">
                <label for="video_url">Archivo de Video:</label>
                <input type="file" id="video_url" name="video_url" accept="video/*">
                <small>Dejar en blanco para mantener el video actual.</small>
            </div>

            <button type="submit" class="btn-submit">Actualizar Contenido</button>
        </form>
    </div>
</body>
</html>
