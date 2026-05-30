<?php
/**
 * PdfExporter — Export notulen ke PDF menggunakan mPDF atau fallback HTML print
 */
class PdfExporter
{
    public static function export(array $meeting, array $notulen, array $participants, array $tindakLanjutList, array $exportedBy): string
    {
        if (class_exists('\Mpdf\Mpdf')) {
            return self::exportViaMpdf($meeting, $notulen, $participants, $tindakLanjutList, $exportedBy);
        }
        return self::exportViaHtml($meeting, $notulen, $participants, $tindakLanjutList);
    }

    /**
     * Normalisasi konten: jika masih format EditorJS JSON, konversi ke HTML.
     * Jika sudah HTML (Quill), kembalikan apa adanya.
     */
    public static function normalizeContent(?string $raw): string
    {
        if (empty($raw)) return '<p><em>Belum ada isi notulen.</em></p>';

        $decoded = json_decode($raw, true);
        if ($decoded === null || !isset($decoded['blocks'])) {
            return $raw;
        }

        $html = '';
        foreach ($decoded['blocks'] as $block) {
            $text = htmlspecialchars_decode($block['data']['text'] ?? '');
            $html .= match($block['type']) {
                'header'    => '<h' . ($block['data']['level'] ?? 2) . '>' . $text . '</h' . ($block['data']['level'] ?? 2) . '>',
                'paragraph' => '<p>' . $text . '</p>',
                'list'      => self::renderList($block['data']),
                'checklist' => self::renderChecklist($block['data']),
                'quote'     => '<blockquote style="border-left:3px solid #f76707;padding-left:12px;color:#555;">' . $text . '</blockquote>',
                'delimiter' => '<hr>',
                default     => '<p>' . $text . '</p>',
            };
        }
        return $html ?: '<p><em>Belum ada isi notulen.</em></p>';
    }

    /**
     * Ganti placeholder {{VAR}} di konten template dengan data meeting nyata.
     */
    public static function resolvePlaceholders(string $content, array $meeting, array $participants, string $notulisName): string
    {
        $hari = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa',
                 'Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
        $dayName  = $hari[date('l', strtotime($meeting['start_datetime']))] ?? date('l', strtotime($meeting['start_datetime']));
        $peserta  = implode(', ', array_map(fn($p) => htmlspecialchars($p['name']), $participants));

        $map = [
            '{{MEETING_TITLE}}'    => htmlspecialchars($meeting['title'] ?? ''),
            '{{MEETING_DAY}}'      => $dayName,
            '{{MEETING_DATE}}'     => date('d F Y', strtotime($meeting['start_datetime'])),
            '{{MEETING_START}}'    => date('H:i', strtotime($meeting['start_datetime'])),
            '{{MEETING_END}}'      => date('H:i', strtotime($meeting['end_datetime'])),
            '{{MEETING_LOCATION}}' => htmlspecialchars($meeting['location'] ?? '-'),
            '{{NOTULIS_NAME}}'     => htmlspecialchars($notulisName),
            '{{PARTICIPANTS}}'     => $peserta,
        ];

        return str_replace(array_keys($map), array_values($map), $content);
    }

    private static function renderList(array $data): string
    {
        $tag   = ($data['style'] ?? 'unordered') === 'ordered' ? 'ol' : 'ul';
        $items = array_map(function($i) {
            $text = is_array($i) ? ($i['content'] ?? '') : $i;
            return '<li>' . htmlspecialchars_decode($text) . '</li>';
        }, $data['items'] ?? []);
        return "<{$tag}>" . implode('', $items) . "</{$tag}>";
    }

    private static function renderChecklist(array $data): string
    {
        $html = '<ul style="list-style:none;padding-left:0;">';
        foreach ($data['items'] ?? [] as $item) {
            $check = ($item['checked'] ?? false) ? '&#x2705;' : '&#x2B1C;';
            $text  = htmlspecialchars_decode($item['text'] ?? '');
            $html .= "<li>{$check} {$text}</li>";
        }
        return $html . '</ul>';
    }

    private static function buildHtmlContent(array $meeting, array $notulen, array $participants, array $tindakLanjutList): string
    {
        $appName   = defined('APP_NAME') ? APP_NAME : 'Meeting Management';
        $title     = htmlspecialchars($meeting['title']);
        $printDate = date('d F Y H:i');

        // Ambil logo & data instansi dari settings
        $settings      = self::getSettings();
        $instansiNama  = $settings['instansi_nama']  ?? $appName;
        $instansiAlamat= $settings['instansi_alamat'] ?? '';
        $instansiTlp   = $settings['instansi_telepon'] ?? '';
        $logoPath      = !empty($settings['logo']) ? ROOT_PATH . '/uploads/' . $settings['logo'] : null;
        $logoHtml      = '';
        if ($logoPath && file_exists($logoPath)) {
            $mime     = mime_content_type($logoPath);
            $b64      = base64_encode(file_get_contents($logoPath));
            $logoHtml = "<img src=\"data:{$mime};base64,{$b64}\" style=\"height:72px;max-width:120px;object-fit:contain;\" alt=\"Logo\">";
        }

        // Notulis = user yang terakhir update notulen, fallback ke creator meeting
        $notulisName = $notulen['editor_name'] ?? $meeting['creator_name'] ?? '-';
        if (empty($notulisName) || $notulisName === '-') {
            $notulisName = $meeting['creator_name'] ?? '-';
        }

        // Cek apakah konten notulen adalah template placeholder atau HTML biasa
        $rawContent  = $notulen['content'] ?? '';
        $notulenHtml = self::normalizeContent($rawContent);
        // Resolve placeholder jika ada {{VAR}} di konten
        if (str_contains($notulenHtml, '{{')) {
            $notulenHtml = self::resolvePlaceholders($notulenHtml, $meeting, $participants, $notulisName);
        }

        $tlRows = '';
        foreach ($tindakLanjutList as $i => $tl) {
            $no     = $i + 1;
            $desk   = htmlspecialchars($tl['description'] ?? '-');
            $pic    = htmlspecialchars($tl['assigned_name'] ?? '-');
            $dl     = !empty($tl['due_date']) ? date('d M Y', strtotime($tl['due_date'])) : '-';
            $prio   = ucfirst($tl['priority']   ?? '-');
            $status = ucfirst(str_replace('_', ' ', $tl['status'] ?? '-'));
            $tlRows .= "<tr><td>{$no}</td><td>{$desk}</td><td>{$pic}</td><td>{$dl}</td><td>{$prio}</td><td>{$status}</td></tr>";
        }
        $tlTable = $tlRows
            ? "<table class='tl-table'><thead><tr><th>#</th><th>Deskripsi</th><th>PIC</th><th>Deadline</th><th>Prioritas</th><th>Status</th></tr></thead><tbody>{$tlRows}</tbody></table>"
            : '<p><em>Tidak ada tindak lanjut.</em></p>';

        // Kop surat HTML
        $kopHtml = self::buildKopHtml($logoHtml, $instansiNama, $instansiAlamat, $instansiTlp);

        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Notulen &mdash; {$title}</title>
  <style>
    @page { margin: 20mm 25mm; }
    * { box-sizing: border-box; }
    body { font-family: Arial, sans-serif; font-size: 12pt; color: #222; }
    /* Kop Surat */
    .kop-surat { display:table; width:100%; border-bottom:3px double #222; padding-bottom:10px; margin-bottom:4px; }
    .kop-logo  { display:table-cell; width:90px; vertical-align:middle; }
    .kop-info  { display:table-cell; vertical-align:middle; text-align:center; padding:0 8px; }
    .kop-info .nama-instansi { font-size:15pt; font-weight:bold; text-transform:uppercase; margin:0; }
    .kop-info .alamat        { font-size:8.5pt; color:#333; margin:2px 0 0 0; }
    .kop-spacer { height:8px; }
    /* Tabel Info Rapat */
    .info-table { width:100%; border-collapse:collapse; margin-bottom:16px; font-size:11pt; }
    .info-table td { padding:3px 6px; vertical-align:top; }
    .info-table td:first-child { width:175px; }
    .info-table td:nth-child(2) { width:14px; }
    /* Konten */
    h2 { font-size:13pt; text-align:center; text-transform:uppercase; }
    h3 { font-size:12pt; }
    blockquote { border-left:3px solid #555; padding-left:12px; color:#555; margin:8px 0; }
    ul, ol { padding-left: 20px; }
    .ql-align-center  { text-align:center; }
    .ql-align-right   { text-align:right; }
    .ql-align-justify { text-align:justify; }
    /* Tindak Lanjut */
    .tl-table { width:100%; border-collapse:collapse; font-size:10pt; margin-top:8px; }
    .tl-table th { background:#555; color:#fff; padding:6px 8px; text-align:left; }
    .tl-table td { padding:5px 8px; border-bottom:1px solid #e5e7eb; }
    .tl-table tr:nth-child(even) td { background:#f9f9f9; }
    /* TTD */
    .ttd { margin-top:48px; }
    .ttd table { width:100%; }
    .ttd td { text-align:center; padding:0 16px; }
    .ttd .garis { margin-top:48px; border-top:1px solid #222; padding-top:4px; font-size:10pt; }
    /* Footer */
    .footer { font-size:8pt; color:#9ca3af; border-top:1px solid #e5e7eb; margin-top:24px; padding-top:8px; text-align:center; }
    @media print {
      .no-print { display:none; }
      body { print-color-adjust:exact; -webkit-print-color-adjust:exact; }
    }
  </style>
</head>
<body>
  <div class="no-print" style="margin-bottom:20px;">
    <button onclick="window.print()" style="background:#333;color:#fff;border:none;padding:10px 24px;border-radius:6px;font-size:14px;cursor:pointer;">&#x1F5A8; Cetak / Simpan PDF</button>
    <button onclick="window.close()" style="margin-left:8px;padding:10px 24px;border-radius:6px;cursor:pointer;">&times; Tutup</button>
  </div>

  {$kopHtml}
  <div class="kop-spacer"></div>

  {$notulenHtml}

  <?php if (!empty($tindakLanjutList)): ?>
  <h3>Tindak Lanjut</h3>
  {$tlTable}
  <?php endif; ?>

  <div class="footer">
    Dicetak pada {$printDate} &mdash; {$appName}
  </div>
</body>
</html>
HTML;
    }

    /**
     * Bangun blok kop surat HTML.
     * Jika tidak ada logo/info instansi, kembalikan kop minimal.
     */
    private static function buildKopHtml(string $logoHtml, string $nama, string $alamat, string $telepon): string
    {
        $alamatBaris = trim($alamat . ($telepon ? ' | Telp. ' . htmlspecialchars($telepon) : ''));
        return <<<KOP
<div class="kop-surat">
  <div class="kop-logo">{$logoHtml}</div>
  <div class="kop-info">
    <p class="nama-instansi">{$nama}</p>
    <p class="alamat">{$alamatBaris}</p>
  </div>
  <div class="kop-logo"></div>
</div>
KOP;
    }

    /**
     * Ambil semua settings dari DB (key-value).
     */
    private static function getSettings(): array
    {
        try {
            $rows = Database::query("SELECT setting_key, setting_value FROM settings");
            $out  = [];
            foreach ($rows as $r) $out[$r['setting_key']] = $r['setting_value'];
            return $out;
        } catch (\Throwable $e) {
            return [];
        }
    }

    private static function exportViaMpdf(array $meeting, array $notulen, array $participants, array $tindakLanjutList, array $exportedBy): string
    {
        $html     = self::buildHtmlContent($meeting, $notulen, $participants, $tindakLanjutList);
        $filename = 'notulen-' . $meeting['id'] . '-' . date('Ymd') . '.pdf';
        $dir      = ROOT_PATH . '/public/exports/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $mpdf = new \Mpdf\Mpdf(['margin_top' => 20, 'margin_bottom' => 15, 'margin_left' => 25, 'margin_right' => 25]);
        $mpdf->WriteHTML($html);
        $mpdf->Output($dir . $filename, \Mpdf\Output\Destination::FILE);
        return '/exports/' . $filename;
    }

    private static function exportViaHtml(array $meeting, array $notulen, array $participants, array $tindakLanjutList): string
    {
        return self::buildHtmlContent($meeting, $notulen, $participants, $tindakLanjutList);
    }
}
