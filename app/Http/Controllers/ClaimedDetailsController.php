<?php

namespace App\Http\Controllers;

use App\Models\ClaimedDetailsModel;
use App\Models\User;
use App\Models\VendorModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ClaimedDetailsController extends Controller
{
    public function index()
    {
        $this->datas['route'] = route("claimeddetails.create");
        return view('claimed_details.claimed_details')->with($this->datas);
    }

    public function getClaimedlist(Request $request)
    {
        $dataQry = ClaimedDetailsModel::select(
                        'claimed_details.id',
                        'claimed_details.amount',
                        'claimed_details.date',
                        'users.name as user_name',
                        'vendor.name as vendor_name',
                    )
                    ->leftjoin('vendor', 'claimed_details.vendor_id', '=', 'vendor.id')
                    ->leftjoin('users', 'claimed_details.claimed_by', '=', 'users.id')
                    ->where('claimed_details.status', 1);
        $limit = $request->input('length');
        $offset = $request->input('start');

        $datas = $dataQry->get();
        $datalist = [];
        $i = $offset;
        foreach($datas as $list)
        {
            $list->sno          = ++$i .'';
            $list->action       = '<td align="center">
                                        <a class="pl-2" href="'.route('claimed-details-delete', [$list->id]).'"><i style="color: red;" class="fa fa-trash"></i></a>
                                   </td>';
            $datalist[]         = $list;
            
            // <a href="'.route('claimeddetails.edit', [$list->id]).'"><i style="color: green;" class="fa fa-edit"></i></a>
        }

        $json_data = ['data'=>$datalist];
        echo json_encode($json_data);
    }

    public function create()
    {
        $users = User::where('status', 1)->get();
        $admin_option = "";
        foreach($users as $user) {
            $admin_option.= "<option value={$user['id']}>{$user['name']}</option>";
        }

        $vendors = VendorModel::select('id', 'name')->where('status', 1)->get();
        $vendors_option = "";
        foreach($vendors as $vendor) {
            $vendors_option.= "<option value={$vendor['id']}>{$vendor['name']}</option>";
        }

        $this->datas['vendors_option'] = $vendors_option;
        $this->datas['admin_option'] = $admin_option;
        $this->datas['route'] = route("claimeddetails.store");
        return view('claimed_details.claimed_detailsaddedit')->with($this->datas);
    }

    public function delete($id)
    {
        ClaimedDetailsModel::where('id', $id)->update(["status" => 0]);
        $output = array('success' => 1, 'msg' => "Claimed Details Deleted Successfully");
        return redirect()->route('claimeddetails.index')->with('status', $output);
    }

    public function edit($id)
    {
        $unit = ClaimedDetailsModel::find($id);
        $this->datas['route'] = route("claimeddetails.update", $id);
        $this->datas['unit'] = $unit;
        return view('claimed_details.claimed_detailsaddedit')->with($this->datas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required',
            'claimed_by' => 'required',
            'amount' => 'required',
            'date' => 'required',
        ]);

        if ($validator->fails())
        {
            $output = array('success' => 0, 'msg' => $validator->errors()->first());
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }
        $data = [];
        $data['vendor_id'] = $request->vendor_id;
        $data['claimed_by'] = $request->claimed_by;
        $data['amount'] = $request->amount;
        $data['date'] = $request->date;
        
        if($request->desc) {
            $data['description'] = $request->desc;
        }

        $data['created_by'] = Auth::id();
        ClaimedDetailsModel::create($data);
        $output = array('success' => 1, 'msg' => 'Claim Create Successfully');
        return redirect()->route('claimeddetails.index')->with('status', $output);
    }

    public function update(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'unit' => 'unique:expense,name',
        // ]);

        // if ($validator->fails())
        // {
        //     $output = array('success' => 0, 'msg' => $validator->errors()->first());
        //     return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        // }
           
        // $data = [];
        // $data['name'] = $request->unit;
        // ClaimedDetailsModel::where("id", $request->id)->update($data);
        // $output = array('success' => 1, 'msg' => 'Expense Updated Successfully');
        // return redirect()->route('claimeddetails.index')->with('status', $output);
    }

}
