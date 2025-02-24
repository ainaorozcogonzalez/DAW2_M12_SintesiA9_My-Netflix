<?php
session_start();
require_once '../conexion.php';

// Verificar si el usuario es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
    header('Location: login.php');
    exit;
}

// Obtener usuarios validados
$stmt = $conn->prepare("SELECT id, nombre, email, rol_id, activo FROM usuarios");
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar eliminación
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$delete_id]);
        header('Location: admin_gestion_usuarios.php?success=1');
        exit;
    } catch (PDOException $e) {
        $error = "Error al eliminar el usuario: " . $e->getMessage();
    }
}

// Procesar edición
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_usuario'])) {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $rol_id = $_POST['rol_id'];
    $activo = isset($_POST['activo']) ? 1 : 0;

    try {
        $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, email = ?, rol_id = ?, activo = ? WHERE id = ?");
        $stmt->execute([$nombre, $email, $rol_id, $activo, $id]);
        header('Location: admin_gestion_usuarios.php?success=2');
        exit;
    } catch (PDOException $e) {
        $error = "Error al actualizar el usuario: " . $e->getMessage();
    }
}

// Procesar creación
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_usuario'])) {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $rol_id = $_POST['rol_id'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, contraseña, rol_id, activo) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $email, $password, $rol_id, $activo]);
        header('Location: admin_gestion_usuarios.php?success=3');
        exit;
    } catch (PDOException $e) {
        $error = "Error al crear el usuario: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios - MyNetflix</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="admin-page">
    <header>
        <div class="navbar">
            <div class="logo">
                <img src="../img/logo.webp" alt="logo">
            </div>
            <nav>
                <a href="../logout.php" class="logout-icon"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
            </nav>
        </div>
    </header>

    <div class="admin-container">
        <h1 style="color: white;">Gestionar Usuarios</h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php 
                if ($_GET['success'] == 1) {
                    echo 'Usuario eliminado correctamente';
                } elseif ($_GET['success'] == 2) {
                    echo 'Usuario actualizado correctamente';
                } elseif ($_GET['success'] == 3) {
                    echo 'Usuario creado correctamente';
                }
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#crearUsuarioModal">
            <i class="fas fa-plus"></i> Crear Usuario
        </button>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($usuarios) > 0): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                <td><?php echo ($usuario['rol_id'] == 2) ? 'Administrador' : 'Usuario'; ?></td>
                                <td><?php echo ($usuario['activo'] == 1) ? 'Habilitado' : 'Deshabilitado'; ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editarUsuarioModal<?php echo $usuario['id']; ?>">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                    <a href="?delete_id=<?php echo $usuario['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este usuario?');">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </a>
                                </td>
                            </tr>

                            <!-- Modal para editar usuario -->
                            <div class="modal fade" id="editarUsuarioModal<?php echo $usuario['id']; ?>" tabindex="-1" aria-labelledby="editarUsuarioModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editarUsuarioModalLabel">Editar Usuario</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST" action="">
                                                <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                                                <div class="mb-3">
                                                    <label for="nombre" class="form-label">Nombre</label>
                                                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="email" class="form-label">Email</label>
                                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="rol_id" class="form-label">Rol</label>
                                                    <select class="form-select" id="rol_id" name="rol_id" required>
                                                        <option value="1" <?php echo ($usuario['rol_id'] == 1) ? 'selected' : ''; ?>>Usuario</option>
                                                        <option value="2" <?php echo ($usuario['rol_id'] == 2) ? 'selected' : ''; ?>>Administrador</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3 form-check">
                                                    <input type="checkbox" class="form-check-input" id="activo" name="activo" <?php echo ($usuario['activo'] == 1) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="activo">Habilitado</label>
                                                </div>
                                                <button type="submit" name="editar_usuario" class="btn btn-primary">Guardar Cambios</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No hay usuarios registrados</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para crear usuario -->
    <div class="modal fade" id="crearUsuarioModal" tabindex="-1" aria-labelledby="crearUsuarioModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="crearUsuarioModalLabel">Crear Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="rol_id" class="form-label">Rol</label>
                            <select class="form-select" id="rol_id" name="rol_id" required>
                                <option value="1">Usuario</option>
                                <option value="2">Administrador</option>
                            </select>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="activo" name="activo" checked>
                            <label class="form-check-label" for="activo">Habilitado</label>
                        </div>
                        <button type="submit" name="crear_usuario" class="btn btn-primary">Crear Usuario</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>