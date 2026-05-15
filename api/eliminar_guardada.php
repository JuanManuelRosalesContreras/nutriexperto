<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!estaLogueado() || !esPremium()) {
    echo json_encode(['ok'=>false,'mensaje'=>'No autorizado']);
    exit();
}

$data      = json_decode(file_get_contents('php://input'), true);
$id        = intval($data['id'] ?? 0);
$usuarioId = datosUsuario()['id'];

if (!$id) {
    echo json_encode(['ok'=>false,'mensaje'=>'ID inválido']);
    exit();
}

$conn = conectar();
$stmt = $conn->prepare("DELETE FROM recetas_guardadas WHERE id=? AND usuario_id=?");
$stmt->bind_param('ii', $id, $usuarioId);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['ok'=>true]);
} else {
    echo json_encode(['ok'=>false,'mensaje'=>'No se pudo eliminar']);
}
?>