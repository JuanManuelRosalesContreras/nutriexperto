<?php
require_once 'includes/auth.php';
$usuario   = datosUsuario();
$esPremium = esPremium();
$msg       = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium — NutriExperto 🌮</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --verde:     #2d6a4f;
            --verde-mid: #40916c;
            --crema:     #fefae0;
            --naranja:   #e76f51;
            --dorado:    #f4a261;
            --cafe:      #6d4c41;
            --gold-dark: #7b4f12;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'DM Sans',sans-serif; background:var(--crema); min-height:100vh; }

        /* NAVBAR */
        .navbar { background:var(--verde) !important; padding:1rem 2rem; box-shadow:0 4px 20px rgba(0,0,0,.15); }
        .navbar-brand { font-family:'Playfair Display',serif; font-size:1.6rem; color:var(--crema) !important; }
        .navbar-brand span { color:var(--dorado); }
        .nav-link { color:rgba(255,255,255,.85) !important; font-weight:500; }
        .nav-link:hover { color:var(--dorado) !important; }

        /* HERO PREMIUM */
        .hero-premium {
            background: linear-gradient(135deg, var(--gold-dark) 0%, var(--dorado) 60%, #f9c74f 100%);
            padding: 5rem 0 8rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .hero-premium::before {
            content:'';
            position:absolute;
            inset:0;
            background:url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.06'%3E%3Cpath d='M40 0l40 40-40 40L0 40z'/%3E%3C/g%3E%3C/svg%3E");
        }
        .hero-premium h1 {
            font-family:'Playfair Display',serif;
            font-size:clamp(2.2rem,5vw,3.8rem);
            color:white;
            margin-bottom:1rem;
            position:relative;
        }
        .hero-premium p {
            color:rgba(255,255,255,.85);
            font-size:1.15rem;
            max-width:520px;
            margin:0 auto;
            line-height:1.7;
            position:relative;
        }
        .crown { font-size:4rem; display:block; margin-bottom:1rem; }

        /* PLANES */
        .planes-row {
            margin-top:-5rem;
            position:relative;
            z-index:10;
        }
        .plan-card {
            background:white;
            border-radius:24px;
            padding:2.5rem 2rem;
            box-shadow:0 20px 60px rgba(0,0,0,.1);
            height:100%;
            position:relative;
            transition:transform .3s;
        }
        .plan-card:hover { transform:translateY(-6px); }
        .plan-card.destacado {
            background:linear-gradient(160deg, var(--gold-dark) 0%, var(--dorado) 100%);
            color:white;
            transform:scale(1.03);
        }
        .plan-card.destacado:hover { transform:scale(1.03) translateY(-6px); }
        .badge-popular {
            position:absolute;
            top:-14px;
            left:50%;
            transform:translateX(-50%);
            background:var(--naranja);
            color:white;
            border-radius:50px;
            padding:.3rem 1.2rem;
            font-size:.8rem;
            font-weight:700;
            letter-spacing:.5px;
            white-space:nowrap;
        }
        .plan-nombre {
            font-family:'Playfair Display',serif;
            font-size:1.5rem;
            margin-bottom:.3rem;
        }
        .plan-precio {
            font-family:'Playfair Display',serif;
            font-size:3rem;
            font-weight:900;
            line-height:1;
            margin:1rem 0 .3rem;
        }
        .plan-precio span { font-size:1rem; font-weight:400; opacity:.7; }
        .plan-desc { font-size:.88rem; opacity:.75; margin-bottom:1.5rem; }
        .plan-features { list-style:none; padding:0; margin-bottom:2rem; }
        .plan-features li {
            display:flex;
            align-items:center;
            gap:.7rem;
            padding:.5rem 0;
            font-size:.9rem;
            border-bottom:1px solid rgba(0,0,0,.06);
        }
        .plan-card.destacado .plan-features li {
            border-bottom-color:rgba(255,255,255,.15);
        }
        .plan-features li:last-child { border-bottom:none; }
        .feat-icon { font-size:1.1rem; flex-shrink:0; }
        .feat-lock { opacity:.45; }

        .btn-plan {
            width:100%;
            border:none;
            border-radius:14px;
            padding:.9rem;
            font-size:1rem;
            font-weight:700;
            cursor:pointer;
            transition:all .2s;
        }
        .btn-plan-free {
            background:var(--crema);
            color:var(--verde);
            border:2px solid #ddd;
        }
        .btn-plan-free:hover { border-color:var(--verde); }
        .btn-plan-premium {
            background:white;
            color:var(--gold-dark);
            box-shadow:0 8px 25px rgba(0,0,0,.15);
        }
        .btn-plan-premium:hover {
            transform:translateY(-2px);
            box-shadow:0 15px 35px rgba(0,0,0,.2);
        }
        .btn-plan-activo {
            background:rgba(255,255,255,.2);
            color:white;
            border:2px solid rgba(255,255,255,.4);
        }

        /* COMPARATIVA */
        .comparativa-section { padding:5rem 0; }
        .section-title {
            font-family:'Playfair Display',serif;
            font-size:2rem;
            color:var(--verde);
            margin-bottom:.5rem;
        }
        .tabla-comparativa {
            background:white;
            border-radius:20px;
            overflow:hidden;
            box-shadow:0 8px 30px rgba(0,0,0,.08);
        }
        .tabla-comparativa table {
            width:100%;
            border-collapse:collapse;
        }
        .tabla-comparativa thead th {
            padding:1.2rem 1.5rem;
            text-align:center;
            font-size:.9rem;
            font-weight:700;
        }
        .tabla-comparativa thead th:first-child { text-align:left; color:#888; }
        .th-free { background:#f8f8f8; color:var(--verde); }
        .th-premium {
            background:linear-gradient(135deg,var(--gold-dark),var(--dorado));
            color:white;
        }
        .tabla-comparativa tbody tr { border-top:1px solid #f0f0f0; }
        .tabla-comparativa tbody tr:hover { background:#fafafa; }
        .tabla-comparativa tbody td {
            padding:1rem 1.5rem;
            font-size:.9rem;
            color:#555;
            text-align:center;
        }
        .tabla-comparativa tbody td:first-child { text-align:left; font-weight:500; color:#333; }
        .check-si { color:var(--verde); font-size:1.2rem; }
        .check-no { color:#ccc; font-size:1.2rem; }
        .check-parcial { color:var(--dorado); font-size:.85rem; font-weight:600; }

        /* FAQ */
        .faq-section { padding:0 0 5rem; }
        .faq-item {
            background:white;
            border-radius:14px;
            margin-bottom:.8rem;
            overflow:hidden;
            box-shadow:0 4px 15px rgba(0,0,0,.05);
        }
        .faq-pregunta {
            width:100%;
            background:none;
            border:none;
            padding:1.2rem 1.5rem;
            text-align:left;
            font-weight:600;
            font-size:.95rem;
            color:#333;
            cursor:pointer;
            display:flex;
            justify-content:space-between;
            align-items:center;
            transition:color .2s;
        }
        .faq-pregunta:hover { color:var(--verde); }
        .faq-pregunta.activo { color:var(--verde); }
        .faq-respuesta {
            display:none;
            padding:0 1.5rem 1.2rem;
            font-size:.9rem;
            color:#666;
            line-height:1.7;
        }
        .faq-respuesta.activo { display:block; }

        /* MODAL PAGO SIMULADO */
        .modal-content { border-radius:20px; border:none; overflow:hidden; }
        .modal-pago-header {
            background:linear-gradient(135deg,var(--gold-dark),var(--dorado));
            padding:2rem;
            color:white;
            text-align:center;
        }
        .modal-pago-header h4 { font-family:'Playfair Display',serif; font-size:1.6rem; }
        .plan-resumen {
            background:linear-gradient(135deg,#fff8f0,#fff3e0);
            border:2px solid var(--dorado);
            border-radius:14px;
            padding:1.2rem;
            margin-bottom:1.5rem;
            text-align:center;
        }
        .plan-resumen .precio {
            font-family:'Playfair Display',serif;
            font-size:2.5rem;
            font-weight:900;
            color:var(--gold-dark);
        }
        .form-control-pago {
            border:2px solid #e8e8e8;
            border-radius:12px;
            padding:.75rem 1rem;
            font-size:.95rem;
            width:100%;
            transition:border-color .2s;
            margin-bottom:1rem;
            font-family:'DM Sans',sans-serif;
        }
        .form-control-pago:focus {
            border-color:var(--dorado);
            outline:none;
            box-shadow:0 0 0 4px rgba(244,162,97,.15);
        }
        .btn-pagar {
            width:100%;
            background:linear-gradient(135deg,var(--gold-dark),var(--dorado));
            color:white;
            border:none;
            border-radius:14px;
            padding:.9rem;
            font-size:1rem;
            font-weight:700;
            cursor:pointer;
            transition:all .2s;
        }
        .btn-pagar:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(244,162,97,.4); }
        .seguro-badge {
            display:flex;
            align-items:center;
            justify-content:center;
            gap:.5rem;
            font-size:.8rem;
            color:#aaa;
            margin-top:.8rem;
        }

        /* YA ES PREMIUM */
        .ya-premium {
            background:linear-gradient(135deg,var(--gold-dark),var(--dorado));
            border-radius:20px;
            padding:3rem 2rem;
            text-align:center;
            color:white;
            margin:2rem 0;
        }
        .ya-premium h3 { font-family:'Playfair Display',serif; font-size:2rem; }

        @media(max-width:768px) {
            .plan-card.destacado { transform:scale(1); margin-top:1rem; }
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
                <a href="perfil.php" class="nav-link"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($usuario['nombre']) ?></a>
                <a href="logout.php" class="nav-link"><i class="bi bi-box-arrow-right"></i></a>
            <?php else: ?>
                <a href="login.php" class="nav-link"><i class="bi bi-person"></i> Iniciar sesión</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- HERO -->
<div class="hero-premium">
    <div class="container position-relative">
        <span class="crown">👑</span>
        <h1>NutriExperto <em>Premium</em></h1>
        <p>Desbloquea recetas exclusivas, tu historial completo y un plan nutricional semanal personalizado.</p>
    </div>
</div>

<div class="container">

    <?php if($esPremium): ?>
    <!-- YA ES PREMIUM -->
    <div class="ya-premium mt-5">
        <div style="font-size:3rem; margin-bottom:1rem">⭐</div>
        <h3>¡Ya eres Premium!</h3>
        <p style="opacity:.85; margin-top:.5rem">Tienes acceso completo a todas las funciones de NutriExperto.</p>
        <a href="index.php" class="btn mt-3 px-4 rounded-pill fw-bold" style="background:white; color:var(--gold-dark)">
            Ir a buscar recetas
        </a>
    </div>

    <?php else: ?>

    <!-- PLANES -->
    <div class="row g-4 planes-row mb-5">

        <!-- PLAN FREE -->
        <div class="col-md-4">
            <div class="plan-card">
                <div class="plan-nombre">🆓 Gratuito</div>
                <div class="plan-precio">$0 <span>/ siempre</span></div>
                <div class="plan-desc">Para explorar el sistema</div>
                <ul class="plan-features">
                    <li><span class="feat-icon">✅</span> Acceso a 30 recetas gratuitas</li>
                    <li><span class="feat-icon">✅</span> Análisis nutricional básico</li>
                    <li><span class="feat-icon">✅</span> Recomendaciones del sistema experto</li>
                    <li><span class="feat-icon feat-lock">🔒</span> <span style="opacity:.45">Historial completo</span></li>
                    <li><span class="feat-icon feat-lock">🔒</span> <span style="opacity:.45">10 recetas premium</span></li>
                    <li><span class="feat-icon feat-lock">🔒</span> <span style="opacity:.45">Plan nutricional semanal</span></li>
                    <li><span class="feat-icon feat-lock">🔒</span> <span style="opacity:.45">Guardar recetas favoritas</span></li>
                </ul>
                <?php if($usuario): ?>
                    <button class="btn-plan btn-plan-free" disabled>Plan actual</button>
                <?php else: ?>
                    <a href="registro.php" class="btn-plan btn-plan-free d-block text-center text-decoration-none">
                        Registrarse gratis
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- PLAN PREMIUM MENSUAL -->
        <div class="col-md-4">
            <div class="plan-card destacado">
                <div class="badge-popular">🔥 MÁS POPULAR</div>
                <div class="plan-nombre" style="color:white">⭐ Premium Mensual</div>
                <div class="plan-precio" style="color:white">$49 <span>MXN / mes</span></div>
                <div class="plan-desc">Todo incluido, cancela cuando quieras</div>
                <ul class="plan-features">
                    <li><span class="feat-icon">✅</span> Todo del plan gratuito</li>
                    <li><span class="feat-icon">✅</span> 10 recetas premium exclusivas</li>
                    <li><span class="feat-icon">✅</span> Historial completo de consultas</li>
                    <li><span class="feat-icon">✅</span> Guardar recetas favoritas</li>
                    <li><span class="feat-icon">✅</span> Plan nutricional semanal</li>
                    <li><span class="feat-icon">✅</span> Sin anuncios</li>
                    <li><span class="feat-icon">✅</span> Soporte prioritario</li>
                </ul>
                <?php if(!$usuario): ?>
                    <a href="registro.php" class="btn-plan btn-plan-premium d-block text-center text-decoration-none">
                        Comenzar ahora
                    </a>
                <?php else: ?>
                    <button class="btn-plan btn-plan-premium" onclick="abrirPago('mensual','$49 MXN/mes')">
                        Obtener Premium
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- PLAN PREMIUM ANUAL -->
        <div class="col-md-4">
            <div class="plan-card">
                <div class="plan-nombre">👑 Premium Anual</div>
                <div class="plan-precio">$399 <span>MXN / año</span></div>
                <div class="plan-desc">Ahorra 32% vs pago mensual</div>
                <ul class="plan-features">
                    <li><span class="feat-icon">✅</span> Todo del plan mensual</li>
                    <li><span class="feat-icon">✅</span> Ahorro de $189 MXN al año</li>
                    <li><span class="feat-icon">✅</span> Acceso anticipado a nuevas recetas</li>
                    <li><span class="feat-icon">✅</span> Descarga de planes en PDF</li>
                    <li><span class="feat-icon">✅</span> Badge exclusivo de usuario anual</li>
                    <li><span class="feat-icon feat-lock"></span></li>
                    <li><span class="feat-icon feat-lock"></span></li>
                </ul>
                <?php if(!$usuario): ?>
                    <a href="registro.php" class="btn-plan btn-plan-free d-block text-center text-decoration-none">
                        Comenzar ahora
                    </a>
                <?php else: ?>
                    <button class="btn-plan btn-plan-free" onclick="abrirPago('anual','$399 MXN/año')">
                        Obtener anual
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php endif; ?>

    <!-- TABLA COMPARATIVA -->
    <div class="comparativa-section">
        <div class="text-center mb-4">
            <h2 class="section-title">Comparativa de planes</h2>
            <p class="text-muted">Todo lo que incluye cada plan</p>
        </div>
        <div class="tabla-comparativa">
            <table>
                <thead>
                    <tr>
                        <th style="padding:1.2rem 1.5rem; text-align:left; color:#888">Característica</th>
                        <th class="th-free">Gratuito</th>
                        <th class="th-premium">⭐ Premium</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Recetas disponibles</td>
                        <td><span class="check-parcial">30 recetas</span></td>
                        <td><span class="check-si">✓</span> <small>40+ recetas</small></td>
                    </tr>
                    <tr>
                        <td>Motor de inferencia</td>
                        <td><span class="check-si">✓</span></td>
                        <td><span class="check-si">✓</span></td>
                    </tr>
                    <tr>
                        <td>Análisis nutricional</td>
                        <td><span class="check-si">✓</span></td>
                        <td><span class="check-si">✓</span></td>
                    </tr>
                    <tr>
                        <td>Historial de consultas</td>
                        <td><span class="check-parcial">Últimas 5</span></td>
                        <td><span class="check-si">✓</span> Completo</td>
                    </tr>
                    <tr>
                        <td>Guardar recetas favoritas</td>
                        <td><span class="check-no">✗</span></td>
                        <td><span class="check-si">✓</span></td>
                    </tr>
                    <tr>
                        <td>Recetas premium exclusivas</td>
                        <td><span class="check-no">✗</span></td>
                        <td><span class="check-si">✓</span></td>
                    </tr>
                    <tr>
                        <td>Plan nutricional semanal</td>
                        <td><span class="check-no">✗</span></td>
                        <td><span class="check-si">✓</span></td>
                    </tr>
                    <tr>
                        <td>Sin anuncios</td>
                        <td><span class="check-no">✗</span></td>
                        <td><span class="check-si">✓</span></td>
                    </tr>
                    <tr>
                        <td>Soporte prioritario</td>
                        <td><span class="check-no">✗</span></td>
                        <td><span class="check-si">✓</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- FAQ -->
    <div class="faq-section">
        <div class="text-center mb-4">
            <h2 class="section-title">Preguntas frecuentes</h2>
        </div>
        <?php
        $faqs = [
            ['¿Puedo cancelar en cualquier momento?',
             'Sí, puedes cancelar tu suscripción cuando quieras. Seguirás teniendo acceso Premium hasta el final del período pagado.'],
            ['¿Cómo funciona el plan nutricional semanal?',
             'El sistema experto genera una combinación de 7 días de recetas balanceadas basadas en tus preferencias e historial de ingredientes.'],
            ['¿Mis datos están seguros?',
             'Sí. Tu información personal y tus consultas están almacenadas de forma segura y nunca se comparten con terceros.'],
            ['¿Puedo usar NutriExperto sin registrarme?',
             'Sí, puedes usar el sistema como invitado, pero no podrás guardar tu historial ni acceder a funciones exclusivas.'],
            ['¿El plan anual tiene algún beneficio extra?',
             'Sí, además del ahorro del 32%, obtienes acceso anticipado a nuevas recetas y la posibilidad de descargar tu plan nutricional en PDF.'],
        ];
        foreach($faqs as $i => $faq): ?>
        <div class="faq-item">
            <button class="faq-pregunta" onclick="toggleFaq(<?= $i ?>)">
                <?= $faq[0] ?>
                <i class="bi bi-chevron-down" id="faq-icon-<?= $i ?>"></i>
            </button>
            <div class="faq-respuesta" id="faq-resp-<?= $i ?>">
                <?= $faq[1] ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- MODAL PAGO SIMULADO -->
<div class="modal fade" id="modalPago" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-pago-header">
                <div style="font-size:2rem; margin-bottom:.5rem">👑</div>
                <h4>Activar Premium</h4>
                <p style="opacity:.85; font-size:.9rem; margin-top:.3rem">Pago seguro y simulado</p>
            </div>
            <div class="modal-body p-4">
                <div class="plan-resumen">
                    <div style="font-size:.85rem; color:#888; margin-bottom:.3rem">Plan seleccionado</div>
                    <div class="precio" id="modalPrecioPlan"></div>
                    <div style="font-size:.8rem; color:#aaa; margin-top:.3rem" id="modalNombrePlan"></div>
                </div>

                <input type="text" class="form-control-pago"
                       placeholder="Nombre en la tarjeta"
                       id="pagoNombre" maxlength="50">
                <input type="text" class="form-control-pago"
                       placeholder="Número de tarjeta (simulado)"
                       id="pagoTarjeta" maxlength="19"
                       oninput="formatTarjeta(this)">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem">
                    <input type="text" class="form-control-pago"
                           placeholder="MM/AA" maxlength="5"
                           id="pagoFecha" oninput="formatFecha(this)">
                    <input type="text" class="form-control-pago"
                           placeholder="CVV" maxlength="3"
                           id="pagoCVV">
                </div>

                <button class="btn-pagar" onclick="procesarPago()">
                    <i class="bi bi-lock-fill"></i> Pagar y activar Premium
                </button>
                <div class="seguro-badge">
                    <i class="bi bi-shield-check"></i> Pago 100% simulado — proyecto académico
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL ÉXITO -->
<div class="modal fade" id="modalExito" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4">
            <div style="font-size:4rem; margin-bottom:1rem">🎉</div>
            <h4 style="font-family:'Playfair Display',serif; color:var(--verde)">¡Bienvenido a Premium!</h4>
            <p class="text-muted mt-2">Tu cuenta ha sido actualizada exitosamente.</p>
            <div class="mt-3 mb-2" style="color:var(--dorado); font-size:2rem">⭐⭐⭐</div>
            <a href="index.php" class="btn btn-success rounded-pill px-4 mt-2">
                Ir a explorar recetas premium
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let planActivo = 'mensual';

function abrirPago(plan, precio) {
    planActivo = plan;
    document.getElementById('modalPrecioPlan').textContent = precio;
    document.getElementById('modalNombrePlan').textContent =
        plan === 'mensual' ? 'Premium Mensual' : 'Premium Anual';
    new bootstrap.Modal(document.getElementById('modalPago')).show();
}

function formatTarjeta(input) {
    let val = input.value.replace(/\D/g,'').substring(0,16);
    input.value = val.replace(/(.{4})/g,'$1 ').trim();
}

function formatFecha(input) {
    let val = input.value.replace(/\D/g,'').substring(0,4);
    if (val.length >= 2) val = val.substring(0,2) + '/' + val.substring(2);
    input.value = val;
}

function procesarPago() {
    const nombre  = document.getElementById('pagoNombre').value.trim();
    const tarjeta = document.getElementById('pagoTarjeta').value.trim();
    const fecha   = document.getElementById('pagoFecha').value.trim();
    const cvv     = document.getElementById('pagoCVV').value.trim();

    if (!nombre || !tarjeta || !fecha || !cvv) {
        alert('Por favor completa todos los campos.');
        return;
    }

    const btn = document.querySelector('.btn-pagar');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Procesando...';
    btn.disabled  = true;

    // Simular procesamiento
    setTimeout(() => {
        fetch('api/activar_premium.php', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({plan: planActivo})
        })
        .then(r => r.json())
        .then(data => {
            bootstrap.Modal.getInstance(document.getElementById('modalPago')).hide();
            if (data.ok) {
                new bootstrap.Modal(document.getElementById('modalExito')).show();
            } else {
                alert(data.mensaje || 'Error al procesar. Intenta de nuevo.');
                btn.innerHTML = '<i class="bi bi-lock-fill"></i> Pagar y activar Premium';
                btn.disabled  = false;
            }
        });
    }, 2000);
}

function toggleFaq(i) {
    const resp = document.getElementById('faq-resp-' + i);
    const icon = document.getElementById('faq-icon-' + i);
    const btn  = resp.previousElementSibling;
    const activo = resp.classList.contains('activo');
    document.querySelectorAll('.faq-respuesta').forEach(r => r.classList.remove('activo'));
    document.querySelectorAll('.faq-pregunta').forEach(b => b.classList.remove('activo'));
    document.querySelectorAll('[id^="faq-icon-"]').forEach(ic => {
        ic.style.transform = '';
    });
    if (!activo) {
        resp.classList.add('activo');
        btn.classList.add('activo');
        icon.style.transform = 'rotate(180deg)';
    }
}
</script>
</body>
</html>