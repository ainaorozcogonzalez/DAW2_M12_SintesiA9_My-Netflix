<?php
session_start(); // Iniciar la sesión
require 'conexion.php'; // Incluir la conexión a la base de datos

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
</head>
<body>
    <header>
        <div class="navbar">
            <div class="logo">
                <h1>MyNetflix</h1> <!-- Aquí puedes poner una imagen como logo -->
            </div>
            <div class="search-container">
                <input type="text" placeholder="Buscar películas o series..." aria-label="Buscar">
            </div>
            <nav>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span>Usuario</span> <!-- Mostrar nombre de usuario o "Usuario" -->
                    <a href="logout.php">Logout</a> <!-- Botón de logout -->
                <?php else: ?>
                    <a href="login.php">Iniciar Sesión</a>
                    <a href="register.php">Registrarse</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main>
        <section id="top5">
            <h2>Top 5 Contenidos</h2>
            <div class="top5-container">
                <?php foreach ($top5_contenidos as $contenido): ?>
                    <div class="contenido">
                        <img src="<?php echo $contenido['imagen']; ?>" alt="<?php echo $contenido['titulo']; ?>">
                        <h3><?php echo $contenido['titulo']; ?></h3>
                        <p><?php echo $contenido['descripcion']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="contenidos">
            <h2>Contenidos Disponibles</h2>
            <div class="contenidos-container">
                <?php foreach ($all_contenidos as $contenido): ?>
                    <div class="contenido">
                        <img src="<?php echo $contenido['imagen']; ?>" alt="<?php echo $contenido['titulo']; ?>">
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
