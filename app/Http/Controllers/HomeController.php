<?php

namespace App\Http\Controllers;

use App\Work_Hour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Notifications;
use App\MultipleDepartments;

class HomeController extends Controller
{
    private $actuall_date;
    private $actuall_hour;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->actuall_date = date("Y-m-d");
        $this->actuall_hour = date("H:i:s");
    }


    public function index()
    {
        if ($this->checkStatusWork() == 3 || $this->checkStatusWork() == 4) {
            $register_start = Work_Hour::where('date',$this->actuall_date)
                ->where('id_user',Auth::id())
                ->pluck('register_start')
                ->first();

            $register_stop = Work_Hour::where('date',$this->actuall_date)
                ->where('id_user',Auth::id())
                ->pluck('register_stop')
                ->first();
        } else {
            $register_start = 0;
            $register_stop = 0;
        }

        return view('home.index')
        ->with('status',$this->checkStatusWork())
        ->with('register_start', $register_start)
        ->with('register_stop', $register_stop);
    }

    public function startWork(Request $request)
    {
        if($request->ajax() && $this->checkStatusWork() == 0)
        {
            $work_hour = new Work_Hour;
            $work_hour->status = 1;
            $work_hour->accept_sec = 0;
            $work_hour->success = 0;
            $work_hour->date = $this->actuall_date;
            $work_hour->click_start = $this->actuall_hour;
            $work_hour->id_user = Auth::id();
            $work_hour->created_at = date('Y-m-d H:i:s');
            $work_hour->save();
            return 'success';
        }else{
            return 'fail';
        }
    }

    public function stopWork(Request $request)
    {
        if($request->ajax() && $this->checkStatusWork() == 1)
        {
            Work_Hour::where('id_user', Auth::id())
                ->where('date',$this->actuall_date)
                ->update(['status' => 2,'click_stop' => $this->actuall_hour, 'updated_at' => date('Y-m-d H:i:s')]);
            return 'success';
        }else{
            return 'fail';
        }
    }

    public function checkStatusWork()
    {
        try{
            $status = Work_Hour::where('date',$this->actuall_date)
                ->where('id_user',Auth::id())
                ->pluck('status')
                ->first();
            if(empty($status)){
                return 0;
            }
            return $status;
        }catch(\Exception $e){
            return -1;
        }
    }
    public function admin()
    {
        return view('admin');
    }

    public function changeDepartment(Request $request) {
        if($request->ajax()) {
            $user = User::find(Auth::user()->id);
            $access = false;
            $multiple_departments = MultipleDepartments::all();
            foreach($multiple_departments as $department) {
                if ($department->user_id == $user->id) {
                    if ($department->department_info_id == $request->department_info_id) {
                        $access = true;
                    }
                }
            }
            if ($access === true) {
                $user->department_info_id = $request->department_info_id;
                $user->save();
                return 1;
            }
        }
    }

    public function itSupport(Request $request) {
        if($request->ajax()) {
            $notifications = Notifications::with('user')
            ->with('notification_type')
                ->where('status', 1)->orderBy('notification_type_id', 'asc')->get();

            return $notifications;
        }
    }

    public function itCountNotifications(Request $request) {
        if($request->ajax()) {
            $notifications_count = Notifications::where('status', 1)->count();

            return $notifications_count;
        }
    }

    /**
     * Pobieranie powiadomień bootstrapowych 
     */
    public function getBootstrapNotifications(Request $request) {
        if ($request->ajax()) {
            return 1;
        }
    }
}
