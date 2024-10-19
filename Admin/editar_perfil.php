<?php
session_start();

// Verificar si el usuario está autenticado y tiene el rol de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: ../login.php');
    exit;
}

// Conectar a la base de datos
require '../Conexion/conexion.php';

// Obtener la información del usuario
$usuario_id = $_SESSION['usuario_id'];

try {
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id');
    $stmt->execute([':id' => $usuario_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        die('Usuario no encontrado.');
    }
} catch (PDOException $e) {
    die('Error al obtener información del usuario: ' . $e->getMessage());
}

// Manejar el formulario de edición de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['correo'];
    $contrasena = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Validar contraseñas
    if ($contrasena !== $password_confirm) {
        die('Las contraseñas no coinciden.');
    }

    try {
        // Iniciar una transacción
        $pdo->beginTransaction();

        // Actualizar la información del usuario
        $stmt = $pdo->prepare('UPDATE usuarios SET nombre = :nombre, email = :email WHERE id = :id');
        $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':id' => $usuario_id
        ]);

        // Actualizar la contraseña si se proporciona una
        if (!empty($contrasena)) {
            $password = ($contrasena);
            $stmt = $pdo->prepare('UPDATE usuarios SET contrasena = :contrasena WHERE id = :id');
            $stmt->execute([
                ':contrasena' => $password,
                ':id' => $usuario_id
            ]);
        }

        // Confirmar la transacción
        $pdo->commit();

        // Redirigir a la página de perfil
        header('Location: perfil.php');
        exit;
    } catch (PDOException $e) {
        // Deshacer la transacción en caso de error
        $pdo->rollBack();
        die('Error al actualizar perfil: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
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
            <h2>Editar Perfil</h2>

            <form action="editar_perfil.php" method="post">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>

                    <label for="correo">Correo Electrónico:</label>
                    <input type="email" id="correo" name="correo" value="<?= htmlspecialchars($usuario['email']) ?>" required>

                    <label for="password">Nueva Contraseña:</label>
                    <input type="password" id="password" name="password">

                    <label for="password_confirm">Confirmar Nueva Contraseña:</label>
                    <input type="password" id="password_confirm" name="password_confirm">
                </div>
                <button type="submit" class="btn-actualizar">Actualizar Perfil</button>
            </form>
        </section>
    </main>
</body>
<footer class="footer">
        <div class="container">
            <p>&copy; 2024 Clínica Dental del Dr. Fabián Mora. Todos los derechos reservados.</p>
        </div>
    </footer>

</html>
