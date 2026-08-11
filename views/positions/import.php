<p class="text-muted mb-3"><a href="<?= url('/positions') ?>"><i class="bi bi-arrow-left me-1"></i>Back to Positions</a></p>

<div class="card p-4" style="max-width:520px">
  <h5 class="mb-1">Import Positions from Excel</h5>
  <p class="text-muted mb-3" style="font-size:13px">
    Upload the exported .xlsx file after editing. Rows with an ID will be updated; rows with a blank ID will be inserted (Company ID required for new rows).
  </p>
  <form method="post" action="<?= url('/positions/import') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="mb-3">
      <label class="form-label fw-500">Excel File (.xlsx)</label>
      <input type="file" name="file" class="form-control" accept=".xlsx" required>
    </div>
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-upload me-1"></i> Import
      </button>
      <a href="<?= url('/positions') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>
