<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin 
{
    public function handle(Request $request, Closure $next): Response
    {
        // 未ログイン、または role が管理者(1)がでなければ403で遮断
        if (!Auth::check() || Auth::user()->role !== 1) {
            abort(403, '管理者権限が必要です。');
        }

        return $next($request);
    }
}