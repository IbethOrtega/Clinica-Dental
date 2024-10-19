<?php
session_start();

// Verificar si el usuario está autenticado y tiene el rol de paciente
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'paciente') {
    header('Location: ../login.php');
    exit;
}

// Conectar a la base de datos (ajusta la ruta de conexión si es necesario)
require '../Conexion/conexion.php';

try {
    // Obtener el historial clínico del paciente
    $stmt = $pdo->prepare('SELECT historial_clinico.id, historial_clinico.fecha, historial_clinico.descripcion, historial_clinico.tratamiento, doctores.nombre AS doctor
                           FROM historial_clinico
                           JOIN doctores ON historial_clinico.doctor_id = doctores.id
                           WHERE historial_clinico.paciente_id = :paciente_id
                           ORDER BY historial_clinico.fecha DESC');
    $stmt->execute(['paciente_id' => $_SESSION['usuario_id']]);
    $historiales = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Error en la consulta: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Clínico</title>
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
                <a href="principal_pacientes.php" class="nav-link">Inicio</a>
                <a href="ver_citas.php" class="nav-link">Ver Citas</a>
                <a href="ver_historial.php" class="nav-link">Historial Clínico</a>
                <a href="perfil.php" class="nav-link">Mi Perfil</a>
                <a href="../logout.php" class="nav-link">Cerrar sesión</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="container-principal">
            <h2>Mi Historial Clínico</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Descripción</th>
                        <th>Tratamiento</th>
                        <th>Doctor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($historiales) > 0): ?>
                        <?php foreach ($historiales as $historial): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($historial['fecha']); ?></td>
                                <td><?php echo htmlspecialchars($historial['descripcion']); ?></td>
                                <td><?php echo htmlspecialchars($historial['tratamiento']); ?></td>
                                <td><?php echo htmlspecialchars($historial['doctor']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No tienes historial clínico disponible.</td>
                        </tr>
                    <?php endif; ?>
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
