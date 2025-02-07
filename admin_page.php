<?php
session_start();
require_once 'conexion.php';

// Verificar si el usuario es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
    header('Location: login.php');
    exit;
}

$error = '';
$mensaje = '';

// Manejo de sesión (Logout)
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Obtener usuarios y películas
$stmt = $conn->prepare("SELECT id, nombre, email, activo FROM usuarios");
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM contenidos ORDER BY titulo");
$stmt->execute();
$peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - MyNetflix</title>
    <link rel="stylesheet" href="./css/styles.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

<header>
    <div class="navbar">
        <div class="logo">
            <img src="./img/logo.webp" alt="logo">
        </div>
        <div class="search-container">
            <input type="text" placeholder="Buscar películas o series..." aria-label="Buscar">
        </div>
        <nav>
            <a href="perfil.php" class="user-icon"><i class="fas fa-user"></i></a>
            <form method="POST" style="display:inline;">
                <button type="submit" name="logout" class="logout-icon"><i class="fas fa-sign-out-alt"></i></button>
            </form>
        </nav>
    </div>
</header>

<div class="container">
    <!-- Menú lateral -->
    <nav class="sidebar">
        <ul>
            <li><a href="?seccion=validar" class="<?= ($_GET['seccion'] ?? 'validar') == 'validar' ? 'active' : '' ?>"><i class="fas fa-user-check"></i> Validar Usuarios</a></li>
            <li><a href="?seccion=usuarios" class="<?= ($_GET['seccion'] ?? '') == 'usuarios' ? 'active' : '' ?>"><i class="fas fa-users"></i> Administrar Usuarios</a></li>
            <li><a href="?seccion=peliculas" class="<?= ($_GET['seccion'] ?? '') == 'peliculas' ? 'active' : '' ?>"><i class="fas fa-film"></i> Administrar Películas/Series</a></li>
        </ul>
    </nav>

    <!-- Contenido dinámico -->
    <main class="content">
        <?php 
        $seccion = $_GET['seccion'] ?? 'validar';

        if ($seccion == 'validar') {
            include 'admin_validar_usuarios.php';
        } elseif ($seccion == 'usuarios') {
            include 'admin_gestion_usuarios.php';
        } elseif ($seccion == 'peliculas') {
            include 'admin_gestion_peliculas.php';
        } else {
            echo "<h2>Bienvenido al panel de administración</h2>";
        }
        ?>
    </main>
</div>

</body>
</html>
