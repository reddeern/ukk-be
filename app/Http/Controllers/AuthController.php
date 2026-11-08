<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $r)
    {
        $d = $r->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:pelanggan,username',
            'email' => 'required|email|max:150|unique:pelanggan,email',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $p = Pelanggan::create($d);
        return response()->json(['pelanggan' => $p, 'token' => $p->createToken('pelanggan')->plainTextToken], 201);
    }

    public function login(Request $r)
    {
        $d = $r->validate(['login' => 'required|string', 'password' => 'required|string']);
        $p = Pelanggan::where('username', $d['login'])->orWhere('email', $d['login'])->first();
        if (!$p || !Hash::check($d['password'], $p->password)) {
            throw ValidationException::withMessages(['login' => 'Username/email atau password salah.']);
        }
        return ['pelanggan' => $p, 'token' => $p->createToken('pelanggan')->plainTextToken];
    }

    public function me(Request $r) { return $r->user(); }

    public function logout(Request $r)
    {
        $r->user()->currentAccessToken()->delete();
        return response()->noContent();
    }

    public function adminLogin(Request $r)
    {
        $d = $r->validate(['username' => 'required|string', 'password' => 'required|string']);
        $a = Admin::where('admin_username', $d['username'])->first();
        if (!$a || !Hash::check($d['password'], $a->admin_password)) {
            throw ValidationException::withMessages(['username' => 'Username atau password salah.']);
        }
        return ['admin' => $a, 'token' => $a->createToken('admin')->plainTextToken];
    }
}
