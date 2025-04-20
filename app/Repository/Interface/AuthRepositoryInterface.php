<?php

namespace App\Repository\Interface;


use Illuminate\Http\Request;

interface AuthRepositoryInterface
{
    public function register($request);

    public function login($request);

    public function logout($request);
}
