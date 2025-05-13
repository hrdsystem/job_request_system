<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SpaController extends Controller
{
    public function job_request(){
        return view('layouts.job_request');
    }

    public function job_request_settings(){
        return view('layouts.job_request_settings');
    }
    
}