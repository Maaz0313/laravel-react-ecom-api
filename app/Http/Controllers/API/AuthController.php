<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    //Register POST
    public function register(Request $request)
    {
        //validate first using Validator
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|max:255|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'validation_errors' => $validator->messages(),
            ]);
        }
        $otp = rand(100000, 999999); // Generate a random 6-digit OTP
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10)
        ]);

        // Send OTP email
        Mail::to($user->email)->send(new OtpMail($otp));
        $token = $user->createToken($user->email . '_auth_token')->plainTextToken;
        return response()->json(
            [
                'username' => $user->name,
                'email' => $user->email,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'message' => 'Registered successfully. Please verify your email by entering the OTP sent to your email. Please use same browser for registration and verification.',
            ],
            201);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->otp !== $request->otp || $user->otp_expires_at < now()) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 403);
        }

        // OTP is valid, mark user as verified
        $user->email_verified_at = now('Asia/Karachi');
        $user->otp = null; // Clear OTP
        $user->otp_expires_at = null; // Clear OTP expiration
        $user->save();

        return response()->json(['message' => 'Email verified successfully.'], 200);
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Generate new OTP
        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();

        // Send new OTP email
        Mail::to($user->email)->send(new OtpMail($otp));

        return response()->json(['message' => 'OTP resent. Please check your email.'], 200);
    }

}
