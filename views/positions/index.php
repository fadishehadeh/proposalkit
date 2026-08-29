<?php
$showAll = !$companyId;
$byCompany = !$showAll;
?>
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <!-- Company tabs -->
    <a href="<?= url('/positions') ?>"
       class="btn btn-sm <?= !$companyId ? 'btn-primary' : 'btn-outline-secondary' ?>">All</a>
    <?php foreach ($companies as $c): ?>
      <a href="<?= url("/positions?company={$c['id']}") ?>"
         class="btn btn-sm <?= $companyId == $c['id'] ? 'btn-primary' : 'btn-outline-secondary' ?>">
        <?= e($c['name']) ?>
      </a>
    <?php endforeach ?>
  </div>
  <div class="d-flex gap-2">
    <?php if ($companyId): ?>
      <a href="<?= url("/positions/create?company={$companyId}") ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Add Position
      </a>
    <?php else: ?>
      <a href="<?= url('/positions/create') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Add Position
      </a>
    <?php endif ?>
    <a href="<?= url('/positions/export' . ($companyId ? "?company={$companyId}" : '')) ?>"
       class="btn btn-outline-success btn-sm"><i class="bi bi-download me-1"></i> Export</a>
    <a href="<?= url('/positions/import') ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-upload me-1"></i> Import
    </a>
  </div>
</div>

<?php if (empty($positions)): ?>
  <div class="card p-5 text-center text-muted">
    No positions found. <a href="<?= url('/positions/create') ?>">Add the first one</a>.
  </div>
<?php else: ?>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>#</th>
          <?php if ($showAll): ?><th>Company</th><?php endif ?>
          <th>Department</th>
          <th>Designation</th>
          <th class="text-end">Doha / Month</th>
          <th class="text-end">Lebanon / Month</th>
          <th class="text-end">Hourly</th>
          <th class="text-end">Daily</th>
          <th class="text-center">Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($positions as $i => $p):
          $doha = (float)($p['monthly_salary_doha'] ?? $p['monthly_salary'] ?? 0);
          $leb  = (float)($p['monthly_salary_lebanon'] ?? 0);
          $base = $doha ?: $leb;
          $h    = $base > 0 ? $base / 150 : 0;
          $d    = $h * 8;
          $active = ($p['is_active'] ?? 1);
        ?>
        <tr class="<?= !$active ? 'opacity-50' : '' ?>">
          <td class="text-muted" style="font-size:12px"><?= $i + 1 ?></td>
          <?php if ($showAll): ?>
            <td><span class="co-pill"><?= e($p['company_name'] ?? '') ?></span></td>
          <?php endif ?>
          <td class="text-muted" style="font-size:12px"><?= e($p['department'] ?? '') ?></td>
          <td class="fw-500"><?= e($p['designation']) ?></td>
          <td class="text-end num"><?= $doha > 0 ? number_format($doha, 0) : '<span class="text-muted">—</span>' ?></td>
          <td class="text-end num"><?= $leb > 0 ? number_format($leb, 0) : '<span class="text-muted">—</span>' ?></td>
          <td class="text-end num text-muted"><?= $h > 0 ? number_format($h, 2) : '—' ?></td>
          <td class="text-end num text-muted"><?= $d > 0 ? number_format($d, 2) : '—' ?></td>
          <td class="text-center">
            <?php if ($active): ?>
              <span class="badge-active" style="font-size:11px">Active</span>
            <?php else: ?>
              <span class="badge bg-secondary" style="font-size:11px">Inactive</span>
            <?php endif ?>
          </td>
          <td>
            <a href="<?= url("/positions/{$p['id']}/edit") ?>" class="btn btn-sm btn-outline-secondary py-0 px-2">
              <i class="bi bi-pencil" style="font-size:11px"></i>
            </a>
          </td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
<p class="text-muted mt-2" style="font-size:11px">
  <?= count($positions) ?> position<?= count($positions) !== 1 ? 's' : '' ?>.
  Hourly = Doha monthly / 150. Daily = Hourly &times; 8.
</p>
<?php endif ?>
