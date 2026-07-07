<?php
$baseUrl     = rtrim(BASE_URL, '/');
$csrfToken   = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES);
$statusMap   = [
    'pending'     => ['label' => 'Menunggu',    'color' => 'secondary', 'bg' => '#f0f0f0',  'text' => '#555'],
    'in_progress' => ['label' => 'Berlangsung', 'color' => 'blue',      'bg' => '#e0f4ff',  'text' => '#0284c7'],
    'done'        => ['label' => 'Selesai',     'color' => 'green',     'bg' => '#e6faf0',  'text' => '#16a34a'],
    'cancelled'   => ['label' => 'Dibatalkan',  'color' => 'red',       'bg' => '#fff0f0',  'text' => '#dc2626'],
];
$priorityMap = [
    'low'    => ['label' => 'Rendah', 'color' => 'green',  'bg' => '#e6faf0', 'text' => '#16a34a'],
    'medium' => ['label' => 'Sedang', 'color' => 'orange', 'bg' => 