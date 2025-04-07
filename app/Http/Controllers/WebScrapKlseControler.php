<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExcelExport;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class WebScrapKlseControler extends Controller
{
    private const DOMAIN = 'https://www.klsescreener.com';
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
        ];
    }
    public function index(){
        $market_url = self::DOMAIN.'/v2/markets';
        $marketResponse = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ])->get($market_url);
        
     
        $market_crawler = new Crawler($marketResponse->body());
        $hardCodeLink = $this->hardCodeArray();
        $allowedCompanies = [
            'AGMO', 'ARTRONIQ', 'AYS', 'CDB', 'CTOS', 'DIALOG', 'EDARAN', 'GAMUDA', 'GCB', 
            'HLIND', 'JETSON', 'KENANGA', 'KHJB', 'KOSSAN', 'MAGMA', 'MI', 'MINHO', 'MSM', 
            'NEXG-WB', 'PHB', 'RAMSSOL', 'TAANN', 'TOMEI', 'WPRTS', 'YTL'
        ];
        
        // Extract and filter links
        $links = $market_crawler->filter('span.text-primary a')->each(function (Crawler $node) use ($allowedCompanies) {
            $text = trim($node->text());
    
            // Return only if the company is in the allowed list
            if (in_array($text, $allowedCompanies)) {
                return [
                    'text' => $text,
                    'href' => $node->attr('href'),
                ];
            }
            return null;
        });
    
        // Remove null values (entries not in the allowed list)
        $filteredLinks = array_filter($links);

        $mergedLinks = array_merge($filteredLinks, $hardCodeLink);

        // Remove duplicates based on the 'text' field
        $uniqueLinks = [];
        foreach ($mergedLinks as $link) {
            $uniqueLinks[$link['text']] = $link; // Store only unique values based on text
        }
    
        // Convert associative array back to indexed array
        $finalLinks = array_values($uniqueLinks);
        

        return Inertia::render('WebScrapKLSE',['data' => $finalLinks]);

    }

    public function formSubmit(Request $request){
      
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
            'stocks' => 'required'
        ]);
        $data = [];
        $file = $request->file('file');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();

        foreach (json_decode($request['stocks']) as $stock) {
           $url = self::DOMAIN.$stock;
           $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            ])->get($url);
            
            $crawler = new Crawler($response->body());
            // dd($response->body());
            $title = $crawler->filter('div.container-fluid h3')->count() 
            ? $crawler->filter('div.container-fluid h3')->text()
            : ($crawler->filter('div.container-fluid h2')->count() 
                ? $crawler->filter('div.container-fluid h2')->text()
                : 'Title Not Found');
            $link_title_details = array_values(array_filter($crawler->filter('ul.list-group li.list-group-item.list-group-item-action')
            ->each(function (Crawler $node) {
                $href = $node->filter('h6 a');
                $annoDate = $node->filter('time')->attr('datetime');//
                if(str_contains(strtolower($href->text()), 'changes in sub.') && str_contains($annoDate, now()->format('Y-m-d'))){
                    return [
                        'href' => $href->attr('href'),
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

        $firstEmptyRow = 2;
        for ($row = 2; $row <= $highestRow; $row++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($nameIndex + 1);
            $cellValue = $worksheet->getCell($colLetter . $row)->getValue();
            if ($cellValue === null) {
                $firstEmptyRow = $row;
                break;
            }
        }
        $indexNo= $firstEmptyRow-1;
       
        foreach ($list_data as $company_name => $transactions) {
            foreach ($transactions as $index => $data) {
                foreach($data['tableData'] as $trancDate => $table_data){
                    $rowIndex = $firstEmptyRow++;
                    $date = \DateTime::createFromFormat('d M Y', $trancDate);
                    $excelDate = ($date) ? Date::PHPToExcel($date) : $trancDate;
                    // Fill data into respective columns
                    $currentDate = Date::PHPToExcel(now());
                    $worksheet->setCellValueByColumnAndRow($noIndex + 1, $rowIndex, $indexNo);
                    $worksheet->setCellValueByColumnAndRow($nameIndex + 1, $rowIndex, $data['name']);
                    $worksheet->setCellValueByColumnAndRow($annoDateIndex + 1, $rowIndex, $currentDate);
                    $worksheet->setCellValueByColumnAndRow($trancDateIndex + 1, $rowIndex, $excelDate);
                    $worksheet->setCellValueByColumnAndRow($percentIndex + 1, $rowIndex, $data['directPercentage'].'%');
                    $worksheet->setCellValueByColumnAndRow($companyIndex + 1, $rowIndex, $company_name);
                    $worksheet->setCellValueByColumnAndRow($buyIndex + 1, $rowIndex, ($table_data['Acquired']??null));
                    $worksheet->setCellValueByColumnAndRow($sellIndex + 1, $rowIndex, ($table_data['Disposed'] ?? null));
              
                    $indexNo++;
                }

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
          
            $name = str_contains(strtoupper($name),'EMPLOYEES PROVIDENT FUND BOARD')? 'EPF':$name;
            $name = str_contains(strtoupper($name),'KUMPULAN WANG PERSARAAN')? 'KWP':$name;
           
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
            
            $data[] = [
                'tableData' => $mergedData,
                'directPercentage' => $direct_percentage,
                'name' => $name,
            ];
           
        }
        return $data;
    }

}
