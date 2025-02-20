<?php
session_start();
require_once '../conexion.php';

// Verificar si el usuario es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
    header('Location: ../login.php');
    exit;
}

// Obtener todos los usuarios
$stmt = $conn->prepare("SELECT id, nombre, email, activo, fecha_registro FROM usuarios");
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Gestión de Usuarios</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Estado</th>
                    <th>Fecha Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?= $usuario['id'] ?></td>
                    <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                    <td><?= $usuario['activo'] ? 'Activo' : 'Inactivo' ?></td>
                    <td><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></td>
                    <td>
                        <a href="admin_validar_usuarios.php?id=<?= $usuario['id'] ?>">Validar</a>
                        <a href="#" onclick="confirmarEliminacion(<?= $usuario['id'] ?>)">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
    function confirmarEliminacion(id) {
        if (confirm('¿Estás seguro de eliminar este usuario?')) {
            window.location.href = 'admin_eliminar_usuario.php?id=' + id;
        }
    }
    </script>
</body>
</html>
