<?php
session_start();
require 'conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=reproducir&id=' . $_GET['id']);
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM contenidos WHERE id = ? AND activo = 1");
$stmt->execute([$id]);
$contenido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contenido) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reproduciendo: <?php echo $contenido['titulo']; ?></title>
    <link rel="stylesheet" href="./css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="reproductor-page">
    <nav class="nav-reproductor">
        <a href="index.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
        <div class="nav-title"><?php echo $contenido['titulo']; ?></div>
    </nav>

    <div class="video-container">
        <video controls autoplay>
            <source src="./vd/<?php echo $contenido['video_url']; ?>" type="video/mp4">
            Tu navegador no soporta el elemento de video.
        </video>
        
        <div class="content-info">
            <h1><?php echo $contenido['titulo']; ?></h1>
            <div class="metadata">
                <span class="tipo-badge"><?php echo ucfirst($contenido['tipo']); ?></span>
                <span class="fecha"><?php echo date('Y', strtotime($contenido['fecha_lanzamiento'])); ?></span>
                <span class="likes"><i class="fas fa-heart"></i> <?php echo $contenido['likes']; ?></span>
            </div>
            <p class="descripcion"><?php echo $contenido['descripcion']; ?></p>
        </div>
    </div>
</body>
</html> 