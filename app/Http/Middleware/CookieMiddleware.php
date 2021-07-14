<?php

namespace App\Http\Middleware;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
class CookieMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }
    public function handle($request, Closure $next,$guard = null)
    {
        $current = $this->auth->guard()->user();
        $current = json_decode($current);
        if(!$current->admin){
            return response('Not authorized');
        }
        return $next($request);
    }
}