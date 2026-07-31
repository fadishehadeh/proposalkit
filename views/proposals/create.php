<?php
// Build positions map: all positions with company info
$posMap    = [];
$posByComp = ['all' => []];
foreach ($positions as $p) {
    $pid = $p['id'];
    $cid = (int)($p['company_id'] ?? 0);
    $posMap[$pid] = [
        'id'             => $pid,
        'designation'    => $p['designation'],
        'monthly_salary' => (float)$p['monthly_salary'],
        'company_id'     => $cid,
    ];
    if ($cid) {
        $posByComp[$cid][] = $posMap[$pid];
    }
    $posByComp['all'][] = $posMap[$pid];
}
?>
<style>
  .item-row td { padding: 6px 8px; }
  .item-row select, .item-row input { font-size: 13px; }
  tfoot td { font-size: 13px; }
</style>

<p class="text-muted mb-4"><a href="<?= url('/proposals') ?>"><i class="bi bi-arrow-left me-1"></i>Back to proposals</a></p>

<div class="row">
  <div class="col-xl-11">
    <form method="post" action="<?= url('/proposals/create') ?>" id="proposalForm">
      <?= csrf_field() ?>

      <!-- Proposal Details -->
      <div class="card mb-4">
        <div class="card-header">Proposal Details</div>
        <div class="card-body p-4">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label fw-semibold">Agency / Company <span class="text-danger">*</span></label>
              <select name="company_id" id="companySelect" class="form-select" onchange="onCompanyChange()" required>
                <option value="">— Select Agency —</option>
                <?php foreach ($companies as $c): ?>
                  <option value="<?= $c['id'] ?>"
                          <?= old('company_id', '') == $c['id'] ? 'selected' : '' ?>
                          data-logo="<?= $c['logo_path'] ? url('/public/' . e($c['logo_path'])) : '' ?>">
                    <?= e($c['name']) ?>
                  </option>
                <?php endforeach ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Client <span class="text-danger">*</span></label>
              <div class="input-group">
                <select name="client_id" class="form-select" required>
                  <option value="">— Select Client —</option>
                  <?php foreach ($clients as $cl): ?>
                    <option value="<?= $cl['id'] ?>" <?= old('client_id', '') == $cl['id'] ? 'selected' : '' ?>>
                      <?= e($cl['name']) ?>
                    </option>
                  <?php endforeach ?>
                </select>
                <a href="<?= url('/clients/create') ?>" target="_blank"
                   class="btn btn-outline-secondary" title="Add new client">
                  <i class="bi bi-plus-lg"></i>
                </a>
              </div>
              <div style="font-size:11px;color:#94a3b8;margin-top:4px">
                Can't find it? <a href="<?= url('/clients/create') ?>" target="_blank">Add a new client</a> then refresh.
              </div>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Project Name <span class="text-danger">*</span></label>
              <input type="text" name="project_name" class="form-control" value="<?= old('project_name') ?>" required placeholder="e.g. Brand Launch 2025">
            </div>
            <div class="col-md-1">
              <label class="form-label fw-semibold">Currency</label>
              <select name="currency" id="currency" class="form-select">
                <?php foreach ($currencies as $c): ?>
                  <option value="<?= $c ?>" <?= old('currency', 'AED') === $c ? 'selected' : '' ?>><?= $c ?></option>
                <?php endforeach ?>
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold">Multiplier <span class="text-danger">*</span></label>
              <select name="multiplier" id="multiplier" class="form-select" onchange="recalcAll()">
                <option value="">— Select —</option>
                <?php foreach ($multipliers as $m): ?>
                  <option value="<?= $m ?>" <?= old('multiplier', '') == (string)$m ? 'selected' : '' ?>><?= number_format($m, 1) ?>x</option>
                <?php endforeach ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Notes <span class="text-muted fw-normal">(optional)</span></label>
              <textarea name="notes" class="form-control" rows="2" placeholder="Any additional context for this proposal..."><?= old('notes') ?></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- Position Lines -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>Positions</span>
          <button type="button" class="btn btn-primary btn-sm" onclick="addRow()">
            <i class="bi bi-plus-lg me-1"></i> Add Position
          </button>
        </div>
        <div class="table-responsive">
          <table class="table mb-0" id="itemsTable">
            <thead>
              <tr style="background:#f8fafc">
                <th style="width:210px">Select Position</th>
                <th style="min-width:170px">Designation / Title</th>
                <th style="width:145px">Monthly Salary</th>
                <th style="width:100px">FTE</th>
                <th style="width:150px">Monthly Fee</th>
                <th style="width:150px">Annual Fee</th>
                <th style="width:40px"></th>
              </tr>
            </thead>
            <tbody id="itemsTbody"></tbody>
            <tfoot>
              <tr style="background:#f0f4f8">
                <td colspan="4" class="text-end fw-bold pe-3">Total</td>
                <td class="fw-bold num" id="totalMonthly">—</td>
                <td class="fw-bold num" id="totalAnnual">—</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary px-4">
          <i class="bi bi-check-lg me-1"></i> Save Proposal
        </button>
        <a href="<?= url('/proposals') ?>" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<script>
const POSITIONS_BY_COMPANY = <?= json_encode($posByComp, JSON_HEX_TAG) ?>;
const POSITIONS_MAP        = <?= json_encode($posMap, JSON_HEX_TAG) ?>;
const companySelect = document.getElementById('companySelect');
const multiplierSelect = document.getElementById('multiplier');

function getCurrentPositions() {
  const cid = companySelect.value;
  return cid ? (POSITIONS_BY_COMPANY[cid] || []) : (POSITIONS_BY_COMPANY['all'] || []);
}

function buildOptions(selectedId) {
  const positions = getCurrentPositions();
  let opts = '<option value="">— Custom —</option>';
  positions.forEach(p => {
    opts += `<option value="${p.id}" ${p.id == selectedId ? 'selected' : ''} data-salary="${p.monthly_salary}">${p.designation}</option>`;
  });
  return opts;
}

function onCompanyChange() {
  // Refresh position dropdowns in all existing rows
  document.querySelectorAll('.pos-select').forEach(sel => {
    const prev = sel.value;
    sel.innerHTML = buildOptions('');
    // If the previously selected position is still available, keep it
    const opt = sel.querySelector(`option[value="${prev}"]`);
    if (opt) sel.value = prev;
    else {
      // position not in this company — clear
      const row = sel.closest('tr');
      row.querySelector('.pos-desig').value  = '';
      row.querySelector('.pos-salary').value = '';
      recalcRow(row);
    }
  });
}

function addRow(posId, desig, salary, alloc) {
  const tr = document.createElement('tr');
  tr.className = 'item-row';
  tr.innerHTML = `
    <td>
      <select class="form-select form-select-sm pos-select" name="position_id[]" onchange="onPosChange(this)">
        ${buildOptions(posId || '')}
      </select>
    </td>
    <td>
      <input type="text" class="form-control form-control-sm pos-desig" name="designation[]"
             value="${escHtml(desig || '')}" placeholder="Enter or select above" required>
    </td>
    <td>
      <input type="number" class="form-control form-control-sm pos-salary" name="monthly_salary[]"
             value="${salary || ''}" step="0.01" min="1" placeholder="0" required onchange="recalcRow(this.closest('tr'))">
    </td>
    <td>
      <input type="number" class="form-control form-control-sm pos-alloc" name="allocation[]"
             value="${alloc || '1'}" step="0.1" min="0.1" max="20" required onchange="recalcRow(this.closest('tr'))">
    </td>
    <td class="pos-monthly text-end num text-muted">—</td>
    <td class="pos-annual text-end num text-muted">—</td>
    <td>
      <button type="button" class="btn btn-sm btn-outline-danger px-1 py-0" onclick="removeRow(this)">
        <i class="bi bi-x-lg" style="font-size:11px"></i>
      </button>
    </td>`;
  document.getElementById('itemsTbody').appendChild(tr);
  recalcRow(tr);
}

function onPosChange(sel) {
  const row = sel.closest('tr');
  const id  = sel.value;
  if (id && POSITIONS_MAP[id]) {
    row.querySelector('.pos-desig').value  = POSITIONS_MAP[id].designation;
    row.querySelector('.pos-salary').value = POSITIONS_MAP[id].monthly_salary;
  }
  recalcRow(row);
}

function recalcRow(row) {
  const mult  = parseFloat(multiplierSelect.value) || 0;
  const sal   = parseFloat(row.querySelector('.pos-salary').value) || 0;
  const alloc = parseFloat(row.querySelector('.pos-alloc').value)  || 0;
  const mFee  = sal * mult * alloc;
  const aFee  = mFee * 12;
  row.querySelector('.pos-monthly').textContent = mFee ? fmt(mFee) : '—';
  row.querySelector('.pos-annual').textContent  = aFee ? fmt(aFee) : '—';
  recalcTotals();
}

function recalcAll() {
  document.querySelectorAll('.item-row').forEach(recalcRow);
}

function recalcTotals() {
  const mult = parseFloat(multiplierSelect.value) || 0;
  let tm = 0, ta = 0;
  document.querySelectorAll('.item-row').forEach(row => {
    const sal   = parseFloat(row.querySelector('.pos-salary').value) || 0;
    const alloc = parseFloat(row.querySelector('.pos-alloc').value)  || 0;
    const mFee  = sal * mult * alloc;
    tm += mFee; ta += mFee * 12;
  });
  document.getElementById('totalMonthly').textContent = tm ? fmt(tm) : '—';
  document.getElementById('totalAnnual').textContent  = ta ? fmt(ta) : '—';
}

function removeRow(btn) {
  const tbody = document.getElementById('itemsTbody');
  btn.closest('tr').remove();
  if (!tbody.children.length) addRow();
  recalcTotals();
}

function fmt(n) { return n.toLocaleString('en-US', {minimumFractionDigits:0, maximumFractionDigits:0}); }
function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

// Init
addRow();
</script>
