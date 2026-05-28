<?php
/**
 * DocxExporter — generate file .docx dari notulen tanpa library eksternal.
 * Menggunakan format Office Open XML (OOXML) minimal yang bisa dibuka
 * di Microsoft Word, LibreOffice, dan Google Docs.
 */
class DocxExporter
{
    public static function export(
        array $meeting,
        array $notulen,
        array $participants,
        array $tindakLanjutList,
        array $user
    ): void {
        $title    = $meeting['title'] ?? 'Notulen';
        $filename = 'notulen-' . ($meeting['id'] ?? 0) . '-' . date('Ymd') . '.docx';
        $content  = $notulen['content'] ?? '';

        // ── Konversi HTML sederhana ke paragraf OOXML ──────────────────────────
        $bodyXml = self::htmlToOoxml($content);

        // ── Header dokumen ─────────────────────────────────────────────────────
        $headerRows = '';
        $fields = [
            'Judul Kegiatan' => htmlspecialchars($meeting['title'] ?? '-'),
            'Departemen'     => htmlspecialchars($meeting['dept_name'] ?? '-'),
            'Lokasi'         => htmlspecialchars($meeting['location'] ?? '-'),
            'Tanggal Mulai'  => !empty($meeting['start_datetime'])
                                  ? date('d F Y H:i', strtotime($meeting['start_datetime']))
                                  : '-',
            'Tanggal Selesai'=> !empty($meeting['end_datetime'])
                                  ? date('d F Y H:i', strtotime($meeting['end_datetime']))
                                  : '-',
            'Status'         => ucfirst($meeting['status'] ?? '-'),
        ];
        foreach ($fields as $label => $value) {
            $headerRows .= self::tableRow(
                self::tableCell($label, true),
                self::tableCell($value)
            );
        }

        // ── Tabel peserta ──────────────────────────────────────────────────────
        $participantRows = self::tableRow(
            self::tableCell('Nama', true),
            self::tableCell('Status', true)
        );
        foreach ($participants as $p) {
            $participantRows .= self::tableRow(
                self::tableCell(htmlspecialchars($p['name'] ?? '-')),
                self::tableCell(ucfirst($p['status'] ?? '-'))
            );
        }

        // ── Tabel tindak lanjut ────────────────────────────────────────────────
        $tlRows = self::tableRow(
            self::tableCell('Deskripsi', true),
            self::tableCell('PIC', true),
            self::tableCell('Deadline', true),
            self::tableCell('Prioritas', true),
            self::tableCell('Status', true)
        );
        foreach ($tindakLanjutList as $tl) {
            $tlRows .= self::tableRow(
                self::tableCell(htmlspecialchars($tl['description'] ?? '-')),
                self::tableCell(htmlspecialchars($tl['assigned_name'] ?? '-')),
                self::tableCell(!empty($tl['due_date']) ? date('d M Y', strtotime($tl['due_date'])) : '-'),
                self::tableCell(ucfirst($tl['priority'] ?? '-')),
                self::tableCell(ucfirst(str_replace('_', ' ', $tl['status'] ?? '-')))
            );
        }

        // ── Rakit dokumen ──────────────────────────────────────────────────────
        $docXml = self::buildDocXml(
            $title,
            $headerRows,
            empty($participants) ? '<w:p><w:r><w:t>-</w:t></w:r></w:p>' : null,
            $participantRows,
            $bodyXml,
            empty($tindakLanjutList) ? '<w:p><w:r><w:t>Belum ada tindak lanjut.</w:t></w:r></w:p>' : null,
            $tlRows,
            htmlspecialchars($user['name'] ?? '-'),
            date('d F Y H:i')
        );

        // ── Buat file ZIP (.docx) ──────────────────────────────────────────────
        $zip = new ZipArchive();
        $tmp = tempnam(sys_get_temp_dir(), 'docx_');
        if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
            http_response_code(500);
            die('Gagal membuat file DOCX.');
        }

        $zip->addFromString('[Content_Types].xml',     self::contentTypes());
        $zip->addFromString('_rels/.rels',              self::rootRels());
        $zip->addFromString('word/document.xml',        $docXml);
        $zip->addFromString('word/_rels/document.xml.rels', self::documentRels());
        $zip->addFromString('word/settings.xml',        self::settings());
        $zip->addFromString('word/styles.xml',          self::styles());
        $zip->close();

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmp));
        header('Cache-Control: no-store');
        readfile($tmp);
        unlink($tmp);
        exit;
    }

    // ── HTML → OOXML (paragraf sederhana) ─────────────────────────────────────
    private static function htmlToOoxml(string $html): string
    {
        if (trim($html) === '') {
            return '<w:p><w:r><w:t>Belum ada isi notulen.</w:t></w:r></w:p>';
        }

        // Strip tag style/script
        $html = preg_replace('/<(style|script)[^>]*>.*?<\/\1>/si', '', $html);

        // Pecah per blok heading / paragraf / list
        $lines = [];

        // Heading
        $html = preg_replace_callback('/<h([2-6])[^>]*>(.*?)<\/h\1>/si', function ($m) use (&$lines) {
            $level = (int)$m[1];
            $text  = strip_tags($m[2]);
            $style = $level <= 2 ? 'Heading2' : 'Heading3';
            return "%%HEADING:{$style}:" . htmlspecialchars($text, ENT_XML1) . "%%\n";
        }, $html);

        // List item
        $html = preg_replace_callback('/<li[^>]*>(.*?)<\/li>/si', function ($m) {
            $text = strip_tags($m[1]);
            return '%%LI:' . htmlspecialchars($text, ENT_XML1) . "%%\n";
        }, $html);

        // br → newline
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);

        // Paragraf — tangkap formatting bold/italic
        $html = preg_replace_callback('/<p[^>]*>(.*?)<\/p>/si', function ($m) {
            $inner = $m[1];
            // bold
            $inner = preg_replace_callback('/<(strong|b)[^>]*>(.*?)<\/\1>/si', function ($mm) {
                $t = htmlspecialchars(strip_tags($mm[2]), ENT_XML1);
                return "%%BOLD:{$t}%%";
            }, $inner);
            // italic
            $inner = preg_replace_callback('/<(em|i)[^>]*>(.*?)<\/\1>/si', function ($mm) {
                $t = htmlspecialchars(strip_tags($mm[2]), ENT_XML1);
                return "%%ITALIC:{$t}%%";
            }, $inner);
            $inner = strip_tags($inner);
            return '%%P:' . trim($inner) . "%%\n";
        }, $html);

        // Sisa teks biasa
        $html = strip_tags($html);

        // Rakit OOXML
        $xml = '';
        $rawLines = explode("\n", $html);
        foreach ($rawLines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            if (preg_match('/^%%HEADING:(\w+):(.*)%%$/', $line, $hm)) {
                $xml .= '<w:p><w:pPr><w:pStyle w:val="' . $hm[1] . '"/></w:pPr>'
                      . '<w:r><w:t xml:space="preserve">' . $hm[2] . '</w:t></w:r></w:p>';
            } elseif (preg_match('/^%%LI:(.*)%%$/', $line, $lm)) {
                $xml .= '<w:p><w:pPr><w:numPr><w:ilvl w:val="0"/><w:numId w:val="1"/></w:numPr></w:pPr>'
                      . '<w:r><w:t xml:space="preserve">' . $lm[1] . '</w:t></w:r></w:p>';
            } elseif (preg_match('/^%%P:(.*)%%$/', $line, $pm)) {
                $xml .= self::buildParagraph($pm[1]);
            } else {
                $safe = htmlspecialchars($line, ENT_XML1);
                $xml .= '<w:p><w:r><w:t xml:space="preserve">' . $safe . '</w:t></w:r></w:p>';
            }
        }

        return $xml ?: '<w:p><w:r><w:t>Belum ada isi notulen.</w:t></w:r></w:p>';
    }

    private static function buildParagraph(string $inner): string
    {
        // Pecah token bold/italic
        $parts = preg_split('/(%%(?:BOLD|ITALIC):[^%]*%%)/U', $inner, -1, PREG_SPLIT_DELIM_CAPTURE);
        $runs  = '';
        foreach ($parts as $part) {
            if (preg_match('/^%%BOLD:(.*)%%$/', $part, $m)) {
                $runs .= '<w:r><w:rPr><w:b/></w:rPr><w:t xml:space="preserve">' . $m[1] . '</w:t></w:r>';
            } elseif (preg_match('/^%%ITALIC:(.*)%%$/', $part, $m)) {
                $runs .= '<w:r><w:rPr><w:i/></w:rPr><w:t xml:space="preserve">' . $m[1] . '</w:t></w:r>';
            } elseif ($part !== '') {
                $safe  = htmlspecialchars($part, ENT_XML1);
                $runs .= '<w:r><w:t xml:space="preserve">' . $safe . '</w:t></w:r>';
            }
        }
        return $runs ? '<w:p>' . $runs . '</w:p>' : '<w:p/>';
    }

    // ── Tabel helpers ──────────────────────────────────────────────────────────
    private static function tableRow(string ...$cells): string
    {
        return '<w:tr>' . implode('', $cells) . '</w:tr>';
    }

    private static function tableCell(string $text, bool $bold = false): string
    {
        $rpr  = $bold ? '<w:rPr><w:b/></w:rPr>' : '';
        $shd  = $bold ? '<w:shd w:val="clear" w:color="auto" w:fill="E8EDF2"/>' : '';
        return '<w:tc><w:tcPr><w:tcBorders>'
             . '<w:top w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>'
             . '<w:left w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>'
             . '<w:bottom w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>'
             . '<w:right w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>'
             . '</w:tcBorders>' . $shd . '</w:tcPr>'
             . '<w:p><w:r>' . $rpr . '<w:t xml:space="preserve">' . $text . '</w:t></w:r></w:p>'
             . '</w:tc>';
    }

    // ── Rakit document.xml ────────────────────────────────────────────────────
    private static function buildDocXml(
        string $title,
        string $headerRows,
        ?string $noParticipants,
        string $participantRows,
        string $bodyXml,
        ?string $noTl,
        string $tlRows,
        string $exportedBy,
        string $exportedAt
    ): string {
        $participantSection = $noParticipants
            ? $noParticipants
            : '<w:tbl><w:tblPr><w:tblStyle w:val="TableGrid"/>'
              . '<w:tblW w:w="0" w:type="auto"/>'
              . '<w:tblLook w:val="04A0"/></w:tblPr>'
              . $participantRows . '</w:tbl>';

        $tlSection = $noTl
            ? $noTl
            : '<w:tbl><w:tblPr><w:tblStyle w:val="TableGrid"/>'
              . '<w:tblW w:w="0" w:type="auto"/>'
              . '<w:tblLook w:val="04A0"/></w:tblPr>'
              . $tlRows . '</w:tbl>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
             . '<w:document xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas"'
             . ' xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006"'
             . ' xmlns:o="urn:schemas-microsoft-com:office:office"'
             . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"'
             . ' xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math"'
             . ' xmlns:v="urn:schemas-microsoft-com:vml"'
             . ' xmlns:wp14="http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing"'
             . ' xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"'
             . ' xmlns:w10="urn:schemas-microsoft-com:office:word"'
             . ' xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"'
             . ' xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml"'
             . ' xmlns:wpg="http://schemas.microsoft.com/office/word/2010/wordprocessingGroup"'
             . ' xmlns:wpi="http://schemas.microsoft.com/office/word/2010/wordprocessingInk"'
             . ' xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml"'
             . ' xmlns:wps="http://schemas.microsoft.com/office/word/2010/wordprocessingShape"'
             . ' mc:Ignorable="w14 wp14">'
             . '<w:body>'

             // ── Judul ──
             . '<w:p><w:pPr><w:pStyle w:val="Title"/><w:jc w:val="center"/></w:pPr>'
             . '<w:r><w:t>' . htmlspecialchars($title, ENT_XML1) . '</w:t></w:r></w:p>'

             . '<w:p><w:pPr><w:jc w:val="center"/></w:pPr>'
             . '<w:r><w:rPr><w:color w:val="666666"/></w:rPr>'
             . '<w:t xml:space="preserve">Diekspor oleh ' . $exportedBy . ' pada ' . $exportedAt . '</w:t>'
             . '</w:r></w:p>'

             . '<w:p><w:pPr><w:pBdr><w:bottom w:val="single" w:sz="6" w:space="1" w:color="CCCCCC"/></w:pBdr></w:pPr></w:p>'

             // ── Info Kegiatan ──
             . '<w:p><w:pPr><w:pStyle w:val="Heading1"/></w:pPr><w:r><w:t>Informasi Kegiatan</w:t></w:r></w:p>'
             . '<w:tbl><w:tblPr><w:tblStyle w:val="TableGrid"/>'
             . '<w:tblW w:w="0" w:type="auto"/>'
             . '<w:tblLook w:val="04A0"/></w:tblPr>'
             . $headerRows
             . '</w:tbl>'

             . '<w:p/>'

             // ── Peserta ──
             . '<w:p><w:pPr><w:pStyle w:val="Heading1"/></w:pPr><w:r><w:t>Peserta</w:t></w:r></w:p>'
             . $participantSection

             . '<w:p/>'

             // ── Notulen ──
             . '<w:p><w:pPr><w:pStyle w:val="Heading1"/></w:pPr><w:r><w:t>Notulen</w:t></w:r></w:p>'
             . $bodyXml

             . '<w:p/>'

             // ── Tindak Lanjut ──
             . '<w:p><w:pPr><w:pStyle w:val="Heading1"/></w:pPr><w:r><w:t>Tindak Lanjut</w:t></w:r></w:p>'
             . $tlSection

             . '<w:sectPr>'
             . '<w:pgSz w:w="12240" w:h="15840"/>'
             . '<w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440"'
             . ' w:header="720" w:footer="720" w:gutter="0"/>'
             . '</w:sectPr>'
             . '</w:body></w:document>';
    }

    // ── OOXML boilerplate files ────────────────────────────────────────────────
    private static function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
             . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
             . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
             . '<Default Extension="xml" ContentType="application/xml"/>'
             . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
             . '<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
             . '<Override PartName="/word/settings.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.settings+xml"/>'
             . '</Types>';
    }

    private static function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
             . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
             . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
             . '</Relationships>';
    }

    private static function documentRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
             . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
             . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
             . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/settings" Target="settings.xml"/>'
             . '</Relationships>';
    }

    private static function settings(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
             . '<w:settings xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
             . '<w:defaultTabStop w:val="720"/>'
             . '</w:settings>';
    }

    private static function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
             . '<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"'
             . ' xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml"'
             . ' w:docDefaults="1">'

             // Default
             . '<w:style w:type="paragraph" w:default="1" w:styleId="Normal">'
             . '<w:name w:val="Normal"/>'
             . '<w:rPr><w:sz w:val="22"/><w:szCs w:val="22"/>'
             . '<w:lang w:val="id-ID"/></w:rPr></w:style>'

             // Title
             . '<w:style w:type="paragraph" w:styleId="Title">'
             . '<w:name w:val="Title"/>'
             . '<w:pPr><w:spacing w:after="120"/></w:pPr>'
             . '<w:rPr><w:b/><w:sz w:val="40"/><w:szCs w:val="40"/>'
             . '<w:color w:val="1F3864"/></w:rPr></w:style>'

             // Heading 1
             . '<w:style w:type="paragraph" w:styleId="Heading1">'
             . '<w:name w:val="heading 1"/>'
             . '<w:pPr><w:spacing w:before="240" w:after="60"/>'
             . '<w:pBdr><w:bottom w:val="single" w:sz="4" w:space="1" w:color="4472C4"/></w:pBdr></w:pPr>'
             . '<w:rPr><w:b/><w:sz w:val="28"/><w:szCs w:val="28"/>'
             . '<w:color w:val="2F5496"/></w:rPr></w:style>'

             // Heading 2
             . '<w:style w:type="paragraph" w:styleId="Heading2">'
             . '<w:name w:val="heading 2"/>'
             . '<w:pPr><w:spacing w:before="200" w:after="40"/></w:pPr>'
             . '<w:rPr><w:b/><w:sz w:val="26"/><w:szCs w:val="26"/>'
             . '<w:color w:val="2E74B5"/></w:rPr></w:style>'

             // Heading 3
             . '<w:style w:type="paragraph" w:styleId="Heading3">'
             . '<w:name w:val="heading 3"/>'
             . '<w:pPr><w:spacing w:before="160" w:after="40"/></w:pPr>'
             . '<w:rPr><w:b/><w:i/><w:sz w:val="24"/><w:szCs w:val="24"/>'
             . '<w:color w:val="1F3864"/></w:rPr></w:style>'

             // Table Grid
             . '<w:style w:type="table" w:styleId="TableGrid">'
             . '<w:name w:val="Table Grid"/>'
             . '<w:tblPr><w:tblBorders>'
             . '<w:top w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>'
             . '<w:left w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>'
             . '<w:bottom w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>'
             . '<w:right w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>'
             . '<w:insideH w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>'
             . '<w:insideV w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>'
             . '</w:tblBorders></w:tblPr>'
             . '<w:tcPr><w:tcMar>'
             . '<w:top w:w="80" w:type="dxa"/>'
             . '<w:left w:w="108" w:type="dxa"/>'
             . '<w:bottom w:w="80" w:type="dxa"/>'
             . '<w:right w:w="108" w:type="dxa"/>'
             . '</w:tcMar></w:tcPr></w:style>'

             . '</w:styles>';
    }
}
