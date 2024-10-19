<?php
session_start();

// Verificar si el usuario está autenticado y tiene el rol de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'administrador') {
    echo json_encode(['success' => false, 'error' => 'No tienes permisos para realizar esta acción.']);
    exit;
}

// Conectar a la base de datos (ajusta la ruta de conexión si es necesario)
require '../Conexion/conexion.php';

// Obtener datos de la solicitud
$solicitud_id = $_POST['solicitud_id'] ?? null;
$accion = $_POST['accion'] ?? null;

if (!$solicitud_id || !$accion || !in_array($accion, ['aprobar', 'rechazar'])) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos.']);
    exit;
}

try {
    // Obtener la solicitud para verificar su estado
    $stmt = $pdo->prepare('SELECT * FROM solicitudes WHERE id = :solicitud_id');
    $stmt->execute([':solicitud_id' => $solicitud_id]);
    $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$solicitud) {
        echo json_encode(['success' => false, 'error' => 'No se encontró la solicitud.']);
        exit;
    }

    if ($solicitud['estado'] !== 'pendiente') {
        echo json_encode(['success' => false, 'error' => 'La solicitud ya ha sido procesada.']);
        exit;
    }

    if ($accion === 'aprobar') {
        // Actualizar el estado de la solicitud a 'aprobada'
        $stmt = $pdo->prepare('UPDATE solicitudes SET estado = "aprobada" WHERE id = :solicitud_id');
        $stmt->execute([':solicitud_id' => $solicitud_id]);

        // Procesar la solicitud según el tipo
        if ($solicitud['tipo'] === 'eliminacion') {
            // Eliminar la cita
            $stmt = $pdo->prepare('DELETE FROM citas WHERE id = :cita_id');
            $stmt->execute([':cita_id' => $solicitud['cita_id']]);
        } elseif ($solicitud['tipo'] === 'aplazamiento') {
            // Actualizar la cita a 'aplazada'
            $stmt = $pdo->prepare('UPDATE citas SET estado = "aplazada" WHERE id = :cita_id');
            $stmt->execute([':cita_id' => $solicitud['cita_id']]);
        }
    } elseif ($accion === 'rechazar') {
        // Actualizar el estado de la solicitud a 'rechazada'
        $stmt = $pdo->prepare('UPDATE solicitudes SET estado = "rechazada" WHERE id = :solicitud_id');
        $stmt->execute([':solicitud_id' => $solicitud_id]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error al procesar la solicitud: ' . $e->getMessage()]);
}
