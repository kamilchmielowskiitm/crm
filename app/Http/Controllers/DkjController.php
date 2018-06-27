<?php

namespace App\Http\Controllers;

use App\Agencies;
use App\Department_info;
use App\Dkj;
use App\JankyPenatlyProc;
use App\User;
use App\Work_Hour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\ActivityRecorder;
use Illuminate\Support\Facades\Redirect;
use Yajra\DataTables\Facades\DataTables;

class DkjController extends Controller
{
    public function dkjRaportGet()
    {
        $departments =  $this->getDepartment();

        $today = date('Y-m-d') . "%";
        $dkj_user = Dkj::where('id_dkj', Auth::user()->id)
            ->where('add_date', 'like', $today)
            ->count();

        $user_yanek = Dkj::where('id_dkj', Auth::user()->id)
            ->where('add_date', 'like', $today)
            ->where('dkj_status', 1)
            ->count();


        return view('dkj.dkjRaport')
            ->with('departments',$departments)
            ->with('user_yanek',$user_yanek)
            ->with('dkj_user', $dkj_user);
    }
    public function showDkjEmployeeGet()
    {
        $dkjEmployee = User::where('user_type_id',2)
            ->where('status_work', '=', 1)
            ->orderBy('last_name')
            ->get();
        return view('dkj.showDkjEmployee')
            ->with('dkjEmployee',$dkjEmployee);
    }

    public function datatableDkjShowEmployee(Request $request)
    {
        $janky_status = $request->janky_status;
        $date_start = $request->start_date;
        $date_stop = $request->stop_date;
        $user_dkj_id = $request->user_dkj_id;

        $employee_info =  DB::table('dkj')
            ->join('users', 'dkj.id_user', '=', 'users.id')
            ->join('department_info', 'users.department_info_id', '=', 'department_info.id')
            ->join('departments', 'department_info.id_dep', '=', 'departments.id')
            ->join('department_type', 'department_info.id_dep_type', '=', 'department_type.id')
            ->select(DB::raw('dkj.*,users.first_name,users.last_name,department_type.name as type_name,departments.name'))

            ->whereBetween('add_date',[$date_start.=' 00:00:00',$date_stop.=' 23:00:00']);
        if($user_dkj_id != -1)
        {
            $employee_info = $employee_info->where('id_dkj',$user_dkj_id);
        }
        if($janky_status == 1) // tylko janki
        {
            $employee_info = $employee_info->where('dkj_status',1)
              ->where('deleted',0);
        }else if($janky_status == 2)// tylko podwazone
        {
            $employee_info = $employee_info->where('dkj_status',1)
              ->where('manager_status',1);
        }else if($janky_status == 3)//usuniete
        {
            $employee_info = $employee_info->where('deleted',1);
        }
        return datatables($employee_info)->make(true);
    }

    public function dkjRaportPost(Request $request)
    {
        $today = date('Y-m-d') . "%";
        $dkj_user = Dkj::where('id_dkj', Auth::user()->id)
            ->where('add_date', 'like', $today)
            ->count();

          $user_yanek = Dkj::where('id_dkj', Auth::user()->id)
              ->where('add_date', 'like', $today)
              ->where('dkj_status', 1)
              ->count();

        $departments = $this->getDepartment();
        $department_id_info = $request->department_id_info;
        $dating_type = 0;
        if($department_id_info<0)
        {
            $department_id_info = $department_id_info*(-1);
            $dating_type = 1;
        }
        $department_type = Department_info::find($department_id_info);
        if ($department_type == null) {
            return view('errors.404');
        }
        // zmiana wykazu użytkowników DKJ
        $users = DB::table('users')
            ->select(DB::raw('users.*'))
            ->join('work_hours','work_hours.id_user','users.id')
            ->where('department_info_id',$department_id_info)
            ->where('status_work',1)
            ->where('user_type_id',1)
            ->whereBetween('work_hours.date',[$request->start_date,$request->stop_date]);
        if ($request->department_id_info<0) {
            $users = $users->where('dating_type',$dating_type);
        }else if($department_type->type == 'Badania/Wysyłka')
            $users = $users->where('dating_type',$dating_type);
        $users = $users->groupBy('id')->orderBy('users.last_name')->get();



        return view('dkj.dkjRaport')
        ->with('departments',$departments)
        ->with('select_department_id_info',$request->department_id_info)
        ->with('select_start_date',$request->start_date)
        ->with('select_stop_date',$request->stop_date)
        ->with('users',$users)
        ->with('dkj_user', $dkj_user)
        ->with('user_yanek',$user_yanek)
        ->with('show_raport',1);
    }

    public function jankyStatistics()
    {
        $actual_month = date("Y-m");
        $today = date('Y-m-d') . "%";
        $user_dkj_info = DB::table('dkj')
            ->select(DB::raw(
                'Date(add_date) as add_date,
                SUM(CASE WHEN 
                    (
                        (dkj_status = 0 OR deleted = 1) OR
                        (dkj_status = 1 AND manager_status IS NULL AND add_date LIKE "' . $today .  '") 
                    )
                    THEN 1 ELSE 0 END)
                as good,
                SUM(CASE WHEN
                    (
                        (dkj_status = 1 AND deleted = 0 AND manager_status IS NULL AND add_date NOT LIKE "' . $today . '") OR
                        (dkj_status = 1 AND deleted = 0 AND manager_status IS NOT NULL)
                    )
                THEN 1 ELSE 0 END) as bad'))
            ->where('id_user', Auth::user()->id)
            ->where('add_date','like',$actual_month.'%')
            ->groupBy(DB::raw('Date(add_date)'))
            ->get();
        $department_info = Department_info::find(Auth::user()->department_info_id);
        $janky_system = JankyPenatlyProc::where('system_id',$department_info->janky_system_id)->get();
        return view('dkj.jankyStatistics')->with('user_info', $user_dkj_info)
            ->with('janky_system', $janky_system);
    }
    public function dkjVerificationGet()
    {
        return view('dkj.dkjVerification');
    }

    public function jankyVerificationGet()
    {
        $departments =  $this->getDepartment();

        return view('dkj.jankyVerification')
            ->with('departments',$departments);
    }

    public function jankyVerificationPOST(Request $request)
    {
        $departments = $this->getDepartment();
        $department_id_info = $request->department_id_info;
        $dating_type = 0;
        if($department_id_info<0)
        {
            $department_id_info = $department_id_info*(-1);
            $dating_type = 1;
        }
        $departments_type = Department_info::where('id',$department_id_info)->first();
        if ($departments_type == null) {
            return view('errors.404');
        }
        if($departments_type->type == 'Badania')
        {
            $dating_type = 0;
        }else if($departments_type->type == 'Wysyłka')
        {
            $dating_type = 1;
        }
        $users = User::where('department_info_id',$department_id_info)
                ->where('dating_type',$dating_type)
                ->where('user_type_id',1)
                ->where('status_work',1)
                ->get();
        return view('dkj.jankyVerification')
            ->with('departments',$departments)
            ->with('select_department_id_info',$request->department_id_info)
            ->with('select_start_date',$request->start_date)
            ->with('select_stop_date',$request->stop_date)
            ->with('users',$users)
            ->with('show_raport',1);
    }

    public function consultantStatisticsGet()
    {
        $departments = Department_info::
        whereHas(
            'department_type', function ($query) {
            $query->whereIn('id',[1,2]);
        })->get();
        return view('dkj.consultantStatistics')
            ->with('departments',$departments);
    }

    public function consultantStatisticsPOST(Request $request)
    {
        $departments = Department_info::
        whereHas(
            'department_type', function ($query) {
            $query->whereIn('id',[1,2]);
        })->get();
        $month = $request->month;
        $user_id = $request->users_id;
        $department_info_id = $request->department_info_id;
        $user_dkj_info = DB::table('dkj')
            ->select(DB::raw(
                'Date(add_date) as add_date,
                SUM(CASE WHEN dkj_status = 0 or deleted = 1 THEN 1 ELSE 0 END) as good ,
                SUM(CASE WHEN dkj_status = 1 AND deleted = 0 THEN 1 ELSE 0 END) as bad'))
            ->where('id_user', $user_id)
            ->where('add_date','like',$month.'%')
            ->groupBy(DB::raw('Date(add_date)'))
            ->get();
        $all_users = $this->getUserDepartmentInfo($request);
        return view('dkj.consultantStatistics')
            ->with('departments',$departments)
            ->with('user_dkj_info',$user_dkj_info)
            ->with('month',$month)
            ->with('user_id',$user_id)
            ->with('department_info_id',$department_info_id)
            ->with('all_users',$all_users);
    }

// Statystyki oddziału (Konkretnego)
    public function departmentStatisticsGet()
    {
        return view('dkj.departmentStatistics');
    }
// Statystyki oddziału (Konkretnego)
    public function departmentStatisticsPOST(Request $request)
    {
        $user_dkj_info = Dkj::whereHas('user', function ($query) {
                $query->where('department_info_id', 'like', Auth::user()->department_info_id);
            })->
            selectRaw(
                'Date(add_date) as add_date,
                SUM(CASE WHEN dkj_status = 0 or deleted = 1 THEN 1 ELSE 0 END) as good ,
                SUM(CASE WHEN dkj_status = 1 AND deleted = 0 THEN 1 ELSE 0 END) as bad')
            ->where('add_date','like',$request->month.'%')
            ->groupBy(DB::raw('Date(add_date)'))
            ->get();
        return view('dkj.departmentStatistics')
            ->with('user_info', $user_dkj_info)
            ->with('month',$request->month);
    }

// Statystyki oddziałów, do wyboru;
    public function departmentsStatisticsGet()
    {
        $departments_info = Department_info::where('janky_system_id','>',0)->get();
        return view('dkj.departmentsStatistics')
            ->with('departments_info',$departments_info);
    }
// Statystyki oddziałów, do wyboru;
    public function departmentsStatisticsPOST(Request $request)
    {
        $departments_info = Department_info::where('janky_system_id','>',0)->get();
        $department_info_id = $request->department_info_id;
        $user_dkj_info = Dkj::whereHas('user', function ($query) use ($department_info_id){
            $query->where('department_info_id', $department_info_id);
        })->
        selectRaw(
            'Date(add_date) as add_date,
                SUM(CASE WHEN dkj_status = 0 or deleted = 1 THEN 1 ELSE 0 END) as good ,
                SUM(CASE WHEN dkj_status = 1 AND deleted = 0 THEN 1 ELSE 0 END) as bad')
            ->where('add_date','like',$request->month.'%')
            ->groupBy(DB::raw('Date(add_date)'))
            ->get();

        return view('dkj.departmentsStatistics')
            ->with('user_info', $user_dkj_info)
            ->with('departments_info',$departments_info)
            ->with('department_info_id', $department_info_id)
            ->with('month',$request->month);
    }



    public function datatableDkjVerification(Request $request)
    {
        $date_actual = date('Y-m-d');
        $query = DB::table('dkj')
            ->join('users as user', 'dkj.id_user', '=', 'user.id')
            ->leftjoin('users as manager', 'dkj.id_manager', '=', 'manager.id')
            ->join('users as dkj_user', 'dkj.id_dkj', '=', 'dkj_user.id')
            ->select(DB::raw(
                'dkj.id as id,
                CURDATE() as expiration_date,
                user.id as id_user,
                user.first_name as user_first_name,
                user.last_name as user_last_name,
                dkj.add_date,
                dkj.phone,
                dkj.campaign,
                dkj.comment,
                dkj.comment_manager,
                dkj.manager_status
                '))->where('dkj.dkj_status',1)
                 ->where('dkj.deleted',0)
                 ->where('dkj.manager_status',null)
                ->where(DB::raw('DATE_ADD(dkj.add_date, INTERVAL 0 DAY)'),'>=',$date_actual.' 00:00:00')
                 ->where('user.department_info_id',Auth::user()->department_info_id);
        return datatables($query)->make(true);
    }

    public function datatableShowDkjVerification(Request $request)
    {
        $query = DB::table('dkj')
            ->join('users as user', 'dkj.id_user', '=', 'user.id')
            ->leftjoin('users as manager', 'dkj.id_manager', '=', 'manager.id')
            ->join('users as dkj_user', 'dkj.id_dkj', '=', 'dkj_user.id')
            ->select(DB::raw(
                'dkj.id as id,
                DATE_ADD(dkj.add_date, INTERVAL 0 DAY) as expiration_date,
                user.id as id_user,
                user.first_name as user_first_name,
                user.last_name as user_last_name,
                dkj.add_date,
                dkj.phone,
                dkj.campaign,
                dkj.comment,
                dkj.comment_manager,
                dkj.manager_status,
                dkj.dkj_status
                '))
            ->where('dkj.dkj_status',1)
            ->where('dkj.deleted',0)
            ->where('user.department_info_id',Auth::user()->department_info_id);

        $query->where(function ($query) {
            $query->where('add_date', 'not like', date('Y-m-d%'))
                ->orWhere('add_date', 'like', date('Y-m-d%'))
                ->where('manager_status', '!=', null);
        });

        return datatables($query)->make(true);
    }

    public function saveDkjVerification(Request $request)
    {
        if($request->manager_status != 0 && $request->manager_status!= 1){
            return 0;
        }
        $dkj_id = $request->id;
        $manager_comment = $request->manager_coment;
        $manager_status = $request->manager_status;
        $dkj_record = Dkj::find($dkj_id);
        $dkj_record->comment_manager = $manager_comment;
        $dkj_record->manager_status = $manager_status;
        $dkj_record->date_manager = date('Y-m-d H:i:s');
        $dkj_record->id_manager = Auth::user()->id;
        $dkj_record->save();
        new ActivityRecorder(4, "Weryfikacja janka, status: " . $request->manager_status . ', komentarz trenera: ' . $request->manager_coment);
        return 1;
    }


    public function datatableDkjRaport(Request $request)
    {
        // zmian -1 na 1 gdy oddział jest wysyłka/badania, pojebane ale skuteczne
        if($request->ajax()) {
            $start_date = $request->start_date;
            $stop_date = $request->stop_date;
            $department_id_info = $request->department_id_info;
            $save_deaprtemnt_inf = $department_id_info;
            if($department_id_info<0)
            {
                $department_id_info=$department_id_info*(-1);
            }
            $type = Department_info::find($department_id_info);
            $type = $type->type;
            $query = DB::table('dkj')
                ->join('users as user', 'dkj.id_user', '=', 'user.id')
                ->leftjoin('users as manager', 'dkj.id_manager', '=', 'manager.id')
                ->join('users as dkj_user', 'dkj.id_dkj', '=', 'dkj_user.id')
                ->select(DB::raw(
                    'dkj.id as id,
                    user.id as id_user,
                    user.first_name as user_first_name,
                    user.last_name as user_last_name,
                    manager.first_name as manager_first_name,
                    manager.last_name as manager_last_name,
                    dkj_user.first_name as dkj_user_first_name,
                    dkj_user.last_name as dkj_user_last_name,
                    dkj.add_date,
                    dkj.phone,
                    dkj.campaign,
                    dkj.comment,
                    dkj.dkj_status,
                    dkj.comment_manager,
                    dkj.manager_status
                   '))
                ->where('deleted',0)
                ->where('add_date','>=',$start_date.' 00:00:00')
                ->where('add_date','<=',$stop_date.' 23:00:00')
                ->where('user.department_info_id', '=', $department_id_info);

            //  -1 Wysyłka 0 Badania
            if($type =='Badania/Wysyłka')
            {
                if($save_deaprtemnt_inf<0)
                {
                    $query->where('user.dating_type', '=', 1);
                }else
                {
                    $query->where('user.dating_type', '=', 0);
                }
            }else if($type == "Badania")
            {
                $query->where('user.dating_type', '=', 0);
            }else if($type == "Wysyłka")
            {
                $query->where('user.dating_type', '=', 1);
            }
            if($request->type_verification == 1)
            {
                $query->where('dkj_status',1);
            }

            return datatables($query)
                ->filterColumn('dkj_status', function($query, $keyword) {
                    $sql = "dkj.dkj_status = ?";
                    if(strtolower($keyword) == 'tak')
                        $query->whereRaw($sql, ["1"]);
                    else if(strtolower($keyword) == 'nie')
                        $query->whereRaw($sql, ["0"]);
                })->make(true);
        }
    }

    public function dkjRaportSave(Request $request)
    {
        if ($request->action == 'create') {
            $dkj = new DKJ();
            $dkj->id_dkj = Auth::user()->id;
            //Activity type
            $activity = 'Dodanie janka przez dkj, status: ';
        }
        if ($request->action == 'edit') {
            $dkj = Dkj::find($request->id);
            if ($dkj == null) {
                die;
            }
            $dkj->edit_dkj = Auth::user()->id;
            $dkj->edit_date = date('Y-m-d H:i:s');
            //Activity type
            $activity = 'Edycja janka przez dkj, status: ';
            new ActivityRecorder(4, 'Edycja janka o id: ' . $request->id,11,2);
        }
        if($request->action == 'create' || $request->action == 'edit')
        {
            $userCheck = User::find($request->id_user);
            if ($userCheck == null) {
                return 0;
            }
            $dkj->id_user = $request->id_user;
            $dkj->phone = $request->phone;
            $dkj->dkj_status = $request->dkj_status;
            $dkj->comment = $request->comment;
            $dkj->campaign = $request->campaign;
            $department = $request->select_department_id_info;
            if($department < 0)
            {
                $department = $department * (-1);
            }
            $dkj->department_info_id = $department;
            $dkj->save();
        }
        if ($request->action == 'remove') {
                $dkj = Dkj::find($request->id);
                if  ($dkj == null) {
                    die;
                }
                new ActivityRecorder(4, 'Usunięce janka o id: ' . $request->id,11,3);
                $dkj->deleted = 1;
                $dkj->edit_dkj = Auth::user()->id;
                $dkj->edit_date = date('Y-m-d H:i:s');
                $dkj->save();
        }

        if (isset($activity)) {
          new ActivityRecorder(4, $activity . $request->dkj_status . ', komentarz: ' . $request->comment . ', numer telefonu: ' . $request->phone . ', kampania: ' . $request->campaign, 11,4);
        }
        return 1;

    }
    private function getDepartment()
    {
        $departments = DB::table('department_info')
            ->join('departments', 'department_info.id_dep', '=', 'departments.id')
            ->join('department_type', 'department_info.id_dep_type', '=', 'department_type.id')
            ->select(DB::raw(
                'department_info.id,
                    departments.name as department_name,
                    department_info.type,
                    department_type.name as department_type_name
                   '))
            ->where('department_info.janky_system_id','!=',0)->get();
        return $departments;
    }

    public function getUser(Request $request)
    {
        if($request->ajax())
        {
            $department_id_info = $request->department_info;
            $save_deaprtemnt_inf = $department_id_info;

            if($department_id_info!=0) {
                if ($department_id_info < 0) {
                    $department_id_info = $department_id_info * (-1);
                }
                $type = Department_infos::find($department_id_info);
                $type = $type->type;
                $query = DB::table('users')
                    ->join('department_info', 'department_info.id', '=', 'users.department_info_id')
                    ->select(DB::raw(
                        'users.first_name,
                        users.last_name,
                        users.id
                   '));
                if ($type == 'Badania/Wysyłka') {
                    if ($save_deaprtemnt_inf < 0) {
                        $query->where('department_info.id', '=', $department_id_info)
                            ->where('users.dating_type', '=', 1);
                    } else {
                        $query->where('department_info.id', '=', $department_id_info)
                            ->where('users.dating_type', '=', 0);
                    }
                } else {
                    $query->where('department_info.id', '=', $department_id_info);
                }
                return $query->where('users.user_type_id', '=', 1)
                    ->orderBy('last_name')
                    ->get();
            }else
            return 0;
        }
    }
//dating_type [ 0 -> Badania 1-> Wysyłka ]
    public function getStats(Request $request) {
        if($request->ajax()) {
            $today = date("Y-m-d") . "%";
            $dkj_user = Dkj::
                join('users', 'dkj.id_user', '=', 'users.id')
                ->join('department_info', 'users.department_info_id', '=', 'department_info.id')
                ->select(DB::raw("
                department_info.id as department_info_id,
                department_info.type,
                count(dkj.id) as all_check_talk,
                sum(CASE WHEN users.dating_type = 1  and deleted = 0 THEN 1 ELSE 0 END) as shipping_all,
                sum(CASE WHEN users.dating_type = 0  and deleted = 0 THEN 1 ELSE 0 END) as research_all,
                sum(CASE WHEN users.dating_type = 0  and deleted = 0 and  dkj.dkj_status = 1  THEN 1 ELSE 0 END) as research_janky_count,
                sum(CASE WHEN users.dating_type = 1  and deleted = 0 and  dkj.dkj_status = 1  THEN 1 ELSE 0 END) as shipping_janky_count,
                SUM(CASE WHEN dkj.dkj_status = 1  and deleted = 0 THEN 1 ELSE 0 END) as all_bad"))
                ->where('dkj.add_date','like',$today)
                ->groupBy('department_info.id','department_info.type')->get();
          return $dkj_user;
        }
    }
    // wyświetlenie informacji czy jest niezweryfikowany janek i ich ilość
//    public function getInfoJankyVerification(Request $request) {
//        $today = date('Y-m-d');
//        $count_janky_verification = DKJ::where('manager_status',null)
//            ->where('add_date','like',$today.'%');
//
//
//    }
    public function getStatsDkjMaster(Request $request) { //dodac to u góry + 2 kolumny gdzie liczymy status_manager = 1 lub 0
        if ($request->ajax()) {
          $today = date("Y-m-d") . "%";
          $dkj_user = Dkj::
              join('users', 'dkj.id_user', '=', 'users.id')
              ->join('department_info', 'users.department_info_id', '=', 'department_info.id')
              ->select(DB::raw("
               department_info.id as department_info_id,
              department_info.type,
              count(dkj.id) as all_check_talk,
              sum(CASE WHEN users.dating_type = 1 and deleted = 0 THEN 1 ELSE 0 END) as shipping_all,
              sum(CASE WHEN users.dating_type = 0 and deleted = 0 THEN 1 ELSE 0 END) as research_all,
              sum(CASE WHEN users.dating_type = 0 and  dkj.dkj_status = 1  and deleted = 0 THEN 1 ELSE 0 END) as research_janky_count,
              sum(CASE WHEN users.dating_type = 1 and  dkj.dkj_status = 1  and deleted = 0 THEN 1 ELSE 0 END) as shipping_janky_count,
              sum(CASE WHEN users.dating_type = 0 and  dkj.dkj_status = 1 and manager_status = 1 and deleted = 0 THEN 1 ELSE 0 END) as manager_research_janky_count,
              sum(CASE WHEN users.dating_type = 1 and  dkj.dkj_status = 1 and manager_status = 1 and deleted = 0 THEN 1 ELSE 0 END) as manager_shipping_janky_count,
              SUM(CASE WHEN dkj.dkj_status = 1 and deleted = 0 THEN 1 ELSE 0 END) as all_bad"))
              ->where('dkj.add_date','like',$today)
              ->groupBy('department_info.id','department_info.type')->get();
            return $dkj_user;
        }
    }

    public function getUsers(Request $request) {
        if($request->ajax()) {
            $today = date("Y-m-d") . "%";
            $department_id = $request->department_id_info;

            $users_statistic = Dkj::select(DB::raw("
                id_user,
                count(id) as count,
                SUM(CASE WHEN dkj_status = 1 THEN 1 ELSE 0 END) as bad"))
                ->where('add_date','like',$today)
                ->where('department_info_id',$request->department_id_info)
                ->groupBy('id_user');

                $users = User::select(DB::raw("users.first_name,users.last_name,users.id"))
                    ->Join('work_hours', 'work_hours.id_user', '=', 'users.id')
                    ->where('work_hours.status', 1)
                    ->where('work_hours.date','like',$today)
                    ->orderBy('last_name');
                if($department_id<0)
                {
                    $users->where('department_info_id',$department_id*(-1))
                        ->where('dating_type',1);
                }else
                {
                    $users->where('department_info_id',$department_id);
                }
                $users->whereIn('user_type_id',[1,2]);
                $array = array();
                $array['users'] = $users->get();
                $array['users_statistic'] = $users_statistic->get();
                return $array;
        }
    }

    public function getUserDepartmentInfo(Request $request)
    {
         if($request->ajax() || $request->isMethod('post'))
         {
             $department_info_id = $request->department_info_id;
             $department_info_id_save = $department_info_id;
             if($department_info_id < 0)
                 $department_info_id = $department_info_id * (-1);
             $type = Department_info::find($department_info_id);
             if ($type == null) {
                die;
             }
             $type = $type->type;
             $query = User::where('department_info_id',$department_info_id);
             if ($type == 'Badania/Wysyłka') {
                 if ($department_info_id_save < 0)
                     $query->where('dating_type', 1);
                 else
                 {
                     $query->where('dating_type', 0);
                 }
             }
             return $query->whereIn('user_type_id', [1,2])
                ->where('status_work', '=', 1)
                ->orderBy('last_name')
                ->get();
         }
    }

}
