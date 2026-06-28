<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;
use App\User;
use Illuminate\Routing\Route;
use Storage, Auth;

class BaseController extends Controller
{
    public function __construct(Request $request, Route $route)
    {
        $this->apiRequestLogs($request, $route);
        $this->delete10DaysAgoFiles();
    }

    
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message, $check_empty=0,$extra=[])
    {
        if(!empty($result) || $check_empty) {
            $code = 200;
            if(is_null($result)) $result = [];
            $authentication = false;
            if (Auth::check()) {
                $authentication = true;
            }
            $response = [
                'success' => true,
                'authentication' => $authentication,
                'code' => $code,
                'data'    => $result,
                'message' => $message,
                'server_time' => date('d-m-Y H:i:s')
            ];
            $res = array_merge($response,$extra);
            return response()->json($res, $code);
        } else {
            return $this->sendError('No data found.',[],200);
        }
    }


    public function sendEmptyResponse($result, $message, $check_empty=0,$extra=[])
    {
        $code = 200;
        $authentication = false;
        if (Auth::check()) {
            $authentication = true;
        }
        $response = [
            'success' => true,
            'authentication' => $authentication,
            'code' => $code,
            'data'    => $result,
            'message' => $message,
            'server_time' => date('d-m-Y H:i:s')
        ];
        $res = array_merge($response,$extra);
        return response()->json($res, $code);
    }


    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $data = [], $code = 200)
    {
        $authentication = false;
        if (Auth::check()) {
            $authentication = true;
        }
    	$response = [
            'success' => false,
            'authentication' => $authentication,
            'code' => $code,
            'data' => [],
            'message' => $error,
            'server_time' => date('d-m-Y H:i:s')
        ];

        if(!empty($data)){
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    public function apiRequestLogs($request, $route) {
        $file_name = date('d-m-Y').'_'.'api-req-log.txt';
        if($request->route()->uri == 'api/login') {
            Storage::disk('local')->append($file_name,date('d-m-Y H:I:s').' => '.$request->route()->uri.' => '.$request->method().' => '.json_encode(["email"=>$request->email,"regId"=>$request->regId]));            
        } else {
            Storage::disk('local')->append($file_name,date('d-m-Y H:I:s').' => '.$request->route()->uri.' => '.$request->method().' => '.json_encode($request->all()));
        }
    }

    public function delete10DaysAgoFiles() {
        $allFiles = Storage::files('');
		if(!empty($allFiles)) {
            $days_ago = date('d-m-Y', strtotime('-10 days'));                    
			foreach ($allFiles as $path) {
				if (strpos($path, '_api-req-log.txt') == true) {
                    $file_date = str_replace('_api-req-log.txt','',$path);
                    if(strtotime($file_date)<strtotime($days_ago)) {
                        Storage::delete($path);
                    }
				}		
			}			
        }
    }

    public function checkNullorReturn($value, $returnValue) {
        if(empty($value)) {
            return $returnValue;
        } else {
            return $value;
        }
    }

    
        // foreach($datas as $data => $val) {
        //     VisitsModel::create([
        //         "customer" => $findIdJson[$data],
        //         "user_id" => 1,
        //         "purpose_of_visit_id" => $val['purpose_of_visit_id'],
        //         "accompany_list_id" => $val['accompany_list_id'],
        //         "campaign_name" => $val['campaign_name'],
        //         "follow_up_needed" => $val['follow_up_needed'] ? 1 : 0,
        //         "product_list_id" => $val['product_list_id'],
        //         "quantity" => $val['qty'],
        //         "remarks" => $val['remarks'],
        //     ]);   
        // }
        // $findIdArr = [];
        // foreach($datas as $data => $val) {
        //     $result = CustomerModel::select('id')->where('mobile_number', $val['mobile_number'])->get()->toArray();
        //     $findIdArr[$data] = $result[0]['id'];
        // }
        // print_r(json_encode($findIdArr));die;
    
}