<?php
session_start(); // Iniciar la sesión
require 'conexion.php'; // Incluir la conexión a la base de datos

// Verificar si el usuario está logueado y activo
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT activo FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user['activo'] == 0) {
        session_destroy();
        header('Location: login.php?error=Tu cuenta está pendiente de activación');
        exit;
    }
}

// Obtener los 5 contenidos más populares
$stmt = $conn->prepare("SELECT * FROM contenidos WHERE activo = 1 ORDER BY likes DESC LIMIT 5");
$stmt->execute();
$top5_contenidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener todos los contenidos
$stmt = $conn->prepare("SELECT * FROM contenidos WHERE activo = 1");
$stmt->execute();
$all_contenidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyNetflix</title>
    <link rel="stylesheet" href="./css/styles.css"> <!-- Enlace a tu archivo CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <!-- Iconos -->
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <script src="./js/likes.js" defer></script>
    <script src="./js/filtros.js" defer></script>
</head>
<body>
    <header class="header1">
        <div class="navbar">
            <div class="logo">
                <img src="./img/logo.webp" alt="logo">
            </div>
            <div class="search-container">
                <input type="text" placeholder="Buscar películas o series..." aria-label="Buscar">
            </div>
            <nav>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="perfil.php" class="user-icon"><i class="fas fa-user"></i></a>
                    <a href="logout.php" class="logout-icon"><i class="fas fa-sign-out-alt"></i></a>

                <?php else: ?>
                    <a href="login.php">Iniciar Sesión</a>
                    <a href="register.php">Registrarse</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <div class="filtros-container">
        <nav class="filtros-nav">
            <button class="filtro-btn" data-filtro="all">ALL</button>
            <button class="filtro-btn" data-filtro="movies">MOVIES</button>
            <button class="filtro-btn" data-filtro="series">SERIES</button>
            <button class="filtro-btn" data-filtro="most-liked">MOST LIKED</button>
        </nav>
    </div>

    <main>
        <section id="top5">
            <h2>Top 5 Contenidos</h2>
            <div class="top5-container">
                <?php 
                $ranking = 1;
                foreach ($top5_contenidos as $contenido): 
                ?>
                    <div class="contenido" data-tipo="<?php echo $contenido['tipo']; ?>" data-likes="<?php echo $contenido['likes']; ?>">
                        <div class="imagen-container">
                            <div class="ranking-number">#<?php echo $ranking; ?></div>
                            <a href="detalles.php?id=<?php echo $contenido['id']; ?>" class="imagen-link">
                                <img src="./img/<?php echo $contenido['imagen']; ?>" alt="<?php echo $contenido['titulo']; ?>">
                            </a>
                            <div class="overlay">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <a href="reproducir.php?id=<?php echo $contenido['id']; ?>" class="play-button">
                                        <i class="fas fa-play"></i>
                                    </a>
                                    <button class="like-button" data-id="<?php echo $contenido['id']; ?>">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                <?php else: ?>
                                    <a href="login.php?redirect=reproducir&id=<?php echo $contenido['id']; ?>" class="play-button">
                                        <i class="fas fa-play"></i>
                                    </a>
                                    <a href="login.php" class="like-button">
                                        <i class="fas fa-heart"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <h3 class="top5-title"><?php echo $contenido['titulo']; ?></h3>
                    </div>
                <?php 
                $ranking++;
                endforeach; 
                ?>
            </div>
        </section>

        <section id="contenidos">
            <h2>Contenidos Disponibles</h2>
            <div class="contenidos-container">
                <?php foreach ($all_contenidos as $contenido): ?>
                    <div class="contenido" data-tipo="<?php echo $contenido['tipo']; ?>" data-likes="<?php echo $contenido['likes']; ?>">
                        <a href="detalles.php?id=<?php echo $contenido['id']; ?>" class="imagen-link">
                            <img src="./img/<?php echo $contenido['imagen']; ?>" alt="<?php echo $contenido['titulo']; ?>">
                        </a>
                        <h3><?php echo $contenido['titulo']; ?></h3>
                        <p><?php echo $contenido['descripcion']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer>
        <!-- Derechos de autor eliminados -->
    </footer>
</body>
</html>
