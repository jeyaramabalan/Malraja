<?php

namespace App\Http\Controllers\API;

use App\Models\AttendanceModel;
use App\Models\CustomerModel;
use App\Models\FamilyMemberModel;
use App\Models\ProductsModel;
use App\Models\User;
use App\Models\VisitsModel;
use Exception;
use PDF;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
Use Illuminate\Support\Facades\DB;

class CustomerController extends BaseController
{
    public function createCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'=>'required',
            'mobile_number' => 'required|unique:customer',
            'address' => 'required',
            'area_id' => 'required',
            'user_id' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        if($validator->fails()) {
            return $this->sendError($validator->errors()->first(),[],200);
        }

        try {

            $customer = CustomerModel::create([
                "name" => $request->input('name'),
                "mobile_number" => $request->input('mobile_number'),
                "address" => $request->input('address'),
                "area_id" => $request->input('area_id'),
                "user_id" => $request->input('user_id'),
                "date_of_birth" => $request->input('date_of_birth'),
                "wedding_date" => $request->input('wedding_date'),
                "latitude" => $request->input('latitude'),
                "longitude" => $request->input('longitude'),
            ]);   
            
            // $customer = [];   
            // $customer[] = [];   
            
            // if($request->family_members && $customer) {
            //     $family_members = json_decode($request->family_members, true);return $this->sendEmptyResponse([$family_members], 'Customer Created successfully.');
            //     foreach($family_members as $family_member) {
            //         FamilyMemberModel::create([
            //             "name" => $family_member['name'],
            //             "customer_id" => $customer['id'],
            //             "relation_id" => $family_member['relation_id'],
            //             "dob" => $family_member['dob'],
            //         ]);
            //     }
            // }

            return $this->sendEmptyResponse(['customer_id' => $customer['id']], 'Customer Created successfully.');

        } catch(Exception $e) {
            return $this->sendError($e->getMessage(), [], 200);
        }
    }

    public function updateCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'=>'required',
            'address' => 'required',
            'area_id' => 'required',
            'id' => 'required',
        ]);

        if($validator->fails()) {
            return $this->sendError($validator->errors()->first(),[],200);
        }

        try {

            if($request->input('mobile_number')) {
                $isExist = CustomerModel::select('mobile_number')->where('mobile_number', $request->input('mobile_number'))->where('id', '!=', $request->input('id'))->first();
                if($isExist) {
                    return $this->sendError("Mobile number already taken",[],200);
                }
                CustomerModel::where('id', $request->input('id'))->update([
                    "name" => $request->input('name'),
                    "mobile_number" => $request->input('mobile_number'),
                    "address" => $request->input('address'),
                    "area_id" => $request->input('area_id'),
                    "date_of_birth" => $request->input('date_of_birth'),
                    "wedding_date" => $request->input('wedding_date'),
                ]);
            } else {
                CustomerModel::where('id', $request->input('id'))->update([
                    "name" => $request->input('name'),
                    "address" => $request->input('address'),
                    "area_id" => $request->input('area_id'),
                    "date_of_birth" => $request->input('date_of_birth'),
                    "wedding_date" => $request->input('wedding_date'),
                ]);
            }
            
            return $this->sendEmptyResponse([], 'Customer Updated successfully.');
            
        } catch(Exception $e) {
            return $this->sendError($e->getMessage(), [], 200);
        }
    }

    public function updateFamilyMember(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'=>'required',
            'relation_id' => 'required',
            'dob' => 'required',
            'id' => 'required',
        ]);

        if($validator->fails()) {
            return $this->sendError($validator->errors()->first(),[],200);
        }

        try {

            FamilyMemberModel::where('id', $request->input('id'))->update([
                "name" => $request->input('name'),
                "relation_id" => $request->input('relation_id'),
                "dob" => $request->input('dob'),
            ]);

            return $this->sendEmptyResponse([], 'Familymember Updated successfully.');
            
        } catch(Exception $e) {
            return $this->sendError($e->getMessage(), [], 200);
        }
    }

    public function addFamilyMember(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'=>'required',
            'relation_id' => 'required',
            'dob' => 'required',
            'customer_id' => 'required',
        ]);

        if($validator->fails()) {
            return $this->sendError($validator->errors()->first(),[],200);
        }

        try {

            $member = FamilyMemberModel::create([
                "name" => $request->name,
                "customer_id" => $request->customer_id,
                "relation_id" => $request->relation_id,
                "dob" => $request->dob,
            ]);
            return $this->sendEmptyResponse(['family_member_id' => $member['id']], 'Familymember Added successfully.');
            
        } catch(Exception $e) {
            return $this->sendError($e->getMessage(), [], 200);
        }
    }

    public function getCustomers(Request $request)
    {
        try {
            $customer = CustomerModel::select(DB::raw("IFNULL(`wedding_date`, '') AS wedding_date"), DB::raw("IFNULL(`date_of_birth`, '') AS date_of_birth"), 'customer.id', 'name', 'mobile_number', 'address', 'customer.area_id', 'places.text as area_name')
                        ->leftjoin('places', 'customer.area_id', '=', 'places.id')
                        ->where('customer.status', 1);

            if($request->user_id) {
                $customer->where('customer.user_id', $request->user_id);
            }
            if($request->name) {
                $customer->where('customer.name', 'like', '%' . $request->name . '%');
            }
            if($request->id) {
                $customer->where('customer.id', $request->id);
            }

            // where based on user type
            if($request->user_type == 1) {
                $customer = $customer->where('customer.user_id', $request->user_id);
            }
            else if($request->user_type == 2) {
                $userIds = $this->getManagerUserIds($request->user_id);
                if(!empty($userIds)) {
                    $customer = $customer->whereIn('customer.user_id', $userIds);
                }
            }

            $customers = $customer->orderBy('customer.created_at', 'DESC')->get();

            $customer_array = [];
            foreach($customers as $customer) {
                $customer['family_member'] = [];
                $customer['visits'] = [];
                $familyMember = FamilyMemberModel::select('family_members.id', 'relation_id', 'relative_types.text AS relation_text', 'name', 'dob')
                                ->leftjoin('relative_types', 'family_members.relation_id', '=', 'relative_types.id')
                                ->where('customer_id', $customer['id'])->get();
                if($familyMember) {
                    $customer['family_member'] = $familyMember;
                }

                $visits = VisitsModel::select(DB::raw("CASE WHEN follow_up_needed = 0 THEN 'No' ELSE 'Yes' END AS follow_up_needed"),"visits.id","customer","user_id","purpose_of_visit_id","purpose_of_visit.text as purpose_of_visit_name",DB::raw("IFNULL(`accompany_list_id`, '') AS accompany_list_id"),DB::raw("IFNULL(`campaign_name`, '') AS campaign_name"),DB::raw("IFNULL(`product_list_id`, '') AS product_list_id"), DB::raw("IFNULL(`quantity`, '') AS quantity"), DB::raw("IFNULL(`remarks`, '') AS remarks"))
                        ->leftjoin('purpose_of_visit', 'visits.purpose_of_visit_id', '=', 'purpose_of_visit.id')
                        ->where('customer', $customer['id'])
                        ->get()
                        ->toArray();
                // print_r($visits);die;
                $visits_array = [];
                foreach($visits as $visit) {
                    $visit['accompany_names'] = "";
                    $visit['products_names'] = "";
                    if(!empty($visit['accompany_list_id'])) {
                        $accompany = User::select(DB::raw("GROUP_CONCAT(name) AS accompany_names"))->whereIn('id', explode(",", $visit['accompany_list_id']))->get()->toArray();
                        $visit['accompany_names'] = $accompany[0]['accompany_names'];
                    }
                    if(!empty($visit['product_list_id'])) {
                        $accompany = ProductsModel::select(DB::raw("GROUP_CONCAT(text) AS products_names"))->whereIn('id', explode(",", $visit['product_list_id']))->get()->toArray();
                        $visit['products_names'] = $accompany[0]['products_names'];
                    }
                    $visits_array[] = $visit;
                }

                if($visits_array) {
                    $customer['visits'] = $visits_array;
                }

                $customer_array[] = $customer;
            }

            $check_in_status = 0;
            $todayDate = date("Y-m-d");
            $attendance_status = AttendanceModel::select('check_in_status')
                    ->where('user_id', $request->user_id)
                    ->where('date', "$todayDate")->first();

            if($attendance_status) {
                $check_in_status = $attendance_status->check_in_status;
            }
            return $this->sendResponse(["customer" => $customer_array, "check_in_status" => $check_in_status], "Customer retrived succesfully");
        } catch(Exception $e) {
            return $this->sendError($e->getMessage(), [], 200);
        }
    }

    public static function customerPdf(Request $request){
        
        $customer = CustomerModel::select(DB::raw("IFNULL(`wedding_date`, '') AS wedding_date"), DB::raw("IFNULL(`date_of_birth`, '') AS date_of_birth"), 'customer.id', 'name', 'mobile_number', 'address', 'places.text as area_name')
                        ->leftjoin('places', 'customer.area_id', '=', 'places.id')
                        ->where('customer.status', 1);

            if($request->user_id) {
                $customer->where('customer.user_id', $request->user_id);
            }
            if($request->area_id) {
                $customer->where('customer.area_id', $request->area_id);
            }
            $customers = $customer->get();

        $view = view('pdf.report')->with(compact('customers'));
        $html = $view->render();
        $file_name = "report.pdf";
        $pdf = PDF::loadHTML($html)->setPaper('a4', 'landscape')->save(public_path("customer_pdf/$file_name"));
        return $this->sendResponse(["report" => "https://innoblitz.in/demo/sales/public/customer_pdf/report.pdf"], "Report generated succesfully");
    }

}
