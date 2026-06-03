<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        $code = rand(100000, 999999);

        OtpCode::create([
            'email' => $request->email,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($request->email)->send(new OtpMail($code));

        session([
            'register_name' => $request->name,
            'register_email' => $request->email,
        ]);

        return redirect()->route('register.verify');
    }

    public function showVerifyOtp()
    {
        if (!session('register_email')) {
            return redirect()->route('register');
        }

        return view('auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $otp = OtpCode::where('email', session('register_email'))
            ->where('code', $request->code)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) {
            return back()->withErrors([
                'code' => 'Código inválido ou expirado.',
            ]);
        }

        $otp->update([
            'used' => true,
        ]);

        session([
            'otp_verified' => true,
        ]);

        return redirect()->route('register.profile');
    }

    public function showProfileForm()
    {
        if (!session('register_email') || !session('otp_verified')) {
            return redirect()->route('register');
        }

        return view('auth.complete-register');
    }

    public function completeRegistration(Request $request)
    {
        if (!session('register_email') || !session('otp_verified')) {
            return redirect()->route('register');
        }

        $request->validate([
            'birth_date' => 'required|date',
            'gender' => 'required|string',
            'password' => 'required|string|confirmed|min:6',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $photoPath = null;

        if ($request->hasFile('profile_photo')) {
            $photoPath = $request->file('profile_photo')
                ->store('profile-photos', 'public');
        }

        $user = User::create([
            'name' => session('register_name'),
            'email' => session('register_email'),
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
            'profile_photo' => $photoPath,
            'email_verified_at' => now(),
            'password' => $request->password,
        ]);

        Auth::login($user);

        session()->forget([
            'register_name',
            'register_email',
            'otp_verified',
        ]);

        return redirect()->route('dashboard');
    }
}