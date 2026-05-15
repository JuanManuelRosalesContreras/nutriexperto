<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!estaLogueado()) {
    echo json_encode(['ok'=>false,'mensaje'=>'Inicia sesión para guardar recetas']);
    exit();
}
if (!esPremium()) {
    echo json_encode(['ok'=>false,'mensaje'=>'Función exclusiva Premium']);
    exit();
}

$data      = json_decode(file_get_contents('php://input'), true);
$recetaId  = intval($data['receta_id'] ?? 0);
$usuarioId = datosUsuario()['id'];

if (!$recetaId) {
    echo json_encode(['ok'=>false,'mensaje'=>'Receta inválida']);
    exit();
}

$conn = conectar();

// Verificar si ya está guardada
$check = $conn->prepare("SELECT id FROM recetas_guardadas WHERE usuario_id=? AND receta_id=?");
$check->bind_param('ii', $usuarioId, $recetaId);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(['ok'=>false,'mensaje'=>'Esta receta ya está guardada']);
    exit();
}

$stmt = $conn->prepare("INSERT INTO recetas_guardadas (usuario_id, receta_id) VALUES (?,?)");
$stmt->bind_param('ii', $usuarioId, $recetaId);

if ($stmt->execute()) {
    echo json_encode(['ok'=>true]);
} else {
    echo json_encode(['ok'=>false,'mensaje'=>'Error al guardar']);
}
?>