<?php
session_start();

// Verificar si el usuario está autenticado y tiene el rol de doctor
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'doctor') {
    header('Location: ../login.php');
    exit;
}

// Conectar a la base de datos
require '../Conexion/conexion.php';

$doctor_id = $_SESSION['usuario_id']; 
$citas = [];
$errores = [];

try {
    // Obtener el ID del usuario que inició sesión
    $usuario_id = $_SESSION['usuario_id'];

    // Consulta para obtener las citas del paciente que ha iniciado sesión
    $stmt = $pdo->prepare('
    SELECT citas.id, paciente.nombre AS paciente_nombre, paciente.telefono, paciente.email
    FROM citas
    JOIN pacientes AS paciente ON citas.paciente_id = paciente.id
    JOIN doctores AS doctor ON citas.doctor_id = doctor.id
    WHERE doctor.usuario_id = :usuario_id
    ORDER BY paciente.id
');
    // Enlazar el ID del usuario actual
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);

    // Ejecutar la consulta
    $stmt->execute();
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('Error en la consulta: ' . $e->getMessage());
    $errores[] = 'Error al procesar la solicitud. Inténtelo de nuevo más tarde.';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Citas</title>
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
            <h2>Mis Citas</h2>
            <?php if (!empty($errores)): ?>
                <div class="error">
                    <?php foreach ($errores as $error): ?>
                        <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Paciente</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($citas) > 0): ?>
                        <?php foreach ($citas as $cita):?>
                            <tr id="cita-<?php echo htmlspecialchars($cita['id']); ?>">
                                <td><?php echo htmlspecialchars($cita['paciente_nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($cita['telefono'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($cita['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                <a href="crear_seguimiento.php?paciente_id=<?php echo htmlspecialchars($cita['id']); ?>" class="btn-editar">Agregar Seguimiento</a>

                                </td>
                            </tr>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No tienes pacientes agendadas.</td>
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
