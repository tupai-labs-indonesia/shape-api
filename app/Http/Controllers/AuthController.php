<?php

namespace App\Http\Controllers;

use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {

            return response()->json(['error' => true, 'message' => 'Please check parameters!', 'data' => $validator->errors()], 422);
        }
        else {
            $user = DB::table('users')->where('username', '=', $request->username)->first();

            if($user != null){
                if ($request->password == Crypt::decrypt($user->password)) {
                    $userId = $user->id;
                    if ($userId) {
                        $token = auth()->claims([
                            'user_id' => $userId,
                            'username' => $user->username,
                            'name' => $user->name,
                        ]);
                        $token = auth()->tokenById($userId);
                        $token = auth()->setTTL(24*60)->tokenById($userId);

                        $response['error'] = false;
                        $response['message'] = "Successfully logged in";
                        $response['token'] = $token;
                        $response['token_type'] = 'bearer';
                        $response['expires_in'] = Auth::factory()->getTTL();
                        LogService::insertLog('Authentication', 'Login', $request->username, $response['error'], $response['message'], $userId);
                        return response()->json($response, 200);
                    }
                }
                else{
                    $response['error'] = true;
                    $response['message'] = "User not found";
                    return response()->json($response, 401);
                }
            }else{
                $response['error'] = true;
                $response['message'] = "User not found";
                 return response()->json($response, 401);
            }
        }
    }

    public function logout()
    {
        $user_id = auth()->user()->id;
        Auth::logout();
        $response['message'] = "Successfully logged out";
        $response['error'] = false;
        LogService::insertLog('Authentication', 'Logout', null, $response['error'], $response['message'], $user_id);
        return response()->json($response);
    }
}
