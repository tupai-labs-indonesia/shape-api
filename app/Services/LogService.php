<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LogService
{

    public static function insertLog($apps_name = null, $apps_stage = null, $request = null, $error = null, $message = null, $user_id = null)
    {
        DB::beginTransaction();

        try{
            $data = [
                'apps_name' => $apps_name,
                'apps_stage' => $apps_stage,
                'request' => $request,
                'error' => $error,
                'message' => $message,
                'user_id' => $user_id
            ];
            DB::table('logs')->insert($data);
            DB::commit();
        }
        catch(\Exception $e){
            Log::error("LogService::createLog Error: " . $e->getMessage() . " - StackTrace: " . $e->getTraceAsString());
            DB::rollBack();
            throw $e;
        }
    }

}
