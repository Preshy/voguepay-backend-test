<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use App\Models\User;
use App\Mail\Welcome;
use App\Models\Referrals;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Verifications;
use App\Mail\EmailVerification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\UtilsController as Utils;
use App\Models\Wallets;

class SignupController extends Controller
{
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname'  => 'required',
            'country'   => 'nullable',
            'phone'     => 'nullable|unique:users,phone_number',
            'email'     => 'required|unique:users,email_address',
            'pass'      => 'required',
        ]);

        if($validator->fails()) {
            return Utils::errorResponse($validator->errors());
        } else {
            
            $fname = $request->input('firstname');
            $lname = $request->input('lastname');
            $country = $request->input('country') ?? 'NG';
            $phone = $request->input('phone');
            $email = $request->input('email');
            $pass  = $request->input('pass');

            $account_id = Utils::generateAccountID();

            // Register user
            $user = new User();
            $user->account_id = $account_id;
            $user->firstname = $fname;
            $user->lastname  = $lname;
            $user->country = $country;
            $user->phone_number = $phone;
            $user->email_address = $email;
            $user->password = Hash::make($pass);
            $user->save();

            // Setup default wallet for user
            $wallet = new Wallets();
            $wallet->account_id = $account_id;
            $wallet->currency = 'NGN';
            $wallet->amount = 10000; // sandbox money.
            $wallet->save();

            // Email Verification
            // Mail::to($email)->send(new EmailVerification(['user' => $user, 'token' => $email_verification_code]));
            // Mail::to($email)->send(new Welcome(['user' => $user]));

            return Utils::successResponse(['token' => $this->jwt($user), 'user' => $user, 'wallets' => $wallet]);

        }
    }
}
