<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AdminLoginController extends Controller
{
    protected function guard()
    {
        return auth()->guard('web');
    }

    public function __construct()
    {
        $this->middleware('web')->except('postLogout');
        $this->loginRoute = route('login');
    }

    public function postLogout()
    {
        Auth::guard('web')->logout();
        return redirect('login');
    }

    public function index()
    {
        return view('login');
    }

    public function postLogin(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required'
        ]);

        $credential = [
            'email' => $request->input('email'),
            'password' => md5($request->input('password'))
        ];

        $chk_user = User::select('id')->where('email',$request->input('email'))->where('password', $request->input('password'))->first();
        if($chk_user) {
            if (Auth::guard('web')->loginUsingId($chk_user['id'])) {
                $user = User::find(1);
                return redirect()->intended('dashboard');
            }
        } else {
            $output = array('success'=>0,'msg'=> 'Incorrect user login credentials!');
            return redirect()->back()->withInput()->with('error',$output);
        }
        // if($chk_user && $chk_user->status == 0)
        // {
        //     $output = array('success'=>0,'msg'=> 'Your Account is Blocked.  Please Contact Admin');
        //     return redirect()->back()->withInput()->with('error',$output);
        // }
        // print_r($credential);die;
        
    }
}
