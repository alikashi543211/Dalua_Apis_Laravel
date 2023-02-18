<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->header('Authorization')) {
            try {

                $user = JWTAuth::parseToken()->authenticate();
                if ($user) {
                    if ($user->status == STATUS_ACTIVE) {
                        return $next($request);
                    }
                    Auth::logout();
                    return response()->json(['success' => false, 'message' => 'You’re no longer authorized to use this app, please contact administrator team for further details.'], ERROR_401);
                }
                return response()->json(['success' => false, 'message' => 'Session has been expired'], ERROR_401);
            } catch (TokenExpiredException $e) {
                return response()->json(['success' => false, 'message' => 'Session has been expired'], ERROR_401);
            } catch (TokenBlacklistedException $e) {
                return response()->json(['success' => false, 'message' => 'Session has been expired'], ERROR_401);
            } catch (TokenInvalidException $e) {
                return response()->json(['success' => false, 'message' => 'Session has been expired'], ERROR_401);
            } catch (JWTException $e) {
                return response()->json(['success' => false, 'message' => 'Session has been expired'], ERROR_401);
            }
        } else return response()->json([
            'success' => false,
            'message' => 'Authorization token is missing'
        ], ERROR_401);
    }
}
