<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminStaffController extends Controller
{
    public function index()
    {
        $staffs = User::where('is_admin', false)->get();
        return view('admin_staff_list', compact('staffs'));
    }
}