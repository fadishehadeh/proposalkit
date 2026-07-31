<?php
declare(strict_types=1);

function rate_card_index(): void
{
    $companies         = db_all('SELECT * FROM companies WHERE is_active=1 ORDER BY sort_order, name');
    $companyId         = (int) ($_GET['company'] ?? 0) ?: null;
    $currencies        = config('currencies');
    $multipliers       = config('multipliers');
    $selected_mult     = (float) ($_GET['multiplier'] ?? 1.4);
    $selected_currency = in_array($_GET['currency'] ?? '', $currencies) ? $_GET['currency'] : 'AED';

    if ($companyId) {
        $positions = db_all('SELECT * FROM positions WHERE is_active=1 AND company_id=? ORDER BY sort_order, designation', [$companyId]);
    } else {
        $positions = db_all('SELECT p.*, c.name AS company_name FROM positions p LEFT JOIN companies c ON c.id=p.company_id WHERE p.is_active=1 ORDER BY c.sort_order, p.sort_order, p.designation');
    }

    layout('rate-card.index', 'Rate Card', compact(
        'positions', 'companies', 'currencies', 'multipliers',
        'selected_mult', 'selected_currency', 'companyId'
    ));
}

function proposals_index(): void
{
    $status  = $_GET['status'] ?? '';
    $allowed = ['', 'draft', 'sent', 'approved', 'rejected'];
    if (!in_array($status, $allowed, true)) $status = '';

    if ($status) {
        $proposals = db_all('
            SELECT pr.*, c.name AS company_name, c.logo_path AS company_logo
            FROM proposals pr
            LEFT JOIN companies c ON c.id = pr.company_id
            WHERE pr.status = ?
            ORDER BY pr.created_at DESC
        ', [$status]);
    } else {
        $proposals = db_all('
            SELECT pr.*, c.name AS company_name, c.logo_path AS company_logo
            FROM proposals pr
            LEFT JOIN companies c ON c.id = pr.company_id
            ORDER BY pr.created_at DESC
        ');
    }

    $counts = db_all('SELECT status, COUNT(*) AS n FROM proposals GROUP BY status');
    $statusCounts = ['draft' => 0, 'sent' => 0, 'approved' => 0, 'rejected' => 0];
    $total = 0;
    foreach ($counts as $row) {
        $statusCounts[$row['status']] = (int) $row['n'];
        $total += (int) $row['n'];
    }

    layout('proposals.index', 'Proposals', compact('proposals', 'status', 'statusCounts', 'total'));
}

function proposals_create(): void
{
    $companies   = db_all('SELECT * FROM companies WHERE is_active=1 ORDER BY sort_order, name');
    $clients     = db_all('SELECT * FROM clients WHERE is_active=1 ORDER BY name');
    $positions   = db_all('
        SELECT p.*, c.name AS company_name
        FROM positions p
        LEFT JOIN companies c ON c.id = p.company_id
        WHERE p.is_active=1
        ORDER BY c.sort_order, p.sort_order, p.designation
    ');
    $currencies  = config('currencies');
    $multipliers = config('multipliers');
    layout('proposals.create', 'New Proposal', compact('positions', 'companies', 'clients', 'currencies', 'multipliers'));
}

function proposals_store(): void
{
    if (!csrf_verify()) {
        flash('error', 'Invalid request.');
        redirect('/proposals/create');
    }

    $client_id    = (int) ($_POST['client_id']    ?? 0) ?: null;
    $project_name = trim($_POST['project_name']   ?? '');
    $multiplier   = (float) ($_POST['multiplier'] ?? 0);
    $currency     = $_POST['currency']            ?? 'AED';
    $notes        = trim($_POST['notes']          ?? '');
    $company_id   = (int) ($_POST['company_id']   ?? 0) ?: null;

    $currencies = config('currencies');
    $errors = [];
    if (!$client_id)          $errors[] = 'Please select a client.';
    if ($project_name === '') $errors[] = 'Project name is required.';
    if ($multiplier <= 0)     $errors[] = 'Please select a multiplier.';
    if (!in_array($currency, $currencies, true)) $errors[] = 'Invalid currency.';

    $client_name = '';
    if ($client_id) {
        $cl = db_fetch('SELECT name FROM clients WHERE id = ?', [$client_id]);
        if (!$cl) $errors[] = 'Selected client not found.';
        else       $client_name = $cl['name'];
    }

    $designations = $_POST['designation']    ?? [];
    $salaries     = $_POST['monthly_salary'] ?? [];
    $allocations  = $_POST['allocation']     ?? [];
    $position_ids = $_POST['position_id']    ?? [];

    $items = [];
    foreach ($designations as $i => $desig) {
        $desig = trim($desig);
        $sal   = (float) ($salaries[$i] ?? 0);
        $alloc = (float) ($allocations[$i] ?? 0);
        $pid   = ($position_ids[$i] ?? '') !== '' ? (int) $position_ids[$i] : null;
        if ($desig === '' || $sal <= 0 || $alloc <= 0) continue;
        $items[] = ['designation' => $desig, 'monthly_salary' => $sal, 'allocation' => $alloc, 'position_id' => $pid];
    }
    if (empty($items)) $errors[] = 'At least one position line is required.';

    if ($errors) {
        set_old($_POST);
        flash('error', implode(' ', $errors));
        redirect('/proposals/create');
    }

    $id = db_insert(
        'INSERT INTO proposals (company_id, client_id, client_name, project_name, multiplier, currency, notes) VALUES (?,?,?,?,?,?,?)',
        [$company_id, $client_id, $client_name, $project_name, $multiplier, $currency, $notes]
    );

    foreach ($items as $i => $item) {
        db_run(
            'INSERT INTO proposal_items (proposal_id, position_id, designation, monthly_salary, allocation, sort_order) VALUES (?,?,?,?,?,?)',
            [$id, $item['position_id'], $item['designation'], $item['monthly_salary'], $item['allocation'], $i]
        );
    }

    clear_old();
    flash('success', 'Proposal created.');
    redirect("/proposals/{$id}");
}

function proposals_show(int $id): void
{
    $proposal = db_fetch('
        SELECT pr.*, c.name AS company_name, c.logo_path AS company_logo, c.slug AS company_slug
        FROM proposals pr
        LEFT JOIN companies c ON c.id = pr.company_id
        WHERE pr.id = ?
    ', [$id]);
    if (!$proposal) { flash('error', 'Proposal not found.'); redirect('/proposals'); }

    $items = db_all('SELECT * FROM proposal_items WHERE proposal_id = ? ORDER BY sort_order', [$id]);

    // Version chain: find root then fetch all siblings
    $rootId   = $proposal['parent_id'] ?? $proposal['id'];
    $versions = db_all('
        SELECT id, version, status, created_at FROM proposals
        WHERE id = ? OR parent_id = ?
        ORDER BY version ASC
    ', [$rootId, $rootId]);

    layout('proposals.show',
        $proposal['client_name'] . ' — ' . $proposal['project_name'],
        compact('proposal', 'items', 'versions')
    );
}

function proposals_pdf(int $id): void
{
    $proposal = db_fetch('
        SELECT pr.*, c.name AS company_name, c.logo_path AS company_logo
        FROM proposals pr
        LEFT JOIN companies c ON c.id = pr.company_id
        WHERE pr.id = ?
    ', [$id]);
    if (!$proposal) { flash('error', 'Proposal not found.'); redirect('/proposals'); }
    $items = db_all('SELECT * FROM proposal_items WHERE proposal_id = ? ORDER BY sort_order', [$id]);
    view('proposals.pdf', compact('proposal', 'items'));
}

function proposals_status(int $id): void
{
    if (!csrf_verify()) { flash('error', 'Invalid request.'); redirect("/proposals/{$id}"); }
    $status  = $_POST['status'] ?? '';
    $allowed = ['draft', 'sent', 'approved', 'rejected'];
    if (!in_array($status, $allowed, true)) {
        flash('error', 'Invalid status.');
        redirect("/proposals/{$id}");
    }
    db_run('UPDATE proposals SET status = ? WHERE id = ?', [$status, $id]);
    $labels = ['draft' => 'Draft', 'sent' => 'Sent', 'approved' => 'Approved', 'rejected' => 'Rejected'];
    flash('success', 'Status changed to ' . $labels[$status] . '.');
    redirect("/proposals/{$id}");
}

function proposals_clone(int $id): void
{
    if (!csrf_verify()) { flash('error', 'Invalid request.'); redirect("/proposals/{$id}"); }
    $proposal = db_fetch('SELECT * FROM proposals WHERE id = ?', [$id]);
    if (!$proposal) { flash('error', 'Proposal not found.'); redirect('/proposals'); }

    $newId = db_insert(
        'INSERT INTO proposals (company_id, client_id, client_name, project_name, multiplier, currency, notes, status, version)
         VALUES (?,?,?,?,?,?,?,?,?)',
        [
            $proposal['company_id'], $proposal['client_id'],
            $proposal['client_name'],
            $proposal['project_name'] . ' (Copy)',
            $proposal['multiplier'], $proposal['currency'],
            $proposal['notes'], 'draft', 1,
        ]
    );

    $items = db_all('SELECT * FROM proposal_items WHERE proposal_id = ? ORDER BY sort_order', [$id]);
    foreach ($items as $item) {
        db_run(
            'INSERT INTO proposal_items (proposal_id, position_id, designation, monthly_salary, allocation, sort_order) VALUES (?,?,?,?,?,?)',
            [$newId, $item['position_id'], $item['designation'], $item['monthly_salary'], $item['allocation'], $item['sort_order']]
        );
    }

    flash('success', 'Proposal cloned as a new draft.');
    redirect("/proposals/{$newId}");
}

function proposals_version(int $id): void
{
    if (!csrf_verify()) { flash('error', 'Invalid request.'); redirect("/proposals/{$id}"); }
    $proposal = db_fetch('SELECT * FROM proposals WHERE id = ?', [$id]);
    if (!$proposal) { flash('error', 'Proposal not found.'); redirect('/proposals'); }

    // All versions share the same root (v1's id)
    $rootId     = $proposal['parent_id'] ?? $proposal['id'];
    $maxVersion = (int) (db_fetch(
        'SELECT MAX(version) AS v FROM proposals WHERE id = ? OR parent_id = ?',
        [$rootId, $rootId]
    )['v'] ?? 1);

    $newVersion = $maxVersion + 1;
    $newId = db_insert(
        'INSERT INTO proposals (company_id, client_id, client_name, project_name, multiplier, currency, notes, status, version, parent_id)
         VALUES (?,?,?,?,?,?,?,?,?,?)',
        [
            $proposal['company_id'], $proposal['client_id'],
            $proposal['client_name'], $proposal['project_name'],
            $proposal['multiplier'], $proposal['currency'],
            $proposal['notes'], 'draft', $newVersion, $rootId,
        ]
    );

    $items = db_all('SELECT * FROM proposal_items WHERE proposal_id = ? ORDER BY sort_order', [$id]);
    foreach ($items as $item) {
        db_run(
            'INSERT INTO proposal_items (proposal_id, position_id, designation, monthly_salary, allocation, sort_order) VALUES (?,?,?,?,?,?)',
            [$newId, $item['position_id'], $item['designation'], $item['monthly_salary'], $item['allocation'], $item['sort_order']]
        );
    }

    flash('success', "v{$newVersion} created as draft. Original v{$proposal['version']} is preserved.");
    redirect("/proposals/{$newId}");
}

function proposals_destroy(int $id): void
{
    if (!csrf_verify()) { flash('error', 'Invalid request.'); redirect('/proposals'); }
    db_run('DELETE FROM proposals WHERE id = ?', [$id]);
    flash('success', 'Proposal deleted.');
    redirect('/proposals');
}
