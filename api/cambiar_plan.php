<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
header('Content-Type: application/json');

// Solo para pruebas — quitar en producción
if (!estaLogueado()) {
    echo json_encode(['ok'=>false]);
    exit();
}

$data  = json_decode(file_get_contents('php://input'), true);
$plan  = in_array($data['plan'] ?? '', ['free','premium']) ? $data['plan'] : 'free';
$uid   = datosUsuario()['id'];
$conn  = conectar();

$stmt = $conn->prepare("UPDATE usuarios SET tipo=? WHERE id=?");
$stmt->bind_param('si', $plan, $uid);
$stmt->execute();
$_SESSION['tipo'] = $plan;

echo json_encode(['ok'=>true, 'plan'=>$plan]);
?>