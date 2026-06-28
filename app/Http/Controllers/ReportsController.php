<?php

namespace App\Http\Controllers;

use App\Models\CategoryModel;
use App\Models\CollectionModel;
use App\Models\CustomerModel;
use App\Models\HsnModel;
use App\Models\OrderDetailModel;
use App\Models\OrderModel;
use App\Models\ProductsModel;
use App\Models\PurposeVisitModel;
use App\Models\StockModel;
use App\Models\User;
use App\Models\VisitsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as FacadesExcel;

class ReportsController extends Controller
{
    public function dailyProfitIndex(Request $request)
    {
        return view('reports.daily_profit');
    }

    public function dailyProductIndex(Request $request)
    {
        return view('reports.daily_product');
    }

    public function paymentPendingIndex(Request $request)
    {
        return view('reports.pending_payment_report');
    }

    public function paymentPendingPrint(Request $request)
    {
        $visits = OrderModel::select(
            'order.id',
            'order.bill_id',
            'order.type',
            'order.date',
            'order.payment_method',
            'order.total',
            'order.status',
            'customer.name as customerName',
            'users.name as userName',
        )
        ->leftjoin('customer', 'order.customer_id', '=', 'customer.id')
        ->leftjoin('users', 'order.created_by', '=', 'users.id')
        ->where('order.status', 3);

        $sDate = date("d-m-Y");
        $eDate = date("d-m-Y");
        if(isset($request->date) && !empty($request->date)) {
            $date = explode("-", $request->date);
            $sDate = date("Y-m-d", strtotime($date[0]));
            $eDate = date("Y-m-d", strtotime($date[1]));
            $visits = $visits->whereBetween('order.date', [$sDate, $eDate]);
        }

        $visits = $visits->orderBy('order.created_at', 'DESC')->get();
        $collectionSums = CollectionModel::select('order_id', DB::raw('SUM(amount) as total_paid'))
            ->whereIn('order_id', $visits->pluck('id')->toArray())
            ->groupBy('order_id')
            ->pluck('total_paid', 'order_id');
        $datalist = [];
        $i = 0;
        foreach($visits as $list)
        {
            if($list->type == 1) {
                $list->type = "Delivery";
            } else {
                $list->type = "POS";
            }
            $list->paymentPending = 0;
            if($list->status != 4) {
                $paymentPending = $collectionSums[$list->id] ?? 0;
                $list->paymentPending = $list->total - $paymentPending;
            }
            $datalist[] = $list;
        }
        $sDate = date("d-m-Y", strtotime($sDate));
        $eDate = date("d-m-Y", strtotime($eDate));
        $this->datas['completeData'] = $datalist;
        $this->datas['date'] = "$sDate : $eDate";
        return view('reports.pending_payment_report_bill')->with($this->datas);
    }

    public function stockReport(Request $request)
    {
        $categories = CategoryModel::select('id', 'name')->get();
        $category_option = "";
        foreach($categories as $category) {
            $category_option.= "<option value={$category['id']}>{$category['name']}</option>";
        }
        $customers = HsnModel::all();
        $customers_option = "";
        foreach($customers as $category) {
            $customers_option.= "<option value={$category['id']}>{$category['hsn']}</option>";
        }
        $this->datas['category'] = $category_option;
        $this->datas['hsn'] = $customers_option;
        $this->datas['route'] = route('get-stock-report');
        return view('reports.stock_report')->with($this->datas);
    }

    public function dailyProductGet(Request $request)
    {
        $date = date('Y-m-d');
        // $orders = ProductsModel::select('products.name as productName', DB::raw('SUM(sale) as saleCount'))
        //             ->leftjoin('stock', 'products.id', '=', 'stock.product_id');
        $orders = StockModel::select('products.id', 'products.name as productName', DB::raw('SUM(sale) as saleCount'))
                    ->leftjoin('products', 'stock.product_id', '=', 'products.id')
                    ->leftjoin('order', 'stock.bill', '=', 'order.bill_id');
        $date = explode("-", $request->date);
        // $sDate = date("Y-m-d", strtotime($date[0])) . " 00:00:00";
        // $eDate = date("Y-m-d", strtotime($date[1])) . " 23:59:59";
        $sDate = date("Y-m-d", strtotime($date[0]));
        $eDate = date("Y-m-d", strtotime($date[1]));
        $orders = $orders->whereBetween('order.date', [$sDate, $eDate]);
        $orders = $orders->groupBy('stock.product_id')->orderBy(DB::raw('SUM(sale)'), 'DESC')->get();
        $saleAmounts = OrderDetailModel::select('product_id', DB::raw('SUM(amount) as saleAmount'))
            ->where('status', 1)
            ->whereBetween('created_at', ["$sDate 00:00:00", "$eDate 23:59:59"])
            ->groupBy('product_id')
            ->pluck('saleAmount', 'product_id');
        
        $output = '';
        $output .='
            <table class="mt-4 table table-bordered table-striped export-table no-footer dtr-inline dataTable" width="100%" border="1" cellpadding="2" cellspacing="0">
                <tr>
                <th>SNO</th>
                <th>Product Name</th>
                <th>Sale Count</th>
                <th>Sale Amount</th>
            </tr>
            ';
        $i = 1;
        foreach($orders as $order)
        {
            if(empty($order['saleCount'])) {
                continue;
            }
            $saleAmount = $saleAmounts[$order["id"]] ?? 0;
            $output .= '
            <tr>
            <td>'.$i.'</td>
            <td>'.$order["productName"].'</td>
            <td class= "text-center">'.$order["saleCount"].'</td>
            <td class= "text-right">'.number_format($saleAmount, 2).'</td>
            </tr>';
            $i = $i + 1;
        }
          
        $output .='
        </table>';

        // header('Content-Type: application/xls');
        // header('Content-Disposition: attachment; filename=report-profit.xls');
        echo json_encode($output);
    }

    public function getSaleAmount($productId, $sDate, $eDate) {
        $orderData = OrderDetailModel::select(DB::raw('SUM(quantity) as saleQuantity'), DB::raw('SUM(amount) as saleAmount'))
                        ->where('product_id', $productId)
                        ->where('status', 1)
                        ->whereBetween('created_at', ["$sDate 00:00:00", "$eDate 23:59:59"])->get();
        
        return $orderData[0];
        
    }

    public function dailyProfitGet(Request $request)
    {
        $stagename = ["", "Confirmed", "Delivery Pending", "Payment Pending", "Completed"];
        $orderData = [];
        $statusArray = [1,2,3,4];
        $date = explode("-", $request->date);
        $sDate = date("Y-m-d", strtotime($date[0]));
        $eDate = date("Y-m-d", strtotime($date[1]));

        $ordersQuery = OrderModel::select('id', 'status')
            ->whereBetween('date', [$sDate, $eDate])
            ->whereIn('status', $statusArray);
        if(isset($request->payment_method_id) && $request->payment_method_id != "") {
            $ordersQuery = $ordersQuery->where('payment_method', $request->payment_method_id);
        }
        $orders = $ordersQuery->get();
        $ordersByStatus = [];
        foreach ($orders as $order) {
            $ordersByStatus[$order->status][] = $order->id;
        }

        $orderIds = $orders->pluck('id')->toArray();
        $details = [];
        if(!empty($orderIds)) {
            $details = OrderDetailModel::select(
                    'order_details.order_id',
                    'order_details.amount',
                    'order_details.quantity',
                    'order_details.gst',
                    'products.purchase_rate'
                )
                ->leftJoin('products', 'order_details.product_id', '=', 'products.id')
                ->whereIn('order_details.order_id', $orderIds)
                ->where('order_details.status', 1)
                ->get();
        }

        $summaryByStatus = [];
        foreach($statusArray as $status) {
            $summaryByStatus[$status] = [
                'count' => isset($ordersByStatus[$status]) ? count($ordersByStatus[$status]) : 0,
                'amount' => 0,
                'tax' => 0,
                'purchase' => 0,
            ];
        }

        $orderStatusMap = $orders->pluck('status', 'id')->toArray();
        foreach($details as $orderDetail) {
            $status = $orderStatusMap[$orderDetail->order_id] ?? null;
            if(!$status) {
                continue;
            }
            $summaryByStatus[$status]['amount'] += (float)$orderDetail->amount;
            $summaryByStatus[$status]['tax'] += ((float)$orderDetail->amount * (float)$orderDetail->gst) / 100;
            $summaryByStatus[$status]['purchase'] += ((float)$orderDetail->purchase_rate * (float)$orderDetail->quantity);
        }

        foreach($statusArray as $status) {
            $orderData[$status] = array(
                "stage" => $stagename[$status],
                "count" => $summaryByStatus[$status]['count'],
                "amount" => $summaryByStatus[$status]['amount'],
                "tax" => $summaryByStatus[$status]['tax'],
                "purchase" => $summaryByStatus[$status]['purchase'],
                "profit" => $summaryByStatus[$status]['amount'] - $summaryByStatus[$status]['purchase'],
            );
        }

        $overall_puchase_amt = 0;
        $overall_sale_amt = 0;
        $overall_sale_tax =0;
        $overall_profit_amt = 0;
        $output = '';
        $output .='
            <table class="mt-4 table table-bordered table-striped export-table no-footer dtr-inline dataTable" width="100%" border="1" cellpadding="2" cellspacing="0">
                <tr>
                <th>Stage</th>
                <th>Orders</th>
                <th>Sales Amount</th>
                <th>Sales Tax</th>
                <th>Purchase Amount</th>
                <th>Profit Amount</th>
            </tr>
            ';
        foreach($orderData as $order)
        {
            $output .= '
            <tr>
            <td>'.$order["stage"].'</td>
            <td>'.$order["count"].'</td>
            <td>'.$order["amount"].'</td>
            <td>'.$order["tax"].'</td>
            <td>'.$order['purchase'].'</td>
            <td>'.$order['profit'].'</td>
            </tr>';
            $overall_puchase_amt = ($overall_puchase_amt  + $order["purchase"]);
            $overall_sale_amt = ($overall_sale_amt + $order["amount"]);
            $overall_sale_tax = ($overall_sale_tax + $order['tax']);
            $overall_profit_amt = ($overall_profit_amt + $order["profit"]);
        }
          
        $output .='
        <tr> <td colspan=2><b><center>Overall Total </td>
        <td><b>'.number_format($overall_sale_amt,2).'</td>
        <td><b>'.number_format($overall_sale_tax,2).'</td>
        <td><b>'.number_format($overall_puchase_amt,2).'</td>
        <td><b>'.number_format($overall_profit_amt,2).'</td>
        </tr>
        </table><br>
        <br>';

        // header('Content-Type: application/xls');
        // header('Content-Disposition: attachment; filename=report-profit.xls');
        echo json_encode($output);
    }

    public function dailyProfit(Request $request)
    {
        $date = date('Y-m-d');
        $orders = OrderModel::select(
            'order.id',
            'order.bill_id',
            'order.customer_id',
            'order.date',
            'order.order_discount',
            'order.total',
            'order.status',
            'customer.name as customerName',
            'users.name as userName',
        )
        ->leftjoin('customer', 'order.customer_id', '=', 'customer.id')
        ->leftjoin('users', 'order.created_by', '=', 'users.id');
        $date = explode("-", $request->date);//print_r($request->all());die;
        $sDate = date("Y-m-d", strtotime($date[0]));
        $eDate = date("Y-m-d", strtotime($date[1]));
        if(isset($request->payment_method_id) && $request->payment_method_id != "") {
                $orders = $orders->where('order.payment_method', $request->payment_method_id);
        }
        $orders = $orders->whereBetween('order.date', [$sDate, $eDate]);
        $orders = $orders->get();
        foreach($orders as $order) {
            $orderDetails = OrderDetailModel::where('order_id', $order->id)
                            ->where('status', 1)
                            ->orwhere('status', 2)
                            ->get();
            $amount = 0;
            $tax = 0;
            $purchaseAmount = 0;
            foreach($orderDetails as $orderDetail) {
                $taxAmount = ($orderDetail->amount * $orderDetail->tax) / 100;
                $tax = $taxAmount + $tax;
                $product = ProductsModel::find($orderDetail->product_id);
                $purchaseAmount = ($product->purchase_rate * $orderDetail->quantity) + $purchaseAmount;
                $amount = $orderDetail->amount + $amount;
            }
            $order['order_amount'] = $amount;
            $order['order_tax_amount'] = $tax;
            $order['purchase_amount'] = $purchaseAmount;
            $order['profit_amount'] = $amount - $purchaseAmount;
        }

        $overall_puchase_amt = 0;
        $overall_sale_amt = 0;
        $overall_sale_tax =0;
        $overall_profit_amt = 0;
        $output = '';
        $output .='  <h2 align="center">Malraja Traders</h3><br>
                        <p align="center"><b>Billwise Profit Report Details</p>
        <table width="100%" border="1" cellpadding="2" cellspacing="0">
                
                <tr>
                <th>S.No.</th>
                <th>Bill Date</th>
                <th>Bill No</th>
                <th>Customer Name</th>
                <th>Received Employee</th>
                <th>Sales Amount</th>
                <th>Sales Tax</th>
                <th>Purchase Amount</th>
                <th>Profit Amount</th>
                
            </tr>
            ';
        $i =0;
        foreach($orders as $order)
        {
            $output .= '
            <tr>
            <td>'.$i.'</td>
            <td>'.$order["date"].'</td>
            <td>'.$order["bill_id"].'</td>
            <td>'.$order["customerName"].'</td>
            <td>'.$order['userName'].'</td>
            <td>'.number_format($order["order_amount"],2).'</td> 
            <td>'.number_format($order["order_tax_amount"],2).'</td>
            <td>'.number_format($order["purchase_amount"],2).'</td>
            <td>'.number_format($order["profit_amount"],2).'</td>
            </tr>';
            $overall_puchase_amt = ($overall_puchase_amt  + $order["purchase_amount"]);
            $overall_sale_amt = ($overall_sale_amt + $order["order_amount"]);
            $overall_sale_tax = ($overall_sale_tax + $order['order_tax_amount']);
            $overall_profit_amt = ($overall_profit_amt + $order["profit_amount"]);
            $i++;
        }
          
        $output .='
        <tr> <td colspan=5><b><center>Overall Total </td>
        <td><b>'.number_format($overall_sale_amt,2).'</td>
        <td><b>'.number_format($overall_sale_tax,2).'</td>
        <td><b>'.number_format($overall_puchase_amt,2).'</td>
        <td><b>'.number_format($overall_profit_amt,2).'</td>
        </tr>
        </table><br>
        <br>';

        header('Content-Type: application/xls');
        header('Content-Disposition: attachment; filename=report-profit.xls');
        echo $output;
    }

    public function customerReport(Request $request)
    {
        $customers = CustomerModel::select('id', 'name')->get();
        $category_option = "";
        foreach($customers as $category) {
            $category_option.= "<option value={$category['id']}>{$category['name']}</option>";
        }
        $this->datas['customers'] = $category_option;
        return view('reports.customer_report')->with($this->datas);
    }

    public function customerReportGet(Request $request)
    {
        $stagename = ["", "Confirmed", "Delivery Pending", "Payment Pending", "Completed"];
        $orderData = "";
        $statusArray = [1,2,3,4];
        $table = "";
        $orderData.="<table style='width: 100%;' id='example1' class='table table-bordered table-striped export-table no-footer dtr-inline dataTable' aria-describedby='example1_info'>";
        $orderData.="<thead>
                    <tr>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                </thead>";
        foreach($statusArray as $status)
        {
            $orders = OrderModel::select(
                'order.id',
                'order.bill_id',
                'order.customer_id',
                'order.date',
                'order.payment_method',
                'order.order_discount',
                'order.total',
                'order.status',
                'customer.name as customerName',
            )
            ->leftjoin('customer', 'order.customer_id', '=', 'customer.id');
            $temp = $orders->where('order.status', $status)->where('order.customer_id', $request->customerId)->sum('total');//print_r($orders);die;
            $orders = $orders->where('order.status', $status)->where('order.customer_id', $request->customerId)->get();//print_r($orders);die;

            $orderData.="<tbody>
                        <td>{$stagename[$status]}</td>
                        <td>$temp</td>
                    </tr>";

            if(count($orders) > 0) {
                $table.="<table style='width: 100%;' id='example1' class='table table-bordered table-striped export-table no-footer dtr-inline dataTable' aria-describedby='example1_info'>";
                $table.="<thead>
                            <tr>
                                <th>SNO</th>
                                <th>Bill No</th>
                                <th>Customer</th>
                                <th>Order Type</th>
                                <th>Date</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                        </thead>";
            }
            $i = 1;
            foreach($orders as $order) {
                $type = $order->type == 1 ? "Delivery" : "POS";
                $table.="<tbody>
                            <tr class='odd'>
                                <td>$i</td>
                                <td>{$order->bill_id}</td>
                                <td>{$order->customerName}</td>
                                <td>$type</td>
                                <td>{$order->date}</td>
                                <td>{$order->payment_method}</td>
                                <td>{$stagename[$status]}</td>
                                <td>{$order->total}</td>
                            </tr>";
                $i = $i + 1;
            }
            if(count($orders) > 0) {
                $table.="</table>";
            }
        }
        $maybe = $orderData . $table;
        echo $maybe;
    }

    public function getStockReport(Request $request)
    {
        $stocks = StockModel::select(
                    DB::raw('SUM(stock.sale) as sale'), 
                    DB::raw('SUM(stock.purchase) as purchase'),
                    DB::raw('SUM(stock.sale_return) as sale_return'),
                    'stock.product_id',
                    'products.name as productName',
                    'products.tamil_name as tamil_name',
                    'category.name as categoryName',
                    'hsn.hsn',
                    )
                    ->leftjoin('products', 'stock.product_id', '=', 'products.id')
                    ->leftjoin('category', 'stock.category_id', '=', 'category.id')
                    ->leftjoin('hsn', 'stock.hsn_id', '=', 'hsn.id');

        if(isset($request->cat_id) && !empty($request->cat_id)) {
            $stocks = $stocks->where("stock.category_id", $request->cat_id);
        }
        if(isset($request->hsn_id) && !empty($request->hsn_id)) {
            $stocks = $stocks->where("stock.hsn_id", $request->hsn_id);
        }
        $stocks = $stocks->groupBy('products.id')->orderBy('products.name', 'ASC')->get();
        if(isset($request->isPrint) && !empty($request->isPrint)) {
            $this->datas['stocks'] = $stocks;
            return view('product_stock_print')->with($this->datas);
        }
        $output = '';
        $overall_pcount = 0;
        $overall_sold = 0;
        $output .='  <h2 align="center">Malraja Traders</h3><br>
                   <p align="center">
                   <b>Stock Report</p>
                  <table width="100%" border="1" cellpadding="2" cellspacing="0">
                    <thead>
                      <tr>
                        <th>S.No</th>
                        <th>Category</th>
                        <th>Product Name</th>
                        <th>HSN Code</th>
                        <th>Sold Count</th>
                        <th>Purchased Count</th>
                        <th>Stock In Hand</th>
                      </tr>
                    </thead>';
        $i=1;
        foreach ($stocks as $row)
        {
            $row['sale'] = $row['sale'] - $row['sale_return'];
            $stock = $row['purchase'] - $row['sale'];
            $output.='<tr><td>'.$i.'</td>';
            $output.='<td>'.$row['productName'].'</td>';
            $output.='<td>'.$row['categoryName'].'</td>';
            $output.='<td>'.$row['hsn'].'</td>';
            $output.='<td>'.$row['sale'].'</td>';
            $output.='<td>'.$row['purchase'].'</td>';
            $output.='<td>'.$stock.'</td></tr>';
            $overall_pcount = $overall_pcount + $row['purchase'];
            $overall_sold = $overall_sold + $row['sale'];
            $i = $i + 1;
        }
        
        $output.='<tr><td colspan=4><b><center>Overall Total </td>
        <td><b>'.$overall_sold.'</td>
        <td><b>'.$overall_pcount.'</td>
        </tr></table>';


        header('Content-Type: application/xls');
        header('Content-Disposition: attachment; filename=stock-report.xls');
        echo $output;

    }

    public function paymentPendingGet(Request $request)
    {
        $visits = OrderModel::select(
            'order.id',
            'order.bill_id',
            'order.type',
            'order.date',
            'order.payment_method',
            'order.total',
            'order.status',
            'customer.name as customerName',
            'users.name as userName',
        )
        ->leftjoin('customer', 'order.customer_id', '=', 'customer.id')
        ->leftjoin('users', 'order.created_by', '=', 'users.id')
        ->where('order.status', 3);

        if(isset($request->date) && !empty($request->date)) {
        $date = explode("-", $request->date);
        $sDate = date("Y-m-d", strtotime($date[0]));
        $eDate = date("Y-m-d", strtotime($date[1]));
        $visits = $visits->whereBetween('order.date', [$sDate, $eDate]);
        }

        $visits = $visits->orderBy('order.created_at', 'DESC')->get();
        $collectionSums = CollectionModel::select('order_id', DB::raw('SUM(amount) as total_paid'))
            ->whereIn('order_id', $visits->pluck('id')->toArray())
            ->groupBy('order_id')
            ->pluck('total_paid', 'order_id');
        $datalist = [];
        $i = 0;
        
        $output = '';
        $output .='  <h2 align="center">Malraja Traders</h3><br>
        <p align="center">
        <b>Stock Report</p>
       <table width="100%" border="1" cellpadding="2" cellspacing="0">
         <thead>
           <tr>
             <th>S.No</th>
             <th>Bill No</th>
             <th>Customer</th>
             <th>Amount</th>
             <th>Pending</th>
           </tr>
         </thead>';
        $i=1;
        $overall = 0;
        $overallPending = 0;
        foreach($visits as $list)
        {
            if($list->type == 1) {
                $list->type = "Delivery";
            } else {
                $list->type = "POS";
            }
            $list->paymentPending = 0;
            if($list->status != 4) {
                $paymentPending = $collectionSums[$list->id] ?? 0;
                $list->paymentPending = $list->total - $paymentPending;
            }
            
            $output.='<tr><td>'.$i.'</td>';
            $output.='<td>'.$list['bill_id'].'</td>';
            $output.='<td>'.$list['customerName'].'</td>';
            $output.='<td>'.$list['total'].'</td>';
            $output.='<td>'.$list->paymentPending.'</td>';
            $i = $i + 1;
            $overall = $overall + $list['total'];
            $overallPending = $overallPending + $list->paymentPending;
        }
            
        $output.='<tr><td colspan=3><b><center>Overall Total </td>
        <td><b>'.$overall.'</td>
        <td><b>'.$overallPending.'</td>
        </tr></table>';

        echo json_encode($output);
    }
}