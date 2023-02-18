<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegistrationService {

    public static function createUser($data, $imageName = null){
        try{
            DB::beginTransaction();
            $newUser = [
                'name' => $data->name,
                'username' => $data->username,
                'password' => Crypt::encrypt($data->password),
                'email' => $data->email,
                'picture_path' => $imageName
            ];

            $checkEmail = DB::table('users')->where('email', '=', $newUser['email'])->first();

            $checkUsername = DB::table('users')->where('username', '=', $newUser['username'])->first();

            if($checkEmail != null || $checkUsername != null){
                return (
                    [
                        "error"=>true,
                        "message"=>"Registration failed, user data has been taken"
                    ]
                );
            }

            DB::table('users')->insertGetId($newUser);
            DB::commit();

            return (
                [
                    "error"=>false,
                    "message"=>"Success"
                ]
            );

        }catch(\Exception $e){
            Log::error("RegistrationService::createUser Error: " . $e->getMessage() . " - StackTrace: " . $e->getTraceAsString());
            DB::rollBack();
            throw $e;
        }
    }

    public static function updateUser($data, $imageName = null){
        try{
            DB::beginTransaction();
            $updateData = $data->all();


            if($imageName != ''){
                $updateData['picture_path'] = $imageName;
            }

            if(isset($updateData['password'])){
                if($updateData['password'] != ''){
                    $updateData['password'] = Crypt::encrypt($updateData['password']);
                }
            }

            if(isset($updateData['profile_image'])){
                unset($updateData['profile_image']);
            }

            $user_id = auth()->user()->id;
            $checkEmail = null;
            $checkUsername = null;

            if(isset($data['email'])){
                $checkEmail = DB::table('users')->where('email', '=', $data['email'])->first();
            }

            if(isset($data['username'])){
                $checkUsername = DB::table('users')->where('username', '=', $data['username'])->first();
            }

            if($checkEmail != null || $checkUsername != null){
                return (
                    [
                        "error"=>true,
                        "message"=>"Update failed, user data has been taken"
                    ]
                );
            }

            DB::table('users')->where('id', '=', $user_id)->update($updateData);

            DB::commit();

            return (
                [
                    "error"=>false,
                    "message"=>"Success"
                ]
            );

        }catch(\Exception $e){
            Log::error("RegistrationService::updateeUser Error: " . $e->getMessage() . " - StackTrace: " . $e->getTraceAsString());
            DB::rollBack();
            throw $e;
        }
    }

}
