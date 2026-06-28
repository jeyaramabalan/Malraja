<?php

namespace App\Http\Controllers;
use App\Models\ProductsModel;
use App\Models\UnitModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UnitController extends Controller
{
    public function index()
    {
        $this->datas['route'] = route("unit.create");
        return view('unit.unit')->with($this->datas);
    }

    public function getUnitlist(Request $request)
    {
        $dataQry = UnitModel::where('status', 1);
        $limit = $request->input('length');
        $offset = $request->input('start');

        $datas = $dataQry->get();
        $datalist = [];
        $i = $offset;
        foreach($datas as $list)
        {
            $list->sno          = ++$i .'';
            $list->action       = '<td align="center">
                                        <a href="'.route('unit.edit', [$list->id]).'"><i style="color: green;" class="fa fa-edit"></i></a>
                                        <a class="pl-2" href="'.route('unit-delete', [$list->id]).'"><i style="color: red;" class="fa fa-trash"></i></a>
                                   </td>';
            $datalist[]         = $list;
        }

        $json_data = ['data'=>$datalist];
        echo json_encode($json_data);
    }

    public function create()
    {
        $this->datas['unit'] = array();
        $this->datas['route'] = route("unit.store");
        return view('unit.unitaddedit')->with($this->datas);
    }

    public function delete($id)
    {
        UnitModel::where('id', $id)->update(["status" => 0]);
        $output = array('success' => 1, 'msg' => "Unit Deleted Successfully");
        return redirect()->route('unit.index')->with('status', $output);
    }

    public function edit($id)
    {
        $unit = UnitModel::find($id);
        $this->datas['route'] = route("unit.update", $id);
        $this->datas['unit'] = $unit;
        return view('unit.unitaddedit')->with($this->datas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit' => 'unique:unit,unit',
        ]);

        if ($validator->fails())
        {
            $output = array('success' => 0, 'msg' => $validator->errors()->first());
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }
        $data = [];
        $data['unit'] = $request->unit;
        $count = UnitModel::where("unit", $request->unit)->count();
        $output = array('success' => 1, 'msg' => 'Unit Create Successfully');
        if($count == 0) {
            UnitModel::create($data);
            $output = array('success' => 1, 'msg' => 'Unit Create Successfully');
        } else {
            $output = array('failed' => 0, 'msg' => 'Unit Name Already Exists');
            return redirect()->back()->with('status', $output);
        }
        return redirect()->route('unit.index')->with('status', $output);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit' => 'unique:unit,unit',
        ]);

        if ($validator->fails())
        {
            $output = array('success' => 0, 'msg' => $validator->errors()->first());
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }
           
        $data = [];
        $data['unit'] = $request->unit;
        UnitModel::where("id", $request->id)->update($data);
        $output = array('success' => 1, 'msg' => 'Unit Updated Successfully');
        return redirect()->route('unit.index')->with('status', $output);
    }

}
