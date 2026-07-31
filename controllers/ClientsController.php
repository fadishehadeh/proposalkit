<?php
declare(strict_types=1);

function clients_index(): void
{
    $clients = db_all('
        SELECT cl.*, COUNT(pr.id) AS proposal_count
        FROM clients cl
        LEFT JOIN proposals pr ON pr.client_id = cl.id
        GROUP BY cl.id
        ORDER BY cl.name
    ');
    layout('clients.index', 'Clients', compact('clients'));
}

function clients_create(): void
{
    layout('clients.form', 'New Client', ['client' => null, 'editing' => false]);
}

function clients_store(): void
{
    if (!csrf_verify()) { flash('error', 'Invalid request.'); redirect('/clients/create'); }

    $name          = trim($_POST['name']          ?? '');
    $contact_name  = trim($_POST['contact_name']  ?? '');
    $contact_email = trim($_POST['contact_email'] ?? '');
    $contact_phone = trim($_POST['contact_phone'] ?? '');
    $industry      = trim($_POST['industry']      ?? '');
    $notes         = trim($_POST['notes']         ?? '');

    $errors = [];
    if ($name === '') $errors[] = 'Client name is required.';
    if ($contact_email !== '' && !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Contact email is not valid.';
    }

    if ($errors) {
        set_old($_POST);
        flash('error', implode(' ', $errors));
        redirect('/clients/create');
    }

    db_insert(
        'INSERT INTO clients (name, contact_name, contact_email, contact_phone, industry, notes) VALUES (?,?,?,?,?,?)',
        [$name, $contact_name ?: null, $contact_email ?: null, $contact_phone ?: null, $industry ?: null, $notes ?: null]
    );

    clear_old();
    flash('success', "Client \"{$name}\" added.");
    redirect('/clients');
}

function clients_edit(int $id): void
{
    $client = db_fetch('SELECT * FROM clients WHERE id = ?', [$id]);
    if (!$client) { flash('error', 'Client not found.'); redirect('/clients'); }
    layout('clients.form', 'Edit Client', ['client' => $client, 'editing' => true]);
}

function clients_update(int $id): void
{
    if (!csrf_verify()) { flash('error', 'Invalid request.'); redirect('/clients'); }

    $client = db_fetch('SELECT * FROM clients WHERE id = ?', [$id]);
    if (!$client) { flash('error', 'Client not found.'); redirect('/clients'); }

    $name          = trim($_POST['name']          ?? '');
    $contact_name  = trim($_POST['contact_name']  ?? '');
    $contact_email = trim($_POST['contact_email'] ?? '');
    $contact_phone = trim($_POST['contact_phone'] ?? '');
    $industry      = trim($_POST['industry']      ?? '');
    $notes         = trim($_POST['notes']         ?? '');
    $is_active     = isset($_POST['is_active']) ? 1 : 0;

    $errors = [];
    if ($name === '') $errors[] = 'Client name is required.';
    if ($contact_email !== '' && !filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Contact email is not valid.';
    }

    if ($errors) {
        set_old($_POST);
        flash('error', implode(' ', $errors));
        redirect("/clients/{$id}/edit");
    }

    db_run(
        'UPDATE clients SET name=?, contact_name=?, contact_email=?, contact_phone=?, industry=?, notes=?, is_active=? WHERE id=?',
        [$name, $contact_name ?: null, $contact_email ?: null, $contact_phone ?: null, $industry ?: null, $notes ?: null, $is_active, $id]
    );

    clear_old();
    flash('success', 'Client updated.');
    redirect('/clients');
}

function clients_destroy(int $id): void
{
    if (!csrf_verify()) { flash('error', 'Invalid request.'); redirect('/clients'); }

    $client = db_fetch('SELECT * FROM clients WHERE id = ?', [$id]);
    if (!$client) { flash('error', 'Client not found.'); redirect('/clients'); }

    $count = (int) (db_fetch('SELECT COUNT(*) AS n FROM proposals WHERE client_id = ?', [$id])['n'] ?? 0);
    if ($count > 0) {
        flash('error', "Cannot delete — this client has {$count} proposal(s). Archive the client instead.");
        redirect('/clients');
    }

    db_run('DELETE FROM clients WHERE id = ?', [$id]);
    flash('success', 'Client deleted.');
    redirect('/clients');
}
