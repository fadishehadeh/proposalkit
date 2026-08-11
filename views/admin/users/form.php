<p class="text-muted mb-4">
  <a href="<?= url('/admin/users') ?>"><i class="bi bi-arrow-left me-1"></i>Back to Users</a>
</p>

<div class="row">
  <div class="col-lg-7">
    <form method="post" action="<?= $user ? url("/admin/users/{$user['id']}/edit") : url('/admin/users/create') ?>">
      <?= csrf_field() ?>

      <div class="card mb-4">
        <div class="card-header">Account Details</div>
        <div class="card-body p-4">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control"
                     value="<?= old('name', $user['name'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control"
                     value="<?= old('email', $user['email'] ?? '') ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">
                Password <?= $user ? '<span class="text-muted fw-normal">(leave blank to keep current)</span>' : '<span class="text-danger">*</span>' ?>
              </label>
              <input type="password" name="password" class="form-control"
                     <?= $user ? '' : 'required' ?> minlength="6" autocomplete="new-password">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
              <select name="role" class="form-select">
                <option value="user"       <?= old('role', $user['role'] ?? 'user') === 'user'       ? 'selected' : '' ?>>User</option>
                <option value="superadmin" <?= old('role', $user['role'] ?? '') === 'superadmin' ? 'selected' : '' ?>>Super Admin</option>
              </select>
            </div>
            <div class="col-md-3 d-flex align-items-end pb-1">
              <div class="form-check mb-1">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                       <?= (int) old('is_active', $user['is_active'] ?? 1) ? 'checked' : '' ?>>
                <label class="form-check-label fw-semibold" for="is_active">Active</label>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-header">Company Access</div>
        <div class="card-body p-4">
          <p class="text-muted mb-3" style="font-size:13px">
            Super Admins can see all companies regardless of this selection.
          </p>
          <?php if (empty($companies)): ?>
            <p class="text-muted">No companies found.</p>
          <?php else: ?>
          <div class="row g-2">
            <?php
            $oldIds = $_SESSION['old']['company_ids'] ?? null;
            $selectedIds = $oldIds !== null
                ? array_map('intval', (array) $oldIds)
                : (array) $userCompanyIds;
            ?>
            <?php foreach ($companies as $c): ?>
            <div class="col-md-6">
              <div class="form-check">
                <input class="form-check-input" type="checkbox"
                       name="company_ids[]" value="<?= $c['id'] ?>"
                       id="co_<?= $c['id'] ?>"
                       <?= in_array((int)$c['id'], $selectedIds, true) ? 'checked' : '' ?>>
                <label class="form-check-label" for="co_<?= $c['id'] ?>"><?= e($c['name']) ?></label>
              </div>
            </div>
            <?php endforeach ?>
          </div>
          <?php endif ?>
        </div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary px-4">
          <i class="bi bi-check-lg me-1"></i> <?= $user ? 'Save Changes' : 'Create User' ?>
        </button>
        <a href="<?= url('/admin/users') ?>" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
