<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h5 class="mb-0">Users</h5>
    <div class="text-muted" style="font-size:12px;margin-top:2px">Manage access and company assignments</div>
  </div>
  <a href="<?= url('/admin/users/create') ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-person-plus me-1"></i> New User
  </a>
</div>

<?php if (empty($users)): ?>
  <div class="card p-5 text-center text-muted">
    <i class="bi bi-people" style="font-size:32px;opacity:.3;margin-bottom:10px"></i>
    <div>No users yet. <a href="<?= url('/admin/users/create') ?>">Add one.</a></div>
  </div>
<?php else: ?>
<div class="card">
  <div class="table-responsive">
    <table class="table mb-0">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Companies</th>
          <th class="text-center">Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td class="fw-semibold"><?= e($u['name']) ?></td>
          <td class="text-muted"><?= e($u['email']) ?></td>
          <td>
            <?php if ($u['role'] === 'superadmin'): ?>
              <span style="background:#ede9fe;color:#5b21b6;font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px">
                Super Admin
              </span>
            <?php else: ?>
              <span style="background:#f1f5f9;color:#475569;font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px">
                User
              </span>
            <?php endif ?>
          </td>
          <td class="text-muted" style="font-size:12.5px">
            <?= $u['role'] === 'superadmin' ? '<span style="color:#94a3b8">All companies</span>' : (e($u['company_names'] ?? '') ?: '<span style="color:#94a3b8">None assigned</span>') ?>
          </td>
          <td class="text-center">
            <span class="<?= $u['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
              <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
            </span>
          </td>
          <td class="text-end" style="white-space:nowrap">
            <a href="<?= url("/admin/users/{$u['id']}/edit") ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
            <?php if ((auth_user()['id'] ?? 0) !== (int)$u['id']): ?>
            <form method="post" action="<?= url("/admin/users/{$u['id']}/delete") ?>" class="d-inline"
                  onsubmit="return confirm('Delete <?= e(addslashes($u['name'])) ?>? This cannot be undone.')">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
            <?php endif ?>
          </td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif ?>
