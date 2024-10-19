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
    SELECT citas.id, paciente.nombre AS paciente_nombre, doctor.nombre AS doctor_nombre, 
           citas.fecha, citas.hora, citas.estado, citas.motivo, citas.costo, sede.nombre AS sede_nombre,
           especialidad.nombre AS especialidad
    FROM citas
    JOIN pacientes AS paciente ON citas.paciente_id = paciente.id
    JOIN doctores AS doctor ON citas.doctor_id = doctor.id
    JOIN sedes AS sede ON citas.sede_id = sede.id
    JOIN especialidades AS especialidad ON citas.especialidad_id = especialidad.id
    WHERE doctor.usuario_id = :usuario_id
    ORDER BY citas.fecha, citas.hora
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
    <script>
        function marcarComoCompletada(citaId) {
            if (confirm("¿Estás seguro de que deseas marcar esta cita como completada?")) {
                const formData = new FormData();
                formData.append('cita_id', citaId);

                fetch('marcar_completada.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert(data.message);
                            document.querySelector(`#cita-${citaId} .estado`).textContent = 'completada';
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Hubo un error al procesar la solicitud.');
                    });
            }
        }
    </script>

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
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                        <th>Costo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($citas) > 0): ?>
                        <?php foreach ($citas as $cita): ?>
                            <tr id="cita-<?php echo htmlspecialchars($cita['id']); ?>">
                                <td><?php echo htmlspecialchars($cita['paciente_nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($cita['fecha'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($cita['hora'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($cita['motivo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="estado"><?php echo htmlspecialchars($cita['estado'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($cita['costo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="acciones">
                                    <a href="crear_historial.php?cita_id=<?php echo htmlspecialchars($cita['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn-Agendar">Crear Historial</a>
                                    <a href="realiza_cita.php?cita_id=<?php echo htmlspecialchars($cita['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn-Agendar">Ir a la cita</a>
                                </td>


                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No tienes citas agendadas.</td>
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