<?php
session_start();

// Verificar si el usuario está autenticado y tiene el rol de doctor
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'doctor') {
    header('Location: ../login.php');
    exit;
}

// Conectar a la base de datos
require '../Conexion/conexion.php';

// Obtener el ID del historial de la URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die('ID de historial no válido.');
}

try {
    // Obtener el historial clínico
    $stmt = $pdo->prepare('
        SELECT * FROM historial_clinico WHERE id = :id
    ');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $historial = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$historial) {
        die('El historial clínico no existe.');
    }

    // Obtener el usuario_id del doctor asociado al historial
    $doctor_id = $historial['doctor_id'];
    $stmt = $pdo->prepare('
        SELECT usuario_id FROM doctores WHERE id = :doctor_id
    ');
    $stmt->bindParam(':doctor_id', $doctor_id, PDO::PARAM_INT);
    $stmt->execute();
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doctor || $doctor['usuario_id'] !== $_SESSION['usuario_id']) {
        die('El historial clínico no le pertenece a este doctor.');
    }
    
    // Procesar el formulario cuando se envía una solicitud POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $descripcion = $_POST['descripcion'];
        $tratamiento = $_POST['tratamiento'];
        $paciente_id = $_POST['paciente_id'];

        try {
            $stmt = $pdo->prepare('
                UPDATE historial_clinico
                SET paciente_id = :paciente_id, descripcion = :descripcion, tratamiento = :tratamiento
                WHERE id = :id
            ');
            $stmt->execute([
                ':paciente_id' => $paciente_id,
                ':descripcion' => $descripcion,
                ':tratamiento' => $tratamiento,
                ':id' => $id
            ]);

            // Redirigir a la página de historial clínico con un mensaje de éxito
            header('Location: ver_historial.php?mensaje=Historial actualizado exitosamente');
            exit;
        } catch (PDOException $e) {
            $error = 'Error al actualizar el historial: ' . $e->getMessage();
        }
    }

    // Obtener la lista de pacientes
    $stmt = $pdo->prepare('
        SELECT pacientes.id, usuarios.nombre
        FROM pacientes
        JOIN usuarios ON pacientes.usuario_id = usuarios.id
    ');
    $stmt->execute();
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die('Error al obtener el historial clínico: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Historial Clínico</title>
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
        <h2>Editar Historial Clínico</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="post" action="editar_historial.php?id=<?php echo htmlspecialchars($id); ?>">
            <div class="form-group">
                <label for="paciente_id">Paciente:</label>
                <select id="paciente_id" name="paciente_id" required>
                    <?php foreach ($pacientes as $paciente): ?>
                        <option value="<?php echo htmlspecialchars($paciente['id']); ?>" <?php echo $paciente['id'] == $historial['paciente_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($paciente['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" required><?php echo htmlspecialchars($historial['descripcion']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="tratamiento">Tratamiento:</label>
                <textarea id="tratamiento" name="tratamiento" required><?php echo htmlspecialchars($historial['tratamiento']); ?></textarea>
            </div>
            <button type="submit" class="btn-actualizar">Guardar Cambios</button>
            <a href="ver_historial_clinico.php" class="btn-cancelar2">Cancelar</a>
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
