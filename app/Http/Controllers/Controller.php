<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

        // function to generate the data of table format
        public function tableDataCollection($items,$columnOrder, $columnRemove = [],$linkPart, $actions = []) {
            $columns = [];
            $data = []; 
            $count = 1; 
            
            $columns['action'] = [
                'title' => 'Action',
                'dataIndex' => 'action',
                'sortable' => false,
                'slotName' => 'action'
            ];
            $unorderedColumns = [];
    
            foreach ($items as $listData) { 
                foreach ($listData->getAttributes() as $key => $value) { 
                    if (!in_array($key, $columnRemove) && empty($columns[$key])) {
    
                        $column = [
                            'title' => ucfirst(str_replace('_', ' ', $key)), 
                            'dataIndex' => $key,
                            'sortable' => ['sortDirections' => ['ascend']],
                        ];
        
                        // Apply filtering for string columns only
        
                        $unorderedColumns[$key] = $column;
                    }
                }
                break;
            }
    
            foreach ($columnOrder as $key) {
                if (isset($unorderedColumns[$key])) {
                    $columns[$key] = $unorderedColumns[$key];
                    unset($unorderedColumns[$key]);
                }
            }
    
            foreach ($unorderedColumns as $key => $column) {
                $columns[$key] = $column;
            }
        
            // Extract table rows
            foreach ($items as $listData) {
                $row = ['key' => $count++];
                // generate action rows
                $row['action'] = [];
                foreach ($actions as $action) {
                    $row['action'][] = [
                            '/'.$linkPart.'/'.$listData->id .$action['action'], 
                            $action['name'],
                            $action['method'] 
                        ];
                }
    
                foreach ($listData->getAttributes() as $key => $value) {
                    if (!in_array($key, $columnRemove)) {
                        $row[$key] = $value; 
                        
                    }
                }
                $data[] = $row;
            }
        
            return [
                'columns' => array_values($columns),
                'data' => $data,
            ];
        }
    
        // function to generate the filter data
        public function filterDataCollection($items, $column_sequence,$columnRemove = []) {
            $filters = [];
            
            foreach ($items as $listData) {
                foreach ($listData->getAttributes() as $key => $value) {
                    if (!in_array($key, $columnRemove)) { // Exclude columns from filter
                        if (!isset($filters[$key])) {
                            $filters[$key] = []; // Initialize filter array for the key
                        }
        
                        // Ensure unique values only
                        if (!in_array($value, array_column($filters[$key], 'value'))) {
                            $filters[$key][] = [
                                'label' => $value,
                                'value' => $value
                            ];
                        }
                    }
                }
            }
            $sortedFilters = [];
            foreach ($column_sequence as $column) {
                if (isset($filters[$column])) {
                    $sortedFilters[$column] = $filters[$column]; // Add in the specified order
                }
            }
    
            // Append any additional columns that were not in `$column_sequence`
            foreach ($filters as $key => $value) {
                if (!isset($sortedFilters[$key])) {
                    $sortedFilters[$key] = $value;
                }
            }
            return $sortedFilters;
        }
    
        // filter function
        public function filterByLike(Request $request, $query, $columnRemove = []) {
            foreach ($request->query() as $key => $value) {
                $table = $query->getModel()->getTable();
                
                if (in_array($key, $columnRemove) || empty($value)) {
                    continue;
                }
               
                if (DB::getSchemaBuilder()->hasColumn($table, $key)) { 
                    if (is_numeric($value)) {
                        $query->where($key, $value);
                    } else {
                        $query->where($key, 'LIKE', "%{$value}%");
                    }
                } elseif (method_exists($query->getModel(), $key)) {
                    $query->whereHas($key, function ($q) use ($value) {
                        $q->where('name', 'LIKE', "%{$value}%");
                    });
                }
              
            }
            return $query;
        }
}
