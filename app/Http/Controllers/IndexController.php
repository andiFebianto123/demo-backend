<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class IndexController extends Controller
{
    // function index(){
    //     return view('page/index');
    // }
    // function create(){
    //     return view('create/index');
    // }

    function login(){
        return view('inc/login');
    }
    function register(){
        return view('inc/register');
    }

    function customLogin(Request $request){
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                 ->withErrors($validator->errors())
                 ->withInput();
       }

       if (Auth::attempt($request->only('username', 'password'))) {
            $request->session()->regenerate();
            return redirect()->intended('product');
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');  
    }

    function customRegistration(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'username' => 'required|unique:users,username',
            'password' => 'required|min:6',
            'confirm_password' => [
                'required',
                'min:6', 
                function ($attribute, $value, $fail) use($request){
                    if($value !== $request->password){
                        $fail("The password and confirmation password do not match.");
                    }
                },
            ],
            'terms' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                 ->withErrors($validator->errors())
                 ->withInput();
       }

       $user = new User;
       $user->name = $request->name;
       $user->username = $request->username;
       $user->password = Hash::make($request->password);
       $user->save();
       Session::flash('register_success','You have successfully registered');
       return redirect('login');
    }

    function logout(Request $request){
        Auth::logout();
 
        $request->session()->invalidate();
    
        $request->session()->regenerateToken();
        return Redirect('login');
    }

}
