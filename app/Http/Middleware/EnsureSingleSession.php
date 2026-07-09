<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleSession
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->current_token_id) {
            $currentToken = $user->currentAccessToken();

            if (!$currentToken || $currentToken->id != $user->current_token_id) {

                // force logout
                return response()->json([
                    'message' => 'Session expired due to login from another device'
                ], 401);
            }
        }

        return $next($request);
    }
}
