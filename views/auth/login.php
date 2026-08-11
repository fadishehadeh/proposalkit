<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In — ProposalKit</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; }
    body {
      margin: 0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #0f172a;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      font-size: 14px;
    }
    .login-card {
      width: 100%;
      max-width: 380px;
      background: #fff;
      border-radius: 14px;
      padding: 40px 36px 36px;
      box-shadow: 0 24px 60px rgba(0,0,0,.35);
    }
    .login-brand {
      text-align: center;
      margin-bottom: 28px;
    }
    .login-brand-logo {
      font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px;
    }
    .login-brand-logo span { color: #2563eb; }
    .login-brand-sub {
      font-size: 11px; color: #94a3b8; text-transform: uppercase;
      letter-spacing: 0.6px; margin-top: 3px;
    }
    .form-label { font-weight: 600; font-size: 12.5px; color: #374151; margin-bottom: 5px; }
    .form-control {
      border: 1.5px solid #e2e8f0; border-radius: 8px;
      padding: 9px 12px; font-size: 13.5px; color: #0f172a;
      transition: border-color .15s;
    }
    .form-control:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); outline: none; }
    .btn-login {
      width: 100%; padding: 10px; border: none; border-radius: 8px;
      background: #1d4ed8; color: #fff; font-weight: 700; font-size: 14px;
      cursor: pointer; transition: background .15s;
    }
    .btn-login:hover { background: #1e40af; }
    .flash-error {
      background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;
      border-radius: 8px; padding: 10px 14px; margin-bottom: 18px;
      font-size: 13px; display: flex; align-items: center; gap: 8px;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="login-brand">
      <div class="login-brand-logo">Proposal<span>Kit</span></div>
      <div class="login-brand-sub">G2 Group</div>
    </div>

    <?php
    // Flash error (read directly without layout helper)
    $flashMsg = $_SESSION['flash']['error'] ?? null;
    unset($_SESSION['flash']['error']);
    if ($flashMsg): ?>
      <div class="flash-error">
        <i class="bi bi-exclamation-circle"></i><?= $flashMsg ?>
      </div>
    <?php endif ?>

    <form method="post" action="<?= url('/login') ?>">
      <?= csrf_field() ?>

      <div class="mb-3">
        <label class="form-label">Email address</label>
        <input type="email" name="email" class="form-control"
               value="<?= e($_SESSION['old']['email'] ?? '') ?>"
               autofocus autocomplete="email" required>
      </div>

      <div class="mb-4">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control"
               autocomplete="current-password" required>
      </div>

      <?php unset($_SESSION['old']) ?>

      <button type="submit" class="btn-login">
        Sign in <i class="bi bi-arrow-right ms-1"></i>
      </button>
    </form>
  </div>
</body>
</html>
