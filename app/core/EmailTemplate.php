<?php
/**
 * EmailTemplate — template HTML email yang konsisten
 */
class EmailTemplate
{
    private static function wrap(string $content, string $preheader = ''): string
    {
        $appName = defined('APP_NAME') ? APP_NAME : 'Meeting Management App';
        $year    = date('Y');
        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body { margin:0; padding:0; background:#f4f6fb; font-family:Arial,sans-serif; }
    .wrapper { max-width:600px; margin:32px auto; background:#fff;
               border-radius:12px; overflow:hidden;
               box-shadow:0 2px 16px rgba(0,0,0,.08); }
    .header  { background:#f76707; padding:28px 32px; text-align:center; }
    .header h1 { color:#fff; margin:0; font-size:22px; }
    .body    { padding:32px; color:#374151; font-size:15px; line-height:1.7; }
    .body h2 { color:#f76707; font-size:18px; margin-top:0; }
    .btn     { display:inline-block; background:#f76707; color:#fff!important;
               padding:12px 28px; border-radius:8px; text-decoration:none;
               font-weight:600; font-size:15px; margin:16px 0; }
    .info-box { background:#fff7ed; border-left:4px solid #f76707;
                padding:16px 20px; border-radius:0 8px 8px 0; margin:16px 0; }
    .info-row { display:flex; gap:8px; margin:6px 0; font-size:14px; }
    .info-label { color:#6b7280; min-width:110px; }
    .info-value { color:#111827; font-weight:500; }
    .footer  { background:#f9fafb; padding:20px 32px; text-align:center;
               font-size:12px; color:#9ca3af; border-top:1px solid #f3f4f6; }
    .badge   { display:inline-block; padding:3px 10px; border-radius:20px;
               font-size:12px; font-weight:600; }
    .badge-orange { background:#fff7ed; color:#f76707; }
    .badge-green  { background:#f0fdf4; color:#16a34a; }
    .badge-red    { background:#fef2f2; color:#dc2626; }
    .badge-blue   { background:#eff6ff; color:#2563eb; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <h1>&#128197; {$appName}</h1>
    </div>
    <div class="body">{$content}</div>
    <div class="footer">
      &copy; {$year} {$appName} &mdash; Email otomatis, jangan dibalas.
    </div>
  </div>
</body>
</html>
HTML;
    }

    public static function invitation(array $meeting, array $user): string
    {
        $appUrl   = defined('APP_URL') ? APP_URL : '';
        $start    = date('l, d F Y \p\u\k\u\l H:i', strtotime($meeting['start_datetime']));
        $end      = date('H:i', strtotime($meeting['end_datetime']));
        $location = htmlspecialchars($meeting['location'] ?? 'Belum ditentukan');
        $title    = htmlspecialchars($meeting['title']);
        $name     = htmlspecialchars($user['name']);
        $desc     = $meeting['description'] ? '<p>' . nl2br(htmlspecialchars($meeting['description'])) . '</p>' : '';
        $link     = "{$appUrl}/meetings/{$meeting['id']}";

        return self::wrap(<<<HTML
<h2>Undangan Meeting</h2>
<p>Halo <strong>{$name}</strong>,</p>
<p>Anda diundang untuk menghadiri meeting berikut:</p>
<div class="info-box">
  <div class="info-row"><span class="info-label">&#128203; Judul</span><span class="info-value">{$title}</span></div>
  <div class="info-row"><span class="info-label">&#128336; Waktu</span><span class="info-value">{$start} &ndash; {$end}</span></div>
  <div class="info-row"><span class="info-label">&#128205; Lokasi</span><span class="info-value">{$location}</span></div>
</div>
{$desc}
<a href="{$link}" class="btn">Lihat Detail Meeting &rarr;</a>
<p style="font-size:13px;color:#6b7280;">Harap konfirmasi kehadiran Anda melalui aplikasi.</p>
HTML);
    }

    public static function deadlineReminder(array $tl, array $user): string
    {
        $appUrl   = defined('APP_URL') ? APP_URL : '';
        $deadline = date('d F Y', strtotime($tl['deadline']));

        // PHP 7.4 compat: ganti match() dengan array lookup
        $priorityMap = [
            'high'   => '<span class="badge badge-red">&#128308; Tinggi</span>',
            'medium' => '<span class="badge badge-orange">&#128992; Sedang</span>',
        ];
        $priority = $priorityMap[$tl['priority']] ?? '<span class="badge badge-green">&#128994; Rendah</span>';

        $name    = htmlspecialchars($user['name']);
        $desk    = htmlspecialchars($tl['deskripsi']);
        $meeting = htmlspecialchars($tl['meeting_title'] ?? 'Meeting');
        $link    = "{$appUrl}/tindak-lanjut";

        return self::wrap(<<<HTML
<h2>&#9200; Reminder Deadline Tindak Lanjut</h2>
<p>Halo <strong>{$name}</strong>,</p>
<p>Tindak lanjut berikut memiliki <strong>deadline besok</strong>:</p>
<div class="info-box">
  <div class="info-row"><span class="info-label">&#128221; Tugas</span><span class="info-value">{$desk}</span></div>
  <div class="info-row"><span class="info-label">&#128197; Meeting</span><span class="info-value">{$meeting}</span></div>
  <div class="info-row"><span class="info-label">&#9203; Deadline</span><span class="info-value">{$deadline}</span></div>
  <div class="info-row"><span class="info-label">&#128678; Prioritas</span><span class="info-value">{$priority}</span></div>
</div>
<a href="{$link}" class="btn">Update Status Sekarang &rarr;</a>
HTML);
    }

    public static function meetingSummary(array $meeting, array $notulenText, array $tindakLanjutList, array $user): string
    {
        $appUrl  = defined('APP_URL') ? APP_URL : '';
        $title   = htmlspecialchars($meeting['title']);
        $name    = htmlspecialchars($user['name']);
        $tanggal = date('d F Y', strtotime($meeting['start_datetime']));
        $link    = "{$appUrl}/notulen/{$meeting['id']}";

        $tlRows = '';
        foreach ($tindakLanjutList as $tl) {
            $desk     = htmlspecialchars($tl['deskripsi']);
            $assigned = htmlspecialchars($tl['assigned_name'] ?? '-');
            $dl       = $tl['deadline'] ? date('d M Y', strtotime($tl['deadline'])) : '-';
            $tlRows  .= "<tr><td style='padding:6px 8px;border-bottom:1px solid #f3f4f6;'>{$desk}</td>"
                      . "<td style='padding:6px 8px;border-bottom:1px solid #f3f4f6;color:#6b7280;'>{$assigned}</td>"
                      . "<td style='padding:6px 8px;border-bottom:1px solid #f3f4f6;color:#6b7280;'>{$dl}</td></tr>";
        }
        $tlSection = $tlRows ? <<<HTML
<h3 style="font-size:15px;color:#374151;margin:24px 0 8px;">&#9989; Tindak Lanjut</h3>
<table width="100%" style="border-collapse:collapse;font-size:13px;">
  <thead><tr style="background:#fff7ed;">
    <th style="padding:8px;text-align:left;">Deskripsi</th>
    <th style="padding:8px;text-align:left;">PIC</th>
    <th style="padding:8px;text-align:left;">Deadline</th>
  </tr></thead>
  <tbody>{$tlRows}</tbody>
</table>
HTML : '<p style="color:#6b7280;">Tidak ada tindak lanjut.</p>';

        return self::wrap(<<<HTML
<h2>&#128203; Ringkasan Meeting</h2>
<p>Halo <strong>{$name}</strong>,</p>
<p>Berikut ringkasan meeting <strong>{$title}</strong> tanggal <strong>{$tanggal}</strong>:</p>
{$tlSection}
<br>
<a href="{$link}" class="btn">Baca Notulen Lengkap &rarr;</a>
HTML);
    }
}
