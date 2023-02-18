<?php

namespace App\Http\Controllers;

use App\Services\StringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\AssetService;

class AssetController extends Controller
{
    public function getAssets(Request $request){
        $assets= DB::table('asset_headers')->get();
        $response['error'] = false;
        $response['message'] = "Success";
        $response['total_data'] = count($assets);
        $response['data'] = $assets;

        return response()->json($response, 200);
    }

    public function create(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'category' => 'required|string',
                'created_by' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Please check parameters!',
                    'data' => ['error_message' => $validator->errors()]
                ], 422);
            }else{
                if($request->hasFile('asset_image')){
                    $file = $request->file('asset_image');
                    $allowedfileExtension = ['jpeg', 'jpg', 'png'];
                    $extension = $file->getClientOriginalExtension();

                    $checkExtension = in_array($extension, $allowedfileExtension);

                    if ($checkExtension) {
                        $imagePath = public_path('uploads/images/assets');
                        $imageName = StringService::getRandString(15) . '.' . $extension;
                        $request->file('asset_image')->move($imagePath, $imageName);
                    } else {
                        return response()->json([
                            'error' => true,
                            'message' => 'Please check your image extension, only [.jpeg, .jpg, .png] allowed!',
                            'data' => ['error_message' => $validator->errors()]
                        ], 422);
                    }
                }else{
                    $imageName = '';
                }

                $status = AssetService::createAsset($request, $imageName);

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
