<?php

namespace App\Http\Controllers;

use App\Http\Resources\ResponseResource;
use App\Mail\SendCodeForResetPassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    
    public function register(Request $request){

         /* Request Validation */
      $validator = Validator::make($request->toArray(), [
        'full_name' => 'required',
        "mobile_number" => "required|unique:users",
        "password" => "required|min:6",
        "email" => "required|unique:users",
    ]);
        if ($validator->fails()) {
        return new ResponseResource((['message' =>  $validator->errors()->first()]), 422);
        }
       $user = new User();
       $user->full_name = $request->full_name;
       $user->mobile_number = $request->mobile_number ?? null;
       $user->password = Hash::make($request->password);
       $user->email = $request->email;
       $user->save();
        if (isset($user)){
        $token = $user->createToken($user->email ?? $user->number)->plainTextToken;
        return new ResponseResource(['message' => 'success',  'token' => $token, 'data' => $user], 200);
    }
        return new ResponseResource(['message' => 'something went wrong'], 400);
    }


    public function login(Request $request){
    
        $validator = Validator::make($request->toArray(), [
            'email' => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return new ResponseResource(['message' =>  $validator->errors()->first()], 422);
        }
        $user = User::where('email', $request->email)->first();
        if (!is_null($user)) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken($user->email ?? $user->mobile_number)->plainTextToken;
                Auth::guard('web')->login($user);
                return new ResponseResource(['message' => 'success', 'data' => $user, 'token' => $token], 200);
            } else {
                return new ResponseResource(['message' => 'Invalid password'], 401);
            }
        } else {
            return new ResponseResource(['message' => 'User not found'], 404);
        }

    }

   
    public function forgotPassword(Request $request)
    {
        $data = $request->validate([
        'email' => 'required|email',
        ]);
            $user= User::where('email', $request->email)->first();
            if(!$user){
             return new ResponseResource(['message' => 'No Account Founded'], 401);
            }
                else{
                // Generate random code
                $code = mt_rand(100000, 999999);
                // Create a new code
                $codeData = User::where('email',$request->email)->update([
                'verification_code' => $code
                ]);
                Mail::to($request->email)->send(new SendCodeForResetPassword($code));
                 return new ResponseResource(['message' => 'We have Mailed you the OTP for password reset, Please Check Your Mail!'], 200);
                }
         }

       public function verificationCodeCheck(Request $request)
        {
            $request->validate([
            'code' => 'required|string',
            ]);
            // find the code
            $passwordReset = User::firstWhere('verification_code', $request->code);
            if(isset($passwordReset) && $passwordReset->created_at < now()->addHour()){
             return new ResponseResource(['message' => 'Password Reset Initiated'], 200);            
            }       
            elseif(!$passwordReset){
            return new ResponseResource(['message' => 'Invalid Code'], 200);            
            }
            else{
            return new ResponseResource(['message' => 'Verification Code is Expired!'], 422);                     
            }
            }


         public function resetPassword(Request $request)
            {
                $request->validate([
                    'verification_code' => 'required|string|exists:users',
                    'password' => 'required|string|min:6|confirmed',
                ]);      
                // find the code
                $passwordReset = User::firstWhere('verification_code', $request->verification_code);     
                // check if it does not expired: the time is one hour
                if ($passwordReset->created_at > now()->addHour()) {
                return new ResponseResource(['message' => 'Verification Code is Expired!'], 422);                     
                }      
                // find user's email 
                $user= User::firstWhere('email', $passwordReset->email);             
                // update user password
                $user->password=Hash::make($request->password);
                $user->update(); 
                return new ResponseResource(['message' => 'Password has been successfully reset'], 200);                     
            }

            public function logout(){
                $logout = Auth::user()->tokens()->delete();
                return new ResponseResource(['message' => 'Logged Out'], 200);                     

                }
        







}
