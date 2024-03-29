<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Models\Users;
use App\Models\Chats;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use App\Events\RecordActivity;


class ChatsController extends Controller
{
    //obtiene el chat entre el admin y todos los doctores
    public function getAllDoctorsChats()
    {        
        
        $dataToOrder= DB::table('chats')
                   ->select('doctor_id_parent as user_id', DB::raw('MAX(created_at) as last_post_created_at'))
                   ->whereNull('deleted_at')       
                   ->groupBy('doctor_id_parent');

      
        $_allDoctorsChats= Users::Join('profiles', 'users.profile_id', '=', 'profiles.id')        
        ->with('chatHistory')
        ->leftJoinSub($dataToOrder, 'data_to_order', function ($join) {
            $join->on('users.id', '=', 'data_to_order.user_id');
        })
        ->where('profiles.description','=', 'DOCTOR')    
        ->whereNull('users.deleted_at')  
        ->select(DB::raw("users.id, users.avatar, CONCAT(IFNULL(users.name,users.email),' ',IFNULL(users.last_name,'')) as user_name, 
        null as date, null as time, IFNULL(DATEDIFF(now(),data_to_order.last_post_created_at),500) as daysDifference, 
        IFNULL(date(data_to_order.last_post_created_at),'') as date, IFNULL(time(data_to_order.last_post_created_at),'') as time"))
        ->orderBy('data_to_order.last_post_created_at', 'desc')
        ->get();
        
        $_avatarAdmin = Users::Join('profiles', 'users.profile_id', '=', 'profiles.id')     
        ->where('users.id', Auth::user()->id)    
        ->select('users.avatar')
        ->get();

               
        return response()
        ->json([
        'AllDoctorsChats' => $_allDoctorsChats,
        'avatarMe'=>$_avatarAdmin
        ]);
    }
    //obtiene el chat del doctor y el admin
    public function getAdminAndDoctorChats()
    {        
        
        $_AdminAndDoctorChats= Users::Join('profiles', 'users.profile_id', '=', 'profiles.id')    
        ->where('profiles.description','=', 'ADMIN')    
        ->whereNull('users.deleted_at')  
        ->select(DB::raw("users.id, users.avatar, CONCAT(IFNULL(users.name,users.email),' ',IFNULL(users.last_name,'')) as user_name, 
        null as date, null as time"))
        ->orderBy('users.id', 'desc')
        ->get();
        
        
        $chats= Chats::Join('users', 'users.id', '=', 'chats.user_id')
        ->Join('profiles', 'users.profile_id', '=', 'profiles.id')
        ->where('chats.doctor_id_parent',Auth::user()->id)    
        ->whereNull('users.deleted_at')  
        ->select(DB::raw("chats.id, chats.doctor_id_parent, 
        date(chats.created_at) as date,  time(chats.created_at) as time, (CASE WHEN profiles.description = 'DOCTOR' THEN 'amber-7' ELSE 'primary' END) AS bgcolor,
        (CASE WHEN profiles.description = 'DOCTOR' THEN 'Yo' ELSE users.name END) AS who, 
        chats.comment"))
        ->orderBy('chats.id', 'desc')->get();

        $_AdminAndDoctorChats[0]['chat_history_being_doctor']=$chats;

        $_avatarMe = Users::where('users.id', Auth::user()->id)    
        ->select('users.avatar')
        ->get();

        return response()
        ->json([
        'AllDoctorsChats' => $_AdminAndDoctorChats,
        'avatarMe'=>$_avatarMe
        ]);
    }

    //Guardar los chats
    public function store(Request $request)
    {              

        $this->validate($request, [     
            'comment' => 'required',
            'doctor_id_parent' => 'required'
            ]); 
        
        
        $data = $request->all(); 
        $data['user_id'] = Auth::user()->id; 
        
        $_profile = Users::Join('profiles', 'users.profile_id', '=', 'profiles.id')     
        ->where('users.id', Auth::user()->id)    
        ->select('profiles.description')
        ->get();
        
        event(new RecordActivity(Auth::user()->name.' Te dedó un nuevo mensaje ',
        'chats', null, true, $data['doctor_id_parent']));
        
        if ($_profile[0]['description']=='DOCTOR')
        {
            $data['doctor_id_parent']=Auth::user()->id;
        }
        
        $item = Chats::create($data);    

        
        
        return response()
            ->json([
                'created' => true
            ]);
    }

}
