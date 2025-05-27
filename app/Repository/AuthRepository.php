<?php

namespace App\Repository;

use App\Models\User;
use App\ResponseTrait;
use http\Env\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthRepository implements Interface\AuthRepositoryInterface
{
    use ResponseTrait;

    public function register($request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|between:2,100',
                'email' => 'required|string|email|max:100|unique:users',
                'password' => 'required|string|confirmed|min:8',
            ]);
            if($validator->fails()){
//                return response()->json($validator->errors()->toJson(), 400);
                return $this->fail($validator->errors(), 400);
            }
            $user = User::create($validator->validated(),[
//                $validator->validated(),
                'name'=>$request->name,
                'email'=>$request->email,
                'password'=>Hash::make($request->password),
            ]);
            $token = $user->createToken('auth_token')->plainTextToken;

            $responseData = [
                'user' => [
                    'id'=>$user->id,
                    'name'=>$user->name,
                    'email'=>$user->email,
                    'role'=>$user->role,
                    'is_verified_agent'=>$user->is_verified_agent
                ],
                'token' => $token,
            ];

            return $this->success('User has been created successfully', 201, $responseData);

        }catch (\Exception $e){
            return $this->fail($e->getMessage(),500);
        }
    }

    public function login($request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:8',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $user = User::where('email','=',$request->email)->first();
            $validator->validated();
            if (!$user || !Hash::check($request->password,$user->password)){
                return $this->fail('The provided credentials are incorrect', 505);
            }
            if ($user->status === 'ban'){
                return $this->fail('Your account has been banned. Please contact the administrator',403);
            }
            $token = $user->createToken('auth_token')->plainTextToken;

            $responseData = [
                'user' => [
                    'id'=>$user->id,
                    'name'=>$user->name,
                    'email'=>$user->email,
                    'role'=>$user->role,
                    'is_verified_agent'=>$user->is_verified_agent
                ],
                'token'=> $token
            ];
            return $this->success('You have been logged in successfully', 200, $responseData);

        }catch (\Exception $e){
            return $this->fail($e->getMessage(),500);
        }
    }

    public function logout($request)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return $this->fail('Unauthenticated', 401);
        }

        try {
            $user->tokens()->delete();
            return response()->json(['message' => 'User successfully logged out']);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage(),500);
        }
    }

    public function updateProfile($request, $id){
        try {
            if (auth()->id() != $id) {
                return $this->fail("You don't have permission to do this action", 403);
            }

            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'new_name' => 'required|string|between:2,100',
                'new_email' => 'required|string|email|max:100|unique:users,email,' . $user->id,
                'password' => 'required|string|confirmed|min:8',
            ]);

            if ($validator->fails()) {
                return $this->fail($validator->errors(), 400);
            }

            $user->update([
                'name' => $request->new_name,
                'email' => $request->new_email,
                'password' => Hash::make($request->password),
            ]);

            return $this->success('Account information updated successfully', 200, $user);

        } catch (\Exception $e) {
            return $this->fail($e->getMessage(), 500);
        }
    }
}
