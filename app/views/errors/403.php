<?php $baseUrl = rtrim(BASE_URL, '/'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>403 — Akses Ditolak</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --primary:       #7B1C1C;
      --primary-dark:  #5A1212;
      --gold:          #C9A84C;
      --gold-dark:     #A8872F;
      --surface:       #FBF8F3;
      --surface-2:     #F5F0E8;
      --border:        #DDD5C4;
      --text:          #1C1714;
      --text-muted:    #6B6055;
      --text-faint:    #A89E90;
      --orange:        #C05621;
      --orange-bg:     rgba(192,86,33,.10);
      --radius:        14px;
      --radius-sm:     8px;
      --shadow-md:     0 4px 24px rgba(28,23,20,.10);
    }
    html, body {
      min-height: 100vh;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: var(--surface);
      color: var(--text);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 1rem;
    }
    .err-wrap {
      width: 100%;
      max-width: 480px;
      text-align: center;
    }

    /* Illustration */
    .err-illustration {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 2rem;
      position: relative;
    }
    .err-circle-outer {
      width: 140px; height: 140px; border-radius: 50%;
      background: linear-gradient(135deg, rgba(192,86,33,.08), rgba(201,168,76,.10));
      border: 2px solid var(--border);
      display: flex; align-items: center; justify-content: center;
    }
    .err-circle-inner {
      width: 100px; height: 100px; border-radius: 50%;
      background: linear-gradient(135deg, #8B3A10, #C05621);
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 8px 32px rgba(192,86,33,.30);
    }
    .err-circle-inner svg { color: rgba(255,255,255,.9); }
    .err-badge {
      position: absolute; top: -6px; right: -6px;
      background: var(--orange); color: #fff;
      font-size: 11px; font-weight: 800;
      padding: .2em .6em; border-radius: 20px;
      border: 2px solid var(--surface);
      letter-spacing: .04em;
    }

    /* Code */
    .err-code {
      font-size: clamp(64px, 14vw, 96px);
      font-weight: 900;
      line-height: 1;
      letter-spacing: -.04em;
      background: linear-gradient(135deg, #8B3A10 30%, var(--gold-dark));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: .25rem;
    }
    .err-title {
      font-size: clamp(17px, 3vw, 22px);
      font-weight: 800;
      color: var(--text);
      margin-bottom: .6rem;
    }
    .err-desc {
      font-size: 14px;
      color: var(--text-muted);
      line-height: 1.65;
      max-width: 360px;
      margin: 0 auto 1.75rem;
    }

    /* Divider */
    .err-divider {
      width: 48px; height: 3px;
      background: linear-gradient(90deg, var(--orange), var(--gold-dark));
      border-radius: 2px;
      margin: 1rem auto;
    }

    /* Info box */
    .err-infobox {
      background: var(--orange-bg);
      border: 1px solid rgba(192,86,33,.20);
      border-radius: var(--radius-sm);
      padding: .75rem 1rem;
      margin-bottom: 1.75rem;
      display: flex; align-items: flex-start; gap: .6rem;
      text-align: left;
    }
    .err-infobox svg { flex-shrink: 0; margin-top: .1rem; color: var(--orange); }
    .err-infobox p { font-size: 12.5px; color: var(--orange); font-weight: 600; line-height: 1.5; }

    /* Actions */
    .err-actions { display: flex; gap: .65rem; justify-content: center; flex-wrap: wrap; }
    .err-btn {
      display: inline-flex; align-items: center; gap: .4rem;
      font-size: 13.5px; font-weight: 700;
      padding: .55rem 1.25rem; border-radius: var(--radius-sm);
      cursor: pointer; text-decoration: none;
      border: 1.5px solid transparent;
      transition: all 180ms cubic-bezier(.16,1,.3,1);
    }
    .err-btn-primary {
      background: linear-gradient(135deg, var(--primary), #9B2020);
      color: #fff; border-color: var(--primary-dark);
      box-shadow: 0 2px 10px rgba(123,28,28,.25);
    }
    .err-btn-primary:hover { background: linear-gradient(135deg,#9B2020,var(--primary-dark)); color:#fff; }
    .err-btn-outline {
      background: #fff; color: var(--text-muted);
      border-color: var(--border);
    }
    .err-btn-outline:hover { border-color: var(--primary); color: var(--primary); }

    /* Card */
    .err-card {
      background: #fff; border: 1px solid var(--border);
      border-radius: var(--radius); padding: 2rem 2rem 1.75rem;
      box-shadow: var(--shadow-md);
      position: relative; overflow: hidden;
    }
    .err-card::before {
      content: '';
      position: absolute; top: 0; left: 0; right: 0;
      height: 3px;
      background: linear-gradient(90deg, var(--orange), var(--gold-dark));
    }

    /* Watermark */
    .err-watermark {
      position: absolute; bottom: -20px; right: -20px;
      font-size: 120px; font-weight: 900;
      color: rgba(192,86,33,.04);
      line-height: 1; pointer-events: none;
      user-select: none; letter-spacing: -.04em;
    }

    /* Hint */
    .err-hint {
      margin-top: 1.5rem;
      font-size: 12px; color: var(--text-faint);
    }
    .err-hint code {
      background: var(--surface-2); padding: .1em .4em;
      border-radius: 4px; font-size: 11.5px;
      color: var(--text-muted);
    }
  </style>
</head>
<body>
  <div class="err-wrap">
    <div class="err-card">
      <div class="err-watermark">403</div>

      <div class="err-illustration">
        <div class="err-circle-outer">
          <div class="err-circle-inner">
            <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
          </div>
          <span class="err-badge">403</span>
        </div>
      </div>

      <div class="err-code">403</div>
      <div class="err-title">Akses Ditolak</div>
      <div class="err-divider"></div>
      <p class="err-desc">
        Anda tidak memiliki izin untuk mengakses halaman ini.
        Silakan hubungi administrator jika Anda merasa ini adalah kesalahan.
      </p>

      <div class="err-infobox">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <p>Halaman ini memerlukan hak akses khusus. Pastikan Anda telah masuk dengan akun yang memiliki izin yang sesuai.</p>
      </div>

      <div class="err-actions">
        <a href="<?= $baseUrl ?>" class="err-btn err-btn-primary">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          Kembali ke Beranda
        </a>
        <a href="<?= $baseUrl ?>/auth/login" class="err-btn err-btn-outline">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
          Masuk Ulang
        </a>
      </div>

      <div class="err-hint">
        URL: <code><?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '-') ?></code>
      </div>
    </div>
  </div>
</body>
</html>
