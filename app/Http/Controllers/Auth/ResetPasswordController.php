<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PasswordReset;
use App\Mail\EmailNotification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\UtilsController;
use Illuminate\Support\Facades\Validator;

class ResetPasswordController extends Controller
{
    public function reset(Request $request) {
        $validator = Validator::make($request->all(), [
            'email_address' => ['required', 'exists:users']
        ]);

        if($validator->fails()) {
            return UtilsController::errorResponse($validator->errors());
        } else {
            $email = $request->email_address;
            $code = Str::uuid();
            // create password reset code
            $passwordreset = new PasswordReset();
            $passwordreset->email = $email;
            $passwordreset->code = $code;
            $passwordreset->expires_at = strtotime("+2 hours", time());
            $passwordreset->save();

            // send mail
            $payload = [
                'sender'        => 'info@excrow.club',
                'sender_name'   => 'eXcrow.club',
                'receiver'      => $email,
                'receiver_name' => $email,
                'subject'       => 'Password Reset',
                'html_content'  => 'Did you request for a password reset? if yes: click <a href="'.url('auth/change_password/'.$code).'">here</a> else ignore this email. It will become invalid in 2 hours.'
            ];

            Mail::to($email)->send(new EmailNotification($payload));

            return UtilsController::successResponse(['message' => 'Password Reset Link Sent.']);

        }
    }
}
