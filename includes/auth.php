<?php

function auth_user()
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT id, username, email, phone, address, role FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        unset($_SESSION['user_id'], $_SESSION['role']);
        return null;
    }

    return $user;
}

function is_logged_in()
{
    return !empty($_SESSION['user_id']);
}

function is_admin()
{
    return (($_SESSION['role'] ?? '') === 'admin');
}

function login_user(array $user)
{
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
}

function logout_user()
{
    unset($_SESSION['user_id'], $_SESSION['role']);
}

function require_login()
{
    if (!is_logged_in()) {
        redirect_to('auth/login.php');
    }
}

function require_admin()
{
    require_login();

    if (!is_admin()) {
        redirect_to('index.php');
    }
}
