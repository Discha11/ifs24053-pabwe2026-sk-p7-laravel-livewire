<?php

namespace Tests\Unit\Services;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Services\StockExcelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class StockExcelServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_has_headers_and_rows(): void
    {
        $stock = Stock::factory()->create(['name' => 'Beras', 'sku' => 'BRS-1']);
        $path = tempnam(sys_get_temp_dir(), 'exp');
        file_put_contents($path, (new StockExcelService)->export(collect([$stock])));

        $rows = IOFactory::load($path)->getActiveSheet()->toArray();
        unlink($path);

        $this->assertSame(StockExcelService::HEADERS, $rows[0]);
        $this->assertSame('Beras', $rows[1][0]);
        $this->assertSame('BRS-1', $rows[1][1]);
        $this->assertSame($stock->status->value, $rows[1][6]);
    }

    public function test_parse_maps_qty_and_skips_blank_names(): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['Name', 'SKU', 'Qty', 'Unit', 'Price', 'Min_Stock', 'Description'], null, 'A1');
        $sheet->fromArray(['Beras', 'BRS-1', 10, 'kg', 12000, 3, 'Beras putih'], null, 'A2');
        $sheet->fromArray(['', 'KOSONG', 1], null, 'A3');
        $sheet->fromArray(['Gula'], null, 'A4');

        $path = tempnam(sys_get_temp_dir(), 'par');
        (new Xlsx($spreadsheet))->save($path);

        $rows = (new StockExcelService)->parse($path);
        unlink($path);

        $this->assertCount(2, $rows);
        $this->assertSame('Beras', $rows[0]['name']);
        $this->assertEquals(10, $rows[0]['quantity']);
        $this->assertSame('Gula', $rows[1]['name']);
        $this->assertNull($rows[1]['sku']);
    }

    public function test_parse_empty_sheet_returns_empty_array(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'emp');
        (new Xlsx(new Spreadsheet))->save($path);

        $this->assertSame([], (new StockExcelService)->parse($path));
        unlink($path);
    }
}
