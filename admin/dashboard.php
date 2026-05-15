<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

date_default_timezone_set('America/Mexico_City');

// Solo admin puede entrar
if (!estaLogueado() || !esAdmin()) {
    header('Location: ../login.php');
    exit();
}

$conn = conectar();

// ── ESTADÍSTICAS GENERALES ──────────────────────────────────
$totalUsuarios   = $conn->query("SELECT COUNT(*) as t FROM usuarios WHERE tipo != 'admin'")->fetch_assoc()['t'];
$totalFree       = $conn->query("SELECT COUNT(*) as t FROM usuarios WHERE tipo='free'")->fetch_assoc()['t'];
$totalPremium    = $conn->query("SELECT COUNT(*) as t FROM usuarios WHERE tipo='premium'")->fetch_assoc()['t'];
$totalConsultas  = $conn->query("SELECT COUNT(*) as t FROM historial_consultas")->fetch_assoc()['t'];
$totalRecetas    = $conn->query("SELECT COUNT(*) as t FROM recetas")->fetch_assoc()['t'];
$promedioSalud   = $conn->query("SELECT ROUND(AVG(puntuacion_salud),1) as t FROM historial_consultas")->fetch_assoc()['t'];

// ── CONSULTAS POR DÍA (últimos 14 días) ────────────────────
$consultasDia = $conn->query("
    SELECT DATE(fecha) as dia, COUNT(*) as total
    FROM historial_consultas
    WHERE fecha >= DATE_SUB(NOW(), INTERVAL 14 DAY)
    GROUP BY DATE(fecha)
    ORDER BY dia ASC
")->fetch_all(MYSQLI_ASSOC);

// ── TOP 10 ALIMENTOS MÁS SELECCIONADOS ─────────────────────
$topAlimentos = $conn->query("
    SELECT a.nombre, a.emoji, a.categoria,
           COALESCE(dw.total_selecciones, 0) as total
    FROM alimentos a
    LEFT JOIN dw_alimentos_populares dw ON a.id = dw.alimento_id
    ORDER BY total DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// ── TOP 10 RECETAS MÁS RECOMENDADAS ────────────────────────
$topRecetas = $conn->query("
    SELECT r.nombre, r.dificultad, r.es_premium,
           COALESCE(dw.total_recomendaciones, 0) as total
    FROM recetas r
    LEFT JOIN dw_recetas_populares dw ON r.id = dw.receta_id
    ORDER BY total DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// ── DISTRIBUCIÓN POR CATEGORÍA DE ALIMENTO ─────────────────
$categorias = $conn->query("
    SELECT a.categoria,
           COUNT(DISTINCT dw.alimento_id) as variedad,
           COALESCE(SUM(dw.total_selecciones), 0) as total_selecciones
    FROM alimentos a
    LEFT JOIN dw_alimentos_populares dw ON a.id = dw.alimento_id
    GROUP BY a.categoria
    ORDER BY total_selecciones DESC
")->fetch_all(MYSQLI_ASSOC);

// ── USUARIOS RECIENTES ──────────────────────────────────────
$usuariosRecientes = $conn->query("
    SELECT nombre, email, tipo, fecha_registro
    FROM usuarios
    WHERE tipo != 'admin'
    ORDER BY fecha_registro DESC
    LIMIT 8
")->fetch_all(MYSQLI_ASSOC);

// ── PUNTUACIÓN PROMEDIO POR DÍA ─────────────────────────────
$puntuacionDia = $conn->query("
    SELECT DATE(fecha) as dia, ROUND(AVG(puntuacion_salud),1) as promedio
    FROM historial_consultas
    WHERE fecha >= DATE_SUB(NOW(), INTERVAL 14 DAY)
    GROUP BY DATE(fecha)
    ORDER BY dia ASC
")->fetch_all(MYSQLI_ASSOC);

// ── INGRESOS SIMULADOS ──────────────────────────────────────
$ingresosSimulados = $totalPremium * 49;

// Preparar datos para gráficas
$diasLabels      = array_column($consultasDia, 'dia');
$diasData        = array_column($consultasDia, 'total');
$puntLabels      = array_column($puntuacionDia, 'dia');
$puntData        = array_column($puntuacionDia, 'promedio');
$catLabels       = array_column($categorias, 'categoria');
$catData         = array_column($categorias, 'total_selecciones');
$alimentoLabels  = array_map(fn($a) => $a['emoji'].' '.$a['nombre'], $topAlimentos);
$alimentoData    = array_column($topAlimentos, 'total');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — NutriExperto</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --verde:      #2d6a4f;
            --verde-mid:  #40916c;
            --verde-light:#74c69d;
            --crema:      #fefae0;
            --naranja:    #e76f51;
            --dorado:     #f4a261;
            --cafe:       #6d4c41;
            --sidebar-w:  260px;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'DM Sans',sans-serif; background:#f0f2f5; display:flex; min-height:100vh; }

        /* ── SIDEBAR ── */
        .sidebar {
            width:var(--sidebar-w);
            background:linear-gradient(180deg, var(--verde) 0%, #1a4231 100%);
            height:100vh;
            position:fixed;
            left:0;
            top:0;
            display:flex;
            flex-direction:column;
            z-index:100;
            box-shadow:4px 0 20px rgba(0,0,0,.15);

            overflow-y:auto;
            overflow-x:hidden;
        }
        .sidebar-brand {
            padding:2rem 1.5rem 1.5rem;
            border-bottom:1px solid rgba(255,255,255,.1);
        }
        .sidebar-brand h4 {
            font-family:'Playfair Display',serif;
            color:white;
            font-size:1.3rem;
            margin-bottom:.2rem;
        }
        .sidebar-brand h4 span { color:var(--dorado); }
        .sidebar-brand small { color:rgba(255,255,255,.5); font-size:.78rem; }
        .sidebar-nav { padding:1.5rem 1rem; flex:1; }
        .sidebar-section {
            font-size:.7rem;
            color:rgba(255,255,255,.4);
            text-transform:uppercase;
            letter-spacing:1.5px;
            padding:.5rem .5rem .3rem;
            margin-top:1rem;
        }
        .sidebar-item {
            display:flex;
            align-items:center;
            gap:.8rem;
            padding:.75rem 1rem;
            border-radius:12px;
            color:rgba(255,255,255,.75);
            text-decoration:none;
            font-size:.92rem;
            font-weight:500;
            transition:all .2s;
            margin-bottom:.2rem;
            cursor:pointer;
            border:none;
            background:none;
            width:100%;
            text-align:left;
        }
        .sidebar-item:hover,
        .sidebar-item.activo {
            background:rgba(255,255,255,.12);
            color:white;
        }
        .sidebar-item.activo { background:rgba(116,198,157,.2); color:var(--verde-light); }
        .sidebar-item i { font-size:1.1rem; width:20px; flex-shrink:0; }
        .sidebar-footer {
            padding:1rem 1.5rem 1.5rem;
            border-top:1px solid rgba(255,255,255,.1);
        }
        .sidebar-footer a {
            color:rgba(255,255,255,.5);
            font-size:.85rem;
            text-decoration:none;
            display:flex;
            align-items:center;
            gap:.5rem;
        }
        .sidebar-footer a:hover { color:white; }

        /* ── MAIN ── */
        .main-content {
            margin-left:var(--sidebar-w);
            flex:1;
            padding:2rem;
            min-height:100vh;
        }

        .sidebar::-webkit-scrollbar {
            width:6px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background:rgba(255,255,255,.2);
            border-radius:10px;
        }

        /* ── TOPBAR ── */
        .topbar {
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:2rem;
            flex-wrap:wrap;
            gap:1rem;
        }
        .topbar-title {
            font-family:'Playfair Display',serif;
            font-size:1.8rem;
            color:#1a1a2e;
        }
        .topbar-title span { color:var(--verde); }
        .topbar-fecha {
            background:white;
            border-radius:10px;
            padding:.5rem 1rem;
            font-size:.85rem;
            color:#888;
            box-shadow:0 2px 10px rgba(0,0,0,.06);
        }

        /* ── STAT CARDS ── */
        .stat-card {
            background:white;
            border-radius:18px;
            padding:1.2rem;
            box-shadow:0 4px 20px rgba(0,0,0,.06);
            display:flex;
            align-items:center;
            gap:.8rem;
            transition:transform .2s;
            height:100%;
            overflow:hidden;
        }
        .stat-card:hover { transform:translateY(-3px); }
        .stat-num {
            font-family:'Playfair Display',serif;
            font-size:1.6rem;
            font-weight:900;
            color:#1a1a2e;
            line-height:1;
        }
        .ic-verde     { background:#f0faf5; }
        .ic-dorado    { background:#fff8f0; }
        .ic-naranja   { background:#fff3f0; }
        .ic-azul      { background:#f0f4ff; }
        .ic-morado    { background:#f8f0ff; }
        .ic-cafe      { background:#fdf5f0; }
        .stat-info { flex:1; }
        .stat-num {
            font-family:'Playfair Display',serif;
            font-size:2rem;
            font-weight:900;
            color:#1a1a2e;
            line-height:1;
        }
        .stat-label {
            font-size:.72rem;
            color:#aaa;
            text-transform:uppercase;
            letter-spacing:1px;
            margin-top:.2rem;
            line-height:1.3;
        }
        .stat-icon-box {
            width:48px; height:48px;
            border-radius:12px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:1.3rem;
            flex-shrink:0;
        }
        .stat-change { font-size:.78rem; font-weight:600; margin-top:.3rem; }
        .change-up   { color:var(--verde); }
        .change-down { color:var(--naranja); }

        /* ── CHART CARDS ── */
        .chart-card {
            background:white;
            border-radius:18px;
            padding:1.5rem;
            box-shadow:0 4px 20px rgba(0,0,0,.06);
            height:100%;
        }
        .chart-title {
            font-family:'Playfair Display',serif;
            font-size:1.1rem;
            color:#1a1a2e;
            margin-bottom:1.2rem;
            display:flex;
            align-items:center;
            gap:.5rem;
        }
        .chart-title span { font-size:1.2rem; }

        /* ── TABLA ── */
        .tabla-card {
            background:white;
            border-radius:18px;
            padding:1.5rem;
            box-shadow:0 4px 20px rgba(0,0,0,.06);
        }
        .tabla-header {
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:1.2rem;
        }
        .tabla-title {
            font-family:'Playfair Display',serif;
            font-size:1.1rem;
            color:#1a1a2e;
        }
        .tabla-admin {
            width:100%;
            border-collapse:collapse;
            font-size:.88rem;
        }
        .tabla-admin thead th {
            padding:.75rem 1rem;
            text-align:left;
            font-size:.75rem;
            color:#aaa;
            text-transform:uppercase;
            letter-spacing:1px;
            border-bottom:2px solid #f0f0f0;
            font-weight:600;
        }
        .tabla-admin tbody td {
            padding:.85rem 1rem;
            color:#444;
            border-bottom:1px solid #f8f8f8;
            vertical-align:middle;
        }
        .tabla-admin tbody tr:last-child td { border-bottom:none; }
        .tabla-admin tbody tr:hover td { background:#fafafa; }
        .badge-tipo {
            border-radius:50px;
            padding:.2rem .7rem;
            font-size:.72rem;
            font-weight:700;
        }
        .badge-free    { background:#f0f0f0; color:#888; }
        .badge-premium { background:#fff8f0; color:var(--dorado); border:1px solid var(--dorado); }
        .badge-admin   { background:#f0faf5; color:var(--verde); border:1px solid var(--verde); }

        /* ── TOP LIST ── */
        .top-item {
            display:flex;
            align-items:center;
            gap:1rem;
            padding:.7rem 0;
            border-bottom:1px solid #f5f5f5;
        }
        .top-item:last-child { border-bottom:none; }
        .top-rank {
            width:28px; height:28px;
            border-radius:8px;
            background:var(--crema);
            color:var(--cafe);
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:.78rem;
            font-weight:700;
            flex-shrink:0;
        }
        .top-rank.gold   { background:#fff8f0; color:var(--dorado); }
        .top-rank.silver { background:#f8f8f8; color:#aaa; }
        .top-rank.bronze { background:#fdf5f0; color:#cd7f32; }
        .top-nombre { flex:1; font-size:.88rem; font-weight:500; color:#333; }
        .top-bar-wrap { width:80px; }
        .top-bar {
            height:6px;
            border-radius:3px;
            background:var(--verde-light);
        }
        .top-num { font-size:.85rem; font-weight:700; color:var(--verde); min-width:30px; text-align:right; }

        /* ── SECCIONES ── */
        .seccion { display:none; }
        .seccion.activa { display:block; }

        /* ── KPI PREMIUM ── */
        .kpi-premium {
            background:linear-gradient(135deg,#7b4f12,var(--dorado));
            border-radius:18px;
            padding:2rem;
            color:white;
            text-align:center;
        }
        .kpi-premium .monto {
            font-family:'Playfair Display',serif;
            font-size:3rem;
            font-weight:900;
        }

        @media(max-width:992px) {
            .sidebar { transform:translateX(-100%); }
            .main-content { margin-left:0; }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <h4>Nutri<span>Experto</span></h4>
        <small>Panel de administración</small>
    </div>
    <nav class="sidebar-nav">
        <div class="sidebar-section">Principal</div>
        <button class="sidebar-item activo" onclick="mostrarSeccion('general', this)">
            <i class="bi bi-grid-1x2-fill"></i> Panel principal
        </button>
        <button class="sidebar-item" onclick="mostrarSeccion('usuarios', this)">
            <i class="bi bi-people-fill"></i> Usuarios
        </button>

        <div class="sidebar-section">Data Warehouse</div>
        <button class="sidebar-item" onclick="mostrarSeccion('alimentos', this)">
            <i class="bi bi-basket2-fill"></i> Alimentos populares
        </button>
        <button class="sidebar-item" onclick="mostrarSeccion('recetas', this)">
            <i class="bi bi-journal-richtext"></i> Recetas populares
        </button>
        <button class="sidebar-item" onclick="mostrarSeccion('consultas', this)">
            <i class="bi bi-graph-up-arrow"></i> Consultas y tendencias
        </button>

        <div class="sidebar-section">Negocio</div>
        <button class="sidebar-item" onclick="mostrarSeccion('ingresos', this)">
            <i class="bi bi-cash-stack"></i> Ingresos Premium
        </button>
    </nav>
    <div class="sidebar-footer">
        <a href="../index.php"><i class="bi bi-arrow-left-circle"></i> Volver al sitio</a>
    </div>
</aside>

<!-- MAIN -->
<main class="main-content">
    <div class="topbar">
        <div>
            <div class="topbar-title">Panel de <span>administración</span></div>
            <div style="font-size:.85rem; color:#aaa">NutriExperto — Sistema de recomendación de recetas saludables</div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="dropdown">
                <button class="btn btn-success rounded-pill px-3 dropdown-toggle" 
                        data-bs-toggle="dropdown" style="font-size:.88rem; font-weight:600">
                    <i class="bi bi-download"></i> Exportar datos
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                    <li>
                        <a class="dropdown-item py-2" href="exportar.php?tipo=consultas">
                            📋 Historial de consultas (.csv)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="exportar.php?tipo=alimentos">
                            🛒 Alimentos populares (.csv)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="exportar.php?tipo=recetas">
                            🍽️ Recetas populares (.csv)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="exportar.php?tipo=usuarios">
                            👥 Usuarios registrados (.csv)
                        </a>
                    </li>
                </ul>
            </div>
            <div class="topbar-fecha">
                <i class="bi bi-calendar3"></i> <?= date('d/m/Y h:i A') ?>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         SECCIÓN: GENERAL
    ══════════════════════════════════════════════ -->
    <div class="seccion activa" id="sec-general">

        <!-- KPIs principales -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-lg-2">
                <div class="stat-card">
                    <div class="stat-icon-box ic-verde">👥</div>
                    <div class="stat-info">
                        <div class="stat-num"><?= $totalUsuarios ?></div>
                        <div class="stat-label">Usuarios</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="stat-card">
                    <div class="stat-icon-box ic-dorado">⭐</div>
                    <div class="stat-info">
                        <div class="stat-num"><?= $totalPremium ?></div>
                        <div class="stat-label">Premium</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="stat-card">
                    <div class="stat-icon-box ic-azul">🆓</div>
                    <div class="stat-info">
                        <div class="stat-num"><?= $totalFree ?></div>
                        <div class="stat-label">Gratuitos</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="stat-card">
                    <div class="stat-icon-box ic-naranja">🔍</div>
                    <div class="stat-info">
                        <div class="stat-num"><?= $totalConsultas ?></div>
                        <div class="stat-label">Consultas</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="stat-card">
                    <div class="stat-icon-box ic-morado">🍽️</div>
                    <div class="stat-info">
                        <div class="stat-num"><?= $totalRecetas ?></div>
                        <div class="stat-label">Recetas</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="stat-card">
                    <div class="stat-icon-box ic-cafe">📊</div>
                    <div class="stat-info">
                        <div class="stat-num"><?= $promedioSalud ?></div>
                        <div class="stat-label">Prom. salud</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficas principales -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="chart-card">
                    <div class="chart-title"><span>📈</span> Consultas diarias (últimos 14 días)</div>
                    <canvas id="graficaConsultas" height="100"></canvas>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="chart-card">
                    <div class="chart-title"><span>🥧</span> Usuarios por plan</div>
                    <canvas id="graficaPlanes" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Top alimentos y recetas -->
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="chart-card">
                    <div class="chart-title"><span>🛒</span> Top ingredientes seleccionados</div>
                    <?php foreach($topAlimentos as $i => $al): ?>
                    <div class="top-item">
                        <div class="top-rank <?= $i==0?'gold':($i==1?'silver':($i==2?'bronze':'')) ?>">
                            <?= $i+1 ?>
                        </div>
                        <div class="top-nombre"><?= $al['emoji'] ?> <?= htmlspecialchars($al['nombre']) ?></div>
                        <div class="top-bar-wrap">
                            <div class="top-bar" style="width:<?= $alimentoData[0]>0 ? round(($al['total']/$alimentoData[0])*100) : 0 ?>%"></div>
                        </div>
                        <div class="top-num"><?= $al['total'] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-card">
                    <div class="chart-title"><span>🍽️</span> Top recetas recomendadas</div>
                    <?php
                    $maxReceta = !empty($topRecetas) ? $topRecetas[0]['total'] : 1;
                    foreach($topRecetas as $i => $rec):
                    ?>
                    <div class="top-item">
                        <div class="top-rank <?= $i==0?'gold':($i==1?'silver':($i==2?'bronze':'')) ?>">
                            <?= $i+1 ?>
                        </div>
                        <div class="top-nombre">
                            <?= htmlspecialchars($rec['nombre']) ?>
                            <?php if($rec['es_premium']): ?>
                                <span style="font-size:.7rem; color:var(--dorado)">⭐</span>
                            <?php endif; ?>
                        </div>
                        <div class="top-bar-wrap">
                            <div class="top-bar" style="width:<?= $maxReceta>0 ? round(($rec['total']/$maxReceta)*100) : 0 ?>%; background:var(--dorado)"></div>
                        </div>
                        <div class="top-num" style="color:var(--dorado)"><?= $rec['total'] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         SECCIÓN: USUARIOS
    ══════════════════════════════════════════════ -->
    <div class="seccion" id="sec-usuarios">
        <div class="tabla-card">
            <div class="tabla-header">
                <div class="tabla-title">👥 Usuarios registrados</div>
                <span style="font-size:.85rem; color:#aaa">Últimos 8 registros</span>
            </div>
            <div class="table-responsive">
                <table class="tabla-admin">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Plan</th>
                            <th>Registro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($usuariosRecientes as $u): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($u['nombre']) ?></strong></td>
                            <td style="color:#888"><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <span class="badge-tipo badge-<?= $u['tipo'] ?>">
                                    <?= $u['tipo'] === 'premium' ? '⭐ Premium' : '🆓 Free' ?>
                                </span>
                            </td>
                            <td style="color:#aaa; font-size:.83rem">
                                <?= date('d/m/Y', strtotime($u['fecha_registro'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         SECCIÓN: ALIMENTOS
    ══════════════════════════════════════════════ -->
    <div class="seccion" id="sec-alimentos">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="chart-card">
                    <div class="chart-title"><span>📊</span> Selecciones por alimento (Top 10)</div>
                    <canvas id="graficaAlimentos" height="180"></canvas>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="chart-card">
                    <div class="chart-title"><span>🥧</span> Selecciones por categoría</div>
                    <canvas id="graficaCategorias" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         SECCIÓN: RECETAS
    ══════════════════════════════════════════════ -->
    <div class="seccion" id="sec-recetas">
        <div class="chart-card">
            <div class="chart-title"><span>🍽️</span> Recetas más recomendadas</div>
            <canvas id="graficaRecetas" height="120"></canvas>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         SECCIÓN: CONSULTAS
    ══════════════════════════════════════════════ -->
    <div class="seccion" id="sec-consultas">
        <div class="row g-4">
            <div class="col-12">
                <div class="chart-card">
                    <div class="chart-title"><span>📈</span> Consultas diarias</div>
                    <canvas id="graficaConsultas2" height="80"></canvas>
                </div>
            </div>
            <div class="col-12">
                <div class="chart-card">
                    <div class="chart-title"><span>💚</span> Puntuación de salud promedio diaria</div>
                    <canvas id="graficaPuntuacion" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════
         SECCIÓN: INGRESOS
    ══════════════════════════════════════════════ -->
    <div class="seccion" id="sec-ingresos">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="kpi-premium">
                    <div style="font-size:2rem; margin-bottom:.5rem">💰</div>
                    <div style="opacity:.8; font-size:.9rem">Ingresos simulados totales</div>
                    <div class="monto">$<?= number_format($ingresosSimulados) ?></div>
                    <div style="opacity:.7; font-size:.8rem; margin-top:.3rem">MXN / mes</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card" style="flex-direction:column; text-align:center; padding:2rem">
                    <div style="font-size:2.5rem; margin-bottom:.5rem">⭐</div>
                    <div class="stat-num" style="font-size:2.5rem"><?= $totalPremium ?></div>
                    <div class="stat-label">Suscriptores premium</div>
                    <div style="margin-top:.5rem; font-size:.85rem; color:var(--verde); font-weight:600">
                        <?= $totalUsuarios > 0 ? round(($totalPremium/$totalUsuarios)*100) : 0 ?>% tasa de conversión
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card" style="flex-direction:column; text-align:center; padding:2rem">
                    <div style="font-size:2.5rem; margin-bottom:.5rem">📦</div>
                    <div class="stat-num" style="font-size:2rem">$49</div>
                    <div class="stat-label">Precio mensual MXN</div>
                    <div style="margin-top:.5rem; font-size:.85rem; color:var(--dorado); font-weight:600">
                        $399 MXN plan anual
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="chart-card">
                    <div class="chart-title"><span>📊</span> Distribución de planes</div>
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <canvas id="graficaIngresos" height="200"></canvas>
                        </div>
                        <div class="col-md-6">
                            <div style="padding:1rem">
                                <div class="top-item">
                                    <div style="width:14px;height:14px;border-radius:4px;background:#2d6a4f;flex-shrink:0"></div>
                                    <div class="top-nombre">Usuarios Free</div>
                                    <div class="top-num"><?= $totalFree ?></div>
                                </div>
                                <div class="top-item">
                                    <div style="width:14px;height:14px;border-radius:4px;background:#f4a261;flex-shrink:0"></div>
                                    <div class="top-nombre">Usuarios Premium</div>
                                    <div class="top-num"><?= $totalPremium ?></div>
                                </div>
                                <div style="margin-top:1.5rem; padding:1rem; background:#f9f9f9; border-radius:12px">
                                    <div style="font-size:.85rem; color:#888; margin-bottom:.5rem">Proyección anual</div>
                                    <div style="font-family:'Playfair Display',serif; font-size:1.8rem; color:var(--verde); font-weight:900">
                                        $<?= number_format($ingresosSimulados * 12) ?> MXN
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── DATOS PHP → JS ──────────────────────────────────────────
const diasLabels     = <?= json_encode($diasLabels) ?>;
const diasData       = <?= json_encode($diasData) ?>;
const puntLabels     = <?= json_encode($puntLabels) ?>;
const puntData       = <?= json_encode($puntData) ?>;
const catLabels      = <?= json_encode($catLabels) ?>;
const catData        = <?= json_encode($catData) ?>;
const alimentoLabels = <?= json_encode($alimentoLabels) ?>;
const alimentoData   = <?= json_encode($alimentoData) ?>;
const recetaLabels   = <?= json_encode(array_column($topRecetas, 'nombre')) ?>;
const recetaData     = <?= json_encode(array_column($topRecetas, 'total')) ?>;
const totalFree      = <?= $totalFree ?>;
const totalPremium   = <?= $totalPremium ?>;

const colores = ['#2d6a4f','#40916c','#74c69d','#f4a261','#e76f51','#6d4c41','#f9c74f','#90be6d','#43aa8b','#577590'];

// ── GRÁFICA CONSULTAS DIARIAS ───────────────────────────────
function crearGraficaConsultas(id) {
    const ctx = document.getElementById(id);
    if (!ctx) return;
    new Chart(ctx, {
        type:'line',
        data:{
            labels: diasLabels.length ? diasLabels : ['Sin datos'],
            datasets:[{
                label:'Consultas',
                data: diasData.length ? diasData : [0],
                borderColor:'#2d6a4f',
                backgroundColor:'rgba(45,106,79,.1)',
                borderWidth:3,
                pointBackgroundColor:'#2d6a4f',
                pointRadius:5,
                tension:.4,
                fill:true
            }]
        },
        options:{
            responsive:true,
            plugins:{ legend:{display:false} },
            scales:{
                y:{ beginAtZero:true, grid:{color:'#f5f5f5'}, ticks:{stepSize:1} },
                x:{ grid:{display:false} }
            }
        }
    });
}
crearGraficaConsultas('graficaConsultas');
crearGraficaConsultas('graficaConsultas2');

// ── GRÁFICA PLANES ──────────────────────────────────────────
new Chart(document.getElementById('graficaPlanes'), {
    type:'doughnut',
    data:{
        labels:['Free','Premium'],
        datasets:[{
            data:[totalFree, totalPremium],
            backgroundColor:['#2d6a4f','#f4a261'],
            borderWidth:0,
            hoverOffset:8
        }]
    },
    options:{
        responsive:true,
        plugins:{
            legend:{ position:'bottom', labels:{ padding:15, font:{size:12} } }
        },
        cutout:'65%'
    }
});

// ── GRÁFICA ALIMENTOS ───────────────────────────────────────
new Chart(document.getElementById('graficaAlimentos'), {
    type:'bar',
    data:{
        labels: alimentoLabels.length ? alimentoLabels : ['Sin datos'],
        datasets:[{
            label:'Selecciones',
            data: alimentoData.length ? alimentoData : [0],
            backgroundColor: colores,
            borderRadius:8,
            borderSkipped:false
        }]
    },
    options:{
        responsive:true,
        indexAxis:'y',
        plugins:{ legend:{display:false} },
        scales:{
            x:{ beginAtZero:true, grid:{color:'#f5f5f5'} },
            y:{ grid:{display:false}, ticks:{font:{size:11}} }
        }
    }
});

// ── GRÁFICA CATEGORÍAS ──────────────────────────────────────
new Chart(document.getElementById('graficaCategorias'), {
    type:'pie',
    data:{
        labels: catLabels.length ? catLabels : ['Sin datos'],
        datasets:[{
            data: catData.length ? catData : [1],
            backgroundColor: colores,
            borderWidth:2,
            borderColor:'white'
        }]
    },
    options:{
        responsive:true,
        plugins:{
            legend:{ position:'bottom', labels:{ padding:10, font:{size:11} } }
        }
    }
});

// ── GRÁFICA RECETAS ─────────────────────────────────────────
new Chart(document.getElementById('graficaRecetas'), {
    type:'bar',
    data:{
        labels: recetaLabels.length ? recetaLabels : ['Sin datos'],
        datasets:[{
            label:'Recomendaciones',
            data: recetaData.length ? recetaData : [0],
            backgroundColor:'#f4a261',
            borderRadius:8,
            borderSkipped:false
        }]
    },
    options:{
        responsive:true,
        plugins:{ legend:{display:false} },
        scales:{
            y:{ beginAtZero:true, grid:{color:'#f5f5f5'}, ticks:{stepSize:1} },
            x:{ grid:{display:false}, ticks:{font:{size:11}} }
        }
    }
});

// ── GRÁFICA PUNTUACIÓN ──────────────────────────────────────
new Chart(document.getElementById('graficaPuntuacion'), {
    type:'line',
    data:{
        labels: puntLabels.length ? puntLabels : ['Sin datos'],
        datasets:[{
            label:'Promedio salud',
            data: puntData.length ? puntData : [0],
            borderColor:'#74c69d',
            backgroundColor:'rgba(116,198,157,.15)',
            borderWidth:3,
            tension:.4,
            fill:true,
            pointBackgroundColor:'#74c69d',
            pointRadius:5
        }]
    },
    options:{
        responsive:true,
        plugins:{ legend:{display:false} },
        scales:{
            y:{ beginAtZero:true, max:100, grid:{color:'#f5f5f5'} },
            x:{ grid:{display:false} }
        }
    }
});

// ── GRÁFICA INGRESOS ────────────────────────────────────────
new Chart(document.getElementById('graficaIngresos'), {
    type:'doughnut',
    data:{
        labels:['Free','Premium'],
        datasets:[{
            data:[totalFree, totalPremium],
            backgroundColor:['#2d6a4f','#f4a261'],
            borderWidth:0,
            hoverOffset:8
        }]
    },
    options:{
        responsive:true,
        plugins:{ legend:{display:false} },
        cutout:'60%'
    }
});

// ── NAVEGACIÓN SIDEBAR ──────────────────────────────────────
function mostrarSeccion(id, btn) {
    document.querySelectorAll('.seccion').forEach(s => s.classList.remove('activa'));
    document.querySelectorAll('.sidebar-item').forEach(b => b.classList.remove('activo'));
    document.getElementById('sec-' + id).classList.add('activa');
    btn.classList.add('activo');
}
</script>
</body>
</html>