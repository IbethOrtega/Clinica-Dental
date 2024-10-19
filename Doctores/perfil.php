<?php
session_start();

// Verificar si el usuario está autenticado y tiene el rol de paciente
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'doctor') {
    header('Location: ../login.php');
    exit;
}

// Conectar a la base de datos (ajusta la ruta de conexión si es necesario)
require '../Conexion/conexion.php';

try {
    // Obtener información del paciente desde la base de datos
    $stmt = $pdo->prepare('SELECT * FROM doctores WHERE usuario_id = :usuario_id');
    $stmt->execute(['usuario_id' => $_SESSION['usuario_id']]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    // Obtener información del usuario desde la base de datos
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id');
    $stmt->execute(['id' => $_SESSION['usuario_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar si las consultas retornaron resultados válidos
    if (!$paciente || !$usuario) {
        $error = 'No se encontraron datos del usuario o paciente.';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = $_POST['nombre'] ?? '';
        $email = $_POST['email'] ?? '';
        $contrasena = $_POST['contrasena'] ?? '';
        $contrasena_confirmar = $_POST['contrasena_confirmar'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $direccion = $_POST['direccion'] ?? '';
        $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';

        $error = '';
        $success = '';

        // Validar contraseñas
        if ($contrasena !== '' && $contrasena !== $contrasena_confirmar) {
            $error = 'Las contraseñas no coinciden.';
        }

        if (empty($error)) {
            // Actualizar datos del usuario
            $update_user = 'UPDATE usuarios SET nombre = :nombre, email = :email' .
                ($contrasena ? ', contrasena = :contrasena' : '') .
                ' WHERE id = :id';
            $stmt = $pdo->prepare($update_user);
            $params = [
                'nombre' => $nombre,
                'email' => $email,
                'id' => $_SESSION['usuario_id'],
            ];
            if ($contrasena) {
                $params['contrasena'] = password_hash($contrasena, PASSWORD_DEFAULT);
            }
            $stmt->execute($params);

            // Actualizar datos del paciente
            $stmt = $pdo->prepare('UPDATE pacientes SET telefono = :telefono, direccion = :direccion, fecha_nacimiento = :fecha_nacimiento WHERE usuario_id = :usuario_id');
            $stmt->execute([
                'telefono' => $telefono,
                'direccion' => $direccion,
                'fecha_nacimiento' => $fecha_nacimiento,
                'usuario_id' => $_SESSION['usuario_id'],
            ]);

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
                <a href="principal_doctores.php" class="nav-link">Inicio</a>
                <a href="ver_citas.php" class="nav-link">Ver Citas</a>
                <a href="ver_paciente.php" class="nav-link">Mis pacientes</a>
                <a href="seguimiento.php"class="nav-link">Seguimiento</a>
                <a href="ver_historial.php"class="nav-link">Historiales</a>
                <a href="perfil.php" class="nav-link">Mi Perfil</a>
                <a href="../logout.php" class="nav-link">Cerrar sesión</a>
            </nav>
        </div>
    </header>
    <main>
        <section class="container-principal">
            <h2>Mi Perfil</h2>
            <table class="table">
                <tbody>
                    <tr>
                        <th>Nombre:</th>
                        <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                    </tr>
                    <tr>
                        <th>Teléfono:</th>
                        <td><?php echo htmlspecialchars($paciente['telefono']); ?></td>
                    </tr>
                    <tr>
                        <th>Dirección:</th>
                        <td><?php echo htmlspecialchars($paciente['direccion']); ?></td>
                    </tr>
                    <tr>
                        <th>Fecha de Nacimiento:</th>
                        <td><?php echo htmlspecialchars($paciente['fecha_nacimiento']); ?></td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Clínica Dental del Dr. Fabián Mora. Todos los derechos reservados.</p>
        </div>
    </footer>
</body>

</html>