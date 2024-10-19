<?php
session_start();

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'doctor') {
    header('Location: ../login.php');
    exit;
}


require '../Conexion/conexion.php';


$doctor_id = $_SESSION['usuario_id'];


$stmt = $pdo->prepare('SELECT id FROM doctores WHERE usuario_id = :doctor_id');
$stmt->execute([':doctor_id' => $doctor_id]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
    die('El doctor no existe en la base de datos.');
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paciente_id = $_POST['paciente_id'];
    $descripcion = $_POST['descripcion'];
    $tratamiento = $_POST['tratamiento'];

    try {
        $stmt = $pdo->prepare('
            INSERT INTO historial_clinico (paciente_id, doctor_id, descripcion, tratamiento, fecha)
            VALUES (:paciente_id, :doctor_id, :descripcion, :tratamiento, NOW())
        ');
        $stmt->execute([
            ':paciente_id' => $paciente_id,
            ':doctor_id' => $doctor['id'],  
            ':descripcion' => $descripcion,
            ':tratamiento' => $tratamiento
        ]);

        header('Location: ver_historial.php?mensaje=Historial creado exitosamente');
        exit;
    } catch (PDOException $e) {
        $error = 'Error al crear el historial: ' . $e->getMessage();
    }
}
try {

    $stmt = $pdo->prepare('
        SELECT pacientes.id, usuarios.nombre 
        FROM pacientes
        JOIN usuarios ON pacientes.usuario_id = usuarios.id
    ');
    $stmt->execute();
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Error al obtener los pacientes: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Historial Clínico</title>
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
        <h2>Crear Historial Clínico</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="post" action="crear_historial.php">
            <div class="form-group">
                <label for="paciente_id">Paciente:</label>
                <select id="paciente_id" name="paciente_id" required>
                    <?php foreach ($pacientes as $paciente): ?>
                        <option value="<?php echo htmlspecialchars($paciente['id']); ?>">
                            <?php echo htmlspecialchars($paciente['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" required></textarea>
            </div>
            <div class="form-group">
                <label for="tratamiento">Tratamiento:</label>
                <textarea id="tratamiento" name="tratamiento" required></textarea>
            </div>
            <button type="submit" class="btn-actualizar">Guardar Historial</button>
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