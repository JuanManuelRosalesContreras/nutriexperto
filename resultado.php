<?php
require_once 'includes/auth.php';
require_once 'includes/motor_inferencia.php';

// Validar que vengan alimentos seleccionados
if (empty($_POST['alimentos'])) {
    header('Location: index.php');
    exit();
}

$idsAlimentos = array_map('intval', $_POST['alimentos']);
$usuario      = datosUsuario();
$esPremium    = esPremium();

// Ejecutar motor de inferencia
$motor     = new MotorInferencia();
$resultado = $motor->inferir($idsAlimentos, $esPremium);

// Guardar consulta en historial y Data Warehouse
$motor->guardarConsulta($idsAlimentos, $resultado, $usuario ? $usuario['id'] : null);

$recetas         = $resultado['recetas'];
$recomendaciones = $resultado['recomendaciones'];
$puntuacion      = $resultado['puntuacion_salud'];
$alimentos       = $resultado['alimentos'];
$categorias      = $resultado['categorias'];

// Color de puntuación
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
    <title>Tus Recetas — NutriExperto 🌮</title>
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

        /* HEADER RESULTADO */
        .resultado-header {
            background: linear-gradient(135deg, var(--verde) 0%, var(--verde-mid) 100%);
            padding: 3rem 0 5rem;
            position: relative;
        }
        .resultado-header h1 {
            font-family:'Playfair Display',serif;
            color:white;
            font-size:clamp(1.8rem,4vw,2.8rem);
            margin-bottom:.5rem;
        }
        .resultado-header h1 span { color:var(--dorado); }
        .resultado-header p { color:rgba(255,255,255,.8); font-size:1rem; }

        /* TARJETA PUNTUACIÓN */
        .card-puntuacion {
            background:white;
            border-radius:24px;
            padding:2rem;
            box-shadow:0 20px 60px rgba(0,0,0,.12);
            margin-top:-3rem;
            position:relative;
            z-index:10;
        }
        .puntuacion-circle {
            width:110px; height:110px;
            border-radius:50%;
            border:8px solid;
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            margin:0 auto 1rem;
        }
        .puntuacion-num {
            font-family:'Playfair Display',serif;
            font-size:2rem;
            font-weight:900;
            line-height:1;
        }
        .puntuacion-label {
            font-size:.7rem;
            font-weight:600;
            text-transform:uppercase;
            letter-spacing:1px;
        }
        .alimentos-usados {
            display:flex;
            flex-wrap:wrap;
            gap:.5rem;
            margin-top:1rem;
        }
        .chip-alimento {
            background:var(--crema);
            border:1.5px solid #ddd;
            border-radius:50px;
            padding:.3rem .9rem;
            font-size:.82rem;
            font-weight:500;
            color:var(--cafe);
        }

        /* RECOMENDACIONES */
        .recomendacion-item {
            display:flex;
            align-items:flex-start;
            gap:.9rem;
            padding:1rem 1.2rem;
            border-radius:14px;
            margin-bottom:.7rem;
            font-size:.92rem;
            line-height:1.5;
        }
        .rec-exito    { background:#f0faf5; border-left:4px solid var(--verde); }
        .rec-info     { background:#fff8f0; border-left:4px solid var(--dorado); }
        .rec-advertencia { background:#fff3f0; border-left:4px solid var(--naranja); }
        .rec-icono { font-size:1.3rem; flex-shrink:0; }

        /* RECETAS GRID */
        .recetas-section { padding:3rem 0 5rem; }
        .section-title {
            font-family:'Playfair Display',serif;
            font-size:1.9rem;
            color:var(--verde);
            margin-bottom:.3rem;
        }
        .receta-card {
            background:white;
            border-radius:20px;
            overflow:hidden;
            box-shadow:0 8px 30px rgba(0,0,0,.08);
            transition:transform .3s cubic-bezier(.34,1.56,.64,1), box-shadow .3s;
            height:100%;
            display:flex;
            flex-direction:column;
        }
        .receta-card:hover {
            transform:translateY(-6px);
            box-shadow:0 20px 50px rgba(0,0,0,.15);
        }
        .receta-header {
            background:linear-gradient(135deg, var(--verde) 0%, var(--verde-mid) 100%);
            padding:2rem 1.5rem 1.5rem;
            position:relative;
        }
        .receta-header.premium-header {
            background:linear-gradient(135deg, #7b4f12 0%, var(--dorado) 100%);
        }
        .receta-emoji {
            font-size:3rem;
            display:block;
            margin-bottom:.5rem;
        }
        .receta-nombre {
            font-family:'Playfair Display',serif;
            font-size:1.15rem;
            color:white;
            line-height:1.3;
        }
        .badge-premium {
            position:absolute;
            top:12px; right:12px;
            background:var(--dorado);
            color:white;
            border-radius:50px;
            padding:.2rem .7rem;
            font-size:.72rem;
            font-weight:700;
            letter-spacing:.5px;
        }
        .badge-dificultad {
            display:inline-block;
            border-radius:50px;
            padding:.2rem .8rem;
            font-size:.72rem;
            font-weight:600;
            margin-top:.5rem;
        }
        .dif-facil   { background:rgba(255,255,255,.2); color:white; }
        .dif-media   { background:rgba(244,162,97,.3); color:white; }
        .dif-dificil { background:rgba(231,111,81,.3); color:white; }
        .receta-body {
            padding:1.4rem;
            flex:1;
            display:flex;
            flex-direction:column;
        }
        .receta-meta {
            display:flex;
            gap:1rem;
            margin-bottom:1rem;
            font-size:.83rem;
            color:#888;
        }
        .receta-meta span { display:flex; align-items:center; gap:.3rem; }
        .receta-desc {
            font-size:.9rem;
            color:#555;
            line-height:1.6;
            margin-bottom:1rem;
            flex:1;
        }
        .btn-ver-receta {
            background:var(--verde);
            color:white;
            border:none;
            border-radius:50px;
            padding:.6rem 1.5rem;
            font-weight:600;
            font-size:.9rem;
            cursor:pointer;
            transition:all .2s;
            width:100%;
        }
        .btn-ver-receta:hover { background:var(--verde-mid); transform:scale(1.02); }
        .btn-premium-lock {
            background:linear-gradient(135deg,#7b4f12,var(--dorado));
            color:white;
            border:none;
            border-radius:50px;
            padding:.6rem 1.5rem;
            font-weight:600;
            font-size:.9rem;
            cursor:pointer;
            width:100%;
            transition:all .2s;
        }

        /* MODAL RECETA */
        .modal-content { border-radius:20px; border:none; overflow:hidden; }
        .modal-header-custom {
            background:linear-gradient(135deg, var(--verde) 0%, var(--verde-mid) 100%);
            padding:2rem;
            color:white;
        }
        .modal-header-custom.premium {
            background:linear-gradient(135deg,#7b4f12,var(--dorado));
        }
        .modal-title-custom {
            font-family:'Playfair Display',serif;
            font-size:1.5rem;
        }
        .modal-meta {
            display:flex;
            gap:1.5rem;
            flex-wrap:wrap;
            margin-top:1rem;
            font-size:.85rem;
            opacity:.9;
        }
        .instrucciones-list {
            list-style:none;
            padding:0;
        }
        .instrucciones-list li {
            display:flex;
            gap:1rem;
            padding:.8rem 0;
            border-bottom:1px solid #f0f0f0;
            font-size:.93rem;
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
            margin-top:.1rem;
        }

        /* SIN RESULTADOS */
        .sin-resultados {
            text-align:center;
            padding:4rem 2rem;
            background:white;
            border-radius:20px;
            box-shadow:0 8px 30px rgba(0,0,0,.08);
        }
        .sin-resultados .emoji { font-size:4rem; display:block; margin-bottom:1rem; }

        /* BTN VOLVER */
        .btn-volver {
            background:white;
            color:var(--verde);
            border:2px solid var(--verde);
            border-radius:50px;
            padding:.6rem 1.8rem;
            font-weight:600;
            text-decoration:none;
            transition:all .2s;
            display:inline-flex;
            align-items:center;
            gap:.5rem;
        }
        .btn-volver:hover { background:var(--verde); color:white; }

        @media(max-width:576px) {
            .card-puntuacion { margin:0 1rem; margin-top:-2rem; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="index.php">Nutri<span>Experto</span> 🌮</a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <?php if($usuario): ?>
                <a href="perfil.php" class="nav-link">
                    <i class="bi bi-person-circle"></i> <?= htmlspecialchars($usuario['nombre']) ?>
                </a>
                <?php if($usuario['tipo'] !== 'premium' && $usuario['tipo'] !== 'admin'): ?>
                <a href="premium.php" class="nav-link btn-nav-premium" 
                style="background:var(--naranja); border-radius:50px; padding:.35rem 1.2rem; font-weight:600">
                    ⭐ Premium
                </a>
                <?php endif; ?>
                <?php if($usuario['tipo'] === 'admin'): ?>
                <a href="admin/dashboard.php" class="nav-link" 
                style="color:var(--dorado) !important; font-weight:700">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <?php endif; ?>
                <a href="logout.php" class="nav-link">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            <?php else: ?>
                <a href="login.php" class="nav-link">
                    <i class="bi bi-person"></i> Iniciar sesión
                </a>
                <a href="premium.php" class="nav-link" 
                style="background:var(--naranja); color:white !important; border-radius:50px; padding:.35rem 1.2rem; font-weight:600">
                    ⭐ Premium
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- HEADER -->
<div class="resultado-header">
    <div class="container">
        <a href="index.php" class="btn-volver mb-3">
            <i class="bi bi-arrow-left"></i> Volver a ingredientes
        </a>
        <h1>Tu análisis <span>nutricional</span> 🧠</h1>
        <p>Basado en <?= count($alimentos) ?> ingrediente(s) seleccionado(s) — <?= count($recetas) ?> receta(s) encontrada(s)</p>
    </div>
</div>

<div class="container">

    <!-- TARJETA PUNTUACIÓN + RECOMENDACIONES -->
    <div class="card-puntuacion mb-5">
        <div class="row g-4 align-items-start">

            <!-- Puntuación -->
            <div class="col-md-3 text-center">
                <div class="puntuacion-circle" style="border-color:<?= colorPuntuacion($puntuacion) ?>; color:<?= colorPuntuacion($puntuacion) ?>">
                    <span class="puntuacion-num"><?= $puntuacion ?></span>
                    <span class="puntuacion-label">/ 100</span>
                </div>
                <div style="font-weight:700; color:<?= colorPuntuacion($puntuacion) ?>; font-size:1.1rem">
                    <?= labelPuntuacion($puntuacion) ?>
                </div>
                <div style="font-size:.82rem; color:#999; margin-top:.3rem">Índice de salud</div>

                <!-- Barra de progreso -->
                <div style="background:#eee; border-radius:10px; height:8px; margin-top:1rem; overflow:hidden">
                    <div style="width:<?= $puntuacion ?>%; height:100%; background:<?= colorPuntuacion($puntuacion) ?>; border-radius:10px; transition:width 1s ease"></div>
                </div>

                <!-- Ingredientes seleccionados -->
                <div class="alimentos-usados mt-3">
                    <?php foreach($alimentos as $a): ?>
                        <span class="chip-alimento"><?= $a['emoji'] ?> <?= htmlspecialchars($a['nombre']) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recomendaciones -->
            <div class="col-md-9">
                <h5 style="font-family:'Playfair Display',serif; color:var(--verde); margin-bottom:1rem">
                    💡 Recomendaciones del sistema experto
                </h5>
                <?php foreach($recomendaciones as $rec): ?>
                    <div class="recomendacion-item rec-<?= $rec['tipo'] ?>">
                        <span class="rec-icono"><?= $rec['icono'] ?></span>
                        <span><?= htmlspecialchars($rec['mensaje']) ?></span>
                    </div>
                <?php endforeach; ?>

                <?php if(!$esPremium): ?>
                <div class="recomendacion-item rec-info mt-2" style="background:linear-gradient(135deg,#fff8f0,#fff3e0); border-left-color:var(--dorado)">
                    <span class="rec-icono">⭐</span>
                    <span>
                        <strong>¿Quieres un plan nutricional semanal personalizado?</strong><br>
                        Hazte <a href="premium.php" style="color:var(--dorado); font-weight:700">Premium</a> y accede a recetas exclusivas, historial completo y más.
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- RECETAS -->
    <div class="recetas-section">
        <h2 class="section-title">Recetas recomendadas 🍽️</h2>
        <p class="text-muted mb-4">Ordenadas por compatibilidad con tus ingredientes</p>

        <?php if(empty($recetas)): ?>
            <div class="sin-resultados">
                <span class="emoji">🤔</span>
                <h4 style="color:var(--verde)">No encontramos recetas exactas</h4>
                <p class="text-muted mt-2">Intenta seleccionar más ingredientes o diferentes combinaciones.</p>
                <a href="index.php" class="btn-volver mt-3">Intentar de nuevo</a>
            </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach($recetas as $receta): ?>
            <div class="col-md-6 col-lg-4">
                <div class="receta-card">
                    <div class="receta-header <?= $receta['es_premium'] ? 'premium-header' : '' ?>">
                        <?php if($receta['es_premium']): ?>
                            <span class="badge-premium">⭐ Premium</span>
                        <?php endif; ?>
                        <span class="receta-emoji">
                            <?php
                            $emojisRecetas = [
                                1  => '🫔', // Enfrijoladas
                                2  => '🍲', // Sopa lentejas
                                3  => '🌵', // Nopalitos con huevo
                                4  => '🍜', // Pozole pollo
                                5  => '🥩', // Bistec con verduras
                                6  => '🌮', // Tacos frijol nopal
                                7  => '🍚', // Arroz rojo
                                8  => '🍵', // Caldo de pollo
                                9  => '🫔', // Chilaquiles verdes
                                10 => '🥗', // Ensalada nopales
                                11 => '🍳', // Huevos a la mexicana
                                12 => '🥣', // Bowl arroz pollo
                                13 => '🍝', // Sopa de fideo
                                14 => '🧀', // Quesadillas
                                15 => '🥙', // Tostadas nopal
                                16 => '🫘', // Lentejas con chorizo
                                17 => '🥒', // Calabacitas elote
                                18 => '🐟', // Atún a la mexicana
                                19 => '🫑', // Chiles rellenos
                                20 => '🍲', // Sopa garbanzo
                                21 => '🥚', // Machaca con huevo
                                22 => '🌶️', // Rajas con queso
                                23 => '🐠', // Ceviche atún
                                24 => '🫘', // Frijoles de olla
                                25 => '🍗', // Tinga de pollo
                                26 => '🥑', // Guacamole tostadas
                                27 => '🫙', // Sopa azteca
                                28 => '🥩', // Picadillo res
                                29 => '🍝', // Espagueti mexicano
                                30 => '🥣', // Avena con fruta
                                31 => '🌮', // Enchiladas rojas
                                32 => '🍜', // Pozole verde
                                33 => '🫕', // Mole negro
                                34 => '🍋', // Sopa de lima
                                35 => '🦐', // Camarones diabla
                                36 => '🌮', // Birria de res
                                37 => '🫑', // Chiles en nogada
                                38 => '🥖', // Tortas ahogadas
                                39 => '🫓', // Gorditas chicharrón
                                40 => '📅', // Plan nutricional
                                41 => '🫘', // Frijoles pintos charra
                                42 => '🐟', // Sardinas guisadas
                                43 => '🥩', // Hígado encebollado
                                44 => '🥗', // Ensalada espinaca
                                45 => '🍳', // Caldo espinacas huevo
                                46 => '🥦', // Brócoli crema queso
                                47 => '🍵', // Sopa brócoli
                                48 => '🍎', // Agua manzana
                                49 => '🥣', // Avena manzana
                                50 => '🍊', // Agua naranja
                                51 => '🍈', // Agua guayaba
                                52 => '🍉', // Agua sandía
                                53 => '🍑', // Agua melón
                                54 => '🍋', // Agua lima
                                55 => '🌺', // Agua tuna
                                56 => '🍞', // Sándwich integral
                                57 => '🥛', // Yogur con fruta
                                58 => '🍗', // Pollo con brócoli
                                59 => '🥜', // Cacahuates enchilados
                                60 => '🥗', // Ensalada espinaca sardina
                            ];

                            $emoji = $emojisRecetas[$receta['id']] ?? '🍽️';
                            echo '<span class="receta-emoji" style="font-family: Segoe UI Emoji, Apple Color Emoji, Noto Color Emoji, sans-serif; font-size:3rem">'.$emoji.'</span>';
                            ?>
                        </span>
                        <div class="receta-nombre"><?= htmlspecialchars($receta['nombre']) ?></div>
                        <span class="badge-dificultad dif-<?= $receta['dificultad'] ?>">
                            <?= ucfirst($receta['dificultad']) ?>
                        </span>
                    </div>
                    <div class="receta-body">
                        <div class="receta-meta">
                            <span><i class="bi bi-clock"></i> <?= $receta['tiempo_minutos'] ?> min</span>
                            <span><i class="bi bi-fire"></i> ~<?= $receta['calorias_aprox'] ?> kcal</span>
                            <span><i class="bi bi-star-fill" style="color:var(--dorado)"></i> <?= $receta['score'] ?> pts</span>
                        </div>
                        <div class="receta-desc"><?= htmlspecialchars($receta['descripcion']) ?></div>

                        <?php if($receta['es_premium'] && !$esPremium): ?>
                            <a href="premium.php" class="btn-premium-lock">
                                🔒 Desbloquear con Premium
                            </a>
                        <?php else: ?>
                            <?php
                            // Limpiar instrucciones para JavaScript
                            $instruccionesJS = addslashes(str_replace(["\r\n", "\r", "\n"], '\\n', $receta['instrucciones']));
                            $nombreJS = addslashes(htmlspecialchars($receta['nombre']));
                            ?>
                            <button class="btn-ver-receta"
                                onclick="verReceta(
                                    <?= $receta['id'] ?>,
                                    '<?= $nombreJS ?>',
                                    '<?= $instruccionesJS ?>',
                                    <?= $receta['tiempo_minutos'] ?>,
                                    '<?= $receta['dificultad'] ?>',
                                    <?= $receta['calorias_aprox'] ?>,
                                    <?= $receta['es_premium'] ? 'true' : 'false' ?>
                                )">
                                Ver instrucciones <i class="bi bi-arrow-right"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Botón guardar receta (solo usuarios logueados) -->
        <?php if(!$usuario): ?>
        <div class="text-center mt-5 p-4" style="background:white; border-radius:20px; box-shadow:0 8px 30px rgba(0,0,0,.08)">
            <div style="font-size:2.5rem; margin-bottom:1rem">🔖</div>
            <h5 style="color:var(--verde); font-family:'Playfair Display',serif">¿Quieres guardar tus recetas?</h5>
            <p class="text-muted">Crea una cuenta gratuita para guardar tu historial de consultas.</p>
            <div class="d-flex justify-content-center gap-3 mt-3 flex-wrap">
                <a href="registro.php" class="btn btn-success rounded-pill px-4">Crear cuenta gratis</a>
                <a href="login.php" class="btn btn-outline-success rounded-pill px-4">Iniciar sesión</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL INSTRUCCIONES -->
<div class="modal fade" id="modalReceta" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header-custom" id="modalHeaderCustom">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="modal-title-custom" id="modalTitulo"></div>
                        <div class="modal-meta" id="modalMeta"></div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-4">
                <h6 style="color:var(--verde); font-weight:700; margin-bottom:1rem">
                    <i class="bi bi-list-check"></i> Instrucciones paso a paso
                </h6>
                <ul class="instrucciones-list" id="modalInstrucciones"></ul>
            </div>
            <div class="modal-footer border-0 pb-4">
                <?php if($usuario): ?>
                    <?php if(esPremium()): ?>
                        <button class="btn btn-success rounded-pill px-4" 
                                id="btnGuardarReceta" 
                                onclick="guardarReceta()">
                            <i class="bi bi-bookmark-plus"></i> Guardar receta
                        </button>
                    <?php else: ?>
                        <a href="premium.php" class="btn btn-warning rounded-pill px-4">
                            ⭐ Hazte Premium para guardar
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-success rounded-pill px-4">
                        <i class="bi bi-person"></i> Inicia sesión para guardar
                    </a>
                <?php endif; ?>
                <button type="button" 
                        class="btn btn-outline-secondary rounded-pill px-4" 
                        data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let recetaActivaId = null;

    function verReceta(id, nombre, instrucciones, tiempo, dificultad, calorias, esPremium) {
        recetaActivaId = id;

        document.getElementById('modalTitulo').textContent = nombre;
        document.getElementById('modalHeaderCustom').className =
            'modal-header-custom' + (esPremium ? ' premium' : '');

        document.getElementById('modalMeta').innerHTML =
            '<span><i class="bi bi-clock"></i> ' + tiempo + ' minutos</span>' +
            '&nbsp;&nbsp;<span><i class="bi bi-fire"></i> ~' + calorias + ' kcal</span>' +
            '&nbsp;&nbsp;<span><i class="bi bi-bar-chart"></i> ' +
            dificultad.charAt(0).toUpperCase() + dificultad.slice(1) + '</span>';

        // Parsear pasos correctamente
        const pasos = instrucciones.split('\\n').filter(p => p.trim() !== '');
        const lista = document.getElementById('modalInstrucciones');
        lista.innerHTML = '';

        if (pasos.length === 0) {
            lista.innerHTML = '<li><span class="paso-num">1</span><span>' + instrucciones + '</span></li>';
            return;
        }

        pasos.forEach(function(paso, i) {
            const textoLimpio = paso.replace(/^\d+\.\s*/, '').trim();
            if (textoLimpio) {
                lista.innerHTML +=
                    '<li>' +
                        '<span class="paso-num">' + (i + 1) + '</span>' +
                        '<span>' + textoLimpio + '</span>' +
                    '</li>';
            }
        });

        // Mostrar modal
        var modal = new bootstrap.Modal(document.getElementById('modalReceta'));
        modal.show();
    }

    function guardarReceta() {
        if (!recetaActivaId) return;
        fetch('api/guardar_receta.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({receta_id: recetaActivaId})
        })
        .then(r => r.json())
        .then(data => {
            const btn = document.getElementById('btnGuardarReceta');
            if (data.ok) {
                btn.innerHTML = '<i class="bi bi-bookmark-check-fill"></i> ¡Guardada!';
                btn.classList.replace('btn-success','btn-secondary');
                btn.disabled = true;
            } else {
                alert(data.mensaje || 'Error al guardar');
            }
        });
    }
</script>
</body>
</html>