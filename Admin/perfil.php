<?php
session_start();

// Inicializar variables de error y éxito
$error = '';
$success = '';

// Verificar si el usuario está autenticado y tiene el rol de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: ../login.php');
    exit;
}

// Conectar a la base de datos
require '../Conexion/conexion.php';

try {
    // Obtener información del usuario desde la base de datos
    $stmt = $pdo->prepare('
        SELECT nombre, email, direccion, telefono, fecha_nacimiento
        FROM usuarios
        WHERE id = :usuario_id
    ');
    $stmt->execute(['usuario_id' => $_SESSION['usuario_id']]);
    $administrador = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar si la consulta retornó resultados válidos
    if (!$administrador) {
        $error = 'No se encontraron datos del administrador.';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = $_POST['nombre'] ?? '';
        $email = $_POST['email'] ?? '';
        $contrasena = $_POST['contrasena'] ?? '';
        $contrasena_confirmar = $_POST['contrasena_confirmar'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $direccion = $_POST['direccion'] ?? '';
        $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';

        // Validar contraseñas
        if ($contrasena !== '' && $contrasena !== $contrasena_confirmar) {
            $error = 'Las contraseñas no coinciden.';
        }

        if (empty($error)) {
            // Actualizar datos del usuario
            $update_user = 'UPDATE usuarios SET nombre = :nombre, email = :email, direccion = :direccion, telefono = :telefono, fecha_nacimiento = :fecha_nacimiento' .
                ($contrasena ? ', contrasena = :contrasena' : '') .
                ' WHERE id = :id';
            $stmt = $pdo->prepare($update_user);
            $params = [
                'nombre' => $nombre,
                'email' => $email,
                'direccion' => $direccion,
                'telefono' => $telefono,
                'fecha_nacimiento' => $fecha_nacimiento,
                'id' => $_SESSION['usuario_id'],
            ];
            if ($contrasena) {
                $params['contrasena'] = password_hash($contrasena, PASSWORD_DEFAULT);
            }
            $stmt->execute($params);

            $success = 'Perfil actualizado exitosamente.';
        }
    }
} catch (PDOException $e) {
    $error = 'Error en la consulta: ' . $e->getMessage();
} catch (Exception $e) {
    $error = 'Error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>

<body>
<header class="header">
        <div class="container">
            <h1 class="logo">
                <img src="/Imagenes/odontologia.png" alt="Logo de Clínica Dental">
                Clínica Dental
            </h1>
            <nav class="nav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="principalAdmin.php" class="nav-link">Inicio</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link">Administrar</a>
                        <ul class="dropdown-menu">
                            <li><a href="ver_citas.php" class="dropdown-link">Ver Citas</a></li>
                            <li><a href="ver_historial.php" class="dropdown-link">Historial Clínico</a></li>
                            <li><a href="perfil.php" class="dropdown-link">Mi Perfil</a></li>
                            <li><a href="usuarios.php" class="dropdown-link">Usuarios</a></li>
                            <li><a href="ver_solicitudes.php" class="dropdown-link">Solicitudes</a></li>
                            <li><a href="sedes.php" class="dropdown-link">Sedes</a></li>
                            <li><a href="especialidades.php" class="dropdown-link">Especialidades</a></li>
                            <li><a href="productos.php" class="dropdown-link">Productos</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a href="../logout.php" class="nav-link">Cerrar sesión</a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <section class="container-principal">
            <h2>Mi Perfil</h2>
            <table class="table">
                <?php if ($error): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
                <?php if ($success): ?>
                    <p class="exito"><?php echo htmlspecialchars($success); ?></p>
                <?php endif; ?>
                <tbody>
                    <tr>
                        <th>Nombre:</th>
                        <td><?php echo htmlspecialchars($administrador['nombre']); ?></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?php echo htmlspecialchars($administrador['email']); ?></td>
                    </tr>
                    <tr>
                        <th>Teléfono:</th>
                        <td><?php echo htmlspecialchars($administrador['telefono']); ?></td>
                    </tr>
                    <tr>
                        <th>Dirección:</th>
                        <td><?php echo htmlspecialchars($administrador['direccion']); ?></td>
                    </tr>
                    <tr>
                        <th>Fecha de Nacimiento:</th>
                        <td><?php echo htmlspecialchars($administrador['fecha_nacimiento']); ?></td>
                    </tr>
                </tbody>
            </table>
            <div class="container-principal">
                <a href="editar_perfil.php" class="btn-perfil">Editar Perfil</a>
            </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Clínica Dental del Dr. Fabián Mora. Todos los derechos reservados.</p>
        </div>
    </footer>
</body>

</html>