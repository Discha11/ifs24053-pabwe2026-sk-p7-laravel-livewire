<?php

namespace App\Features\Stock\Services;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StockExcelService
{
    public const HEADERS = ['name', 'sku', 'qty', 'unit', 'price', 'min_stock', 'status', 'description'];

    /**
     * Hasilkan isi file .xlsx (binary) dari kumpulan stok.
     */
    public function export(Collection $stocks): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Stok');
        $sheet->fromArray(self::HEADERS, null, 'A1');

        $row = 2;
        foreach ($stocks as $stock) {
            $sheet->fromArray([
                $stock->name,
                $stock->sku,
                $stock->quantity,
                $stock->unit,
                $stock->price,
                $stock->min_stock,
                $stock->status->value,
                $stock->description,
            ], null, 'A'.$row);
            $row++;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'stk');
        (new Xlsx($spreadsheet))->save($tmp);
        $content = file_get_contents($tmp);
        unlink($tmp);

        return $content;
    }

    /**
     * Baca file Excel dan kembalikan baris data (kolom: name, sku, quantity, unit, price, min_stock, description).
     *
     * @return array<int, array<string, mixed>>
     */
    public function parse(string $path): array
    {
        $rows = IOFactory::load($path)->getActiveSheet()->toArray(null, true, true, false);
        $header = array_map(fn ($h) => strtolower(trim((string) $h)), array_shift($rows) ?? []);
        $header = str_replace('qty', 'quantity', $header);
        $width = count($header);

        $result = [];
        foreach ($rows as $row) {
            $item = array_combine($header, array_pad(array_slice($row, 0, $width), $width, null));

            if (trim((string) ($item['name'] ?? '')) === '') {
                continue;
            }

            $result[] = $item;
        }

        return $result;
    }
}
