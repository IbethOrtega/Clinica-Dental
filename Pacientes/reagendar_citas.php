<?php
session_start();

// Verificar si el usuario está autenticado como paciente
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'paciente') {
    header('Location: ../login.php');
    exit;
}

// Conectar a la base de datos
require '../Conexion/conexion.php';

// Inicializar variables para mensajes de error y éxito
$mensaje = '';
$errores = [];

// Horarios disponibles
$horarios_disponibles = [
    '08:00:00' => '08:00 - 09:00',
    '09:00:00' => '09:00 - 10:00',
    '10:00:00' => '10:00 - 11:00',
    '11:00:00' => '11:00 - 12:00',
    '14:00:00' => '14:00 - 15:00',
    '15:00:00' => '15:00 - 16:00',
    '16:00:00' => '16:00 - 17:00',
    '17:00:00' => '17:00 - 18:00'
];

try {
    if (isset($_GET['cita_id'])) {
        $cita_id = $_GET['cita_id'];

        // Obtener datos de la cita
        $stmtCita = $pdo->prepare('
            SELECT c.id, c.usuario_id, c.doctor_id, c.fecha, c.hora, c.sede_id, c.especialidad_id, c.motivo, d.nombre AS doctor_nombre, s.nombre AS sede_nombre
            FROM citas c
            JOIN doctores d ON c.doctor_id = d.id
            JOIN sedes s ON c.sede_id = s.id
            WHERE c.id = :cita_id AND c.usuario_id = :usuario_id
        ');
        $stmtCita->execute([
            'cita_id' => $cita_id,
            'usuario_id' => $_SESSION['usuario_id']
        ]);
        $cita = $stmtCita->fetch(PDO::FETCH_ASSOC);

        if (!$cita) {
            $errores[] = 'La cita no existe o no está asociada a tu cuenta.';
        } else {
            // Procesar formulario si se ha enviado
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $fecha = $_POST['fecha'] ?? '';
                $hora = $_POST['hora'] ?? '';

                // Validar entradas
                if (empty($fecha) || empty($hora)) {
                    $errores[] = 'La fecha y la hora son obligatorias.';
                }

                // Validar formato de la fecha
                if (!DateTime::createFromFormat('Y-m-d', $fecha)) {
                    $errores[] = 'La fecha no tiene un formato válido (YYYY-MM-DD).';
                }

                // Validar formato de la hora
                if (!array_key_exists($hora, $horarios_disponibles)) {
                    $errores[] = 'La hora seleccionada no es válida.';
                }

                // Verificar si el doctor ya tiene una cita en el horario seleccionado
                if (empty($errores)) {
                    $stmtCitaExistente = $pdo->prepare('
                        SELECT COUNT(*) FROM citas 
                        WHERE doctor_id = :doctor_id AND fecha = :fecha AND hora = :hora AND id != :cita_id
                    ');
                    $stmtCitaExistente->execute([
                        'doctor_id' => $cita['doctor_id'],
                        'fecha' => $fecha,
                        'hora' => $hora,
                        'cita_id' => $cita_id
                    ]);
                    $citaExistente = $stmtCitaExistente->fetchColumn();

                    if ($citaExistente > 0) {
                        $errores[] = 'El doctor ya tiene una cita programada en este horario.';
                    } else {
                        // Actualizar la cita
                        $stmtActualizarCita = $pdo->prepare('
                            UPDATE citas 
                            SET fecha = :fecha, hora = :hora 
                            WHERE id = :cita_id
                        ');
                        $stmtActualizarCita->execute([
                            'fecha' => $fecha,
                            'hora' => $hora,
                            'cita_id' => $cita_id
                        ]);
                        $mensaje = 'Cita reagendada con éxito.';
                    }
                }
            }
        }
    } else {
        $errores[] = 'No se ha especificado una cita para reagendar.';
    }
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
    <title>Clínica Dental - Reagendar Cita</title>
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
            <h2>Reagendar Cita</h2>
            <?php if (!empty($errores)): ?>
                <div class="error">
                    <?php foreach ($errores as $error): ?>
                        <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if ($mensaje): ?>
                <div class="exito">
                    <p><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            <?php endif; ?>
            <form action="reagendar_citas.php?cita_id=<?php echo htmlspecialchars($cita_id, ENT_QUOTES, 'UTF-8'); ?>" method="post">
                <div class="form-group">
                    <label for="doctor_id">Doctor:</label>
                    <input type="text" id="doctor_id" value="<?php echo htmlspecialchars($cita['doctor_nombre'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="sede_id">Sede:</label>
                    <input type="text" id="sede_id" value="<?php echo htmlspecialchars($cita['sede_nombre'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="fecha">Fecha:</label>
                    <input type="date" id="fecha" name="fecha" value="<?php echo htmlspecialchars($cita['fecha'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="hora">Hora:</label>
                    <select id="hora" name="hora" required>
                        <?php foreach ($horarios_disponibles as $hora => $descripcion): ?>
                            <option value="<?php echo htmlspecialchars($hora, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $hora === $cita['hora'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-agendar-cita">Actualizar</button>
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
