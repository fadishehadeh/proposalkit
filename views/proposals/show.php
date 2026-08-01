<?php
$multiplier = (float)$proposal['multiplier'];
$currency   = $proposal['currency'];
$status     = $proposal['status'] ?? 'draft';
$version    = (int)($proposal['version'] ?? 1);
$totalM = 0.0; $totalA = 0.0;
foreach ($items as $item) {
    $mFee = (float)$item['monthly_salary'] * $multiplier * (float)$item['allocation'];
    $totalM += $mFee;
    $totalA += $mFee * 12;
}

// Status transitions
$transitions = [
    'draft'    => [['status'=>'sent',     'label'=>'Mark as Sent',     'icon'=>'send',       'class'=>'btn-primary']],
    'sent'     => [['status'=>'approved', 'label'=>'Mark as Approved', 'icon'=>'check-circle','class'=>'btn-success'],
                   ['status'=>'rejected', 'label'=>'Mark as Rejected', 'icon'=>'x-circle',   'class'=>'btn-danger'],
                   ['status'=>'draft',    'label'=>'Revert to Draft',  'icon'=>'arrow-counterclockwise','class'=>'btn-outline-secondary']],
    'approved' => [['status'=>'sent',     'label'=>'Revert to Sent',   'icon'=>'arrow-counterclockwise','class'=>'btn-outline-secondary']],
    'rejected' => [['status'=>'draft',    'label'=>'Revert to Draft',  'icon'=>'arrow-counterclockwise','class'=>'btn-outline-secondary']],
];
?>

<script>
document.getElementById('topbar-actions').innerHTML = `
  <a href="<?= url("/proposals/{$proposal['id']}/pdf") ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-printer me-1"></i> Print / PDF
  </a>
  <a href="<?= url("/proposals/{$proposal['id']}/export/excel") ?>" class="btn btn-sm btn-outline-success">
    <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
  </a>`;
</script>

<p class="text-muted mb-3"><a href="<?= url('/proposals') ?>"><i class="bi bi-arrow-left me-1"></i>All proposals</a></p>

<!-- Status bar -->
<div class="card mb-3 p-3">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
      <span class="status-<?= $status ?>" style="font-size:13px;padding:4px 12px"><?= ucfirst($status) ?></span>

      <!-- Version chain -->
      <?php if (count($versions) > 1): ?>
      <div class="d-flex align-items-center gap-1">
        <span style="font-size:11px;color:#94a3b8">Versions:</span>
        <?php foreach ($versions as $v): ?>
          <?php if ($v['id'] == $proposal['id']): ?>
            <span style="font-size:11px;background:#1d4ed8;color:#fff;padding:2px 7px;border-radius:4px;font-weight:700">v<?= $v['version'] ?></span>
          <?php else: ?>
            <a href="<?= url("/proposals/{$v['id']}") ?>"
               style="font-size:11px;background:#f1f5f9;color:#475569;padding:2px 7px;border-radius:4px;font-weight:600;text-decoration:none">
              v<?= $v['version'] ?>
              <span class="status-<?= $v['status'] ?>" style="font-size:9px;padding:1px 4px;margin-left:2px"><?= $v['status'] ?></span>
            </a>
          <?php endif ?>
        <?php endforeach ?>
      </div>
      <?php endif ?>
    </div>

    <!-- Actions -->
    <div class="d-flex gap-2 flex-wrap">
      <!-- Status transitions -->
      <?php foreach ($transitions[$status] ?? [] as $t): ?>
      <form method="post" action="<?= url("/proposals/{$proposal['id']}/status") ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="status" value="<?= $t['status'] ?>">
        <button class="btn btn-sm <?= $t['class'] ?>">
          <i class="bi bi-<?= $t['icon'] ?> me-1"></i><?= $t['label'] ?>
        </button>
      </form>
      <?php endforeach ?>

      <!-- Edit -->
      <a href="<?= url("/proposals/{$proposal['id']}/edit") ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-pencil me-1"></i> Edit
      </a>

      <!-- New version -->
      <form method="post" action="<?= url("/proposals/{$proposal['id']}/version") ?>"
            onsubmit="return confirm('Create v<?= $version + 1 ?>? This proposal (v<?= $version ?>) will be preserved as-is.')">
        <?= csrf_field() ?>
        <button class="btn btn-sm btn-outline-primary">
          <i class="bi bi-layers me-1"></i> New Version (v<?= $version + 1 ?>)
        </button>
      </form>

      <!-- Clone -->
      <form method="post" action="<?= url("/proposals/{$proposal['id']}/clone") ?>"
            onsubmit="return confirm('Clone this proposal as a new draft copy?')">
        <?= csrf_field() ?>
        <button class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-copy me-1"></i> Clone
        </button>
      </form>
    </div>
  </div>
</div>

<!-- Meta -->
<div class="card mb-4">
  <div class="card-body p-4">
    <div class="row g-3 align-items-center">
      <?php if ($proposal['company_logo'] || $proposal['company_name']): ?>
      <div class="col-md-2">
        <?php if ($proposal['company_logo']): ?>
          <img src="<?= url('/public/' . e($proposal['company_logo'])) ?>"
               alt="<?= e($proposal['company_name']) ?>"
               style="max-height:48px;max-width:140px;object-fit:contain">
        <?php else: ?>
          <span class="co-pill fs-6 px-3 py-2"><?= e($proposal['company_name']) ?></span>
        <?php endif ?>
      </div>
      <?php endif ?>
      <div class="col-md-2">
        <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Client</div>
        <div class="fw-bold mt-1" style="font-size:16px"><?= e($proposal['client_name']) ?></div>
      </div>
      <div class="col-md-3">
        <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Project</div>
        <div class="fw-semibold mt-1"><?= e($proposal['project_name']) ?></div>
      </div>
      <div class="col-md-1">
        <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Currency</div>
        <div class="mt-1"><span class="rate-pill px-3 py-1"><?= e($currency) ?></span></div>
      </div>
      <div class="col-md-1">
        <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Multiplier</div>
        <div class="mt-1"><span class="rate-pill px-3 py-1"><?= number_format($multiplier, 1) ?>x</span></div>
      </div>
      <div class="col-md-2">
        <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Date</div>
        <div class="mt-1 text-muted" style="font-size:13px"><?= date('d M Y', strtotime($proposal['created_at'])) ?></div>
      </div>
      <?php if ($proposal['notes']): ?>
      <div class="col-12">
        <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Notes</div>
        <div class="mt-1 text-muted" style="font-size:13px"><?= nl2br(e($proposal['notes'])) ?></div>
      </div>
      <?php endif ?>
    </div>
  </div>
</div>

<!-- Table -->
<div class="card">
  <div class="card-header">Rate Summary</div>
  <div class="table-responsive">
    <table class="table mb-0">
      <thead>
        <tr>
          <th style="width:36px">#</th>
          <th>Position / Designation</th>
          <th class="text-center" style="width:100px">FTE</th>
          <th class="text-end" style="width:170px">Base Monthly</th>
          <th class="text-end" style="width:170px">Monthly Fee (<?= e($currency) ?>)</th>
          <th class="text-end" style="width:170px">Annual Fee (<?= e($currency) ?>)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $i => $item):
          $base = (float)$item['monthly_salary'];
          $mFee = $base * $multiplier * (float)$item['allocation'];
          $aFee = $mFee * 12;
        ?>
          <tr>
            <td class="text-muted" style="font-size:12px"><?= $i + 1 ?></td>
            <td><?= e($item['designation']) ?></td>
            <td class="text-center"><?= number_format((float)$item['allocation'], 2) ?></td>
            <td class="text-end num text-muted"><?= number_format($base, 0) ?></td>
            <td class="text-end num fw-semibold"><?= number_format($mFee, 0) ?></td>
            <td class="text-end num"><?= number_format($aFee, 0) ?></td>
          </tr>
        <?php endforeach ?>
      </tbody>
      <tfoot>
        <tr style="background:#f0f4f8">
          <td colspan="4" class="text-end fw-bold pe-3">Total</td>
          <td class="text-end num fw-bold" style="font-size:15px"><?= number_format($totalM, 0) ?></td>
          <td class="text-end num fw-bold" style="font-size:15px"><?= number_format($totalA, 0) ?></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<!-- Stats -->
<div class="row mt-4 g-3">
  <div class="col-md-4">
    <div class="card p-3 text-center">
      <div style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;font-weight:600">Total Monthly Fee</div>
      <div class="fw-bold mt-1" style="font-size:22px"><?= e($currency) ?> <?= number_format($totalM, 0) ?></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card p-3 text-center">
      <div style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;font-weight:600">Total Annual Fee</div>
      <div class="fw-bold mt-1" style="font-size:22px"><?= e($currency) ?> <?= number_format($totalA, 0) ?></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card p-3 text-center">
      <div style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;font-weight:600">Positions</div>
      <div class="fw-bold mt-1" style="font-size:22px"><?= count($items) ?></div>
    </div>
  </div>
</div>

<div class="mt-4 d-flex gap-2">
  <a href="<?= url("/proposals/{$proposal['id']}/edit") ?>" class="btn btn-outline-secondary">
    <i class="bi bi-pencil me-1"></i> Edit
  </a>
  <a href="<?= url("/proposals/{$proposal['id']}/pdf") ?>" target="_blank" class="btn btn-outline-secondary">
    <i class="bi bi-printer me-1"></i> Print / PDF
  </a>
  <a href="<?= url("/proposals/{$proposal['id']}/export/excel") ?>" class="btn btn-outline-success">
    <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
  </a>
  <form method="post" action="<?= url("/proposals/{$proposal['id']}/delete") ?>" class="ms-auto"
        onsubmit="return confirm('Permanently delete this proposal?')">
    <?= csrf_field() ?>
    <button type="submit" class="btn btn-outline-danger btn-sm">
      <i class="bi bi-trash me-1"></i> Delete
    </button>
  </form>
</div>
