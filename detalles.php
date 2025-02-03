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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $contenido['titulo']; ?> - MyNetflix</title>
    <link rel="stylesheet" href="./css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="detalles-page">
    <nav class="nav-reproductor">
        <a href="index.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Volver
        </a>
        <div class="nav-title"><?php echo $contenido['titulo']; ?></div>
    </nav>

    <div class="detalles-container">
        <div class="detalles-content">
            <div class="detalles-imagen">
                <img src="./img/<?php echo $contenido['imagen']; ?>" alt="<?php echo $contenido['titulo']; ?>">
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
            <div class="detalles-info">
                <h1><?php echo $contenido['titulo']; ?></h1>
                <div class="metadata">
                    <span class="tipo-badge"><?php echo ucfirst($contenido['tipo']); ?></span>
                    <span class="fecha"><?php echo date('Y', strtotime($contenido['fecha_lanzamiento'])); ?></span>
                    <span class="likes"><i class="fas fa-heart"></i> <?php echo $contenido['likes']; ?></span>
                </div>
                <p class="descripcion"><?php echo $contenido['descripcion']; ?></p>
            </div>
        </div>
    </div>
</body>
</html> 