<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Usage: ->middleware('role:admin') or ('role:admin,organizer')
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (!empty($roles) && !in_array($user->role, $roles, true)) {
            return response()->json(['message' => 'Forbidden (insufficient role)'], 403);
        }

        return $next($request);
    }
}
