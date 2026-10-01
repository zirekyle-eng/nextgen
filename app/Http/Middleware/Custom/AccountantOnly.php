<?php

namespace App\Http\Middleware\Custom;

use Closure;
use Illuminate\Support\Facades\Auth;

class AccountantOnly
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (strtolower((string) Auth::user()->user_type) !== 'accountant') {
            abort(403, 'Only accountant can access Academic Management.');
        }

        return $next($request);
    }
}
