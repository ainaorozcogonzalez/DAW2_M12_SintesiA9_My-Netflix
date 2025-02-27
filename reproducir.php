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

// Verificar si el usuario ha dado like
$stmt = $conn->prepare("SELECT id FROM likes WHERE usuario_id = ? AND contenido_id = ?");
$stmt->execute([$_SESSION['user_id'], $id]);
$liked = $stmt->fetch() ? true : false;

// Manejar like/dislike
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['toggle_like'])) {
    if ($liked) {
        // Quitar like
        $stmt = $conn->prepare("DELETE FROM likes WHERE usuario_id = ? AND contenido_id = ?");
        $stmt->execute([$_SESSION['user_id'], $id]);
        $stmt = $conn->prepare("UPDATE contenidos SET likes = likes - 1 WHERE id = ?");
    } else {
        // Dar like
        $stmt = $conn->prepare("INSERT INTO likes (usuario_id, contenido_id) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $id]);
        $stmt = $conn->prepare("UPDATE contenidos SET likes = likes + 1 WHERE id = ?");
    }
    $stmt->execute([$id]);
    
    // Obtener el nuevo número de likes
    $stmt = $conn->prepare("SELECT likes FROM contenidos WHERE id = ?");
    $stmt->execute([$id]);
    $newLikes = $stmt->fetchColumn();
    
    // Devolver la respuesta en JSON
    header('Content-Type: application/json');
    echo json_encode([
        'likes' => $newLikes,
        'liked' => !$liked
    ]);
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
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body class="reproductor-page">
    <nav class="nav-reproductor">
        <div class="nav-left">
            <a href="detalles.php?id=<?php echo $contenido['id']; ?>" class="back-button">
                <i class="fas fa-arrow-left"></i>
                <span>Volver</span>
            </a>
            <div class="nav-title"><?php echo $contenido['titulo']; ?></div>
        </div>
    </nav>

    <div class="video-wrapper">
        <div class="video-container">
            <video controls autoplay class="main-video" poster="./img/<?php echo $contenido['imagen']; ?>">
                <source src="./vd/<?php echo $contenido['video_url']; ?>" type="video/mp4">
                Tu navegador no soporta el elemento de video.
            </video>
        </div>
        
        <div class="content-info">
            <div class="content-header">
                <h1><?php echo $contenido['titulo']; ?></h1>
                <div class="metadata">
                    <span class="tipo-badge"><?php echo ucfirst($contenido['tipo']); ?></span>
                    <span class="fecha"><i class="far fa-calendar-alt"></i> <?php echo date('Y', strtotime($contenido['fecha_lanzamiento'])); ?></span>
                    <span class="likes-count">
                        <button class="like-button <?php echo $liked ? 'liked' : ''; ?>" data-id="<?php echo $contenido['id']; ?>">
                            <i class="fas fa-heart"></i>
                            <?php echo $contenido['likes']; ?>
                        </button>
                    </span>                    <?php if (!empty($contenido['duracion'])): ?>
                        <span class="duracion"><i class="far fa-clock"></i> <?php echo $contenido['duracion']; ?> min</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="content-details">
                <div class="sinopsis">
                    <h2><i class="fas fa-film"></i> Sinopsis</h2>
                    <p><?php echo $contenido['descripcion']; ?></p>
                </div>
                
                <?php if (!empty($contenido['director'])): ?>
                    <div class="crew-info">
                        <h3><i class="fas fa-film"></i> Director</h3>
                        <p><?php echo $contenido['director']; ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($contenido['reparto'])): ?>
                    <div class="crew-info">
                        <h3><i class="fas fa-users"></i> Reparto</h3>
                        <p><?php echo $contenido['reparto']; ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="./js/likes.js"></script>
</body>
</html> 