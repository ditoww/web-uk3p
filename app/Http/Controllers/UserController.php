<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class UserController extends Controller
{
    public function keepAlive(Request $request)
    {
        if (Auth::check()) {
            $exp = Carbon::now()->addSeconds(15); 
            Cache::put('user-online-' . Auth::user()->id, true, $exp);
        }
        return response()->noContent();
    }
}
