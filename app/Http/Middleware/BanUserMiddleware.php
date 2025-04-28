<?php

namespace App\Http\Middleware;

use App\ResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BanUserMiddleware
{
    use ResponseTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(auth()->check() && (auth()->user()->status === 'ban')){
//            Auth::logout();
//
//            $request->session()->invalidate();
//
//            $request->session()->regenerateToken();

            $user = Auth::guard('sanctum')->user();
            $user->tokens()->delete();

            return $this->fail('Your account has been banned. Please contact the administrator',403);
        }
        return $next($request);
    }
}
