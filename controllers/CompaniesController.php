<?php
declare(strict_types=1);

function companies_index(): void
{
    $companies = db_all('
        SELECT c.*,
               (SELECT COUNT(*) FROM positions p WHERE p.company_id = c.id) AS position_count,
               (SELECT COUNT(*) FROM proposals pr WHERE pr.company_id = c.id) AS proposal_count
        FROM companies c
        ORDER BY c.sort_order, c.name
    ');
    layout('companies.index', 'Companies', compact('companies'));
}

function companies_create(): void
{
    layout('companies.form', 'Add Company', [
        'company' => null,
        'action'  => url('/companies/create'),
    ]);
}

function companies_store(): void
{
    if (!csrf_verify()) {
        flash('error', 'Invalid request.');
        redirect('/companies/create');
    }

    $name       = trim($_POST['name'] ?? '');
    $sort_order = (int) ($_POST['sort_order'] ?? 0);

    $slug = strtolower(trim($_POST['slug'] ?? ''));
    if ($slug === '') {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
        $slug = trim($slug, '-');
    }

    $errors = [];
    if ($name === '') $errors[] = 'Company name is required.';
    if (!preg_match('/^[a-z0-9-]+$/', $slug)) $errors[] = 'Slug may only contain lowercase letters, numbers, and hyphens.';
    if (db_fetch('SELECT id FROM companies WHERE slug = ?', [$slug])) $errors[] = 'That slug is already taken.';

    $logo_path = handle_logo_upload($_FILES['logo'] ?? null, $errors);

    if ($errors) {
        set_old($_POST);
        flash('error', implode(' ', $errors));
        redirect('/companies/create');
    }

    db_insert(
        'INSERT INTO companies (name, slug, logo_path, sort_order) VALUES (?,?,?,?)',
        [$name, $slug, $logo_path, $sort_order]
    );
    clear_old();
    flash('success', "Company <strong>" . e($name) . "</strong> added.");
    redirect('/companies');
}

function companies_edit(int $id): void
{
    $company = db_fetch('SELECT * FROM companies WHERE id = ?', [$id]);
    if (!$company) { flash('error', 'Company not found.'); redirect('/companies'); }
    layout('companies.form', 'Edit Company', [
        'company' => $company,
        'action'  => url("/companies/{$id}/edit"),
    ]);
}

function companies_update(int $id): void
{
    if (!csrf_verify()) {
        flash('error', 'Invalid request.');
        redirect("/companies/{$id}/edit");
    }

    $company = db_fetch('SELECT * FROM companies WHERE id = ?', [$id]);
    if (!$company) { flash('error', 'Company not found.'); redirect('/companies'); }

    $name       = trim($_POST['name'] ?? '');
    $sort_order = (int) ($_POST['sort_order'] ?? 0);
    $is_active  = isset($_POST['is_active']) ? 1 : 0;

    $slug = strtolower(trim($_POST['slug'] ?? ''));
    if ($slug === '') {
        $slug = $company['slug'];
    }

    $errors = [];
    if ($name === '') $errors[] = 'Company name is required.';
    if (!preg_match('/^[a-z0-9-]+$/', $slug)) $errors[] = 'Slug may only contain lowercase letters, numbers, and hyphens.';

    $existing = db_fetch('SELECT id FROM companies WHERE slug = ? AND id != ?', [$slug, $id]);
    if ($existing) $errors[] = 'That slug is already taken by another company.';

    $logo_path = $company['logo_path'];

    // Handle logo removal
    if (isset($_POST['remove_logo']) && $logo_path) {
        delete_logo_file($logo_path);
        $logo_path = null;
    }

    // Handle new logo upload
    $uploaded = handle_logo_upload($_FILES['logo'] ?? null, $errors);
    if ($uploaded !== null) {
        if ($logo_path) delete_logo_file($logo_path); // remove old
        $logo_path = $uploaded;
    }

    if ($errors) {
        set_old($_POST);
        flash('error', implode(' ', $errors));
        redirect("/companies/{$id}/edit");
    }

    db_run(
        'UPDATE companies SET name=?, slug=?, logo_path=?, is_active=?, sort_order=?, updated_at=NOW() WHERE id=?',
        [$name, $slug, $logo_path, $is_active, $sort_order, $id]
    );
    clear_old();
    flash('success', 'Company updated.');
    redirect('/companies');
}

function companies_destroy(int $id): void
{
    if (!csrf_verify()) { flash('error', 'Invalid request.'); redirect('/companies'); }

    $company = db_fetch('SELECT * FROM companies WHERE id = ?', [$id]);
    if (!$company) { flash('error', 'Company not found.'); redirect('/companies'); }

    $hasPositions = db_fetch('SELECT id FROM positions WHERE company_id = ? LIMIT 1', [$id]);
    $hasProposals = db_fetch('SELECT id FROM proposals WHERE company_id = ? LIMIT 1', [$id]);
    if ($hasPositions || $hasProposals) {
        flash('error', 'Cannot delete — this company has positions or proposals. Deactivate it instead.');
        redirect('/companies');
    }

    if ($company['logo_path']) delete_logo_file($company['logo_path']);
    db_run('DELETE FROM companies WHERE id = ?', [$id]);
    flash('success', 'Company deleted.');
    redirect('/companies');
}

function handle_logo_upload(?array $file, array &$errors): ?string
{
    if (!$file || empty($file['name'])) return null;
    if ($file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) { $errors[] = 'Logo upload failed (error ' . $file['error'] . ').'; return null; }
    if ($file['size'] > 5 * 1024 * 1024) { $errors[] = 'Logo must be under 5 MB.'; return null; }

    $allowed = [
        'image/png'     => 'png',
        'image/jpeg'    => 'jpg',
        'image/gif'     => 'gif',
        'image/svg+xml' => 'svg',
        'image/webp'    => 'webp',
    ];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        $errors[] = 'Logo must be PNG, JPG, GIF, SVG, or WebP.';
        return null;
    }

    $ext      = $allowed[$mime];
    $filename = 'logo_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest     = BASE_PATH . '/public/logos/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        $errors[] = 'Could not save the logo file.';
        return null;
    }
    return 'logos/' . $filename;
}

function delete_logo_file(string $logo_path): void
{
    $full = BASE_PATH . '/public/' . $logo_path;
    if (file_exists($full)) @unlink($full);
}
