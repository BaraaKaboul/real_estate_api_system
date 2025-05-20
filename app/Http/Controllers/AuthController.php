<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repository\Interface\AuthRepositoryInterface;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $auth;

    public function __construct(AuthRepositoryInterface $auth)
    {
        $this->auth = $auth;
    }

    public function register(Request $request){
        return $this->auth->register($request);
    }

    public function login(Request $request){
        return $this->auth->login($request);
    }

    public function logout(Request $request){
        return $this->auth->logout($request);
    }

    public function updateProfile(Request $request, $id){
        return $this->auth->updateProfile($request, $id);
    }
}
