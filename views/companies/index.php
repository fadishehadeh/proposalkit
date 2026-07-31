<div class="d-flex justify-content-between align-items-center mb-4">
  <p class="text-muted mb-0" style="font-size:13px">Manage your agencies. Each company has its own positions, rate card, and proposals.</p>
  <a href="<?= url('/companies/create') ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i> Add Company
  </a>
</div>

<div class="row g-4">
  <?php foreach ($companies as $c): ?>
  <div class="col-md-6 col-xl-4">
    <div class="card h-100" style="<?= !$c['is_active'] ? 'opacity:.6' : '' ?>">
      <div class="card-body d-flex flex-column align-items-center text-center p-4">
        <!-- Logo -->
        <?php if ($c['logo_path']): ?>
          <img src="<?= url('/public/' . e($c['logo_path'])) ?>"
               alt="<?= e($c['name']) ?>"
               style="height:64px; max-width:180px; object-fit:contain; margin-bottom:16px">
        <?php else: ?>
          <div style="width:72px; height:72px; border-radius:14px; background:#1e293b; display:flex; align-items:center; justify-content:center; margin-bottom:16px">
            <span style="color:#fff; font-size:22px; font-weight:800; letter-spacing:-1px"><?= e(mb_substr($c['name'], 0, 2)) ?></span>
          </div>
        <?php endif ?>

        <div class="fw-bold" style="font-size:17px"><?= e($c['name']) ?></div>
        <div class="text-muted" style="font-size:12px; margin-top:2px"><?= e($c['slug']) ?></div>

        <?php if (!$c['is_active']): ?>
          <span class="badge-inactive mt-2">Inactive</span>
        <?php endif ?>

        <div class="d-flex gap-3 mt-3" style="font-size:12px; color:#64748b">
          <span><strong class="text-dark"><?= $c['position_count'] ?></strong> positions</span>
          <span><strong class="text-dark"><?= $c['proposal_count'] ?></strong> proposals</span>
        </div>
      </div>
      <div class="card-footer bg-transparent d-flex gap-2 justify-content-center pb-3">
        <a href="<?= url("/positions?company={$c['id']}") ?>" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-people me-1"></i>Positions
        </a>
        <a href="<?= url("/companies/{$c['id']}/edit") ?>" class="btn btn-sm btn-outline-primary">
          <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <form method="post" action="<?= url("/companies/{$c['id']}/delete") ?>" class="d-inline"
              onsubmit="return confirm('Delete <?= e(addslashes($c['name'])) ?>? This cannot be undone.')">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-sm btn-outline-danger">
            <i class="bi bi-trash"></i>
          </button>
        </form>
      </div>
    </div>
  </div>
  <?php endforeach ?>

  <?php if (empty($companies)): ?>
    <div class="col-12 text-center text-muted py-5">No companies yet. <a href="<?= url('/companies/create') ?>">Add one</a>.</div>
  <?php endif ?>
</div>
