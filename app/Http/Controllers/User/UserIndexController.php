<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserIndexController extends Controller
{
    public function __invoke(): AnonymousResourceCollection
    {
        return UserResource::collection(User::all());
    }
}
