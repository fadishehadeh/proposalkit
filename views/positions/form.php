<div class="row justify-content-center">
  <div class="col-lg-6">
    <p class="text-muted mb-3"><a href="<?= url('/positions') ?>"><i class="bi bi-arrow-left me-1"></i>Back to positions</a></p>
    <div class="card">
      <div class="card-header"><?= $position ? 'Edit Position' : 'Add New Position' ?></div>
      <div class="card-body p-4">
        <form method="post" action="<?= e($action) ?>">
          <?= csrf_field() ?>

          <div class="mb-3">
            <label class="form-label fw-semibold">Company</label>
            <select name="company_id" class="form-select">
              <option value="">— No company —</option>
              <?php foreach ($companies as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($presetCompanyId ?? ($position['company_id'] ?? null)) == $c['id'] ? 'selected' : '' ?>>
                  <?= e($c['name']) ?>
                </option>
              <?php endforeach ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Designation <span class="text-danger">*</span></label>
            <input type="text" name="designation" class="form-control"
                   value="<?= $position ? e($position['designation']) : old('designation') ?>"
                   placeholder="e.g. Senior Account Manager" required autofocus>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Monthly Salary <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text text-muted" style="font-size:12px">per month</span>
              <input type="number" name="monthly_salary" class="form-control"
                     value="<?= $position ? e($position['monthly_salary']) : old('monthly_salary') ?>"
                     step="0.01" min="1" placeholder="e.g. 25000" required>
            </div>
            <div class="form-text" id="ratePreview"></div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" style="max-width:120px"
                   value="<?= $position ? e($position['sort_order']) : old('sort_order', '0') ?>" min="0">
            <div class="form-text">Lower number appears first in lists.</div>
          </div>

          <?php if ($position): ?>
            <div class="mb-3">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                       <?= $position['is_active'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_active">Active</label>
              </div>
            </div>
          <?php endif ?>

          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-1"></i><?= $position ? 'Save Changes' : 'Add Position' ?>
            </button>
            <a href="<?= url('/positions') ?>" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
const salInput = document.querySelector('[name=monthly_salary]');
const preview  = document.getElementById('ratePreview');
function updatePreview() {
  const m = parseFloat(salInput.value) || 0;
  if (!m) { preview.textContent = ''; return; }
  const h = (m / 150).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
  const d = (m / 150 * 8).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
  const a = (m * 12).toLocaleString('en-US', {minimumFractionDigits:0, maximumFractionDigits:0});
  preview.innerHTML = `Hourly: <strong>${h}</strong> &nbsp;·&nbsp; Daily: <strong>${d}</strong> &nbsp;·&nbsp; Annual: <strong>${a}</strong>`;
}
salInput.addEventListener('input', updatePreview);
updatePreview();
</script>
