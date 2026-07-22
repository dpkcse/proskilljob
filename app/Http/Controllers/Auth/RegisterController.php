<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Traits\HasCountryBasedJobs;
use App\Models\Candidate;
use App\Models\PendingUser;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\CandidateCreateApprovalPendingNotification;
use App\Notifications\CandidateCreateNotification;
use App\Notifications\CompanyCreateApprovalPendingNotification;
use App\Notifications\CompanyCreatedNotification;
use App\Notifications\EmailVerifyNotification;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use HasCountryBasedJobs, RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        $data['candidates'] = Candidate::count();

        return view('frontend.auth.register', $data);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make(
            $data,
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    'indisposable',
                    'unique:users,email',
                    'unique:pending_users,email',
                ],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'g-recaptcha-response' => config('captcha.active') ? 'required|captcha' : '',
            ],
            [
                'g-recaptcha-response.required' => 'Please verify that you are not a robot.',
                'indisposable' => 'Please use a valid email address.',
                'g-recaptcha-response.captcha' => 'Captcha error! Try again later or contact site admin.',
                'email.unique' => 'This email address is already registered or pending verification.',
            ]
        );
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $newUsername = Str::slug($data['name']);

        // Check for username conflicts in both users and pending_users tables
        $existingUser = User::where('username', $newUsername)->first();
        $existingPendingUser = PendingUser::where('username', $newUsername)->valid()->first();

        if ($existingUser || $existingPendingUser) {
            $username = Str::slug($newUsername).'_'.Str::random(5);
        } else {
            $username = Str::slug($newUsername);
        }

        // Create verification token
        $verificationToken = Str::random(60);

        // Store user data in database instead of session
        $pendingUser = PendingUser::create([
            'role' => $data['role'] == 'candidate' ? 'candidate' : 'company',
            'name' => $data['name'],
            'username' => $username,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'created_ip' => request()->ip(),
            'verification_token' => $verificationToken,
            'expires_at' => now()->addHours(24), // Token expires in 24 hours
        ]);

        // Store verification token in password_resets table for email verification
        $verify_data = DB::table('password_resets')->updateOrInsert(
            ['email' => $data['email']],
            [
                'token' => $verificationToken,
                'created_at' => now(),
            ]
        );

        // Create temporary user object for notifications (not saved to database yet)
        $user = new User;
        $user->role = $pendingUser->role;
        $user->name = $pendingUser->name;
        $user->username = $pendingUser->username;
        $user->email = $pendingUser->email;
        $user->created_ip = $pendingUser->created_ip;

        // if mail configured, send notification to candidate and company
        if (checkMailConfig()) {
            if ($user->role == 'candidate') {
                $candidate_account_auto_activation_enabled = Setting::where('candidate_account_auto_activation', 1)->count();

                if ($candidate_account_auto_activation_enabled) {
                    Notification::route('mail', $user->email)->notify(new CandidateCreateNotification($user, $data['password']));
                } else {
                    Notification::route('mail', $user->email)->notify(new CandidateCreateApprovalPendingNotification($user, $data['password']));
                }
            } elseif ($user->role == 'company') {

                $employer_auto_activation_enabled = Setting::where('employer_auto_activation', 1)->count();

                if ($employer_auto_activation_enabled) {
                    Notification::route('mail', $user->email)->notify(new CompanyCreatedNotification($user, $data['password']));
                } else {
                    Notification::route('mail', $user->email)->notify(new CompanyCreateApprovalPendingNotification($user, $data['password']));
                }

            }

            if (setting('email_verification')) {
                Notification::route('mail', $user->email)->notify(new EmailVerifyNotification($user->email, $verificationToken));
            }
        }

        // Store email in session for redirect to verification page
        session()->put('pending_email', $data['email']);

        // Return the temporary user object (for backward compatibility)
        return $user;
    }

    public function resendMail()
    {
        // Get email from request or session
        $email = request()->get('email') ?? session()->get('pending_email');

        if (! $email) {
            return redirect()->route('register')->with('error', 'Email parameter is required.');
        }

        // Find pending user by email
        $pendingUser = PendingUser::where('email', $email)->valid()->first();

        if (! $pendingUser) {
            return redirect()->route('register')->with('error', 'No pending registration found for this email or token has expired.');
        }

        if (setting('email_verification')) {
            $token = DB::table('password_resets')->where('email', $pendingUser->email)->first();
            if ($token) {
                Notification::route('mail', $pendingUser->email)->notify(new EmailVerifyNotification($pendingUser->email, $token->token));

                return redirect()->route('verification.notice', ['email' => $email])
                    ->with('success', 'Verification email has been resent to your email address.');
            }
        }

        return redirect()->route('verification.notice', ['email' => $email])
            ->with('error', 'Unable to resend verification email. Please contact support.');
    }

    /**
     * Show the email verification notice page
     */
    public function showVerificationNotice()
    {
        $email = request()->get('email') ?? session()->get('pending_email');

        if (! $email) {
            return redirect()->route('register')->with('error', 'Email parameter is required.');
        }

        // Check if pending user still exists
        $pendingUser = PendingUser::where('email', $email)->valid()->first();

        if (! $pendingUser) {
            return redirect()->route('register')->with('error', 'No pending registration found for this email or token has expired.');
        }

        return view('frontend.auth.verify', ['email' => $email]);
    }

    /**
     * The user has been registered.
     *
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        // If email verification is enabled, redirect to verification notice
        if (setting('email_verification')) {
            return redirect()->route('verification.notice', ['email' => $user->email])
                ->with('success', 'Registration successful! Please check your email to verify your account.');
        }

        // Otherwise, redirect to dashboard as usual
        return redirect($this->redirectPath());
    }
}
