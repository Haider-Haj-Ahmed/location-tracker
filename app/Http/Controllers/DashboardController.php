<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

// app/Http/Controllers/DashboardController.php
class DashboardController extends Controller
{
    public function index()
    {
        $deviceCount = Device::where('is_active', true)->count();
        return view('dashboard', compact('deviceCount'));
    }
}
