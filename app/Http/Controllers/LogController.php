<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\LogService;

class LogController extends Controller
{
    public function getLog(Request $request)
    {
        $logs = DB::table('log')->get();

        $response['error'] = false;
        $response['message'] = "Success";
        $response['total_data'] = count($logs);
        $response['data'] = $logs;
    }

}
