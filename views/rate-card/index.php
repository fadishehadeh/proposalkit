<form method="get" action="<?= url('/rate-card') ?>" class="d-flex gap-3 align-items-end mb-4 flex-wrap">
  <div>
    <label class="form-label fw-semibold mb-1" style="font-size:12px">COMPANY</label>
    <select name="company" class="form-select" style="width:160px" onchange="this.form.submit()">
      <option value="">All Companies</option>
      <?php foreach ($companies as $c): ?>
        <option value="<?= $c['id'] ?>" <?= $companyId == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
      <?php endforeach ?>
    </select>
  </div>
  <?php if (!empty($departments)): ?>
  <div>
    <label class="form-label fw-semibold mb-1" style="font-size:12px">DEPARTMENT</label>
    <select name="department" class="form-select" style="width:180px" onchange="this.form.submit()">
      <option value="">All Departments</option>
      <?php foreach ($departments as $dept): ?>
        <option value="<?= e($dept) ?>" <?= $selected_dept === $dept ? 'selected' : '' ?>><?= e($dept) ?></option>
      <?php endforeach ?>
    </select>
  </div>
  <?php endif ?>
  <div>
    <label class="form-label fw-semibold mb-1" style="font-size:12px">LOCATION</label>
    <select name="location" class="form-select" style="width:130px" onchange="this.form.submit()">
      <option value="doha"    <?= $selected_location === 'doha'    ? 'selected' : '' ?>>Doha</option>
      <option value="lebanon" <?= $selected_location === 'lebanon' ? 'selected' : '' ?>>Lebanon</option>
    </select>
  </div>
  <div>
    <label class="form-label fw-semibold mb-1" style="font-size:12px">MULTIPLIER</label>
    <select name="multiplier" class="form-select" style="width:130px" onchange="this.form.submit()">
      <?php foreach ($multipliers as $m): ?>
        <option value="<?= $m ?>" <?= $selected_mult == $m ? 'selected' : '' ?>><?= number_format($m, 1) ?>x</option>
      <?php endforeach ?>
    </select>
  </div>
  <div>
    <label class="form-label fw-semibold mb-1" style="font-size:12px">CURRENCY</label>
    <select name="currency" class="form-select" style="width:110px" onchange="this.form.submit()">
      <?php foreach ($currencies as $c): ?>
        <option value="<?= $c ?>" <?= $selected_currency === $c ? 'selected' : '' ?>><?= $c ?></option>
      <?php endforeach ?>
    </select>
  </div>
  <div class="ms-2">
    <span class="rate-pill fs-6 px-3 py-2">
      <?= $companyId ? e($companies[array_search($companyId, array_column($companies, 'id'))]['name'] ?? '') . ' &nbsp;·&nbsp; ' : '' ?>
      <strong><?= ucfirst($selected_location) ?></strong>
      &nbsp;·&nbsp; <strong><?= number_format($selected_mult, 1) ?>x</strong>
      &nbsp;·&nbsp; <strong><?= e($selected_currency) ?></strong>
    </span>
  </div>
</form>

<?php
// Filter by department if selected
$filteredPositions = $positions;
if ($selected_dept !== '') {
    $filteredPositions = array_filter($positions, fn($p) => ($p['department'] ?? '') === $selected_dept);
}
$filteredPositions = array_values($filteredPositions);
?>

<?php if (empty($filteredPositions)): ?>
  <div class="card p-5 text-center text-muted">No active positions. <a href="<?= url('/positions/create') ?>">Add some</a>.</div>
<?php else: ?>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>#</th>
          <?php if (!$companyId): ?><th>Company</th><?php endif ?>
          <th>Department</th>
          <th>Designation</th>
          <th class="text-end">Base Monthly</th>
          <th class="text-end">Hourly Rate</th>
          <th class="text-end">Daily Rate</th>
          <th class="text-end" style="background:#eff6ff">Charged Monthly</th>
          <th class="text-end" style="background:#eff6ff">Charged Annual</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($filteredPositions as $i => $p):
          $m  = $selected_location === 'lebanon'
                  ? (float)($p['monthly_salary_lebanon'] ?? 0)
                  : (float)($p['monthly_salary_doha'] ?? $p['monthly_salary'] ?? 0);
          if ($m <= 0) $m = (float)($p['monthly_salary'] ?? 0);
          $h  = $m > 0 ? $m / 150 : 0;
          $d  = $h * 8;
          $cm = $m * $selected_mult;
          $ca = $cm * 12;
        ?>
        <tr>
          <td class="text-muted" style="font-size:12px"><?= $i + 1 ?></td>
          <?php if (!$companyId): ?><td><span class="co-pill"><?= e($p['company_name'] ?? '') ?></span></td><?php endif ?>
          <td class="text-muted" style="font-size:12px"><?= e($p['department'] ?? '') ?></td>
          <td class="fw-500"><?= e($p['designation']) ?></td>
          <td class="text-end num text-muted"><?= $m > 0 ? number_format($m, 0) : '<span class="text-muted">—</span>' ?></td>
          <td class="text-end num"><?= $h > 0 ? number_format($h, 2) : '—' ?></td>
          <td class="text-end num"><?= $d > 0 ? number_format($d, 2) : '—' ?></td>
          <td class="text-end num fw-semibold" style="background:#f0f7ff"><?= $m > 0 ? number_format($cm, 0) : '—' ?></td>
          <td class="text-end num fw-semibold" style="background:#f0f7ff"><?= $m > 0 ? number_format($ca, 0) : '—' ?></td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
<p class="text-muted mt-3" style="font-size:12px">
  All amounts in <?= e($selected_currency) ?>.
  Rates shown for <strong><?= ucfirst($selected_location) ?></strong> market.
  Charged Monthly = Base Monthly &times; <?= number_format($selected_mult, 1) ?>.
  Charged Annual = Charged Monthly &times; 12.
</p>
<?php endif ?>
