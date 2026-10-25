<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage in routes: ->middleware('permission:approve_pr')
 * Multiple keys mean "any of": ->middleware('permission:view_all_po,view_assigned_po')
 */
class HasPermission
{
    public function handle(Request $request, Closure $next, string ...$keys): Response
    {
        $user = $request->user();

        foreach ($keys as $key) {
            if ($user?->hasPermission($key)) {
                return $next($request);
            }
        }

        return response()->json(['message' => 'You do not have permission to perform this action.'], 403);
    }
}
