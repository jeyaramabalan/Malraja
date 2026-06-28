<?php

namespace App\Http\Controllers;

use App\Models\CollectionModel;
use App\Models\OrderModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CollectionController extends Controller
{
    public function index()
    {
        $this->datas['route'] = route("collection.create");
        return view('collection.collection')->with($this->datas);
    }

    public function getCollectionlist(Request $request)
    {
        $dataQry = CollectionModel::select(
                        'collection.id',
                        'collection.amount',
                        'collection.date',
                        'collection.order_id',
                        'collection.description',
                        'order.bill_id as bill_id',
                        'order.total as bill_amount',
                        'users.name as collected_name',
                        'customer.name as customer_name',
                        'collection.status as collection_status'
                    )
                    ->leftjoin('order', 'collection.order_id', '=', 'order.id')
                    ->leftjoin('users', 'collection.collected_by', '=', 'users.id')
                    ->leftjoin('customer', 'order.customer_id', '=', 'customer.id');
        $limit = $request->input('length');
        $offset = $request->input('start');
        if(isset($request->search) && isset($request->search['value']) && $request->search['value'] != '') {
            $dataQry = $dataQry->orwhere('collection.order_id', 'like', '%'.$request->search['value'].'%');
            $dataQry = $dataQry->orwhere('customer.name', 'like', '%'.$request->search['value'].'%');
        } else {
            $dataQry = $dataQry->where('collection.status', '!=', 0);
        }
        $datasCount = $dataQry->count();
        $datas = $dataQry->offset($offset)->limit($limit)->orderBy('date', 'DESC')->get();
        $datalist = [];
        $i = $offset;
        foreach($datas as $list)
        {
            if($list->collection_status == 0) {
                continue;
            }
            $list->sno          = ++$i .'';
            $list->action       = '<td align="center">
                                        <a class="pl-2" href="'.route('collection-delete', [$list->id]).'"><i style="color: red;" class="fa fa-trash"></i></a>
                                        <a class="pl-2" href="'.route('collection.show', [$list->order_id]).'"><i style="color: green;" class="fa fa-eye"></i></a>
                                   </td>';
            $datalist[]         = $list;
        }
        $json_data = ['draw'=>intval($request->input('draw')),'recordsTotal'=>intval($datasCount),'recordsFiltered'=>intval($datasCount),'data'=>$datalist];
        echo json_encode($json_data);
        
        // $json_data = ['data'=>$datalist];
        // echo json_encode($json_data);
    }

    public function getPaidAmount($id) {
        $total = 0;
        $count = CollectionModel::where('order_id', $id)->count();
        if($count > 0) {
            $total = CollectionModel::where('order_id', $id)->sum('amount');
        }
        return $total;
    }

    public function create()
    {
        $customers = User::select('id', 'name')->where('status', 1)->get();
        $customers_option = "";
        foreach($customers as $category) {
            $customers_option.= "<option value={$category['id']}>{$category['name']}</option>";
        }
        $order = OrderModel::select('id', 'bill_id', 'total')->where('status', 3)->get();
        $order_option = "";
        foreach($order as $category) {
            $paidAmount = self::getPaidAmount($category['id']);
            $totalAmount = $category['total'];
            $dataArr = array("id" => $category['id'], "paid" => round($paidAmount, 2), "total" => round($totalAmount, 2));
            $dataArr = json_encode($dataArr);
            $order_option.= "<option value=$dataArr>{$category['bill_id']}</option>";
        }
        $this->datas['admin_option'] = $customers_option;
        $this->datas['order_option'] = $order_option;
        $this->datas['route'] = route("collection.store");
        return view('collection.collection_addedit')->with($this->datas);
    }

    public function delete($id)
    {
        CollectionModel::where('id', $id)->update(["status" => 0]);
        $output = array('success' => 1, 'msg' => "Collection Deleted Successfully");
        return redirect()->route('collection.index')->with('status', $output);
    }

    public function show($id) {

        $collections = CollectionModel::select('collection.*', 'users.name as name')
                        ->leftjoin('users', 'collection.collected_by', '=', 'users.id')
                        ->where('order_id', $id)
                        ->where('collection.status', 1)
                        ->get();
        $total = CollectionModel::where('order_id', $id)->where('status', '!=', 0)->sum('amount');
        $order = OrderModel::find($id);

        $this->datas['orderId'] = $id;
        $this->datas['total'] = $total;
        $this->datas['orderTotal'] = $order->total;
        $this->datas['collections'] = $collections;
        return view('collection.view')->with($this->datas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required',
            'collected_by' => 'required',
            'date' => 'required',
            'order_id' => 'required',
        ]);

        if ($validator->fails())
        {
            $output = array('success' => 0, 'msg' => $validator->errors()->first());
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }
        $data = [];
        $data['amount'] = $request->amount;
        if(isset($request->upi)) {
            $data['upi'] = $request->upi;
        }
        $data['date'] = $request->date;
        $data['collected_by'] = $request->collected_by;
        $data['payment_method_id'] = $request->payment_method_id;
        $data['created_by'] = Auth::id();
        $data['order_id'] = json_decode($request->order_id, true);
        $data['order_id'] = $data['order_id']['id'];
        if($request->desc) {
            $data['description'] = $request->desc;
        }
        
        $result = CollectionModel::create($data);
        if($result) {
            $output = array('success' => 1, 'msg' => 'Collection Added Successfully');
            return redirect()->route('collection.index')->with('status', $output);
        } else {
            $output = array('success' => 0, 'msg' => 'Failed');
            return redirect()->route('collection.create')->with('status', $output);
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required',
            'collected_by' => 'required',
            'date' => 'required',
            'order_id' => 'required',
        ]);

        if ($validator->fails())
        {
            $output = array('success' => 0, 'msg' => $validator->errors()->first());
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }
        $data = [];
        $data['amount'] = $request->amount;
        if(isset($request->upi)) {
            $data['upi'] = $request->upi;
        }
        $data['date'] = $request->date;
        $data['collected_by'] = $request->collected_by;
        $data['payment_method_id'] = $request->payment_method_id;
        $data['created_by'] = Auth::id();
        $data['order_id'] = json_decode($request->order_id, true);
        $data['order_id'] = $data['order_id']['id'];
        if($request->desc) {
            $data['description'] = $request->desc;
        }
        
        $result = CollectionModel::create($data);
        if($result) {
            $output = array('success' => 1, 'msg' => 'Collection Added Successfully');
            return redirect()->route('collection.index')->with('status', $output);
        } else {
            $output = array('success' => 0, 'msg' => 'Failed');
            return redirect()->route('collection.create')->with('status', $output);
        } 
    }

}
