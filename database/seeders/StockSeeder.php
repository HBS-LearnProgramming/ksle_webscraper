<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Stocks;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stock = [
            [
                "text" => 'AGMO',
                "href" => '/v2/stocks/view/0258'
            ],
            [
                "text" => 'ARTRONIQ',
                "href" => '/v2/stocks/view/0038'
            ],
            [
                "text" => 'AYS',
                "href" => '/v2/stocks/view/5021'
            ],
            [
                "text" => 'AYS',
                "href" => '/v2/stocks/view/5021'
            ],
            [
                "text" => 'CDB',
                "href" => '/v2/stocks/view/6947'
            ],
            [
                "text" => 'CTOS',
                "href" => '/v2/stocks/view/5301'
            ],
            [
                "text" => 'DIALOG',
                "href" => '/v2/stocks/view/7277'
            ],
            [
                "text" => 'EDARAN',
                "href" => '/v2/stocks/view/5036'
            ],
            [
                "text" => 'GAMUDA',
                "href" => '/v2/stocks/view/5398'
            ],
            [
                "text" => 'GCB',
                "href" => '/v2/stocks/view/5102'
            ],
            [
                "text" => 'HLIND',
                "href" => '/v2/stocks/view/3301'
            ],
            [
                "text" => 'JETSON',
                "href" => '/v2/stocks/view/9083'
            ],
            [
                "text" => 'KENANGA',
                "href" => '/v2/stocks/view/6483'
            ],
            [
                "text" => 'KHJB',
                "href" => '/v2/stocks/view/0210'
            ],
            [
                "text" => 'KOSSAN',
                "href" => '/v2/stocks/view/7153'
            ],
            [
                "text" => 'MAGMA',
                "href" => '/v2/stocks/view/7243'
            ],
            [
                "text" => 'MI',
                "href" => '/v2/stocks/view/5286'
            ],
            [
                "text" => 'MINHO',
                "href" => '/v2/stocks/view/5576'
            ],
            [
                "text" => 'MSM',
                "href" => '/v2/stocks/view/5202'
            ],
            [
                "text" => 'NEXG-WB',
                "href" => '/v2/stocks/view/5216WB'
            ],
            [
                "text" => 'PHB',
                "href" => '/v2/stocks/view/4464'
            ],
            [
                "text" => 'RAMSSOL',
                "href" => '/v2/stocks/view/0236'
            ],
            [
                "text" => 'TAANN',
                "href" => '/v2/stocks/view/5012'
            ],
            [
                "text" => 'TOMEI',
                "href" => '/v2/stocks/view/7230'
            ],
            [
                "text" => 'WPRTS',
                "href" => '/v2/stocks/view/5246'
            ],
            [
                "text" => 'YTL',
                "href" => '/v2/stocks/view/4677'
            ],
            [
                "text" => 'MYEG',
                "href" => '/v2/stocks/view/0138'
            ],
            [
                "text" => 'TEOSENG',
                "href" => '/v2/stocks/view/7252'
            ],
        ];
        foreach ($stock as $value) {
            Stocks::updateOrCreate(
                ['name' => $value['text']],
                ['href' => $value['href']]
            );
        }

    }
}
