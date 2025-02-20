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
            // Subir archivos
            $imagen_path = "../img/$imagen";
            $video_path = "../vd/$video_url";
            
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $imagen_path) &&
                move_uploaded_file($_FILES['video_url']['tmp_name'], $video_path)) {
                
                // Insertar nuevo contenido
                $stmt = $conn->prepare("INSERT INTO contenidos (titulo, descripcion, tipo, fecha_lanzamiento, imagen, video_url) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$titulo, $descripcion, $tipo, $fecha_lanzamiento, $imagen, $video_url]);
                $success = 'Contenido agregado correctamente';
            } else {
                $error = 'Error al subir los archivos';
            }
        } catch (PDOException $e) {
            $error = 'Error al agregar el contenido: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Película/Serie - Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Agregar Nueva Película/Serie</h1>
        
        <?php if ($error): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="titulo">Título:</label>
                <input type="text" id="titulo" name="titulo" required>
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" required></textarea>
            </div>

            <div class="form-group">
                <label for="tipo">Tipo:</label>
                <select id="tipo" name="tipo" required>
                    <option value="pelicula">Película</option>
                    <option value="serie">Serie</option>
                </select>
            </div>

            <div class="form-group">
                <label for="fecha_lanzamiento">Fecha de Lanzamiento:</label>
                <input type="date" id="fecha_lanzamiento" name="fecha_lanzamiento" required>
            </div>

            <div class="form-group">
                <label for="imagen">Imagen (Poster):</label>
                <input type="file" id="imagen" name="imagen" accept="image/*" required>
            </div>

            <div class="form-group">
                <label for="video_url">Archivo de Video:</label>
                <input type="file" id="video_url" name="video_url" accept="video/*" required>
            </div>

            <button type="submit" class="btn-submit">Agregar Contenido</button>
        </form>
    </div>
</body>
</html>
