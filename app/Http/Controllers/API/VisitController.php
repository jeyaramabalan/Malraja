<?php

namespace App\Http\Controllers\API;
use App\Models\CustomerModel;
use App\Models\FamilyMemberModel;
use App\Models\ProductsModel;
use App\Models\User;
use App\Models\VisitsModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
Use Illuminate\Support\Facades\DB;

class VisitController extends BaseController
{
    public function addVisit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer'=>'required',
            'user_id' => 'required',
            'purpose_of_visit_id' => 'required',
            // 'latitude' => 'required',
            // 'longitude' => 'required',
        ]);
    
        if($validator->fails()) {
            return $this->sendError($validator->errors()->first(),[],200);
        }
    
        try {
    
            VisitsModel::create([
                "customer" => $request->input('customer'),
                "user_id" => $request->input('user_id'),
                "purpose_of_visit_id" => $request->input('purpose_of_visit_id'),
                "accompany_list_id" => $request->input('accompany_list_id'),
                "campaign_name" => $request->input('campaign_name'),
                "follow_up_needed" => $this->checkNullorReturn($request->input('follow_up_needed'), 0),
                "product_list_id" => $request->input('product_list_id'),
                "quantity" => $request->input('quantity'),
                "remarks" => $request->input('remarks'),
                "lattitude" => $request->input('latitude'),
                "longtitude" => $request->input('longitude'),
            ]);   
            return $this->sendEmptyResponse([], 'Visit Added successfully.');
    
        } catch(Exception $e) {
            return $this->sendError($e->getMessage(), [], 200);
        }
    }

    public function getVisit(Request $request, $isAPI = true)
    {   
        try {
    
            $visits = VisitsModel::select(
                        "visits.id",
                        "customer",
                        "customer.name as customer_name",
                        "visits.user_id",
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
                        ->get()
                        ->toArray();
            
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
            if(!$isAPI) {
                return $visits_array;
            }
            return $this->sendResponse(["visits" => $visits_array], 'Visit Retrived successfully.');
    
        } catch(Exception $e) {
            return $this->sendError($e->getMessage(), [], 200);
        }
    }

}
