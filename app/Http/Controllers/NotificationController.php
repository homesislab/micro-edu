<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        return auth()->user()->unreadNotifications;
    }

    public function markAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->noContent();
    }
}
