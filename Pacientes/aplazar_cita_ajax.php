<?php
session_start();

// Verificar si el usuario está autenticado y tiene el rol de paciente
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'paciente') {
    echo json_encode(['success' => false, 'error' => 'No tienes permisos para realizar esta acción.']);
    exit;
}

// Conectar a la base de datos
require '../Conexion/conexion.php';

// Obtener datos de la solicitud
$cita_id = $_POST['cita_id'] ?? null;

if (!$cita_id) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos.']);
    exit;
}

try {
    // Insertar la solicitud de aplazamiento
    $stmt = $pdo->prepare('INSERT INTO solicitudes (cita_id, tipo, estado, fecha_solicitud) VALUES (:cita_id, "aplazamiento", "pendiente", NOW())');
    $stmt->execute([':cita_id' => $cita_id]);

    echo json_encode(['success' => true, 'message' => 'Solicitud de aplazamiento enviada con éxito.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error al procesar la solicitud: ' . $e->getMessage()]);
}
?>
