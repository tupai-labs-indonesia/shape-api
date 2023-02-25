<?php

namespace App\Http\Controllers;

use App\Services\StringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\AssetService;
use App\Services\LogService;

class AssetController extends Controller
{
    public function getAssets(Request $request){
        $assets= DB::table('asset_headers')->get();

        foreach($assets as $asset){
            $details = DB::table('asset_details')->where('asset_id', '=', $asset->id);

            $preview = $details;

            $preview = $preview->where('type', '=', 'preview')->get();
            foreach($preview as $preview_url){
                $preview_url->url = url('uploads/images/assets') . '/' . $preview_url->file_name;
            }
            $asset->preview = $preview;

            $details = $details->get();

            foreach($details as $detail){
                $detail->url = url('uploads/images/assets') . '/' . $detail->file_name;
            }
            $asset->details = $details;
        }

        $response['error'] = false;
        $response['message'] = "Success";
        $response['total_data'] = count($assets);
        $response['data'] = $assets;

        $user_id=null;

        if(auth()->user()){
            $user_id = auth()->user()->id;
        }else{
            $user_id = null;
        }

        LogService::insertLog('Asset', 'Get Data', null, $response['error'], $response['message'], $user_id);
        return response()->json($response, 200);
    }

    public function create(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'category' => 'required|string',
                'asset_id' => 'required|string',
                'asset_name' => 'required|string',
                'type' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Please check parameters!',
                    'data' => ['error_message' => $validator->errors()]
                ], 422);
            }else{
                if($request->hasFile('asset_file')){
                    $file = $request->file('asset_file');
                    $extension = $file->getClientOriginalExtension();

                    $imagePath = public_path('uploads/images/assets');
                    $imageName = StringService::getRandString(15) . '.' . $extension;
                    $request->file('asset_file')->move($imagePath, $imageName);
                }else{
                    $imageName = '';
                }

                $status = AssetService::createAsset($request, $imageName, $request->asset_id);

                if($status['error']){
                    return response()->json([
                        'error' => true,
                        'message' => $status['message']
                    ], 201);
                } else {
                    return response()->json([
                        'error' => false,
                        'message' => 'Asset has been added.'
                    ], 201);
                }
            }
        }catch(\Exception $e){
            return response()->json([
                'error' => true,
                'message' => 'Failed save asset data!, ' . $e->getMessage()
            ], 409);
        }
    }
}
