<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MyParentMiddleware
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
        $user = auth()->user();

        // Check if user is authenticated and has user_type = 'parent'
        if (!$user || $user->user_type !== 'parent') {
            return redirect()->route('home')->with('error', 'أنت لا تملك صلاحية الوصول');
        }

        return $next($request);
    }
}
