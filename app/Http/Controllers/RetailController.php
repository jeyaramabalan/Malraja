<?php

namespace App\Http\Controllers;

use App\Models\CategoryModel;
use App\Models\CollectionModel;
use App\Models\CustomerModel;
use App\Models\RetailDetailModel;
use App\Models\RetailModel;
use App\Models\ProductsModel;
use App\Models\RetailRetailStockModel;
use App\Models\RetailStockModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Mpdf\Mpdf;

class RetailController extends Controller
{
    public function index(Request $request)
    {
        $this->datas['route'] = route("retail.create");
        return view('retail.retail')->with($this->datas);
    }

    public function create(Request $request)
    {
        $categories = CategoryModel::select('id', 'name')->where('id', 229)->get();
        $category_option = "";
        foreach($categories as $category) {
            $category_option.= "<option value={$category['id']}>{$category['name']}</option>";
        }
        $customers = CustomerModel::all();
        $customers_option = "";
        foreach($customers as $category) {
            $customers_option.= "<option value={$category['id']}>{$category['name']}</option>";
        }

        $this->datas['customers'] = $customers_option;
        Session::forget('products');
        $this->datas['route'] = route("retail.store");
        $this->datas['category'] = $category_option;
        return view('retail.add')->with($this->datas);
    }

    public function posStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required',
        ]);

        if ($validator->fails())
        {
            $output = array('success' => 0, 'msg' => $validator->errors()->first());
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }

        if(Session::get('products') == null || count(Session::get('products')) == 0) {
            $output = array('success' => 0, 'msg' => "Add any product");
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }

        
        $data = [];
        $data['bill_id'] = time();
        $data['type'] = 1;
        $data['payment_method'] = $request->payment_method_id;

        // if UPI store in seperate
        if($request->payment_method_id == "Mixed") {
            $data['upi'] = $request->upi;    
        }

        $data['customer_id'] = 1;//$request->cust_id;
        $data['order_discount'] = $request->discount;
        $data['date'] = $request->date;
        $data['created_by'] = Auth::id();
        $data['total'] = Session::get('cart_total');
        $data['status'] = 4;
        $order = RetailModel::create($data);
        RetailModel::where('id', $order['id'])->update(["bill_id" => sprintf('%08s', $order['id'])]);
        
        foreach(Session::get('products') as $cartArray) {
            $order_details = [];
            $order_details['order_id'] = sprintf('%08s', $order['id']);
            $order_details['product_id'] = $cartArray['id'];
            $order_details['category_id'] = $cartArray['category_id'];
            $order_details['quantity'] = $cartArray['count'];
            $order_details['amount'] = $cartArray['tgst'];
            $order_details['discount'] = 0;
            $order_details['quantity_price'] = $cartArray['final_price'];
            $order_details['free_item'] = 0;
            $order_details['tax'] = $cartArray['additional_tax'];
            $order_details['gst'] = $cartArray['gst'];
            RetailDetailModel::create($order_details);

            // Stock add
            $stock = [];
            $stock['type'] = "sale";
            $stock['bill'] = sprintf('%08s', $order['id']);
            $stock['date'] = $data['date'];
            $stock['product_id'] = $cartArray['id'];
            $stock['category_id'] = $cartArray['category_id'];
            $stock['hsn_id'] = $cartArray['hsn'];
            $stock['sale'] = $cartArray['count'];
            RetailStockModel::create($stock);
        }

        if(isset($request->isPrint) && $request->isPrint == "1") {
            if(isset($request->isGstPrint) && $request->isGstPrint == "1") {
                return redirect()->route('retail-bill-gst', [$order['id']]);
            } else {
                return redirect()->route('retail-bill', [$order['id']]);
            }
        } else {
            $output = array('success' => 1, 'msg' => 'Order Created Successfully');
            return redirect()->route('new-pos-order')->with('status', $output);
        }
    }

    public function update($id, Request $request)
    {
        try{
            $data = RetailModel::find($id);
            $total = Session::get('cart_total');
            $order_update_data = [];
            $order_update_data['total'] = $total;
            $order_update_data['payment_method'] = $request->payment_method;
            if(isset($request->upi)) {
                $order_update_data['upi'] = $request->upi;
            }
            RetailModel::where('id', $id)->update($order_update_data);
            $orderDetailList = RetailDetailModel::where('order_id', $id)->get();
            foreach($orderDetailList as $key => $val ) {
                foreach(Session::get('products') as $cartArray) {
                    if($cartArray['id'] == $val['product_id']) {
                        unset($orderDetailList[$key]);
                    }
                }
            }
            
            if(count($orderDetailList) > 0) {
                foreach($orderDetailList as $detailsData) {
                    RetailDetailModel::where('id', $detailsData->id)->update(['status' => 0]);
                    RetailStockModel::where('bill', $data['bill_id'])
                            ->where('product_id', $detailsData->product_id)
                            ->update(["sale" => 0]);
                }
            }

            foreach(Session::get('products') as $cartArray) {
                $count = RetailDetailModel::where('order_id', $id)->where('product_id', $cartArray['id'])->count();
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
                $stock['bill'] = $data['bill_id'];
                $stock['date'] = $data['date'];
                $stock['product_id'] = $cartArray['id'];
                $stock['category_id'] = $cartArray['category_id'];
                $stock['hsn_id'] = $cartArray['hsn'];
                $stock['sale'] = $cartArray['count'];
                if($count > 0) {
                    RetailDetailModel::where('order_id', $id)->where('product_id', $cartArray['id'])->update($order_details);
                    RetailStockModel::where('bill', $data['bill_id'])
                            ->where('product_id', $cartArray['id'])
                            ->update(["sale" => $cartArray['count']]);
                } else {
                    $order_details['order_id'] = $id;
                    $order_details['product_id'] = $cartArray['id'];
                    $order_details['category_id'] = $cartArray['category_id'];
                    RetailStockModel::create($stock);
                    RetailDetailModel::create($order_details);
                }
            }
            $output = array('success' => 1, 'msg' => 'Retail Updated Successfully');
            return redirect()->route('retail.index')->with('status', $output);
        } catch(Exception $e) {
            $output = array('success' => 0, 'msg' => $e->getMessage());
            return redirect()->route('retail.index')->with('status', $output);
        }
    }

    public function statusUpdate(Request $request)
    {
        RetailModel::where('id', $request->id)->update(["status" => $request->status]);
        $output = array('success' => 1, 'msg' => 'Order Status Updated Successfully');
        return redirect()->route('customer.index')->with('status', $output);
    }

    public function statusUpdateAll(Request $request)
    {
        RetailModel::where('status', $request->status)->update(["status" => $request->newStatus]);
        return "";
    }

    public function edit($id)
    {
        $orders = RetailModel::find($id);
        $categories = CategoryModel::select('id', 'name')->where('id', 229)->get();
        $category_option = "";
        foreach($categories as $category) {
            $category_option.= "<option value={$category['id']}>{$category['name']}</option>";
        }
        $customers = CustomerModel::select('id', 'name')->where('status', 1)->get();
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
        $this->datas['payment_method'] = $orders->payment_method;
        $this->datas['upi'] = $orders->upi;
        $this->datas['vendor'] = $customers_option;
        $this->datas['route'] = route("order.update", $id);
        $this->datas['category'] = $category_option;
        Session::forget('products');
        return view('retail.edit')->with($this->datas);
    }

    public function getCustomerPendingPayment(Request $request)
    {
        $pendingAmount = RetailModel::where('customer_id', $request->id)
        ->where('status', '!=', 4)
        ->where('return_status', '!=', 1)
        ->sum('total');
        $collectedAmount = CollectionModel::leftjoin('order', 'collection.order_id', '=', 'order.id')
                            ->where('order.customer_id', $request->id)
                            ->where('order.status', 3)
                            ->where('collection.status', 1)
                            ->sum('collection.amount');
        echo $pendingAmount - $collectedAmount;
    }

    public function generateBill($id)
    {
        // $connector = new FilePrintConnector("/dev/usb/lp0");
        // $printer = new Printer($connector);
        // $printer -> text("Hello World!\n");
        // $printer -> cut();
        // $printer -> close();die;
        
        $head = '
                    <b>
                    NANDHA AND CO
                    </b><br>
                    No:1, TH Road 5<sup>th</sup> Lane,<br>
                    TONDIARPET, CHENNAI - 81.<br/>
                    nandhaandco@gmail.com<br/>
                    Phone : +91 9445300461<br/>
                    GSTIN : 33CZMPK0759B1ZJ
                ';
        $footer = '';

        // Bill details
        $order = RetailModel::select(
            'retail.id as order_id',
            'retail.bill_id',
            'retail.customer_id',
            'retail.date',
            'retail.order_discount',
            'retail.payment_method',
            'retail.total',
            'retail.status',
            'retail.created_at',
            'customer.name as customerName',
            'customer.address as customerAddress',
            'customer.mobile as customerMobile',
            'customer.gst as customerGST',
            'users.name as userName'
        )
        ->leftjoin('customer', 'retail.customer_id', '=', 'customer.id')
        ->leftjoin('users', 'retail.created_by', '=', 'users.id')
        ->where('retail.id', $id)->first();

        $order_items = RetailDetailModel::select(
                        'retail_details.quantity', 
                        'retail_details.quantity_price', 
                        'retail_details.amount', 
                        'retail_details.gst', 
                        'category.name as category_name', 
                        'products.name as product_name',
                        'products.tamil_name as product_tamilname',
                        'products.id as productId',
                        'retail_details.tax')
                        ->leftjoin('category', 'retail_details.category_id', '=', 'category.id')
                        ->leftjoin('products', 'retail_details.product_id', '=', 'products.id')
                        ->where('order_id', $id)
                        ->where('retail_details.status', '!=', 0)
                        ->get();
        
        $totalGst = 0;
        $totalQuantity = 0;
        foreach($order_items as $item) {
            $totalQuantity = $totalQuantity + $item->quantity;
            $totalGst = $totalGst + (($item->quantity * $item->quantity_price) * $item->gst)/100;
        }
        
        $order->bill_id = "R" . $order->bill_id;
        $this->datas['head'] = $head;
        $this->datas['order_items'] = $order_items;
        $this->datas['total_items'] = count($order_items);
        $this->datas['total_quantity'] = $totalQuantity;
        $this->datas['orders'] = $order;
        $this->datas['totalGst'] = $totalGst;
        return view('billprint')->with($this->datas);
    }

    public function generateBillgst($id)
    {
        // $connector = new FilePrintConnector("/dev/usb/lp0");
        // $printer = new Printer($connector);
        // $printer -> text("Hello World!\n");
        // $printer -> cut();
        // $printer -> close();die;
        
        $head = '
                    <b>
                    NANDHA AND CO
                    </b><br>
                    No:1, TH Road 5<sup>th</sup> Lane,<br>
                    TONDIARPET, CHENNAI - 81.<br/>
                    nandhaandco@gmail.com<br/>
                    Phone : +91 9445300461<br/>
                    GSTIN : 33CZMPK0759B1ZJ
                ';
        $footer = '';

        // Bill details
        $order = RetailModel::select(
            'retail.id as order_id',
            'retail.bill_id',
            'retail.customer_id',
            'retail.date',
            'retail.order_discount',
            'retail.payment_method',
            'retail.total',
            'retail.status',
            'retail.created_at',
            'customer.name as customerName',
            'customer.address as customerAddress',
            'customer.mobile as customerMobile',
            'customer.gst as customerGST',
            'users.name as userName'
        )
        ->leftjoin('customer', 'retail.customer_id', '=', 'customer.id')
        ->leftjoin('users', 'retail.created_by', '=', 'users.id')
        ->where('retail.id', $id)->first();

        $order_items = RetailDetailModel::select(
                        'retail_details.quantity', 
                        'retail_details.quantity_price', 
                        'retail_details.amount', 
                        'retail_details.gst', 
                        'category.name as category_name', 
                        'products.name as product_name',
                        'products.tamil_name as product_tamilname',
                        'products.id as productId',
                        'retail_details.tax')
                        ->leftjoin('category', 'retail_details.category_id', '=', 'category.id')
                        ->leftjoin('products', 'retail_details.product_id', '=', 'products.id')
                        ->where('order_id', $id)
                        ->where('retail_details.status', '!=', 0)
                        ->get();
        
        $totalGst = 0;
        $totalQuantity = 0;
        foreach($order_items as $item) {
            $totalQuantity = $totalQuantity + $item->quantity;
            $totalGst = $totalGst + (($item->quantity * $item->quantity_price) * $item->gst)/100;
        }
        
        $order->bill_id = "R" . $order->bill_id;
        $this->datas['head'] = $head;
        $this->datas['order_items'] = $order_items;
        $this->datas['total_items'] = count($order_items);
        $this->datas['total_quantity'] = $totalQuantity;
        $this->datas['orders'] = $order;
        $this->datas['totalGst'] = $totalGst;
        return view('billPrintGST')->with($this->datas);
    }

    public function generateBillOld($id)
    {
        $head = '
                    <b>
                    NANDHA AND CO
                    </b><br>
                    No:1, TH Road 5<sup>th</sup> Lane,<br>
                    TONDIARPET, CHENNAI - 81.<br/>
                    nandhaandco@gmail.com<br/>
                    Phone : +91 9445300461<br/>
                    GSTIN : 33CZMPK0759B1ZJ
                ';
        $footer = '';

        // Bill details
        $order = RetailModel::select(
            'order.id as order_id',
            'order.bill_id',
            'order.customer_id',
            'order.date',
            'order.order_discount',
            'order.total',
            'order.status',
            'customer.name as customerName',
            'customer.address as customerAddress',
            'customer.mobile as customerMobile',
            'customer.gst as customerGST',
            'users.name as userName',
        )
        ->leftjoin('customer', 'order.customer_id', '=', 'customer.id')
        ->leftjoin('users', 'order.created_by', '=', 'users.id')
        ->where('order.id', $id)->first();

        $order_items = RetailDetailModel::select(
                        'order_details.quantity', 
                        'order_details.quantity_price', 
                        'order_details.amount', 
                        'order_details.gst', 
                        'category.name as category_name', 
                        'products.name as product_name',
                        'products.id as productId',
                        'order_details.tax')
                        ->leftjoin('category', 'order_details.category_id', '=', 'category.id')
                        ->leftjoin('products', 'order_details.product_id', '=', 'products.id')
                        ->where('order_id', $id)
                        ->where('order_details.status', '!=', 0)
                        ->get();

        $client = '<b>To :</b><br/>';
        $client.= "{$order['customerName']}<br/>";      
        $client.=''.$order['customerAddress'].',<br>Phone : ';
        $client.=''.$order['customerMobile'].'<br>GSTIN :';
        $client.=''.$order['customerGST'];
        $client.='';
        $client1= '<b>INVOICE NO : '.$order['bill_id'].'</b><br/><br/>';
        $client1.= 'Date : '.date('d-m-Y', strtotime($order['date'])).'<br/><br/>';
        $client1.= 'Billed By : '.$order['userName'].'<br/><br/>';

        $style = '
                <style>
                    body
                    { 
                        font-family: "Times New Roman", Times, serif;
                        background: url("backgroundlogo.png") no-repeat center center fixed; 
                        -webkit-background-size: 20%;
                        -moz-background-size: 50%;
                        -o-background-size: 50%;
                        background-size: 50%;
                    }
                </style>';
        $header = ' <div class="row" style=" min-height:5%;max-height:5%">
                    <div class="col-sm-4" style="width:30%; float:left; padding:0px 0 0;font-size:12px; font-weight:400;">'.$head.'</div>
                    <div class="col-sm-4" style="width:40%; float:left; padding:0px 0 0 5px;font-size:12px; font-weight:400;">
                    '.$client.'</div>
                    <div class="col-sm-4" style="width:28%; float:right; text-align:right; padding:0px 0 0 5px;font-size:12px; font-weight:400;">
                    '.$client1.'</div>
                    </div>
                ';

        $output='<div height="250">
                <table height="250
                " width = "100%"  cellspacing="0" cellpadding="1" border="2" 
                style = "border:1px solid black;border-collapse: collapse;font-size: 11px;">
                <tr style="line-height: 9px;border:1px solid black; font-size:10px; font-weight:bold;">
                <th align="center" width="25" style = "border:1px solid black;">S.No</th>
                <th  align="center" width="45" style = "border:1px solid black;">HSN/ SAC</th>
                <th align="center"  width="170" style = "border:1px solid black;">PRODUCT NAME</th>
                <th align="center"  width="35" style = "border:1px solid black;">UNIT</th>
                <th  align="center" width="35" style = "border:1px solid black;">QTY</th>
                <th align="center"  width="35" style = "border:1px solid black;">FREE</th>
                <th align="center"  width="40" style = "border:1px solid black;">RATE</th>
                <th align="center"  width="35" style = "border:1px solid black;">DISC</th>
                <th align="center"  width="35" style = "border:1px solid black;">GST<br/>%</th>
                <th align="center"  width="45" style = "border:1px solid black;">GST<br/>AMT</th>
                <th align="center"  width="35" style = "border:1px solid black;">ADT<br/>%</th>
                <th align="center"  width="40" style = "border:1px solid black;">ADT<br/>AMT</th>
                <th align="center"  width="60" style = "border:1px solid black;">TOTAL</th>
                </tr>';

            $sno=1;
            $overalltotal = $order['total'];
            $qu=0;
            $fr=0;
            $gst=0;
            $rate1=0;
            $rate5=0;
            $rate12=0;
            $rate18=0;
            $rate28=0;
            $gst1p=0;
            $gst5p=0;
            $gst12p=0;
            $gst18p=0;
            $gst28p=0;
            $gst1a=0;
            $gst5a=0;
            $gst12a=0;
            $gst18a=0;
            $gst28a=0;
            $mpc = count($order_items);
            if($mpc%7==0) {
                $mpc = 1;
            } else {
                $mpc=0;
            }
            foreach($order_items as $items)
            {
                $qu = $qu + $items["count"];
                $fr=$fr+$items["free"];
                $sgst=$items["gstp"]/2;
                $cgst=$items["gstp"]/2; 

                $output.= '<tr>';
                $output.= '<td align="center" style="height:0.7cm" >'.$sno.'</td>';
                $output.= '<td align="center" style="border-right:1px solid black;border-left:1px solid black;" >'.ProductController::getProductHsn($items['productId']).'</td>';
                $output.= '<td style="border-right:1px solid black;padding-left:4px">'.$items["product_name"].'</td>';
                $output.= '<td align="center"  style="border-right:1px solid black;">'.ProductController::getProductUnit($items['productId']).'</td>';
                $output.= '<td align="center" style="border-right:1px solid black;">'.$items["quantity"].'</td>';
                $output.= '<td align="center" style="border-right:1px solid black;">'.$items["free"].'</td>';
                $output.= '<td align="right" style="border-right:1px solid black;padding-right:4px">'.number_format($items["price"],2).'</td>';
                $output.= '<td align="center" style="border-right:1px solid black;">'.$items["discount"].'</td>';
                $output.= '<td align="center" style="border-right:1px solid black;">'. $items["gst"].'</td>';
                $output.= '<td align="right" style="border-right:1px solid black;padding-right:4px">'.number_format(($items['final_price'] * $items["gst"]) / 100, 2).'</td>';
                $output.= '<td align="center" style="border-right:1px solid black;padding-right:4px">'.$items["tax"].'</td>';
                $output.= '<td style="border-right:1px solid black;text-align:right;padding-right:4px">'.number_format(($items['final_price'] * $items["tax"]) / 100, 2).'</td>';
                $output.= '<td align="right" style="border-left-width:0.1px; border-right-width:0.1px;padding-right:4px">'.number_format($order["total"],2).'</td>';

                // $gst=$gst+(number_format($items["gamt"],2));
                // $gst=$gst+($items["gamt"]);
              
                           
                              if (($items["gstp"]) == 0)
                              {
                                  $rate1 = $rate1 + (number_format($items["price"], 2));
                                  // $gst1p = ($rate1 * 1) / 100;
                                  // $gst1a = $gst1a + $items["gamt"];
                                  $gst1p = (number_format((($rate1 * 1) / 100), 2));
                                  // $gst1a = (number_format(($gst1a + $items["gamt"]), 2));
                                  $gst1a = ($gst1a + $items["gamt"]);
                              }
                              if (($items["gstp"]) == 5)
                              {
                                  $rate5 = $rate5 + (number_format($items["price"], 2));
                                  $gs5p = (number_format((($rate5 * 5) / 100), 2));
                                  // $gst5a = (number_format(($gst5a + $items["gamt"]), 2));
                                  $gst5a = ($gst5a + $items["gamt"]);
                              }
                              if (($items["gstp"]) == 12)
                              {
                                  $rate12 = $rate12 + (number_format($items["price"], 2));
                                  $gst12p = (number_format((($rate12 * 12) / 100), 2));
                                  // $gst12a = (number_format(($gst12a + $items["gamt"]), 2));
                                  $gst12a = ($gst12a + $items["gamt"]);
                                 
                              }
                              if (($items["gstp"]) == 18)
                              {
                                  $rate18 = $rate18 + (number_format($items["price"], 2));
                                  $gst18p = (number_format((($rate18 * 18) / 100), 2));
                                  // $gst18a = (number_format(($gst18a + $items["gamt"]), 2));
                                  $gst18a = ($gst18a + $items["gamt"]);
              
                              }
                              if (($items["gstp"]) == 28)
                              {
                                  $rate28 = $rate28 + (number_format($items["price"], 2));
                                  $gst28p = (number_format((($rate28 * 28) / 100), 2));
                                  // $gst28a = (number_format(($gst28a + $items["gamt"]), 2));
                                  $gst28a = ($gst28a + $items["gamt"]);
                            
                              }
                  
                $output.= '</tr>';
                 
                 if($mpc!=1)
                 {
                 if((($sno%7)==0))
                  {
              
              
                    $output.= '</table>';
              
                  // $output.='<tr style=" font-size:10px; font-weight:bold;">
                  // <td width="30" style = ""></td>
                  // <td  align="center" width="50" style = ""></td>
                  // <td align="center" width="135" style = ""></td>
                  // <td width="40" ></td>
                  // <td width="37">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$qu.'</td>
                  // <td width="41">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$fr.'</td>
                  // <td width="45" ></td>
                  // <td width="37" ></td>
                  // <td  width="38" ></td>
                  // <td width="35" ></td>
                  // <td width="169" ></td>
                  // <td width="55" ></td>
                  // </tr>';
              
                  $output.='<table width=100% border=0>
                  <tr style="line-height: 9px;border:1px solid black;">
                  <td align="center" width="25" ></td>
                  <td  align="center" width="85" ></td>
                  <td align="center"  width="170" ></td>
                  <td align="center"  width="35" ></td>
                  <td  align="center" width="35" style="font-size:12px;" >'.$qu.'</td>
                  <td align="center"  width="35" style="font-size:12px;">'.$fr.'</td>
                  <td align="center"  width="40" ></td>
                  <td align="center"  width="35" ></td>
                  <td align="center"  width="35" ></td>
                  <td align="center"  width="35" ></td>
                  <td align="center"  width="45" ></td>
                  <td align="center"  width="35" ></td>
                  <td align="center"  width="40" ></td>
                  <td align="center"  width="45" ></td>
                  <td align="center"  width="60" ></td>
                  </tr></table>';
                  
                  
              
                  $output.='<table border="0" cellpadding="2" cellspacing="2">
                <tr >
                <td width="80" align="left" style="font-size:12px"><b>GST %:</b><br/>
                Value:<br/>
                GST AMT:
                </td>
                <td width="50" align="right"  style="font-size:12px"><b>Exempt</b><br/>
                '.number_format($gst1p, 2).'<br/>
                '.number_format($gst1a, 2).'</td> 
                <td width="50" align="right"  style="font-size:12px"><b>5.00%</b><br/>
                '.number_format($gst5p, 2).'<br/>
                '.number_format($gst5a, 2).'</td>
                <td width="50" align="right"  style="font-size:12px"><b>12.00%</b><br/>
                '.number_format($gst12p, 2).'<br/>
                '.number_format($gst12a, 2).'</td>
                <td width="50" align="right"  style="font-size:12px"><b>18.00%</b><br/>
                '.number_format($gst18p, 2).'<br/>
                '.number_format($gst18a, 2).'</td>
                <td width="50" align="right"  style="font-size:12px"><b>28.00%</b><br/>
                '.number_format($gst28p, 2).'<br/>
                '.number_format($gst28a, 2).'</td>
                <td width="5" ></td>
                </tr>
              </table>
                  
                  
              <p style="text-align:right"><br>(Continued...)</p>';
                    $output.= '<div height="250">
                    <table  width = "100%"  cellspacing="0" cellpadding="1" border="2" 
                    style = "border:1px solid black;border-collapse: collapse;font-size: 11px;">
                    <tr style="line-height: 9px;border:1px solid black; font-size:10px; font-weight:bold;">
                    <th align="center"  width="25" style = "border:1px solid black;">S.No</th>
                    <th  align="center" width="45" style = "border:1px solid black;">HSN/ SAC</th>
                    <th align="center"  width="170" style = "border:1px solid black;">PRODUCT NAME</th>
                    <th align="center"  width="35" style = "border:1px solid black;">UNIT</th>
                    <th  align="center" width="35" style = "border:1px solid black;">QTY</th>
                    <th align="center"  width="35" style = "border:1px solid black;">FREE</th>
                    <th align="center"  width="40" style = "border:1px solid black;">RATE</th>
                    <th align="center"  width="35" style = "border:1px solid black;">MRP</th>
                    <th align="center"  width="35" style = "border:1px solid black;">DISC<br/>%</th>
                    <th align="center"  width="35" style = "border:1px solid black;">GST<br/>%</th>
                    <th align="center"  width="45" style = "border:1px solid black;">GST<br/>AMT</th>
                    <th align="center"  width="35" style = "border:1px solid black;">ADT<br/>%</th>
                    <th align="center"  width="40" style = "border:1px solid black;">ADT<br/>AMT</th>
                    <th align="center"  width="45" style = "border:1px solid black;">NET<br/>RATE</th>
                    <th align="center"  width="60" style = "border:1px solid black;">TOTAL</th>
                    </tr>';
                  }
                }
                      $sno++;   
                    }
              
                if(($i=($sno%7)==0))
                 {
                   $output.='<tr>
                   <td style="height:0.7cm"></td>
                  <td style="border-left:1px solid black;;border-right:1px solid black;padding-left:4px"></td>
                  <td style="border-right:1px solid black;padding-left:4px"></td>
                  <td style="border-right:1px solid black;padding-left:4px"></td>
                  <td style="border-right:1px solid black;padding-left:4px"></td>
                  <td style="border-right:1px solid black;padding-left:4px"></td>
                  <td style="border-right:1px solid black;padding-left:4px"></td>
                  <td style="border-right:1px solid black;padding-left:4px"></td>
                  <td style="border-right:1px solid black;padding-left:4px"></td>
                  <td style="border-right:1px solid black;padding-left:4px"></td>
                  <td style="border-right:1px solid black;padding-left:4px"></td>
                  <td style="border-right:1px solid black;padding-left:4px"></td>
                  <td style="border-right:1px solid black;padding-left:4px"></td>
                  ';
                   $output.='</tr>';
                 }
                 else
                 {
                  if($mpc!=1)
                  {
                 for($i=($sno%7);$i<=7;++$i)
                 {
                 $output.='<tr>
                 <td style="height:0.7cm"></td>
                 <td style="border-left:1px solid black;border-right:1px solid black;padding-left:4px"></td>
                 <td style="border-right:1px solid black;padding-left:4px"></td>
                 <td style="border-right:1px solid black;padding-left:4px"></td>
                 <td style="border-right:1px solid black;padding-left:4px"></td>
                 <td style="border-right:1px solid black;padding-left:4px"></td>
                 <td style="border-right:1px solid black;padding-left:4px"></td>
                 <td style="border-right:1px solid black;padding-left:4px"></td>
                 <td style="border-right:1px solid black;padding-left:4px"></td>
                 <td style="border-right:1px solid black;padding-left:4px"></td>
                 <td style="border-right:1px solid black;padding-left:4px"></td>
                 <td style="border-right:1px solid black;padding-left:4px"></td>
                 <td style="border-right:1px solid black;padding-left:4px"></td>
                  ';
                   $output.='</tr>';
                 }}
               }
                
                $output.= '</table></div>';
               
              
                $output.= '<table width = "100%" cellspacing="0" cellpadding="0" border="" style = "font-size: 12px;padding-bottom:50px;">
                <tr>
                    <th align="center"  width="25" ></th>
                    <th  align="center" width="45" ></th>
                    <th align="center"  width="170" ></th>
                    <th align="center"  width="35" ></th>
                    <th  align="center" width="35" ></th>
                    <th align="center"  width="35" ></th>
                    <th align="center"  width="40" ></th>
                    <th align="center"  width="35" ></th>
                    <th align="center"  width="35" ></th>
                    <th align="center"  width="35" ></th>
                    <th align="center"  width="45" ></th>
                    <th align="center"  width="35" ></th>
                    <th align="center"  width="40" ></th>
                    <th align="right"  width="45" >Invoice Amount</th>
                    <th align="right"  width="30" >'.number_format($overalltotal,2).'</th>
                    </tr></table></div>';
                  //   $output.='</table>';
                  $hello = '<table border="0" cellpadding="2" cellspacing="0">
                  <tr >
                  <td width="80" align="left" style="font-size:12px"><b>GST %:</b><br/>
                  Value:<br/>
                  GST AMT:
                  </td>
                  <td width="50" align="right" style="font-size:12px"><b>Exempt</b><br/>
                  '.number_format($gst1p,2).'<br/>
                  '.number_format($gst1a,2).'</td>
                  <td width="50" align="right" style="font-size:12px"><b>5.00%</b><br/>
                  '.number_format($gst5p, 2).'<br/>
                  '.number_format($gst5a, 2).'</td>
                  <td width="50" align="right" style="font-size:12px"><b>12.00%</b><br/>
                  '.number_format($gst12p, 2).'<br/>
                  '.number_format($gst12a, 2).'</td>
                  <td width="50" align="right" style="font-size:12px"><b>18.00%</b><br/>
                  '.number_format($gst18p, 2).'<br/>
                  '.number_format($gst18a, 2).'</td>
                  <td width="50" align="right" style="font-size:12px"><b>28.00%</b><br/>
                  '.number_format($gst28p, 2).'<br/>
                  '.number_format($gst28a, 2).'</td>
                  <td width="5" >
                  </td>
                 <td style="padding:10px 20px">
                  &nbsp;
                 </td>
              <td style="border:1px solid">
              Tot.GST Amt &nbsp;&nbsp;  <span style="text-align:right">&nbsp;&nbsp;&nbsp;&nbsp;'.number_format($gst, 2).'</span> <br>
              Tot.CGST Amt  <span style="text-align:right">&nbsp;&nbsp;&nbsp;&nbsp;'.number_format(($gst/2), 2).'</span> <br>
              Tot.SGST Amt  <span style="text-align:right">&nbsp;&nbsp;&nbsp;&nbsp;'.number_format(($gst/2), 2).'</span>
              </td>
              
              
              
                  
                  
                  </tr>
                  </table>';
                  // $pdf->writeHTML($hello, true, false, false, false, '');
                   $number =$overalltotal;
                   $no = floor($number);
                   $point = round($number - $no, 2) * 100;
                   $hundred = null;
                   $digits_1 = strlen($no);
                   $i = 0;
                   $str = array();
                   $words = array('0' => '', '1' => 'one', '2' => 'two',
                    '3' => 'three', '4' => 'four', '5' => 'five', '6' => 'six',
                    '7' => 'seven', '8' => 'eight', '9' => 'nine',
                    '10' => 'ten', '11' => 'eleven', '12' => 'twelve',
                    '13' => 'thirteen', '14' => 'fourteen',
                    '15' => 'fifteen', '16' => 'sixteen', '17' => 'seventeen',
                    '18' => 'eighteen', '19' =>'nineteen', '20' => 'twenty',
                    '30' => 'thirty', '40' => 'forty', '50' => 'fifty',
                    '60' => 'sixty', '70' => 'seventy',
                    '80' => 'eighty', '90' => 'ninety');
                   $digits = array('', 'hundred', 'thousand', 'lakh', 'crore');
                   while ($i < $digits_1) {
                     $divider = ($i == 2) ? 10 : 100;
                     $number = floor($no % $divider);
                     $no = floor($no / $divider);
                     $i += ($divider == 10) ? 1 : 2;
                     if ($number) {
                        $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                        $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                        $str [] = ($number < 21) ? $words[$number] .
                            " " . $digits[$counter] . $plural . " " . $hundred
                            :
                            $words[floor($number / 10) * 10]
                            . " " . $words[$number % 10] . " "
                            . $digits[$counter] . $plural . " " . $hundred;
                     } else $str[] = null;
                  }
                  $str = array_reverse($str);
                  $result = implode('', $str);
                  $points = ($point) ?
                    "." . $words[$point / 10] . " " . 
                          $words[$point = $point % 10] : '';
                     $rswords=$result."Rupees Only.";
                  // $pdf->SetFont('times', 'N', 10);
                  $output1 = '';
                  $output1 = '<table border="0" cellpadding="2" cellspacing="2" width=100%>
                  <tr>
                  <td align="left"><b  style="text-transform:capitalize;font-size:13px;">'.$rswords.'</b><br/><br/>
                  <span style="font-size:10px;">Received the above mentioned goods in good condition.<br/>
                  Customer Signature With Seal</span>
                  </td>
                  <td align="right">
                  <b style="font-size:16px;">For Malraja Traders</b><br/><br/><br/>
                  Authorised Signatory
                  </td>
                  </tr>
                  </table>';

        // echo $header;
        // echo $output;
        // echo $output1;die;
        $pdf = new mPDF();
        $pdf->SetDisplayMode('fullpage');
        $pdf->SetHTMLHeader($header, '0');
        $pdf->AddPage('Times New Roman','L','A4','','',5,6,37,5,5,10); // ,LEFT,RIGHT,
        $pdf->WriteHTML($style);
        $pdf->WriteHTML($output);
        // $pdf->WriteHTML($hello);
        $pdf->WriteHTML($output1);
        return $pdf->Output();
    }

    public function show($id)
    {
        $order = RetailModel::select(
            'retail.id',
            'retail.bill_id',
            'retail.customer_id',
            'retail.date',
            'retail.order_discount',
            'retail.total',
            'retail.status',
            'customer.name as customerName',
            'users.name as userName',
        )
        ->leftjoin('customer', 'retail.customer_id', '=', 'customer.id')
        ->leftjoin('users', 'retail.created_by', '=', 'users.id')
        ->where('retail.id', $id)->first();

        $order_items = RetailDetailModel::select(
                        'retail_details.quantity', 
                        'retail_details.quantity_price', 
                        'retail_details.amount', 
                        'retail_details.gst', 
                        'category.name as category_name', 
                        'products.name as product_name', 
                        'products.name as product_name', 
                        'retail_details.tax')
                        ->leftjoin('category', 'retail_details.category_id', '=', 'category.id')
                        ->leftjoin('products', 'retail_details.product_id', '=', 'products.id')
                        ->where('order_id', $id)
                        ->where('retail_details.status', '!=', 0)
                        ->get();
                        

        $this->datas['order'] = $order;
        $this->datas['order_items'] = $order_items;
        return view('orders.view')->with($this->datas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cust_id' => 'required',
            'date' => 'required',
        ]);
        
        if ($validator->fails())
        {
            $output = array('success' => 0, 'msg' => $validator->errors()->first());
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }

        if ($request->cust_id == "0" || $request->cust_id == 0)
        {
            $output = array('success' => 0, 'msg' => "Please select customer");
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }

        if(Session::get('products') == null || count(Session::get('products')) == 0) {
            $output = array('success' => 0, 'msg' => "Add any product");
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }
        
        $data = [];
        $data['bill_id'] = time();
        $data['type'] = 1;
        $data['payment_method'] = $request->payment_method_id;

        // if UPI store in seperate
        if($request->payment_method_id == "Mixed") {
            $data['upi'] = $request->upi;    
        }

        $data['customer_id'] = $request->cust_id;
        $data['order_discount'] = 0;
        $data['date'] = $request->date;
        $data['created_by'] = Auth::id();
        $data['total'] = Session::get('cart_total');
        if(isset($request->ispaid)) {
            $data['status'] = 4;   
        } else {
            $data['status'] = 1;
        }
        $order = RetailModel::create($data);
        RetailModel::where('id', $order['id'])->update(["bill_id" => sprintf('%08s', $order['id'])]);
        foreach(Session::get('products') as $cartArray) {
            $order_details = [];
            $order_details['order_id'] = sprintf('%08s', $order['id']);
            $order_details['product_id'] = $cartArray['id'];
            $order_details['category_id'] = $cartArray['category_id'];
            $order_details['quantity'] = $cartArray['count'];
            $order_details['amount'] = $cartArray['tgst'];
            $order_details['discount'] = 0;
            $order_details['quantity_price'] = $cartArray['final_price'];
            $order_details['free_item'] = 0;
            $order_details['tax'] = $cartArray['additional_tax'];
            $order_details['gst'] = $cartArray['gst'];
            RetailDetailModel::create($order_details);

            // Stock add
            $stock = [];
            $stock['type'] = "sale";
            $stock['bill'] = sprintf('%08s', $order['id']);
            $stock['date'] = $data['date'];
            $stock['product_id'] = $cartArray['id'];
            $stock['category_id'] = $cartArray['category_id'];
            $stock['hsn_id'] = $cartArray['hsn'];
            $stock['sale'] = $cartArray['count'];
            RetailStockModel::create($stock);
        }

        if(isset($request->ischeck)) {
            return redirect()->route('order-bill', [$order['id']]);
        } else {
            $output = array('success' => 1, 'msg' => 'Order Created Successfully');
            return redirect()->route('retail.index')->with('status', $output);
        }
        
    }

    public function getOrderCart(Request $request)
    {
        $orderItems = RetailDetailModel::where('order_id', $request->id)->get();
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
                'category.name as categoryName',
                'unit.unit as unit'
                )
                ->leftjoin('category', 'products.category_id', '=', 'category.id')
                ->leftjoin('unit', 'products.unit', '=', 'unit.id')
                ->where('products.id', $orderItem->product_id)
                ->first();
                
                $productPrice = 0;
                $productPrice = $orderItem->quantity_price;
                $productQuantity = $orderItem->quantity;
                $gstAmount = ($productPrice * $product['gst']) / 100;
                $gstPercentage = $product['gst'];
                $taxPercentage = $product['tax'];
                $taxAmount = ($productPrice * $product['tax']) / 100;
                $productTotal = $productPrice * $orderItem->quantity;
    
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
        $products = Session::get('products');
        if($products != null) {
            foreach(Session::get('products') as $cartArray) {
            $total = $total + $cartArray['tgst'];
            $i = $i+1;
            $output .= "<tr>
            <td> $i</td>
            <td>{$cartArray['categoryName']}</td>
            <td>{$cartArray['name']}</td>

            <td><div class='input-group' style = 'width:157px;'><a onclick='minusCartQty({$cartArray['id']}, {$cartArray['count']})' class='minus-item input-group-addon btn btn-outline-warning'  data-name='{$cartArray['name']}'   data-dis = '  {$cartArray['dis']}  '    data-tgst = '  {$cartArray['tgst']}  '  data-price = '  {$cartArray['price']}  '   data-cprice = '  {$cartArray['customer_rate']}  '  data-ori_price = '  {$cartArray['ori_price']}  ' data-gstp = '  {$cartArray['gstp']}  '  data-count = '  {$cartArray['count']}  ' data-adtp = '  {$cartArray['adtp']}  '   style = 'width:5px;'     ><i class='fa fa-minus' aria-hidden='true' style = 'margin-left:-5px;' ></i></a>
            <input style = 'text-align:center;' type='text' id = 'number{$cartArray['id']}' min = '0'  class='item-count form-control' data-name='  {$cartArray['name']}' data-qty=' {$cartArray['count']}'   data-tgst = '  {$cartArray['tgst']}  '  data-dis = '  {$cartArray['dis']}  '  data-price = '  {$cartArray['price']}  ' data-cprice = '  {$cartArray['customer_rate']}  ' data-ori_price = '  {$cartArray['ori_price']}  '  data-gstp = '  {$cartArray['gstp']}  ' data-adtp = '  {$cartArray['adtp']}  '  value='  {$cartArray['count']}  '>
            <a id = 'myqty{$cartArray['id']}' onclick='plusCartQty({$cartArray['id']}, {$cartArray['count']})' class='plus-item btn btn-outline-warning input-group-addon' data-category = '  {$cartArray['category']}  '  data-count = '  {$cartArray['count']}  '   data-dis = '  {$cartArray['dis']}  '   data-name='  {$cartArray['name']}  '   data-name='  {$cartArray['name']}  '     data-tgst = '  {$cartArray['tgst']}  '  data-price = '  {$cartArray['price']}  '   data-cprice = '  {$cartArray['customer_rate']}  '    data-ori_price = '  {$cartArray['ori_price']}  '  data-gstp = '  {$cartArray['gstp']}  '  data-adtp = '  {$cartArray['adtp']}  '  style = 'width:5px;'   ><i class='fa fa-plus' aria-hidden='true'  style = 'margin-left:-5px;'  ></i></a></div></td>
        
            <td>{$cartArray['unit']}</td>   
            <td><input id='itemrate{$cartArray['id']}' style = 'text-align:center; width:100px;' type='text' data-id='{$cartArray['id']}' class='item-rate form-control' value='{$cartArray['final_price']}'></td>
            

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

    public function getRetailOrderList(Request $request)
    {
        $visits = RetailModel::select(
                        'retail.id',
                        'retail.bill_id',
                        'retail.type',
                        'retail.customer_id',
                        'retail.date',
                        'retail.payment_method',
                        'retail.order_discount',
                        'retail.total',
                        'retail.status',
                        'customer.name as customerName',
                        'users.name as userName',
                    )
                    ->leftjoin('customer', 'retail.customer_id', '=', 'customer.id')
                    ->leftjoin('users', 'retail.created_by', '=', 'users.id');
        
        if(isset($request->status) && $request->status != "Select Status") {
            $visits = $visits->where('retail.status', $request->status);
        } else {
            $visits = $visits->where('retail.return_status', 0);
        }

        if(isset($request->types) && $request->types != "") {
            $visits = $visits->where('retail.type', $request->types);
        }

        if(isset($request->date) && !empty($request->date)) {
            $date = explode("-", $request->date);
            $sDate = date("Y-m-d", strtotime($date[0]));
            $eDate = date("Y-m-d", strtotime($date[1]));
            $visits = $visits->whereBetween('retail.date', [$sDate, $eDate]);
        }

        if(isset($request->search) && isset($request->search['value']) && $request->search['value'] != '') {
            $visits = $visits->where('retail.id', 'like', '%'.$request->search['value'].'%');
            $visits = $visits->orwhere('customer.name', 'like', '%'.$request->search['value'].'%');
        }

        $limit = $request->input('length');
        $offset = $request->input('start');

        $datasCount = $visits->count();
        $visits = $visits->offset($offset)->limit(25)
        ->orderBy('retail.created_at', 'DESC')->get();
        $datalist = [];
        $i = 0;
        foreach($visits as $list)
        {
            $list->sno = ++$i .'';
            $status = '';
            $orderStatus = '';
            $return = '';
            if($list->type == 1) {
                $list->type = "Delivery";
            } else {
                $list->type = "POS";
            }
            $list->paymentPending = 0;
            if($list->status != 4) {
                $paymentPending = CollectionModel::where('order_id', $list->id)->sum('amount');
                $list->paymentPending = $list->total - $paymentPending;
            }
            if($list->return_status != 1 && (Auth::user()->role == 1 || Auth::user()->role == 2 || Auth::user()->role == 3)) {
                $orderStatus = 'Returned';
                $return = '<a class="pl-2" href="'.route('return.edit', [$list->id]).'"><i style="color: red;" class="fa fa-trash"></i></a>';
            }
            if($list->status == 1) {
                $orderStatus = 'Confirmed';
                $status = '<button class="pl-2 btn btn-primary" id="update-btn'.$list->id.'" onclick="updateStatus('.$list->id.', 2)">Move to Delivery</button>';
            }
            if($list->status == 2) {
                $orderStatus = 'Pending Delivery';
                $status = '<button class="pl-2 btn btn-warning" id="update-btn'.$list->id.'" onclick="updateStatus('.$list->id.', 3)">Move to Payment</button>';
            }
            if($list->status == 3) {
                $orderStatus = 'Pending Payment';
                $status = '<button class="pl-2 btn btn-danger" id="update-btn'.$list->id.'" onclick="showModal('.$list->id.', 4, '. "'{$list->payment_method}'" .', '.$list->paymentPending.')">Move to Completed</button>';
            }
            if($list->status == 4) {
                $orderStatus = 'Completed';
                $status = '';
                // <button class="pl-2 btn btn-danger" id="update-btn'.$list->id.'" onclick="updateStatus('.$list->id.', 5)">Return Bill</button>
            }
            $list->action       = '<td align="center">

                                        <a class="pl-2" href="'.route('retail-bill', [$list->id]).'"><i style="color: orange;" class="fa fa-print"></i></a>
                                        <a class="pl-2" href="'.route('retail-bill-gst', [$list->id]).'"><i style="color: blue;" class="fa fa-print"></i></a>
                                        <a class="pl-2" href="'.route('retail.show', [$list->id]).'"><i style="color: primary;" class="fa fa-eye"></i></a>
                                   </td>';
            
            $list->orderStatus = $orderStatus;
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

            $currentStock = 100;
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


}
// ->orderBy('customer.created_at', 'ASC')->simplePaginate(25);
// <?php echo $customer_data->render(); ?>