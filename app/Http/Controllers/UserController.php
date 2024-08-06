<?php

namespace App\Http\Controllers;

use App\Http\Resources\User\UserResource;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        return inertia('User/Index', [
            'users' => UserResource::collection(User::whereNot('id', auth()->id())->get()),
        ]);
    }
}
