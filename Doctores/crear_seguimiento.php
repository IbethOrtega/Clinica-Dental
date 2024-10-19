<?php
session_start();

// Verificar si el usuario está autenticado y tiene el rol de doctor
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'doctor') {
    header('Location: ../login.php');
    exit;
}

// Conectar a la base de datos
require '../Conexion/conexion.php';

$doctor_id = $_SESSION['usuario_id']; // Usar el usuario_id del doctor desde la sesión
$pacientes = [];
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

// Obtener la lista de pacientes asociados al doctor
try {
    $stmt = $pdo->prepare('
        SELECT paciente.id, paciente.nombre 
        FROM pacientes AS paciente
        JOIN citas ON paciente.id = citas.paciente_id
        JOIN doctores AS doctor ON citas.doctor_id = doctor.id
        WHERE doctor.id = :doctor_id
        GROUP BY paciente.id, paciente.nombre
    ');
    $stmt->bindParam(':doctor_id', $doctor_id, PDO::PARAM_INT);
    $stmt->execute();
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error en la consulta de pacientes: ' . $e->getMessage());
    $errores[] = 'Error al obtener los pacientes. Inténtelo de nuevo más tarde.';
}

// Procesar el formulario cuando se envía una solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paciente_id = $_POST['paciente_id'];
    $detalle = $_POST['detalle'];

    try {
        // Insertar el nuevo seguimiento
        $stmt = $pdo->prepare('
            INSERT INTO seguimientos (paciente_id, doctor_id, fecha, detalle)
            VALUES (:paciente_id, :doctor_id, NOW(), :detalle)
        ');
        $stmt->execute([
            ':paciente_id' => $paciente_id,
            ':doctor_id' => $doctor_id,
            ':detalle' => $detalle
        ]);

        // Redirigir a la página de seguimientos con un mensaje de éxito
        header('Location: ver_paciente.php?mensaje=Seguimiento creado exitosamente');
        exit;
    } catch (PDOException $e) {
        $error = 'Error al crear el seguimiento: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Seguimiento</title>
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
        <h2>Agregar Seguimiento</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <form method="post" action="crear_seguimiento.php">
            <div class="form-group">
                <label for="paciente_id">Paciente:</label>
                <select id="paciente_id" name="paciente_id" required>
                    <option value="">Selecciona un paciente</option>
                    <?php foreach ($pacientes as $paciente): ?>
                        <option value="<?php echo htmlspecialchars($paciente['id']); ?>">
                            <?php echo htmlspecialchars($paciente['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="detalle">Detalles del Seguimiento:</label>
                <textarea id="detalle" name="detalle" required></textarea>
            </div>
            <button type="submit" class="btn-Agendar">Guardar Seguimiento</button>
            <a href="ver_paciente.php" class="btn-cancelar">Cancelar</a>
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
