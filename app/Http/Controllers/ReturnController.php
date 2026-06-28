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
        $this->datas['route'] = route('return.create');
        return view('return.orders')->with($this->datas);
    }

    public function getReturnList(Request $request)
    {
        $query = ReturnModel::select(
            'return_products.id',
            'return_products.bill_id',
            'return_products.order_id',
            'return_products.quantity',
            'return_products.amount',
            'return_products.tax',
            'return_products.created_at',
            'products.name as product_name',
            'customer.name as customerName',
            'order.date as order_date'
        )
            ->leftJoin('order', 'return_products.order_id', '=', 'order.id')
            ->leftJoin('customer', 'order.customer_id', '=', 'customer.id')
            ->leftJoin('products', 'return_products.product_id', '=', 'products.id');

        $orderColumns = [
            1 => 'return_products.bill_id',
            3 => 'customer.name',
            4 => 'products.name',
            5 => 'return_products.created_at',
            6 => 'return_products.quantity',
            7 => 'return_products.amount',
            8 => 'return_products.tax',
        ];

        if ($request->has('order') && isset($request->order[0]['column'])) {
            $colIndex = (int) $request->order[0]['column'];
            $dir = ($request->order[0]['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
            if (isset($orderColumns[$colIndex])) {
                $query->orderBy($orderColumns[$colIndex], $dir);
            }
        }

        $query->orderBy('return_products.id', 'desc');

        if (isset($request->search) && isset($request->search['value']) && $request->search['value'] != '') {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('return_products.bill_id', 'like', '%'.$search.'%')
                    ->orWhere('return_products.order_id', 'like', '%'.$search.'%')
                    ->orWhere('customer.name', 'like', '%'.$search.'%')
                    ->orWhere('products.name', 'like', '%'.$search.'%');
            });
        }

        $datasCount = (clone $query)->count();

        $limit = (int) $request->input('length', 25);
        $offset = (int) $request->input('start', 0);
        if ($limit <= 0) {
            $limit = max($datasCount, 1);
        }

        $rows = $query->offset($offset)->limit($limit)->get();
        $datalist = [];
        $i = $offset;
        foreach ($rows as $list) {
            $list->sno = ++$i . '';
            $list->date = $list->created_at
                ? date('Y-m-d', strtotime($list->created_at))
                : ($list->order_date ?? '');
            $list->order_no = '<a href="'.route('order-show', [$list->order_id]).'">#'.$list->order_id.'</a>';
            $list->action = '<td align="center">
                <a href="'.route('order-show', [$list->order_id]).'"><i class="fa fa-eye"></i></a>
            </td>';
            $datalist[] = $list;
        }

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => (int) $datasCount,
            'recordsFiltered' => (int) $datasCount,
            'data' => $datalist,
        ]);
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
