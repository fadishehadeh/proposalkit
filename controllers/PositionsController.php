<?php
declare(strict_types=1);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

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

function positions_export(): void
{
    $companyId = (int) ($_GET['company'] ?? 0) ?: null;

    if ($companyId) {
        $positions = db_all(
            'SELECT p.*, c.name AS company_name FROM positions p LEFT JOIN companies c ON c.id = p.company_id WHERE p.company_id = ? ORDER BY p.sort_order, p.designation',
            [$companyId]
        );
    } else {
        $positions = db_all(
            'SELECT p.*, c.name AS company_name FROM positions p LEFT JOIN companies c ON c.id = p.company_id ORDER BY c.sort_order, p.sort_order, p.designation'
        );
    }

    $ss = new Spreadsheet();
    $ws = $ss->getActiveSheet();
    $ws->setTitle('Positions');

    $ws->mergeCells('A1:G1');
    $ws->setCellValue('A1', 'Position Database — ProposalKit');
    $ws->getStyle('A1')->applyFromArray([
        'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF1E3A5F']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
    ]);
    $ws->getRowDimension(1)->setRowHeight(22);

    $ws->mergeCells('A2:G2');
    $ws->setCellValue('A2', 'Tip: Edit columns D-G freely. Do not change column A (ID). Leave A blank to add new rows; column B (Company ID) is required for new rows. Active: 1=Active, 0=Inactive.');
    $ws->getStyle('A2')->applyFromArray([
        'font' => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF64748B']],
    ]);
    $ws->getRowDimension(2)->setRowHeight(16);

    $headers = ['ID', 'Company ID', 'Company', 'Designation', 'Monthly Salary', 'Sort Order', 'Active'];
    foreach ($headers as $i => $h) {
        $ws->setCellValue(chr(65 + $i) . '3', $h);
    }
    $ws->getStyle('A3:G3')->applyFromArray([
        'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    ]);
    $ws->getRowDimension(3)->setRowHeight(20);

    $refStyle = [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE8EEF7']],
        'font' => ['color' => ['argb' => 'FF475569']],
    ];

    $row = 4;
    foreach ($positions as $p) {
        $isActiveVal = ($p['is_active'] === null) ? 1 : (int) $p['is_active'];
        $ws->setCellValue("A{$row}", (int) $p['id']);
        $ws->setCellValue("B{$row}", (int) ($p['company_id'] ?? 0));
        $ws->setCellValue("C{$row}", $p['company_name'] ?? '');
        $ws->setCellValue("D{$row}", $p['designation']);
        $ws->setCellValue("E{$row}", (float) $p['monthly_salary']);
        $ws->setCellValue("F{$row}", (int) ($p['sort_order'] ?? 0));
        $ws->setCellValue("G{$row}", $isActiveVal);
        $ws->getStyle("A{$row}:C{$row}")->applyFromArray($refStyle);
        $ws->getStyle("E{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        if ($row % 2 === 0) {
            $ws->getStyle("D{$row}:G{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
            ]);
        }
        $row++;
    }

    $ws->getColumnDimension('A')->setWidth(8);
    $ws->getColumnDimension('B')->setWidth(12);
    $ws->getColumnDimension('C')->setWidth(22);
    $ws->getColumnDimension('D')->setWidth(38);
    $ws->getColumnDimension('E')->setWidth(16);
    $ws->getColumnDimension('F')->setWidth(12);
    $ws->getColumnDimension('G')->setWidth(10);

    if ($row > 4) {
        $ws->getStyle('A3:G' . ($row - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
        ]);
    }

    $ws->freezePane('A4');

    $filename = 'positions-' . date('Y-m-d') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    (new Xlsx($ss))->save('php://output');
    exit;
}

function positions_import(): void
{
    layout('positions.import', 'Import Positions');
}

function positions_import_process(): void
{
    if (!csrf_verify()) {
        flash('error', 'Invalid request.');
        redirect('/positions/import');
    }

    if (empty($_FILES['file']['tmp_name']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        flash('error', 'Please upload an Excel file (.xlsx).');
        redirect('/positions/import');
    }

    $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
    if ($ext !== 'xlsx') {
        flash('error', 'Only .xlsx files are supported.');
        redirect('/positions/import');
    }

    try {
        $reader = new XlsxReader();
        $reader->setReadDataOnly(true);
        $ss = $reader->load($_FILES['file']['tmp_name']);
        $ws = $ss->getActiveSheet();
    } catch (\Exception $e) {
        flash('error', 'Could not read the file: ' . htmlspecialchars($e->getMessage()));
        redirect('/positions/import');
    }

    $validCompanyIds = array_flip(array_map('intval', array_column(db_all('SELECT id FROM companies'), 'id')));

    $updated     = 0;
    $inserted    = 0;
    $deleted     = 0;
    $deactivated = 0;
    $skipped     = 0;
    $errors      = [];
    $seenIds     = [];   // IDs present in the Excel file
    $highestRow  = $ws->getHighestRow();

    // Pass 1: update/insert all rows from the file
    for ($row = 4; $row <= $highestRow; $row++) {
        $id            = trim((string) $ws->getCell("A{$row}")->getValue());
        $companyId     = trim((string) $ws->getCell("B{$row}")->getValue());
        $designation   = trim((string) $ws->getCell("D{$row}")->getValue());
        $monthlySalary = trim((string) $ws->getCell("E{$row}")->getValue());
        $sortOrder     = trim((string) $ws->getCell("F{$row}")->getValue());
        $isActive      = trim((string) $ws->getCell("G{$row}")->getValue());

        if ($designation === '') continue;

        $monthlySalary = (float) str_replace(',', '', $monthlySalary);
        if ($monthlySalary <= 0) {
            $errors[] = "Row {$row}: monthly salary must be > 0 for \"{$designation}\".";
            $skipped++;
            continue;
        }

        $sortOrderInt = (int) $sortOrder;
        $isActiveInt  = ($isActive === '' || $isActive === '1') ? 1 : 0;
        $idInt        = (int) $id;

        if ($idInt > 0) {
            if (!db_fetch('SELECT id FROM positions WHERE id = ?', [$idInt])) {
                $errors[] = "Row {$row}: ID {$idInt} not found — skipped.";
                $skipped++;
                continue;
            }
            db_run(
                'UPDATE positions SET designation=?, monthly_salary=?, sort_order=?, is_active=?, updated_at=NOW() WHERE id=?',
                [$designation, $monthlySalary, $sortOrderInt, $isActiveInt, $idInt]
            );
            $seenIds[] = $idInt;
            $updated++;
        } else {
            $companyIdInt = (int) $companyId;
            if (!isset($validCompanyIds[$companyIdInt])) {
                $errors[] = "Row {$row}: invalid Company ID \"{$companyId}\" for \"{$designation}\" — skipped.";
                $skipped++;
                continue;
            }
            $newId = db_insert(
                'INSERT INTO positions (company_id, designation, monthly_salary, sort_order, is_active) VALUES (?, ?, ?, ?, ?)',
                [$companyIdInt, $designation, $monthlySalary, $sortOrderInt, $isActiveInt]
            );
            $seenIds[] = $newId;
            $inserted++;
        }
    }

    // Pass 2: remove positions not in the file
    // IDs used in at least one proposal item must not be hard-deleted — deactivate instead
    if (!empty($seenIds)) {
        $placeholders = implode(',', array_fill(0, count($seenIds), '?'));
        $missing = db_all(
            "SELECT id FROM positions WHERE id NOT IN ({$placeholders})",
            $seenIds
        );
        foreach ($missing as $m) {
            $mid  = (int) $m['id'];
            $used = db_fetch('SELECT id FROM proposal_items WHERE position_id = ? LIMIT 1', [$mid]);
            if ($used) {
                db_run('UPDATE positions SET is_active=0, updated_at=NOW() WHERE id=?', [$mid]);
                $deactivated++;
            } else {
                db_run('DELETE FROM positions WHERE id=?', [$mid]);
                $deleted++;
            }
        }
    }

    $parts = array_filter([
        $updated     ? "{$updated} updated"         : '',
        $inserted    ? "{$inserted} added"           : '',
        $deleted     ? "{$deleted} deleted"          : '',
        $deactivated ? "{$deactivated} deactivated"  : '',
        $skipped     ? "{$skipped} skipped"          : '',
    ]);
    $msg = 'Import complete: ' . implode(', ', $parts ?: ['no changes']) . '.';
    if ($errors) {
        $shown = array_slice($errors, 0, 5);
        $msg .= ' Issues: ' . implode(' ', $shown);
        if (count($errors) > 5) $msg .= ' …and ' . (count($errors) - 5) . ' more.';
    }

    flash($errors ? 'warning' : 'success', $msg);
    redirect('/positions');
}
