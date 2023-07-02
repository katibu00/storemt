<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\Models\LoginLog;


class AuthController extends Controller
{
    public function loginIndex()
    {
        return view('auth.login');
    }
    public function changePasswordIndex()
    {
        return view('auth.change_password');
    }


    public function login(Request $request)
    {
        $request->validate([
            'email_or_phone' => 'required',
            'password' => 'required',
        ]);
    
        $credentials = $this->getCredentials($request);
        $rememberMe = $request->filled('rememberMe');
    
        try {
            if (Auth::attempt($credentials, $rememberMe)) {
                // Create a login log entry
                $this->createLoginLog($request->ip(), auth()->user());
    
                if ($request->ajax()) {
                    return response()->json(['success' => true, 'redirect_url' => $this->getRedirectUrl()]);
                } else {
                    return redirect()->route($this->getRedirectRoute());
                }
            } else {
                throw ValidationException::withMessages([
                    'login_error' => 'Invalid credentials.',
                ]);
            }
        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            } else {
                return redirect()->back()->withErrors($e->errors())->withInput()->with('error_message', 'Invalid credentials.');
            }
        }
    }
    
    private function createLoginLog($ipAddress, $user)
    {
        // Create a new login log entry in the database
        LoginLog::create([
            'ip_address' => $ipAddress,
            'user_id' => $user->id,
            'business_id' => $user->business_id,
            'login_at' => now(),
        ]);
    }
    


    protected function getCredentials(Request $request)
    {
        $field = filter_var($request->email_or_phone, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        return [
            $field => $request->email_or_phone,
            'password' => $request->password,
        ];
    }

    protected function getRedirectRoute()
    {
        $userType = auth()->user()->usertype;

        switch ($userType) {
            case 'admin':
                return 'admin.home';
            case 'cashier':
                return 'cashier.home';
            case 'super':
                return 'super.home';
            case 'supplier':
                return 'supplier.home';
            default:
                return 'login';
        }
    }

    protected function getRedirectUrl()
    {
        return route($this->getRedirectRoute());
    }

    public function logout(){
        auth()->logout();
        return redirect()->route('login');
    }

    // public function changePasswordStore(){
       
    // }


    public function changePasswordStore(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->with('error', 'The current password is incorrect.');
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->back()->with('success', 'Password changed successfully.');
    }
}
