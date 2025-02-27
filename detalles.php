<?php
session_start();
require 'conexion.php';

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

// Verificar si el usuario ha dado like
$liked = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT id FROM likes WHERE usuario_id = ? AND contenido_id = ?");
    $stmt->execute([$_SESSION['user_id'], $id]);
    $liked = $stmt->fetch() ? true : false;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $contenido['titulo']; ?> - MyNetflix</title>
    <link rel="stylesheet" href="./css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body class="detalles-page">
    <nav class="nav-reproductor">
        <a href="index.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
        <div class="nav-title"><?php echo $contenido['titulo']; ?></div>
    </nav>

    <div class="hero-section" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.8)), url('./img/<?php echo $contenido['imagen']; ?>');">
        <div class="hero-content">
            <h1><?php echo $contenido['titulo']; ?></h1>
            <div class="hero-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="reproducir.php?id=<?php echo $contenido['id']; ?>" class="play-button-large">
                        <i class="fas fa-play"></i> Reproducir
                    </a>
                <?php else: ?>
                    <a href="login.php?redirect=reproducir&id=<?php echo $contenido['id']; ?>" class="play-button-large">
                        <i class="fas fa-play"></i> Reproducir
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="detalles-container">
        <div class="detalles-content">
            <div class="detalles-info">
            <div class="metadata">
                <span class="tipo-badge"><?php echo ucfirst($contenido['tipo']); ?></span>
                <span class="fecha"><?php echo date('Y', strtotime($contenido['fecha_lanzamiento'])); ?></span>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <button class="like-button <?php echo $liked ? 'liked' : ''; ?>" data-id="<?php echo $contenido['id']; ?>">
                        <i class="fas fa-heart"></i>
                        <span class="likes-count"><?php echo $contenido['likes']; ?></span>
                    </button>
                <?php else: ?>
                    <a href="login.php" class="like-button">
                        <i class="fas fa-heart"></i>
                        <span class="likes-count"><?php echo $contenido['likes']; ?></span>
                    </a>
                <?php endif; ?>
                <span class="duracion"><i class="fas fa-clock"></i> 120 min</span>
            </div>
                
                <div class="sinopsis">
                    <h2>Sinopsis</h2>
                    <p><?php echo $contenido['descripcion']; ?></p>
                </div>

                <?php if (!empty($contenido['director'])): ?>
                    <div class="detalle-item">
                        <h3>Director</h3>
                        <p><?php echo $contenido['director']; ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($contenido['reparto'])): ?>
                    <div class="detalle-item">
                        <h3>Reparto</h3>
                        <p><?php echo $contenido['reparto']; ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html> 