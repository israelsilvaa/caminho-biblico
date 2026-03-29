<?php
/**
 * XlsxWriter — gerador de XLSX puro em PHP, sem dependências externas.
 * Compatível com qualquer hospedagem compartilhada (PHP 7.4+).
 *
 * Uso:
 *   $x = new XlsxWriter();
 *   $x->addSheet('Plan1');
 *   $x->writeRow(['Olá', 'Mundo'], 'header');
 *   $x->writeRow(['dado', 123]);
 *   $x->download('arquivo.xlsx');
 */
class XlsxWriter {

    private string $sheet   = 'Plan1';
    private array  $rows    = [];
    private array  $widths  = [];
    private array  $styles  = [];   // índice => definição XFID
    private array  $styleMap= [];   // chave => índice
    private array  $fills   = [];
    private array  $fonts   = [];
    private array  $borders = [];
    private array  $numFmts = [];
    private array  $sharedStrings = [];
    private array  $ssMap   = [];
    private int    $ssIdx   = 0;

    // Cores padrão
    private const DFLT_FILL   = 'FFFFFFFF';
    private const DFLT_BORDER = '00000000';

    public function __construct() {
        // fill 0 = none, fill 1 = gray125 (exigido pelo spec)
        $this->fills[] = '<fill><patternFill patternType="none"/></fill>';
        $this->fills[] = '<fill><patternFill patternType="gray125"/></fill>';
        // font 0 padrão
        $this->fonts[] = '<font><sz val="10"/><name val="Arial"/><family val="2"/></font>';
        // border 0 = sem borda
        $this->borders[] = '<border><left/><right/><top/><bottom/><diagonal/></border>';
        // numFmt 0 = geral
        $this->numFmts = [];
    }

    public function addSheet(string $name): void {
        $this->sheet = $name;
    }

    public function setColWidth(int $col, float $width): void {
        $this->widths[$col] = $width;
    }

    /**
     * @param array $cells  [[valor, estilo_key], ...] ou [valor, ...]
     * @param string $defStyle estilo padrão para células sem estilo explícito
     */
    public function writeRow(array $cells, string $defStyle = 'body'): void {
        $row = [];
        foreach ($cells as $cell) {
            if (is_array($cell)) {
                $row[] = [$cell[0], $this->getStyleId($cell[1] ?? $defStyle)];
            } else {
                $row[] = [$cell, $this->getStyleId($defStyle)];
            }
        }
        $this->rows[] = $row;
    }

    public function writeRowMerged(string $value, int $fromCol, int $toCol, string $style): void {
        // Célula mesclada — guarda como linha especial
        $this->rows[] = ['__merged__', $value, $fromCol, $toCol, $this->getStyleId($style)];
    }

    // ─── Estilos pré-definidos ────────────────────────────────────────
    private function getStyleId(string $key): int {
        if (isset($this->styleMap[$key])) return $this->styleMap[$key];

       [$fontId, $fillId, $borderId, $numFmtId, $halign, $wrap] = match($key) {
            'title'   => [$this->font(14,true,'2E4057'),  $this->fill('FFFFFF'), $this->border0(), 0, 'center', false],
            'sub'     => [$this->font(9,false,'888888',true), $this->fill('FFFFFF'), $this->border0(), 0, 'center', false],
            
            'header'  => [$this->font(11,true,'2E4057'),  $this->fill('E0EAFF'), $this->thinBorder('DDDDDD'), 0, 'left', false],
            
            'zebra_even' => [$this->font(10), $this->fill('EEF3FF'), $this->thinBorder('DDDDDD'), 0, 'left', false],
            'zebra_odd'  => [$this->font(10), $this->fill('E0EAFF'), $this->thinBorder('DDDDDD'), 0, 'left', false],
            
            'legend'  => [$this->font(8,false,'999999',true), $this->fill('FFFFFF'), $this->border0(), 0, 'center', false],
            default   => [$this->font(10), $this->fill('FFFFFF'), $this->border0(), 0, 'left', false],
        };

        $idx = count($this->styles);
        $this->styles[] = compact('fontId','fillId','borderId','numFmtId','halign','wrap');
        $this->styleMap[$key] = $idx;
        return $idx;
    }

    private function font(int $sz=10, bool $bold=false, string $color='000000', bool $italic=false): int {
        $k = "$sz|$bold|$color|$italic";
        if (!isset($this->fontMap[$k])) {
            $b = $bold   ? '<b/>' : '';
            $i = $italic ? '<i/>' : '';
            $this->fonts[] = "<font><sz val=\"$sz\"/>$b$i<color rgb=\"FF$color\"/><name val=\"Arial\"/><family val=\"2\"/></font>";
            $this->fontMap[$k] = count($this->fonts)-1;
        }
        return $this->fontMap[$k];
    }
    private array $fontMap = [];

    private function fill(string $hex): int {
        $k = $hex;
        if (!isset($this->fillMap[$k])) {
            $this->fills[] = "<fill><patternFill patternType=\"solid\"><fgColor rgb=\"FF$hex\"/><bgColor indexed=\"64\"/></patternFill></fill>";
            $this->fillMap[$k] = count($this->fills)-1;
        }
        return $this->fillMap[$k];
    }
    private array $fillMap = [];

    private function border0(): int { return 0; }

    private function thinBorder(string $hex): int {
        if (!isset($this->borderMap[$hex])) {
            $s = "<border><left style=\"thin\"><color rgb=\"FF$hex\"/></left><right style=\"thin\"><color rgb=\"FF$hex\"/></right><top style=\"thin\"><color rgb=\"FF$hex\"/></top><bottom style=\"thin\"><color rgb=\"FF$hex\"/></bottom><diagonal/></border>";
            $this->borders[] = $s;
            $this->borderMap[$hex] = count($this->borders)-1;
        }
        return $this->borderMap[$hex];
    }
    private array $borderMap = [];

    // ─── Shared Strings ──────────────────────────────────────────────
    private function ss(string $v): int {
        if (!isset($this->ssMap[$v])) {
            $this->sharedStrings[] = $v;
            $this->ssMap[$v] = $this->ssIdx++;
        }
        return $this->ssMap[$v];
    }

    // ─── Gerar XML ────────────────────────────────────────────────────
    private function colLetter(int $n): string {
        $r = '';
        while ($n > 0) {
            $r = chr(65 + ($n-1)%26) . $r;
            $n = intdiv($n-1, 26);
        }
        return $r;
    }

    private function buildSheet(): string {
        $cols = '';
        foreach ($this->widths as $c => $w) {
            $cols .= "<col min=\"$c\" max=\"$c\" width=\"$w\" customWidth=\"1\"/>";
        }
        $xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
              . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
        if ($cols) $xml .= "<cols>$cols</cols>";
        $xml .= '<sheetData>';

        $merges = [];
        $rowNum = 0;
        foreach ($this->rows as $row) {
            $rowNum++;
            if ($row[0] === '__merged__') {
                [, $val, $fc, $tc, $sid] = $row;
                $cellRef = $this->colLetter($fc) . $rowNum;
                $merges[] = "{$cellRef}:{$this->colLetter($tc)}{$rowNum}";
                $xml .= "<row r=\"$rowNum\"><c r=\"{$cellRef}\" s=\"{$sid}\" t=\"s\"><v>{$this->ss(htmlspecialchars($val,ENT_XML1))}</v></c></row>";
                continue;
            }
            $xml .= "<row r=\"$rowNum\">";
            foreach ($row as $colIdx => [$val, $sid]) {
                $colIdx++; // 1-based
                $ref = $this->colLetter($colIdx) . $rowNum;
                if (is_numeric($val) && !is_string($val)) {
                    $xml .= "<c r=\"$ref\" s=\"$sid\"><v>$val</v></c>";
                } else {
                    $xml .= "<c r=\"$ref\" s=\"$sid\" t=\"s\"><v>{$this->ss(htmlspecialchars((string)$val,ENT_XML1))}</v></c>";
                }
            }
            $xml .= '</row>';
        }
        $xml .= '</sheetData>';
        if ($merges) {
            $xml .= '<mergeCells count="'.count($merges).'">';
            foreach ($merges as $m) $xml .= "<mergeCell ref=\"$m\"/>";
            $xml .= '</mergeCells>';
        }
        $xml .= '</worksheet>';
        return $xml;
    }

    private function buildStyles(): string {
        $xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';

        // numFmts (vazio — usamos apenas geral)
        $xml .= '<numFmts count="0"/>';

        // fonts
        $xml .= '<fonts count="'.count($this->fonts).'">'.implode('',$this->fonts).'</fonts>';
        // fills
        $xml .= '<fills count="'.count($this->fills).'">'.implode('',$this->fills).'</fills>';
        // borders
        $xml .= '<borders count="'.count($this->borders).'">'.implode('',$this->borders).'</borders>';

        // cellStyleXfs
        $xml .= '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>';

        // cellXfs
        $xml .= '<cellXfs>';
        // xf 0 padrão
        $xml .= '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>';
        foreach ($this->styles as $s) {
            $aln = $s['halign'] !== 'left'
                ? "<alignment horizontal=\"{$s['halign']}\"/>"
                : '';
            $xml .= "<xf numFmtId=\"{$s['numFmtId']}\" fontId=\"{$s['fontId']}\" fillId=\"{$s['fillId']}\" borderId=\"{$s['borderId']}\" xfId=\"0\" applyFont=\"1\" applyFill=\"1\" applyBorder=\"1\" applyAlignment=\"1\">$aln</xf>";
        }
        $xml .= '</cellXfs>';
        $xml .= '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>';
        $xml .= '</styleSheet>';
        return $xml;
    }

    private function buildSharedStrings(): string {
        $total = count($this->sharedStrings);
        $xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= "<sst xmlns=\"http://schemas.openxmlformats.org/spreadsheetml/2006/main\" count=\"$total\" uniqueCount=\"$total\">";
        foreach ($this->sharedStrings as $s) {
            $xml .= "<si><t xml:space=\"preserve\">$s</t></si>";
        }
        $xml .= '</sst>';
        return $xml;
    }

    private function buildWorkbook(): string {
        $xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
              . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
        $xml .= '<sheets><sheet name="'.htmlspecialchars($this->sheet,ENT_XML1).'" sheetId="1" r:id="rId1"/></sheets>';
        $xml .= '</workbook>';
        return $xml;
    }

    private function buildRels(): string {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
             . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
             . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
             . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
             . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>'
             . '</Relationships>';
    }

    private function buildTopRels(): string {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
             . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
             . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
             . '</Relationships>';
    }

    private function buildContentTypes(): string {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
             . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
             . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
             . '<Default Extension="xml" ContentType="application/xml"/>'
             . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
             . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
             . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
             . '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
             . '</Types>';
    }

    // ─── Montar ZIP ───────────────────────────────────────────────────
    private function makeZip(): string {
        $files = [
            '[Content_Types].xml'          => $this->buildContentTypes(),
            '_rels/.rels'                  => $this->buildTopRels(),
            'xl/workbook.xml'              => $this->buildWorkbook(),
            'xl/_rels/workbook.xml.rels'   => $this->buildRels(),
            'xl/worksheets/sheet1.xml'     => $this->buildSheet(),
            'xl/styles.xml'                => $this->buildStyles(),
            'xl/sharedStrings.xml'         => $this->buildSharedStrings(),
        ];

        $zip = '';
        $centralDir = '';
        $offset = 0;

        foreach ($files as $name => $content) {
            $crc    = crc32($content);
            $size   = strlen($content);
            $comp   = gzdeflate($content, 6);
            $csize  = strlen($comp);
            $dosTime= $this->dosTime();
            $nameB  = $name;
            $nameLen= strlen($nameB);

            // Local file header
            $lfh = pack('VvvvVVVVvv', 0x04034b50, 20, 0, 8, $dosTime, $crc, $csize, $size, $nameLen, 0);
            $entry = $lfh . $nameB . $comp;
            $zip  .= $entry;

            // Central directory entry
            $cde = pack('VvvvvVVVVvvvvvVV',
                0x02014b50, 20, 20, 0, 8, $dosTime, $crc, $csize, $size,
                $nameLen, 0, 0, 0, 0, 0, $offset);
            $centralDir .= $cde . $nameB;
            $offset += strlen($entry);
        }

        $cdSize  = strlen($centralDir);
        $count   = count($files);
        $eocd = pack('VvvvvVVv', 0x06054b50, 0, 0, $count, $count, $cdSize, $offset, 0);
        return $zip . $centralDir . $eocd;
    }

    private function dosTime(): int {
        $t = getdate();
        return (($t['year']-1980) << 25) | ($t['mon'] << 21) | ($t['mday'] << 16)
             | ($t['hours'] << 11) | ($t['minutes'] << 5) | ($t['seconds'] >> 1);
    }

    public function download(string $filename): void {
        $data = $this->makeZip();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($data));
        header('Cache-Control: max-age=0');
        echo $data;
    }

    public function getBytes(): string {
        return $this->makeZip();
    }
}
