<?php
declare(strict_types=1);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

function proposals_excel(int $id): void
{
    $proposal = db_fetch('
        SELECT pr.*, c.name AS company_name, c.logo_path AS company_logo
        FROM proposals pr
        LEFT JOIN companies c ON c.id = pr.company_id
        WHERE pr.id = ?
    ', [$id]);
    if (!$proposal) { flash('error', 'Proposal not found.'); redirect('/proposals'); }

    $items      = db_all('SELECT * FROM proposal_items WHERE proposal_id = ? ORDER BY sort_order', [$id]);
    $multiplier = (float) $proposal['multiplier'];
    $currency   = $proposal['currency'];
    $company    = $proposal['company_name'] ?? 'G2 Group';

    $ss = new Spreadsheet();
    $ss->getProperties()->setCreator($company)->setTitle($proposal['client_name'] . ' — Rate Card')->setCompany($company);

    // ── Sheet 1: Client Proposal ─────────────────────────────────────────────
    $ws = $ss->getActiveSheet()->setTitle('Rate Card');

    $ws->getColumnDimension('A')->setWidth(5);
    $ws->getColumnDimension('B')->setWidth(36);
    $ws->getColumnDimension('C')->setWidth(14);
    $ws->getColumnDimension('D')->setWidth(20);
    $ws->getColumnDimension('E')->setWidth(20);

    $darkBg   = '0F172A';
    $accentBg = '1E3A5F';
    $lightBg  = 'F0F4F8';
    $borderCl = 'CBD5E1';
    $white    = 'FFFFFF';
    $muted    = '64748B';
    $numFmt   = '#,##0';

    // Row 1 — Header
    $ws->mergeCells('A1:E1');
    $ws->setCellValue('A1', strtoupper($company) . ' — CLIENT RATE CARD');
    $ws->getRowDimension(1)->setRowHeight(34);
    $ws->getStyle('A1')->applyFromArray([
        'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => $white], 'name' => 'Arial'],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $darkBg]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
    ]);

    // Rows 2-6 — Meta
    $meta = [
        ['Client',     $proposal['client_name']],
        ['Project',    $proposal['project_name']],
        ['Date',       date('d M Y', strtotime($proposal['created_at']))],
        ['Multiplier', $multiplier . 'x'],
        ['Currency',   $currency],
    ];
    $r = 2;
    foreach ($meta as [$label, $value]) {
        $ws->mergeCells("C{$r}:E{$r}");
        $ws->setCellValue("A{$r}", $label);
        $ws->setCellValue("C{$r}", $value);
        $ws->getStyle("A{$r}")->getFont()->setBold(true)->setName('Arial')->setSize(9);
        $ws->getStyle("A{$r}")->getFont()->getColor()->setRGB($muted);
        $ws->getStyle("C{$r}")->getFont()->setName('Arial')->setSize(9)->setBold(true);
        $ws->getRowDimension($r)->setRowHeight(16);
        $r++;
    }
    $r++;

    // Column headers
    $headers = ['#', 'Position / Designation', 'FTE', "Monthly Fee ({$currency})", "Annual Fee ({$currency})"];
    foreach (['A','B','C','D','E'] as $ci => $col) {
        $ws->setCellValue($col . $r, $headers[$ci]);
    }
    $ws->getStyle("A{$r}:E{$r}")->applyFromArray([
        'font'      => ['bold' => true, 'color' => ['rgb' => $white], 'name' => 'Arial', 'size' => 9],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $accentBg]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    ]);
    $ws->getRowDimension($r)->setRowHeight(18);
    $headerRow = $r;
    $r++;

    $totalM = 0.0;
    $totalA = 0.0;

    foreach ($items as $i => $item) {
        $monthly = (float)$item['monthly_salary'] * $multiplier * (float)$item['allocation'];
        $annual  = $monthly * 12;
        $totalM += $monthly;
        $totalA += $annual;

        $ws->setCellValue("A{$r}", $i + 1);
        $ws->setCellValue("B{$r}", $item['designation']);
        $ws->setCellValue("C{$r}", (float)$item['allocation']);
        $ws->setCellValue("D{$r}", $monthly);
        $ws->setCellValue("E{$r}", $annual);

        $ws->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $ws->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $ws->getStyle("D{$r}:E{$r}")->getNumberFormat()->setFormatCode($numFmt);
        $ws->getStyle("D{$r}:E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $ws->getStyle("A{$r}:E{$r}")->getFont()->setName('Arial')->setSize(9);

        if ($i % 2 === 0) {
            $ws->getStyle("A{$r}:E{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($lightBg);
        }
        $ws->getRowDimension($r)->setRowHeight(16);
        $r++;
    }

    // Totals
    $ws->setCellValue("B{$r}", 'TOTAL');
    $ws->setCellValue("D{$r}", $totalM);
    $ws->setCellValue("E{$r}", $totalA);
    $ws->getStyle("A{$r}:E{$r}")->applyFromArray([
        'font' => ['bold' => true, 'name' => 'Arial', 'size' => 9],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
    ]);
    $ws->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $ws->getStyle("D{$r}:E{$r}")->getNumberFormat()->setFormatCode($numFmt);
    $ws->getStyle("D{$r}:E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    $ws->getStyle("A{$headerRow}:E{$r}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB($borderCl);
    $ws->getStyle("A{$headerRow}:E{$r}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB($accentBg);

    // ── Sheet 2: Internal Detail ──────────────────────────────────────────────
    $ws2 = $ss->createSheet()->setTitle('Internal Detail');
    foreach (['A'=>36,'B'=>18,'C'=>16,'D'=>16,'E'=>18,'F'=>10,'G'=>18,'H'=>18] as $col => $w) {
        $ws2->getColumnDimension($col)->setWidth($w);
    }

    $ws2->mergeCells('A1:H1');
    $ws2->setCellValue('A1', 'INTERNAL RATE BREAKDOWN — ' . strtoupper($proposal['client_name']) . ' / ' . strtoupper($proposal['project_name']));
    $ws2->getRowDimension(1)->setRowHeight(28);
    $ws2->getStyle('A1')->applyFromArray([
        'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => $white], 'name' => 'Arial'],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $darkBg]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'indent' => 1],
    ]);

    $h2 = ['Designation', "Monthly Salary ({$currency})", 'Hourly Rate', 'Daily Rate', 'Charged Monthly', 'FTE', 'Monthly Fee', 'Annual Fee'];
    foreach ($h2 as $ci => $h) {
        $ws2->setCellValue(chr(65 + $ci) . '2', $h);
    }
    $ws2->getStyle('A2:H2')->applyFromArray([
        'font'      => ['bold' => true, 'color' => ['rgb' => $white], 'name' => 'Arial', 'size' => 9],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $accentBg]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ]);
    $ws2->getRowDimension(2)->setRowHeight(18);

    $r2 = 3;
    foreach ($items as $i => $item) {
        $sal     = (float)$item['monthly_salary'];
        $hourly  = $sal / 150;
        $daily   = $hourly * 8;
        $charged = $sal * $multiplier;
        $alloc   = (float)$item['allocation'];
        $mFee    = $charged * $alloc;
        $aFee    = $mFee * 12;

        $ws2->setCellValue("A{$r2}", $item['designation']);
        $ws2->setCellValue("B{$r2}", $sal);
        $ws2->setCellValue("C{$r2}", $hourly);
        $ws2->setCellValue("D{$r2}", $daily);
        $ws2->setCellValue("E{$r2}", $charged);
        $ws2->setCellValue("F{$r2}", $alloc);
        $ws2->setCellValue("G{$r2}", $mFee);
        $ws2->setCellValue("H{$r2}", $aFee);

        $ws2->getStyle("B{$r2}:E{$r2}")->getNumberFormat()->setFormatCode($numFmt);
        $ws2->getStyle("G{$r2}:H{$r2}")->getNumberFormat()->setFormatCode($numFmt);
        $ws2->getStyle("C{$r2}:D{$r2}")->getNumberFormat()->setFormatCode('#,##0.00');
        $ws2->getStyle("F{$r2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $ws2->getStyle("A{$r2}:H{$r2}")->getFont()->setName('Arial')->setSize(9);

        if ($i % 2 === 0) {
            $ws2->getStyle("A{$r2}:H{$r2}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($lightBg);
        }
        $ws2->getRowDimension($r2)->setRowHeight(16);
        $r2++;
    }
    $ws2->getStyle("A2:H" . ($r2 - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB($borderCl);

    $ss->setActiveSheetIndex(0);

    $safe     = preg_replace('/[^a-zA-Z0-9_-]/', '_', $proposal['client_name']);
    $filename = "{$company}_{$safe}_RateCard_" . date('Ymd') . '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');

    (new Xlsx($ss))->save('php://output');
    exit;
}
