<?php

namespace App\Http\Controllers;

use App\Models\ProductsModel;
use App\Models\ExpenseModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    public function index()
    {
        $this->datas['route'] = route("expense.create");
        return view('expense.expense')->with($this->datas);
    }

    public function getExpenselist(Request $request)
    {
        $dataQry = ExpenseModel::where('status', 1);
        $limit = $request->input('length');
        $offset = $request->input('start');

        $datas = $dataQry->get();
        $datalist = [];
        $i = $offset;
        foreach($datas as $list)
        {
            $list->sno          = ++$i .'';
            $list->action       = '<td align="center">
                                        <a href="'.route('expense.edit', [$list->id]).'"><i style="color: green;" class="fa fa-edit"></i></a>
                                        <a class="pl-2" href="'.route('expense-delete', [$list->id]).'"><i style="color: red;" class="fa fa-trash"></i></a>
                                   </td>';
            $datalist[]         = $list;
        }

        $json_data = ['data'=>$datalist];
        echo json_encode($json_data);
    }

    public function create()
    {
        $this->datas['unit'] = array();
        $this->datas['route'] = route("expense.store");
        return view('expense.expenseaddedit')->with($this->datas);
    }

    public function delete($id)
    {
        ExpenseModel::where('id', $id)->update(["status" => 0]);
        $output = array('success' => 1, 'msg' => "Expense Deleted Successfully");
        return redirect()->route('expense.index')->with('status', $output);
    }

    public function edit($id)
    {
        $unit = ExpenseModel::find($id);
        $this->datas['route'] = route("expense.update", $id);
        $this->datas['unit'] = $unit;
        return view('expense.expenseaddedit')->with($this->datas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit' => 'unique:expense,name',
        ]);

        if ($validator->fails())
        {
            $output = array('success' => 0, 'msg' => $validator->errors()->first());
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }
        $data = [];
        $data['name'] = $request->unit;
        $count = ExpenseModel::where("name", $request->unit)->count();
        $output = array('success' => 1, 'msg' => 'Expense Create Successfully');
        if($count == 0) {
            ExpenseModel::create($data);
            $output = array('success' => 1, 'msg' => 'Expense Create Successfully');
        } else {
            $output = array('failed' => 0, 'msg' => 'Expense Name Already Exists');
            return redirect()->back()->with('status', $output);
        }
        return redirect()->route('unit.index')->with('status', $output);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit' => 'unique:expense,name',
        ]);

        if ($validator->fails())
        {
            $output = array('success' => 0, 'msg' => $validator->errors()->first());
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }
           
        $data = [];
        $data['name'] = $request->unit;
        ExpenseModel::where("id", $request->id)->update($data);
        $output = array('success' => 1, 'msg' => 'Expense Updated Successfully');
        return redirect()->route('expense.index')->with('status', $output);
    }

}
