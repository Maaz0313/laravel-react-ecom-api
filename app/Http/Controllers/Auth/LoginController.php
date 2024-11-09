<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth:sanctum')->only('logout');
    }

    /**
     * Handle a login request to the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'validation_errors' => $validator->messages(),
            ], 422); // 422 Unprocessable Entity
        }
        $remember = $request->remember ?? false; // Check if the 'remember' flag is sent
        // Attempt to log the user in
        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            // Create a token for the user
            $token = $user->createToken($user->email . '_auth_token')->plainTextToken;
            if (!$user->email_verified_at) {
                // Revoke the token
                $user->tokens()->delete(); // Revoke all tokens for the user

                // Return a JSON response with an error message and status code
                return response()->json([
                    'message' => 'Your email address is not verified. Please check your inbox for the verification link.',
                ], 403); // 403 Forbidden status code
            }
            return response()->json([
                'message' => 'Login successful',
                'username' => $user->name,
                'email' => $user->email,
                'access_token' => $token,
            ], 200);
        }

        // If the login attempt was unsuccessful, return an error response
        return response()->json([
            'message' => 'Invalid credentials',
        ], 401); // 401 Unauthorized
    }

    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Revoke the user's current access token
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out.'], 200);
    }
}
