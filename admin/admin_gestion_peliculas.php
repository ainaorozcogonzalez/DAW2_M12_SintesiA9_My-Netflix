<?php
session_start();
require_once '../conexion.php';

// Verificar si el usuario es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
    header('Location: ../login.php');
    exit;
}

// Buscador rápido
$search = $_GET['search'] ?? '';
$query = "SELECT * FROM contenidos WHERE titulo LIKE ? ORDER BY titulo";
$stmt = $conn->prepare($query);
$stmt->execute(["%$search%"]);
$peliculas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Películas - Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Gestión de Películas/Series</h1>
        
        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Buscar películas..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Buscar</button>
        </form>

        <a href="admin_agregar_pelicula.php" class="btn-add">Agregar Nueva Película/Serie</a>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Tipo</th>
                    <th>Likes</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($peliculas as $pelicula): ?>
                <tr>
                    <td><?= $pelicula['id'] ?></td>
                    <td><?= htmlspecialchars($pelicula['titulo']) ?></td>
                    <td><?= ucfirst($pelicula['tipo']) ?></td>
                    <td><?= $pelicula['likes'] ?></td>
                    <td>
                        <a href="admin_editar_pelicula.php?id=<?= $pelicula['id'] ?>">Editar</a>
                        <a href="#" onclick="confirmarEliminacion(<?= $pelicula['id'] ?>)">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
    function confirmarEliminacion(id) {
        if (confirm('¿Estás seguro de eliminar este contenido?')) {
            window.location.href = 'admin_eliminar_pelicula.php?id=' + id;
        }
    }
    </script>
</body>
</html>
