<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $userRole = (string) $request->user()->role;
        $allowedRoles = [];

        foreach ($roles as $role) {
            $allowedRoles[] = $role;
            // Backward-compatible alias: "shop" middleware should allow "shop_owner" users.
            if ($role === 'shop') {
                $allowedRoles[] = 'shop_owner';
            }
        }

        if ($userRole !== 'admin' && !in_array($userRole, $allowedRoles, true)) {
            return response()->json(['message' => 'Unauthorized. You do not have permission to access this resource.'], 403);
        }

        return $next($request);
    }
}
