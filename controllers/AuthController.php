<?php
declare(strict_types=1);

function auth_login_page(): void
{
    if (auth_check()) redirect('/');
    view('auth.login', ['pageTitle' => 'Sign In']);
}

function auth_login_submit(): void
{
    if (!csrf_verify()) {
        flash('error', 'Invalid request.');
        redirect('/login');
    }

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = db_fetch('SELECT * FROM users WHERE email = ? AND is_active = 1', [$email]);
    if (!$user || !password_verify($password, $user['password_hash'])) {
        flash('error', 'Invalid email or password.');
        redirect('/login');
    }

    auth_login($user);
    redirect('/');
}

function auth_logout_action(): void
{
    auth_logout();
    redirect('/login');
}
