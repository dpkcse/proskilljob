<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Candidate\CandidateResource;
use App\Http\Resources\Company\CompanyResource;
use App\Models\Admin;
use App\Models\PendingUser;
use App\Models\Setting;
use App\Models\User;
use App\Models\VerificationCode;
use App\Notifications\Admin\NewUserRegisteredNotification;
use App\Notifications\Api\ResetPassword;
use App\Notifications\CandidateCreateApprovalPendingNotification;
use App\Notifications\CandidateCreateNotification;
use App\Notifications\CompanyCreateApprovalPendingNotification;
use App\Notifications\CompanyCreatedNotification;
use App\Notifications\EmailVerifyNotification;
use F9Web\ApiResponseHelpers;
use Firebase\Auth\Token\Exception\InvalidToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    use ApiResponseHelpers;

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $user->createToken('naxas')->plainTextToken;

            return $this->respondWithSuccess([
                'data' => [
                    'token' => $token,
                    'message' => 'Login Succeeded',
                    'user' => $user->role == 'candidate' ? new CandidateResource($user->candidate) : new CompanyResource($user->company),
                ],
            ]);
        } else {
            return $this->respondUnAuthenticated('Invalid Credentials');
        }
    }

    public function getUserInfo(Request $request)
    {
        $user = auth('sanctum')->user();

        if ($user) {
            $token = $request->bearerToken();

            return $this->respondWithSuccess([
                'data' => [
                    'token' => $request->bearerToken(),
                    'message' => 'User data retrieved successfully',
                    'user' => $user->role == 'candidate' ? new CandidateResource($user->candidate) : new CompanyResource($user->company),

                ],
            ]);
        } else {
            return $this->respondUnAuthenticated('Unauthenticated User');

        }
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => ['required', Rule::in(['candidate', 'company'])],
            'email' => 'required|string|email|max:255|unique:users|unique:pending_users,email',
            'password' => 'required|string|min:8|confirmed',
            'company_registration_number' => [
                'nullable',
                'required_if:role,company',
                'string',
                'max:100',
                Rule::unique('companies', 'company_registration_number'),
                Rule::unique('pending_users', 'company_registration_number'),
            ],
        ]);

        $newUsername = Str::slug($request->name);
        $oldUserName = User::where('username', $newUsername)->exists()
            || PendingUser::where('username', $newUsername)->exists();

        if ($oldUserName) {
            $username = Str::slug($newUsername).'_'.Str::random(5);
        } else {
            $username = Str::slug($newUsername);
        }

        if (setting('email_verification')) {
            return $this->registerPendingUser($request, $username);
        }

        try {
            $user = DB::transaction(function () use ($request, $username) {
                $user = User::create([
                    'role' => $request->role == 'candidate' ? 'candidate' : 'company',
                    'name' => $request->name,
                    'username' => $username,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                if ($user->role === 'company') {
                    $user->company()->update([
                        'company_registration_number' => trim($request->company_registration_number),
                    ]);
                }

                return $user;
            });
        } catch (\Throwable $th) {
            Log::error('API registration failed', [
                'email' => $request->email,
                'role' => $request->role,
                'exception' => $th,
            ]);

            return response()->json([
                'message' => 'Account could not be created. Please try again shortly.',
            ], 500);
        }

        try {
            $admins = Admin::all();
            foreach ($admins as $admin) {
                Notification::send($admin, new NewUserRegisteredNotification($admin, $user));
            }
        } catch (\Throwable $th) {
        }

        // if mail configured, send notification to candidate and company
        try {
            if (checkMailConfig()) {
                if ($user->role == 'candidate') {
                    $candidate_account_auto_activation_enabled = Setting::where('candidate_account_auto_activation', 1)->count();

                    if ($candidate_account_auto_activation_enabled) {
                        Notification::route('mail', $user->email)->notify(new CandidateCreateNotification($user, $request->password));
                    } else {
                        Notification::route('mail', $user->email)->notify(new CandidateCreateApprovalPendingNotification($user, $request->password));
                    }
                } elseif ($user->role == 'company') {
                    $employer_auto_activation_enabled = Setting::where('employer_auto_activation', 1)->count();

                    if ($employer_auto_activation_enabled) {
                        Notification::route('mail', $user->email)->notify(new CompanyCreatedNotification($user, $request->password));
                    } else {
                        Notification::route('mail', $user->email)->notify(new CompanyCreateApprovalPendingNotification($user, $request->password));
                    }
                }
            }
        } catch (\Throwable $th) {
            Log::warning('Registration email could not be sent', [
                'user_id' => $user->id,
                'exception' => $th,
            ]);
        }

        if ($user) {
            return $this->respondWithSuccess([
                'data' => $user,
                'message' => 'Registration Succeeded',
            ]);
        }

        return $this->respondError('Registration Failed');
    }

    public function verificationStatus(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if ($user?->email_verified_at) {
            return $this->respondWithSuccess([
                'data' => ['verified' => true],
                'message' => 'Email verified successfully.',
            ]);
        }

        $pendingUser = PendingUser::where('email', $request->email)->first();

        return $this->respondWithSuccess([
            'data' => [
                'verified' => false,
                'pending' => (bool) $pendingUser,
                'expired' => $pendingUser?->isExpired() ?? false,
            ],
            'message' => $pendingUser?->isExpired()
                ? 'Verification link has expired. Please resend the email.'
                : 'Email verification is still pending.',
        ]);
    }

    public function resendVerification(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $pendingUser = PendingUser::where('email', $request->email)->first();
        if (! $pendingUser) {
            return $this->respondError('No pending registration was found for this email.');
        }

        $token = Str::random(60);
        $pendingUser->update([
            'verification_token' => $token,
            'expires_at' => now()->addHours(24),
        ]);
        DB::table('password_resets')->updateOrInsert(
            ['email' => $pendingUser->email],
            ['token' => $token, 'created_at' => now()]
        );

        try {
            $this->sendVerificationEmail($pendingUser->email, $token);
        } catch (\Throwable $th) {
            Log::warning('API verification email resend failed', [
                'email' => $pendingUser->email,
                'exception' => $th,
            ]);

            return response()->json([
                'message' => 'Verification email could not be sent. Please try again shortly.',
            ], 503);
        }

        return $this->respondWithSuccess([
            'message' => 'A new verification email has been sent.',
        ]);
    }

    private function registerPendingUser(Request $request, string $username)
    {
        $token = Str::random(60);

        try {
            $pendingUser = DB::transaction(function () use ($request, $username, $token) {
                $pendingUser = PendingUser::create([
                    'role' => $request->role,
                    'name' => $request->name,
                    'username' => $username,
                    'email' => $request->email,
                    'company_registration_number' => $request->role === 'company'
                        ? trim($request->company_registration_number)
                        : null,
                    'password' => Hash::make($request->password),
                    'created_ip' => $request->ip(),
                    'verification_token' => $token,
                    'expires_at' => now()->addHours(24),
                ]);

                DB::table('password_resets')->updateOrInsert(
                    ['email' => $request->email],
                    ['token' => $token, 'created_at' => now()]
                );

                return $pendingUser;
            });

            $this->sendVerificationEmail($pendingUser->email, $token);
        } catch (\Throwable $th) {
            PendingUser::where('email', $request->email)->delete();
            DB::table('password_resets')->where('email', $request->email)->delete();
            Log::error('API pending registration failed', [
                'email' => $request->email,
                'exception' => $th,
            ]);

            return response()->json([
                'message' => 'Verification email could not be sent. Please check the address and try again.',
            ], 503);
        }

        return $this->respondWithSuccess([
            'data' => [
                'requires_verification' => true,
                'email' => $pendingUser->email,
                'expires_at' => $pendingUser->expires_at?->toIso8601String(),
            ],
            'message' => 'Registration received. Please verify your email address.',
        ]);
    }

    private function sendVerificationEmail(string $email, string $token): void
    {
        if (! checkMailConfig()) {
            throw new \RuntimeException('Mail configuration is incomplete.');
        }

        Notification::route('mail', $email)
            ->notify(new EmailVerifyNotification($email, $token));
    }

    public function profile()
    {
        $user = Auth::user();

        return $this->respondWithSuccess([
            'data' => $user,
        ]);
    }

    public function sendResetCodeEmail(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|string|email|exists:users,email',
        ]);

        $customer = User::where('email', $request->email)->first();
        $code = rand(100000, 999999);

        $customer->verificationCodes()->reset()->delete();
        $customer->verificationCodes()->create([
            'code' => $code,
            'type' => 'reset_password',
            'expire_at' => now()->addMinutes(10),
        ]);

        try {
            if (! checkMailConfig()) {
                throw new \RuntimeException('Mail configuration is incomplete.');
            }
            $customer->notify(new ResetPassword($code));
        } catch (\Throwable $th) {
            $customer->verificationCodes()->reset()->delete();
            Log::warning('Password reset email could not be sent', [
                'user_id' => $customer->id,
                'exception' => $th,
            ]);

            return response()->json([
                'message' => 'Password reset email could not be sent. Please try again shortly.',
            ], 503);
        }

        return $this->respondWithSuccess([
            'data' => [
                'message' => 'We have emailed you password reset code',
            ],
        ]);
    }

    public function reset(Request $request)
    {
        $this->validate($request, [
            'code' => 'required',
            'email' => 'required|string|max:100|email|exists:users,email',
            'password' => 'required|min:8|max:50|confirmed',
        ]);

        $customer = User::where('email', $request->email)->first();
        $verificationCode = VerificationCode::reset()
            ->where('user_id', $customer->id)
            ->where('code', $request->code)
            ->first();

        if (! $verificationCode) {
            return $this->respondError('Invalid code');
        } elseif (! $verificationCode->expire_at || now()->isAfter($verificationCode->expire_at)) {
            return $this->respondError('Code expired');
        }

        if ($customer) {
            DB::transaction(function () use ($customer, $request) {
                $customer->update([
                    'password' => bcrypt($request->password),
                ]);
                $customer->tokens()->delete();
                $customer->verificationCodes()->reset()->delete();
            });

            return $this->respondWithSuccess([
                'data' => [
                    'message' => 'Password reset successfully',
                ],
            ]);
        }

        return $this->respondNotFound('Invalid code');
    }

    public function socialLogin(Request $request)
    {

        // Launch Firebase Auth
        $auth = app('firebase.auth');

        // Retrieve the Firebase credential's token
        $idTokenString = $request->input('Firebasetoken');

        try {
            // Try to verify the Firebase credential token with Google
            $verifiedIdToken = $auth->verifyIdToken($idTokenString);
        } catch (\InvalidArgumentException $e) {
            // If the token has the wrong format
            return response()->json([
                'message' => 'Unauthorized - Can\'t parse the token: '.$e->getMessage(),
            ], 401);
        } catch (InvalidToken $e) {
            // If the token is invalid (expired ...)
            return response()->json([
                'message' => 'Unauthorized - Token is invalide: '.$e->getMessage(),
            ], 401);
        }

        // Retrieve the UID (User ID) from the verified Firebase credential's token
        $uid = $verifiedIdToken->getClaim('sub');

        // Retrieve the user model linked with the Firebase UID
        $user = User::where('firebase_uid', $uid)->first();

        // If the user doesn't exist, create a new user (you may customize this)
        try {
            if (! $user) {
                $user = User::create([
                    'firebase_uid' => $uid,
                    'role' => $request->input('role'),
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating user: '.$e->getMessage(),
            ], 500);
        }

        // Create a Personal Access Token using Sanctum
        $token = $user->createToken('naxas')->plainTextToken;

        return $this->respondWithSuccess([
            'data' => [
                'token' => $token,
                'message' => 'User data retrieved successfully',
                'user' => $user->role == 'candidate' ? new CandidateResource($user->candidate) : new CompanyResource($user->company),
            ],
        ]);

    }
}
