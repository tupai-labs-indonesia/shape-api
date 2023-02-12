<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class UserController extends Controller
{
    public function getUsers(Request $request){
        $users = DB::table('users')->get();

        $response['error'] = false;
        $response['message'] = "Success";
        $response['total_data'] = count($users);
        $response['data'] = $users;

        return response()->json($response, 200);

    }
}
