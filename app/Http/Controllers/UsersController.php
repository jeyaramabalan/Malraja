<?php

namespace App\Http\Controllers;

use App\Models\CollectionModel;
use App\Models\DailyExpenseModel;
use App\Models\ExpenseModel;
use App\Models\OrderModel;
use App\Models\RetailModel;
use App\Models\RolesModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    public function index()
    {
        $this->datas['users'] = array();
        $this->datas['page'] = 'users';
        $this->datas['role'] = Auth::user()->role;
        $this->datas['route'] = route('user.create');
        return view('users.index')->with($this->datas);
    }

    public function dashboard()
    {
        $fdate = date('Y-m-d') . " 00:00:00";
        $tdate = date('Y-m-d') . " 23:59:59";
        //$fdate = date('Y-m-d') . " 00:00:00";
        //$tdate = date('Y-m-d') . " 23:59:59";
        $totalOrders = OrderModel::where('status', '!=', 5)->count();
        $completedOrders = OrderModel::where('status', 4)->count();
        $totalPendingOrders = OrderModel::where('status', '!=', 4)->where('return_status', 0)->count();
        $amount = CollectionModel::
                        leftjoin('order', 'collection.order_id', '=', 'order.id')
                        ->where('order.status', 3)
                        ->where('collection.status', 1)
                        ->sum('amount');
        $todayTotalOrders = OrderModel::where('status', '!=', 5)->whereBetween('order.date', [date('Y-m-d'), date('Y-m-d')])->count();
        $paymentMethodWise = OrderModel::select(DB::raw('SUM(total) as total'), DB::raw('SUM(upi) as upi'), 'payment_method')->where('date', date('Y-m-d'))->where('status', 4)->groupBy('payment_method')->get();

        // Payment method wise data and mixed method logic
        $upi = array("payment_method" => "UPI", "total" => "0");
        $cash = array("payment_method" => "CASH", "total" => "0");
        if(count($paymentMethodWise) > 0) {
            foreach($paymentMethodWise as $key => $method) {
                if($method['payment_method'] == "Cash") {
                    $cash = array("payment_method" => "Cash", "total" => $method['total']);
                }
                if($method['payment_method'] == "UPI") {
                    $upi = array("payment_method" => "UPI", "total" => $upi['total'] + $method['total']);
                }
                if($method['payment_method'] == "Mixed") {
                    $upi['total'] = $upi['total'] + $method['upi'];
                    $cash['total'] = $cash['total'] + ($method['total'] - $method['upi']);
                    unset($paymentMethodWise[$key]);
                }
            }
        }

        $paymentMethodWise = RetailModel::select(DB::raw('SUM(total) as total'), DB::raw('SUM(upi) as upi'), 'payment_method')->where('date', date('Y-m-d'))->where('status', 4)->groupBy('payment_method')->get();
        
        // Payment method wise data and mixed method logic
        $retail_upi = array("payment_method" => "UPI", "total" => "0");
        $retail_cash = array("payment_method" => "CASH", "total" => "0");
        if(count($paymentMethodWise) > 0) {
            foreach($paymentMethodWise as $key => $method) {
                if($method['payment_method'] == "Cash") {
                    $retail_cash = array("payment_method" => "Cash", "total" => $method['total']);
                }
                if($method['payment_method'] == "UPI") {
                    $retail_upi = array("payment_method" => "UPI", "total" => $retail_upi['total'] + $method['total']);
                }
                if($method['payment_method'] == "Mixed") {
                    $retail_upi['total'] = $retail_upi['total'] + $method['upi'];
                    $retail_cash['total'] = $retail_cash['total'] + ($method['total'] - $method['upi']);
                    unset($paymentMethodWise[$key]);
                }
            }
        }

        $paymentMethodWise = CollectionModel::select(DB::raw('SUM(amount) as total'), DB::raw('SUM(upi) as upi'), 'payment_method_id')->where('date', date('Y-m-d'))->where('status', 1)->groupBy('payment_method_id')->get();
        // Payment method wise data and mixed method logic
        $collection_upi = array("payment_method" => "UPI", "total" => "0");
        $collection_cash = array("payment_method" => "CASH", "total" => "0");
        $todayCollectionTotal = 0;
        if(count($paymentMethodWise) > 0) {
            foreach($paymentMethodWise as $key => $method) {
                $todayCollectionTotal = $todayCollectionTotal + $method['total'];
                if($method['payment_method_id'] == "Cash") {
                    $collection_cash = array("payment_method" => "Cash", "total" => $method['total']);
                }
                if($method['payment_method_id'] == "UPI") {
                    $collection_upi = array("payment_method" => "UPI", "total" => $collection_upi['total'] + $method['total']);
                }
                if($method['payment_method_id'] == "Mixed") {
                    $collection_upi['total'] = $collection_upi['total'] + $method['upi'];
                    $collection_cash['total'] = $collection_cash['total'] + ($method['total'] - $method['upi']);
                    unset($paymentMethodWise[$key]);
                }
            }
        }

        $todayCompletedOrders = OrderModel::whereBetween('date', [date('Y-m-d'), date('Y-m-d')])->where('status', 4)->count();
        $todayPendingOrders = OrderModel::whereBetween('date', [date('Y-m-d'), date('Y-m-d')])->where('status', '!=', 4)->where('return_status', 0)->count();
        $todayAmount = CollectionModel::leftjoin('order', 'collection.order_id', '=', 'order.id')
                        ->whereBetween('collection.date', [date('Y-m-d'), date('Y-m-d')])
                        ->where('order.status', 3)
                        ->where('collection.status', 1)
                        ->sum('amount');

        $pendingCollectionAmount = CollectionModel::select(DB::raw('SUM(order.total) as orderTotal'), DB::raw('SUM(collection.amount) as collectionTotal'))->leftjoin('order', 'collection.order_id', '=', 'order.id')
                        ->where('order.status', '!=', 4)
                        ->where('order.return_status', 0)
                        ->where('collection.status', 1)
                        ->get();

        $orderTotal = $pendingCollectionAmount[0]['orderTotal'];
        $collectionTotal = $pendingCollectionAmount[0]['collectionTotal'];

        $todayExpense = DailyExpenseModel::whereBetween('created_at', [$fdate, $tdate])->where('status', 1)->sum('amount');
        $this->datas['total'] = $totalOrders;
        $this->datas['completed'] = $completedOrders;
        $this->datas['pending'] = $totalPendingOrders;
        $this->datas['amount'] = $amount;
        $this->datas['todayTotalOrders'] = $todayTotalOrders;
        $this->datas['todayPendingOrders'] = $todayPendingOrders;
        $this->datas['todayCompletedOrders'] = $todayCompletedOrders;
        $this->datas['todayAmount'] = $todayAmount;
        $this->datas['todayExpense'] = $todayExpense;
        $this->datas['orderTotal'] = $orderTotal;
        $this->datas['collectionTotal'] = $collectionTotal;
        $this->datas['todayCollectionTotal'] = $todayCollectionTotal;
        $this->datas['paymentMethodWise'] = json_decode(json_encode(array($upi, $cash)));
        $this->datas['collectionpaymentMethodWise'] = json_decode(json_encode(array($collection_upi, $collection_cash)));
        $this->datas['retailpaymentMethodWise'] = json_decode(json_encode(array($retail_upi, $retail_cash)));
        return view('dashboard.dashboard')->with($this->datas);
    }

    public function getUserlist(Request $request)
    {
        $dataQry = User::where('status', 1);
        $datasCount = (clone $dataQry)->count();

        $limit = (int) $request->input('length', 25);
        $offset = (int) $request->input('start', 0);
        // DataTables sends length=-1 when paging is disabled.
        if ($limit <= 0) {
            $limit = max($datasCount, 1);
        }

        $datas = $dataQry->offset($offset)->limit($limit)->get();
        $datalist = [];
        $i = $offset;
        foreach ($datas as $list) {
            $list->sno = ++$i . '';
            $list->action = '<td align="center">
                                        <a href="'.route('user.edit', [$list->id]).'"><i style="color: green;" class="fa fa-edit"></i></a>
                                        <a class="pl-2" href="'.route('user-delete', [$list->id]).'"><i style="color: red;" class="fa fa-trash"></i></a>
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

    public function delete($id)
    {
        User::where('id', $id)->update(["status" => 0]);
        $output = array('success' => 0, 'msg' => "User Deleted Successfully");
        return redirect()->route('user.index')->with('status', $output);
    }

    public function edit($id)
    {
        $user = User::find($id);
        $roles = RolesModel::select('id', 'role')->get();
        $roles_option = "";
        foreach($roles as $role) {
            $selected = "";
            if($role['id'] == $user['role']) {
                $selected = 'selected="selected"'; 
            }
            $roles_option.= "<option $selected value={$role['id']}>{$role['role']}</option>";
        }
        $this->datas['roles'] = $roles_option;
        $this->datas['route'] = route('user.update', $id);
        $this->datas['user'] = $user;
        return view('users.useraddedit')->with($this->datas);
    }

    public function create()
    {
        $roles = RolesModel::select('id', 'role')->get();
        $roles_option = "";
        foreach($roles as $role) {
            $roles_option.= "<option value={$role['id']}>{$role['role']}</option>";
        }
        $this->datas['user'] = array();
        $this->datas['roles'] = $roles_option;
        $this->datas['route'] = route('user.store');
        return view('users.useraddedit')->with($this->datas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'password' => 'required',
            'mobile' => 'required',
        ]);

        if ($validator->fails())
        {
            $output = array('success' => 0, 'msg' => $validator->errors()->first());
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }

        $data = [];
        $data['name'] = $request->name;
        $data['mobile'] = $request->mobile;
        $data['password'] = $request->password;
        $data['role'] = $request->role;
        if(isset($request->email)) {
            $data['email'] = $request->email;
        }
        if(isset($request->dob)) {
            $data['dob'] = $request->dob;
        }
        if(isset($request->aadhar_number)) {
            $data['aadhar_number'] = $request->aadhar_number;
        }
        
        User::create($data);
        $output = array('success' => 1, 'msg' => 'User Created Successfully');
        return redirect()->route('user.index')->with('status', $output);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'mobile' => 'required',
        ]);

        if ($validator->fails())
        {
            $output = array('success' => 0, 'msg' => $validator->errors()->first());
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }

        $data = [];
        $data['name'] = $request->name;
        $data['mobile'] = $request->mobile;
        $data['role'] = $request->role;
        if(isset($request->email)) {
            $data['email'] = $request->email;
        }
        if(isset($request->dob)) {
            $data['dob'] = $request->dob;
        }
        if(isset($request->aadhar_number)) {
            $data['aadhar_number'] = $request->aadhar_number;
        }
        
        User::where('id', $request->id)->update($data);
        $output = array('success' => 1, 'msg' => 'User Updated Successfully');
        return redirect()->route('user.index')->with('status', $output);
    }
}
