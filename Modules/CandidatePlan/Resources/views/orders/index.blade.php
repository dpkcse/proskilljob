@extends('backend.layouts.app')

@section('title')
    {{ __('candidate_orders') }}
@endsection

@section('content')
    <div class="">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title line-height-36">{{ __('candidate_orders') }}</h3>
                            @if (request('candidate') || request('plan') || request('payment_method') || request('payment_status') || request('sort_by'))
                                <div>
                                    <a href="{{ route('module.candidateplan.orders') }}" class="btn bg-danger"><i class="fas fa-times"></i>
                                        &nbsp;{{ __('clear') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                    <form id="filterForm" action="{{ route('module.candidateplan.orders') }}" method="GET">
                        <div class="card-body border-bottom row">
                            <div class="col-xl-3 col-md-6 col-12">
                                <label>{{ __('candidates') }}</label>
                                <select name="candidate" class="form-control select2bs4">
                                    <option {{ request('candidate') ? '' : 'selected' }} value="" selected>
                                        {{ __('all') }}
                                    </option>
                                    @foreach ($candidates as $candidate)
                                        <option {{ request('candidate') == $candidate->id ? 'selected' : '' }}
                                            value="{{ $candidate->id }}">{{ $candidate->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-3 col-md-6 col-12">
                                <label>{{ __('plan') }}</label>
                                <select name="plan" class="form-control select2bs4">
                                    <option {{ request('plan') ? '' : 'selected' }} value="" selected>
                                        {{ __('all') }}
                                    </option>
                                    @foreach ($plans as $plan)
                                        <option {{ request('plan') == $plan->id ? 'selected' : '' }}
                                            value="{{ $plan->id }}">{{ $plan->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-3 col-md-6 col-12">
                                <label>{{ __('payment_method') }}</label>
                                <select name="payment_method" class="form-control select2bs4">
                                    <option {{ request('payment_method') ? '' : 'selected' }} value="" selected>
                                        {{ __('all') }}
                                    </option>
                                    <option {{ request('payment_method') == 'paypal' ? 'selected' : '' }} value="paypal">
                                        {{ __('paypal') }}
                                    </option>
                                    <option {{ request('payment_method') == 'stripe' ? 'selected' : '' }} value="stripe">
                                        {{ __('stripe') }}
                                    </option>
                                    <option {{ request('payment_method') == 'razorpay' ? 'selected' : '' }} value="razorpay">
                                        {{ __('razorpay') }}
                                    </option>
                                    <option {{ request('payment_method') == 'paystack' ? 'selected' : '' }} value="paystack">
                                        {{ __('paystack') }}
                                    </option>
                                    <option {{ request('payment_method') == 'sslcommerz' ? 'selected' : '' }} value="sslcommerz">
                                        {{ __('sslcommerz') }}
                                    </option>
                                    <option {{ request('payment_method') == 'instamojo' ? 'selected' : '' }} value="instamojo">
                                        {{ __('instamojo') }}
                                    </option>
                                    <option {{ request('payment_method') == 'flutterwave' ? 'selected' : '' }}
                                        value="flutterwave">
                                        {{ __('flutterwave') }}
                                    </option>
                                    <option {{ request('payment_method') == 'mollie' ? 'selected' : '' }} value="mollie">
                                        {{ __('mollie') }}
                                    </option>
                                    <option {{ request('payment_method') == 'midtrans' ? 'selected' : '' }} value="midtrans">
                                        {{ __('midtrans') }}
                                    </option>
                                    <option {{ request('payment_method') == 'offline' ? 'selected' : '' }} value="offline">
                                        {{ __('offline') }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-xl-3 col-md-6 col-12">
                                <label>{{ __('payment_status') }}</label>
                                <select name="payment_status" class="form-control select2bs4">
                                    <option {{ request('payment_status') ? '' : 'selected' }} value="" selected>
                                        {{ __('all') }}
                                    </option>
                                    <option {{ request('payment_status') == 'paid' ? 'selected' : '' }} value="paid">
                                        {{ __('paid') }}
                                    </option>
                                    <option {{ request('payment_status') == 'unpaid' ? 'selected' : '' }} value="unpaid">
                                        {{ __('unpaid') }}
                                    </option>
                                    <option {{ request('payment_status') == 'pending' ? 'selected' : '' }} value="pending">
                                        {{ __('pending') }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-3 col-md-6 col-12">
                                <label>{{ __('sort_by') }}</label>
                                <select name="sort_by" class="form-control select2bs4">
                                    <option {{ !request('sort_by') || request('sort_by') == 'latest' ? 'selected' : '' }}
                                        value="latest" selected>
                                        {{ __('latest') }}
                                    </option>
                                    <option {{ request('sort_by') == 'oldest' ? 'selected' : '' }} value="oldest">
                                        {{ __('oldest') }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </form>
                    <div class="card-body text-center table-responsive p-0">
                        <table class="ll-table table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>{{ __('order_and_transaction') }}</th>
                                    <th>{{ __('candidate') }}</th>
                                    <th>{{ __('plan') }}</th>
                                    <th>{{ __('amount') }}</th>
                                    <th>{{ __('payment_method') }}</th>
                                    <th>{{ __('created_time') }}</th>
                                    <th>{{ __('payment_status') }}</th>
                                    <th width="10%">{{ __('action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    <tr>
                                        <td>
                                            <p>#{{ $order->id }}</p>
                                            <p>{{ __('transaction') }}: <strong>{{ $order->transaction_id ?? 'N/A' }}</strong></p>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $order->user->avatar_url ?? asset('backend/image/default.png') }}" 
                                                     alt="avatar" class="rounded-circle me-2" width="40" height="40">
                                                <div>
                                                    <h6 class="mb-0">{{ $order->user->name }}</h6>
                                                    <small class="text-muted">{{ $order->user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-primary">{{ $order->plan->name }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ number_format($order->amount, 2) }} {{ config('templatecookie.currency', 'USD') }}</strong>
                                        </td>
                                        <td>
                                            @if ($order->payment_method == 'offline')
                                                {{ __('offline') }}
                                            @else
                                                {{ ucfirst($order->payment_method) }}
                                            @endif
                                        </td>
                                        <td class="text-muted">
                                            {{ $order->created_at->format('M d, Y') }}
                                        </td>
                                        <td>
                                            @if ($order->payment_status == 'paid')
                                                <span class="ll-badge ll-bg-success">{{ __('paid') }}</span>
                                            @elseif ($order->payment_status == 'pending')
                                                <span class="ll-badge ll-bg-warning">{{ __('pending') }}</span>
                                            @else
                                                <span class="ll-badge ll-bg-danger">{{ __('unpaid') }}</span>
                                            @endif
                                        </td>
                                        <td class="d-flex align-items-center">
                                            <a href="{{ route('module.candidateplan.orders.show', $order->id) }}" class="btn ll-btn ll-border-none">
                                                {{ __('view_details') }}
                                                <x-svg.table-btn-arrow />
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">
                                            <div class="empty py-5">
                                                <x-not-found message="{{ __('no_data_found') }}" />
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($orders->total() > $orders->count())
                        <div class="mt-3 d-flex justify-content-center">{{ $orders->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Auto-submit form when filter changes
        $('select[name="candidate"], select[name="plan"], select[name="payment_method"], select[name="payment_status"], select[name="sort_by"]').on('change', function() {
            $('#filterForm').submit();
        });
    });
</script>
@endsection
