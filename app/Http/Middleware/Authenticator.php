<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\JWTAuth;

class Authenticator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    private $jw;
    public function __construct(JWTAuth $jw)
    {
        $this->jw = $jw;
    }

    public function handle($request, Closure $next)
    {
        try {
            $this->jw->parseToken();
            $user = $this->jw->parseToken()->authenticate();
            if (isset($user)){
                $request['user_id'] = $user->id;
                return $next($request);
            }
        } catch (\Exception $e){
            return response('User not found. Check the token', 403);
        }
    }
}
