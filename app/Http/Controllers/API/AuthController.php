<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\AttendanceModel;
use App\Models\CustomerModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\VisitsModel;
use Exception;
use Illuminate\Support\Carbon;


class AuthController extends BaseController
{

    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email'=>'required',
            'password' => 'required',
            'device_id' => 'required',
        ]);

        if($validator->fails()) {
            return $this->sendError($validator->errors()->first(),[],200);
        } else {
            try {

                $user = User::select('id', 'device_id')
                    ->where('email', $request->input('email'))
                    ->where(function ($query) use ($request) {
                        $query->where('password', $request->input('password'))
                            ->orWhere('password', md5($request->input('password')));
                    })
                    ->first();
                if(empty($user)) {
                    return $this->sendError('Invalid Credentials', [], 200);
                }

                if(!empty($user['device_id']) && $request->input('device_id') != $user['device_id']) {
                    return $this->sendError('This account has been already logged in another device.', [], 200);
                }

                if(empty($user['device_id'])) {
                    User::where('id', $user['id'])->update(['device_id' => $request->input('device_id')]);
                }
                
                if (auth()->loginUsingId($user['id'])) {
                    $token = auth()->user()->createToken('authToken')->accessToken;
                    $user = auth()->user();
                    $userDetails = ["user_id" => $user['id'], "token" => $token, "email" => $user['email'], "user_type" => $user['user_type'], "name" => $user['name']];
                    return $this->sendResponse($userDetails, 'Login successfully.');
                } else {
                    return $this->sendError('Invalid Credentials', [], 200);
                }
                
            } catch(Exception $e) {
                return $this->sendError($e->getMessage(), [], 200);
            }
        }
    }

    public function getAllUser() {
        $user = User::select('id', 'name', 'mobile_number', 'employee_id', 'dob', 'user_type', 'aadhar_number')->where('status', 1)->get();
        return $this->sendResponse(["users" => $user], 'Users Retrived successfully.');
    }

}
