<?php

namespace App\Http\Controllers;

use App\Models\DailyExpenseModel;
use App\Models\ExpenseModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DailyExpenseController extends Controller
{
    public function index()
    {
        $vendor = User::get();
        $this->datas['admin'] = $vendor;
        $this->datas['route'] = route("dailyexpense.create");
        return view('daily_expense.daily_expense')->with($this->datas);
    }

    public function create()
    {
        $user = User::select('id', 'name')->where('status', 1)->get();
        $user_option = "";
        foreach($user as $hsnData) {
            $user_option.= "<option value={$hsnData['id']}>{$hsnData['name']}</option>";
        }

        $expense_type = ExpenseModel::select('id', 'name')->where('status', 1)->get();
        $expense_type_option = "";
        foreach($expense_type as $hsnData) {
            $expense_type_option.= "<option value={$hsnData['id']}>{$hsnData['name']}</option>";
        }

        $this->datas['admin_option'] = $user_option;
        $this->datas['expense_type'] = $expense_type_option;
        $this->datas['route'] = route("dailyexpense.store");
        return view('daily_expense.daily_expenseaddedit')->with($this->datas);
    }

    public function edit($id)
    {
        $vendor = DailyExpenseModel::find($id);
        $this->datas['route'] = route("dailyexpense.update", $id);
        $this->datas['vendor'] = $vendor;
        return view('daily_expense.daily_expenseaddedit')->with($this->datas);
    }


    public function getDailyExpenselist(Request $request)
    {
        $dataQry = DailyExpenseModel::select(
                        'daily_expense.id',
                        'daily_expense.amount',
                        'daily_expense.approved',
                        'daily_expense.date',
                        'users.name as user_name',
                        'expense.name as expense_name',
                    )
                    ->leftjoin('expense', 'daily_expense.expense_id', '=', 'expense.id')
                    ->leftjoin('users', 'daily_expense.expense_by', '=', 'users.id')
                    ->where('expense.status', 1);
        $limit = $request->input('length');
        $offset = $request->input('start');
        $dataQry = $dataQry->get();
        // $visits = $visits->offset($offset)->limit($limit)->get();
        $datalist = [];
        $i = 0;
        foreach($dataQry as $list)
        {
            $button = "";
            $list->sno = ++$i .'';
            $list->status = $list->status == 1 ? "Active" : "inactive";
            $button = $list->approved == 1 ? "" : '<button class="ml-4 btn btn-success" id="update-btn'.$list->id.'" onclick="updateStatus('.$list->id.', 1)">Approve</button>';
            $list->action       = '<td align="center">
                                        <a class="pl-2" href="'.route('dailyexpense-delete', [$list->id]).'"><i style="color: red;" class="fa fa-trash"></i></a>
                                        '.$button.'
                                        <div id="spinner'.$list->id.'" style="display:none;" class="spinner-border text-primary" role="status">
                                        </div>
                                   </td>';
            $datalist[] = $list;
            // <a href="'.route('dailyexpense.edit',[$list->id]).'"><i style="color: green;" class="fa fa-edit"></i></a>
        }

        $json_data = ['data'=>$datalist];
        echo json_encode($json_data);
    }

    public function delete($id)
    {
        DailyExpenseModel::where('id', $id)->update(["status" => 0]);
        $output = array('success' => 1, 'msg' => "Daily Expense Deleted Successfully");
        return redirect()->route('dailyexpense.index')->with('status', $output);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required',
            'user_id' => 'required',
            'expense_id' => 'required',
            'date' => 'required',
        ]);

        if ($validator->fails())
        {
            $output = array('success' => 0, 'msg' => $validator->errors()->first());
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }

        $data = [];
        $data['amount'] = $request->amount;
        $data['expense_by'] = $request->user_id;
        $data['expense_id'] = $request->expense_id;
        $data['created_by'] = Auth::id();
        $data['date'] = $request->date;
        $data['bill'] = "";
        
        $result = DailyExpenseModel::create($data);
        if($result) {
            $output = array('success' => 1, 'msg' => 'Daily Expense Create Successfully');
            return redirect()->route('dailyexpense.index')->with('status', $output);
        } else {
            $output = array('failed' => 0, 'msg' => 'Failed');
            return redirect()->route('dailyexpense.index')->with('status', $output);
        }
    }
    public function update(Request $request)
    {
        $data = [];
        $data['name'] = $request->name;
        $data['mobile'] = $request->mobile;
        $data['email'] = $request->email;
        $data['address'] = $request->address;
        $data['gst'] = $request->gst;
        
        $result = DailyExpenseModel::where('id', $request->id)->update($data);
        if($result) {
            $output = array('success' => 1, 'msg' => 'vendor Updated Successfully');
            return redirect()->route('dailyexpense.index')->with('status', $output);
        }
    }

    public function approveExpense(Request $request)
    {
        DailyExpenseModel::where('id', $request->id)->update(["approved" => 1]);
        $output = array('success' => 1, 'msg' => 'Visit Updated Successfully');
        return redirect()->route('dailyexpense.index')->with('status', $output);
    }

}
