<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppliedJob;
use App\Models\Candidate;
use App\Models\Company;
use App\Models\Earning;
use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use PDF;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('backend.index');
    }

    /*
    * Show the application dashboard.
    *
    * @return \Illuminate\Contracts\Support\Renderable
    */
    public function dashboard()
    {
        session(['layout_mode' => 'left_nav']);
        $jobs = Job::withoutEdited()->get();

        $data['all_jobs'] = $jobs->count();
        $data['active_jobs'] = $jobs->where('status', 'active')->count();
        $data['expire_jobs'] = $jobs->where('status', 'expired')->count();
        $data['pending_jobs'] = $jobs->where('status', 'pending')->count();
        $data['verified_users'] = User::whereNotNull('email_verified_at')->count();
        $data['candidates'] = Candidate::all()->count();
        $data['companies'] = Company::all()->count();
        $data['earnings'] = currencyConversion(Earning::sum('usd_amount'));
        $data['email_verification'] = setting('email_verification');
        $data['monthly_job_posts'] = Job::withoutEdited()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        $data['active_users'] = User::where('status', 1)->count();

        $totalApplications = AppliedJob::count();
        $nonRejectedApplications = AppliedJob::whereHas('applicationGroup', function ($query) {
            $query->whereRaw('LOWER(name) != ?', [strtolower(__('rejected'))]);
        })->count();

        $data['hiring_success_rate'] = $totalApplications > 0
            ? round(($nonRejectedApplications / $totalApplications) * 100, 2)
            : 0;

        $currentMonthRevenue = Earning::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('usd_amount');
        $lastMonthRevenue = Earning::whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->sum('usd_amount');
        $revenueGrowth = $lastMonthRevenue > 0
            ? round((($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 2)
            : 0;

        $suspiciousCompanies = Job::select('company_id', DB::raw('count(*) as total_jobs'))
            ->whereDate('created_at', now()->toDateString())
            ->groupBy('company_id')
            ->having('total_jobs', '>=', 10)
            ->count();

        $decisionAlerts = [
            [
                'type' => 'warning',
                'title' => __('pending_jobs_need_review'),
                'value' => $data['pending_jobs'],
                'message' => __('pending_jobs_need_review_message'),
            ],
            [
                'type' => 'danger',
                'title' => __('suspicious_company_activity'),
                'value' => $suspiciousCompanies,
                'message' => __('suspicious_company_activity_message'),
            ],
            [
                'type' => 'info',
                'title' => __('unverified_users_need_attention'),
                'value' => User::whereNull('email_verified_at')->count(),
                'message' => __('unverified_users_need_attention_message'),
            ],
            [
                'type' => $revenueGrowth < 0 ? 'warning' : 'success',
                'title' => __('monthly_revenue_growth'),
                'value' => $revenueGrowth.'%',
                'message' => __('monthly_revenue_growth_message'),
            ],
        ];

        $notificationModules = [
            [
                'key' => 'user_activity_notifications',
                'priority' => 'low',
                'channel' => 'dashboard + email',
                'items' => [
                    __('new_user_registration').': '.User::whereDate('created_at', now()->toDateString())->count(),
                    __('profile_completion_based').': '.Candidate::whereNotNull('profile_complete')->count(),
                    __('account_deactivation').': '.User::where('status', 0)->count(),
                ],
            ],
            [
                'key' => 'job_content_activity_notifications',
                'priority' => 'medium',
                'channel' => 'dashboard',
                'items' => [
                    __('new_job_posted').': '.Job::whereDate('created_at', now()->toDateString())->count(),
                    __('job_pending_approval').': '.$data['pending_jobs'],
                    __('job_expired').': '.$data['expire_jobs'],
                ],
            ],
            [
                'key' => 'payment_revenue_notifications',
                'priority' => 'high',
                'channel' => 'dashboard + email',
                'items' => [
                    __('successful_payment').': '.Earning::where('payment_status', 'paid')->whereDate('created_at', now()->toDateString())->count(),
                    __('failed_payment').': '.Earning::where('payment_status', 'unpaid')->whereDate('created_at', now()->toDateString())->count(),
                ],
            ],
            [
                'key' => 'system_error_notifications',
                'priority' => 'critical',
                'channel' => 'dashboard + technical alerts',
                'items' => [
                    __('api_failure_monitoring'),
                    __('database_error_monitoring'),
                    __('slow_response_time_monitoring'),
                ],
            ],
            [
                'key' => 'security_fraud_alerts',
                'priority' => 'critical',
                'channel' => 'dashboard + email',
                'items' => [
                    __('suspicious_company_activity').': '.$suspiciousCompanies,
                    __('bulk_job_posting_alert'),
                    __('spam_message_detection'),
                ],
            ],
            [
                'key' => 'analytics_performance_notifications',
                'priority' => 'medium',
                'channel' => 'dashboard',
                'items' => [
                    __('daily_active_user_drop_monitoring'),
                    __('traffic_spike_monitoring'),
                    __('conversion_rate_change_monitoring'),
                ],
            ],
        ];

        $months = Earning::select(
            \DB::raw('MIN(created_at) AS created_at'),
            \DB::raw('sum(usd_amount) as `amount`'),
            \DB::raw("DATE_FORMAT(created_at,'%M') as month")
        )
            ->where('created_at', '>', \Carbon\Carbon::now()->startOfYear())
            ->orderBy('created_at')
            ->groupBy('month')
            ->get();

        $earnings = $this->formatEarnings($months);
        $latest_jobs = Job::withoutEdited()->with(['company', 'job_type', 'experience'])->latest()->get()->take(10);
        $latest_earnings = Earning::with('plan', 'manualPayment:id,name')->latest()->take(10)->get();
        $users = User::select(['id', 'name', 'email', 'role', 'status', 'email_verified_at', 'created_at', 'image', 'username'])->latest()->take(10)->get();
        $popular_countries = DB::table('jobs')
            ->select('country', DB::raw('count(*) as total'))
            ->orderBy('total', 'desc')
            ->groupBy('country')
            ->limit(10)
            ->get();

        $current_currency = currentCurrency();

        return view('backend.index', compact('data', 'earnings', 'popular_countries', 'latest_jobs', 'latest_earnings', 'users', 'current_currency', 'decisionAlerts', 'notificationModules'));
    }

    /*
    * Mark all notifications as read
    *
    * @return Response
    */
    public function notificationRead()
    {
        $admin = auth('admin')->user();
        if (! $admin) {
            return response()->json(false, 401);
        }

        foreach ($admin->unreadNotifications as $notification) {
            $notification->markAsRead();
        }

        return response()->json(true);
    }

    /*
    * Get all notifications
    *
    * @return Response
    */
    public function allNotifications()
    {
        $admin = auth('admin')->user();
        abort_if(! $admin, 401);

        $notifications = $admin->notifications()->paginate(20);

        return view('backend.notifications', compact('notifications'));
    }

    /*
    * Format earnings data
    *
    * @param object $data
    * @return array
    */
    private function formatEarnings(object $data)
    {
        $amountArray = [];
        $monthArray = [];

        foreach ($data as $value) {
            array_push($amountArray, $value->amount);
            array_push($monthArray, $value->month);
        }

        return ['amount' => $amountArray, 'months' => $monthArray];
    }

    /*
    * Download transaction invoice
    *
    * @param Earning $transaction
    * @return Response
    */
    public function downloadTransactionInvoice(Earning $transaction)
    {
        $transaction = $transaction->load('plan', 'company.user.contactInfo');
        $pdf = PDF::loadView('frontend.pages.invoice.download-invoice', compact('transaction'))->setOptions(['defaultFont' => 'sans-serif']);

        return $pdf->stream();

        return $pdf->download('invoice_'.$transaction->order_id.'.pdf');
    }

    /*
    * View transaction invoice
    *
    * @param Earning $transaction
    * @return Response
    */
    public function viewTransactionInvoice(Earning $transaction)
    {
        $transaction = $transaction->load('plan', 'company.user.contactInfo');

        return view('frontend.pages.invoice.preview-invoice', compact('transaction'));
    }
}
