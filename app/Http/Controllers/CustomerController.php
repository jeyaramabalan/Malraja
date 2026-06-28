<?php

namespace App\Http\Controllers;

use App\Models\CustomerModel;
use App\Models\PlaceModel;
use App\Models\RouteModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $this->datas['route'] = route('customer.create');
        $this->datas['page'] = 'customer';
        return view('customer.customer')->with($this->datas);
    }

    public function getcustomerlist(Request $request)
    {
        $dataQry = CustomerModel::select('customer.id', 'customer.name', 'customer.mobile', 
        'customer.email', 'customer.address', 'gst', 'customer.aadhar_number', 'customer.status', 'users.name as user_name')
        ->leftjoin('users', 'customer.created_by', '=', 'users.id');
        $limit = $request->input('length');
        $offset = $request->input('start');
        // print_r($request->search['value']);die; 
        if(isset($request->search) && isset($request->search['value']) && $request->search['value'] != '') {
            $dataQry = $dataQry->orwhere('customer.name', 'like', '%'.$request->search['value'].'%');
            $dataQry = $dataQry->orwhere('customer.mobile', 'like', '%'.$request->search['value'].'%');
        } else {
           $dataQry = $dataQry->where('customer.status', 1);
        }
        $datas = $dataQry->get();
        $datalist = [];
        $i = $offset;
        foreach($datas as $list)
        {
            if($list->status != 1) {
                continue;
            }
            $list->sno          = ++$i .'';
            $list->action       = '<td align="center">
                                        <a href="'.route('customer.edit',[$list->id]).'"><i style="color: green;" class="fa fa-edit"></i></a>
                                        <a class="pl-2" href="'.route('customer-delete', [$list->id]).'"><i style="color: red;" class="fa fa-trash"></i></a>
                                   </td>';
            $datalist[]         = $list;
        }

        $json_data = ['data'=>$datalist];
        echo json_encode($json_data);
    }

    public function delete($id)
    {
        CustomerModel::where('id', $id)->update(["status" => 0]);
        $output = array('success' => 0, 'msg' => "Customer Deleted Successfully");
        return redirect()->route('customer.index')->with('status', $output);
    }

    public function edit($id)
    {
        $customer = CustomerModel::find($id);
        $categories = RouteModel::select('id', 'name')->where('status', 1)->get();
        $category_option = "";
        foreach($categories as $category) {
            $selected = "";
            if($category['id'] == $customer->route) {
                $selected = "selected";    
            }
            $category_option.= "<option $selected value={$category['id']}>{$category['name']}</option>";
        }
        $this->datas['route_option'] = $category_option;
        $this->datas['route'] = route('customer.update', $id);
        $this->datas['customer'] = $customer;
        return view('customer.customeraddedit')->with($this->datas);
    }

    public function create()
    {
        $categories = RouteModel::select('id', 'name')->where('status', 1)->get();
        $category_option = "";
        foreach($categories as $category) {
            $category_option.= "<option value={$category['id']}>{$category['name']}</option>";
        }

        $this->datas['customer'] = array();
        $this->datas['route'] = route('customer.store');
        $this->datas['route_option'] = $category_option;
        return view('customer.customeraddedit')->with($this->datas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:customer,name',
            'address' => 'required',
        ]);

        if ($validator->fails())
        {
            $output = array('success' => 0, 'msg' => $validator->errors()->first());
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }

        $data = [];
        $data['name'] = $request->name;
        $data['mobile'] = $request->mobile;
        $data['address'] = $request->address;
        $data['created_by'] = Auth::id();
        if(isset($request->email)) {
            $data['email'] = $request->email;
        }
        if(isset($request->route)) {
            $data['route'] = $request->route;
        }
        if(isset($request->aadhar_number)) {
            $data['aadhar_number'] = $request->aadhar_number;
        }
        if(isset($request->gst)) {
            $data['gst'] = $request->gst;
        }
        
        CustomerModel::create($data);
        $output = array('success' => 1, 'msg' => 'Customer Created Successfully');
        return redirect()->route('customer.index')->with('status', $output);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails())
        {
            $output = array('success' => 0, 'msg' => $validator->errors()->first());
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }

        $data = [];
        $data['name'] = $request->name;
        $data['address'] = $request->address;
        $data['created_by'] = Auth::id();
        if(isset($request->mobile)) {
            $data['mobile'] = $request->mobile;
        }
        if(isset($request->email)) {
            $data['email'] = $request->email;
        }
        if(isset($request->route)) {
            $data['route'] = $request->route;
        }
        if(isset($request->aadhar_number)) {
            $data['aadhar_number'] = $request->aadhar_number;
        }
        if(isset($request->gst)) {
            $data['gst'] = $request->gst;
        }
        
        CustomerModel::where('id', $request->id)->update($data);
        $output = array('success' => 1, 'msg' => 'Customer Updated Successfully');
        return redirect()->route('customer.index')->with('status', $output);
    }

}