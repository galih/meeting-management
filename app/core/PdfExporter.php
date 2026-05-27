<?php
/**
 * PdfExporter — Export notulen ke PDF menggunakan mPDF atau fallback HTML print
 * 
 * Untuk mengaktifkan mPDF: composer require mpdf/mpdf
 * Tanpa composer (shared hosting): gunakan fallback HTML printable
 */
class PdfExporter
{
    /**
     * Export notulen ke PDF
     * Return: string path file PDF atau HTML printable
     */
    public static function export(array $meeting, array $notulen, array $participants, array $tindakLanjutList, array $exportedBy): string
    {
        // Cek apakah mPDF tersedia (via composer)
        if (class_exists('\Mpdf\Mpdf')) {
            return self::exportViaMpdf($meeting, $notulen, $participants, $tindakLanjutList, $exportedBy);
        }
        // Fallback: kirim HTML printable ke browser
        return self::exportViaHtml($meeting, $notulen, $participants, $tindakLanjutList);
    }

    /**
     * Generate konten notulen dari JSON Editor.js ke HTML
     */
    public static function editorJsToHtml(?string $jsonContent): string
    {
        if (empty($jsonContent)) return '<p><em>Belum ada notulen.</em></p>';
        $data   = json_decode($jsonContent, true);
        $blocks = $data['blocks'] ?? [];
        $html   = '';
        foreach ($blocks as $block) {
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

    private static function renderList(array $data): string
    {
        $tag   = ($data['style'] ?? 'unordered') === 'ordered' ? 'ol' : 'ul';
        $items = array_map(fn($i) => '<li>' . htmlspecialchars_decode($i) . '</li>', $data['items'] ?? []);
        return "<{$tag}>" . implode('', $items) . "</{$tag}>";
    }

    private static function renderChecklist(array $data): string
    {
        $html = '<ul style="list-style:none;padding-left:0;">';
        foreach ($data['items'] ?? [] as $item) {
            $check = ($item['checked'] ?? false) ? '✅' : '⬜';
            $text  = htmlspecialchars_decode($item['text'] ?? '');
            $html .= "<li>{$check} {$text}</li>";
        }
        return $html . '</ul>';
    }

    private static function buildHtmlContent(array $meeting, array $notulen, array $participants, array $tindakLanjutList): string
    {
        $appName  = defined('APP_NAME') ? APP_NAME : 'Meeting Management';
        $title    = htmlspecialchars($meeting['title']);
        $location = htmlspecialchars($meeting['location'] ?? '-');
        $start    = date('d F Y H:i', strtotime($meeting['start_datetime']));
        $end      = date('d F Y H:i', strtotime($meeting['end_datetime']));
        $creator  = htmlspecialchars($meeting['creator_name'] ?? '-');
        $printDate = date('d F Y H:i');

        // Peserta
        $pesertaList = implode(', ', array_map(fn($p) => htmlspecialchars($p['name']), $participants));

        // Isi notulen
        $notulenHtml = self::editorJsToHtml($notulen['content'] ?? null);

        // Tindak lanjut rows
        $tlRows = '';
        foreach ($tindakLanjutList as $i => $tl) {
            $no      = $i + 1;
            $desk    = htmlspecialchars($tl['deskripsi']);
            $pic     = htmlspecialchars($tl['assigned_name'] ?? '-');
            $dl      = $tl['deadline'] ? date('d M Y', strtotime($tl['deadline'])) : '-';
            $prio    = ucfirst($tl['priority']);
            $status  = ucfirst(str_replace('_', ' ', $tl['status']));
            $tlRows .= "<tr><td>{$no}</td><td>{$desk}</td><td>{$pic}</td><td>{$dl}</td><td>{$prio}</td><td>{$status}</td></tr>";
        }
        $tlTable = $tlRows
            ? "<table class='tl-table'><thead><tr><th>#</th><th>Deskripsi</th><th>PIC</th><th>Deadline</th><th>Prioritas</th><th>Status</th></tr></thead><tbody>{$tlRows}</tbody></table>"
            : '<p><em>Tidak ada tindak lanjut.</em></p>';

        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Notulen &mdash; {$title}</title>
  <style>
    @page { margin: 20mm; }
    * { box-sizing: border-box; }
    body { font-family: Arial, sans-serif; font-size: 12pt; color: #222; }
    .kop { border-bottom: 3px solid #f76707; padding-bottom: 12px; margin-bottom: 20px; display:flex; align-items:center; gap:16px; }
    .kop-title h1 { margin:0; font-size:18pt; color:#f76707; }
    .kop-title p  { margin:2px 0; font-size:9pt; color:#666; }
    .meta { background:#fff7ed; border:1px solid #fed7aa; border-radius:6px; padding:12px 16px; margin-bottom:20px; font-size:10pt; }
    .meta table { width:100%; border-collapse:collapse; }
    .meta td { padding:3px 8px; }
    .meta .label { color:#6b7280; width:130px; font-weight:600; }
    h2 { font-size:13pt; color:#f76707; border-bottom:1px solid #fed7aa; padding-bottom:4px; margin-top:24px; }
    h3,h4 { font-size:12pt; }
    blockquote { border-left:3px solid #f76707; padding-left:12px; color:#555; margin:8px 0; }
    .tl-table { width:100%; border-collapse:collapse; font-size:10pt; margin-top:8px; }
    .tl-table th { background:#f76707; color:#fff; padding:6px 8px; text-align:left; }
    .tl-table td { padding:5px 8px; border-bottom:1px solid #f3f4f6; }
    .tl-table tr:nth-child(even) td { background:#fff7ed; }
    .ttd { margin-top:48px; display:flex; gap:48px; }
    .ttd-item { text-align:center; }
    .ttd-item .line { border-top:1px solid #222; margin-top:48px; padding-top:4px; font-size:10pt; }
    .footer { font-size:8pt; color:#9ca3af; border-top:1px solid #e5e7eb; margin-top:24px; padding-top:8px; text-align:center; }
    @media print {
      .no-print { display:none; }
      body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
    }
  </style>
</head>
<body>

  <!-- Tombol Print (hilang saat cetak) -->
  <div class="no-print" style="margin-bottom:20px;">
    <button onclick="window.print()" style="background:#f76707;color:#fff;border:none;padding:10px 24px;border-radius:6px;font-size:14px;cursor:pointer;">🖨️ Cetak / Simpan PDF</button>
    <button onclick="window.close()" style="margin-left:8px;padding:10px 24px;border-radius:6px;cursor:pointer;">✕ Tutup</button>
  </div>

  <!-- Kop Surat -->
  <div class="kop">
    <div class="kop-title">
      <h1>{$appName}</h1>
      <p>Notulen Rapat Resmi &mdash; Dicetak {$printDate}</p>
    </div>
  </div>

  <!-- Info Meeting -->
  <div class="meta">
    <table>
      <tr><td class="label">Judul Meeting</td><td>: <strong>{$title}</strong></td></tr>
      <tr><td class="label">Tanggal</td><td>: {$start}</td></tr>
      <tr><td class="label">Selesai</td><td>: {$end}</td></tr>
      <tr><td class="label">Lokasi</td><td>: {$location}</td></tr>
      <tr><td class="label">Dibuat oleh</td><td>: {$creator}</td></tr>
      <tr><td class="label">Peserta</td><td>: {$pesertaList}</td></tr>
    </table>
  </div>

  <!-- Isi Notulen -->
  <h2>📝 Isi Notulen</h2>
  {$notulenHtml}

  <!-- Tindak Lanjut -->
  <h2>✅ Tindak Lanjut</h2>
  {$tlTable}

  <!-- Kolom TTD -->
  <div class="ttd">
    <div class="ttd-item">
      <div class="line">Notulis</div>
    </div>
    <div class="ttd-item">
      <div class="line">Pimpinan Rapat</div>
    </div>
  </div>

  <div class="footer">
    Dokumen ini dibuat otomatis oleh {$appName} &mdash; {$printDate}
  </div>

</body>
</html>
HTML;
    }

    private static function exportViaMpdf(array $meeting, array $notulen, array $participants, array $tindakLanjutList, array $exportedBy): string
    {
        $html     = self::buildHtmlContent($meeting, $notulen, $participants, $tindakLanjutList);
        $filename = 'notulen-' . $meeting['id'] . '-' . date('Ymd') . '.pdf';
        $dir      = ROOT_PATH . '/public/exports/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $mpdf = new \Mpdf\Mpdf(['margin_top' => 15, 'margin_bottom' => 15]);
        $mpdf->WriteHTML($html);
        $mpdf->Output($dir . $filename, \Mpdf\Output\Destination::FILE);
        return '/exports/' . $filename;
    }

    private static function exportViaHtml(array $meeting, array $notulen, array $participants, array $tindakLanjutList): string
    {
        // Simpan HTML sementara & buka di tab baru untuk di-print/save PDF
        return self::buildHtmlContent($meeting, $notulen, $participants, $tindakLanjutList);
    }
}
