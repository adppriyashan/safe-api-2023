<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Disaster;
use App\Models\User;
use App\Traits\RespondsWithHttpStatus;
use App\Traits\SendSMS;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use RespondsWithHttpStatus;
    use SendSMS;

    public function register(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'password' => 'required|min:8',
                    'nic' => 'required|unique:users,nic',
                    'contact' => 'required',
                    'district' => 'required|exists:districts,id'
                ]
            );

            if ($validateUser->fails()) {
                return $this->failure('Validation error', $validateUser->errors(), 401);
            }

            User::create([
                'name' => $request->name,
                'password' => Hash::make($request->password),
                'nic' => $request->nic,
                'contact' => $request->contact,
                'district' => $request->district,
            ]);

            return $this->success('User successfully created.');
        } catch (Exception $e) {
            return $this->failure($e->getMessage(), status: 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'nic' => 'required|exists:users,nic',
                    'password' => 'required|min:8'
                ]
            );

            if ($validateUser->fails()) {
                return $this->failure('Validation error', $validateUser->errors(), 401);
            }

            if (!Auth::attempt($request->only(['nic', 'password']))) {
                return $this->failure('NIC & Password does not match.', status: 401);
            }

            $user = User::where('nic', $request->nic)->first();
            $user->is_safe = Disaster::where('status', 2)->where('district', $user->district)->count() ? 0 : 1;

            return $this->success('User successfully created.', ['user' => $user, 'token' => $user->createToken(env('AUTH_TOKEN', 'TEST_TOKEN_KEY'))->plainTextToken]);
        } catch (Exception $e) {
            return $this->failure($e->getMessage(), status: 500);
        }
    }

    public function sendOtp(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'nic' => 'required|exists:users,nic',
                ]
            );

            if ($validateUser->fails()) {
                return $this->failure('Validation error', $validateUser->errors(), 401);
            }

            $user = User::where('nic', $request->nic)->first();

            if ($user && $user->contact) {
                $code = rand(100000, 999999);
                $this->sendNow('Please use ' . $code . ' OTP code to recover your account', $user->contact);
                return $this->success('OTP code sent.', ['code' => $code, 'nic' => $request->nic]);
            }

            return $this->failure('Invalid national identity card number.');
        } catch (Exception $e) {
            return $this->failure($e->getMessage(), status: 500);
        }
    }

    public function getData()
    {
        $user = Auth::user();
        $user->is_safe = Disaster::where('status', 2)->where('district', $user->district)->count() > 0 ? 0 : 1;
        return $this->success('User authenticated successfully.', $user);
    }

    public function resetPassword(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'nic' => 'required|exists:users,nic',
                    'password' => 'required|min:8'
                ]
            );

            if ($validateUser->fails()) {
                return $this->failure('Validation error', $validateUser->errors(), 401);
            }

            $user = User::where('nic', $request->nic)->first();

            if ($user && $user->contact) {
                $user->update([
                    'password' => Hash::make($request->password)
                ]);
                return $this->success('Password successfully changed.');
            }

            return $this->failure('Error.', status: 403);
        } catch (Exception $e) {
            return $this->failure($e->getMessage(), status: 500);
        }
    }
}
