<?php
require_once 'includes/auth.php';
$usuario = datosUsuario();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriExperto México 🌮</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --verde:     #2d6a4f;
            --verde-mid: #40916c;
            --verde-light:#74c69d;
            --crema:     #fefae0;
            --naranja:   #e76f51;
            --dorado:    #f4a261;
            --cafe:      #6d4c41;
            --blanco:    #ffffff;
        }

        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--crema);
            min-height: 100vh;
        }

        /* ── NAVBAR ── */
        .navbar {
            background: var(--verde) !important;
            padding: 1rem 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            color: var(--crema) !important;
            letter-spacing: 1px;
        }
        .navbar-brand span { color: var(--dorado); }
        .nav-link {
            color: rgba(255,255,255,0.85) !important;
            font-weight: 500;
            transition: color .2s;
        }
        .nav-link:hover { color: var(--dorado) !important; }
        .btn-nav-premium {
            background: var(--naranja);
            color: white !important;
            border-radius: 50px;
            padding: .35rem 1.2rem;
            font-weight: 600;
            transition: transform .2s, box-shadow .2s;
        }
        .btn-nav-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231,111,81,.4);
        }

        /* ── HERO ── */
        .hero {
            background: linear-gradient(135deg, var(--verde) 0%, var(--verde-mid) 60%, var(--verde-light) 100%);
            padding: 5rem 2rem 4rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='10'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .hero-badge {
            display: inline-block;
            background: rgba(255,255,255,0.15);
            color: var(--crema);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 50px;
            padding: .4rem 1.4rem;
            font-size: .85rem;
            font-weight: 500;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }
        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.2rem, 5vw, 3.8rem);
            color: white;
            line-height: 1.2;
            margin-bottom: 1rem;
        }
        .hero h1 span { color: var(--dorado); }
        .hero p {
            color: rgba(255,255,255,0.85);
            font-size: 1.15rem;
            max-width: 550px;
            margin: 0 auto 2rem;
            line-height: 1.7;
        }
        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 2.5rem;
            flex-wrap: wrap;
        }
        .stat {
            text-align: center;
            color: white;
        }
        .stat-num {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 900;
            color: var(--dorado);
            display: block;
        }
        .stat-label {
            font-size: .8rem;
            opacity: .8;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ── SECCIÓN ALIMENTOS ── */
        .section-alimentos {
            padding: 3rem 0 5rem;
        }
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: var(--verde);
            margin-bottom: .5rem;
        }
        .section-subtitle {
            color: #666;
            margin-bottom: 2rem;
            font-size: 1rem;
        }

        /* ── FILTROS DE CATEGORÍA ── */
        .filtros {
            display: flex;
            gap: .6rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }
        .filtro-btn {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 50px;
            padding: .45rem 1.2rem;
            font-size: .88rem;
            font-weight: 500;
            cursor: pointer;
            transition: all .2s;
            color: #555;
        }
        .filtro-btn:hover,
        .filtro-btn.activo {
            background: var(--verde);
            border-color: var(--verde);
            color: white;
        }

        /* ── GRID DE ALIMENTOS ── */
        .alimentos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 1rem;
        }
        .alimento-card {
            background: white;
            border: 2.5px solid #e8e8e8;
            border-radius: 16px;
            padding: 1.2rem .8rem;
            text-align: center;
            cursor: pointer;
            transition: all .25s cubic-bezier(.34,1.56,.64,1);
            position: relative;
            user-select: none;
        }
        .alimento-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.1);
            border-color: var(--verde-light);
        }
        .alimento-card.seleccionado {
            border-color: var(--verde);
            background: linear-gradient(135deg, #f0faf5, #e8f5ee);
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(45,106,79,0.2);
        }
        .alimento-card.seleccionado::after {
            content: '✓';
            position: absolute;
            top: 8px; right: 10px;
            background: var(--verde);
            color: white;
            width: 22px; height: 22px;
            border-radius: 50%;
            font-size: .75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }
        .alimento-emoji {
            font-size: 2.5rem;
            display: block;
            margin-bottom: .5rem;
            line-height: 1;
        }
        .alimento-nombre {
            font-size: .82rem;
            font-weight: 600;
            color: #333;
            line-height: 1.3;
        }
        .alimento-cal {
            font-size: .72rem;
            color: #999;
            margin-top: .2rem;
        }
        .categoria-badge {
            position: absolute;
            top: 6px; left: 8px;
            font-size: .6rem;
            background: var(--crema);
            color: var(--cafe);
            border-radius: 4px;
            padding: 1px 5px;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* ── PANEL FLOTANTE DE SELECCIÓN ── */
        .panel-seleccion {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%) translateY(120px);
            background: var(--verde);
            color: white;
            border-radius: 60px;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            box-shadow: 0 20px 60px rgba(45,106,79,0.45);
            transition: transform .4s cubic-bezier(.34,1.56,.64,1);
            z-index: 1000;
            min-width: 340px;
        }
        .panel-seleccion.visible {
            transform: translateX(-50%) translateY(0);
        }
        .panel-count {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--dorado);
        }
        .panel-texto {
            font-size: .9rem;
            opacity: .9;
            flex: 1;
        }
        .btn-recomendar {
            background: var(--naranja);
            color: white;
            border: none;
            border-radius: 50px;
            padding: .65rem 1.8rem;
            font-weight: 700;
            font-size: .95rem;
            cursor: pointer;
            transition: all .2s;
            white-space: nowrap;
        }
        .btn-recomendar:hover {
            background: #cf5c3b;
            transform: scale(1.05);
        }

        /* ── LOADING ── */
        .loading-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(254,250,224,0.92);
            z-index: 9999;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }
        .loading-overlay.activo { display: flex; }
        .spinner {
            width: 60px; height: 60px;
            border: 5px solid #e0e0e0;
            border-top-color: var(--verde);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loading-texto {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            color: var(--verde);
        }

        /* ── CARDS SIN RESULTADO ── */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #aaa;
        }
        .empty-state .bi { font-size: 3rem; }

        /* ── RESPONSIVE ── */
        @media (max-width: 576px) {
            .alimentos-grid { grid-template-columns: repeat(auto-fill, minmax(110px,1fr)); }
            .panel-seleccion { min-width: 90vw; padding: .8rem 1.2rem; }
            .hero { padding: 3rem 1rem 2.5rem; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="index.php">Nutri<span>Experto</span> 🌮</a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon" style="filter:invert(1)"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <?php if($usuario): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="perfil.php">
                            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($usuario['nombre']) ?>
                        </a>
                    </li>
                    <?php if($usuario['tipo'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/dashboard.php" style="color:var(--dorado) !important; font-weight:700">
                            <i class="bi bi-speedometer2"></i> Panel de administración
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if($usuario['tipo'] !== 'premium' && $usuario['tipo'] !== 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link btn-nav-premium" href="premium.php">⭐ Premium</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Salir</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php"><i class="bi bi-person"></i> Iniciar sesión</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-nav-premium" href="premium.php">⭐ Premium</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="position-relative">
        <div class="hero-badge">🇲🇽 Sistema de recomendación de recetas saludables</div>
        <h1>Descubre recetas <span>saludables</span><br>con lo que tienes en casa</h1>
        <p>Selecciona los ingredientes de la canasta básica mexicana y nuestro sistema experto te recomienda las mejores recetas para ti.</p>
        <div class="hero-stats">
            <div class="stat"><span class="stat-num">40+</span><span class="stat-label">Recetas</span></div>
            <div class="stat"><span class="stat-num">50+</span><span class="stat-label">Ingredientes</span></div>
            <div class="stat"><span class="stat-num">100%</span><span class="stat-label">Mexicano</span></div>
        </div>
    </div>
</section>

<!-- SECCIÓN PRINCIPAL -->
<section class="section-alimentos">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
            <div>
                <h2 class="section-title">¿Qué tienes en casa? 🛒</h2>
                <p class="section-subtitle">Selecciona uno o varios ingredientes para comenzar</p>
            </div>
            <button class="btn btn-outline-secondary btn-sm mt-2" id="btnLimpiar" style="display:none" onclick="limpiarSeleccion()">
                <i class="bi bi-x-circle"></i> Limpiar selección
            </button>
        </div>

        <!-- FILTROS -->
        <div class="filtros" id="filtros">
            <button class="filtro-btn activo" data-cat="todos">🍽️ Todos</button>
            <button class="filtro-btn" data-cat="proteina">🍗 Proteínas</button>
            <button class="filtro-btn" data-cat="verdura">🥦 Verduras</button>
            <button class="filtro-btn" data-cat="fruta">🍊 Frutas</button>
            <button class="filtro-btn" data-cat="cereal">🌽 Cereales</button>
            <button class="filtro-btn" data-cat="lacteo">🥛 Lácteos</button>
            <button class="filtro-btn" data-cat="grasa_saludable">🥑 Grasas saludables</button>
        </div>

        <!-- GRID DE ALIMENTOS -->
        <div class="alimentos-grid" id="alimentosGrid">
            <?php
            require_once 'includes/db.php';
            $conn = conectar();
            $result = $conn->query("SELECT * FROM alimentos WHERE activo = 1 ORDER BY categoria, nombre");
            $categoriaLabels = [
                'proteina'        => 'Proteína',
                'verdura'         => 'Verdura',
                'fruta'           => 'Fruta',
                'cereal'          => 'Cereal',
                'lacteo'          => 'Lácteo',
                'grasa_saludable' => 'Grasa'
            ];
            while($a = $result->fetch_assoc()):
            ?>
            <div class="alimento-card"
                 data-id="<?= $a['id'] ?>"
                 data-cat="<?= $a['categoria'] ?>"
                 onclick="toggleAlimento(this)">
                <span class="categoria-badge"><?= $categoriaLabels[$a['categoria']] ?></span>
                <span class="alimento-emoji"><?= $a['emoji'] ?></span>
                <div class="alimento-nombre"><?= htmlspecialchars($a['nombre']) ?></div>
                <div class="alimento-cal"><?= $a['calorias_100g'] ?> kcal/100g</div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- PANEL FLOTANTE -->
<div class="panel-seleccion" id="panelSeleccion">
    <div>
        <div class="panel-count" id="panelCount">0</div>
        <div class="panel-texto">ingredientes<br>seleccionados</div>
    </div>
    <div style="flex:1">
        <div style="font-size:.8rem; opacity:.7; margin-bottom:.3rem" id="panelNombres"></div>
    </div>
    <button class="btn-recomendar" onclick="buscarRecetas()">
        Ver recetas <i class="bi bi-arrow-right"></i>
    </button>
</div>

<!-- LOADING -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
    <div class="loading-texto">Analizando ingredientes...</div>
    <div style="color:#888; font-size:.9rem">El sistema experto está trabajando 🧠</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let seleccionados = {};

    // Toggle selección de alimento
    function toggleAlimento(card) {
        const id     = card.dataset.id;
        const nombre = card.querySelector('.alimento-nombre').textContent;

        if (seleccionados[id]) {
            delete seleccionados[id];
            card.classList.remove('seleccionado');
        } else {
            seleccionados[id] = nombre;
            card.classList.add('seleccionado');
        }
        actualizarPanel();
    }

    // Actualizar panel flotante
    function actualizarPanel() {
        const ids    = Object.keys(seleccionados);
        const count  = ids.length;
        const panel  = document.getElementById('panelSeleccion');
        const btnLimpiar = document.getElementById('btnLimpiar');

        document.getElementById('panelCount').textContent = count;
        document.getElementById('panelNombres').textContent =
            Object.values(seleccionados).slice(0,3).join(', ') +
            (count > 3 ? ` +${count-3} más` : '');

        panel.classList.toggle('visible', count > 0);
        btnLimpiar.style.display = count > 0 ? 'inline-block' : 'none';
    }

    // Limpiar todo
    function limpiarSeleccion() {
        seleccionados = {};
        document.querySelectorAll('.alimento-card.seleccionado')
                .forEach(c => c.classList.remove('seleccionado'));
        actualizarPanel();
    }

    // Filtros por categoría
    document.getElementById('filtros').addEventListener('click', e => {
        const btn = e.target.closest('.filtro-btn');
        if (!btn) return;

        document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('activo'));
        btn.classList.add('activo');

        const cat = btn.dataset.cat;
        document.querySelectorAll('.alimento-card').forEach(card => {
            const mostrar = cat === 'todos' || card.dataset.cat === cat;
            card.style.display = mostrar ? '' : 'none';
        });
    });

    // Enviar al motor de inferencia
    function buscarRecetas() {
        const ids = Object.keys(seleccionados);
        if (ids.length === 0) return;

        document.getElementById('loadingOverlay').classList.add('activo');

        // Guardar en sessionStorage y redirigir
        sessionStorage.setItem('alimentosIds', JSON.stringify(ids));
        sessionStorage.setItem('alimentosNombres', JSON.stringify(seleccionados));

        // POST a resultado.php
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'resultado.php';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type  = 'hidden';
            input.name  = 'alimentos[]';
            input.value = id;
            form.appendChild(input);
        });
        document.body.appendChild(form);
        form.submit();
    }
</script>
</body>
</html>