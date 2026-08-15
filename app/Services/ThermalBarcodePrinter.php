<?php

namespace App\Services;

use App\Models\Copy;
use Illuminate\Support\Collection;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\PrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use RuntimeException;
use Throwable;

class ThermalBarcodePrinter
{
    public function makeConnector(): PrintConnector
    {
        return match (config('printer.connector')) {
            'usb' => new FilePrintConnector(config('printer.address')),
            'network' => new NetworkPrintConnector(config('printer.address'), config('printer.port', 9100)),
            'windows' => new WindowsPrintConnector(config('printer.address')),
            'cups' => new CupsPrintConnector(config('printer.address')),
            default => throw new RuntimeException("Unsupported PRINTER_CONNECTOR [".config('printer.connector').'].'),
        };
    }

    /**
     * @param  Collection<int, Copy>  $copies
     */
    public function printCopies(Collection $copies): void
    {
        if ($copies->isEmpty()) {
            return;
        }

        try {
            $printer = new Printer($this->makeConnector());
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Could not connect to the printer ('.config('printer.connector').' @ '.config('printer.address').'): '.$e->getMessage(),
                previous: $e
            );
        }

        try {
            foreach ($copies as $copy) {
                $this->printLabel($printer, $copy);
            }
        } catch (Throwable $e) {
            throw new RuntimeException('Printing failed partway through the batch: '.$e->getMessage(), previous: $e);
        } finally {
            $printer->close();
        }
    }

    protected function printLabel(Printer $printer, Copy $copy): void
    {
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->text(($copy->book?->name ?? 'Unknown Book')."\n");
        $printer->setEmphasis(false);
        $printer->feed();

        $printer->setBarcodeHeight(60);

        // Code128 content must be prefixed with a code-set selector ('{A'/'{B'/'{C')
        // -- the library itself validates this (Printer::barcode() rejects anything
        // that doesn't match /^\{[A-C].../) and rejects unprefixed input outright.
        // Code Set B covers the full printable-ASCII range our barcodes use
        // (digits + one uppercase letter for the category segment).
        $printer->barcode('{B'.$copy->barcode, Printer::BARCODE_CODE128);
        $printer->feed();

        $printer->text($copy->barcode."\n");
        $printer->feed(2);
        $printer->cut();
    }
}
