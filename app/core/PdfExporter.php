<?php
/**
 * PdfExporter — Export notulen ke PDF
 * Kop surat diambil dari letterhead_html milik template (Opsi B).
 * Jika template tidak punya letterhead, PDF dicetak tanpa kop.
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
     * Normalisasi konten: EditorJS JSON → HTML. HTML Quill dikembalikan apa adanya.
     */
    public static function normalizeContent(?string $raw): string
    {
        if (empty($raw)) return '<p><em>Belum ada isi notulen.</em></p>';
        $decoded = json_decode($raw, true);
        if ($decoded === null || !isset($decoded['blocks'])) return $raw;
        $html = '';
        foreach ($decoded['blocks'] as $block) {
            $text = htmlspecialchars_decode($block['data']['text'] ?? '');
            $html .= match($block['type']) {
                'header'    => '<h' . ($block['data']['level'] ?? 2) . '>' . $text . '</h' . ($block['data']['level'] ?? 2) . '>',
                'paragraph' => '<p>' . $text . '</p>',
                'list'      => self::renderList($block['data']),
                'checklist' => self::renderChecklist($block['data']),
                'quote'     => '<blockquote style="border-left:3px solid #555;padding-left:12px;color:#555;">' . $text . '</blockquote>',
                'delimiter' => '<hr>',
                default     => '<p>' . $text . '</p>',
            };
        }
        return $html ?: '<p><em>Belum ada isi notulen.</em></p>';
    }

    /**
     * Ganti placeholder {{VAR}} dengan data meeting nyata.
     */
    public static function resolvePlaceholders(string $content, array $meeting, array $participants, string $notulisName): string
    {
        $hariMap = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa',
                    'Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
        $dayName = $hariMap[date('l', strtotime($meeting['start_datetime']))] ?? date('l', strtotime($meeting['start_datetime']));
        $peserta = implode(', ', array_map(fn($p) => htmlspecialchars($p['name']), $participants));

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
        $items = array_map(fn($i) => '<li>' . htmlspecialchars_decode(is_array($i) ? ($i['content'] ?? '') : $i) . '</li>', $data['items'] ?? []);
        return "<{$tag}>" . implode('', $items) . "</{$tag}>";
    }

    private static function renderChecklist(array $data): string
    {
        $html = '<ul style="list-style:none;padding-left:0;">';
        foreach ($data['items'] ?? [] as $item) {
            $check = ($item['checked'] ?? false) ? '&#x2705;' : '&#x2B1C;';
            $html .= '<li>' . $check . ' ' . htmlspecialchars_decode($item['text'] ?? '') . '</li>';
        }
        return $html . '</ul>';
    }

    /**
     * Ambil letterhead_html dari template yang terhubung ke notulen ini.
     * Jika notulen tidak punya template, kembalikan string kosong.
     */
    private static function getLetterhead(array $notulen): string
    {
        $templateId = $notulen['template_id'] ?? null;
        if (!$templateId) return '';
        try {
            $tpl = Database::queryOne(
                "SELECT letterhead_html FROM notulen_templates WHERE id=?",
                [(int)$templateId]
            );
            return $tpl['letterhead_html'] ?? '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    private static function buildHtmlContent(array $meeting, array $notulen, array $participants, array $tindakLanjutList): string
    {
        $appName   = defined('APP_NAME') ? APP_NAME : 'Meeting Management';
        $title     = htmlspecialchars($meeting['title']);
        $printDate = date('d F Y H:i');

        // Kop surat dari template (Opsi B)
        $letterhead = self::getLetterhead($notulen);

        // Notulis
        $notulisName = $notulen['editor_name'] ?? $meeting['creator_name'] ?? '-';

        // Konten notulen + resolve placeholder
        $rawContent  = $notulen['content'] ?? '';
        $notulenHtml = self::normalizeContent($rawContent);
        if (str_contains($notulenHtml, '{{')) {
            $notulenHtml = self::resolvePlaceholders($notulenHtml, $meeting, $participants, $notulisName);
        }

        // Tabel tindak lanjut
        $tlRows = '';
        foreach ($tindakLanjutList as $i => $tl) {
            $no     = $i + 1;
            $desk   = htmlspecialchars($tl['description'] ?? '-');
            $pic    = htmlspecialchars($tl['assigned_name'] ?? '-');
            $dl     = !empty($tl['due_date']) ? date('d M Y', strtotime($tl['due_date'])) : '-';
            $prio   = ucfirst($tl['priority'] ?? '-');
            $status = ucfirst(str_replace('_', ' ', $tl['status'] ?? '-'));
            $tlRows .= "<tr><td>{$no}</td><td>{$desk}</td><td>{$pic}</td><td>{$dl}</td><td>{$prio}</td><td>{$status}</td></tr>";
        }
        $tlSection = $tlRows
            ? "<h3>Tindak Lanjut</h3><table class='tl-table'><thead><tr><th>#</th><th>Deskripsi</th><th>PIC</th><th>Deadline</th><th>Prioritas</th><th>Status</th></tr></thead><tbody>{$tlRows}</tbody></table>"
            : '';

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
    h2 { font-size:13pt; }
    h3 { font-size:12pt; }
    blockquote { border-left:3px solid #555; padding-left:12px; color:#555; margin:8px 0; }
    ul, ol { padding-left:20px; }
    .ql-align-center  { text-align:center; }
    .ql-align-right   { text-align:right; }
    .ql-align-justify { text-align:justify; }
    .tl-table { width:100%; border-collapse:collapse; font-size:10pt; margin-top:8px; }
    .tl-table th { background:#555; color:#fff; padding:6px 8px; text-align:left; }
    .tl-table td { padding:5px 8px; border-bottom:1px solid #e5e7eb; }
    .tl-table tr:nth-child(even) td { background:#f9f9f9; }
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

  {$letterhead}

  {$notulenHtml}

  {$tlSection}

  <div class="footer">Dicetak pada {$printDate} &mdash; {$appName}</div>
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
        $mpdf = new \Mpdf\Mpdf(['margin_top'=>20,'margin_bottom'=>15,'margin_left'=>25,'margin_right'=>25]);
        $mpdf->WriteHTML($html);
        $mpdf->Output($dir . $filename, \Mpdf\Output\Destination::FILE);
        return '/exports/' . $filename;
    }

    private static function exportViaHtml(array $meeting, array $notulen, array $participants, array $tindakLanjutList): string
    {
        return self::buildHtmlContent($meeting, $notulen, $participants, $tindakLanjutList);
    }
}
