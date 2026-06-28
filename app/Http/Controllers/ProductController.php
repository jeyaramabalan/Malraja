<?php

namespace App\Http\Controllers;

use App\Models\CategoryModel;
use App\Models\HsnModel;
use App\Models\ProductsModel;
use App\Models\StockModel;
use App\Models\UnitModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Exception;

class ProductController extends Controller
{
    public function index()
    {
        $category = ProductsModel::select(
            'products.id', 
            'products.name', 
            'category.name as category_name', 
            'code', 
            'hsn', 
            'category_id', 
            'unit', 
            'mrp', 
            'customer_rate', 
            'purchase_rate', 
            'gst', 
            'sgt', 
            'cgst', 
            'additional_tax', 
            'final_price', 
            'products.status'
            )->leftjoin('category', 'products.category_id', '=', 'category.id')
            ->get();
        $categories = CategoryModel::select('id', 'name')->get();
        $category_option = "";
        foreach($categories as $category) {
            $category_option.= "<option value={$category['id']}>{$category['name']}</option>";
        }

        $this->datas['category'] = $category_option;
        $this->datas['products'] = $category;
        $this->datas['route'] = route("products.create");
        return view('product.products')->with($this->datas);
    }

    public function create()
    {
        $categories = CategoryModel::select('id', 'name')->get();
        $category_option = "";
        foreach($categories as $category) {
            $category_option.= "<option value={$category['id']}>{$category['name']}</option>";
        }

        $units = UnitModel::select('id', 'unit')->where('status', 1)->get();
        $units_option = "";
        foreach($units as $unit) {
            $units_option.= "<option value={$unit['id']}>{$unit['unit']}</option>";
        }

        $hsn = HsnModel::select('id', 'hsn')->where('status', 1)->get();
        $hsns_option = "";
        foreach($hsn as $hsnData) {
            $hsns_option.= "<option value={$hsnData['id']}>{$hsnData['hsn']}</option>";
        }

        $this->datas['category'] = $category_option;
        $this->datas['units'] = $units_option;
        $this->datas['hsn'] = $hsns_option;
        $this->datas['product'] = array();
        $this->datas['route'] = route("products.store");
        return view('product.productsaddedit')->with($this->datas);
    }

    public function edit($id)
    {
        $product = ProductsModel::find($id);
        $this->datas['route'] = route('products.update', $id);
        $categories = CategoryModel::select('id', 'name')->get();
        $category_option = "";
        foreach($categories as $category) {
            $selected = "";
            if($category['id'] == $product->category_id) {
                $selected = "selected";    
            }
            $category_option.= "<option $selected value={$category['id']}>{$category['name']}</option>";
        }

        $units = UnitModel::select('id', 'unit')->where('status', 1)->get();
        $units_option = "";
        foreach($units as $unit) {
            $selected = "";
            if($unit['id'] == $product->unit) {
                $selected = "selected";    
            }
            $units_option.= "<option $selected value={$unit['id']}>{$unit['unit']}</option>";
        }

        $hsn = HsnModel::select('id', 'hsn')->where('status', 1)->get();
        $hsns_option = "";
        foreach($hsn as $hsnData) {
            $selected = "";
            if($hsnData['id'] == $product->hsn) {
                $selected = "selected";    
            }
            $hsns_option.= "<option $selected value={$hsnData['id']}>{$hsnData['hsn']}</option>";
        }

        $this->datas['category'] = $category_option;
        $this->datas['units'] = $units_option;
        $this->datas['hsn'] = $hsns_option;
        $this->datas['product'] = $product;
        return view('product.productsaddedit')->with($this->datas);
    }

    public function getProductlist(Request $request)
    {
        $datas = ProductsModel::select(
            'products.id', 
            'products.name', 
            'products.tamil_name', 
            'category.name as category_name', 
            'code',
            'category_id', 
            'mrp', 
            'customer_rate', 
            'purchase_rate', 
            'gst', 
            'sgt', 
            'cgst', 
            'additional_tax', 
            'final_price', 
            'hsn.hsn as hsn', 
            'unit.unit as unit', 
            'products.status')
            ->leftjoin('category', 'products.category_id', '=', 'category.id')
            ->leftjoin('hsn', 'products.hsn', '=', 'hsn.id')
            ->leftjoin('unit', 'products.unit', '=', 'unit.id')
            ->where('products.status', 1);
        
        if(isset($request->searchName) && $request->searchName != "") {
            $datas = $datas->whereRaw('products.name LIKE ' . "'%{$request->searchName}%'",);
        }
        
        if(isset($request->catId) && $request->catId != "0") {
            $datas = $datas->where('products.category_id', $request->catId);
        }
        
        $limit = $request->input('length');
        $offset = $request->input('start');
        $datasCount = $datas->count();
        $datas = $datas->groupBy('products.name')->offset($offset)->limit(50)->get();
        $datalist = [];
        $i = $offset;
        foreach($datas as $list)
        {
            $list->sno          = ++$i .'';
            $list->action       = '<td align="center">
                                        <a href="'.route('products.edit',[$list->id]).'"><i style="color: green;" class="fa fa-edit"></i></a>
                                        <a class="pl-2" href="'.route('products-delete', [$list->id]).'"><i style="color: red;" class="fa fa-trash"></i></a>
                                   </td>';
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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'unique:products,name',
            'code' => 'unique:products,code',
        ]);

        if ($validator->fails())
        {
            $output = array('success' => 0, 'msg' => $validator->errors()->first());
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }
        $data = [];
        $request->code = preg_replace('/\s+/', '', $request->code);
        $data['name'] = $request->name;
        if(isset($request->tname)) {
            $data['tamil_name'] = $request->tname;
        }
        $data['code'] = $request->code;
        $data['unit'] = $request->unit;
        $data['category_id'] = $request->cat_id;//print_r($data);die;
        $data['hsn'] = $request->hsn;
        $data['mrp'] = $request->mrp;
        $data['customer_rate'] = $request->crate;
        $data['purchase_rate'] = $request->prate;
        $data['gst'] = $request->gst == '' ? '0' : $request->gst;
        $data['sgt'] = $request->sgt;
        $data['cgst'] = $request->cgst;
        $data['additional_tax'] = $request->atax;
        $data['final_price'] = $request->fprice;
        ProductsModel::create($data);
        $output = array('success' => 1, 'msg' => 'Product Create Successfully');
        return redirect()->route('products.index')->with('status', $output);
    }

    public function update(Request $request)
    {
        $data = [];
        $request->code = preg_replace('/\s+/', '', $request->code);
        $data['name'] = $request->name;
        if(isset($request->tname)) {
            $data['tamil_name'] = $request->tname;
        }
        $data['code'] = $request->code;
        $data['unit'] = $request->unit;
        $data['category_id'] = $request->cat_id;//print_r($data);die;
        $data['hsn'] = $request->hsn;
        $data['mrp'] = $request->mrp;
        $data['customer_rate'] = $request->crate;
        $data['purchase_rate'] = $request->prate;
        $data['gst'] = $request->gst == '' ? '0' : $request->gst;
        $data['sgt'] = $request->sgt;
        $data['cgst'] = $request->cgst;
        $data['additional_tax'] = $request->atax;
        $data['final_price'] = $request->fprice;
        ProductsModel::where('id', $request->id)->update($data);
        $output = array('success' => 1, 'msg' => 'Product Updated Successfully');
        return redirect()->route('products.index')->with('status', $output);
    }

    public function getCategoryProduct(Request $request)
    {
        $product = ProductsModel::where('category_id', $request->id)->where('status', 1)->get();
        return json_encode($product);
    }

    public function getProduct(Request $request)
    {
        $product = ProductsModel::where('id', $request->id)->first();
        $product['stock'] = self::getProductStock($request->id);
        return json_encode($product);
    }

    public function getRetailProduct(Request $request)
    {
        $product = ProductsModel::where('id', $request->id)->first();
        $product['stock'] = 1000;
        return json_encode($product);
    }

    public function getProductStock($id)
    {
        $dataQry = StockModel::select(
            DB::raw('SUM(stock.sale) as sale'),
            DB::raw('SUM(stock.purchase) as purchase'),
            DB::raw('SUM(stock.sale_return) as sale_return'),
        )
        ->where('stock.product_id', $id)->get();
        $stock = 0;
        foreach($dataQry as $proStock) {
            $sale = $proStock['sale'] - $proStock['sale_return'];
            $stock = $proStock['purchase'] - $sale;
        }
        return $stock;
    }

    public function addToCart(Request $request)
    {
        if($request->proQty != 0) {
            $product = ProductsModel::select(
                'products.id',
                'products.name',
                'products.hsn',
                'products.unit',
                'products.mrp',
                'products.customer_rate',
                'products.purchase_rate',
                'products.gst',
                'products.sgt',
                'products.category_id',
                'products.cgst',
                'products.additional_tax',
                'products.final_price',
                'category.name as categoryName',
                'unit.unit as unit'
                )
                ->leftjoin('category', 'products.category_id', '=', 'category.id')
                ->leftjoin('unit', 'products.unit', '=', 'unit.id')
                ->where('products.id', $request->proId)
                ->first();

            $currentStock = self::getProductStock($request->proId);
            if($currentStock == 0 || $currentStock == "0" || $currentStock < $request->proQty) {
                $resultArr = [
                    "error" => 1
                ];
                echo json_encode($resultArr);
                return;
                die;
            }

            $productPrice = 0;
            if(isset($request->iswhole) && $request->iswhole != "0") {
                $productPrice = $product->mrp;
            } else if(isset($request->rate) && $request->rate != "0") {
                $productPrice = $request->rate;
            } else {
                $productPrice = $product->customer_rate;
            }
            
            $productQuantity = $request->proQty;
            $gstAmount = ($productPrice * $product['gst']) / 100;
            $gstPercentage = $product['gst'];
            $taxPercentage = $product['tax'];
            $taxAmount = ($productPrice * $product['tax']) / 100;
            $productTotal = $productPrice * $request->proQty;

            $product['final_price'] = $productPrice;
            $product['count'] = $productQuantity;
            $product['g_rate'] = $productTotal;
            $product['free'] = 0;
            $product['dis'] = 0;
            $product['disa'] = 0;
            $gstPer = $gstPercentage;
            $product['tax'] = $taxPercentage;
            $product['gstp'] = $gstAmount;
            $product['additional_tax_amount'] = $taxAmount;
            $product['tgst'] = $productTotal;
            $product['gstAmount'] = $gstAmount;

            $existingProducts = Session::get('products');
            if($existingProducts == null) {
                $existingProducts = []; 
            }
            $existingProducts[$product['id']] = $product;
            Session::put('products', $existingProducts);
        } else {
            $existingProducts = Session::get('products');
            if($existingProducts == null) {
                $existingProducts = []; 
            }
            unset($existingProducts[$request->proId]);
            Session::put('products', $existingProducts);
        }
        
        $output = "<thead class = 'text-center'>";
        $output .= "<tr>";
        $output .= "<th rowspan='2'>S.No</th>";
        $output .= "<th rowspan='2' width='400'>Category</th>";
        $output .= "<th rowspan='2' width='400'>Prod_Name</th>";
        $output .= "<th rowspan='2' width='400'>Quantity</th>";
        $output .= "<th rowspan='2' width='100'>Unit</th>";
        $output .= "<th rowspan='2' width='300'>price</th>";
        // $output .= "<th rowspan='2' width='300'>Rate</th>";
        $output .= "<th rowspan='2' width='300'>G Rate</th>";
        $output .= "<th rowspan='2' width='100'>Remove</th>";
        $output .= "<th colspan='2' class='text-center'>Discount</th>";
        $output .= "<th colspan='2' class='text-center' width='100'>GST</th>";
        $output .= "<th colspan='2' class='text-center' width='100'>ADT</th>";
        $output .= "<th rowspan='2' width='100'>Grand Total<br><small>(Rs)</small></th>";
        $output .= "</tr>";
        $output .= "<tr>";
        $output .= "<th width='100'>%</th>";
        $output .= "<th width='300'>Amt</th>";
        $output .= "<th width='100'>%</th>";
        $output .= "<th width='300'>Amt</th>";
        $output .= "<th width='100'>%</th>";
        $output .= "<th width='300'>Amt</th>";
        $output .= "</tr>";
        $output .= "</thead>";
        
        $i = 0;
        $total = 0;
        // $products = Session::get('products');print_r($products);die;
        foreach(Session::get('products') as $cartArray) {
        $total = $total + $cartArray['tgst'];
        $i = $i+1;
        $output .= "<tr>
                <td> $i</td>
                <td>{$cartArray['categoryName']}</td>
                <td>{$cartArray['name']}</td>

                <td><div class='input-group' style = 'width:157px;'><a onclick='minusCartQty({$cartArray['id']}, {$cartArray['count']})' class='minus-item input-group-addon btn btn-outline-warning'  data-name='{$cartArray['name']}'   data-dis = '  {$cartArray['dis']}  '    data-tgst = '  {$cartArray['tgst']}  '  data-price = '  {$cartArray['price']}  '   data-cprice = '  {$cartArray['customer_rate']}  '  data-ori_price = '  {$cartArray['ori_price']}  ' data-gstp = '  {$cartArray['gstp']}  '  data-count = '  {$cartArray['count']}  ' data-adtp = '  {$cartArray['adtp']}  '   style = 'width:5px;'     ><i class='fa fa-minus' aria-hidden='true' style = 'margin-left:-5px;' ></i></a>
                <input style = 'text-align:center;' type='text' id = 'number{$cartArray['id']}' min = '0'  class='item-count form-control' data-name='  {$cartArray['name']}  '   data-tgst = '  {$cartArray['tgst']}  '  data-dis = '  {$cartArray['dis']}  '  data-price = '  {$cartArray['price']}  ' data-cprice = '  {$cartArray['customer_rate']}  ' data-ori_price = '  {$cartArray['ori_price']}  '  data-gstp = '  {$cartArray['gstp']}  ' data-adtp = '  {$cartArray['adtp']}  '  value='  {$cartArray['count']}  '>
                <a onclick='plusCartQty({$cartArray['id']}, {$cartArray['count']})' class='plus-item btn btn-outline-warning input-group-addon' data-category = '  {$cartArray['category']}  '  data-count = '  {$cartArray['count']}  '   data-dis = '  {$cartArray['dis']}  '   data-name='  {$cartArray['name']}  '   data-name='  {$cartArray['name']}  '     data-tgst = '  {$cartArray['tgst']}  '  data-price = '  {$cartArray['price']}  '   data-cprice = '  {$cartArray['customer_rate']}  '    data-ori_price = '  {$cartArray['ori_price']}  '  data-gstp = '  {$cartArray['gstp']}  '  data-adtp = '  {$cartArray['adtp']}  '  style = 'width:5px;'   ><i class='fa fa-plus' aria-hidden='true'  style = 'margin-left:-5px;'  ></i></a></div></td>
            
                <td>{$cartArray['unit']}</td>   
                <td><input id='itemrate{$cartArray['id']}' style = 'text-align:center; width:100px;' type='text' data-id='{$cartArray['id']}' class='item-rate form-control' value='{$cartArray['final_price']}'></td>
                
                <td>".round($cartArray['g_rate'], 2)."</td>  
                <td><a onclick='removeToCart({$cartArray['id']})' class='delete-item btn btn-outline-danger' data-name='  {$cartArray['name']}  ' ><i class='fa fa-trash' aria-hidden='true'></i></a></td>
                
                <td>{$cartArray['dis']}</td> 
                <td>{$cartArray['disa']}</td>
                <td>{$cartArray['gst']}</td> 
                <td>{$cartArray['gstAmount']}</td>
                <td>{$cartArray['additional_tax']}</td>
                <td>{$cartArray['additional_tax_amount']}</td>       
                <td><input style = 'text-align:center; width:100px;' type='text' class='item-gd form-control' data-name='  {$cartArray['name']}  '   data-count = '  {$cartArray['count']}  '  data-dis = '  {$cartArray['dis']}  '  data-price = '  {$cartArray['price']}  ' data-cprice = '  {$cartArray['customer_rate']}  ' data-ori_price = '  {$cartArray['ori_price']}  '  data-gstp = '  {$cartArray['gstp']}  ' data-adtp = '  {$cartArray['adtp']}  ' data-tgst = '  {$cartArray['tgst']}  '  value='  ".round($cartArray['tgst'], 2)."  '></td>
            </tr>";
        }
        // <td><input style = 'text-align:center; width:100px;' type='text' class='item-rate form-control' data-name='  {$cartArray['name']}  '   data-count = '  {$cartArray['count']}  '  data-dis = '  {$cartArray['dis']}  '  data-price = '  {$cartArray['price']}  ' data-cprice = '  {$cartArray['customer_rate']}  '  data-ori_price = '  {$cartArray['ori_price']}  '  data-gstp = '  {$cartArray['gstp']}  ' data-adtp = '  {$cartArray['adtp']}  ' data-tgst = '  {$cartArray['tgst']}'value='".round($cartArray['final_price'], 2)."'></td>
        Session::put('cart_total', $total);
        $data = "$total";
        $resultArr = [
            "cart" => $output,
            "total" => $data,
            "error" => 0
        ];
        return json_encode($resultArr);
    }

    public function addToCartPurchase(Request $request)
    {
        try{
            if($request->proQty != 0) {
                $product = ProductsModel::select(
                    'products.id',
                    'products.name',
                    'products.hsn',
                    'products.unit',
                    'products.mrp',
                    'products.customer_rate',
                    'products.purchase_rate',
                    'products.gst',
                    'products.sgt',
                    'products.category_id',
                    'products.cgst',
                    'products.additional_tax',
                    'products.final_price',
                    'category.name as categoryName',
                    'unit.unit as unit'
                    )
                    ->leftjoin('category', 'products.category_id', '=', 'category.id')
                    ->leftjoin('unit', 'products.unit', '=', 'unit.id')
                    ->where('products.id', $request->proId)
                    ->first();
                    
                    $productPrice = 0;
                    if(isset($request->iswhole) && $request->iswhole != "") {
                        $productPrice = $product->mrp;
                    } else if(isset($request->rate) && $request->rate != "0") {
                        $productPrice = $request->rate;
                    } else {
                        $productPrice = $product->purchase_rate;
                    }
                    $productQuantity = $request->proQty;
                    $gstAmount = ($productPrice * $product['gst']) / 100;
                    $gstPercentage = $product['gst'];
                    $taxPercentage = $product['tax'];
                    $taxAmount = ($productPrice * $product['tax']) / 100;
                    $productTotal = $productPrice * $request->proQty;
        
                    $product['purchase_rate'] = $productPrice;
                    $product['count'] = $productQuantity;
                    $product['g_rate'] = $productTotal;
                    $product['free'] = 0;
                    $product['dis'] = 0;
                    $product['disa'] = 0;
                    $gstPer = $gstPercentage;
                    $product['tax'] = $taxPercentage;
                    $product['gstp'] = $gstAmount;
                    $product['additional_tax_amount'] = $taxAmount;
                    $product['tgst'] = $productTotal;
                    $product['gstAmount'] = $gstAmount;
                

                $existingProducts = Session::get('products');
                if($existingProducts == null) {
                    $existingProducts = []; 
                }
                $existingProducts[$product['id']] = $product;
                Session::put('products', $existingProducts);
            } else {
                $existingProducts = Session::get('products');
                if($existingProducts == null) {
                    $existingProducts = []; 
                }
                unset($existingProducts[$request->proId]);
                Session::put('products', $existingProducts);
            }
            
            $output = "<thead class = 'text-center'>";
            $output .= "<tr>";
            $output .= "<th rowspan='2'>S.No</th>";
            $output .= "<th rowspan='2' width='400'>Category</th>";
            $output .= "<th rowspan='2' width='400'>Prod_Name</th>";
            $output .= "<th rowspan='2' width='400'>Quantity</th>";
            $output .= "<th rowspan='2' width='100'>Unit</th>";
            $output .= "<th rowspan='2' width='300'>price</th>";
            // $output .= "<th rowspan='2' width='300'>Rate</th>";
            $output .= "<th rowspan='2' width='300'>G Rate</th>"         ;
            $output .= "<th rowspan='2' width='100'>Remove</th>";
            $output .= "<th colspan='2' class='text-center'>Discount</th>";
            $output .= "<th colspan='2' class='text-center' width='100'>GST</th>";
            $output .= "<th colspan='2' class='text-center' width='100'>ADT</th>";
            $output .= "<th rowspan='2' width='100'>Grand Total<br><small>(Rs)</small></th>";
            $output .= "</tr>";
            $output .= "<tr>";
            $output .= "<th width='100'>%</th>";
            $output .= "<th width='300'>Amt</th>";
            $output .= "<th width='100'>%</th>";
            $output .= "<th width='300'>Amt</th>";
            $output .= "<th width='100'>%</th>";
            $output .= "<th width='300'>Amt</th>";
            $output .= "</tr>";
            $output .= "</thead>";
            
            $i = 0;
            $total = 0;
            // $products = Session::get('products');print_r($products);die;
            foreach(Session::get('products') as $cartArray) {
            $total = $total + $cartArray['tgst'];
            $i = $i+1;
            $output .= "<tr>
                    <td> $i</td>
                    <td>{$cartArray['categoryName']}</td>
                    <td>{$cartArray['name']}</td>

                    <td><div class='input-group' style = 'width:157px;'><a onclick='minusCartQty({$cartArray['id']}, {$cartArray['count']})' class='minus-item input-group-addon btn btn-outline-warning'  data-name='{$cartArray['name']}'   data-dis = '  {$cartArray['dis']}  '    data-tgst = '  {$cartArray['tgst']}  '  data-price = '  {$cartArray['price']}  '   data-cprice = '  {$cartArray['customer_rate']}  '  data-ori_price = '  {$cartArray['ori_price']}  ' data-gstp = '  {$cartArray['gstp']}  '  data-count = '  {$cartArray['count']}  ' data-adtp = '  {$cartArray['adtp']}  '   style = 'width:5px;'     ><i class='fa fa-minus' aria-hidden='true' style = 'margin-left:-5px;' ></i></a>
                    <input style = 'text-align:center;' type='text' id = 'number{$cartArray['id']}' min = '0'  class='item-count form-control' data-name='  {$cartArray['name']}  '   data-tgst = '  {$cartArray['tgst']}  '  data-dis = '  {$cartArray['dis']}  '  data-price = '  {$cartArray['price']}  ' data-cprice = '  {$cartArray['customer_rate']}  ' data-ori_price = '  {$cartArray['ori_price']}  '  data-gstp = '  {$cartArray['gstp']}  ' data-adtp = '  {$cartArray['adtp']}  '  value='  {$cartArray['count']}  '>
                    <a onclick='plusCartQty({$cartArray['id']}, {$cartArray['count']})' class='plus-item btn btn-outline-warning input-group-addon' data-category = '  {$cartArray['category']}  '  data-count = '  {$cartArray['count']}  '   data-dis = '  {$cartArray['dis']}  '   data-name='  {$cartArray['name']}  '   data-name='  {$cartArray['name']}  '     data-tgst = '  {$cartArray['tgst']}  '  data-price = '  {$cartArray['price']}  '   data-cprice = '  {$cartArray['customer_rate']}  '    data-ori_price = '  {$cartArray['ori_price']}  '  data-gstp = '  {$cartArray['gstp']}  '  data-adtp = '  {$cartArray['adtp']}  '  style = 'width:5px;'   ><i class='fa fa-plus' aria-hidden='true'  style = 'margin-left:-5px;'  ></i></a></div></td>
                
                    <td>{$cartArray['unit']}</td>   
                    <td><input id='itemrate{$cartArray['id']}' style = 'text-align:center; width:100px;' type='text' data-id='{$cartArray['id']}' class='item-rate form-control' value='{$cartArray['purchase_rate']}'></td>
                    <td>{$cartArray['purchase_rate']}</td>   
                    
                    <td><a onclick='removeToCart({$cartArray['id']})' class='delete-item btn btn-outline-danger' data-name='  {$cartArray['name']}  ' ><i class='fa fa-trash' aria-hidden='true'></i></a></td>

                    <td>{$cartArray['dis']}</td> 
                    <td>{$cartArray['disa']}</td>
                    <td>{$cartArray['gst']}</td> 
                    <td>{$cartArray['gstp']}</td>
                    <td>{$cartArray['additional_tax']}</td>
                    <td>{$cartArray['additional_tax_amount']}</td>       
                    <td><input style = 'text-align:center; width:100px;' type='text' class='item-gd form-control' data-name='  {$cartArray['name']}  '   data-count = '  {$cartArray['count']}  '  data-dis = '  {$cartArray['dis']}  '  data-price = '  {$cartArray['price']}  ' data-cprice = '  {$cartArray['customer_rate']}  ' data-ori_price = '  {$cartArray['ori_price']}  '  data-gstp = '  {$cartArray['gstp']}  ' data-adtp = '  {$cartArray['adtp']}  ' data-tgst = '  {$cartArray['tgst']}  '  value='  ".round($cartArray['tgst'], 2)."  '></td>
                </tr>";
            }
            Session::put('cart_total', $total);
            // <td><input style = 'text-align:center; width:100px;' type='text' class='item-rate form-control' data-name='  {$cartArray['name']}  '   data-count = '  {$cartArray['count']}  '  data-dis = '  {$cartArray['dis']}  '  data-price = '  {$cartArray['price']}  ' data-cprice = '  {$cartArray['customer_rate']}  '  data-ori_price = '  {$cartArray['ori_price']}  '  data-gstp = '  {$cartArray['gstp']}  ' data-adtp = '  {$cartArray['adtp']}  ' data-tgst = '  {$cartArray['tgst']}'value='".round($cartArray['final_price'], 2)."'></td>
            $data = "$total";
            $resultArr = [
                "cart" => $output,
                "total" => $data
            ];
            return json_encode($resultArr);
        }
        catch(Exception $e) {
            print_r($e->getMessage());
        }
    }

    public function delete($id)
    {
        ProductsModel::where('id', $id)->update(["status" => 0]);
        $output = array('success' => 1, 'msg' => "Product Deleted Successfully");
        return redirect()->route('products.index')->with('status', $output);
    }

    public static function getProductHsn($id)
    {
        $hsn = ProductsModel::select('hsn.hsn as productHsn')
                        ->leftjoin('hsn', 'products.hsn', '=', 'hsn.id')
                        ->leftjoin('unit', 'products.unit', '=', 'unit.id')
                        ->where('products.id', $id)->get();
        if(count($hsn) > 0 && !empty($hsn[0]['productHsn'])) {
            return $hsn[0]['productHsn'];
        } else {
            return "";
        }
    }
    public static function getProductUnit($id)
    {
        $hsn = ProductsModel::select('unit.unit as productUnit')
                        ->leftjoin('unit', 'products.unit', '=', 'unit.id')
                        ->where('products.id', $id)->get();
        if(count($hsn) > 0 && !empty($hsn[0]['productUnit'])) {
            return $hsn[0]['productUnit'];
        } else {
            return "";
        }
    }
}
