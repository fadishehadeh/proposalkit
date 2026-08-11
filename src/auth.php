<?php
declare(strict_types=1);

function auth_user(): ?array
{
    return $_SESSION['auth_user'] ?? null;
}

function auth_check(): bool
{
    return isset($_SESSION['auth_user']);
}

function auth_require(): void
{
    if (!auth_check()) {
        redirect('/login');
    }
}

function auth_superadmin(): bool
{
    $u = auth_user();
    return $u !== null && $u['role'] === 'superadmin';
}

function auth_superadmin_require(): void
{
    auth_require();
    if (!auth_superadmin()) {
        flash('error', 'Access denied.');
        redirect('/');
    }
}

/**
 * Returns null for superadmin (no filter) or an int[] of allowed company IDs.
 */
function auth_company_ids(): ?array
{
    if (auth_superadmin()) return null;
    $u = auth_user();
    return $u['company_ids'] ?? [];
}

/**
 * Returns ['sql' => ' AND col IN (?,?)', 'params' => [1,2]] for use in raw queries.
 * Superadmin returns empty sql/params (no restriction).
 * User with no companies returns a clause that matches nothing.
 */
function auth_company_in(string $column): array
{
    $ids = auth_company_ids();
    if ($ids === null) {
        return ['sql' => '', 'params' => []];
    }
    if (empty($ids)) {
        return ['sql' => " AND {$column} IN (-1)", 'params' => []];
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    return ['sql' => " AND {$column} IN ({$placeholders})", 'params' => $ids];
}

function auth_login(array $user): void
{
    $rows       = db_all('SELECT company_id FROM user_companies WHERE user_id = ?', [$user['id']]);
    $companyIds = array_map('intval', array_column($rows, 'company_id'));

    $_SESSION['auth_user'] = [
        'id'          => (int) $user['id'],
        'name'        => $user['name'],
        'email'       => $user['email'],
        'role'        => $user['role'],
        'company_ids' => $companyIds,
    ];
}

function auth_logout(): void
{
    unset($_SESSION['auth_user']);
}
