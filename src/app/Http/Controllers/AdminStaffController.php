<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminStaffController extends Controller
{
    public function index()
    {
        return view('admin_staff_list');
    }
}