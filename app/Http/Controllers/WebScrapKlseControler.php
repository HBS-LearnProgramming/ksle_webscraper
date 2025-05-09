<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExcelExport;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Stocks;
class WebScrapKlseControler extends Controller
{
    private const DOMAIN = 'https://www.klsescreener.com';
    // define key word detection
    private const KEY = ['changes in','immediate announcement'];
    // define short form name
    private const SHORT_NAME_KEY = [
        'EMPLOYEES PROVIDENT FUND BOARD' => 'EPF',
        'KUMPULAN WANG PERSARAAN' => 'KWP',
        'ABRDN' => 'ABRDN'
    ];
    private function hardCodeArray(){
        return [
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
    }
    public function index(){
        $stocks = Stocks::all()->toArray();


        return Inertia::render('WebScrapKLSE',['data' => $stocks]);

    }

    public function formSubmit(Request $request){

        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
            'stocks' => 'required'
        ]);
        // C:\laragon\www\ksle_webscraper\public\2025_04_30_KSLE_Scraper.xlsx
        $data = [];
        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();

        foreach (json_decode($request['stocks']) as $stock) {
           $url = self::DOMAIN.$stock;
           $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            ])->get($url);

            $crawler = new Crawler($response->body());
       
            $title = $crawler->filter('div.container-fluid h3')->count()
            ? $crawler->filter('div.container-fluid h3')->text()
            : ($crawler->filter('div.container-fluid h2')->count()
                ? $crawler->filter('div.container-fluid h2')->text()
                : 'Title Not Found');
            $link_title_details = array_values(array_filter($crawler->filter('ul.list-group li.list-group-item.list-group-item-action')
            ->each(function (Crawler $node) {
                $href = $node->filter('h6 a');
                $annoDate = $node->filter('time')->attr('datetime');
                // '2025-04-08'
                if((str_contains(strtolower($href->text()), self::KEY[0]) || str_contains(strtolower($href->text()), self::KEY[1])) && str_contains($annoDate, now()->format('Y-m-d'))){
                    return [
                        'text' => $href->text(),
                        'href' => $href->attr('href')
                    ];
                }
            })));

            $data[$title] = $this->linkDetailsData($link_title_details);


        }
       
        $data = array_filter($data, function($item){
            return !empty($item);
        });

        ksort($data);
        $this->fillExcelSheet($worksheet, $data);

        // Save the modified file temporarily
        $tempFilePath = storage_path('app/temp_filled_excel.xlsx');
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tempFilePath);

        return response()->download($tempFilePath, 'updated_' . $file->getClientOriginalName())->deleteFileAfterSend(true);
    }
    public function autoGenerate(){

        $stocks = Stocks::all()->toArray();
        
        // C:\laragon\www\ksle_webscraper\public\2025_04_30_KSLE_Scraper.xlsx
        $data = [];
        
        $spreadsheet = IOFactory::load('C:\laragon\www\ksle_webscraper\public\2025_04_30_KSLE_Scraper.xlsx');
        
        $worksheet = $spreadsheet->getActiveSheet();
        
        foreach ($stocks as $stock) {
           $url = self::DOMAIN.$stock['href'];
         
           $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            ])->get($url);

            $crawler = new Crawler($response->body());
       
            $title = $crawler->filter('div.container-fluid h3')->count()
            ? $crawler->filter('div.container-fluid h3')->text()
            : ($crawler->filter('div.container-fluid h2')->count()
                ? $crawler->filter('div.container-fluid h2')->text()
                : 'Title Not Found');
            $link_title_details = array_values(array_filter($crawler->filter('ul.list-group li.list-group-item.list-group-item-action')
            ->each(function (Crawler $node) {
                $href = $node->filter('h6 a');
                $annoDate = $node->filter('time')->attr('datetime');
                // '2025-04-08'
                if((str_contains(strtolower($href->text()), self::KEY[0]) || str_contains(strtolower($href->text()), self::KEY[1])) && str_contains($annoDate, now()->format('Y-m-d'))){
                    return [
                        'text' => $href->text(),
                        'href' => $href->attr('href')
                    ];
                }
            })));

            $data[$title] = $this->linkDetailsData($link_title_details);


        }
       
        $data = array_filter($data, function($item){
            return !empty($item);
        });

        ksort($data);
        $this->fillExcelSheet($worksheet, $data);

        // Save the modified file temporarily
        $tempFilePath = storage_path('app/temp_filled_excel.xlsx');
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tempFilePath);

        return response()->download($tempFilePath, now()->format('Y_m_d_').'KSLE_Scraper.xlsx')->deleteFileAfterSend(true);
    }

    private function fillExcelSheet($worksheet, $list_data)
    {
        $highestRow = $worksheet->getHighestRow(); // Get last filled row
        $headers = $worksheet->toArray()[0]; // Read headers

        // Find column indexes
        $noIndex = array_search("NO", $headers);
        $nameIndex = array_search("名字", $headers);
        $annoDateIndex = array_search("宣布日期", $headers);
        $trancDateIndex = array_search("交易日期", $headers);
        $percentIndex = array_search("%", $headers);
        $companyIndex = array_search("公司", $headers);
        $buyIndex = array_search("买进", $headers);
        $sellIndex = array_search('卖出', $headers);
        $buyPriceIndex = array_search("买进价钱", $headers);
        
        // First, collect all existing data (starting from row 2)
        $existingData = [];
        for ($row = 2; $row <= $highestRow; $row++) {
            $nameColLetter = Coordinate::stringFromColumnIndex($nameIndex + 1);
            $cellValue = $worksheet->getCell($nameColLetter . $row)->getValue();
            
            if ($cellValue !== null) {
                // Collect all data from this row
                $rowData = [];
                foreach ($headers as $index => $header) {
                    $colLetter = Coordinate::stringFromColumnIndex($index + 1);
                    $rowData[$index] = $worksheet->getCell($colLetter . $row)->getValue();
                }
                $existingData[] = $rowData;
            }
        }

        if ($annoDateIndex !== false && !empty($existingData)) {
            usort($existingData, function($a, $b) use ($annoDateIndex) {
                // Handle null values
                if ($a[$annoDateIndex] === null) return 1;
                if ($b[$annoDateIndex] === null) return -1;
                
                // Sort in descending order (newest first)
                return $b[$annoDateIndex] <=> $a[$annoDateIndex];
            });
        }
        
        // Start inserting new data from row 2 (after headers)
        $currentRow = 2;
        $indexNo = 1; // Start numbering from 1
        
        // Insert new data
        foreach ($list_data as $company_name => $transactions) {
            foreach ($transactions as $data) {
                foreach($data['tableData'] as $trancDate => $table_data){
                    $rowIndex = $currentRow++;
                    $date = \DateTime::createFromFormat('d M Y', $trancDate);
                    $excelDate = ($date) ? Date::PHPToExcel($date) : $trancDate;
                    
                    // Fill data into respective columns
                    $currentDate = Date::PHPToExcel(now());
                    $worksheet->setCellValue(Coordinate::stringFromColumnIndex($noIndex + 1) . $rowIndex, $indexNo);
                    $worksheet->setCellValue(Coordinate::stringFromColumnIndex($nameIndex + 1) . $rowIndex, $data['name']);
                    $worksheet->setCellValue(Coordinate::stringFromColumnIndex($annoDateIndex + 1) . $rowIndex, $currentDate);
                    $worksheet->setCellValue(Coordinate::stringFromColumnIndex($trancDateIndex + 1) . $rowIndex, $excelDate);
                    $worksheet->setCellValue(Coordinate::stringFromColumnIndex($percentIndex + 1) . $rowIndex, $data['directPercentage'] . '%');
                    $worksheet->setCellValue(Coordinate::stringFromColumnIndex($companyIndex + 1) . $rowIndex, $company_name);
                    $worksheet->setCellValue(Coordinate::stringFromColumnIndex($buyIndex + 1) . $rowIndex, $table_data['Acquired'] ?? null);
                    $worksheet->setCellValue(Coordinate::stringFromColumnIndex($sellIndex + 1) . $rowIndex, $table_data['Disposed'] ?? null);
                    $worksheet->setCellValue(Coordinate::stringFromColumnIndex($buyPriceIndex + 1) . $rowIndex, $table_data['Acquired_price'] ?? null);
                    $indexNo++;
                }
            }
        }
        
        // Now append the existing data after the new data
        foreach ($existingData as $rowData) {
            $rowIndex = $currentRow++;
            
            // Adjust the NO column to continue numbering
            if ($noIndex !== false) {
                $rowData[$noIndex] = $indexNo++;
            }
            
            // Write the existing data back to the worksheet
            foreach ($rowData as $index => $value) {
                $colLetter = Coordinate::stringFromColumnIndex($index + 1);
                $worksheet->setCellValue($colLetter . $rowIndex, $value);
            }
        }
    }

    public function linkDetailsData($link_details){
        $data=[];
        foreach ($link_details as $link) {
            $detail_link_response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            ])->get(self::DOMAIN.$link['href']);
           
            $crawler = new Crawler($detail_link_response->body());
            if(str_contains(strtolower($link['text']), self::KEY[0])){
                $table_column_label = $crawler->filter('table.ven_table tr')->each(function (Crawler $row) {
                    return $row->filter('td.formTableColumnLabel')->each(function (Crawler $cell) {
                        return trim($cell->text());
                    });
                });

                $filteredData = array_filter($table_column_label, function ($item) {
                    return count($item) >= 2;
                });


                $filteredData = array_values($filteredData);


                $direct_percentage = array_values(array_filter($crawler->filter('table.InputTable2')
                    ->filter('tr td.formContentLabel')
                    ->each(function(Crawler $node) {
                        if (str_contains(strtolower($node->text()), 'direct (%)')) {
                            $direct_percentage = $node->siblings()->filter('td.formContentData')->text();
                            return $direct_percentage;
                        }
                    })))[0] ?? 0;
                $name = array_values(array_filter($crawler->filter('table.InputTable2')
                ->filter('tr td.formContentLabel')
                ->each(function(Crawler $node) {
                    if (str_contains(strtolower($node->text()), 'name')) {
                        $name = $node->siblings()->filter('td.formContentData')->text();
                        return $name;
                    }
                })))[0] ?? 'Undefine Name';

                // detect the name and change it to short form
                foreach(self::SHORT_NAME_KEY as $key => $value){
                    $name = str_contains(strtoupper($name), $key)? $value:$name;
                }
                if(preg_match('/^(.*?)\s+BIN\s+/i', $name, $matches)){
                    $name = $matches[1] ?? $name;
                }

                $mergedData = [];

                foreach ($filteredData as $row) {
                    $date = $row[1];
                    $type = $row[3];
                    $amount = (int)str_replace(',', '', $row[2]);

                    if (!in_array($type, ['Acquired', 'Disposed'])) {
                        continue;
                    }

                    if (!isset($mergedData[$date])) {
                        $mergedData[$date] = [];
                    }

                    if (isset($mergedData[$date][$type])) {
                        $mergedData[$date][$type] += $amount;
                    } else {
                        $mergedData[$date][$type] = $amount;
                    }
                }
            }else{
                $name = $crawler->filter('div.stock-info h1 a')->text();
                $direct_percentage = 0;
                $mergedData = [];

                // Extract date and amount information
                $dateOfBuyBack = null;
                $acquiredAmount = null;
                $acquiredPrice  = null;
                $crawler->filter('table.InputTable2 tr')->each(function(Crawler $node) use (&$dateOfBuyBack, &$acquiredAmount, &$direct_percentage, &$acquiredPrice) {
                    $label = $node->filter('td.formContentLabel');
                    if ($label->count() > 0) {
                        $labelText = strtolower($label->text());
                        $dataCell = $node->filter('td.formContentData');

                        if ($dataCell->count() > 0) {
                            if (str_contains($labelText, 'date of buy back')) {
                                $dateOfBuyBack = trim($dataCell->text());
                            } else if (str_contains($labelText, 'total number of shares purchased (units)')) {
                                $acquiredAmount = (int)str_replace(',', '', $dataCell->text());
                            } else if (str_contains($labelText, 'total number of shares purchased and/or held as treasury shares against total number of issued shares of the listed issuer (%)')) {
                                $direct_percentage = trim($dataCell->text());
                            }else if (str_contains($labelText, 'minimum price paid for each share purchased')){
                                $acquiredPrice = trim($dataCell->text());
                                
                            }
                        }
                    }
                });
                // Create the same structure as in the if block
                if ($dateOfBuyBack && $acquiredAmount ) {
                    $mergedData[$dateOfBuyBack] = [
                        'Acquired' => $acquiredAmount,
                        'Acquired_price' => $acquiredPrice,
                    ];
                }
            }


            $data[] = [
                'tableData' => $mergedData,
                'directPercentage' => $direct_percentage,
                'name' => $name,
            ];

        }
        $grouped = [];
        foreach ($data as $item) {
            $key = serialize([
                'name' => $item['name'],
                'directPercentage' => $item['directPercentage'],
                'tableData' => array_keys($item['tableData']), // only use dates to group
            ]);
            
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'record' => $item,
                    'count' => 1
                ];
            } else {
                $grouped[$key]['count']++;

                // Multiply Acquired for existing date(s)
                foreach ($item['tableData'] as $date => $transaction) {
                    foreach ($transaction as $type => $amount) {
                        if (isset($grouped[$key]['record']['tableData'][$date][$type])) {
                            $grouped[$key]['record']['tableData'][$date][$type] += $amount;
                        } else {
                            $grouped[$key]['record']['tableData'][$date][$type] = $amount;
                        }
                    }
                }
            }
        }

        // Final deduplicated array
        $data = array_map(function ($entry) {
            return $entry['record'];
        }, array_values($grouped));
        
        return $data;
    }

}
