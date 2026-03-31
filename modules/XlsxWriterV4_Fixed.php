<?php
/**
 * XlsxWriterV4_Fixed.php
 *
 * Layout fixo do Excel:
 *   Linha 1  -> Título (A1:D1)
 *   Linha 2  -> Subtítulo (A2:D2)
 *   Linha 3  -> Espaço vazio
 *   Linha 4  -> Cabeçalho: #, Data, Leitura, Test. (A4:D4)
 *              + "Status" em E4 + "Resumo" mesclado F4:G4
 *   Linhas 5+ -> Dados + Status (E5+)
 *   F5/G5    -> "Lidos:" / COUNTIF
 *   F6/G6    -> "Não lidos:" / COUNTIF
 *   J/K      -> dados auxiliares do gráfico (colunas ocultas)
 *   Gráfico  -> I4:T22
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\Layout;

class XlsxWriterV4_Fixed {

    private Spreadsheet $spreadsheet;
    private \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws;

    private int $dataStartRow = 0;
    private int $dataEndRow   = 0;

    public function __construct() {
        $this->spreadsheet = new Spreadsheet();
        $this->ws = $this->spreadsheet->getActiveSheet();
    }

    // ── API pública ──────────────────────────────────────────────────

    public function addSheet(string $name): void {
        $this->ws->setTitle($name);
    }

    public function setColWidth(string $col, float $width): void {
        $this->ws->getColumnDimension($col)->setWidth($width);
    }

    public function writeRow(array $cells, string $defStyle = 'body'): void {
        $row = $this->ws->getHighestRow() + 1;
        foreach ($cells as $colIdx => $cell) {
            [$valor, $estilo] = is_array($cell)
                ? [$cell[0], $cell[1] ?? $defStyle]
                : [$cell, $defStyle];
            $ref = $this->numToLetter($colIdx + 1) . $row;
            $this->ws->setCellValue($ref, $valor);
            $this->applyStyle($ref, $estilo);
        }
    }

    public function writeRowMerged(string $value, int $fromCol, int $toCol, string $style): void {
        $row  = $this->ws->getHighestRow() + 1;
        $from = $this->numToLetter($fromCol) . $row;
        $to   = $this->numToLetter($toCol)   . $row;
        $this->ws->mergeCells("$from:$to");
        $this->ws->setCellValue($from, $value);
        $this->applyStyle($from, $style);
    }

    /** Chame ANTES de escrever as linhas de dados */
    public function markDataStart(): void {
        $this->dataStartRow = $this->ws->getHighestRow() + 1;
    }

    /** Chame APÓS escrever todas as linhas de dados */
    public function markDataEnd(): void {
        $this->dataEndRow = $this->ws->getHighestRow();
    }

    /**
     * Adiciona "Status" em E(headerRow) e dropdowns em E(dataStart):E(dataEnd).
     * Adiciona "Resumo" mesclado em F(headerRow):G(headerRow).
     * headerRow é calculado automaticamente como dataStartRow - 1.
     */
    public function addStatusColumnFixed(string $defaultValue = 'Não lido'): void {
        $headerRow = $this->dataStartRow - 1;

        // Cabeçalho Status na mesma linha que #/Data/Leitura/Test.
        $this->ws->setCellValue("E{$headerRow}", 'Status');
        $this->applyStyle("E{$headerRow}", 'header');
        $this->ws->getColumnDimension('E')->setWidth(15);

        // "Resumo" mesclado F:G na mesma linha do cabeçalho
        $this->ws->mergeCells("F{$headerRow}:G{$headerRow}");
        $this->ws->setCellValue("F{$headerRow}", 'Resumo');
        $this->applyStyle("F{$headerRow}", 'header');
        $this->ws->getColumnDimension('F')->setWidth(12);
        $this->ws->getColumnDimension('G')->setWidth(10);

        // Dropdowns em E(dataStart):E(dataEnd)
        for ($row = $this->dataStartRow; $row <= $this->dataEndRow; $row++) {
            $ref = "E{$row}";
            $this->ws->setCellValue($ref, $defaultValue);
            $this->applyStyle($ref, 'body');

            $dv = $this->ws->getCell($ref)->getDataValidation();
            $dv->setType(DataValidation::TYPE_LIST);
            $dv->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $dv->setAllowBlank(false);
            $dv->setShowDropDown(true);
            $dv->setShowInputMessage(true);
            $dv->setPromptTitle('Status da leitura');
            $dv->setPrompt('Selecione: Lido ou Não lido');
            $dv->setFormula1('"Lido,Não lido"');
        }
    }

    /**
     * Totais com COUNTIF logo abaixo do cabeçalho "Resumo".
     * F(dataStart)/G(dataStart) -> Lidos
     * F(dataStart+1)/G(dataStart+1) -> Não lidos
     */
    public function addTotalsRowFixed(): void {
        $range = "E{$this->dataStartRow}:E{$this->dataEndRow}";
        $r1    = $this->dataStartRow;
        $r2    = $this->dataStartRow + 1;

        $this->ws->setCellValue("F{$r1}", 'Lidos:');
        $this->ws->setCellValue("G{$r1}", "=COUNTIF($range,\"Lido\")");
        $this->applyStyle("F{$r1}", 'total_label');
        $this->applyStyle("G{$r1}", 'total_value');

        $this->ws->setCellValue("F{$r2}", 'Não lidos:');
        $this->ws->setCellValue("G{$r2}", "=COUNTIF($range,\"Não lido\")");
        $this->applyStyle("F{$r2}", 'total_label');
        $this->applyStyle("G{$r2}", 'total_value');
    }

    /**
     * Gráfico de pizza com % nas fatias e legenda à direita.
     * Usa colunas J e K como dados auxiliares (ocultas).
     */
    public function addPieChartFixed(): void {
        $range     = "E{$this->dataStartRow}:E{$this->dataEndRow}";
        $sheetName = $this->ws->getTitle();
        $headerRow = $this->dataStartRow - 1;

        $r0 = $headerRow;       // linha do cabeçalho "Resumo" (ex: 4)
        $r1 = $headerRow + 1;   // Categoria / Qtd        (ex: 5)
        $r2 = $headerRow + 2;   // Lidos                  (ex: 6)
        $r3 = $headerRow + 3;   // Não lidos              (ex: 7)

        // ── Cabeçalho da tabela auxiliar ─────────────────────────────
        $this->ws->setCellValue("F{$r1}", 'Categoria');
        $this->ws->setCellValue("G{$r1}", 'Qtd');
        $this->applyStyle("F{$r1}", 'header');
        $this->applyStyle("G{$r1}", 'header');

        // ── Linha Lidos ───────────────────────────────────────────────
        $this->ws->setCellValue("F{$r2}", 'Lidos');
        $this->ws->setCellValue("G{$r2}", "=COUNTIF($range,\"Lido\")");
        $this->applyStyle("F{$r2}", 'zebra_even');
        $this->ws->getStyle("G{$r2}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '4A7FCC']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'EEF3FF']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // ── Linha Não lidos ───────────────────────────────────────────
        $this->ws->setCellValue("F{$r3}", 'Não lidos');
        $this->ws->setCellValue("G{$r3}", "=COUNTIF($range,\"Não lido\")");
        $this->applyStyle("F{$r3}", 'zebra_odd');
        $this->ws->getStyle("G{$r3}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '888888']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E0EAFF']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // ── DataSeriesValues ──────────────────────────────────────────
        $dataRef = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_NUMBER,
            "'{$sheetName}'!\$G\${$r2}:\$G\${$r3}",
            null, 2
        );
        $dataRef->setFillColor(['4A7FCC', 'D0DCF0']);

        $labelRef = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_STRING,
            "'{$sheetName}'!\$F\${$r2}:\$F\${$r3}",
            null, 2
        );

        $titleRef = new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_STRING,
            "'{$sheetName}'!\$G\${$r1}",
            null, 1, ['Qtd']
        );

        // ── Série ─────────────────────────────────────────────────────
        $series = new DataSeries(
            DataSeries::TYPE_PIECHART,
            null, [0],
            [$titleRef], [$labelRef], [$dataRef]
        );

        $layout = new Layout();
        $layout->setShowPercent(true);
        $layout->setShowCatName(true);
        $layout->setShowVal(false);
        $layout->setShowSerName(false);
        $layout->setShowLegendKey(false);

        $plotArea = new PlotArea($layout, [$series]);
        $legend   = new Legend(Legend::POSITION_RIGHT, null, false);

        $chart = new Chart(
            'chartProgresso',
            new Title('Progresso da Leitura'),
            $legend, $plotArea, true, 0, null, null
        );

        $chart->setTopLeftPosition('I' . $r0);
        $chart->setBottomRightPosition('T' . ($r0 + 18));

        $this->ws->addChart($chart);
    }

    // ── Download / bytes ─────────────────────────────────────────────

    public function download(string $filename): void {
        if (ob_get_length()) ob_end_clean();

        $writer = new XlsxWriter($this->spreadsheet);
        $writer->setIncludeCharts(true);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: no-cache');

        $writer->save('php://output');
        exit;
    }

    public function getBytes(): string {
        ob_start();
        $writer = new XlsxWriter($this->spreadsheet);
        $writer->setIncludeCharts(true);
        $writer->save('php://output');
        return ob_get_clean();
    }

    // ── Estilos ──────────────────────────────────────────────────────

    private function applyStyle(string $ref, string $key): void {
        $map = [
            'title' => [
                'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '2E4057']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            'sub' => [
                'font'      => ['size' => 9, 'italic' => true, 'color' => ['rgb' => '888888']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            'header' => [
                'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '2E4057']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E0EAFF']],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BBCCE8']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            'zebra_even' => [
                'font'    => ['size' => 10, 'color' => ['rgb' => '1A1A2E']],
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'EEF3FF']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            'zebra_odd' => [
                'font'    => ['size' => 10, 'color' => ['rgb' => '1A1A2E']],
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E0EAFF']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            'at_even' => [
                'font'      => ['size' => 10, 'color' => ['rgb' => '333333']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F2F2F2']], // cinza bem claro
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D9D9D9']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            'at_odd' => [
                'font'      => ['size' => 10, 'color' => ['rgb' => '333333']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E6E6E6']], // cinza um pouco mais forte
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D9D9D9']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            'body' => [
                'font'    => ['size' => 10],
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFFFF']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            'total_label' => [
                'font'    => ['size' => 10, 'bold' => true, 'color' => ['rgb' => '2E4057']],
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'E0EAFF']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BBCCE8']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            'total_value' => [
                'font'    => ['size' => 11, 'bold' => true, 'color' => ['rgb' => '4A7FCC']],
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'EEF3FF']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BBCCE8']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            'legend' => [
                'font'      => ['size' => 8, 'italic' => true, 'color' => ['rgb' => '999999']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];

        if (isset($map[$key])) {
            $this->ws->getStyle($ref)->applyFromArray($map[$key]);
        }
    }

    // ── Helper ───────────────────────────────────────────────────────

    private function numToLetter(int $n): string {
        $r = '';
        while ($n > 0) {
            $r = chr(65 + ($n - 1) % 26) . $r;
            $n = intdiv($n - 1, 26);
        }
        return $r;
    }
}