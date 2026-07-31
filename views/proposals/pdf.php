<?php
declare(strict_types=1);
$multiplier  = (float)$proposal['multiplier'];
$currency    = $proposal['currency'];
$companyName = $proposal['company_name'] ?? 'G2 Group';
$companyLogo = $proposal['company_logo'] ?? null;
$totalM = 0.0; $totalA = 0.0; $totalFTE = 0.0;
foreach ($items as $item) {
    $mFee = (float)$item['monthly_salary'] * $multiplier * (float)$item['allocation'];
    $totalM   += $mFee;
    $totalA   += $mFee * 12;
    $totalFTE += (float)$item['allocation'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Rate Card — <?= e($proposal['client_name']) ?> / <?= e($proposal['project_name']) ?></title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
      font-size: 9pt; color: #1a1a1a; line-height: 1.5;
      padding: 28px 32px;
    }
    .header {
      display: flex; align-items: center; justify-content: space-between;
      border-bottom: 2.5px solid #0f172a; padding-bottom: 14px; margin-bottom: 18px;
    }
    .logo-area { display: flex; align-items: center; gap: 14px; }
    .logo-img  { max-height: 44px; max-width: 160px; object-fit: contain; }
    .logo-text { font-size: 20pt; font-weight: 800; color: #0f172a; letter-spacing: -0.5px; }
    .logo-sub  { font-size: 7.5pt; color: #64748b; margin-top: 2px; text-transform: uppercase; letter-spacing: 1px; }
    .doc-info  { text-align: right; }
    .doc-title { font-size: 12pt; font-weight: 700; color: #0f172a; }
    .doc-date  { font-size: 8pt; color: #64748b; margin-top: 3px; }

    .meta { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 28px; margin-bottom: 18px; padding: 11px 14px; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0; }
    .meta-item { display: flex; gap: 8px; align-items: baseline; }
    .meta-label { font-size: 7.5pt; font-weight: 600; color: #64748b; min-width: 68px; text-transform: uppercase; letter-spacing: .4px; }
    .meta-value { font-size: 9pt; font-weight: 600; color: #0f172a; }

    table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    thead tr { background: #0f172a; }
    thead th { padding: 8px 10px; font-size: 7.5pt; font-weight: 600; color: #fff; text-align: left; text-transform: uppercase; letter-spacing: .4px; }
    thead th.num { text-align: right; }
    tbody tr:nth-child(even) { background: #f0f4f8; }
    tbody td { padding: 7px 10px; font-size: 8.5pt; border-bottom: 1px solid #e2e8f0; }
    tbody td.num { text-align: right; font-variant-numeric: tabular-nums; }
    tbody td.center { text-align: center; }
    tfoot td { padding: 9px 10px; font-weight: 700; font-size: 9pt; background: #e2e8f0; }
    tfoot td.num { text-align: right; font-variant-numeric: tabular-nums; }

    .doc-footer { margin-top: 22px; padding-top: 10px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; font-size: 7pt; color: #94a3b8; }
    .notes-block { margin-bottom: 14px; padding: 10px 14px; background: #fffbeb; border-left: 3px solid #f59e0b; border-radius: 4px; font-size: 8pt; color: #78350f; }
    .notes-label { font-weight: 700; margin-bottom: 3px; font-size: 7.5pt; text-transform: uppercase; }

    @media print {
      body { padding: 16px 20px; }
      @page { margin: 1.2cm; size: A4 landscape; }
      .print-btn { display: none !important; }
    }
    .print-btn {
      position: fixed; bottom: 20px; right: 20px;
      background: #2563eb; color: #fff; border: none; border-radius: 8px;
      padding: 10px 20px; font-size: 13px; font-weight: 600; cursor: pointer;
      box-shadow: 0 4px 12px rgba(37,99,235,.4);
    }
  </style>
</head>
<body>

  <div class="header">
    <div class="logo-area">
      <?php if ($companyLogo): ?>
        <img class="logo-img" src="<?= url('/public/' . e($companyLogo)) ?>" alt="<?= e($companyName) ?>">
      <?php else: ?>
        <div>
          <div class="logo-text"><?= e($companyName) ?></div>
        </div>
      <?php endif ?>
      <div>
        <div class="logo-sub">Client Rate Card</div>
      </div>
    </div>
    <div class="doc-info">
      <div class="doc-title">Rate Proposal</div>
      <div class="doc-date">Date: <?= date('d M Y', strtotime($proposal['created_at'])) ?></div>
    </div>
  </div>

  <div class="meta">
    <div class="meta-item"><span class="meta-label">Company</span><span class="meta-value"><?= e($companyName) ?></span></div>
    <div class="meta-item"><span class="meta-label">Client</span><span class="meta-value"><?= e($proposal['client_name']) ?></span></div>
    <div class="meta-item"><span class="meta-label">Project</span><span class="meta-value"><?= e($proposal['project_name']) ?></span></div>
    <div class="meta-item"><span class="meta-label">Currency</span><span class="meta-value"><?= e($currency) ?></span></div>
    <div class="meta-item"><span class="meta-label">Multiplier</span><span class="meta-value"><?= number_format($multiplier, 1) ?>x</span></div>
  </div>

  <?php if (!empty($proposal['notes'])): ?>
  <div class="notes-block">
    <div class="notes-label">Notes</div>
    <?= nl2br(e($proposal['notes'])) ?>
  </div>
  <?php endif ?>

  <table>
    <thead>
      <tr>
        <th style="width:28px">#</th>
        <th>Position / Designation</th>
        <th class="num" style="width:80px">FTE</th>
        <th class="num" style="width:145px">Monthly Fee (<?= e($currency) ?>)</th>
        <th class="num" style="width:145px">Annual Fee (<?= e($currency) ?>)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $i => $item):
        $mFee = (float)$item['monthly_salary'] * $multiplier * (float)$item['allocation'];
        $aFee = $mFee * 12;
      ?>
        <tr>
          <td style="color:#94a3b8; font-size:7.5pt"><?= $i + 1 ?></td>
          <td><?= e($item['designation']) ?></td>
          <td class="center"><?= number_format((float)$item['allocation'], 2) ?></td>
          <td class="num"><?= number_format($mFee, 0) ?></td>
          <td class="num"><?= number_format($aFee, 0) ?></td>
        </tr>
      <?php endforeach ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="2">Total</td>
        <td style="text-align:center"><?= number_format($totalFTE, 2) ?></td>
        <td class="num"><?= e($currency) ?> <?= number_format($totalM, 0) ?></td>
        <td class="num"><?= e($currency) ?> <?= number_format($totalA, 0) ?></td>
      </tr>
    </tfoot>
  </table>

  <div class="doc-footer">
    <span><?= e($companyName) ?> — Confidential</span>
    <span>Generated <?= date('d M Y, H:i') ?></span>
  </div>

  <button class="print-btn" onclick="window.print()">&#128438; Print / Save as PDF</button>

</body>
</html>
