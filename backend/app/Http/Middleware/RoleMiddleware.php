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

        $userRole = strtolower((string) ($request->user()->role ?? $request->user()->user_type ?? ''));
        $allowedRoles = [];

        foreach ($roles as $role) {
            $normalizedRole = strtolower((string) $role);
            $allowedRoles[] = $normalizedRole;
            // Backward-compatible alias: "shop" middleware should allow "shop_owner" users.
            if ($normalizedRole === 'shop') {
                $allowedRoles[] = 'shop_owner';
            }
            // Backward-compatible aliases between owner and shop_owner.
            if ($normalizedRole === 'shop_owner') {
                $allowedRoles[] = 'owner';
            }
            if ($normalizedRole === 'owner') {
                $allowedRoles[] = 'shop_owner';
            }
        }

        if ($userRole !== 'admin' && !in_array($userRole, $allowedRoles, true)) {
            return response()->json(['message' => 'Unauthorized. You do not have permission to access this resource.'], 403);
        }

        return $next($request);
    }
}
