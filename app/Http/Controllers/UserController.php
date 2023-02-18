<?php

namespace App\Http\Controllers;

use App\Services\RegistrationService;
use App\Services\StringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function getUsers(Request $request){
        $users = DB::table('users')->get();

        foreach($users as $user){
            unset($user->password);
            if($user->picture_path != null){
                $user->picture_path = url('uploads/images/users') . '/' . $user->picture_path;
            }else{
                unset($user->picture_path);
            }
        }

        $response['error'] = false;
        $response['message'] = "Success";
        $response['total_data'] = count($users);
        $response['data'] = $users;

        return response()->json($response, 200);

    }

    public function create(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'username' => 'required|string',
                'email' => 'required',
                'password' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Please check parameters!',
                    'data' => ['error_message' => $validator->errors()]
                ], 422);
            }else{
                if($request->hasFile('profile_image')){
                    $file = $request->file('profile_image');
                    $allowedfileExtension = ['jpeg', 'jpg', 'png'];
                    $extension = $file->getClientOriginalExtension();

                    $checkExtension = in_array($extension, $allowedfileExtension);

                    if ($checkExtension) {
                        $imagePath = public_path('uploads/images/users');
                        $imageName = StringService::getRandString(15) . '.' . $extension;
                        $request->file('profile_image')->move($imagePath, $imageName);
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

                $regStatus = RegistrationService::createUser($request, $imageName);

                if($regStatus['error']){
                    return response()->json([
                        'error' => true,
                        'message' => $regStatus['message']
                    ], 201);
                } else {
                    return response()->json([
                        'error' => false,
                        'message' => 'User has been created.'
                    ], 201);
                }
            }
        }catch(\Exception $e){
            return response()->json([
                'error' => true,
                'message' => 'Failed save user data!, ' . $e->getMessage()
            ], 409);
        }
    }

    public function update(Request $request){
        try{
            if($request->hasFile('profile_image')){
                $file = $request->file('profile_image');
                $allowedfileExtension = ['jpeg', 'jpg', 'png'];
                $extension = $file->getClientOriginalExtension();

                $checkExtension = in_array($extension, $allowedfileExtension);

                if ($checkExtension) {
                    $imagePath = public_path('uploads/images/users');
                    $imageName = StringService::getRandString(15) . '.' . $extension;
                    $request->file('profile_image')->move($imagePath, $imageName);
                } else {
                    return response()->json([
                        'error' => true,
                        'message' => 'Please check your image extension, only [.jpeg, .jpg, .png] allowed!'
                    ], 422);
                }
            }else{
                $imageName = '';
            }

            $updateStatus = RegistrationService::updateUser($request, $imageName);

            if($updateStatus['error']){
                return response()->json([
                    'error' => true,
                    'message' => $updateStatus['message']
                ], 201);
            } else {
                return response()->json([
                    'error' => false,
                    'message' => 'User has been updated.'
                ], 201);
            }

        }catch(\Exception $e){
            return response()->json([
                'error' => true,
                'message' => 'Failed save user data!, ' . $e->getMessage()
            ], 409);
        }
    }

}
