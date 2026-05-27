<?php
$isLayout = !empty($pageTitle);
if (!$isLayout) {
    header('HTTP/1.1 403 Forbidden');
}
?>
<div class="page page-center" style="min-height:60vh;">
  <div class="container-tight py-4 text-center">
    <div class="display-1 fw-bold mb-2" style="color:#f76707;">403</div>
    <h2 class="h2">Akses Ditolak</h2>
    <p class="text-muted">Anda tidak memiliki izin untuk mengakses halaman ini.</p>
    <a href="/" class="btn btn-primary mt-3">Kembali ke Dashboard</a>
  </div>
</div>
