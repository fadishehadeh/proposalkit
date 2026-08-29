<?php
$departments = ['Account Management', 'Creative', 'Digital', 'Media', 'Operations', 'PR', 'Strategy'];
$editing = $position !== null;
$dohaVal    = $editing ? (float)($position['monthly_salary_doha'] ?? $position['monthly_salary'] ?? 0) : (float)old('monthly_salary_doha', '');
$lebVal     = $editing ? (float)($position['monthly_salary_lebanon'] ?? 0) : (float)old('monthly_salary_lebanon', '');
?>
<p class="text-muted mb-3">
  <a href="<?= url('/positions' . ($presetCompanyId ? '?company=' . $presetCompanyId : '')) ?>">
    <i class="bi bi-arrow-left me-1"></i>Back to Positions
  </a>
</p>

<div class="row">
  <div class="col-lg-7">
    <form method="post" action="<?= url($action) ?>">
      <?= csrf_field() ?>
      <div class="card mb-4">
        <div class="card-header"><?= $editing ? 'Edit Position' : 'New Position' ?></div>
        <div class="card-body p-4">
          <div class="row g-3">

            <div class="col-md-6">
              <label class="form-label fw-semibold">Agency / Company <span class="text-danger">*</span></label>
              <select name="company_id" class="form-select" required>
                <option value="">— Select —</option>
                <?php foreach ($companies as $c): ?>
                  <option value="<?= $c['id'] ?>"
                          <?= old('company_id', $position['company_id'] ?? $presetCompanyId) == $c['id'] ? 'selected' : '' ?>>
                    <?= e($c['name']) ?>
                  </option>
                <?php endforeach ?>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Department</label>
              <input type="text" name="department" class="form-control" list="dept-list"
                     value="<?= e(old('department', $position['department'] ?? '')) ?>"
                     placeholder="e.g. Creative">
              <datalist id="dept-list">
                <?php foreach ($departments as $d): ?>
                  <option value="<?= e($d) ?>">
                <?php endforeach ?>
              </datalist>
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">Designation <span class="text-danger">*</span></label>
              <input type="text" name="designation" class="form-control"
                     value="<?= e(old('designation', $position['designation'] ?? '')) ?>"
                     placeholder="e.g. Account Director" required>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Monthly Salary — Doha <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">QAR</span>
                <input type="number" name="monthly_salary_doha" id="salDoha" class="form-control"
                       value="<?= $dohaVal > 0 ? $dohaVal : '' ?>"
                       step="500" min="0" placeholder="0"
                       oninput="updatePreview()">
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Monthly Salary — Lebanon</label>
              <div class="input-group">
                <span class="input-group-text">USD</span>
                <input type="number" name="monthly_salary_lebanon" id="salLeb" class="form-control"
                       value="<?= $lebVal > 0 ? $lebVal : '' ?>"
                       step="100" min="0" placeholder="0"
                       oninput="updatePreview()">
              </div>
            </div>

            <div class="col-md-4">
              <label class="form-label fw-semibold">Sort Order</label>
              <input type="number" name="sort_order" class="form-control"
                     value="<?= (int) old('sort_order', $position['sort_order'] ?? 0) ?>"
                     step="1" min="0">
            </div>

            <?php if ($editing): ?>
            <div class="col-md-4 d-flex align-items-end">
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                       <?= old('is_active', $position['is_active'] ?? 1) ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_active">Active</label>
              </div>
            </div>
            <?php endif ?>
          </div>
        </div>
      </div>

      <!-- Rate Preview -->
      <div class="card mb-4 p-3" id="ratePreview" style="display:none;background:#f8fafc">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#64748b;margin-bottom:8px">
          Rate Preview
        </div>
        <div class="row g-2" id="previewGrid"></div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary px-4">
          <i class="bi bi-check-lg me-1"></i> <?= $editing ? 'Save Changes' : 'Add Position' ?>
        </button>
        <a href="<?= url('/positions' . ($presetCompanyId ? '?company=' . $presetCompanyId : '')) ?>"
           class="btn btn-outline-secondary">Cancel</a>
        <?php if ($editing): ?>
          <form method="post" action="<?= url("/positions/{$position['id']}/delete") ?>" class="ms-auto"
                onsubmit="return confirm('Delete this position?')">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger btn-sm">
              <i class="bi bi-trash me-1"></i> Delete
            </button>
          </form>
        <?php endif ?>
      </div>
    </form>
  </div>
</div>

<script>
function fmt(n) { return n.toLocaleString('en-US', {minimumFractionDigits:0, maximumFractionDigits:0}); }
function updatePreview() {
  const doha = parseFloat(document.getElementById('salDoha').value) || 0;
  const leb  = parseFloat(document.getElementById('salLeb').value)  || 0;
  const preview = document.getElementById('ratePreview');
  const grid    = document.getElementById('previewGrid');
  if (!doha && !leb) { preview.style.display = 'none'; return; }
  preview.style.display = '';

  const rows = [];
  if (doha) {
    rows.push({loc:'Doha', sal:doha, h: doha/150, d: doha/150*8});
  }
  if (leb) {
    rows.push({loc:'Lebanon', sal:leb, h: leb/150, d: leb/150*8});
  }

  grid.innerHTML = rows.map(r => `
    <div class="col-md-6">
      <div class="card p-2">
        <div class="fw-semibold mb-1" style="font-size:11px;color:#64748b">${r.loc}</div>
        <div class="row text-center g-1">
          <div class="col"><div style="font-size:10px;color:#94a3b8">Hourly</div><div class="fw-bold">${fmt(r.h)}</div></div>
          <div class="col"><div style="font-size:10px;color:#94a3b8">Daily</div><div class="fw-bold">${fmt(r.d)}</div></div>
          <div class="col"><div style="font-size:10px;color:#94a3b8">Monthly</div><div class="fw-bold">${fmt(r.sal)}</div></div>
          <div class="col"><div style="font-size:10px;color:#94a3b8">Annual</div><div class="fw-bold">${fmt(r.sal*12)}</div></div>
        </div>
      </div>
    </div>`).join('');
}
updatePreview();
</script>
