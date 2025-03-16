<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminRequestController extends Controller
{
    public function index()
    {
        return view('admin_request_list');
    }
}