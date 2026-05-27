<?php header('HTTP/1.1 404 Not Found'); ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>404 &mdash; <?= APP_NAME ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css">
  <style>:root{--tblr-primary:#f76707;}</style>
</head>
<body class="d-flex flex-column page page-center">
  <div class="container-tight py-6 text-center">
    <div class="display-1 fw-bold" style="color:#f76707;">404</div>
    <h2>Halaman Tidak Ditemukan</h2>
    <p class="text-muted">Halaman yang Anda cari tidak ada atau telah dipindahkan.</p>
    <a href="/" class="btn btn-primary">Kembali ke Dashboard</a>
  </div>
</body>
</html>
