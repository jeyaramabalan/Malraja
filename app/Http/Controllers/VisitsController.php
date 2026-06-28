<?php

namespace App\Http\Controllers;

use App\Models\CustomerModel;
use App\Models\ProductsModel;
use App\Models\PurposeVisitModel;
use App\Models\User;
use App\Models\VisitsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VisitsController extends Controller
{
    public function index(Request $request)
    {
        $options = "";
        
        $purpose_of_visits = PurposeVisitModel::select('id', 'text')->get();
        $purpose_of_visits_option = "";
        foreach($purpose_of_visits as $purpose_of_visit) {
            $selected = $request->purpose == $purpose_of_visit['id'] ? "selected = 'selected'" : '';
            $purpose_of_visits_option.= "<option $selected value={$purpose_of_visit['id']}>{$purpose_of_visit['text']}</option>";
        }
        
        if($request->follow == null) {
            $request->follow = "";
        }
        $follow_arr = array("1" => "Yes", "2" => "No");
        $follow_up_option = "";
        foreach($follow_arr as $id => $text) {
            $selected = "";
            $selected = $request->follow == $id ? "selected = 'selected'" : '';
            $follow_up_option.= "<option $selected value=$id>$text</option>";
        }

        $visits = VisitsModel::select(
            "visits.id",
            "customer",
            "customer.name as customer_name",
            "users.name as user_name",
            "visits.user_id",
            "visits.lattitude",
            "visits.longtitude",
            "visits.created_at as date",
            "purpose_of_visit_id",
            "purpose_of_visit.text as purpose_of_visit_name",
            DB::raw("IFNULL(`accompany_list_id`, '') AS accompany_list_id"),
            DB::raw("IFNULL(`campaign_name`, '') AS campaign_name"), 
            DB::raw("CASE WHEN follow_up_needed = 0 THEN 'No' ELSE 'Yes' END as follow_up_needed"),
            DB::raw("IFNULL(`product_list_id`, '') AS product_list_id"), 
            DB::raw("IFNULL(`quantity`, '') AS quantity"), 
            DB::raw("IFNULL(`remarks`, '') AS remarks"))
            ->leftjoin('purpose_of_visit', 'visits.purpose_of_visit_id', '=', 'purpose_of_visit.id')
            ->leftjoin('customer', 'visits.customer', '=', 'customer.id')
            ->leftjoin('users', 'visits.user_id', '=', 'users.id')
            ->orderBy('visits.created_at', 'DESC');
        
        if($request->user) {
            $visits = $visits->where('visits.user_id', $request->user);
        }
        if($request->purpose) {
            $visits = $visits->where('visits.purpose_of_visit_id', $request->purpose);
        }
        if($request->follow) {
            $visits = $visits->where('visits.follow_up_needed', $request->follow != 1 ? 0 : 1);
        }

        if($request->date) {
            $temp = explode(",", $request->date);
            $temp[1] = date("Y-m-d", strtotime($temp[1].' +1 day'));
            $visits->where('visits.created_at', '>=', $temp[0]);
            $visits->where('visits.created_at', '<=', $temp[1]);
        }

        // where based on user type
        $user = Auth::user();
        if($user['user_type'] == 1) {
            $visits = $visits->where('visits.user_id', $user['id']);
        }
        else if($user['user_type'] == 2) {
            $userIds = $this->getManagerUserIds($user['id']);
            if(!empty($userIds)) {
                $users = User::select('id', 'name')
                            ->where('status', 1)
                            ->whereIn('id', $userIds)
                            ->get();
                
                foreach($users as $user) {
                    $selected = $request->user == $user['id'] ? "selected = 'selected'" : '';
                    $options.= "<option $selected value={$user['id']}>{$user['name']}</option>";
                }
                $visits = $visits->whereIn('visits.user_id', $userIds);
            } else {
                $this->datas['visits_data'] = [];
                $this->datas['users'] = $options;
                $this->datas['purpose_options'] = $purpose_of_visits_option;
                $this->datas['follow_up_option'] = $follow_up_option;
                $this->datas['page'] = 'visits';
                return view('visits')->with($this->datas);
            }
        }
        else if($user['user_type'] == 3) {
            $users = User::select('id', 'name')
                            ->where('status', 1)
                            ->get();
                
                foreach($users as $user) {
                    $selected = $request->user == $user['id'] ? "selected = 'selected'" : '';
                    $options.= "<option $selected value={$user['id']}>{$user['name']}</option>";
                }
        }

        $visits = $visits->get();
        if($visits) {
            $visits = json_decode(json_encode($visits), true);
        }
        $visits_array = $this->appendVisitNames($visits);
        
        // $this->datas['visits_data'] = json_decode(json_encode($visits_array));
        $this->datas['users'] = $options;
        $this->datas['purpose_options'] = $purpose_of_visits_option;
        $this->datas['follow_up_option'] = $follow_up_option;
        $this->datas['page'] = 'visits';
        return view('visits')->with($this->datas);
    }

    public function getVisitList(Request $request)
    {   
        if($request->follow == null) {
            $request->follow = "";
        }
        $visits = VisitsModel::select(
            "visits.id",
            "customer",
            "customer.name as customer_name",
            "users.name as user_name",
            "visits.user_id",
            "visits.lattitude",
            "visits.longtitude",
            "visits.created_at as date",
            "purpose_of_visit_id",
            "purpose_of_visit.text as purpose_of_visit_name",
            DB::raw("IFNULL(`accompany_list_id`, '') AS accompany_list_id"),
            DB::raw("IFNULL(`campaign_name`, '') AS campaign_name"), 
            DB::raw("CASE WHEN follow_up_needed = 0 THEN 'No' ELSE 'Yes' END as follow_up_needed"),
            DB::raw("IFNULL(`product_list_id`, '') AS product_list_id"), 
            DB::raw("IFNULL(`quantity`, '') AS quantity"), 
            DB::raw("IFNULL(`remarks`, '') AS remarks"))
            ->leftjoin('purpose_of_visit', 'visits.purpose_of_visit_id', '=', 'purpose_of_visit.id')
            ->leftjoin('customer', 'visits.customer', '=', 'customer.id')
            ->leftjoin('users', 'visits.user_id', '=', 'users.id')
            ->orderBy('visits.created_at', 'DESC');
        
        $limit = $request->input('length');
        $offset = $request->input('start');
        if(isset($request->user) && !empty($request->user)) {
            $visits = $visits->where('visits.user_id', $request->user);
        }
        if(isset($request->purpose) && !empty($request->purpose)) {
            $visits = $visits->where('visits.purpose_of_visit_id', $request->purpose);
        }
        if(isset($request->follow) && $request->follow != '') {
            $visits = $visits->where('visits.follow_up_needed', $request->follow != 1 ? 0 : 1);
        } 
        // else {
        //     $visits = $visits->whereIn('visits.follow_up_needed', [0,1]);
        // }

        if(isset($request->sDate) && !empty($request->sDate)) {
            $visits->whereBetween('visits.created_at', [$request->sDate, $request->eDate]);
        }

        // where based on user type
        $user = Auth::user();
        if($user['user_type'] == 1) {
            $visits = $visits->where('visits.user_id', $user['id']);
        }
        else if($user['user_type'] == 2) {
            $userIds = $this->getManagerUserIds($user['id']);
            $visits = $visits->whereIn('visits.user_id', $userIds);
        }

        $visits = $visits->offset($offset)->limit($limit)->get();
        if($visits) {
            $visits = json_decode(json_encode($visits), true);
        }

        $visits_array = $this->appendVisitNames($visits);
        
        $visits_array = json_decode(json_encode($visits_array));
        $datalist = [];
        $i = 0;
        foreach($visits_array as $list)
        {
            $list->sno = ++$i .'';
            $list->accompany_list_id = $list->accompany_names;
            $list->product_list_id = $list->products_names;
            $list->map = '<td align="center"><a href="http://maps.google.com/maps?q={{$list->lattitude}},{{$list->longtitude}}"><i style="color: green;" class="fa fa-map"></i></a></td>';
            $list->action = '<td align="center"><a href="'.route('visits.edit',[$list->id]).'"><i style="color: green;" class="fa fa-edit"></i></a></td>';
            $datalist[] = $list;
        }

        $json_data = ['data'=>$datalist];
        echo json_encode($json_data);
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'purpose' => 'required',
            'customer' => 'required',
            'product' => 'required',
            'follow' => 'required',
        ]);

        if ($validator->fails())
        {
            $output = array('success' => 0, 'msg' => $validator->errors()->first());
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }
        $data = [];
        $data['purpose_of_visit_id'] = $request->purpose;
        $data['customer'] = $request->customer;
        $data['product_list_id'] = implode(",", $request->product);
        $data['follow_up_needed'] = $request->follow;
        if(isset($request->remark) && !empty($request->remark)) {
            $data['remarks'] = $request->remark;
        }
        if(isset($request->quantity) && !empty($request->quantity)) {
            $data['quantity'] = $request->quantity;
        }
        if(isset($request->campaign) && !empty($request->campaign)) {
            $data['campaign_name'] = $request->campaign;
        }
        if(isset($request->accompained) && count($request->accompained) > 0) {
            $data['accompany_list_id'] = implode(",", $request->accompained);
        }
        
        VisitsModel::where('id', $id)->update($data);
        $output = array('success' => 1, 'msg' => 'Visit Updated Successfully');
        return redirect()->route('customer.index')->with('status', $output);
    }

    public function edit($id)
    {
        $selVisit = VisitsModel::where('id', $id)->first();
        
        //  purpose of visit
        $purpose_of_visits = PurposeVisitModel::select('id', 'text')->get();
        $purpose_of_visits_option = "";
        foreach($purpose_of_visits as $purpose_of_visit) {
            $selected = $selVisit->purpose_of_visit_id == $purpose_of_visit['id'] ? "selected = 'selected'" : '';
            $purpose_of_visits_option.= "<option $selected value={$purpose_of_visit['id']}>{$purpose_of_visit['text']}</option>";
        }

        //  accompained
        $users = User::select('id', 'name')->get();
        $accompanied_option = "";
        foreach($users as $user) {
            $selected = '';
            if(str_contains($selVisit->accompany_list_id, $user->id)) {
                $selected = "selected = 'selected'";
            }
            $accompanied_option.= "<option $selected value={$user['id']}>{$user['name']}</option>";
        }

        //  products
        $products = ProductsModel::select('id', 'text')->get();
        $product_option = "";
        foreach($products as $product) {
            $selected = '';
            if(str_contains($selVisit->product_list_id, $user->id)) {
                $selected = "selected = 'selected'";
            }
            $product_option.= "<option $selected value={$product['id']}>{$product['text']}</option>";
        }

        // followup
        $follow_arr = array("1" => "Yes", "2" => "No");
        $follow_up_option = "";
        foreach($follow_arr as $id => $text) {
            $selected = "";
            $selected = $selVisit->follow_up_needed == $id ? "selected = 'selected'" : '';
            $follow_up_option.= "<option $selected value=$id>$text</option>";
        }

        $customer = CustomerModel::select('id', 'name');
        
        // where based on user type
        $user = Auth::user();
        if($user['user_type'] == 1) {
            $customer = $customer->where('id', $user['id']);
        }
        else if($user['user_type'] == 2) {
            $userIds = $this->getManagerUserIds($user['id']);
            $customer = $customer->whereIn('id', $userIds);
        }

        $customers = $customer->get();
        $customer_option = "";
        foreach($customers as $customer) {
            $selected = $selVisit->customer == $customer['id'] ? "selected = 'selected'" : '';
            $customer_option.= "<option $selected value={$customer['id']}>{$customer['name']}</option>";
        }

        $this->datas['route'] = route('visits.update', $selVisit->id);
        $this->datas['visit'] = $selVisit;
        $this->datas['customer_option'] = $customer_option;
        $this->datas['accompanied_option'] = $accompanied_option;
        $this->datas['purpose_options'] = $purpose_of_visits_option;
        $this->datas['follow_up_option'] = $follow_up_option;
        $this->datas['product_option'] = $product_option;
        $this->datas['page'] = 'visits';
        return view('visitedit')->with($this->datas);
    }

    public function getManagerUserIds($managerId)
    {
        $userids = User::select(DB::raw("GROUP_CONCAT(id) as ids"))->where('manager_id', $managerId)->groupBy('manager_id')->first();
        if(!empty($userids)) {
           return explode(",", $userids['ids']); 
        }
        return "";
    }

    private function appendVisitNames(array $visits): array
    {
        $userIds = [];
        $productIds = [];

        foreach($visits as $visit) {
            if(!empty($visit['accompany_list_id'])) {
                $ids = array_filter(array_map('trim', explode(',', $visit['accompany_list_id'])));
                $userIds = array_merge($userIds, $ids);
            }
            if(!empty($visit['product_list_id'])) {
                $ids = array_filter(array_map('trim', explode(',', $visit['product_list_id'])));
                $productIds = array_merge($productIds, $ids);
            }
        }

        $userNameMap = [];
        if(!empty($userIds)) {
            $userNameMap = User::whereIn('id', array_unique($userIds))->pluck('name', 'id')->toArray();
        }

        $productNameMap = [];
        if(!empty($productIds)) {
            $productNameMap = ProductsModel::whereIn('id', array_unique($productIds))->pluck('text', 'id')->toArray();
        }

        $visitsArray = [];
        foreach($visits as $visit) {
            $visit['accompany_names'] = "";
            $visit['products_names'] = "";

            if(!empty($visit['accompany_list_id'])) {
                $ids = array_filter(array_map('trim', explode(',', $visit['accompany_list_id'])));
                $names = [];
                foreach($ids as $id) {
                    if(isset($userNameMap[$id])) {
                        $names[] = $userNameMap[$id];
                    }
                }
                $visit['accompany_names'] = implode(', ', $names);
            }

            if(!empty($visit['product_list_id'])) {
                $ids = array_filter(array_map('trim', explode(',', $visit['product_list_id'])));
                $names = [];
                foreach($ids as $id) {
                    if(isset($productNameMap[$id])) {
                        $names[] = $productNameMap[$id];
                    }
                }
                $visit['products_names'] = implode(', ', $names);
            }

            $visitsArray[] = $visit;
        }

        return $visitsArray;
    }

}
// ->orderBy('customer.created_at', 'ASC')->simplePaginate(25);
// <?php echo $customer_data->render(); ?>