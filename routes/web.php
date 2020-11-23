<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    Auth::login(User::find(1)->load('club', 'buddies'));

    $users = User::with('club')->orderBy('name')->paginate(10);

    return view('users', compact('users'));
});
