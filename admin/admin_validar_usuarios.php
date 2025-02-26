<?php
session_start();
require_once '../conexion.php';

// Verificar si el usuario es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
    header('Location: login.php');
    exit;
}

// MANEJO DE SOLICITUDES AJAX
if (isset($_GET['obtener_tabla']) || isset($_GET['id']) || isset($_GET['delete_id'])) {
    header('Content-Type: application/json');

    // Procesar validación vía AJAX
    if (isset($_GET['id'])) {
        try {
            $stmt = $conn->prepare("UPDATE usuarios SET activo = 1 WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            echo json_encode(['status' => 'success']);
            exit;
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    // Procesar eliminación vía AJAX
    if (isset($_GET['delete_id'])) {
        try {
            $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$_GET['delete_id']]);
            echo json_encode(['status' => 'success']);
            exit;
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    // Devolver tabla vía AJAX
    if (isset($_GET['obtener_tabla'])) {
        try {
            $stmt = $conn->prepare("SELECT id, nombre, email, activo FROM usuarios WHERE activo = 0");
            $stmt->execute();
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            ob_start();
            if (count($usuarios) > 0): ?>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                        <td>
                            <button class="btn btn-success btn-sm btn-validar" data-id="<?php echo $usuario['id']; ?>">
                                <i class="fas fa-check"></i> Validar
                            </button>
                            <button class="btn btn-danger btn-sm btn-eliminar" data-id="<?php echo $usuario['id']; ?>">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="text-center">No hay usuarios pendientes de validación</td>
                </tr>
            <?php endif;
            $html = ob_get_clean();
            echo json_encode(['status' => 'success', 'html' => $html]);
            exit;
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }
}

// CARGA INICIAL DE LA PÁGINA
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validar Usuarios - MyNetflix</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body class="admin-page">
    <header>
        <div class="navbar">
            <div class="logo">
                <img src="../img/logo.webp" alt="logo">
            </div>
            <nav>
                <a href="../admin_page.php" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <a href="../logout.php" class="logout-icon">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </nav>
        </div>
    </header>

    <div class="admin-container">
        <h1 style="color: white;">Validar Usuarios</h1>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- La tabla se cargará vía AJAX -->
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/validar_usuarios.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>