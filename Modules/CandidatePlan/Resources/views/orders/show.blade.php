@extends('backend.layouts.app')

@section('title')
    {{ __('candidate_order_details') }}
@endsection

@section('content')
    <div class="">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title line-height-36">{{ __('candidate_order_details') }}</h3>
                            <a href="{{ route('module.candidateplan.orders') }}" class="btn bg-secondary">
                                <i class="fas fa-arrow-left"></i>
                                {{ __('back_to_orders') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>{{ __('order_information') }}</h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <th>{{ __('order_id') }}</th>
                                        <td>#{{ $order->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('transaction_id') }}</th>
                                        <td>{{ $order->transaction_id ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('payment_method') }}</th>
                                        <td>
                                            @if ($order->payment_method == 'offline')
                                                {{ __('offline') }}
                                            @else
                                                {{ ucfirst($order->payment_method) }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('payment_status') }}</th>
                                        <td>
                                            @if ($order->payment_status == 'paid')
                                                <span class="badge badge-success">{{ __('paid') }}</span>
                                            @elseif ($order->payment_status == 'pending')
                                                <span class="badge badge-warning">{{ __('pending') }}</span>
                                            @else
                                                <span class="badge badge-danger">{{ __('unpaid') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('amount') }}</th>
                                        <td><strong>{{ number_format($order->amount, 2) }} {{ config('templatecookie.currency', 'USD') }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('created_at') }}</th>
                                        <td>{{ $order->created_at->format('M d, Y H:i:s') }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5>{{ __('candidate_information') }}</h5>
                                <div class="d-flex align-items-center mb-3">
                                    <img src="{{ $order->user->avatar_url ?? asset('backend/image/default.png') }}" 
                                         alt="avatar" class="rounded-circle me-3" width="60" height="60">
                                    <div>
                                        <h6 class="mb-1">{{ $order->user->name }}</h6>
                                        <p class="text-muted mb-1">{{ $order->user->email }}</p>
                                        <small class="text-muted">{{ __('candidate') }}</small>
                                    </div>
                                </div>
                                
                                <h5 class="mt-4">{{ __('plan_information') }}</h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <th>{{ __('plan_name') }}</th>
                                        <td>{{ $order->plan->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('plan_description') }}</th>
                                        <td>{{ $order->plan->description ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('job_apply_limit') }}</th>
                                        <td>{{ $order->plan->job_apply_limit }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('plan_price') }}</th>
                                        <td>{{ number_format($order->plan->price, 2) }} {{ config('templatecookie.currency', 'USD') }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('recommended') }}</th>
                                        <td>
                                            @if($order->plan->recommended)
                                                <span class="badge badge-info">{{ __('yes') }}</span>
                                            @else
                                                <span class="badge badge-secondary">{{ __('no') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
