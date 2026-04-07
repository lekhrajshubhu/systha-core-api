<?php

namespace Systha\Core\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\JWTGuard;

class RefreshPlatformToken
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var JWTGuard $guard */
        $guard = Auth::guard('platform');
        $token = $guard->getToken();

        if (! $token) {
            return $next($request);
        }

        $refreshedToken = null;

        try {
            $guard->user();
        } catch (TokenExpiredException $e) {
            try {
                $refreshedToken = $guard->refresh();
                $guard->setToken($refreshedToken);
                $request->headers->set('Authorization', 'Bearer ' . $refreshedToken);
            } catch (JWTException $e) {
                return response()->json(['message' => 'Token cannot be refreshed.'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Invalid token.'], 401);
        }

        $response = $next($request);

        if ($refreshedToken) {
            $response->headers->set('X-Refresh-Token', $refreshedToken);
        }

        return $response;
    }
}
