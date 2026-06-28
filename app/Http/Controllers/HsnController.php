<?php

namespace App\Http\Controllers;
use App\Models\HsnModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HsnController extends Controller
{
    public function index()
    {
        $this->datas['route'] = route("hsn.create");
        return view('hsn.hsn')->with($this->datas);
    }

    public function create()
    {
        $this->datas['route'] = route("hsn.store");
        return view('hsn.hsnaddedit')->with($this->datas);
    }

    public function edit($id)
    {
        $hsn = HsnModel::find($id);
        $this->datas['route'] = route("hsn.update", $id);
        $this->datas['hsn'] = $hsn;
        return view('hsn.hsnaddedit')->with($this->datas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hsn' => 'unique:hsn,hsn',
        ]);

        if ($validator->fails())
        {
            $output = array('success' => 0, 'msg' => $validator->errors()->first());
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }
        $data = [];
        $data['hsn'] = $request->hsn;
        HsnModel::create($data);
        $output = array('success' => 1, 'msg' => 'HSN Created Successfully');
        return redirect()->route('hsn.index')->with('status', $output);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hsn' => 'unique:hsn,hsn',
        ]);

        if ($validator->fails())
        {
            $output = array('success' => 0, 'msg' => $validator->errors()->first());
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }
        $data = [];
        $data['hsn'] = $request->hsn;
        HsnModel::where('id', $request->id)->update($data);
        $output = array('success' => 1, 'msg' => 'HSN Updated Successfully');
        return redirect()->route('hsn.index')->with('status', $output);
    }

    public function gethsnlist(Request $request)
    {
        $visits = HsnModel::where('status', 1)->orderBy('created_at', 'DESC');
        $limit = $request->input('length');
        $offset = $request->input('start');
        $visits = $visits->offset($offset)->limit($limit)->get();
        $datalist = [];
        $i = 0;
        foreach($visits as $list)
        {
            $list->sno = ++$i .'';
            $list->status = $list->status == 1 ? "Active" : "Inactive";
            $list->action       = '<td align="center">
                                        <a href="'.route('hsn.edit',[$list->id]).'"><i style="color: green;" class="fa fa-edit"></i></a>
                                        <a class="pl-2" href="'.route('hsn-delete', [$list->id]).'"><i style="color: red;" class="fa fa-trash"></i></a>
                                   </td>';
            $datalist[] = $list;
        }

        $json_data = ['data'=>$datalist];
        echo json_encode($json_data);
    }

}
