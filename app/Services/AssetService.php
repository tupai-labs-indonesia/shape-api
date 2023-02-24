<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssetService {

    public static function createAsset($data, $imageName = null, $asset_id=null){
        try{
            DB::beginTransaction();

            if($asset_id){
                $id = DB::table('asset_headers')->where('id', '=', $asset_id)->first();
                if($id){
                    DB::table('asset_details')->insert([
                        'asset_id' => $asset_id,
                        'file_name' => $imageName,
                        'type' => $data->type
                    ]);
                }else{
                    $newAssetHeader = [
                        'asset_name' => $data->asset_name,
                        'category' => $data->category,
                    ];
                    $newAssetId = DB::table('asset_headers')->insertGetId($newAssetHeader);
                    DB::table('asset_details')->insert([
                        'asset_id' => $newAssetId,
                        'file_name' => $imageName,
                        'type' => $data->type
                    ]);
                }
            }
            DB::commit();
            return (
                [
                    "error"=>false,
                    "message"=>"Success"
                ]
            );

        }catch(\Exception $e){
            Log::error("AssetService::createAsset Error: " . $e->getMessage() . " - StackTrace: " . $e->getTraceAsString());
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
