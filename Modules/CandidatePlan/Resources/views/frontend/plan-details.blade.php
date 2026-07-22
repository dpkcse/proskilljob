@extends('frontend.layouts.app')

@section('title')
    {{ __('plan') }} ({{ $plan->name }})
@endsection

@php
    $current_currency = currentCurrency();
    $code = $current_currency->code;
@endphp

@section('main')
<div class="breadcrumbs-custom breadcrumbs-height">
    <div class="container">
        <div class="row align-items-center breadcrumbs-height">
            <div class="col-12 justify-content-center text-center">
                <div class="breadcrumb-title rt-mb-10">{{ $plan->name }}</div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="{{ route('website.home') }}">{{ __('home') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('candidate.plan') }}">{{ __('Candidate Pricing Plans') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $plan->name }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<section class="section benefits bgcolor--gray-10 mt-5 pt-5">
    <div class="container">

        {{-- Coupon Section --}}
        {{-- <div class="row justify-content-center mt-3">
            <div class="col-md-6">
                <div class="card shadow-sm border">
                    <div class="card-body">
                        <h5 class="mb-3"><i class="fas fa-tag text-primary mr-2"></i> {{ __('Have a Coupon?') }}</h5>

                        <form id="apply-coupon-form">
                            @csrf
                            <div class="input-group mb-2">
                                <input type="text" name="coupon_code" id="coupon_code" class="form-control"
                                       placeholder="{{ __('Enter your coupon code') }}" aria-label="Coupon code">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">{{ __('Apply') }}</button>
                                </div>
                            </div>
                            <div id="coupon-message"></div>
                        </form>

                        <div id="discount-info" class="alert alert-success mt-3 d-none">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span id="discount-text"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

        {{-- Payment Summary --}}
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card p-3" id="pricing-summary">
                    <h5 class="mb-3">{{ __('Payment Summary') }}</h5>
                    <ul class="list-unstyled mb-0">
                        {{-- <li>
                            <strong>{{ __('Original Price') }}:</strong>
                            <span class="text-muted" id="original-price">
                                <del>{{ currencyPosition($plan->price, true) }}</del>
                            </span>
                        </li> --}}
                        <li class="mt-1 d-none" id="discount-line">
                            <strong>{{ __('Discount') }}:</strong>
                            <span class="text-success" id="discount-amount">–</span>
                        </li>
                        <li class="mt-2">
                            <strong class="text-info">{{ __('Total Amount to Pay') }}:</strong>
                            <span class="text-info font-weight-bold" id="final-price">{{ currencyPosition($plan->price, true) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Payment Gateway Buttons --}}
        <div class="row py-5 justify-content-center">
            <h5 class="col-12 text-center">{{ __('online_payment_gatewats') }}</h5>

            @php
                $showPayment = false;
            @endphp

            {{-- PayPal --}}
            @if ((config('paypal.mode') === 'sandbox' && config('paypal.sandbox.client_id')) ||
                 (config('paypal.mode') === 'live' && config('paypal.live.client_id')))
                @php $showPayment = true; @endphp
                <div class="col-md-4 my-2">
                    <div class="card jobcardStyle1">
                        <div class="card-body">
                            <div class="rt-single-icon-box">
                                <div class="iconbox-content">
                                    <div class="body-font-1 rt-mb-12">{{ __('paypal') }}</div>
                                </div>
                            </div>
                            <div class="post-info d-flex">
                                <div class="flex-grow-1">
                                    <button id="paypal_btn" type="button" class="btn btn-primary2-50 d-block">
                                        {{ __('pay_now') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Stripe --}}
            @if (config('templatecookie.stripe_active') && config('templatecookie.stripe_key') && config('templatecookie.stripe_secret'))
                @php $showPayment = true; @endphp
                <div class="col-md-4 my-2">
                    <div class="card jobcardStyle1">
                        <div class="card-body">
                            <div class="rt-single-icon-box">
                                <div class="iconbox-content">
                                    <div class="body-font-1 rt-mb-12">{{ __('stripe') }}</div>
                                </div>
                            </div>
                            <div class="post-info d-flex">
                                <div class="flex-grow-1">
                                    <button id="stripe_btn" type="button" class="btn btn-primary2-50 d-block">
                                        {{ __('pay_now') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Razorpay --}}
            @if (config('templatecookie.razorpay_active') && config('templatecookie.razorpay_key') && config('templatecookie.razorpay_secret'))
                @php $showPayment = true; @endphp
                <div class="col-md-4 my-2">
                    <div class="card jobcardStyle1">
                        <div class="card-body">
                            <div class="rt-single-icon-box">
                                <div class="iconbox-content">
                                    <div class="body-font-1 rt-mb-12">{{ __('razorpay') }}</div>
                                </div>
                            </div>
                            <div class="post-info d-flex">
                                <div class="flex-grow-1">
                                    <button id="razorpay_btn" type="button" class="btn btn-primary2-50 d-block">
                                        {{ __('pay_now') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Paystack --}}
            @if (config('templatecookie.paystack_active') && config('templatecookie.paystack_key'))
                @php $showPayment = true; @endphp
                <div class="col-md-4 my-2">
                    <div class="card jobcardStyle1">
                        <div class="card-body">
                            <div class="rt-single-icon-box">
                                <div class="iconbox-content">
                                    <div class="body-font-1 rt-mb-12">{{ __('paystack') }}</div>
                                </div>
                            </div>
                            <div class="post-info d-flex">
                                <div class="flex-grow-1">
                                    <button id="paystack_btn" type="button" class="btn btn-primary2-50 d-block">
                                        {{ __('pay_now') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Fallback --}}
            @unless($showPayment)
                <div class="col-12 text-center mt-4">
                    <x-svg.not-found-icon />
                    <h5 class="mt-4">{{ __('no_payment_method_available_here') }}</h5>
                </div>
            @endunless
        </div>
    </div>

    {{-- Payment Forms --}}
    <form action="{{ route('candidate.paypal.process') }}" method="POST" class="d-none" id="paypal-form">
        @csrf
        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
    </form>

    <form action="{{ route('candidate.stripe.process') }}" method="POST" class="d-none" id="stripe-form">
        @csrf
        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
        <script id="stripe_script" src="https://checkout.stripe.com/checkout.js" class="stripe-button"
            data-key="{{ config('templatecookie.stripe_key') }}" data-amount="{{ session('stripe_amount') }}"
            data-name="{{ config('app.name') }}" data-description="Money pay with stripe"
            data-locale="{{ app()->getLocale() == 'default' ? 'en' : app()->getLocale() }}" data-currency="{{ $code }}">
        </script>
    </form>

    {{-- Razorpay Form --}}
    <form action="{{ route('candidate.razorpay.success') }}" method="POST" class="d-none" id="razorpay-form">
        @csrf
        <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
        <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
        <input type="hidden" name="razorpay_signature" id="razorpay_signature">
    </form>

    {{-- Paystack Form --}}
    <form action="{{ route('candidate.paystack.process') }}" method="POST" class="d-none" id="paystack-form">
        @csrf
        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
    </form>
</section>
@endsection

@section('script')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    // PayPal
    $('#paypal_btn').on('click', function(e) {
        e.preventDefault();
        $('#paypal-form').submit();
    });

    // Stripe
    $('#stripe_btn').on('click', function(e) {
        e.preventDefault();
        $('.stripe-button-el').click();
    });

    // Razorpay
    $('#razorpay_btn').on('click', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route('candidate.razorpay.process') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                plan_id: '{{ $plan->id }}'
            },
            success: function(response) {
                if (response.success) {
                    var options = {
                        key: response.key,
                        amount: response.amount,
                        currency: response.currency,
                        name: response.name,
                        description: response.description,
                        order_id: response.order_id,
                        prefill: response.prefill,
                        theme: response.theme,
                        handler: function (response) {
                            $('#razorpay_payment_id').val(response.razorpay_payment_id);
                            $('#razorpay_order_id').val(response.razorpay_order_id);
                            $('#razorpay_signature').val(response.razorpay_signature);
                            $('#razorpay-form').submit();
                        },
                        modal: {
                            ondismiss: function() {
                                // Handle payment cancellation
                                console.log('Payment cancelled');
                            }
                        }
                    };
                    
                    var rzp = new Razorpay(options);
                    rzp.open();
                } else {
                    alert(response.message || 'Failed to initialize payment. Please try again.');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });

    // Paystack
    $('#paystack_btn').on('click', function(e) {
        e.preventDefault();
        $('#paystack-form').submit();
    });

    // Coupon Apply
    $('#apply-coupon-form').on('submit', function(e) {
        e.preventDefault();

        const code = $('#coupon_code').val().trim();
        const planId = '{{ $plan->id }}';

        $('#coupon-message').html('');
        $('#discount-info').addClass('d-none');

        if (!code) {
            $('#coupon-message').html('<small class="text-danger">{{ __("Please enter a coupon code.") }}</small>');
            return;
        }

        $.ajax({
            url: '{{ route('candidate.apply.coupon') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                coupon_code: code,
                plan_id: planId
            },
            success: function(response) {
                if (response.success) {
                    $('#discount-text').text(response.message);
                    $('#discount-info').removeClass('d-none');
                    $('#coupon-message').html('<small class="text-success">'+response.message+'</small>');

                    // Update pricing summary
                    $('#discount-line').removeClass('d-none');
                    $('#discount-amount').text('-' + response.discount);
                    $('#final-price').text(response.new_total);
                } else {
                    $('#coupon-message').html('<small class="text-danger">'+response.message+'</small>');
                }
            },
            error: function() {
                $('#coupon-message').html('<small class="text-danger">{{ __("An error occurred. Please try again.") }}</small>');
            }
        });
    });
</script>
@endsection
