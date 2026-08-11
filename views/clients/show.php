<p class="text-muted mb-3">
  <a href="<?= url('/clients') ?>"><i class="bi bi-arrow-left me-1"></i>All Clients</a>
</p>

<!-- Client header -->
<div class="card p-4 mb-4">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
    <div>
      <h4 class="mb-1"><?= e($client['name']) ?></h4>
      <?php if ($client['industry']): ?>
        <span class="text-muted" style="font-size:13px"><?= e($client['industry']) ?></span>
      <?php endif ?>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= url('/proposals/create?client=' . $client['id']) ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> New Proposal
      </a>
      <a href="<?= url("/clients/{$client['id']}/edit") ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-pencil me-1"></i> Edit Client
      </a>
    </div>
  </div>

  <?php if ($client['contact_name'] || $client['contact_email'] || $client['contact_phone']): ?>
  <div class="d-flex gap-4 mt-3 flex-wrap" style="font-size:13px;color:#64748b">
    <?php if ($client['contact_name']): ?>
      <span><i class="bi bi-person me-1"></i><?= e($client['contact_name']) ?></span>
    <?php endif ?>
    <?php if ($client['contact_email']): ?>
      <a href="mailto:<?= e($client['contact_email']) ?>" class="text-decoration-none" style="color:#64748b">
        <i class="bi bi-envelope me-1"></i><?= e($client['contact_email']) ?>
      </a>
    <?php endif ?>
    <?php if ($client['contact_phone']): ?>
      <span><i class="bi bi-telephone me-1"></i><?= e($client['contact_phone']) ?></span>
    <?php endif ?>
  </div>
  <?php endif ?>

  <?php if ($client['notes']): ?>
    <p class="text-muted mt-2 mb-0" style="font-size:13px"><?= nl2br(e($client['notes'])) ?></p>
  <?php endif ?>
</div>

<!-- Proposals -->
<h6 class="mb-3" style="font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#64748b">
  Proposals (<?= count($proposals) ?>)
</h6>

<?php if (empty($proposals)): ?>
  <div class="card p-5 text-center text-muted">
    No proposals yet for this client.
    <a href="<?= url('/proposals/create?client=' . $client['id']) ?>">Create one</a>.
  </div>
<?php else: ?>
<div class="card">
  <div class="table-responsive">
    <table class="table mb-0">
      <thead>
        <tr>
          <th>Project</th>
          <th class="text-center" style="width:90px">Status</th>
          <th class="text-center" style="width:60px">Ver.</th>
          <th class="text-end" style="width:160px">Monthly Fee</th>
          <th class="text-end" style="width:160px">Annual Fee</th>
          <th class="text-center" style="width:110px">Contract</th>
          <th style="width:60px"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($proposals as $p):
          $monthly = (float) $p['total_monthly'];
          $annual  = $monthly * 12;
        ?>
        <tr>
          <td>
            <a href="<?= url("/proposals/{$p['id']}") ?>" class="fw-semibold text-decoration-none">
              <?= e($p['project_name']) ?>
            </a>
            <div class="text-muted" style="font-size:11px">
              <?= date('d M Y', strtotime($p['created_at'])) ?>
              &nbsp;·&nbsp; <?= $p['position_count'] ?> position<?= $p['position_count'] != 1 ? 's' : '' ?>
              &nbsp;·&nbsp; <?= e($p['currency']) ?> <?= number_format($p['multiplier'], 1) ?>x
            </div>
          </td>
          <td class="text-center">
            <span class="status-<?= $p['status'] ?>" style="font-size:11px;padding:3px 8px">
              <?= ucfirst($p['status']) ?>
            </span>
          </td>
          <td class="text-center text-muted" style="font-size:12px">v<?= $p['version'] ?></td>
          <td class="text-end num fw-semibold"><?= $monthly ? number_format($monthly, 0) : '—' ?></td>
          <td class="text-end num"><?= $annual ? number_format($annual, 0) : '—' ?></td>
          <td class="text-center">
            <?php if ($p['contract_path']): ?>
              <a href="<?= url("/proposals/{$p['id']}/contract/download") ?>" target="_blank"
                 class="btn btn-sm btn-outline-primary py-0 px-2" title="View contract">
                <i class="bi bi-file-earmark-text" style="font-size:12px"></i>
              </a>
            <?php else: ?>
              <span class="text-muted" style="font-size:11px">—</span>
            <?php endif ?>
          </td>
          <td class="text-end">
            <a href="<?= url("/proposals/{$p['id']}") ?>" class="btn btn-sm btn-outline-secondary py-0 px-2">
              <i class="bi bi-arrow-right" style="font-size:12px"></i>
            </a>
          </td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif ?>
