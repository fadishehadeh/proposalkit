<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h5 class="mb-0">All Clients</h5>
    <div class="text-muted" style="font-size:12px;margin-top:2px">Client directory — linked to proposals</div>
  </div>
  <a href="<?= url('/clients/create') ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i> Add Client
  </a>
</div>

<?php if (empty($clients)): ?>
  <div class="card p-5 text-center text-muted">
    <i class="bi bi-person-lines-fill" style="font-size:32px;margin-bottom:10px;opacity:.3"></i>
    <div>No clients yet. <a href="<?= url('/clients/create') ?>">Add your first client.</a></div>
  </div>
<?php else: ?>
<div class="card">
  <div class="table-responsive">
    <table class="table mb-0">
      <thead>
        <tr>
          <th>Client</th>
          <th>Industry</th>
          <th>Contact</th>
          <th>Email</th>
          <th>Phone</th>
          <th class="text-center">Proposals</th>
          <th class="text-center">Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($clients as $cl): ?>
        <tr>
          <td class="fw-semibold"><?= e($cl['name']) ?></td>
          <td class="text-muted"><?= $cl['industry'] ? e($cl['industry']) : '—' ?></td>
          <td><?= $cl['contact_name'] ? e($cl['contact_name']) : '<span class="text-muted">—</span>' ?></td>
          <td>
            <?php if ($cl['contact_email']): ?>
              <a href="mailto:<?= e($cl['contact_email']) ?>" class="text-decoration-none" style="font-size:13px"><?= e($cl['contact_email']) ?></a>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif ?>
          </td>
          <td class="text-muted"><?= $cl['contact_phone'] ? e($cl['contact_phone']) : '—' ?></td>
          <td class="text-center">
            <?php if ($cl['proposal_count'] > 0): ?>
              <a href="<?= url('/proposals?client=' . $cl['id']) ?>" class="rate-pill px-3 py-1">
                <?= $cl['proposal_count'] ?>
              </a>
            <?php else: ?>
              <span class="text-muted">0</span>
            <?php endif ?>
          </td>
          <td class="text-center">
            <span class="<?= $cl['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
              <?= $cl['is_active'] ? 'Active' : 'Archived' ?>
            </span>
          </td>
          <td class="text-end" style="white-space:nowrap">
            <a href="<?= url("/clients/{$cl['id']}/edit") ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
            <?php if ($cl['proposal_count'] == 0): ?>
            <form method="post" action="<?= url("/clients/{$cl['id']}/delete") ?>" class="d-inline"
                  onsubmit="return confirm('Delete <?= e(addslashes($cl['name'])) ?>?')">
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
