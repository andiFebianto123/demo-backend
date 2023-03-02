<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IndexController extends Controller
{
    function index(){
        return view('page/index');
    }
    function create(){
        return view('create/index');
    }
}
