<?php
function stat_card(string $label, int|string $value, string $icon, string $color, string $sub = ''): void { ?>
<div class="col-sm-6 col-xl-3">
  <div class="card p-3 h-100">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#64748b"><?= $label ?></span>
      <span style="font-size:18px;color:<?= $color ?>"><i class="bi bi-<?= $icon ?>"></i></span>
    </div>
    <div style="font-size:26px;font-weight:800;color:#0f172a;line-height:1"><?= $value ?></div>
    <?php if ($sub): ?><div style="font-size:12px;color:#94a3b8;margin-top:4px"><?= $sub ?></div><?php endif ?>
  </div>
</div>
<?php }

function value_card(string $label, float $value, string $currency, string $color, string $sub = ''): void { ?>
<div class="col-md-6">
  <div class="card p-4 h-100" style="border-left:4px solid <?= $color ?>">
    <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#64748b;margin-bottom:8px"><?= $label ?></div>
    <div style="font-size:28px;font-weight:800;color:#0f172a;line-height:1">
      <?= $currency ?> <?= number_format($value, 0) ?>
    </div>
    <?php if ($sub): ?><div style="font-size:12px;color:#94a3b8;margin-top:6px"><?= $sub ?></div><?php endif ?>
  </div>
</div>
<?php }
?>

<!-- Stat cards -->
<div class="row g-3 mb-4">
  <?php stat_card('Total Proposals', $total, 'file-earmark-text', '#2563eb', $total === 1 ? '1 proposal' : "{$total} proposals total") ?>
  <?php stat_card('In Pipeline', $stats['sent'], 'send', '#0891b2', 'Awaiting client decision') ?>
  <?php stat_card('Approved', $stats['approved'], 'check-circle', '#059669', $winRate !== null ? "Win rate {$winRate}%" : 'No closed deals yet') ?>
  <?php stat_card('Rejected / Lost', $stats['rejected'], 'x-circle', '#dc2626', $stats['draft'] . ' still in draft') ?>
</div>

<!-- Value cards -->
<div class="row g-3 mb-4">
  <?php value_card('Pipeline Value (Annual)', $values['sent'], config('default_currency'), '#0891b2', 'Sum of all sent proposals — annual fees') ?>
  <?php value_card('Won Value (Annual)', $values['approved'], config('default_currency'), '#059669', 'Sum of all approved proposals — annual fees') ?>
</div>

<!-- Recent proposals + Top clients -->
<div class="row g-3">
  <div class="col-xl-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Recent Proposals</span>
        <a href="<?= url('/proposals') ?>" class="btn btn-sm btn-outline-secondary">View all</a>
      </div>
      <?php if (empty($recent)): ?>
        <div class="p-4 text-center text-muted">No proposals yet.</div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table mb-0">
          <thead>
            <tr>
              <th>Client</th>
              <th>Project</th>
              <th>Company</th>
              <th class="text-center">Status</th>
              <th class="text-end">Annual Fee</th>
              <th class="text-muted" style="font-size:11px">Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recent as $p): ?>
            <tr style="cursor:pointer" onclick="location.href='<?= url("/proposals/{$p['id']}") ?>'">
              <td class="fw-semibold"><?= e($p['client_name']) ?></td>
              <td style="font-size:13px;color:#475569"><?= e($p['project_name']) ?></td>
              <td><span class="co-pill"><?= e($p['company_name'] ?? '—') ?></span></td>
              <td class="text-center"><span class="status-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
              <td class="text-end num" style="font-size:13px">
                <?= $p['annual_value'] > 0 ? number_format((float)$p['annual_value'], 0) : '—' ?>
              </td>
              <td class="text-muted" style="font-size:12px"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
            </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
      <?php endif ?>
    </div>
  </div>

  <div class="col-xl-4">
    <div class="card">
      <div class="card-header">Top Clients</div>
      <?php if (empty($topClients)): ?>
        <div class="p-4 text-center text-muted">No clients with proposals yet.</div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table mb-0">
          <thead>
            <tr>
              <th>Client</th>
              <th class="text-center">Proposals</th>
              <th class="text-end">Annual Value</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($topClients as $cl): ?>
            <tr>
              <td class="fw-semibold" style="font-size:13px"><?= e($cl['name']) ?></td>
              <td class="text-center"><span class="rate-pill"><?= $cl['cnt'] ?></span></td>
              <td class="text-end num" style="font-size:13px"><?= number_format((float)$cl['annual_value'], 0) ?></td>
            </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
      <?php endif ?>
    </div>

    <!-- Status breakdown -->
    <div class="card mt-3">
      <div class="card-header">Status Breakdown</div>
      <div class="p-3">
        <?php foreach (['draft'=>'Draft','sent'=>'Sent','approved'=>'Approved','rejected'=>'Rejected'] as $s => $label):
          $pct = $total > 0 ? round($stats[$s] / $total * 100) : 0;
          $colors = ['draft'=>'#94a3b8','sent'=>'#2563eb','approved'=>'#059669','rejected'=>'#dc2626'];
        ?>
        <div class="mb-2">
          <div class="d-flex justify-content-between mb-1" style="font-size:12px">
            <span style="color:<?= $colors[$s] ?>;font-weight:600"><?= $label ?></span>
            <span class="text-muted"><?= $stats[$s] ?> (<?= $pct ?>%)</span>
          </div>
          <div style="height:6px;background:#f1f5f9;border-radius:3px">
            <div style="height:6px;width:<?= $pct ?>%;background:<?= $colors[$s] ?>;border-radius:3px;transition:width .3s"></div>
          </div>
        </div>
        <?php endforeach ?>
      </div>
    </div>
  </div>
</div>
