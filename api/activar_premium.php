<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!estaLogueado()) {
    echo json_encode(['ok'=>false,'mensaje'=>'No autenticado']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$plan = $data['plan'] ?? 'mensual';
$uid  = datosUsuario()['id'];
$conn = conectar();

$stmt = $conn->prepare("UPDATE usuarios SET tipo='premium' WHERE id=?");
$stmt->bind_param('i', $uid);

if ($stmt->execute()) {
    // Actualizar sesión
    $_SESSION['tipo'] = 'premium';
    echo json_encode(['ok'=>true]);
} else {
    echo json_encode(['ok'=>false,'mensaje'=>'Error al actualizar']);
}
?>