<?php
/**
 * DocxExporter — generate file .docx notula resmi
 * Format: Kop Kementerian Kebudayaan RI / Inspektorat Jenderal
 * Font  : Times New Roman 12pt (w:sz="24")
 * Kertas: A4 (11906 x 16838 twips), margin standar surat dinas
 */
class DocxExporter
{
    // Font default
    private const FONT = 'Times New Roman';
    // sz dalam half-points: 12pt = 24, 11pt = 22, 14pt = 28
    private const SZ   = '24';

    public static function export(
        array $meeting,
        array $notulen,
        array $participants,
        array $tindakLanjutList,
        array $user
    ): void {
        $filename = 'notula-' . ($meeting['id'] ?? 0) . '-' . date('Ymd') . '.docx';
        $content  = $notulen['content'] ?? '';

        $bodyXml = self::htmlToOoxml($content);

        // ── Bangun bagian-bagian dokumen ──────────────────────────────────────
        $participantLines = '';
        foreach ($participants as $i => $p) {
            $no   = $i + 1;
            $name = htmlspecialchars($p['name'] ?? '-', ENT_XML1);
            $dept = htmlspecialchars($p['dept_name'] ?? '', ENT_XML1);
            $label = $dept ? "{$name} ({$dept})" : $name;
            $participantLines .= self::para("{$no}. {$label}");
        }
        if (!$participantLines) $participantLines = self::para('-');

        $tlRows = self::tableRow(
            self::tableCell('No',         true, '400'),
            self::tableCell('Uraian Tindak Lanjut', true, '4000'),
            self::tableCell('PIC',        true, '2000'),
            self::tableCell('Deadline',   true, '1500'),
            self::tableCell('Status',     true, '1500')
        );
        foreach ($tindakLanjutList as $i => $tl) {
            $tlRows .= self::tableRow(
                self::tableCell((string)($i + 1)),
                self::tableCell(htmlspecialchars($tl['description']  ?? '-', ENT_XML1)),
                self::tableCell(htmlspecialchars($tl['assigned_name'] ?? '-', ENT_XML1)),
                self::tableCell(!empty($tl['due_date']) ? date('d M Y', strtotime($tl['due_date'])) : '-'),
                self::tableCell(ucfirst(str_replace('_', ' ', $tl['status'] ?? '-')))
            );
        }
        if (empty($tindakLanjutList)) {
            $tlRows .= self::tableRow(
                self::tableCell('-'),
                self::tableCell('-'),
                self::tableCell('-'),
                self::tableCell('-'),
                self::tableCell('-')
            );
        }

        // Metadata rapat
        $namaRapat   = htmlspecialchars($meeting['title']    ?? '-', ENT_XML1);
        $hariTgl     = !empty($meeting['start_datetime'])
                       ? self::hariIndonesia(date('N', strtotime($meeting['start_datetime'])))
                         . ', ' . date('d F Y', strtotime($meeting['start_datetime']))
                       : '-';
        $pukul       = !empty($meeting['start_datetime'])
                       ? date('H.i', strtotime($meeting['start_datetime']))
                         . (!empty($meeting['end_datetime']) ? ' – ' . date('H.i', strtotime($meeting['end_datetime'])) : '')
                         . ' WIB'
                       : '-';
        $tempat      = htmlspecialchars($meeting['location'] ?? '-', ENT_XML1);
        $pemimpin    = htmlspecialchars($meeting['created_by_name'] ?? '-', ENT_XML1);
        $notulis     = htmlspecialchars($user['name'] ?? '-', ENT_XML1);
        $jabatanTtd  = htmlspecialchars($meeting['dept_name'] ?? '-', ENT_XML1);

        $docXml = self::buildDocXml(
            $namaRapat,
            $hariTgl,
            $pukul,
            $tempat,
            $pemimpin,
            $participantLines,
            $bodyXml,
            $tlRows,
            $notulis,
            $jabatanTtd
        );

        // ── Buat ZIP (.docx) ──────────────────────────────────────────────────
        $zip = new ZipArchive();
        $tmp = tempnam(sys_get_temp_dir(), 'docx_');
        if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
            http_response_code(500);
            die('Gagal membuat file DOCX.');
        }
        $zip->addFromString('[Content_Types].xml',          self::contentTypes());
        $zip->addFromString('_rels/.rels',                   self::rootRels());
        $zip->addFromString('word/document.xml',             $docXml);
        $zip->addFromString('word/_rels/document.xml.rels',  self::documentRels());
        $zip->addFromString('word/settings.xml',             self::settings());
        $zip->addFromString('word/styles.xml',               self::styles());
        $zip->close();

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmp));
        header('Cache-Control: no-store');
        readfile($tmp);
        unlink($tmp);
        exit;
    }

    // ── Buat document.xml ─────────────────────────────────────────────────────
    private static function buildDocXml(
        string $namaRapat,
        string $hariTgl,
        string $pukul,
        string $tempat,
        string $pemimpin,
        string $participantLines,
        string $bodyXml,
        string $tlRows,
        string $notulis,
        string $jabatanTtd
    ): string {
        $ns = 'xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"'
            . ' xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006"'
            . ' mc:Ignorable=""';

        // ── Kop surat (tabel 2 kolom: logo | teks) ───────────────────────────
        // Logo: karena tidak ada gambar biner, gunakan placeholder teks [ LOGO ]
        $kop =
            '<w:tbl>'
          . '<w:tblPr>'
          . '<w:tblW w:w="0" w:type="auto"/>'
          . '<w:tblBorders>'
          . '<w:top w:val="none" w:sz="0" w:space="0" w:color="auto"/>'
          . '<w:left w:val="none" w:sz="0" w:space="0" w:color="auto"/>'
          . '<w:bottom w:val="single" w:sz="12" w:space="0" w:color="000000"/>'
          . '<w:right w:val="none" w:sz="0" w:space="0" w:color="auto"/>'
          . '<w:insideH w:val="none" w:sz="0" w:space="0" w:color="auto"/>'
          . '<w:insideV w:val="none" w:sz="0" w:space="0" w:color="auto"/>'
          . '</w:tblBorders>'
          . '</w:tblPr>'
          . '<w:tr>'
          // Kolom logo
          . '<w:tc><w:tcPr><w:tcW w:w="1200" w:type="dxa"/><w:vAlign w:val="center"/></w:tcPr>'
          . '<w:p><w:pPr><w:jc w:val="center"/></w:pPr>'
          . '<w:r><w:rPr><w:rFonts w:ascii="' . self::FONT . '" w:hAnsi="' . self::FONT . '"/>'
          . '<w:sz w:val="20"/></w:rPr><w:t>[ LOGO ]</w:t></w:r></w:p></w:tc>'
          // Kolom teks kop
          . '<w:tc><w:tcPr><w:tcW w:w="0" w:type="auto"/></w:tcPr>'
          . self::kopPara('KEMENTERIAN KEBUDAYAAN REPUBLIK INDONESIA', true, '28')
          . self::kopPara('INSPEKTORAT JENDERAL', true, '28')
          . self::kopPara('Jalan Jenderal Sudirman, Senayan, Jakarta 10270', false, '20')
          . self::kopPara('Telepon (021) 5725045     Laman kemenbud.go.id', false, '20')
          . '</w:tc>'
          . '</w:tr>'
          . '</w:tbl>';

        // ── Judul NOTULA ─────────────────────────────────────────────────────
        $judulNotula =
            '<w:p><w:pPr><w:jc w:val="center"/>'
          . '<w:spacing w:before="240" w:after="240"/>'
          . '</w:pPr>'
          . '<w:r><w:rPr>'
          . '<w:rFonts w:ascii="' . self::FONT . '" w:hAnsi="' . self::FONT . '"/>'
          . '<w:b/><w:sz w:val="28"/><w:u w:val="single"/>'
          . '</w:rPr><w:t>N O T U L A</w:t></w:r></w:p>';

        // ── Baris info rapat ─────────────────────────────────────────────────
        $infoRows =
            self::infoRow('Nama rapat',      $namaRapat)
          . self::infoRow('Hari, Tanggal',   $hariTgl)
          . self::infoRow('Pukul',           $pukul)
          . self::infoRow('Tempat',          $tempat)
          . self::infoRow('Pemimpin rapat',  $pemimpin);

        // ── Peserta ──────────────────────────────────────────────────────────
        $pesertaSection =
            self::infoLabelPara('Peserta Rapat')
          . $participantLines;

        // ── Isi pembahasan ────────────────────────────────────────────────────
        $pembahasanSection =
            self::seksiPara('1.  Persoalan yang Dibahas')
          . $bodyXml
          . self::seksiPara('2.  Simpulan')
          . self::para('');

        // ── Tabel Tindak Lanjut ───────────────────────────────────────────────
        $tlSection =
            self::seksiPara('3.  Tindak Lanjut')
          . '<w:tbl>'
          . '<w:tblPr><w:tblW w:w="0" w:type="auto"/>'
          . '<w:tblLook w:val="04A0"/></w:tblPr>'
          . $tlRows
          . '</w:tbl>';

        // ── Blok tanda tangan ─────────────────────────────────────────────────
        $ttd =
            '<w:p><w:pPr><w:spacing w:before="480"/></w:pPr></w:p>'
          . '<w:tbl>'
          . '<w:tblPr><w:tblW w:w="0" w:type="auto"/>'
          . '<w:tblBorders>'
          . '<w:top w:val="none" w:sz="0" w:space="0" w:color="auto"/>'
          . '<w:left w:val="none" w:sz="0" w:space="0" w:color="auto"/>'
          . '<w:bottom w:val="none" w:sz="0" w:space="0" w:color="auto"/>'
          . '<w:right w:val="none" w:sz="0" w:space="0" w:color="auto"/>'
          . '<w:insideH w:val="none" w:sz="0" w:space="0" w:color="auto"/>'
          . '<w:insideV w:val="none" w:sz="0" w:space="0" w:color="auto"/>'
          . '</w:tblBorders>'
          . '</w:tblPr>'
          . '<w:tr>'
          // Notulis
          . '<w:tc><w:tcPr><w:tcW w:w="4000" w:type="dxa"/></w:tcPr>'
          . self::para('Mengetahui,')
          . self::para($jabatanTtd)
          . '<w:p><w:pPr><w:spacing w:before="960"/></w:pPr></w:p>'
          . self::paraB($notulis)
          . '</w:tc>'
          // Pejabat
          . '<w:tc><w:tcPr><w:tcW w:w="4000" w:type="dxa"/></w:tcPr>'
          . self::para('Notulis,')
          . '<w:p><w:pPr><w:spacing w:before="960"/></w:pPr></w:p>'
          . self::para('( ....................................... )')
          . '</w:tc>'
          . '</w:tr>'
          . '</w:tbl>';

        // ── Rakit seluruh body ────────────────────────────────────────────────
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
             . "<w:document {$ns}>"
             . '<w:body>'
             . $kop
             . $judulNotula
             . $infoRows
             . '<w:p/>'
             . $pesertaSection
             . '<w:p/>'
             . $pembahasanSection
             . $tlSection
             . $ttd
             // Ukuran kertas A4 + margin standar surat dinas
             . '<w:sectPr>'
             . '<w:pgSz w:w="11906" w:h="16838"/>'
             . '<w:pgMar w:top="1418" w:right="1134" w:bottom="1418" w:left="1701"'
             . ' w:header="709" w:footer="709" w:gutter="0"/>'
             . '</w:sectPr>'
             . '</w:body></w:document>';
    }

    // ── Helper: baris info (Nama rapat, Tanggal, dsb) ─────────────────────────
    // Format: "Nama rapat : [nilai]" dalam satu paragraf
    private static function infoRow(string $label, string $value): string
    {
        $pad   = str_pad($label, 17);  // rata kiri dengan spasi
        $r     = self::FONT;
        $sz    = self::SZ;
        return '<w:p>'
             . '<w:r><w:rPr><w:rFonts w:ascii="' . $r . '" w:hAnsi="' . $r . '"/>'
             . '<w:sz w:val="' . $sz . '"/></w:rPr>'
             . '<w:t xml:space="preserve">' . htmlspecialchars($pad, ENT_XML1) . ' : </w:t></w:r>'
             . '<w:r><w:rPr><w:rFonts w:ascii="' . $r . '" w:hAnsi="' . $r . '"/>'
             . '<w:sz w:val="' . $sz . '"/></w:rPr>'
             . '<w:t xml:space="preserve">' . $value . '</w:t></w:r>'
             . '</w:p>';
    }

    private static function infoLabelPara(string $text): string
    {
        $r  = self::FONT;
        $sz = self::SZ;
        return '<w:p>'
             . '<w:r><w:rPr><w:rFonts w:ascii="' . $r . '" w:hAnsi="' . $r . '"/>'
             . '<w:b/><w:sz w:val="' . $sz . '"/></w:rPr>'
             . '<w:t>' . htmlspecialchars($text, ENT_XML1) . '</w:t></w:r>'
             . '</w:p>';
    }

    private static function seksiPara(string $text): string
    {
        $r  = self::FONT;
        $sz = self::SZ;
        return '<w:p>'
             . '<w:pPr><w:spacing w:before="160" w:after="80"/></w:pPr>'
             . '<w:r><w:rPr><w:rFonts w:ascii="' . $r . '" w:hAnsi="' . $r . '"/>'
             . '<w:b/><w:sz w:val="' . $sz . '"/></w:rPr>'
             . '<w:t>' . htmlspecialchars($text, ENT_XML1) . '</w:t></w:r>'
             . '</w:p>';
    }

    private static function kopPara(string $text, bool $bold, string $sz): string
    {
        $r   = self::FONT;
        $bTag = $bold ? '<w:b/>' : '';
        return '<w:p>'
             . '<w:r><w:rPr><w:rFonts w:ascii="' . $r . '" w:hAnsi="' . $r . '"/>'
             . $bTag . '<w:sz w:val="' . $sz . '"/></w:rPr>'
             . '<w:t>' . htmlspecialchars($text, ENT_XML1) . '</w:t></w:r>'
             . '</w:p>';
    }

    // Paragraf biasa
    private static function para(string $text): string
    {
        $r  = self::FONT;
        $sz = self::SZ;
        return '<w:p>'
             . '<w:r><w:rPr><w:rFonts w:ascii="' . $r . '" w:hAnsi="' . $r . '"/>'
             . '<w:sz w:val="' . $sz . '"/></w:rPr>'
             . '<w:t xml:space="preserve">' . htmlspecialchars($text, ENT_XML1) . '</w:t></w:r>'
             . '</w:p>';
    }

    // Paragraf bold
    private static function paraB(string $text): string
    {
        $r  = self::FONT;
        $sz = self::SZ;
        return '<w:p>'
             . '<w:r><w:rPr><w:rFonts w:ascii="' . $r . '" w:hAnsi="' . $r . '"/>'
             . '<w:b/><w:sz w:val="' . $sz . '"/></w:rPr>'
             . '<w:t xml:space="preserve">' . htmlspecialchars($text, ENT_XML1) . '</w:t></w:r>'
             . '</w:p>';
    }

    // ── Tabel helpers ─────────────────────────────────────────────────────────
    private static function tableRow(string ...$cells): string
    {
        return '<w:tr>' . implode('', $cells) . '</w:tr>';
    }

    private static function tableCell(string $text, bool $bold = false, string $width = ''): string
    {
        $r   = self::FONT;
        $sz  = self::SZ;
        $rpr = $bold
            ? '<w:rPr><w:rFonts w:ascii="' . $r . '" w:hAnsi="' . $r . '"/>'
              . '<w:b/><w:sz w:val="' . $sz . '"/></w:rPr>'
            : '<w:rPr><w:rFonts w:ascii="' . $r . '" w:hAnsi="' . $r . '"/>'
              . '<w:sz w:val="' . $sz . '"/></w:rPr>';
        $shd  = $bold ? '<w:shd w:val="clear" w:color="auto" w:fill="E8EDF2"/>' : '';
        $wcW  = $width ? '<w:tcW w:w="' . $width . '" w:type="dxa"/>' : '';
        return '<w:tc><w:tcPr>' . $wcW
             . '<w:tcBorders>'
             . '<w:top w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
             . '<w:left w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
             . '<w:bottom w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
             . '<w:right w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
             . '</w:tcBorders>' . $shd . '</w:tcPr>'
             . '<w:p><w:r>' . $rpr
             . '<w:t xml:space="preserve">' . $text . '</w:t></w:r></w:p>'
             . '</w:tc>';
    }

    // ── HTML → OOXML ──────────────────────────────────────────────────────────
    private static function htmlToOoxml(string $html): string
    {
        if (trim($html) === '') {
            return self::para('(belum ada isi notulen)');
        }

        $html = preg_replace('/<(style|script)[^>]*>.*?<\/\1>/si', '', $html);

        $html = preg_replace_callback('/<h([2-6])[^>]*>(.*?)<\/h\1>/si', function ($m) {
            $text = strip_tags($m[2]);
            return '%%HEADING:' . htmlspecialchars($text, ENT_XML1) . "%%\n";
        }, $html);

        $html = preg_replace_callback('/<li[^>]*>(.*?)<\/li>/si', function ($m) {
            $text = strip_tags($m[1]);
            return '%%LI:' . htmlspecialchars($text, ENT_XML1) . "%%\n";
        }, $html);

        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);

        $html = preg_replace_callback('/<p[^>]*>(.*?)<\/p>/si', function ($m) {
            $inner = $m[1];
            $inner = preg_replace_callback('/<(strong|b)[^>]*>(.*?)<\/\1>/si', function ($mm) {
                return '%%BOLD:' . htmlspecialchars(strip_tags($mm[2]), ENT_XML1) . '%%';
            }, $inner);
            $inner = preg_replace_callback('/<(em|i)[^>]*>(.*?)<\/\1>/si', function ($mm) {
                return '%%ITALIC:' . htmlspecialchars(strip_tags($mm[2]), ENT_XML1) . '%%';
            }, $inner);
            $inner = strip_tags($inner);
            return '%%P:' . trim($inner) . "%%\n";
        }, $html);

        $html = strip_tags($html);
        $xml  = '';
        $r    = self::FONT;
        $sz   = self::SZ;

        foreach (explode("\n", $html) as $line) {
            $line = trim($line);
            if ($line === '') continue;

            if (preg_match('/^%%HEADING:(.*)%%$/', $line, $hm)) {
                $xml .= '<w:p><w:pPr><w:spacing w:before="160" w:after="80"/></w:pPr>'
                      . '<w:r><w:rPr><w:rFonts w:ascii="' . $r . '" w:hAnsi="' . $r . '"/>'
                      . '<w:b/><w:sz w:val="' . $sz . '"/></w:rPr>'
                      . '<w:t xml:space="preserve">' . $hm[1] . '</w:t></w:r></w:p>';
            } elseif (preg_match('/^%%LI:(.*)%%$/', $line, $lm)) {
                $xml .= '<w:p><w:pPr><w:ind w:left="360"/></w:pPr>'
                      . '<w:r><w:rPr><w:rFonts w:ascii="' . $r . '" w:hAnsi="' . $r . '"/>'
                      . '<w:sz w:val="' . $sz . '"/></w:rPr>'
                      . '<w:t xml:space="preserve">• ' . $lm[1] . '</w:t></w:r></w:p>';
            } elseif (preg_match('/^%%P:(.*)%%$/', $line, $pm)) {
                $xml .= self::buildParagraph($pm[1]);
            } else {
                $xml .= self::para($line);
            }
        }

        return $xml ?: self::para('(belum ada isi notulen)');
    }

    private static function buildParagraph(string $inner): string
    {
        $r   = self::FONT;
        $sz  = self::SZ;
        $parts = preg_split('/(%%(?:BOLD|ITALIC):[^%]*%%)/U', $inner, -1, PREG_SPLIT_DELIM_CAPTURE);
        $runs  = '';
        foreach ($parts as $part) {
            if (preg_match('/^%%BOLD:(.*)%%$/', $part, $m)) {
                $runs .= '<w:r><w:rPr><w:rFonts w:ascii="' . $r . '" w:hAnsi="' . $r . '"/>'
                       . '<w:b/><w:sz w:val="' . $sz . '"/></w:rPr>'
                       . '<w:t xml:space="preserve">' . $m[1] . '</w:t></w:r>';
            } elseif (preg_match('/^%%ITALIC:(.*)%%$/', $part, $m)) {
                $runs .= '<w:r><w:rPr><w:rFonts w:ascii="' . $r . '" w:hAnsi="' . $r . '"/>'
                       . '<w:i/><w:sz w:val="' . $sz . '"/></w:rPr>'
                       . '<w:t xml:space="preserve">' . $m[1] . '</w:t></w:r>';
            } elseif ($part !== '') {
                $runs .= '<w:r><w:rPr><w:rFonts w:ascii="' . $r . '" w:hAnsi="' . $r . '"/>'
                       . '<w:sz w:val="' . $sz . '"/></w:rPr>'
                       . '<w:t xml:space="preserve">' . htmlspecialchars($part, ENT_XML1) . '</w:t></w:r>';
            }
        }
        return $runs ? '<w:p>' . $runs . '</w:p>' : '<w:p/>';
    }

    // ── Hari dalam Bahasa Indonesia ───────────────────────────────────────────
    private static function hariIndonesia(string $n): string
    {
        return ['1'=>'Senin','2'=>'Selasa','3'=>'Rabu','4'=>'Kamis',
                '5'=>'Jumat','6'=>'Sabtu','7'=>'Minggu'][$n] ?? '';
    }

    // ── OOXML boilerplate ─────────────────────────────────────────────────────
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
             . '<w:defaultTabStop w:val="709"/>'
             . '</w:settings>';
    }

    private static function styles(): string
    {
        $r  = self::FONT;
        $sz = self::SZ;
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
             . '<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"'
             . ' w:docDefaults="1">'

             // Normal — Times New Roman 12pt
             . '<w:style w:type="paragraph" w:default="1" w:styleId="Normal">'
             . '<w:name w:val="Normal"/>'
             . '<w:rPr>'
             . '<w:rFonts w:ascii="' . $r . '" w:hAnsi="' . $r . '" w:cs="' . $r . '"/>'
             . '<w:sz w:val="' . $sz . '"/><w:szCs w:val="' . $sz . '"/>'
             . '<w:lang w:val="id-ID"/>'
             . '</w:rPr></w:style>'

             // Table Grid
             . '<w:style w:type="table" w:styleId="TableGrid">'
             . '<w:name w:val="Table Grid"/>'
             . '<w:tblPr><w:tblBorders>'
             . '<w:top w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
             . '<w:left w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
             . '<w:bottom w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
             . '<w:right w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
             . '<w:insideH w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
             . '<w:insideV w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
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
