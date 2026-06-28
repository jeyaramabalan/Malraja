<?php

namespace App\Http\Controllers;

use App\Models\VendorModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    public function index()
    {
        $vendor = VendorModel::get();
        $this->datas['vendor'] = $vendor;
        $this->datas['route'] = route("vendor.create");
        // $this->datas['editRoute'] = route("vendor.edit");
        return view('vendor.vendor')->with($this->datas);
    }

    public function create()
    {
        $this->datas['vendor'] = array();
        $this->datas['route'] = route("vendor.store");
        return view('vendor.vendoraddedit')->with($this->datas);
    }

    public function edit($id)
    {
        $vendor = VendorModel::find($id);
        $this->datas['route'] = route("vendor.update", $id);
        $this->datas['vendor'] = $vendor;
        return view('vendor.vendoraddedit')->with($this->datas);
    }


    public function getvendorlist(Request $request)
    {
        $visits = VendorModel::where('status', 1)->orderBy('created_at', 'DESC');
        $limit = $request->input('length');
        $offset = $request->input('start');
        $visits = $visits->offset($offset)->limit($limit)->get();
        $datalist = [];
        $i = 0;
        foreach($visits as $list)
        {
            $list->sno = ++$i .'';
            $list->status = $list->status == 1 ? "Active" : "inactive";
            $list->action       = '<td align="center">
                                        <a href="'.route('vendor.edit',[$list->id]).'"><i style="color: green;" class="fa fa-edit"></i></a>
                                        <a class="pl-2" href="'.route('vendor-delete', [$list->id]).'"><i style="color: red;" class="fa fa-trash"></i></a>
                                   </td>';
            $datalist[] = $list;
        }

        $json_data = ['data'=>$datalist];
        echo json_encode($json_data);
    }

    public function delete($id)
    {
        VendorModel::where('id', $id)->update(["status" => 0]);
        $output = array('success' => 1, 'msg' => "Vendor Deleted Successfully");
        return redirect()->route('vendor.index')->with('status', $output);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'unique:vendor,mobile',
            'gst' => 'unique:vendor,gst',
        ]);

        if ($validator->fails())
        {
            $output = array('success' => 0, 'msg' => $validator->errors()->first());
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }

        $data = [];
        $data['name'] = $request->name;
        $data['mobile'] = $request->mobile;
        $data['email'] = $request->email;
        $data['address'] = $request->address;
        $data['gst'] = $request->gst;
        
        $result = VendorModel::create($data);
        if($result) {
            $output = array('success' => 1, 'msg' => 'vendor Create Successfully');
            return redirect()->route('vendor.index')->with('status', $output);
        } else {
            $output = array('failed' => 0, 'msg' => 'vendor Name Already Exists');
            return redirect()->route('vendor.index')->with('status', $output);
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
        
        $result = VendorModel::where('id', $request->id)->update($data);
        if($result) {
            $output = array('success' => 1, 'msg' => 'vendor Updated Successfully');
            return redirect()->route('vendor.index')->with('status', $output);
        }
    }
}
