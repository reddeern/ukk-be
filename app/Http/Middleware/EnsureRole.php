<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use App\Models\Pelanggan;
use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = $request->user();
        $ok = $role === 'admin' ? $user instanceof Admin : $user instanceof Pelanggan;
        abort_unless($ok, 403, 'Akses ditolak.');
        return $next($request);
    }
}
