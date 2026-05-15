<?php
session_start();

function estaLogueado() {
    return isset($_SESSION['usuario_id']);
}

function esPremium() {
    return isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'premium';
}

function esAdmin() {
    return isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'admin';
}

function datosUsuario() {
    if (!estaLogueado()) return null;
    return [
        'id'     => $_SESSION['usuario_id'],
        'nombre' => $_SESSION['nombre'],
        'email'  => $_SESSION['email'],
        'tipo'   => $_SESSION['tipo']
    ];
}

function iniciarSesionUsuario($usuario) {
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['nombre']     = $usuario['nombre'];
    $_SESSION['email']      = $usuario['email'];
    $_SESSION['tipo']       = $usuario['tipo'];
}

function cerrarSesion() {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

function requiereLogin() {
    if (!estaLogueado()) {
        header('Location: /login.php?msg=requiere_login');
        exit();
    }
}

function requierePremium() {
    if (!esPremium()) {
        header('Location: /premium.php?msg=requiere_premium');
        exit();
    }
}
?>