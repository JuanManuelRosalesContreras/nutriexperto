<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
date_default_timezone_set('America/Mexico_City');

if (!estaLogueado() || !esAdmin()) {
    header('Location: ../login.php');
    exit();
}

$tipo = $_GET['tipo'] ?? 'consultas';
$conn = conectar();

// Definir qué exportar
switch($tipo) {

    case 'consultas':
        $titulo  = 'Historial_Consultas';
        $headers = ['ID','Usuario','Alimentos','Recetas','Puntuación Salud','Fecha'];
        $result  = $conn->query("
            SELECT hc.id,
                   COALESCE(u.nombre,'Invitado') as usuario,
                   hc.alimentos_seleccionados,
                   hc.recetas_recomendadas,
                   hc.puntuacion_salud,
                   hc.fecha
            FROM historial_consultas hc
            LEFT JOIN usuarios u ON hc.usuario_id = u.id
            ORDER BY hc.fecha DESC
        ");
        break;

    case 'alimentos':
        $titulo  = 'Alimentos_Populares';
        $headers = ['ID','Alimento','Categoría','Calorías/100g','Total Selecciones','Última Actualización'];
        $result  = $conn->query("
            SELECT a.id, a.nombre, a.categoria, a.calorias_100g,
                   COALESCE(dw.total_selecciones,0) as total,
                   dw.fecha_actualizacion
            FROM alimentos a
            LEFT JOIN dw_alimentos_populares dw ON a.id = dw.alimento_id
            ORDER BY total DESC
        ");
        break;

    case 'recetas':
        $titulo  = 'Recetas_Populares';
        $headers = ['ID','Receta','Dificultad','Calorías','Premium','Total Recomendaciones'];
        $result  = $conn->query("
            SELECT r.id, r.nombre, r.dificultad, r.calorias_aprox,
                   IF(r.es_premium,'Sí','No') as premium,
                   COALESCE(dw.total_recomendaciones,0) as total
            FROM recetas r
            LEFT JOIN dw_recetas_populares dw ON r.id = dw.receta_id
            ORDER BY total DESC
        ");
        break;

    case 'usuarios':
        $titulo  = 'Usuarios';
        $headers = ['ID','Nombre','Email','Plan','Fecha Registro'];
        $result  = $conn->query("
            SELECT id, nombre, email, tipo, fecha_registro
            FROM usuarios
            WHERE tipo != 'admin'
            ORDER BY fecha_registro DESC
        ");
        break;

    default:
        die('Tipo no válido');
}

// ── GENERAR CSV ─────────────────────────────────────────────
$fecha    = date('Y-m-d');
$filename = "NutriExperto_{$titulo}_{$fecha}.csv";

header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// BOM para que Excel lo abra correctamente con acentos
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Encabezados
fputcsv($output, $headers, ',');

// Datos
while ($row = $result->fetch_assoc()) {

    // ── CONVERTIR IDS DE ALIMENTOS A NOMBRES ──
    if ($tipo === 'consultas') {

        // ALIMENTOS
        $idsAlimentos = json_decode($row['alimentos_seleccionados'], true);

        if (is_array($idsAlimentos) && count($idsAlimentos) > 0) {

            $ids = implode(',', array_map('intval', $idsAlimentos));

            $queryAlimentos = $conn->query("
                SELECT nombre
                FROM alimentos
                WHERE id IN ($ids)
            ");

            $nombresAlimentos = [];

            while ($a = $queryAlimentos->fetch_assoc()) {
                $nombresAlimentos[] = $a['nombre'];
            }

            $row['alimentos_seleccionados'] = implode(', ', $nombresAlimentos);
        }

        // RECETAS
        $idsRecetas = json_decode($row['recetas_recomendadas'], true);

        if (is_array($idsRecetas) && count($idsRecetas) > 0) {

            $ids = implode(',', array_map('intval', $idsRecetas));

            $queryRecetas = $conn->query("
                SELECT nombre
                FROM recetas
                WHERE id IN ($ids)
            ");

            $nombresRecetas = [];

            while ($r = $queryRecetas->fetch_assoc()) {
                $nombresRecetas[] = $r['nombre'];
            }

            $row['recetas_recomendadas'] = implode(', ', $nombresRecetas);
        }
    }

    fputcsv($output, array_values($row), ',');
}

fclose($output);
exit();
?>