<p class="text-muted mb-4">
  <a href="<?= url('/clients') ?>"><i class="bi bi-arrow-left me-1"></i>Back to clients</a>
</p>

<div class="row">
  <div class="col-lg-7">
    <form method="post" action="<?= url($editing ? "/clients/{$client['id']}/edit" : '/clients/create') ?>">
      <?= csrf_field() ?>

      <div class="card mb-4">
        <div class="card-header"><?= $editing ? 'Edit Client' : 'New Client' ?></div>
        <div class="card-body p-4">
          <div class="row g-3">

            <div class="col-12">
              <label class="form-label fw-semibold">Client / Company Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" required
                     value="<?= old('name', $client['name'] ?? '') ?>"
                     placeholder="e.g. Acme Corporation">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Industry</label>
              <input type="text" name="industry" class="form-control"
                     value="<?= old('industry', $client['industry'] ?? '') ?>"
                     placeholder="e.g. FMCG, Real Estate, Tech">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Contact Name</label>
              <input type="text" name="contact_name" class="form-control"
                     value="<?= old('contact_name', $client['contact_name'] ?? '') ?>"
                     placeholder="Primary contact person">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Contact Email</label>
              <input type="email" name="contact_email" class="form-control"
                     value="<?= old('contact_email', $client['contact_email'] ?? '') ?>"
                     placeholder="contact@client.com">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Contact Phone</label>
              <input type="text" name="contact_phone" class="form-control"
                     value="<?= old('contact_phone', $client['contact_phone'] ?? '') ?>"
                     placeholder="+971 50 000 0000">
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">Notes <span class="text-muted fw-normal">(optional)</span></label>
              <textarea name="notes" class="form-control" rows="3"
                        placeholder="Any relevant context about this client..."><?= old('notes', $client['notes'] ?? '') ?></textarea>
            </div>

            <?php if ($editing): ?>
            <div class="col-12">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1"
                       <?= ($client['is_active'] ?? 1) ? 'checked' : '' ?>>
                <label class="form-check-label" for="isActive">Active (appears in proposal dropdowns)</label>
              </div>
            </div>
            <?php endif ?>

          </div>
        </div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary px-4">
          <i class="bi bi-check-lg me-1"></i> <?= $editing ? 'Save Changes' : 'Add Client' ?>
        </button>
        <a href="<?= url('/clients') ?>" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
