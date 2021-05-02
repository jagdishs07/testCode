<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Validator;
use DB;
use Illuminate\Support\Str;
use App\User;

use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Interfaces\LocationRepositoryInterface;
use Carbon\Carbon;


class WorkController extends Controller{

	  use \App\Traits\APIResponseManager;
    use \App\Traits\CommonUtil;

    protected $userObj;
   
   

    function sendmail($to ,$subject ,$message ){
    
        $headers = 'From: webmaster@example.com'       . "\r\n" .
                'Reply-To: webmaster@example.com' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();

        mail($to, $subject, $message, $headers);
    }

    public function sendinvitaion(Request $request){
        $data = [];
    	try{
            $request->validate([
                'email' => 'required|email|unique:users'
            ]);
            $email = $request->email;
            $token = rand( 10000 , 99999 );
            $createinvite = User::create([
                'email' => $email,
                'invite_token' => $token,
                'password' => '12345'
            ]);
            if($createinvite){
                $data['email'] = $email;
                $data['tokenPin'] = $token;
                $maildata = $this->sendmail($email , 'Invite',$token);
            }    
        
        return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'SUCCESS', 'response', $data); 
    	} catch (\PDOException $e) {
        DB::rollback();
        $errorResponse = $e->getMessage();
        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
    	}

    }

    public function userregistration(Request $request){
        $data = [];
    	try{
            $request->validate([
                'tokenpin' => 'required',
                'name' => 'required',
                'user_name' => 'required|max:20|min:4',
                'email' => 'required|email',
            ]);
            $userdata = User::where('email',$request->email)->get()->first();
            $pindata = rand(10000 , 99999 );
            if($userdata['invite_token'] == $request->tokenpin){
                $user = User::find($userdata->id);
                $user->name = $request->name;
                $user->user_name = $request->user_name;
                $user->user_role = 'user';
                // $user->registered_at = Carbon\Carbon::now();
                $user->invite_token = $pindata;
                if($user->save()){
                    $data['email'] = $request->email;
                    $data['pin'] = $pindata;
                    return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'SUCCESS', 'response', $data); 
                }
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details','data not saved');
            }else{
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details','Pin Incorect');
            }
    	} catch (\PDOException $e) {
        DB::rollback();
        $errorResponse = $e->getMessage();
        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
    	}

    }


    public function verifyPin(Request $request){
        $data = [];
    	try{
            $request->validate([
                'pin' => 'required',
                'email' => 'required|email',
            ]);
            $userdata = User::where('email',$request->email)->get()->first();
            if($userdata['invite_token'] == $request->pin){
                $user = User::find($userdata->id);
                $user->status = 1;
                $user->registered_at = Carbon::now();
                $user->invite_token = '';
                if($user->save()){
                    $data['email'] = $request->email;
                    $data['message'] = 'registered successfully';
                    return $this->responseManager(Config('statuscodes.request_status.SUCCESS'), 'SUCCESS', 'response', $data); 
                }
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details','data not saved');
            }else{
                return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details','Pin Incorect');
            }
    	} catch (\PDOException $e) {
        DB::rollback();
        $errorResponse = $e->getMessage();
        return $this->responseManager(Config('statuscodes.request_status.ERROR'), 'DB_ERROR', 'error_details', $errorResponse);
    	}

    }

  

}
