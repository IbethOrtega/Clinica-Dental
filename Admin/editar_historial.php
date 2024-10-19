<?php
session_start();

// Verificar si el usuario está autenticado y tiene el rol de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: ../login.php');
    exit;
}

// Conectar a la base de datos
require '../Conexion/conexion.php';

// Verificar si se ha enviado un ID válido
$id = $_GET['id'] ?? null;

if (!$id) {
    die('ID de historial clínico no proporcionado.');
}

// Procesar la actualización del historial clínico si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = $_POST['fecha'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $tratamiento = $_POST['tratamiento'] ?? '';
    $doctor_id = $_POST['doctor_id'] ?? null;
    $paciente_id = $_POST['paciente_id'] ?? null;

    if ($fecha && $descripcion && $tratamiento && $doctor_id && $paciente_id) {
        try {
            $stmt = $pdo->prepare('UPDATE historial_clinico 
                                   SET fecha = :fecha, descripcion = :descripcion, tratamiento = :tratamiento, doctor_id = :doctor_id, paciente_id = :paciente_id 
                                   WHERE id = :id');
            $stmt->execute([
                ':fecha' => $fecha,
                ':descripcion' => $descripcion,
                ':tratamiento' => $tratamiento,
                ':doctor_id' => $doctor_id,
                ':paciente_id' => $paciente_id,
                ':id' => $id
            ]);
            header('Location: ver_historial.php');
            exit;
        } catch (PDOException $e) {
            die('Error al actualizar el historial clínico: ' . $e->getMessage());
        }
    } else {
        echo 'Por favor, completa todos los campos.';
    }
}

// Obtener los detalles del historial clínico junto con el nombre del paciente
try {
    $stmt = $pdo->prepare('SELECT hc.*, p.nombre AS nombre_paciente 
                          FROM historial_clinico hc 
                          JOIN pacientes p ON hc.paciente_id = p.id 
                          WHERE hc.id = :id');
    $stmt->execute([':id' => $id]);
    $historial = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$historial) {
        die('Historial clínico no encontrado.');
    }
    
    // Obtener lista de doctores
    $stmtDoctores = $pdo->query('SELECT id, nombre FROM doctores');
    $doctores = $stmtDoctores->fetchAll(PDO::FETCH_ASSOC);

    // Obtener lista de pacientes
    $stmtPacientes = $pdo->query('SELECT id, nombre FROM pacientes');
    $pacientes = $stmtPacientes->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die('Error al obtener datos: ' . $e->getMessage());
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
            <h2>Editar Historial Clínico</h2>
            <form method="post" action="editar_historial.php?id=<?php echo htmlspecialchars($id); ?>">
                <div class="form-group">
                    <label for="fecha">Fecha:</label>
                    <input type="date" id="fecha" name="fecha" value="<?php echo htmlspecialchars($historial['fecha']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" required><?php echo htmlspecialchars($historial['descripcion']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="tratamiento">Tratamiento:</label>
                    <textarea id="tratamiento" name="tratamiento" required><?php echo htmlspecialchars($historial['tratamiento']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="doctor_id">Doctor:</label>
                    <select id="doctor_id" name="doctor_id" required>
                        <?php foreach ($doctores as $doctor): ?>
                            <option value="<?php echo htmlspecialchars($doctor['id']); ?>" <?php echo $doctor['id'] == $historial['doctor_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($doctor['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
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
                <button type="submit" class="btn-actualizar">Guardar Cambios</button>
                <a href="ver_historial.php?id=<?php echo htmlspecialchars($historial['id']); ?>" class="btn-cancelar2">Cancelar</a>
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
