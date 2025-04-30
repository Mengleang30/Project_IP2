<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        $user = Auth::user()();

        // Check if the user has the required role
        if ($role === 'admin' && !$user->isAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403); // Forbidden if not admin
        }

        if ($role === 'user' && !$user->isUser()) {
            return response()->json(['message' => 'Forbidden'], 403); // Forbidden if not customer
        }

        return $next($request);
    }
}
