<?php
session_start();
require './Conexion/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $contrasena = $_POST['contrasena'];

    // Verificar que la conexión se haya establecido
    if (!$pdo) {
        die('No se pudo conectar a la base de datos.');
    }

    // Consulta para verificar las credenciales del usuario
    try {
        $stmt = $pdo->prepare('SELECT id, rol, contrasena FROM usuarios WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && $contrasena === $usuario['contrasena']) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['rol'] = $usuario['rol'];

            // Redirigir según el rol del usuario
            switch ($usuario['rol']) {
                case 'paciente':
                    header('Location: ./Pacientes/principal_pacientes.php');
                    break;
                case 'doctor':
                    header('Location: ./Doctores/principal_doctores.php');
                    break;
                case 'administrador':
                    header('Location: ./Admin/principalAdmin.php');
                    break;
                default:
                    echo 'Rol desconocido.';
            }
            exit;
        } else {
            $error = 'Credenciales incorrectas.';
        }
    } catch (PDOException $e) {
        $error = 'Error en la consulta: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clínica Dental - Iniciar sesión</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
<header class="header">
        <div class="container">
            <h1 class="logo">
                <img src="Imagenes/odontologia.png" alt="Logo de Clínica Odontológica">
                Clínica Dental
            </h1>
            <nav class="nav">
                <a href="index.php" class="nav-link">Inicio</a>
                <a href="login.php" class="nav-link">Iniciar sesión</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="container-cuenta">
            <h2>Iniciar sesión</h2>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <form action="login.php" method="post">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="contrasena">Contraseña:</label>
                <input type="password" id="contrasena" name="contrasena" required>

                <button type="submit">Iniciar sesión</button>
            </form>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Clínica Dental del Dr. Fabián Mora. Todos los derechos reservados.</p>
        </div>
    </footer>
</body>
</html>
