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
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $conn->beginTransaction();
        
        // Eliminar likes asociados
        $stmt = $conn->prepare("DELETE FROM likes WHERE usuario_id = ?");
        $stmt->execute([$delete_id]);
        
        // Eliminar el usuario
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$delete_id]);
        
        $conn->commit();
        echo json_encode(['status' => 'success']);
        exit;
    } catch (PDOException $e) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
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
        echo json_encode(['status' => 'success']);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
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
        // Verificar si el email ya existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'El email ya está registrado']);
            exit;
        }

        // Crear el usuario
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, contraseña, rol_id, activo) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $email, $password, $rol_id, $activo]);
        echo json_encode(['status' => 'success']);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

// Devolver la tabla completa si se solicita mediante AJAX
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['obtener_tabla'])) {
    $query = "SELECT id, nombre, email, rol_id, activo FROM usuarios";
    $params = [];
    
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = '%' . $_GET['search'] . '%';
        $query .= " WHERE nombre LIKE :search OR email LIKE :search";
        $params['search'] = $search;
    }

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($usuarios as $usuario): ?>
        <tr>
            <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
            <td><?php echo ($usuario['rol_id'] == 2) ? 'Administrador' : 'Usuario'; ?></td>
            <td><?php echo ($usuario['activo'] == 1) ? 'Habilitado' : 'Deshabilitado'; ?></td>
            <td>
                <button class="btn btn-warning btn-sm btn-editar" data-id="<?php echo $usuario['id']; ?>" data-bs-toggle="modal" data-bs-target="#editarUsuarioModal">
                    <i class="fas fa-edit"></i> Editar
                </button>
                <button class="btn btn-danger btn-sm btn-eliminar" data-id="<?php echo $usuario['id']; ?>">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </td>
        </tr>
    <?php endforeach;
    exit;
}

// Obtener un solo usuario
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['obtener_usuario'])) {
    $userId = (int)$_GET['obtener_usuario'];
    if ($userId <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID de usuario no válido']);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT id, nombre, email, rol_id, activo FROM usuarios WHERE id = ?");
        $stmt->execute([$userId]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
            exit;
        }
        
        echo json_encode($usuario);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

        <!-- Buscador rápido -->
        <form class="mb-3 form-buscar">
            <div class="input-group">
                <input type="text" class="form-control" name="search" id="searchInput" placeholder="Buscar usuarios por nombre o email...">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </div>
        </form>

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
                    <!-- Aquí se insertarán los usuarios dinámicamente -->
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
                    <form method="POST" class="form-crear-usuario">
                        <input type="hidden" name="crear_usuario" value="1">
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
                        <button type="submit" class="btn btn-primary">Crear Usuario</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Mover el modal fuera del bucle -->
    <div class="modal fade" id="editarUsuarioModal" tabindex="-1" aria-labelledby="editarUsuarioModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editarUsuarioModalLabel">Editar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" class="form-editar-usuario">
                        <input type="hidden" name="editar_usuario" value="1">
                        <input type="hidden" name="id" id="editarUsuarioId">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="editarNombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editarEmail" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="rol_id" class="form-label">Rol</label>
                            <select class="form-select" id="editarRol" name="rol_id" required>
                                <option value="1">Usuario</option>
                                <option value="2">Administrador</option>
                            </select>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="editarActivo" name="activo">
                            <label class="form-check-label" for="activo">Habilitado</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/gestion_usuarios.js"></script>
</body>
</html>