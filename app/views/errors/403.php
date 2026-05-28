<?php http_response_code(403); ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>403 &mdash; <?= defined('APP_NAME') ? APP_NAME : 'Wicara' ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css">
  <style>
    :root { --tblr-primary: #f76707; }
    .error-number { font-size: 8rem; font-weight: 700; line-height: 1; color: #f76707; }
  </style>
</head>
<body class="d-flex flex-column antialiased">
  <div class="page page-center" style="min-height:100vh;">
    <div class="container-tight py-6">
      <div class="text-center">
        <div class="error-number mb-2">403</div>
        <h2 class="h1 mb-2">Akses Ditolak</h2>
        <p class="text-muted fs-h3 mb-4">
          Anda tidak memiliki izin untuk mengakses halaman ini.
        </p>
        <div class="d-flex gap-2 justify-content-center">
          <a href="javascript:history.back()" class="btn btn-outline-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="15 18 9 12 15 6"/>
            </svg>
            Kembali
          </a>
          <a href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>/" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
              <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            Dashboard
          </a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
