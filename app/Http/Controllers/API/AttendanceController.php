<?php

namespace App\Http\Controllers\API;
use App\Models\AttendanceModel;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AttendanceController extends BaseController
{
    public function addAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'=>'required',
            'check_in_status' => 'required',
        ]);

        if($validator->fails()) {
            return $this->sendError($validator->errors()->first(),[],200);
        }

        try {

            if($request->check_in_status == 1) {
                $isExist = AttendanceModel::select('id')->where('user_id', $request->user_id)->where('date', date("Y-m-d"))->first();
                if($isExist) {
                    return $this->sendError("Already Attendance Added", [], 200);
                } else {
                    AttendanceModel::create([
                        "user_id" => $request->user_id,
                        "date" => date("Y-m-d"),
                        "check_in_status" => $request->check_in_status,
                        "check_in_latitude" => $request->check_in_latitude,
                        "check_in_longitude" => $request->check_in_longitude,
                        "check_out_latitude" => $request->check_out_latitude,
                        "check_out_longitude" => $request->check_out_longitude,
                        "check_in_time" => date('h:i'),
                        "check_out_time" => $request->check_out_time,
                    ]);
                }
                return $this->sendEmptyResponse([], 'Attendance Added successfully.');
            } else {
                $isExist = AttendanceModel::select('id')->where('check_in_status', $request->check_in_status)->where('user_id', $request->user_id)->where('date', date("Y-m-d", strtotime($request->date)))->first();
                if($isExist) {
                    return $this->sendError("Already Attendance Updated", [], 200);
                } else {
                    AttendanceModel::where('user_id', $request->user_id)->where('date', date("Y-m-d"))->update([
                        "check_in_status" => $request->check_in_status,
                        "check_out_latitude" => $request->check_out_latitude,
                        "check_out_longitude" => $request->check_out_longitude,
                        "check_out_time" => date('h:i'),
                    ]);
                }
                
                return $this->sendEmptyResponse([], 'Attendance Updated successfully.');
            }

        } catch(Exception $e) {
            return $this->sendError($e->getMessage(), [], 200);
        }
    }
}
