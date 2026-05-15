<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
requiereLogin();

$usuario = datosUsuario();
$conn    = conectar();

// Historial de consultas (free: últimas 5, premium: todas)
$limite  = esPremium() ? 999 : 5;
$stmt    = $conn->prepare("
    SELECT hc.*, 
           hc.alimentos_seleccionados,
           hc.recetas_recomendadas,
           hc.puntuacion_salud,
           hc.fecha
    FROM historial_consultas hc
    WHERE hc.usuario_id = ?
    ORDER BY hc.fecha DESC
    LIMIT ?
");
$stmt->bind_param('ii', $usuario['id'], $limite);
$stmt->execute();
$historial = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Recetas guardadas (solo premium)
$recetasGuardadas = [];
if (esPremium()) {
    $stmt2 = $conn->prepare("
        SELECT rg.*, r.nombre, r.descripcion, r.tiempo_minutos,
               r.dificultad, r.calorias_aprox, r.instrucciones, r.es_premium
        FROM recetas_guardadas rg
        JOIN recetas r ON rg.receta_id = r.id
        WHERE rg.usuario_id = ?
        ORDER BY rg.fecha DESC
    ");
    $stmt2->bind_param('i', $usuario['id']);
    $stmt2->execute();
    $recetasGuardadas = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Estadísticas del usuario
$stmtStats = $conn->prepare("
    SELECT 
        COUNT(*) as total_consultas,
        AVG(puntuacion_salud) as promedio_salud,
        MAX(puntuacion_salud) as mejor_puntuacion
    FROM historial_consultas
    WHERE usuario_id = ?
");
$stmtStats->bind_param('i', $usuario['id']);
$stmtStats->execute();
$stats = $stmtStats->get_result()->fetch_assoc();

function colorPuntuacion($p) {
    if ($p >= 75) return '#2d6a4f';
    if ($p >= 50) return '#f4a261';
    return '#e76f51';
}
function labelPuntuacion($p) {
    if ($p >= 75) return 'Excelente';
    if ($p >= 50) return 'Moderado';
    return 'Mejorable';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil — NutriExperto 🌮</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --verde:      #2d6a4f;
            --verde-mid:  #40916c;
            --verde-light:#74c69d;
            --crema:      #fefae0;
            --naranja:    #e76f51;
            --dorado:     #f4a261;
            --cafe:       #6d4c41;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'DM Sans',sans-serif; background:var(--crema); min-height:100vh; }

        /* NAVBAR */
        .navbar { background:var(--verde) !important; padding:1rem 2rem; box-shadow:0 4px 20px rgba(0,0,0,.15); }
        .navbar-brand { font-family:'Playfair Display',serif; font-size:1.6rem; color:var(--crema) !important; }
        .navbar-brand span { color:var(--dorado); }
        .nav-link { color:rgba(255,255,255,.85) !important; font-weight:500; }
        .nav-link:hover { color:var(--dorado) !important; }

        /* HEADER PERFIL */
        .perfil-header {
            background:linear-gradient(135deg, var(--verde) 0%, var(--verde-mid) 100%);
            padding:3rem 0 6rem;
        }
        .avatar {
            width:90px; height:90px;
            background:rgba(255,255,255,.2);
            border:3px solid rgba(255,255,255,.4);
            border-radius:50%;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:2.5rem;
            margin-bottom:1rem;
        }
        .perfil-nombre {
            font-family:'Playfair Display',serif;
            font-size:2rem;
            color:white;
        }
        .perfil-email { color:rgba(255,255,255,.7); font-size:.95rem; }
        .badge-tipo {
            display:inline-block;
            border-radius:50px;
            padding:.3rem 1rem;
            font-size:.8rem;
            font-weight:700;
            margin-top:.5rem;
            letter-spacing:.5px;
        }
        .badge-premium { background:var(--dorado); color:white; }
        .badge-free    { background:rgba(255,255,255,.2); color:white; border:1px solid rgba(255,255,255,.3); }

        /* STATS CARDS */
        .stats-row {
            margin-top:-4rem;
            position:relative;
            z-index:10;
        }
        .stat-card {
            background:white;
            border-radius:20px;
            padding:1.8rem;
            text-align:center;
            box-shadow:0 15px 40px rgba(0,0,0,.1);
            height:100%;
        }
        .stat-icon {
            font-size:2rem;
            display:block;
            margin-bottom:.5rem;
        }
        .stat-num {
            font-family:'Playfair Display',serif;
            font-size:2.2rem;
            font-weight:900;
            color:var(--verde);
            display:block;
        }
        .stat-label {
            font-size:.82rem;
            color:#999;
            text-transform:uppercase;
            letter-spacing:1px;
        }

        /* TABS */
        .perfil-tabs {
            background:white;
            border-radius:20px;
            padding:.5rem;
            display:flex;
            gap:.3rem;
            margin-bottom:2rem;
            box-shadow:0 4px 20px rgba(0,0,0,.06);
        }
        .tab-btn {
            flex:1;
            border:none;
            background:transparent;
            border-radius:14px;
            padding:.75rem 1rem;
            font-weight:600;
            font-size:.92rem;
            color:#888;
            cursor:pointer;
            transition:all .2s;
        }
        .tab-btn.activo {
            background:var(--verde);
            color:white;
            box-shadow:0 4px 15px rgba(45,106,79,.3);
        }
        .tab-content { display:none; }
        .tab-content.activo { display:block; }

        /* HISTORIAL */
        .historial-item {
            background:white;
            border-radius:16px;
            padding:1.5rem;
            margin-bottom:1rem;
            box-shadow:0 4px 20px rgba(0,0,0,.06);
            transition:transform .2s;
            cursor:pointer;
        }
        .historial-item:hover { transform:translateX(4px); }
        .historial-fecha {
            font-size:.8rem;
            color:#aaa;
            margin-bottom:.5rem;
        }
        .historial-puntuacion {
            display:inline-block;
            border-radius:50px;
            padding:.25rem .8rem;
            font-size:.8rem;
            font-weight:700;
            color:white;
        }
        .chips-row {
            display:flex;
            flex-wrap:wrap;
            gap:.4rem;
            margin-top:.8rem;
        }
        .chip {
            background:var(--crema);
            border:1.5px solid #ddd;
            border-radius:50px;
            padding:.2rem .7rem;
            font-size:.78rem;
            color:var(--cafe);
            font-weight:500;
        }
        .chip-receta {
            background:#f0faf5;
            border-color:var(--verde-light);
            color:var(--verde);
        }

        /* RECETAS GUARDADAS */
        .receta-guardada-card {
            background:white;
            border-radius:16px;
            overflow:hidden;
            box-shadow:0 4px 20px rgba(0,0,0,.06);
            transition:transform .3s;
            height:100%;
        }
        .receta-guardada-card:hover { transform:translateY(-4px); }
        .receta-guardada-header {
            background:linear-gradient(135deg,var(--verde),var(--verde-mid));
            padding:1.5rem;
            color:white;
        }
        .receta-guardada-header.premium {
            background:linear-gradient(135deg,#7b4f12,var(--dorado));
        }
        .receta-guardada-body { padding:1.2rem; }
        .btn-ver-inst {
            background:var(--verde);
            color:white;
            border:none;
            border-radius:50px;
            padding:.5rem 1.2rem;
            font-size:.85rem;
            font-weight:600;
            cursor:pointer;
            transition:all .2s;
            width:100%;
            margin-top:.8rem;
        }
        .btn-ver-inst:hover { background:var(--verde-mid); }
        .btn-eliminar-guardada {
            background:white;
            color:var(--naranja);
            border:1.5px solid var(--naranja);
            border-radius:50px;
            padding:.4rem 1rem;
            font-size:.8rem;
            font-weight:600;
            cursor:pointer;
            transition:all .2s;
            width:100%;
            margin-top:.4rem;
        }
        .btn-eliminar-guardada:hover {
            background:var(--naranja);
            color:white;
        }

        /* BLOQUEO PREMIUM */
        .premium-lock {
            background:white;
            border-radius:20px;
            padding:3rem 2rem;
            text-align:center;
            box-shadow:0 4px 20px rgba(0,0,0,.06);
        }
        .premium-lock .emoji { font-size:3.5rem; display:block; margin-bottom:1rem; }
        .btn-upgrade {
            background:linear-gradient(135deg,#7b4f12,var(--dorado));
            color:white;
            border:none;
            border-radius:50px;
            padding:.75rem 2rem;
            font-weight:700;
            font-size:1rem;
            cursor:pointer;
            text-decoration:none;
            display:inline-block;
            margin-top:1rem;
            transition:all .2s;
        }
        .btn-upgrade:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(244,162,97,.4); color:white; }

        /* ESTADO VACÍO */
        .empty-state {
            text-align:center;
            padding:3rem 2rem;
            background:white;
            border-radius:20px;
            color:#aaa;
        }
        .empty-state .emoji { font-size:3rem; display:block; margin-bottom:1rem; }

        /* MODAL */
        .modal-content { border-radius:20px; border:none; overflow:hidden; }
        .modal-header-custom {
            background:linear-gradient(135deg,var(--verde),var(--verde-mid));
            padding:2rem;
            color:white;
        }
        .modal-title-custom { font-family:'Playfair Display',serif; font-size:1.4rem; }
        .instrucciones-list { list-style:none; padding:0; }
        .instrucciones-list li {
            display:flex;
            gap:1rem;
            padding:.8rem 0;
            border-bottom:1px solid #f0f0f0;
            font-size:.92rem;
            color:#444;
            line-height:1.6;
        }
        .instrucciones-list li:last-child { border-bottom:none; }
        .paso-num {
            background:var(--verde);
            color:white;
            border-radius:50%;
            width:28px; height:28px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:.78rem;
            font-weight:700;
            flex-shrink:0;
        }

        @media(max-width:576px) {
            .perfil-tabs { flex-direction:column; }
            .stats-row { margin-top:-2rem; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="index.php">Nutri<span>Experto</span> 🌮</a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <a href="index.php" class="nav-link"><i class="bi bi-house"></i> Inicio</a>
            <a href="logout.php" class="nav-link"><i class="bi bi-box-arrow-right"></i> Salir</a>
        </div>
    </div>
</nav>

<!-- HEADER -->
<div class="perfil-header">
    <div class="container text-center">
        <div class="avatar mx-auto">👤</div>
        <div class="perfil-nombre"><?= htmlspecialchars($usuario['nombre']) ?></div>
        <div class="perfil-email"><?= htmlspecialchars($usuario['email']) ?></div>
        <span class="badge-tipo <?= esPremium() ? 'badge-premium' : 'badge-free' ?>">
            <?= esPremium() ? '⭐ Premium' : '🆓 Plan Gratuito' ?>
        </span>
    </div>
</div>

<div class="container pb-5">

    <!-- STATS -->
    <div class="row g-3 stats-row mb-5">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <span class="stat-icon">🔍</span>
                <span class="stat-num"><?= $stats['total_consultas'] ?? 0 ?></span>
                <span class="stat-label">Consultas</span>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <span class="stat-icon">📊</span>
                <span class="stat-num"><?= round($stats['promedio_salud'] ?? 0) ?></span>
                <span class="stat-label">Promedio salud</span>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <span class="stat-icon">🌟</span>
                <span class="stat-num"><?= $stats['mejor_puntuacion'] ?? 0 ?></span>
                <span class="stat-label">Mejor puntuación</span>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <span class="stat-icon">🔖</span>
                <span class="stat-num"><?= count($recetasGuardadas) ?></span>
                <span class="stat-label">Recetas guardadas</span>
            </div>
        </div>
    </div>

    <!-- TABS -->
    <div class="perfil-tabs">
        <button class="tab-btn activo" onclick="cambiarTab('historial', this)">
            <i class="bi bi-clock-history"></i> Mi historial
        </button>
        <button class="tab-btn" onclick="cambiarTab('guardadas', this)">
            <i class="bi bi-bookmark-fill"></i> Recetas guardadas
        </button>
        <button class="tab-btn" onclick="cambiarTab('cuenta', this)">
            <i class="bi bi-person-gear"></i> Mi cuenta
        </button>
    </div>

    <!-- TAB: HISTORIAL -->
    <div class="tab-content activo" id="tab-historial">
        <?php if(!esPremium()): ?>
        <div class="alert" style="background:#fff8f0; border:1.5px solid var(--dorado); border-radius:14px; padding:1rem 1.3rem; margin-bottom:1.5rem; font-size:.9rem">
            <i class="bi bi-info-circle" style="color:var(--dorado)"></i>
            Estás viendo tus últimas <strong>5 consultas</strong>. 
            <a href="premium.php" style="color:var(--dorado); font-weight:700">Hazte Premium</a> para ver tu historial completo.
        </div>
        <?php endif; ?>

        <?php if(empty($historial)): ?>
            <div class="empty-state">
                <span class="emoji">🔍</span>
                <h5>Aún no tienes consultas</h5>
                <p>Selecciona ingredientes en la página principal para comenzar.</p>
                <a href="index.php" class="btn btn-success rounded-pill mt-3 px-4">Ir al inicio</a>
            </div>
        <?php else: ?>
            <?php foreach($historial as $item):
                $alimentosIds = json_decode($item['alimentos_seleccionados'], true) ?? [];
                $recetasIds   = json_decode($item['recetas_recomendadas'], true) ?? [];
                $punt         = $item['puntuacion_salud'];
                $fecha        = date('d/m/Y H:i', strtotime($item['fecha']));

                // Obtener nombres de alimentos
                $nombresAlimentos = [];
                if(!empty($alimentosIds)) {
                    $phs  = implode(',', array_fill(0, count($alimentosIds), '?'));
                    $tipos = str_repeat('i', count($alimentosIds));
                    $sa   = $conn->prepare("SELECT nombre, emoji FROM alimentos WHERE id IN ($phs)");
                    $sa->bind_param($tipos, ...$alimentosIds);
                    $sa->execute();
                    $ra = $sa->get_result();
                    while($row = $ra->fetch_assoc()) {
                        $nombresAlimentos[] = $row['emoji'].' '.$row['nombre'];
                    }
                }

                // Obtener nombres de recetas
                $nombresRecetas = [];
                if(!empty($recetasIds)) {
                    $phs2  = implode(',', array_fill(0, count($recetasIds), '?'));
                    $tipos2 = str_repeat('i', count($recetasIds));
                    $sr    = $conn->prepare("SELECT nombre FROM recetas WHERE id IN ($phs2)");
                    $sr->bind_param($tipos2, ...$recetasIds);
                    $sr->execute();
                    $rr = $sr->get_result();
                    while($row = $rr->fetch_assoc()) {
                        $nombresRecetas[] = $row['nombre'];
                    }
                }
            ?>
            <div class="historial-item">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div class="historial-fecha">
                        <i class="bi bi-calendar3"></i> <?= $fecha ?>
                    </div>
                    <span class="historial-puntuacion" style="background:<?= colorPuntuacion($punt) ?>">
                        <?= $punt ?>/100 — <?= labelPuntuacion($punt) ?>
                    </span>
                </div>

                <div style="font-size:.85rem; font-weight:600; color:#555; margin-top:.8rem">
                    🛒 Ingredientes seleccionados:
                </div>
                <div class="chips-row">
                    <?php foreach($nombresAlimentos as $nombre): ?>
                        <span class="chip"><?= htmlspecialchars($nombre) ?></span>
                    <?php endforeach; ?>
                </div>

                <?php if(!empty($nombresRecetas)): ?>
                <div style="font-size:.85rem; font-weight:600; color:#555; margin-top:.8rem">
                    🍽️ Recetas recomendadas:
                </div>
                <div class="chips-row">
                    <?php foreach($nombresRecetas as $nombre): ?>
                        <span class="chip chip-receta"><?= htmlspecialchars($nombre) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- TAB: RECETAS GUARDADAS -->
    <div class="tab-content" id="tab-guardadas">
        <?php if(!esPremium()): ?>
            <div class="premium-lock">
                <span class="emoji">⭐</span>
                <h4 style="font-family:'Playfair Display',serif; color:var(--verde)">Función Premium</h4>
                <p class="text-muted mt-2">Guarda tus recetas favoritas y accede a ellas cuando quieras, junto con tu historial completo y recetas exclusivas.</p>
                <a href="premium.php" class="btn-upgrade">⭐ Obtener Premium</a>
            </div>
        <?php elseif(empty($recetasGuardadas)): ?>
            <div class="empty-state">
                <span class="emoji">🔖</span>
                <h5>No tienes recetas guardadas</h5>
                <p>Cuando encuentres una receta que te guste, guárdala desde la pantalla de resultados.</p>
                <a href="index.php" class="btn btn-success rounded-pill mt-3 px-4">Buscar recetas</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach($recetasGuardadas as $rg): ?>
                <div class="col-md-6 col-lg-4" id="card-guardada-<?= $rg['id'] ?>">
                    <div class="receta-guardada-card">
                        <div class="receta-guardada-header <?= $rg['es_premium'] ? 'premium' : '' ?>">
                            <div style="font-size:2rem; margin-bottom:.5rem">🍽️</div>
                            <div style="font-family:'Playfair Display',serif; font-size:1.1rem; color:white">
                                <?= htmlspecialchars($rg['nombre']) ?>
                            </div>
                            <div style="font-size:.8rem; color:rgba(255,255,255,.7); margin-top:.3rem">
                                Guardada el <?= date('d/m/Y', strtotime($rg['fecha'])) ?>
                            </div>
                        </div>
                        <div class="receta-guardada-body">
                            <div style="font-size:.83rem; color:#888; margin-bottom:.5rem">
                                <i class="bi bi-clock"></i> <?= $rg['tiempo_minutos'] ?> min &nbsp;
                                <i class="bi bi-fire"></i> ~<?= $rg['calorias_aprox'] ?> kcal
                            </div>
                            <div style="font-size:.88rem; color:#555; line-height:1.5">
                                <?= htmlspecialchars($rg['descripcion']) ?>
                            </div>
                            <button class="btn-ver-inst" onclick="verInstrucciones(
                                '<?= addslashes(htmlspecialchars($rg['nombre'])) ?>',
                                '<?= addslashes(htmlspecialchars($rg['instrucciones'])) ?>',
                                <?= $rg['tiempo_minutos'] ?>,
                                '<?= $rg['dificultad'] ?>',
                                <?= $rg['calorias_aprox'] ?>
                            )">
                                Ver instrucciones <i class="bi bi-arrow-right"></i>
                            </button>
                            <button class="btn-eliminar-guardada" onclick="eliminarGuardada(<?= $rg['id'] ?>)">
                                <i class="bi bi-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- TAB: CUENTA -->
    <div class="tab-content" id="tab-cuenta">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="stat-card text-start">
                    <h6 style="font-family:'Playfair Display',serif; color:var(--verde); margin-bottom:1.2rem">
                        <i class="bi bi-person-circle"></i> Información de cuenta
                    </h6>
                    <div style="font-size:.9rem; color:#555; line-height:2">
                        <strong>Nombre:</strong> <?= htmlspecialchars($usuario['nombre']) ?><br>
                        <strong>Correo:</strong> <?= htmlspecialchars($usuario['email']) ?><br>
                        <strong>Plan:</strong>
                        <span style="color:<?= esPremium() ? '#f4a261' : 'var(--verde)' ?>; font-weight:700">
                            <?= esPremium() ? '⭐ Premium' : '🆓 Gratuito' ?>
                        </span>
                    </div>
                    <?php if(!esPremium()): ?>
                        <a href="premium.php" class="btn-upgrade d-inline-block mt-3" style="font-size:.9rem; padding:.6rem 1.5rem">
                            ⭐ Mejorar a Premium
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card text-start">
                    <h6 style="font-family:'Playfair Display',serif; color:var(--verde); margin-bottom:1.2rem">
                        <i class="bi bi-shield-check"></i> Seguridad
                    </h6>
                    <p style="font-size:.9rem; color:#888; margin-bottom:1rem">
                        Para cambiar tu contraseña, cierra sesión y usa la opción de recuperación.
                    </p>
                    <a href="logout.php" class="btn btn-outline-danger rounded-pill px-4" style="font-size:.9rem">
                        <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL INSTRUCCIONES -->
<div class="modal fade" id="modalInst" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header-custom">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="modal-title-custom" id="modalInstTitulo"></div>
                        <div style="font-size:.85rem; opacity:.8; margin-top:.5rem" id="modalInstMeta"></div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-4">
                <h6 style="color:var(--verde); font-weight:700; margin-bottom:1rem">
                    <i class="bi bi-list-check"></i> Instrucciones paso a paso
                </h6>
                <ul class="instrucciones-list" id="modalInstLista"></ul>
            </div>
            <div class="modal-footer border-0 pb-4">
                <button class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function cambiarTab(tabId, btn) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('activo'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('activo'));
    document.getElementById('tab-' + tabId).classList.add('activo');
    btn.classList.add('activo');
}

function verInstrucciones(nombre, instrucciones, tiempo, dificultad, calorias) {
    document.getElementById('modalInstTitulo').textContent = nombre;
    document.getElementById('modalInstMeta').innerHTML =
        `<i class="bi bi-clock"></i> ${tiempo} min &nbsp;
         <i class="bi bi-fire"></i> ~${calorias} kcal &nbsp;
         <i class="bi bi-bar-chart"></i> ${dificultad}`;

    const pasos = instrucciones.split('\\n').filter(p => p.trim() !== '');
    const lista = document.getElementById('modalInstLista');
    lista.innerHTML = '';
    pasos.forEach((paso, i) => {
        const texto = paso.replace(/^\d+\.\s*/, '');
        lista.innerHTML += `
            <li>
                <span class="paso-num">${i+1}</span>
                <span>${texto}</span>
            </li>`;
    });
    new bootstrap.Modal(document.getElementById('modalInst')).show();
}

function eliminarGuardada(id) {
    if (!confirm('¿Eliminar esta receta de tus guardadas?')) return;
    fetch('api/eliminar_guardada.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({id: id})
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            const card = document.getElementById('card-guardada-' + id);
            card.style.opacity = '0';
            card.style.transform = 'scale(.9)';
            card.style.transition = 'all .3s';
            setTimeout(() => card.remove(), 300);
        }
    });
}
</script>
</body>
</html>