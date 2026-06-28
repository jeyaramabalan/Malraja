<?php

namespace App\Http\Controllers;

use App\Models\AttendanceModel;
use App\Models\CustomerModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $attendance = AttendanceModel::select(
                        DB::raw("IFNULL(`check_out_latitude`, '') AS check_out_latitude"), 
                        DB::raw("IFNULL(`check_out_longitude`, '') AS check_out_longitude"),  
                        DB::raw("IFNULL(`check_out_time`, '') AS check_out_time"),
                        'users.name AS user_name',
                        'check_in_latitude',
                        'check_in_longitude',
                        'check_in_time',
                        'attendance.created_at AS date',
                        'check_in_status',
                    )
        ->leftjoin('users', 'attendance.user_id', '=', 'users.id')
        ->orderBy('attendance.created_at', 'ASC');

        if($request->date) {
            $temp = explode(",", $request->date);
            $temp[1] = date("Y-m-d", strtotime($temp[1].' +1 day'));
            $attendance->where('attendance.created_at', '>=', $temp[0]);
            $attendance->where('attendance.created_at', '<=', $temp[1]);
        }

        // where based on user type
        $user = Auth::user();
        if($user['user_type'] == 1) {
            $visits = $attendance->where('attendance.user_id', $user['id']);
        }
        else if($user['user_type'] == 2) {
            $userIds = $this->getManagerUserIds($user['id']);
            if(!empty($userIds)) {
                $attendance = $attendance->whereIn('attendance.user_id', $userIds);
            } else {
                $this->datas['attendance_data'] = $attendance;
                return view('attendance')->with($this->datas);
            }
        }

        $attendance = $attendance->get();

        $this->datas['attendance_data'] = $attendance;
        return view('attendance')->with($this->datas);
    }

    public function getManagerUserIds($managerId)
    {
        $userids = User::select(DB::raw("GROUP_CONCAT(id) as ids"))->where('manager_id', $managerId)->groupBy('manager_id')->first();
        if(!empty($userids)) {
           return explode(",", $userids['ids']); 
        }
        return "";
    }
}