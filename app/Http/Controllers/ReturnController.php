<?php

namespace App\Http\Controllers;

use App\Models\CategoryModel;
use App\Models\CustomerModel;
use App\Models\OrderDetailModel;
use App\Models\OrderModel;
use App\Models\ReturnModel;
use App\Models\StockModel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ReturnController extends Controller
{
    public function index(Request $request)
    {
        $orders = OrderModel::where('status', 5)->where('return_status', 1)->get();
        $orderData = [];
        foreach($orders as $order) {
            $orderDetails = OrderDetailModel::select(
                'order_details.quantity', 
                'order_details.order_id', 
                'order_details.product_id', 
                'order_details.quantity_price', 
                'order_details.amount', 
                'order_details.gst', 
                'category.name as category_name', 
                'products.name as product_name', 
                'order_details.tax')
                ->leftjoin('category', 'order_details.category_id', '=', 'category.id')
                ->leftjoin('products', 'order_details.product_id', '=', 'products.id')
                ->where('order_id', $order->id)
                ->where('order_details.status', 2)
                ->limit(5);
            foreach($orderDetails as $orderDetail) {
                if($orderDetail['status'] != 1) {
                    $orderDetail['quantity'] = $this->getReturnQuantity($orderDetail['order_id'], $orderDetail['product_id']);
                }
                $orderData[] = $orderDetail;
            }
        }
        
        $this->datas['data'] = $orderData;
        $this->datas['route'] = route("return.create");
        return view('return.orders')->with($this->datas);
    }

    public function getReturnQuantity($id, $product_id)
    {
        $data = ReturnModel::select('quantity')->where('order_id', $id)->where('product_id', $product_id)->first();
        if($data) {
            return $data['quantity'];
        }
        return 0;
    }

    public function edit($id)
    {
        $orders = OrderModel::where('id', $id)->first();
        $categories = CategoryModel::select('id', 'name')->get();
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
        $this->datas['vendor'] = $customers_option;
        $this->datas['route'] = route("return.update", $id);
        $this->datas['category'] = $category_option;
        Session::forget('products');
        return view('return.edit')->with($this->datas);
    }

    public function update($id, Request $request)
    {
        try{
            OrderModel::where('id', $id)->update(["status" => 5, "return_status" => 1]);
            $data = OrderModel::find($id);
            foreach(Session::get('products') as $cartArray) {

                // Partial return logic
                $orderDetails = OrderDetailModel::where('order_id', $id)->where('product_id', $cartArray['id'])->first();
                $stockQty = $cartArray['count'];
                if($orderDetails['quantity'] != $cartArray['count']) {
                    OrderDetailModel::where('order_id', $id)
                                        ->where('product_id', $orderDetails['product_id'])
                                        ->update(["status" => 3, "quantity" => $orderDetails['quantity'] - $cartArray['count']]);
                    $stockQty = $orderDetails['quantity'] - $cartArray['count'];
                } else {
                    OrderDetailModel::where('order_id', $id)
                                        ->where('product_id', $orderDetails['product_id'])
                                        ->update(["status" => 2]);
                }

                StockModel::where('bill', $data['bill_id'])
                            ->where('product_id', $orderDetails['product_id'])
                            ->update(["sale_return" => $stockQty]);

                // Return add
                $stock = [];
                $stock['bill_id'] = $data['bill_id'];
                $stock['order_id'] = $data['id'];
                $stock['product_id'] = $cartArray['id'];
                $stock['quantity'] = $cartArray['count'];
                $stock['quantity_price'] = $cartArray['final_price'];
                $stock['amount'] = $cartArray['tgst'];
                $stock['tax'] = $cartArray['gst'];
                ReturnModel::create($stock);
            }
            $output = array('success' => 1, 'msg' => 'Order Returned Successfully');
            return redirect()->route('order.index')->with('status', $output);
        } catch(Exception $e) {
            $output = array('success' => 0, 'msg' => $e->getMessage());
            return redirect()->route('order.index')->with('status', $output);
        }
    }
}
