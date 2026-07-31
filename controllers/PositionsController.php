<?php
declare(strict_types=1);

function positions_index(): void
{
    $companies = db_all('SELECT * FROM companies WHERE is_active=1 ORDER BY sort_order, name');
    $companyId = (int) ($_GET['company'] ?? 0) ?: null;

    if ($companyId) {
        $positions    = db_all('SELECT p.*, c.name AS company_name FROM positions p LEFT JOIN companies c ON c.id = p.company_id WHERE p.company_id = ? ORDER BY p.sort_order, p.designation', [$companyId]);
        $activeCompany = db_fetch('SELECT * FROM companies WHERE id = ?', [$companyId]);
    } else {
        $positions    = db_all('SELECT p.*, c.name AS company_name FROM positions p LEFT JOIN companies c ON c.id = p.company_id ORDER BY c.sort_order, p.sort_order, p.designation');
        $activeCompany = null;
    }

    layout('positions.index', 'Position Database', compact('positions', 'companies', 'companyId', 'activeCompany'));
}

function positions_create(): void
{
    $companies = db_all('SELECT * FROM companies WHERE is_active=1 ORDER BY sort_order, name');
    $presetCompanyId = (int) ($_GET['company'] ?? 0) ?: null;
    layout('positions.form', 'Add Position', [
        'position'        => null,
        'action'          => url('/positions/create'),
        'companies'       => $companies,
        'presetCompanyId' => $presetCompanyId,
    ]);
}

function positions_store(): void
{
    if (!csrf_verify()) {
        flash('error', 'Invalid request.');
        redirect('/positions/create');
    }

    $designation    = trim($_POST['designation'] ?? '');
    $monthly_salary = (float) ($_POST['monthly_salary'] ?? 0);
    $sort_order     = (int) ($_POST['sort_order'] ?? 0);
    $company_id     = (int) ($_POST['company_id'] ?? 0) ?: null;

    $errors = [];
    if ($designation === '') $errors[] = 'Designation is required.';
    if ($monthly_salary <= 0) $errors[] = 'Monthly salary must be greater than zero.';

    if ($errors) {
        set_old($_POST);
        flash('error', implode(' ', $errors));
        redirect('/positions/create');
    }

    db_insert(
        'INSERT INTO positions (company_id, designation, monthly_salary, sort_order) VALUES (?, ?, ?, ?)',
        [$company_id, $designation, $monthly_salary, $sort_order]
    );
    clear_old();
    flash('success', "Position <strong>" . e($designation) . "</strong> added.");
    $back = $company_id ? "/positions?company={$company_id}" : '/positions';
    redirect($back);
}

function positions_edit(int $id): void
{
    $position  = db_fetch('SELECT * FROM positions WHERE id = ?', [$id]);
    if (!$position) { flash('error', 'Position not found.'); redirect('/positions'); }
    $companies = db_all('SELECT * FROM companies WHERE is_active=1 ORDER BY sort_order, name');
    layout('positions.form', 'Edit Position', [
        'position'        => $position,
        'action'          => url("/positions/{$id}/edit"),
        'companies'       => $companies,
        'presetCompanyId' => (int) ($position['company_id'] ?? 0) ?: null,
    ]);
}

function positions_update(int $id): void
{
    if (!csrf_verify()) {
        flash('error', 'Invalid request.');
        redirect("/positions/{$id}/edit");
    }

    $position = db_fetch('SELECT * FROM positions WHERE id = ?', [$id]);
    if (!$position) { flash('error', 'Position not found.'); redirect('/positions'); }

    $designation    = trim($_POST['designation'] ?? '');
    $monthly_salary = (float) ($_POST['monthly_salary'] ?? 0);
    $sort_order     = (int) ($_POST['sort_order'] ?? 0);
    $is_active      = isset($_POST['is_active']) ? 1 : 0;
    $company_id     = (int) ($_POST['company_id'] ?? 0) ?: null;

    $errors = [];
    if ($designation === '') $errors[] = 'Designation is required.';
    if ($monthly_salary <= 0) $errors[] = 'Monthly salary must be greater than zero.';

    if ($errors) {
        set_old($_POST);
        flash('error', implode(' ', $errors));
        redirect("/positions/{$id}/edit");
    }

    db_run(
        'UPDATE positions SET company_id=?, designation=?, monthly_salary=?, sort_order=?, is_active=?, updated_at=NOW() WHERE id=?',
        [$company_id, $designation, $monthly_salary, $sort_order, $is_active, $id]
    );
    clear_old();
    flash('success', 'Position updated.');
    $back = $company_id ? "/positions?company={$company_id}" : '/positions';
    redirect($back);
}

function positions_destroy(int $id): void
{
    if (!csrf_verify()) { flash('error', 'Invalid request.'); redirect('/positions'); }
    $pos = db_fetch('SELECT company_id FROM positions WHERE id = ?', [$id]);
    $used = db_fetch('SELECT id FROM proposal_items WHERE position_id = ? LIMIT 1', [$id]);
    if ($used) {
        flash('error', 'Cannot delete — this position is used in one or more proposals. Deactivate it instead.');
        redirect('/positions');
    }
    db_run('DELETE FROM positions WHERE id = ?', [$id]);
    flash('success', 'Position deleted.');
    $back = $pos && $pos['company_id'] ? "/positions?company={$pos['company_id']}" : '/positions';
    redirect($back);
}
