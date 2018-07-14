<?php

namespace App\Http\Controllers;

use App\AttemptResult;
use App\CandidateTraining;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\AttemptStatus;
use App\CandidateSource;
use App\Department_info;
use App\Candidate;
use App\RecruitmentAttempt;
use App\RecruitmentStory;
use Illuminate\Support\Facades\Session;
use App\ActivityRecorder;

class CandidateController extends Controller
{

    /**
     * Zwrocenie templatki z wszystkimi kandydatami
     */
    public function all_candidates() {
        return view('recruitment.allCandidates');
    }

    /**
     * Zwraca dane wszystkich kandydatow
     */
    public function datatableShowCandidates(Request $request) {

        $data = DB::table('candidate')
            ->select(DB::raw('
                candidate.id,
                candidate.first_name,
                candidate.last_name,
                candidate.phone,
                candidate.created_at,
                users.first_name as cadre_name,
                users.last_name as cadre_surname,
                attempt_status.name as attempt_name,
                0 as last_edit_user_name,
                candidate.cadre_id
            '))
            ->join('users', 'users.id', 'candidate.cadre_id')
            ->join('attempt_status', 'attempt_status.id', 'candidate.attempt_status_id')
            ->whereBetween('candidate.created_at', [$request->fromDate, $request->toDate . ' 23:59:59'])
            ->orderBy('candidate.last_name')
            ->get();
        $allRecruitemtStory = RecruitmentAttempt::whereIn('candidate_id',$data->pluck('id')->toArray())->orderByDesc('created_at')->get();
        $users = User::whereIn('id',$data->pluck('cadre_id')->toArray())->get();
        $data->map(function ($item) use ($allRecruitemtStory,$users){
            $userEdit = $allRecruitemtStory->where('candidate_id','=',$item->id)->first();
            if(is_object($userEdit)){
                $userEdit = $users->find($userEdit->cadre_id);
                $item->last_edit_user_name = $userEdit->first_name.' '.$userEdit->last_name;
            }
            else{
                $userEdit = 'Brak informacji';
                $item->last_edit_user_name = $userEdit;
            }
            return $item;
        });
        return datatables($data)->make(true);
    }

    /**
     * Zwraca dane kandydatow dla danego rekrutera
     */
    public function datatableShowCadreCandidates(Request $request) {

        $id = (!$request->id) ? Auth::user()->id : $request->id ;

        $data = DB::table('candidate')
            ->select(DB::raw('
                candidate.*,
                attempt_status.name as attempt_name,
                0 as last_edit_user_name
            '))
            ->join('attempt_status', 'attempt_status.id', 'candidate.attempt_status_id')
            ->orderBy('candidate.last_name')
            ->where('candidate.cadre_id', '=', $id)
            ->get();

        $allRecruitemtStory = RecruitmentAttempt::whereIn('candidate_id',$data->pluck('id')->toArray())->orderByDesc('created_at')->get();
        $users = User::all();

        $data->map(function ($item) use ($allRecruitemtStory,$users){
            $userEdit = $allRecruitemtStory->where('candidate_id','=',$item->id)->first();
            if(is_object($userEdit)){
                $userEdit = $users->find($userEdit->cadre_id);
                $item->last_edit_user_name = $userEdit->first_name.' '.$userEdit->last_name;
            }
            else{
                $userEdit = 'Brak informacji';
                $item->last_edit_user_name = $userEdit;
            }
            return $item;
        });

        return datatables($data)->make(true);
    }

    /**
     * Funkcja zwracająca widok z templatką dodającą kandydata, oraz zwolnionych utkowników z danego oddziału
     */
    public function add_candidate() {
        $department_info = Department_info::where('id', '!=', 13)->get();
        $sources = CandidateSource::where('deleted', '=', 0)->get();
        $status = AttemptStatus::all();
        $fired_user = User::where('status_work','=',0)
            ->where('department_info_id','=',Auth::user()->department_info_id)
            ->whereIn('user_type_id',[1,2])
            ->get();
        $users_for_recommendation = User::where('department_info_id', '=', Auth::user()->department_info_id)
            ->whereIn('user_type_id', [1,2])
            ->where('status_work', '=', 1)
            ->orderBy('last_name')
            ->get();

        return view('recruitment.newCandidate')
            ->with('users_for_recommendation', $users_for_recommendation)
            ->with('sources', $sources)
            ->with('status', $status)
            ->with('department_info', $department_info)
            ->with('fired_user',$fired_user);
    }

    /**
     * Dodanie nowego kandydata
     */
    public function addNewCandidate(Request $request) {
        if ($request->ajax()) {
            $candidate = new Candidate();

            $candidate->first_name = $request->candidate_name;
            $candidate->last_name = $request->candidate_surname;
            $candidate->phone = $request->candidate_phone;
            $candidate->department_info_id = $request->candidate_department;
            $candidate->candidate_source_id = $request->candidate_source;
            $candidate->comment = $request->candidate_desc;
            $candidate->recommended_by = ($request->recommended_by != 0) ? $request->recommended_by : null ;
            $candidate->id_user = $request->ex_id_user;
            $candidate->cadre_id = Auth::user()->id;
            $candidate->cadre_edit_id = Auth::user()->id;
            $candidate->attempt_status_id = 1;
            $candidate->created_at = date('Y-m-d H:i:s');
            $candidate->updated_at = date('Y-m-d H:i:s');

            $candidate->save();
            $LogData = array_merge(['T ' => 'Dodanie newgo kandydata'],$candidate->toArray());
            new ActivityRecorder($LogData, 117, 1);
            return $candidate->id;
        }
    }

    /**
     * Funkcja zwracająca widok z profilem kandydata
     */
    public function candidateProfile($id) {
        $candidate = Candidate::find($id);
        $candidate_status = ($candidate->attempt_level_data != null) ? $candidate->attempt_level_data->name : 'Brak aktywnej rekrutacji';

        if ($candidate == null) {
            return view('errors.404');
        }
        $fired_user = User::where('status_work','=',0)
            ->where('department_info_id','=',Auth::user()->department_info_id)
            ->whereIn('user_type_id',[1,2])
            ->get();

        $isInUsers = User::where('candidate_id', '=', $candidate->id)->count();

        $department_info = Department_info::where('id', '!=', 13)->get();
        $sources = CandidateSource::all();
        $status = AttemptStatus::all();
        $attempt_result = AttemptResult::all();
        $attempt_status = AttemptStatus::all();
        $status_to_change = AttemptStatus::where('attempt_order', '!=', null)->orderBy('attempt_order')->get();

        $users_for_recommendation = User::where('department_info_id', '=', Auth::user()->department_info_id)
            ->whereIn('user_type_id', [1,2])
            ->where('status_work', '=', 1)
            ->orderBy('last_name')
            ->get();

        return view('recruitment.candidateProfile')
            ->with('is_in_users', $isInUsers)
            ->with('sources', $sources)
            ->with('status', $status)
            ->with('users_for_recommendation', $users_for_recommendation)
            ->with('status_to_change', $status_to_change)
            ->with('candidate_status', $candidate_status)
            ->with('department_info', $department_info)
            ->with('candidate', $candidate)
            ->with('fired_user',$fired_user)
            ->with('attempt_result',$attempt_result)
            ->with('attempt_status',$attempt_status);
    }

    /**
     * Edycja danych kandydata (nie jego etapow rekrutacji)
     */
    public function editCandidate(Request $request) {
        if ($request->ajax()) {
            $candidate = Candidate::find($request->candidate_id);

            if ($candidate == null) {
                return view('errors.404');
            }

            $candidate->first_name = $request->candidate_name;
            $candidate->last_name = $request->candidate_surname;
            $candidate->phone = $request->candidate_phone;
            $candidate->department_info_id = $request->candidate_department;
            $candidate->candidate_source_id = $request->candidate_source;
            $candidate->comment = $request->candidate_desc;
            $candidate->recommended_by = ($request->recommended_by != 0) ? $request->recommended_by : null ;
            $candidate->id_user = $request->ex_id_user;
            $candidate->cadre_edit_id = Auth::user()->id;
            $candidate->updated_at = date('Y-m-d H:i:s');

            $candidate->save();
            $LogData = array_merge(['T ' => 'Edycja danych kandydata '],$candidate->toArray());
            new ActivityRecorder($LogData, 119, 2);

            return 1;
        }
    }

    /**
     * Dodanie etapu rekrutacji 
     */
    public function addStory($candidate_id, $attempt_id, $status, $comment, $attempt_result = null,$date_training = null) {
        //Ostatni etap rekrutacji
        $old_recritment_story = RecruitmentStory::where('recruitment_attempt_id','=',$attempt_id)
                                ->where('candidate_id','=',$candidate_id)
                                ->orderby('id','desc')
                                ->first();
        $newStory = new RecruitmentStory();
        $newStory->candidate_id = $candidate_id;
        $newStory->cadre_id = Auth::user()->id;
        $newStory->cadre_edit_id = Auth::user()->id;
        $newStory->recruitment_attempt_id = $attempt_id;

        // Przypisanie poprzednich statusów rekrutacji i ich wyników
        if(isset($old_recritment_story)){
            $newStory->last_attempt_status_id = $old_recritment_story->attempt_status_id;
            $newStory->last_attempt_result_id = $old_recritment_story->attempt_result_id;
        }else{
            $newStory->last_attempt_status_id = null;
            $newStory->last_attempt_result_id = null;
        }
        $newStory->attempt_result_id = $attempt_result;
        if($status == 5){
            $newStory->attempt_result_id = 18;
        }
        $newStory->attempt_status_id = $status;
        $newStory->comment = $comment;
        $newStory->created_at = date('Y-m-d H:i:s');
        $newStory->updated_at = date('Y-m-d H:i:s');
        $newStory->save();

        $recruitment_attempt = RecruitmentAttempt::where('candidate_id','=',$candidate_id)
            ->orderby('id','desc')
            ->first();
        $recruitment_attempt->training_date = $date_training;
        $recruitment_attempt->save();
        $LogData = array_merge(['T ' => 'Dodanie etapu rekrutacji'],$recruitment_attempt->toArray());
        new ActivityRecorder($LogData, 119,1);

        /**
         * Zaktualizowanie etapu rekrutacji w danych kandydata
         */
        $candidate_update = Candidate::find($candidate_id);

        $candidate_update->attempt_status_id = $status;
        $candidate_update->save();
    }

    /**
     * Rozpoczęcie nowej rekrutacji (dla istniejącego kandydata)
     */
    public function startNewRecruitment(Request $request) {
        if ($request->ajax()) {
            $id = $request->candidate_id;

            $find_candidate = Candidate::find($id);

            $find_candidate->training_stage = 1;
            $find_candidate->save();

            /**
             * Sprawdzenie czy kandydat nie ma już aktywnej rekrutacji
             */
            $recruitment_check = RecruitmentAttempt::where('candidate_id', '=', $id)->where('status', '=', 0)->count();

            if ($recruitment_check > 0) {
                return 2;
            }

            /**
             * Stworznie nowej rekrutacji
             */
            $newAttempt = new RecruitmentAttempt();

            $newAttempt->candidate_id = $id;
            $newAttempt->status = 0;
            $newAttempt->cadre_id = Auth::user()->id;
            $newAttempt->created_at = date('Y-m-d H:i:s');
            $newAttempt->updated_at = date('Y-m-d H:i:s');
            
            $newAttempt->save();

            $LogData = array_merge(['T ' => 'Rozpoczęcie nowej rekrutacji'],$newAttempt->toArray());
            new ActivityRecorder($LogData, 119,1);
            /**
             * Dodanie pierwszego atepu w tej rekrutacji
             */
            $this->addStory($id, $newAttempt->id, $request->new_recruitment_status, $request->new_recruitment_comment);

            return 1;
        }
    }

    /**
     * Funkcja dezaktywująca rekrutację 
     * w zależności od flagi:
     *  0 - zakończenie rekrutacji bez dodawania kandydata jako konsultant
     *  1 - zakońcenie rekrutacji + dodanie nowego konsultanta
     */
    public function stopRecruitment(Request $request) {
        if ($request->ajax()) {
            $id = $request->candidate_id;
            $recruitmentAttempt = RecruitmentAttempt::where('candidate_id', '=', $id)->where('status', '=', 0)->first();

            if ($recruitmentAttempt == null) {
                return 0;
            }
            /**
             * usunięcie wpisu ze szkoleń
             */
            CandidateTraining::where('candidate_id','=',$id)
                                ->where('completed_training','=',null)
                                ->delete();

            $recruitmentAttempt->status = 1;
            $recruitmentAttempt->cadre_edit_id = Auth::user()->id;
            $recruitmentAttempt->updated_at = date('Y-m-d H:i:s');

            $recruitmentAttempt->save();
            $LogData = array_merge(['T ' => 'Zakończenie procesu rekrutacji'],$recruitmentAttempt->toArray());
            new ActivityRecorder($LogData, 119,4);

            /**
             * Dodanie etapu w tej rekrutacji
             */
            $this->addStory($id, $recruitmentAttempt->id, $request->stop_recruitment_status, $request->stop_recruitment_comment,$request->last_attempt_result);

            if ($request->stop_recruitment_status == 11) {
                return 1;
            } elseif($request->stop_recruitment_status == 10) {
                $candidate = Candidate::find($id);

                Session::put('candidate_data', $candidate);

                return 2;
            }
        }
    }

    /**
     * Dodanie etapu rekrutacji
     */
    public function addRecruitmentLevel(Request $request) {
        if ($request->ajax()) {
            $id = $request->candidate_id;

            $recruitmentAttempt = RecruitmentAttempt::where('candidate_id', '=', $id)->where('status', '=', 0)->first();

            if ($recruitmentAttempt == null) {
                return 0;
            }

            /**
             * Sprawdzenie czy dodajemy rezultat dla danego statusu
             */
            if ($request->add_level_status == 2) {
                $attempt_result = $request->after_call;
            } elseif ($request->add_level_status == 17) {
                $attempt_result = $request->after_interview;
            } else {
                $attempt_result = null;
            }
            /**
             * Dodanie etapu w tej rekrutacji
             */
            $this->addStory($id, $recruitmentAttempt->id, $request->add_level_status, $request->add_level_comment, $attempt_result);

            if ($request->add_level_status == 3) {
                $date_time = $request->interview;
                $this->addInterviewDate($recruitmentAttempt->id, $date_time);
            }

            return 1;
        }
    }

    /**
     * Dodanie kandydata do treningu
     */
    public function addToTraining(Request $request) {
        if ($request->ajax()) {
            $id = $request->candidate_id;

            $recruitmentAttempt = RecruitmentAttempt::where('candidate_id', '=', $id)
                            ->where('status', '=', 0)
                            ->first();

            if ($recruitmentAttempt == null) {
                return 0;
            }

            $candidate = Candidate::find($id);

            /**
             * Sprawdzenie czy użytkownik nie nadpisuje etapu rekrutacji
             */
            if ($request->last_recruitment_story_id != $candidate->recruitment_attempt->where('status', 0)->first()->recruitment_story->last()->id) {
                return 2;
            }

            /**
             * Dodanie etapu w tej rekrutacji
             */
            $this->addStory($id, $recruitmentAttempt->id, $request->add_level_status, $request->add_training_comment,null,$request->date_training);

            return 1;
        }
    }

    /**
     * Sprawdzenie czy numer jest unikalny
     */
    public function uniqueCandidatePhone(Request $request) {
        if ($request->ajax()) {
            $candidate = Candidate::where('phone', '=', $request->candidate_phone)->count();

            return ($candidate > 0) ? 0 : 1 ;
        }
    }

     /**
     * Dodanie czasu rozmowy kwalifikacyjnej
     */
    public function addInterviewDate($recruitment_attempt_id, $recruitment_date) {
        $recruitment = RecruitmentAttempt::find($recruitment_attempt_id);
        $recruitment->interview_date = $recruitment_date;
        $recruitment->interview_cadre = Auth::user()->id;
        $recruitment->save();
        $LogData = array_merge(['T ' => 'Dodanie rozmowy kwalifikacyjnej'],$recruitment->toArray());
        new ActivityRecorder($LogData, 119,1);
    }

    /**
     * Zmiana daty rozmowy kwalifikacyjnej
     */
    public function editInterviewDateTime(Request $request) {
        if ($request->ajax()) {
            $newDate = $request->result;
            $candidate_id = $request->candidate_id;
            $candidate = Candidate::find($candidate_id);

            $data = $candidate->recruitment_attempt->where('status', '=', 0)->first();

            $data->interview_date = $newDate;
            $data->updated_at = date('Y-m-d H:i:s');
            $data->cadre_edit_id = Auth::user()->id;
            $data->save();
            $LogData = array_merge(['T ' => 'Zmiana daty rozmowy kwalifikacyjnej'],$data->toArray());
            new ActivityRecorder($LogData, 119,2);
            return 1;
        }
    }

    /**
     * Dodanie danych kandydata do sesji
     */
    public function addConsultantToSession(Request $request) {
        if ($request->ajax()) {
            $candidate = Candidate::find($request->candidate_id);
            Session::put('candidate_data', $candidate);

            return 1;
        }
    }
}
