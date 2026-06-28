<?php

namespace App\Http\Controllers;

use App\Models\CategoryModel;
use App\Models\ProductsModel;
use App\Models\StockModel;
use App\Models\UnitModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockController extends Controller
{
    public function getStock()
    {
        $categories = CategoryModel::select('id', 'name')->get();
        $category_option = "";
        foreach($categories as $category) {
            $category_option.= "<option value={$category['id']}>{$category['name']}</option>";
        }

        $this->datas['category'] = $category_option;
        $this->datas['route'] = route("purchase.create");
        return view('stock.stock')->with($this->datas);
    }

    public function getStocklist(Request $request)
    {
        $dataQry = StockModel::select(
            DB::raw('SUM(stock.sale) as sale'),
            DB::raw('SUM(stock.purchase) as purchase'),
            DB::raw('SUM(stock.sale_return) as sale_return'),
            DB::raw('SUM(stock.purchase_return) as purchase_return'),
            DB::raw('SUM(stock.purchase_free) as purchase_free'),
            DB::raw('SUM(stock.sale_free) as sale_free'),
            DB::raw('SUM(stock.sale_damage) as sale_damage'),
            DB::raw('SUM(stock.purchase_damage) as purchase_damage'),
            DB::raw('SUM(stock.purchase_damage_return) as purchase_damage_return'),
            DB::raw('SUM(stock.sale_damage_return) as sale_damage_return'),
            'products.name as product_name',
            'products.status as deletedStatus',
            // 'category.name as category_name',
            // 'hsn.hsn as hsn_name',
        )
        // ->leftjoin('hsn', 'stock.hsn_id', '=', 'hsn.id')
        ->leftjoin('products', 'stock.product_id', '=', 'products.id');
        
        if(isset($request->searchName) && $request->searchName != "") {
            $dataQry = $dataQry->whereRaw('products.name LIKE ' . "'%{$request->searchName}%'",);
        }
        if(isset($request->catId) && $request->catId != "0") {
            $dataQry = $dataQry->where('products.category_id', $request->catId);
        }

        $dataQry = $dataQry->orderBy('products.name')->groupBy('stock.product_id');
        $limit = $request->input('length');
        $offset = $request->input('start');
        $page = $request->input('page');

        $datasCount = 300;
        // $datas = $dataQry->get();print_r($offset);die;
        $datas = $dataQry->offset($offset)->limit(50)->get();
        $datalist = [];
        $i = $offset;
        foreach($datas as $list)
        {
            if($list->deletedStatus == 0) {
                $list->product_name = $list->product_name . " (D)";
            }
            $list->sno          = ++$i .'';
            $list->sale         = $list->sale - $list->sale_return;
            $list->loss         = 0;
            $list->stock         = $list->purchase - $list->sale;
            $datalist[]         = $list;
        }

        $json_data = [
            'draw'=>intval($request->input('draw')),
            'recordsTotal'=>intval($datasCount),
            'recordsFiltered'=>intval($datasCount),
            'data'=>$datalist
        ];
        echo json_encode($json_data);
    }
}
