<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\LoginActivities;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\UtilsController as Utils;

class LoginController extends Controller
{
    /**
     * Login function
     *
     * @param Request $request
     * @return void
     */
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email_address',
            'phone' => 'sometimes|exists:users,phone_number',
            'pass'  => 'required'
        ]);

        if($validator->fails()) {
            return Utils::errorResponse($validator->errors());
        } else {
            
            $email = $request->input('email');
            $phone = $request->input('phone');
            $pass  = $request->input('pass');

            // Find the user by email
            $user = User::where('email_address', $email)->orWhere('phone_number', $phone)->first();

            if (!$user) {     
                return Utils::errorResponse(['error' => 'Email does not exist.']);
            }

            // Verify the password and generate the token
            if (Hash::check($pass, $user->password)) {

                $user->last_login_ip = $request->ip();
                $user->last_login_date = Carbon::now();
                $user->save();

                return Utils::successResponse(['token' => $this->jwt($user), 'user' => $user, 'wallet' => $user->wallets()]);
            }

            // Bad Request response
            return Utils::errorResponse(['error' => 'Email or password is wrong.']);
        }
    }
}
