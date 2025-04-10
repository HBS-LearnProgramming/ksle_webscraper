<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stocks;
use Inertia\Inertia;

class StockController extends Controller
{
    public function index(Request $request, $id = null){
        $query = Stocks::query();
        $removeFilterColumn = ['id', 'href' ,'updated_at', 'created_at'];
        $removeListColumn = ['id', 'updated_at'];
        $column_sequence = ['name', 'href','created_at'];
        $stock = null;
        if($id){
            $stock = Stocks::find($id);
        }
        $query = $this->filterByLike($request, $query, $removeFilterColumn);

        $stocks = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $filteredCollection = $stocks->getCollection()->isEmpty() ? 
            Stocks::orderBy('created_at', 'desc')->limit(20)->get() :
            $stocks->getCollection();

        $pagination = [
            'current' => $stocks->currentPage(),
            'perPage' => $stocks->perPage(),
            'total' => $stocks->total(),
        ];
    

        $action = [
            [
                'action' => '/edit',
                'name' => 'Edit',
                'method' => 'get',
            ],
            [
                'action' => '/delete',
                'name' => 'Delete',
                'method' => 'post',
            ],
        ];

        $filterData = $this->filterDataCollection($filteredCollection, $column_sequence, $removeFilterColumn);
        $data = [
            'stock' => $stock,
            'pagination' => $pagination,
            'list_data' => $this->tableDataCollection($stocks->getCollection(), $column_sequence, $removeListColumn,'stock', $action),
            'filter_data' => $filterData,
        ];

        return Inertia::render('Stocks',$data);
    }

    public function formSubmit(Request $request){
        $request->validate([
            'id' => 'nullable',
            'name' => 'required',
            'href' => 'required'
        ]);
        if($request['id']){
            $stock = Stocks::find($request['id']);
            $stock->update($request->all());
            return response()->json(['message' => 'Stock updated successfully'], 200);
        }
        Stocks::updateOrCreate(
            ['name' => $request['name']],
            ['href' => $request['href']]
        );
        return response()->json(['message' => 'Stock created successfully'], 200);
    }

    public function delete($id){
        Stocks::find($id)->delete();
        return response()->json(['message' => 'Stock deleted successfully'], 200);
    }
}
