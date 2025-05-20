<?php

namespace App\Repository\Interface;


use App\Models\User;
use Illuminate\Http\Request;

interface AuthRepositoryInterface
{
    public function register($request);

    public function login($request);

    public function logout($request);

    public function updateProfile($request, $id);
}
