<?php
$hourlyFmt = fn(float $m): string => number_format($m / 150, 2);
$dailyFmt  = fn(float $m): string => number_format(($m / 150) * 8, 2);
$monthFmt  = fn(float $m): string => number_format($m, 0);
$annualFmt = fn(float $m): string => number_format($m * 12, 0);
$showAll   = !$companyId;
?>

<!-- Company filter tabs -->
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
  <ul class="nav nav-tabs border-0 gap-1">
    <li class="nav-item">
      <a class="nav-link <?= $showAll ? 'active' : '' ?>" href="<?= url('/positions') ?>"
         style="font-size:13px; padding: 6px 14px">All Companies</a>
    </li>
    <?php foreach ($companies as $c): ?>
    <li class="nav-item">
      <a class="nav-link <?= $companyId == $c['id'] ? 'active' : '' ?>"
         href="<?= url('/positions?company=' . $c['id']) ?>"
         style="font-size:13px; padding: 6px 14px"><?= e($c['name']) ?></a>
    </li>
    <?php endforeach ?>
  </ul>

  <a href="<?= url('/positions/create' . ($companyId ? "?company={$companyId}" : '')) ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i> Add Position
  </a>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th style="width:36px">#</th>
          <?php if ($showAll): ?><th>Company</th><?php endif ?>
          <th>Designation</th>
          <th class="text-end">Hourly</th>
          <th class="text-end">Daily</th>
          <th class="text-end">Monthly</th>
          <th class="text-end">Annual</th>
          <th class="text-center" style="width:80px">Status</th>
          <th style="width:100px"></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($positions)): ?>
          <tr>
            <td colspan="<?= $showAll ? 9 : 8 ?>" class="text-center text-muted py-5">
              No positions yet.
              <a href="<?= url('/positions/create' . ($companyId ? "?company={$companyId}" : '')) ?>">Add one</a>.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($positions as $i => $p): ?>
            <tr class="<?= $p['is_active'] ? '' : 'text-muted' ?>">
              <td class="text-muted" style="font-size:12px"><?= $i + 1 ?></td>
              <?php if ($showAll): ?>
                <td><span class="co-pill"><?= e($p['company_name'] ?? '—') ?></span></td>
              <?php endif ?>
              <td class="fw-500"><?= e($p['designation']) ?></td>
              <td class="text-end num"><?= $hourlyFmt((float)$p['monthly_salary']) ?></td>
              <td class="text-end num"><?= $dailyFmt((float)$p['monthly_salary']) ?></td>
              <td class="text-end num fw-semibold"><?= $monthFmt((float)$p['monthly_salary']) ?></td>
              <td class="text-end num"><?= $annualFmt((float)$p['monthly_salary']) ?></td>
              <td class="text-center">
                <?php if ($p['is_active']): ?>
                  <span class="badge-active">Active</span>
                <?php else: ?>
                  <span class="badge-inactive">Inactive</span>
                <?php endif ?>
              </td>
              <td class="text-end">
                <a href="<?= url("/positions/{$p['id']}/edit") ?>" class="btn btn-sm btn-outline-secondary py-0 px-2 me-1" title="Edit">
                  <i class="bi bi-pencil" style="font-size:12px"></i>
                </a>
                <form method="post" action="<?= url("/positions/{$p['id']}/delete") ?>" class="d-inline"
                      onsubmit="return confirm('Delete <?= e(addslashes($p['designation'])) ?>?')">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Delete">
                    <i class="bi bi-trash" style="font-size:12px"></i>
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach ?>
        <?php endif ?>
      </tbody>
    </table>
  </div>
</div>

<p class="text-muted mt-3" style="font-size:12px">
  Formula: Hourly = Monthly ÷ 150 &nbsp;·&nbsp; Daily = Hourly × 8 &nbsp;·&nbsp; Annual = Monthly × 12
</p>
