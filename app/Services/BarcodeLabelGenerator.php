<?php

namespace App\Services;

use TCPDF;

class BarcodeLabelGenerator
{

    /**
     * Generate a PDF with 40 labels per page (4x10 grid).
     *
     * Each item: ['barcode' => '...', 'name' => 'Book Title']
     */
    public function generate(array $items, int $columns = 4, int $rows = 10): string
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator(config('app.name'));
        $pdf->SetAuthor(config('app.name'));
        $pdf->SetTitle('Copy Barcodes');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(false);

        // Page layout
        $leftMargin = 10;
        $topMargin = 10;
        $rightMargin = 10;
        $bottomMargin = 10;
        $hSpacing = 2;
        $vSpacing = 2;

        $pageWidth = 210; // A4 width mm
        $pageHeight = 297; // A4 height mm

        $usableWidth = $pageWidth - $leftMargin - $rightMargin - ($columns - 1) * $hSpacing;
        $usableHeight = $pageHeight - $topMargin - $bottomMargin - ($rows - 1) * $vSpacing;

        $labelWidth = $usableWidth / $columns;
        $labelHeight = $usableHeight / $rows;

        $index = 0;
        $total = count($items);

        while ($index < $total) {
            $pdf->AddPage();

            // Generate 4x10 grid of labels
            for ($r = 0; $r < $rows; $r++) {
                for ($c = 0; $c < $columns; $c++) {
                    if ($index >= $total) {
                        break 2;
                    }

                    $item = $items[$index];

                    // Calculate label position
                    $x = $leftMargin + ($c * ($labelWidth + $hSpacing));
                    $y = $topMargin + ($r * ($labelHeight + $vSpacing));

                    // Draw border for debugging (uncomment if needed)
                    // $pdf->Rect($x, $y, $labelWidth, $labelHeight);

                    // Arabic book title - centered with wrapping
                    $bookTitle = (string) ($item['name'] ?? '');
                    if ($bookTitle) {
                        $pdf->SetFont('dejavusans', '', 7);
                        $pdf->SetXY($x + 0.5, $y + 0.5);
                        $pdf->MultiCell($labelWidth - 1, 3, $bookTitle, 0, 'C', false, 1, '', '', true, 0, false, true, 8, 'T');
                    }

                    // Barcode - centered within label
                    $barcodeCode = (string) ($item['barcode'] ?? '');
                    $barcodeY = $y + 9; // Leave more space for wrapped title
                    $barcodeHeight = max(6, $labelHeight - 16);
                    $barcodeWidth = $labelWidth - 2;

                    if ($barcodeCode) {
                        $pdf->write1DBarcode(
                            $barcodeCode,
                            'C128',
                            $x + 1,
                            $barcodeY,
                            $barcodeWidth,
                            $barcodeHeight,
                            0.3,
                            [
                                'position' => '',
                                'align' => 'C',
                                'stretch' => false,
                                'fitwidth' => true,
                                'cellfitalign' => '',
                                'border' => false,
                                'hpadding' => '0',
                                'vpadding' => '0',
                                'fgcolor' => [0, 0, 0],
                                'bgcolor' => false,
                                'text' => false,
                            ],
                            'N'
                        );

                        // Human-readable code - centered
                        $pdf->SetFont('dejavusans', '', 6.5);
                        $pdf->SetXY($x + 0.5, $y + $labelHeight - 4);
                        $pdf->Cell($labelWidth - 1, 3, $barcodeCode, 0, 0, 'C');
                    }

                    $index++;
                }
            }
        }

        return $pdf->Output('copy-barcodes.pdf', 'S');
    }
}

