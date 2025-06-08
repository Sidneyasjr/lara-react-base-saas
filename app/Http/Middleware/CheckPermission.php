<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Você precisa estar logado para acessar esta página.');
        }

        if (!Auth::user()->can($permission)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Você não tem permissão para realizar esta ação.',
                    'required_permission' => $permission,
                ], 403);
            }

            return redirect()->back()
                ->with('error', 'Você não tem permissão para acessar esta página.');
        }

        return $next($request);
    }
}
