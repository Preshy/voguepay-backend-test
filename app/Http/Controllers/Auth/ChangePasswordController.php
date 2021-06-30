<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\PasswordReset;
use App\Mail\EmailNotification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\UtilsController;
use Illuminate\Support\Facades\Validator;

class ChangePasswordController extends Controller
{
    public function change(Request $request, $token) {

        $checkToken = PasswordReset::where('code', $token)->first();

        if(!$checkToken) {
            return UtilsController::errorResponse(['token' => 'Invalid Password Reset Token']);
        }

        if(time() > $checkToken->expires_at) {
            return UtilsController::errorResponse(['token' => 'Password Reset Token Has Expired']);
        }

        $validator = Validator::make($request->all(), [
            'password'              => ['required', 'confirmed'],
            'password_confirmation' => ['required']
        ]);

        if($validator->fails()) {
            return UtilsController::errorResponse($validator->errors());
        } else {

            $password = Hash::make($request->password);
            
            $user = User::where('email_address', $checkToken->email)->first();
            $user->password = $password;
            $user->save();

            $payload = [
                'sender'        => 'info@excrow.club',
                'sender_name'   => 'eXcrow.club',
                'receiver'      => $checkToken->email,
                'receiver_name' => $checkToken->email,
                'subject'       => 'Password Changed',
                'html_content'  => 'Your password has been updated.'
            ];

            Mail::to($checkToken->email)->send(new EmailNotification($payload));

            return UtilsController::successResponse(['message' => 'Password update done.']);
        }
    }
}
