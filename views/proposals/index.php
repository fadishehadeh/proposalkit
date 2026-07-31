<?php
$tabs = [
    ''         => ['label' => 'All',              'count' => $total],
    'draft'    => ['label' => 'Draft',            'count' => $statusCounts['draft']],
    'sent'     => ['label' => 'Sent',             'count' => $statusCounts['sent']],
    'approved' => ['label' => 'Approved',         'count' => $statusCounts['approved']],
    'rejected' => ['label' => 'Rejected / Lost',  'count' => $statusCounts['rejected']],
];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <!-- Status filter tabs -->
  <div class="d-flex gap-1 flex-wrap">
    <?php foreach ($tabs as $s => $tab): ?>
      <a href="<?= url('/proposals' . ($s ? '?status=' . $s : '')) ?>"
         class="btn btn-sm <?= $status === $s ? 'btn-primary' : 'btn-outline-secondary' ?>"
         style="font-size:12px">
        <?= $tab['label'] ?>
        <span class="badge ms-1 <?= $status === $s ? 'bg-white text-primary' : 'bg-secondary bg-opacity-25 text-secondary' ?>"
              style="font-size:10px;font-weight:700"><?= $tab['count'] ?></span>
      </a>
    <?php endforeach ?>
  </div>
  <a href="<?= url('/proposals/create') ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i> New Proposal
  </a>
</div>

<?php if (empty($proposals)): ?>
  <div class="card p-5 text-center text-muted">
    <i class="bi bi-file-earmark-text" style="font-size:32px; opacity:.3"></i>
    <p class="mt-2 mb-0">No proposals<?= $status ? " with status \"" . htmlspecialchars($status) . "\"" : '' ?>.
      <?php if (!$status): ?><a href="<?= url('/proposals/create') ?>">Create your first one</a>.<?php endif ?>
    </p>
  </div>
<?php else: ?>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>#</th>
          <th>Company</th>
          <th>Client</th>
          <th>Project</th>
          <th class="text-center">Status</th>
          <th class="text-center">Cur.</th>
          <th class="text-center">Mult.</th>
          <th>Date</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($proposals as $i => $p): ?>
          <tr style="cursor:pointer" onclick="location.href='<?= url("/proposals/{$p['id']}") ?>'">
            <td class="text-muted" style="font-size:12px"><?= $i + 1 ?></td>
            <td>
              <?php if ($p['company_logo']): ?>
                <img src="<?= url('/public/' . e($p['company_logo'])) ?>" alt="<?= e($p['company_name']) ?>"
                     style="height:20px;max-width:60px;object-fit:contain;vertical-align:middle;margin-right:6px">
              <?php endif ?>
              <span class="co-pill"><?= e($p['company_name'] ?? '—') ?></span>
            </td>
            <td class="fw-semibold"><?= e($p['client_name']) ?></td>
            <td>
              <?= e($p['project_name']) ?>
              <?php if (($p['version'] ?? 1) > 1): ?>
                <span class="ms-1" style="font-size:10px;background:#f1f5f9;color:#64748b;padding:1px 5px;border-radius:4px;font-weight:600">v<?= $p['version'] ?></span>
              <?php endif ?>
            </td>
            <td class="text-center"><span class="status-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
            <td class="text-center"><span class="rate-pill"><?= e($p['currency']) ?></span></td>
            <td class="text-center"><span class="rate-pill"><?= number_format((float)$p['multiplier'], 1) ?>x</span></td>
            <td class="text-muted" style="font-size:13px"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
            <td class="text-end" onclick="event.stopPropagation()" style="white-space:nowrap">
              <a href="<?= url("/proposals/{$p['id']}") ?>" class="btn btn-sm btn-outline-secondary py-0 px-2 me-1" title="View">
                <i class="bi bi-eye" style="font-size:12px"></i>
              </a>
              <form method="post" action="<?= url("/proposals/{$p['id']}/delete") ?>" class="d-inline"
                    onsubmit="return confirm('Delete this proposal for <?= e(addslashes($p['client_name'])) ?>?')">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Delete">
                  <i class="bi bi-trash" style="font-size:12px"></i>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif ?>
