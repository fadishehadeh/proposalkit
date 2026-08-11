<?php
declare(strict_types=1);

function admin_users_index(): void
{
    auth_superadmin_require();
    $users = db_all('
        SELECT u.*, GROUP_CONCAT(c.name ORDER BY c.name SEPARATOR ", ") AS company_names
        FROM users u
        LEFT JOIN user_companies uc ON uc.user_id = u.id
        LEFT JOIN companies c ON c.id = uc.company_id
        GROUP BY u.id
        ORDER BY u.name
    ');
    layout('admin.users.index', 'Users', compact('users'));
}

function admin_users_create(): void
{
    auth_superadmin_require();
    $companies = db_all('SELECT * FROM companies WHERE is_active=1 ORDER BY sort_order, name');
    layout('admin.users.form', 'New User', [
        'user'           => null,
        'companies'      => $companies,
        'userCompanyIds' => [],
    ]);
}

function admin_users_store(): void
{
    auth_superadmin_require();
    if (!csrf_verify()) {
        flash('error', 'Invalid request.');
        redirect('/admin/users/create');
    }

    $name       = trim($_POST['name']     ?? '');
    $email      = trim($_POST['email']    ?? '');
    $password   = $_POST['password']      ?? '';
    $role       = in_array($_POST['role'] ?? '', ['superadmin', 'user'], true) ? $_POST['role'] : 'user';
    $is_active  = isset($_POST['is_active']) ? 1 : 0;
    $companyIds = array_map('intval', (array) ($_POST['company_ids'] ?? []));

    $errors = [];
    if ($name === '')                                      $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))        $errors[] = 'Valid email is required.';
    if (strlen($password) < 6)                             $errors[] = 'Password must be at least 6 characters.';
    if (db_fetch('SELECT id FROM users WHERE email = ?', [$email])) $errors[] = 'Email already in use.';

    if ($errors) {
        set_old($_POST);
        flash('error', implode(' ', $errors));
        redirect('/admin/users/create');
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $id   = db_insert(
        'INSERT INTO users (name, email, password_hash, role, is_active) VALUES (?, ?, ?, ?, ?)',
        [$name, $email, $hash, $role, $is_active]
    );

    foreach (array_filter($companyIds) as $cid) {
        db_run('INSERT IGNORE INTO user_companies (user_id, company_id) VALUES (?, ?)', [$id, $cid]);
    }

    clear_old();
    flash('success', "User <strong>" . e($name) . "</strong> created.");
    redirect('/admin/users');
}

function admin_users_edit(int $id): void
{
    auth_superadmin_require();
    $user = db_fetch('SELECT * FROM users WHERE id = ?', [$id]);
    if (!$user) { flash('error', 'User not found.'); redirect('/admin/users'); }

    $companies      = db_all('SELECT * FROM companies WHERE is_active=1 ORDER BY sort_order, name');
    $userCompanyIds = array_map(
        'intval',
        array_column(db_all('SELECT company_id FROM user_companies WHERE user_id = ?', [$id]), 'company_id')
    );
    layout('admin.users.form', 'Edit User', compact('user', 'companies', 'userCompanyIds'));
}

function admin_users_update(int $id): void
{
    auth_superadmin_require();
    if (!csrf_verify()) {
        flash('error', 'Invalid request.');
        redirect("/admin/users/{$id}/edit");
    }

    $user = db_fetch('SELECT * FROM users WHERE id = ?', [$id]);
    if (!$user) { flash('error', 'User not found.'); redirect('/admin/users'); }

    $name       = trim($_POST['name']     ?? '');
    $email      = trim($_POST['email']    ?? '');
    $password   = $_POST['password']      ?? '';
    $role       = in_array($_POST['role'] ?? '', ['superadmin', 'user'], true) ? $_POST['role'] : 'user';
    $is_active  = isset($_POST['is_active']) ? 1 : 0;
    $companyIds = array_map('intval', (array) ($_POST['company_ids'] ?? []));

    $errors = [];
    if ($name === '')                           $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (db_fetch('SELECT id FROM users WHERE email = ? AND id != ?', [$email, $id])) $errors[] = 'Email already in use.';

    if ($errors) {
        set_old($_POST);
        flash('error', implode(' ', $errors));
        redirect("/admin/users/{$id}/edit");
    }

    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        db_run(
            'UPDATE users SET name=?, email=?, password_hash=?, role=?, is_active=?, updated_at=NOW() WHERE id=?',
            [$name, $email, $hash, $role, $is_active, $id]
        );
    } else {
        db_run(
            'UPDATE users SET name=?, email=?, role=?, is_active=?, updated_at=NOW() WHERE id=?',
            [$name, $email, $role, $is_active, $id]
        );
    }

    db_run('DELETE FROM user_companies WHERE user_id = ?', [$id]);
    foreach (array_filter($companyIds) as $cid) {
        db_run('INSERT IGNORE INTO user_companies (user_id, company_id) VALUES (?, ?)', [$id, $cid]);
    }

    // Refresh the session if the admin edited their own account
    if ((auth_user()['id'] ?? 0) === $id) {
        $refreshed = db_fetch('SELECT * FROM users WHERE id = ?', [$id]);
        if ($refreshed) auth_login($refreshed);
    }

    clear_old();
    flash('success', 'User updated.');
    redirect('/admin/users');
}

function admin_users_destroy(int $id): void
{
    auth_superadmin_require();
    if (!csrf_verify()) { flash('error', 'Invalid request.'); redirect('/admin/users'); }

    if ((auth_user()['id'] ?? 0) === $id) {
        flash('error', 'You cannot delete your own account.');
        redirect('/admin/users');
    }

    db_run('DELETE FROM users WHERE id = ?', [$id]);
    flash('success', 'User deleted.');
    redirect('/admin/users');
}
