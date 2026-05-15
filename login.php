<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (estaLogueado()) {
    header('Location: index.php');
    exit();
}

$error = '';
$msg   = $_GET['msg'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Por favor completa todos los campos.';
    } else {
        $conn = conectar();
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ? AND activo = 1 LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario   = $resultado->fetch_assoc();

        if ($usuario && password_verify($password, $usuario['password'])) {
            iniciarSesionUsuario($usuario);
            header('Location: index.php');
            exit();
        } else {
            $error = 'Correo o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — NutriExperto 🌮</title>
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

        /* LADO IZQUIERDO */
        .lado-izq {
            width:45%;
            background:linear-gradient(160deg, var(--verde) 0%, var(--verde-mid) 100%);
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
            width:400px; height:400px;
            border-radius:50%;
            background:rgba(255,255,255,.05);
            top:-100px; left:-100px;
        }
        .lado-izq::after {
            content:'';
            position:absolute;
            width:300px; height:300px;
            border-radius:50%;
            background:rgba(255,255,255,.05);
            bottom:-80px; right:-80px;
        }
        .brand-logo {
            font-family:'Playfair Display',serif;
            font-size:2.5rem;
            color:white;
            margin-bottom:.5rem;
            position:relative;
            z-index:1;
        }
        .brand-logo span { color:var(--dorado); }
        .brand-tagline {
            color:rgba(255,255,255,.75);
            font-size:1rem;
            text-align:center;
            position:relative;
            z-index:1;
            line-height:1.6;
            max-width:280px;
        }
        .features-list {
            list-style:none;
            padding:0;
            margin-top:2.5rem;
            position:relative;
            z-index:1;
            width:100%;
            max-width:280px;
        }
        .features-list li {
            display:flex;
            align-items:center;
            gap:.8rem;
            color:rgba(255,255,255,.85);
            font-size:.9rem;
            padding:.6rem 0;
            border-bottom:1px solid rgba(255,255,255,.1);
        }
        .features-list li:last-child { border-bottom:none; }
        .feature-icon {
            width:32px; height:32px;
            background:rgba(255,255,255,.15);
            border-radius:8px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:1rem;
            flex-shrink:0;
        }

        /* LADO DERECHO */
        .lado-der {
            flex:1;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:2rem;
        }
        .login-box {
            width:100%;
            max-width:420px;
        }
        .login-box h2 {
            font-family:'Playfair Display',serif;
            font-size:2rem;
            color:var(--verde);
            margin-bottom:.3rem;
        }
        .login-box p.sub {
            color:#888;
            margin-bottom:2rem;
            font-size:.95rem;
        }

        /* FORMULARIO */
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
            background:white;
        }
        .form-control:focus {
            border-color:var(--verde);
            box-shadow:0 0 0 4px rgba(45,106,79,.1);
            outline:none;
        }
        .input-group .form-control { border-right:none; border-radius:12px 0 0 12px; }
        .input-group .btn-ojo {
            border:2px solid #e8e8e8;
            border-left:none;
            border-radius:0 12px 12px 0;
            background:white;
            color:#888;
            padding:0 1rem;
            transition:color .2s;
        }
        .input-group .btn-ojo:hover { color:var(--verde); }

        .btn-login {
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
        .btn-login:hover {
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
        .alert-info-msg {
            background:#f0faf5;
            border:1.5px solid var(--verde);
            border-radius:12px;
            padding:.9rem 1.1rem;
            color:var(--verde);
            font-size:.9rem;
            margin-bottom:1.2rem;
        }

        .divider {
            display:flex;
            align-items:center;
            gap:1rem;
            margin:1.5rem 0;
            color:#ccc;
            font-size:.85rem;
        }
        .divider::before,
        .divider::after {
            content:'';
            flex:1;
            height:1px;
            background:#e8e8e8;
        }

        .link-registro {
            text-align:center;
            font-size:.92rem;
            color:#666;
            margin-top:1.5rem;
        }
        .link-registro a {
            color:var(--verde);
            font-weight:700;
            text-decoration:none;
        }
        .link-registro a:hover { text-decoration:underline; }

        .btn-invitado {
            width:100%;
            background:white;
            border:2px solid #e8e8e8;
            border-radius:12px;
            padding:.75rem;
            font-size:.92rem;
            font-weight:600;
            color:#555;
            cursor:pointer;
            transition:all .2s;
            text-decoration:none;
            display:block;
            text-align:center;
        }
        .btn-invitado:hover {
            border-color:var(--verde);
            color:var(--verde);
        }

        @media(max-width:768px) {
            body { flex-direction:column; }
            .lado-izq { width:100%; padding:2.5rem 2rem; min-height:auto; }
            .features-list { display:none; }
            .brand-tagline { max-width:100%; }
        }
    </style>
</head>
<body>

<!-- LADO IZQUIERDO -->
<div class="lado-izq">
    <a href="index.php" style="text-decoration:none">
        <div class="brand-logo">Nutri<span>Experto</span> 🌮</div>
    </a>
    <p class="brand-tagline">
        Tu sistema experto de recetas saludables basado en la cocina mexicana tradicional.
    </p>
    <ul class="features-list">
        <li>
            <div class="feature-icon">🧠</div>
            Sistema experto con motor de inferencia
        </li>
        <li>
            <div class="feature-icon">🌮</div>
            40+ recetas mexicanas saludables
        </li>
        <li>
            <div class="feature-icon">📊</div>
            Análisis nutricional personalizado
        </li>
        <li>
            <div class="feature-icon">🔖</div>
            Guarda tu historial de recetas
        </li>
        <li>
            <div class="feature-icon">⭐</div>
            Plan nutricional semanal Premium
        </li>
    </ul>
</div>

<!-- LADO DERECHO -->
<div class="lado-der">
    <div class="login-box">
        <h2>Bienvenido de vuelta 👋</h2>
        <p class="sub">Inicia sesión para acceder a tu historial</p>

        <?php if($error): ?>
            <div class="alert-error">
                <i class="bi bi-exclamation-circle-fill"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if($msg === 'requiere_login'): ?>
            <div class="alert-info-msg">
                <i class="bi bi-info-circle"></i> Necesitas iniciar sesión para acceder a esa sección.
            </div>
        <?php endif; ?>

        <?php if($msg === 'registro_exitoso'): ?>
            <div class="alert-info-msg">
                <i class="bi bi-check-circle"></i> ¡Cuenta creada exitosamente! Ya puedes iniciar sesión.
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="mb-3">
                <label class="form-label">Correo electrónico</label>
                <input type="email" name="email" class="form-control"
                       placeholder="tucorreo@ejemplo.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <div class="input-group">
                    <input type="password" name="password" id="inputPassword"
                           class="form-control" placeholder="Tu contraseña" required>
                    <button type="button" class="btn-ojo" onclick="togglePassword()">
                        <i class="bi bi-eye" id="iconOjo"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right"></i> Iniciar sesión
            </button>
        </form>

        <div class="divider">o continúa sin cuenta</div>

        <a href="index.php" class="btn-invitado">
            <i class="bi bi-person-dash"></i> Entrar como invitado
        </a>

        <div class="link-registro">
            ¿No tienes cuenta? <a href="registro.php">Regístrate gratis</a>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('inputPassword');
    const icon  = document.getElementById('iconOjo');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
</body>
</html>