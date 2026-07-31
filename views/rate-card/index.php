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
      <strong><?= number_format($selected_mult, 1) ?>x</strong>
      &nbsp;·&nbsp; <strong><?= e($selected_currency) ?></strong>
    </span>
  </div>
</form>

<?php if (empty($positions)): ?>
  <div class="card p-5 text-center text-muted">No active positions. <a href="<?= url('/positions/create') ?>">Add some</a>.</div>
<?php else: ?>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>#</th>
          <?php if (!$companyId): ?><th>Company</th><?php endif ?>
          <th>Designation</th>
          <th class="text-end">Base Monthly</th>
          <th class="text-end">Hourly Rate</th>
          <th class="text-end">Daily Rate</th>
          <th class="text-end" style="background:#eff6ff">Charged Monthly</th>
          <th class="text-end" style="background:#eff6ff">Charged Annual</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($positions as $i => $p):
          $m  = (float)$p['monthly_salary'];
          $h  = $m / 150;
          $d  = $h * 8;
          $cm = $m * $selected_mult;
          $ca = $cm * 12;
        ?>
        <tr>
          <td class="text-muted" style="font-size:12px"><?= $i + 1 ?></td>
          <?php if (!$companyId): ?><td><span class="co-pill"><?= e($p['company_name'] ?? '—') ?></span></td><?php endif ?>
          <td class="fw-500"><?= e($p['designation']) ?></td>
          <td class="text-end num text-muted"><?= number_format($m, 0) ?></td>
          <td class="text-end num"><?= number_format($h, 2) ?></td>
          <td class="text-end num"><?= number_format($d, 2) ?></td>
          <td class="text-end num fw-semibold" style="background:#f0f7ff"><?= number_format($cm, 0) ?></td>
          <td class="text-end num fw-semibold" style="background:#f0f7ff"><?= number_format($ca, 0) ?></td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
<p class="text-muted mt-3" style="font-size:12px">
  All amounts in <?= e($selected_currency) ?>.
  Charged Monthly = Base Monthly × <?= number_format($selected_mult, 1) ?>.
  Charged Annual = Charged Monthly × 12.
</p>
<?php endif ?>
