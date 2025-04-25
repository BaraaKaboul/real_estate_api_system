<?php

namespace App\Http\Middleware;

use App\ResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserMiddleware
{
    use ResponseTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()){
            if (auth()->user()->role == 'user'){
                return $next($request);
            }
            return $this->fail('you dont have permission',403);
        }
        return $this->fail('You are not authenticated',401);
    }
}
