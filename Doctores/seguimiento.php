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
$seguimientos = [];
$errores = [];

// Obtener el ID del doctor desde la base de datos
try {
    $stmt = $pdo->prepare('
        SELECT id FROM doctores WHERE usuario_id = :usuario_id
    ');
    $stmt->bindParam(':usuario_id', $doctor_id, PDO::PARAM_INT);
    $stmt->execute();
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($doctor) {
        $doctor_id = $doctor['id'];
    } else {
        $errores[] = 'No se encontró el ID del doctor.';
    }
} catch (PDOException $e) {
    error_log('Error al obtener el ID del doctor: ' . $e->getMessage());
    $errores[] = 'Error al obtener el ID del doctor. Inténtelo de nuevo más tarde.';
}

// Obtener todos los seguimientos del doctor
try {
    $stmt = $pdo->prepare('
        SELECT seguimientos.id, paciente.nombre AS paciente_nombre, seguimientos.fecha, seguimientos.detalle
        FROM seguimientos
        JOIN pacientes AS paciente ON seguimientos.paciente_id = paciente.id
        WHERE seguimientos.doctor_id = :doctor_id
        ORDER BY seguimientos.fecha DESC
    ');
    $stmt->bindParam(':doctor_id', $doctor_id, PDO::PARAM_INT);
    $stmt->execute();
    $seguimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error en la consulta de seguimientos: ' . $e->getMessage());
    $errores[] = 'Error al obtener los seguimientos. Inténtelo de nuevo más tarde.';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Seguimientos</title>
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
        <h2>Mis Seguimientos</h2>
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
                    <th>Fecha</th>
                    <th>Detalles</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($seguimientos) > 0): ?>
                    <?php foreach ($seguimientos as $seguimiento): ?>
                        <tr id="seguimiento-<?php echo htmlspecialchars($seguimiento['id']); ?>">
                            <td><?php echo htmlspecialchars($seguimiento['paciente_nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($seguimiento['fecha'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($seguimiento['detalle'], ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No hay seguimientos registrados.</td>
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
