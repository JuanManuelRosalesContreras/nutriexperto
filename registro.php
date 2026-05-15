<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (estaLogueado()) {
    header('Location: index.php');
    exit();
}

$error  = '';
$exito  = false;
$datos  = ['nombre'=>'','email'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre']   ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $confirmar= $_POST['confirmar']     ?? '';

    if (empty($nombre) || empty($email) || empty($password)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $confirmar) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $conn = conectar();
        $check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check->bind_param('s', $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = 'Este correo ya está registrado. ¿Quieres iniciar sesión?';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, tipo) VALUES (?,?,?,'free')");
            $stmt->bind_param('sss', $nombre, $email, $hash);
            if ($stmt->execute()) {
                header('Location: login.php?msg=registro_exitoso');
                exit();
            } else {
                $error = 'Error al crear la cuenta. Intenta de nuevo.';
            }
        }
    }
    $datos = ['nombre'=>$nombre,'email'=>$email];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta — NutriExperto 🌮</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --verde:      #2d6a4f;
            --verde-mid:  #40916c;
            --crema:      #fefae0;
            --naranja:    #e76f51;
            --dorado:     #f4a261;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'DM Sans',sans-serif;
            min-height:100vh;
            display:flex;
            background:var(--crema);
        }
        .lado-izq {
            width:45%;
            background:linear-gradient(160deg,#7b4f12 0%,var(--dorado) 100%);
            display:flex;
            flex-direction:column;
            justify-content:center;
            align-items:center;
            padding:3rem;
            position:relative;
            overflow:hidden;
        }
        .lado-izq::before {
            content:'';
            position:absolute;
            width:350px; height:350px;
            border-radius:50%;
            background:rgba(255,255,255,.07);
            top:-80px; right:-80px;
        }
        .lado-izq::after {
            content:'';
            position:absolute;
            width:250px; height:250px;
            border-radius:50%;
            background:rgba(255,255,255,.07);
            bottom:-60px; left:-60px;
        }
        .brand-logo {
            font-family:'Playfair Display',serif;
            font-size:2.5rem;
            color:white;
            margin-bottom:.5rem;
            position:relative;
            z-index:1;
        }
        .brand-logo span { color:white; opacity:.7; }
        .brand-tagline {
            color:rgba(255,255,255,.8);
            font-size:1rem;
            text-align:center;
            position:relative;
            z-index:1;
            max-width:280px;
            line-height:1.6;
        }
        .plan-card {
            background:rgba(255,255,255,.15);
            border:1px solid rgba(255,255,255,.25);
            border-radius:16px;
            padding:1.5rem;
            margin-top:2rem;
            position:relative;
            z-index:1;
            width:100%;
            max-width:280px;
        }
        .plan-card h4 {
            color:white;
            font-family:'Playfair Display',serif;
            font-size:1.2rem;
            margin-bottom:1rem;
        }
        .plan-item {
            display:flex;
            align-items:center;
            gap:.7rem;
            color:rgba(255,255,255,.9);
            font-size:.88rem;
            margin-bottom:.6rem;
        }
        .plan-item i { color:white; font-size:1rem; }

        /* FORM */
        .lado-der {
            flex:1;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:2rem;
        }
        .registro-box {
            width:100%;
            max-width:440px;
        }
        .registro-box h2 {
            font-family:'Playfair Display',serif;
            font-size:2rem;
            color:var(--verde);
            margin-bottom:.3rem;
        }
        .registro-box p.sub {
            color:#888;
            margin-bottom:2rem;
            font-size:.95rem;
        }
        .form-label {
            font-weight:600;
            font-size:.88rem;
            color:#444;
            margin-bottom:.4rem;
        }
        .form-control {
            border:2px solid #e8e8e8;
            border-radius:12px;
            padding:.75rem 1rem;
            font-size:.95rem;
            transition:border-color .2s, box-shadow .2s;
        }
        .form-control:focus {
            border-color:var(--verde);
            box-shadow:0 0 0 4px rgba(45,106,79,.1);
            outline:none;
        }
        .form-control.invalido { border-color:var(--naranja); }
        .form-control.valido   { border-color:var(--verde); }

        .input-group .form-control { border-right:none; border-radius:12px 0 0 12px; }
        .input-group .btn-ojo {
            border:2px solid #e8e8e8;
            border-left:none;
            border-radius:0 12px 12px 0;
            background:white;
            color:#888;
            padding:0 1rem;
        }
        .input-group .btn-ojo:hover { color:var(--verde); }

        .password-strength {
            height:4px;
            border-radius:4px;
            margin-top:.4rem;
            transition:all .3s;
            background:#eee;
        }
        .strength-text {
            font-size:.75rem;
            margin-top:.3rem;
            font-weight:600;
        }

        .btn-registro {
            background:var(--verde);
            color:white;
            border:none;
            border-radius:12px;
            padding:.85rem;
            font-size:1rem;
            font-weight:700;
            width:100%;
            cursor:pointer;
            transition:all .2s;
            margin-top:.5rem;
        }
        .btn-registro:hover {
            background:var(--verde-mid);
            transform:translateY(-2px);
            box-shadow:0 8px 25px rgba(45,106,79,.3);
        }
        .alert-error {
            background:#fff3f0;
            border:1.5px solid var(--naranja);
            border-radius:12px;
            padding:.9rem 1.1rem;
            color:#c0392b;
            font-size:.9rem;
            margin-bottom:1.2rem;
            display:flex;
            align-items:center;
            gap:.6rem;
        }
        .link-login {
            text-align:center;
            font-size:.92rem;
            color:#666;
            margin-top:1.5rem;
        }
        .link-login a {
            color:var(--verde);
            font-weight:700;
            text-decoration:none;
        }
        .terminos {
            font-size:.82rem;
            color:#999;
            text-align:center;
            margin-top:1rem;
            line-height:1.5;
        }

        @media(max-width:768px) {
            body { flex-direction:column; }
            .lado-izq { width:100%; padding:2rem; min-height:auto; }
            .plan-card { display:none; }
        }
    </style>
</head>
<body>

<!-- LADO IZQUIERDO -->
<div class="lado-izq">
    <a href="index.php" style="text-decoration:none">
        <div class="brand-logo">Nutri<span>Experto</span> 🌮</div>
    </a>
    <p class="brand-tagline">Únete gratis y empieza a descubrir recetas mexicanas saludables.</p>

    <div class="plan-card">
        <h4>✨ Cuenta gratuita incluye:</h4>
        <div class="plan-item"><i class="bi bi-check-circle-fill"></i> Acceso a 30+ recetas</div>
        <div class="plan-item"><i class="bi bi-check-circle-fill"></i> Análisis nutricional</div>
        <div class="plan-item"><i class="bi bi-check-circle-fill"></i> Últimas 5 consultas guardadas</div>
        <div class="plan-item"><i class="bi bi-check-circle-fill"></i> Recomendaciones del sistema experto</div>
        <div class="plan-item" style="opacity:.6"><i class="bi bi-lock-fill"></i> Recetas premium (upgrade)</div>
        <div class="plan-item" style="opacity:.6"><i class="bi bi-lock-fill"></i> Plan nutricional semanal</div>
    </div>
</div>

<!-- LADO DERECHO -->
<div class="lado-der">
    <div class="registro-box">
        <h2>Crear cuenta gratis 🎉</h2>
        <p class="sub">Solo toma 30 segundos, sin tarjeta de crédito</p>

        <?php if($error): ?>
            <div class="alert-error">
                <i class="bi bi-exclamation-circle-fill"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="registro.php" id="formRegistro">
            <div class="mb-3">
                <label class="form-label">Nombre completo</label>
                <input type="text" name="nombre" class="form-control"
                       placeholder="Tu nombre"
                       value="<?= htmlspecialchars($datos['nombre']) ?>"
                       required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label">Correo electrónico</label>
                <input type="email" name="email" class="form-control"
                       placeholder="tucorreo@ejemplo.com"
                       value="<?= htmlspecialchars($datos['email']) ?>"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <div class="input-group">
                    <input type="password" name="password" id="inputPass"
                           class="form-control" placeholder="Mínimo 6 caracteres"
                           oninput="medirFuerza(this.value)" required>
                    <button type="button" class="btn-ojo" onclick="togglePass('inputPass','iconOjo1')">
                        <i class="bi bi-eye" id="iconOjo1"></i>
                    </button>
                </div>
                <div class="password-strength" id="barraFuerza"></div>
                <div class="strength-text" id="textoFuerza"></div>
            </div>

            <div class="mb-3">
                <label class="form-label">Confirmar contraseña</label>
                <div class="input-group">
                    <input type="password" name="confirmar" id="inputConfirmar"
                           class="form-control" placeholder="Repite tu contraseña" required>
                    <button type="button" class="btn-ojo" onclick="togglePass('inputConfirmar','iconOjo2')">
                        <i class="bi bi-eye" id="iconOjo2"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-registro">
                <i class="bi bi-person-plus"></i> Crear cuenta gratis
            </button>
        </form>

        <p class="terminos">
            Al registrarte aceptas nuestros términos de uso.<br>
            Tu información está protegida y no se comparte.
        </p>

        <div class="link-login">
            ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
        </div>
    </div>
</div>

<script>
function togglePass(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    input.type  = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}

function medirFuerza(valor) {
    const barra  = document.getElementById('barraFuerza');
    const texto  = document.getElementById('textoFuerza');
    let fuerza   = 0;
    if (valor.length >= 6)                        fuerza++;
    if (valor.length >= 10)                       fuerza++;
    if (/[A-Z]/.test(valor))                      fuerza++;
    if (/[0-9]/.test(valor))                      fuerza++;
    if (/[^A-Za-z0-9]/.test(valor))              fuerza++;

    const niveles = [
        { color:'#e76f51', label:'Muy débil',  width:'20%' },
        { color:'#f4a261', label:'Débil',       width:'40%' },
        { color:'#e9c46a', label:'Regular',     width:'60%' },
        { color:'#74c69d', label:'Fuerte',      width:'80%' },
        { color:'#2d6a4f', label:'Muy fuerte',  width:'100%'},
    ];
    const nivel = niveles[Math.min(fuerza, 4)];
    barra.style.background = nivel.color;
    barra.style.width      = valor.length ? nivel.width : '0%';
    texto.textContent      = valor.length ? nivel.label : '';
    texto.style.color      = nivel.color;
}
</script>
</body>
</html>