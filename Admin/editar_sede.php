<?php
session_start();

// Verificar si el usuario está autenticado y tiene el rol de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: ../login.php');
    exit;
}

// Conectar a la base de datos
require '../Conexion/conexion.php';

$errores = [];
$mensaje = "";

// Obtener el ID de la sede a editar
$id = $_GET['id'] ?? null;

if ($id) {
    // Obtener los datos de la sede para prellenar el formulario
    try {
        $stmt = $pdo->prepare('SELECT * FROM sedes WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $sede = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$sede) {
            $errores[] = 'Sede no encontrada.';
        }
    } catch (PDOException $e) {
        $errores[] = 'Error al obtener los datos de la sede: ' . $e->getMessage();
    }
} else {
    $errores[] = 'ID de sede no proporcionado.';
}

// Procesar formulario de edición de sede
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];

    if (empty($nombre) || empty($direccion)) {
        $errores[] = 'El nombre y la dirección son obligatorios.';
    }

    if (empty($errores)) {
        try {
            $stmt = $pdo->prepare('UPDATE sedes SET nombre = :nombre, direccion = :direccion, telefono = :telefono WHERE id = :id');
            $stmt->execute(['nombre' => $nombre, 'direccion' => $direccion, 'telefono' => $telefono, 'id' => $id]);
            $mensaje = 'Sede actualizada con éxito.';
        } catch (PDOException $e) {
            $errores[] = 'Error al actualizar la sede: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Sede</title>
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
        <div class="container-principal">
            <h2>Editar Sede</h2>
            <?php if ($mensaje): ?>
                <p class="exito"><?php echo htmlspecialchars($mensaje); ?></p>
            <?php endif; ?>
            <?php if ($errores): ?>
                <ul class="error">
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if ($sede): ?>
                <form action="editar_sede.php?id=<?php echo htmlspecialchars($id); ?>" method="post">
                    <div class="form-group">
                        <label for="nombre">Nombre:</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($sede['nombre']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="direccion">Dirección:</label>
                        <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($sede['direccion']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono:</label>
                        <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($sede['telefono']); ?>">
                    </div>
                    <button type="submit" class="btn-crear">Actualizar Sede</button>
                </form>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>