<div class="row justify-content-center">
  <div class="col-lg-6">
    <p class="text-muted mb-3"><a href="<?= url('/companies') ?>"><i class="bi bi-arrow-left me-1"></i>Back to companies</a></p>
    <div class="card">
      <div class="card-header"><?= $company ? 'Edit ' . e($company['name']) : 'Add New Company' ?></div>
      <div class="card-body p-4">
        <form method="post" action="<?= e($action) ?>" enctype="multipart/form-data">
          <?= csrf_field() ?>

          <div class="mb-3">
            <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="nameInput" class="form-control"
                   value="<?= $company ? e($company['name']) : old('name') ?>"
                   placeholder="e.g. GreyDoha" required autofocus
                   oninput="autoSlug()">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Slug</label>
            <div class="input-group">
              <span class="input-group-text text-muted" style="font-size:12px">/</span>
              <input type="text" name="slug" id="slugInput" class="form-control"
                     value="<?= $company ? e($company['slug']) : old('slug') ?>"
                     placeholder="e.g. greydoha" pattern="[a-z0-9-]+" title="Lowercase letters, numbers, and hyphens only">
            </div>
            <div class="form-text">Auto-generated from name. Used in URLs. Lowercase, no spaces.</div>
          </div>

          <!-- Logo upload -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Logo</label>

            <?php if ($company && $company['logo_path']): ?>
              <div class="mb-2 d-flex align-items-center gap-3">
                <img src="<?= url('/public/' . e($company['logo_path'])) ?>"
                     alt="Current logo" style="height:48px; max-width:140px; object-fit:contain; border:1px solid #e2e8f0; border-radius:6px; padding:4px; background:#f8fafc">
                <div>
                  <div style="font-size:12px; color:#64748b">Current logo</div>
                  <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" name="remove_logo" id="removeLogo" value="1">
                    <label class="form-check-label" for="removeLogo" style="font-size:12px; color:#ef4444">Remove logo</label>
                  </div>
                </div>
              </div>
            <?php endif ?>

            <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg,image/gif,image/svg+xml,image/webp">
            <div class="form-text">PNG, JPG, SVG, or WebP · Max 5 MB · Transparent background works best</div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" style="max-width:120px"
                   value="<?= $company ? e($company['sort_order']) : old('sort_order', '0') ?>" min="0">
            <div class="form-text">Lower number appears first.</div>
          </div>

          <?php if ($company): ?>
          <div class="mb-3">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                     <?= $company['is_active'] ? 'checked' : '' ?>>
              <label class="form-check-label" for="is_active">Active</label>
            </div>
          </div>
          <?php endif ?>

          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg me-1"></i><?= $company ? 'Save Changes' : 'Add Company' ?>
            </button>
            <a href="<?= url('/companies') ?>" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
let slugEdited = <?= $company ? 'true' : 'false' ?>;
const nameInput = document.getElementById('nameInput');
const slugInput = document.getElementById('slugInput');

slugInput.addEventListener('input', () => { slugEdited = true; });

function autoSlug() {
  if (slugEdited) return;
  slugInput.value = nameInput.value
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-|-$/g, '');
}
</script>
