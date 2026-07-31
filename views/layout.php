<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle ?? 'ProposalKit') ?> — ProposalKit</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root {
      --sidebar-w: 240px;
      --sidebar-bg: #0f172a;
      --sidebar-hover: #1e293b;
      --sidebar-active: #1d4ed8;
      --accent: #2563eb;
      --surface: #f8fafc;
      --border: #e2e8f0;
    }
    *, *::before, *::after { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: var(--surface);
      color: #1e293b;
      font-size: 14px;
    }
    .sidebar {
      position: fixed; top: 0; left: 0;
      width: var(--sidebar-w); height: 100vh;
      background: var(--sidebar-bg);
      display: flex; flex-direction: column;
      z-index: 200; overflow-y: auto;
    }
    .sidebar-brand {
      padding: 18px 18px 14px;
      border-bottom: 1px solid #1e293b;
    }
    .sidebar-brand-logo {
      font-size: 20px; font-weight: 800; color: #fff;
      letter-spacing: -0.5px; line-height: 1;
    }
    .sidebar-brand-logo span { color: #60a5fa; }
    .sidebar-brand-sub {
      font-size: 10px; color: #475569; margin-top: 2px;
      letter-spacing: 0.5px; text-transform: uppercase;
    }
    .sidebar-nav { padding: 10px 0; flex: 1; }
    .sidebar-label {
      font-size: 10px; font-weight: 600; color: #475569;
      text-transform: uppercase; letter-spacing: 0.8px;
      padding: 10px 18px 3px;
    }
    .sidebar-nav a {
      display: flex; align-items: center; gap: 10px;
      padding: 8px 18px; color: #94a3b8;
      text-decoration: none; font-size: 13px; font-weight: 500;
      transition: all .12s;
    }
    .sidebar-nav a:hover { background: var(--sidebar-hover); color: #fff; }
    .sidebar-nav a.active { background: var(--sidebar-active); color: #fff; }
    .sidebar-nav a .bi { font-size: 14px; width: 18px; text-align: center; }
    .sidebar-footer {
      padding: 12px 18px;
      border-top: 1px solid #1e293b;
      font-size: 11px; color: #334155;
    }
    .main-wrap {
      margin-left: var(--sidebar-w);
      min-height: 100vh;
      display: flex; flex-direction: column;
    }
    .topbar {
      background: #fff; border-bottom: 1px solid var(--border);
      padding: 13px 28px;
      position: sticky; top: 0; z-index: 100;
      display: flex; align-items: center; justify-content: space-between;
    }
    .topbar-title { font-size: 16px; font-weight: 700; color: #0f172a; }
    .topbar-actions { display: flex; gap: 8px; align-items: center; }
    .page-content { padding: 26px 28px; flex: 1; max-width: 1280px; }
    .card { border: 1px solid var(--border); border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,.05); }
    .card-header { background: #fff; border-bottom: 1px solid var(--border); padding: 13px 20px; border-radius: 10px 10px 0 0 !important; font-weight: 600; }
    .table th { font-size: 11.5px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .4px; border-bottom: 2px solid var(--border); }
    .table td { vertical-align: middle; font-size: 13.5px; }
    .badge-active   { background: #d1fae5; color: #065f46; font-size: 11px; padding: 3px 8px; border-radius: 20px; font-weight: 600; }
    .badge-inactive { background: #fee2e2; color: #991b1b; font-size: 11px; padding: 3px 8px; border-radius: 20px; font-weight: 600; }
    .flash { border-radius: 8px; padding: 11px 16px; margin-bottom: 18px; font-size: 13.5px; }
    .flash-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .flash-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    .rate-pill { display: inline-block; background: #eff6ff; color: #1d4ed8; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 20px; }
    .co-pill   { display: inline-block; background: #f0fdf4; color: #166534; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 20px; }
    .num { font-variant-numeric: tabular-nums; }
    .status-draft    { display:inline-block; background:#f1f5f9; color:#475569; font-size:11px; font-weight:600; padding:2px 8px; border-radius:20px; }
    .status-sent     { display:inline-block; background:#dbeafe; color:#1d4ed8; font-size:11px; font-weight:600; padding:2px 8px; border-radius:20px; }
    .status-approved { display:inline-block; background:#d1fae5; color:#065f46; font-size:11px; font-weight:600; padding:2px 8px; border-radius:20px; }
    .status-rejected { display:inline-block; background:#fee2e2; color:#991b1b; font-size:11px; font-weight:600; padding:2px 8px; border-radius:20px; }
  </style>
</head>
<body>

<div class="sidebar">
  <div class="sidebar-brand">
    <div class="sidebar-brand-logo">Proposal<span>Kit</span></div>
    <div class="sidebar-brand-sub">G2 Group</div>
  </div>

  <nav class="sidebar-nav">
    <a href="<?= url('/') ?>" class="<?= current_uri() === '/' || current_uri() === '/dashboard' ? 'active' : '' ?>">
      <i class="bi bi-speedometer2"></i> Dashboard
    </a>

    <div class="sidebar-label">Agencies</div>
    <a href="<?= url('/companies') ?>" class="<?= active('/companies') ?>">
      <i class="bi bi-buildings"></i> Companies
    </a>

    <div class="sidebar-label">Clients</div>
    <a href="<?= url('/clients') ?>" class="<?= str_starts_with(current_uri(), '/clients') ? 'active' : '' ?>">
      <i class="bi bi-person-lines-fill"></i> All Clients
    </a>
    <a href="<?= url('/clients/create') ?>" class="<?= current_uri() === '/clients/create' ? 'active' : '' ?>">
      <i class="bi bi-person-plus"></i> New Client
    </a>

    <div class="sidebar-label">Database</div>
    <a href="<?= url('/positions') ?>" class="<?= str_starts_with(current_uri(), '/positions') ? 'active' : '' ?>">
      <i class="bi bi-people"></i> Positions
    </a>
    <a href="<?= url('/rate-card') ?>" class="<?= active('/rate-card') ?>">
      <i class="bi bi-grid-3x3"></i> Rate Card
    </a>

    <div class="sidebar-label">Proposals</div>
    <a href="<?= url('/proposals') ?>" class="<?= str_starts_with(current_uri(), '/proposals') && current_uri() !== '/proposals/create' ? 'active' : '' ?>">
      <i class="bi bi-file-earmark-text"></i> All Proposals
    </a>
    <a href="<?= url('/proposals/create') ?>" class="<?= current_uri() === '/proposals/create' ? 'active' : '' ?>">
      <i class="bi bi-plus-circle"></i> New Proposal
    </a>
  </nav>

  <div class="sidebar-footer">
    ProposalKit &copy; <?= date('Y') ?>
  </div>
</div>

<div class="main-wrap">
  <div class="topbar">
    <div class="topbar-title"><?= e($pageTitle ?? '') ?></div>
    <div class="topbar-actions" id="topbar-actions"></div>
  </div>

  <div class="page-content">
    <?php if ($msg = get_flash('success')): ?>
      <div class="flash flash-success"><i class="bi bi-check-circle me-2"></i><?= $msg ?></div>
    <?php endif ?>
    <?php if ($msg = get_flash('error')): ?>
      <div class="flash flash-error"><i class="bi bi-exclamation-circle me-2"></i><?= $msg ?></div>
    <?php endif ?>

    <?= $content ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
