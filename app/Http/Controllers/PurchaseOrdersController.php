<?php

namespace App\Http\Controllers;

use App\Models\CategoryModel;
use App\Models\OrderDetailModel;
use App\Models\OrderModel;
use App\Models\ProductsModel;
use App\Models\PurchaseOrderDetailModel;
use App\Models\PurchaseOrderModel;
use App\Models\PurposeVisitModel;
use App\Models\StockModel;
use App\Models\User;
use App\Models\VendorModel;
use App\Models\VisitsModel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class PurchaseOrdersController extends Controller
{
    public function index(Request $request)
    {
        $this->datas['route'] = route("purchase.create");
        return view('purchase_orders.orders')->with($this->datas);
    }

    public function create(Request $request)
    {
        $categories = CategoryModel::select('id', 'name')->where('status', 1)->get();
        $category_option = "";
        foreach($categories as $category) {
            $category_option.= "<option value={$category['id']}>{$category['name']}</option>";
        }
        $customers = VendorModel::select('id', 'name')->where('status', 1)->get();
        $customers_option = "";
        foreach($customers as $category) {
            $customers_option.= "<option value={$category['id']}>{$category['name']}</option>";
        }

        $this->datas['vendor'] = $customers_option;
        $this->datas['route'] = route("purchase.store");
        $this->datas['category'] = $category_option;
        Session::forget('products');
        return view('purchase_orders.add')->with($this->datas);
    }

    public function update($id, Request $request)
    {
        try{
            $data = PurchaseOrderModel::find($id);
            $total = Session::get('cart_total');
            PurchaseOrderModel::where('id', $id)->update(['total' => $total]);
            $orderDetailList = PurchaseOrderDetailModel::where('order_id', $id)->get();
            foreach($orderDetailList as $key => $val ) {
                foreach(Session::get('products') as $cartArray) {
                    if($cartArray['id'] == $val['product_id']) {
                        unset($orderDetailList[$key]);
                    }
                }
            }
            
            if(count($orderDetailList) > 0) {
                foreach($orderDetailList as $detailsData) {
                    PurchaseOrderDetailModel::where('id', $detailsData->id)->update(['status' => 0]);
                    StockModel::where('bill', $data['bill_no'])
                            ->where('product_id', $detailsData->product_id)
                            ->update(["purchase" => 0]);
                }
            }

            foreach(Session::get('products') as $cartArray) {
                $count = PurchaseOrderDetailModel::where('order_id', $id)->where('product_id', $cartArray['id'])->count();
                $order_details = [];
                $order_details['quantity'] = $cartArray['count'];
                $order_details['amount'] = $cartArray['tgst'];
                $order_details['discount'] = 0;
                $order_details['quantity_price'] = $cartArray['final_price'];
                $order_details['free_item'] = 0;
                $order_details['tax'] = $cartArray['additional_tax'];
                $order_details['gst'] = $cartArray['gst'];
                
                // Stock add
                $stock = [];
                $stock['type'] = "sale";
                $stock['bill'] = $data['bill_no'];
                $stock['date'] = $data['date'];
                $stock['product_id'] = $cartArray['id'];
                $stock['category_id'] = $cartArray['category_id'];
                $stock['hsn_id'] = $cartArray['hsn'];
                $stock['sale'] = $cartArray['count'];
                if($count > 0) {
                    PurchaseOrderDetailModel::where('order_id', $id)->where('product_id', $cartArray['id'])->update($order_details);
                    StockModel::where('bill', $data['bill_no'])
                            ->where('product_id', $cartArray['id'])
                            ->update(["purchase" => $cartArray['count']]);
                } else {
                    $order_details['order_id'] = $id;
                    $order_details['product_id'] = $cartArray['id'];
                    $order_details['category_id'] = $cartArray['category_id'];
                    StockModel::create($stock);
                    PurchaseOrderDetailModel::create($order_details);
                }
            }
            $output = array('success' => 1, 'msg' => 'Purchase Order Updated Successfully');
            return redirect()->route('purchase.index')->with('status', $output);
        }
        catch(Exception $e) {
            $output = array('success' => 0, 'msg' => $e->getMessage());
            return redirect()->route('purchase.index')->with('status', $output);
        }
    }

    public function getEditPurchase(Request $request)
    {
        $orderItems = PurchaseOrderDetailModel::where('order_id', $request->id)->where('status', 1)->get();
        foreach($orderItems as $orderItem) {
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
                'category.name as categoryName'
                )
                ->leftjoin('category', 'products.category_id', '=', 'category.id')
                ->where('products.id', $orderItem->product_id)
                ->first();
                
                $productPrice = 0;
                $productPrice = $product->purchase_rate;
                $productQuantity = $orderItem->quantity;
                $gstAmount = ($productPrice * $product['gst']) / 100;
                $gstPercentage = $product['gst'];
                $taxPercentage = $product['tax'];
                $taxAmount = ($productPrice * $product['tax']) / 100;
                $productTotal = $productPrice * $orderItem->quantity;
    
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
            $existingProducts[$orderItem->product_id] = $product;
            Session::put('products', $existingProducts);
        }
        // print_r(Session::get('products'));die;
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
        <input style = 'text-align:center;' type='text' id = 'number1' min = '0'  class='item-count form-control' data-name='  {$cartArray['name']}  '   data-tgst = '  {$cartArray['tgst']}  '  data-dis = '  {$cartArray['dis']}  '  data-price = '  {$cartArray['price']}  ' data-cprice = '  {$cartArray['customer_rate']}  ' data-ori_price = '  {$cartArray['ori_price']}  '  data-gstp = '  {$cartArray['gstp']}  ' data-adtp = '  {$cartArray['adtp']}  '  value='  {$cartArray['count']}  '>
        <a onclick='plusCartQty({$cartArray['id']}, {$cartArray['count']})' class='plus-item btn btn-outline-warning input-group-addon' data-category = '  {$cartArray['category']}  '  data-count = '  {$cartArray['count']}  '   data-dis = '  {$cartArray['dis']}  '   data-name='  {$cartArray['name']}  '   data-name='  {$cartArray['name']}  '     data-tgst = '  {$cartArray['tgst']}  '  data-price = '  {$cartArray['price']}  '   data-cprice = '  {$cartArray['customer_rate']}  '    data-ori_price = '  {$cartArray['ori_price']}  '  data-gstp = '  {$cartArray['gstp']}  '  data-adtp = '  {$cartArray['adtp']}  '  style = 'width:5px;'   ><i class='fa fa-plus' aria-hidden='true'  style = 'margin-left:-5px;'  ></i></a></div></td>
    
        <td>{$cartArray['unit']}</td>   
        <td><input id='itemrate{$cartArray['id']}' style = 'text-align:center; width:100px;' type='text' data-id='{$cartArray['id']}' class='item-rate form-control' value='{$cartArray['purchase_rate']}'></td>
        <td>{$cartArray['purchase_rate']}</td>
        

        <td>".round($cartArray['g_rate'], 2)."</td>  
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
        // <td><input style = 'text-align:center; width:100px;' type='text' class='item-rate form-control' data-name='  {$cartArray['name']}  '   data-count = '  {$cartArray['count']}  '  data-dis = '  {$cartArray['dis']}  '  data-price = '  {$cartArray['price']}  ' data-cprice = '  {$cartArray['customer_rate']}  '  data-ori_price = '  {$cartArray['ori_price']}  '  data-gstp = '  {$cartArray['gstp']}  ' data-adtp = '  {$cartArray['adtp']}  ' data-tgst = '  {$cartArray['tgst']}'value='".round($cartArray['final_price'], 2)."'></td>
        Session::put('cart_total', $total);
        $data = "$total";
        $resultArr = [
            "cart" => $output,
            "total" => $data
        ];
        return json_encode($resultArr);
    }

    public function edit($id)
    {
        $orders = PurchaseOrderModel::where('id', $id)->first();
        $categories = CategoryModel::select('id', 'name')->get();
        $category_option = "";
        foreach($categories as $category) {
            $category_option.= "<option value={$category['id']}>{$category['name']}</option>";
        }
        $customers = VendorModel::select('id', 'name')->where('status', 1)->get();
        $customers_option = "";
        foreach($customers as $category) {
            $selected = "";
            if($category['id'] == $orders['customer_id']) {
                $selected = "selected";
            }
            $customers_option.= "<option $selected value={$category['id']}>{$category['name']}</option>";
        }

        $this->datas['order_id'] = $id;
        $this->datas['bill_no'] = $orders->bill_id;
        $this->datas['vendor'] = $customers_option;
        $this->datas['route'] = route("purchase.update", $id);
        $this->datas['category'] = $category_option;
        Session::forget('products');
        return view('purchase_orders.edit')->with($this->datas);
    }

    public function show($id)
    {
        // Use findOrFail to automatically handle cases where the order is not found.
        $order = PurchaseOrderModel::select(
            'purchase_order.id',
            'purchase_order.bill_id',
            'purchase_order.customer_id',
            'purchase_order.date',
            'purchase_order.order_discount',
            'purchase_order.total',
            'purchase_order.status',
            'vendor.name as customerName', // Already using correct table and alias
            'users.name as userName'
        )
        ->leftjoin('vendor', 'purchase_order.customer_id', '=', 'vendor.id')
        ->leftjoin('users', 'purchase_order.created_by', '=', 'users.id')
        ->findOrFail($id); // THIS IS THE FIX. Changed from where()->first()

        $order_items = PurchaseOrderDetailModel::select(
                        'purchase_order_details.quantity', 
                        'purchase_order_details.quantity_price', 
                        'purchase_order_details.amount', 
                        'purchase_order_details.gst', 
                        'category.name as category_name', 
                        'products.name as product_name', 
                        'purchase_order_details.tax')
                        ->leftjoin('category', 'purchase_order_details.category_id', '=', 'category.id')
                        ->leftjoin('products', 'purchase_order_details.product_id', '=', 'products.id')
                        ->where('order_id', $id)->get();
                        
        $this->datas['order'] = $order;
        $this->datas['order_items'] = $order_items;
        return view('purchase_orders.view')->with($this->datas);
    }

    public function store(Request $request)
    {   
        $validator = Validator::make($request->all(), [
            'billno' => 'unique:purchase_order,bill_id'
        ]);

        if ($validator->fails())
        {
            $output = array('success' => 0, 'msg' => $validator->errors()->first());
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }
        $data = [];
        $data['bill_id'] = $request->billno;
        $data['bill_no'] = $request->billno;
        $data['customer_id'] = $request->cust_id;
        $data['order_discount'] = $request->discount;
        $data['date'] = $request->date;
        $data['created_by'] = Auth::id();
        $data['total'] = Session::get('cart_total');
        $order = PurchaseOrderModel::create($data);
        PurchaseOrderModel::where('id', $order['id'])->update(["bill_no" => sprintf('%08s', $order['id'])]);
        foreach(Session::get('products') as $cartArray) {
            $order_details = [];
            $order_details['order_id'] = $order['id'];
            $order_details['product_id'] = $cartArray['id'];
            $order_details['category_id'] = $cartArray['category_id'];
            $order_details['quantity'] = $cartArray['count'];
            $order_details['amount'] = $cartArray['tgst'];
            $order_details['discount'] = 0;
            $order_details['quantity_price'] = $cartArray['purchase_rate'];
            $order_details['free_item'] = 0;
            $order_details['tax'] = $cartArray['additional_tax'];
            $order_details['gst'] = $cartArray['gst'];
            PurchaseOrderDetailModel::create($order_details);

            // Stock add
            $stock = [];
            $stock['type'] = "purchase";
            $stock['bill'] = sprintf('%08s', $order['id']);
            $stock['date'] = $data['date'];
            $stock['product_id'] = $cartArray['id'];
            $stock['category_id'] = $cartArray['category_id'];
            $stock['hsn_id'] = $cartArray['hsn'];
            $stock['purchase'] = $cartArray['count'];
            StockModel::create($stock);
        }
        
        $output = array('success' => 1, 'msg' => 'Order Created Successfully');
        return redirect()->route('purchase.index')->with('status', $output);
    }

    public function getOrderList(Request $request)
    {
        $visits = PurchaseOrderModel::select(
                        'purchase_order.id',
                        'purchase_order.bill_id',
                        'purchase_order.customer_id',
                        'purchase_order.date',
                        'purchase_order.order_discount',
                        'purchase_order.total',
                        'purchase_order.status',
                        'vendor.name as customerName',
                        'users.name as userName',
                    )
                    ->leftjoin('vendor', 'purchase_order.customer_id', '=', 'vendor.id')
                    ->leftjoin('users', 'purchase_order.created_by', '=', 'users.id')
                    ->orderBy('purchase_order.created_at', 'DESC');
        
        $limit = $request->input('length');
        $offset = $request->input('start');
        $datasCount = $visits->count();
        $visits = $visits->offset($offset)->limit(25)->get();
        $datalist = [];
        $i = 0;
        foreach($visits as $list)
        {
            $list->sno = ++$i .'';
            $list->action       = '<td align="center">
                                        <a class="pl-2" href="'.route('purchase.show', [$list->id]).'"><i style="color: primary;" class="fa fa-eye"></i></a>
                                        <a class="pl-2" href="'.route('purchase.edit',[$list->id]).'"><i style="color: green;" class="fa fa-edit"></i></a>
                                   </td>';
            $datalist[] = $list;
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
// ->orderBy('customer.created_at', 'ASC')->simplePaginate(25);
// <?php echo $customer_data->render(); ?>