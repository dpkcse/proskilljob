@extends('frontend.layouts.app')

{{-- @section('description')
    @php
        $data = metaData('candidate_pricing');
    @endphp
    {{ $data->description ?? 'Candidate Pricing Plans' }}
@endsection
@section('og:image')
    {{ asset($data->image ?? '') }}
@endsection
@section('title')
    {{ $data->title ?? 'Candidate Pricing Plans' }}
@endsection --}}

@section('main')
    <div class="breadcrumbs-custom breadcrumbs-height">
        <div class="container">
            <div class="row align-items-center breadcrumbs-height">
                <div class="col-12 justify-content-center text-center">
                    <div class="breadcrumb-title rt-mb-10">{{ __('Candidate Pricing Plans') }}</div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center">
                            <li class="breadcrumb-item"><a href="{{ route('website.home') }}">{{ __('home') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Candidate Pricing Plans') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <section class="terms-condition">
        <div class="container">
            <div class="pricing-options tw-justify-between">
                <div class="choose-pricing">
                    <h2>{{ __('Choose Your Candidate Plan') }}</h2>
                    <p>{{ __('Select a plan that best fits your job search needs. Get access to premium features and increase your chances of landing your dream job.') }}</p>
                    <a href="#candidate_pricing_package">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11.875 13.75L8.125 17.5L4.375 13.75" stroke="#0A65CC" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M15.625 2.5C13.6359 2.5 11.7282 3.29018 10.3217 4.6967C8.91518 6.10322 8.125 8.01088 8.125 10V17.5" stroke="#0A65CC" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        {{ __('View Available Plans') }}
                    </a>
                </div>
            </div>

            @if ($candidatePlans->count() > 0)
                <div class="row justify-content-center text-center" id="candidate_pricing_package">
                    <div class="col-12">
                        <div class="rt-spacer-100 rt-spacer-md-50"></div>
                        <h4 class="rt-mb-18">
                            {{ __('Choose Your Plan') }}
                        </h4>
                        <div class="body-font-3 text-gray-500 rt-mb-24 max-474 d-inline-block">
                            {{ __('Select the perfect plan to boost your job search journey') }}
                        </div>
                    </div>
                </div>
            @endif

            <section class="pricing-area mt-5" id="candidate_pricing_package">
                <div class="row">
                    @php
                        $user = auth('user')->user();
                        $hasUsedFreePlan = false;
                        if ($user) {
                            $hasUsedFreePlan = \Illuminate\Support\Facades\DB::table('candidate_plan_transactions')
                                ->where('user_id', $user->id)
                                ->where('amount', 0)
                                ->where('payment_method', 'free')
                                ->exists();
                        }
                    @endphp
                    
                    @forelse ($candidatePlans as $plan)
                        @if ($plan->is_active)
                            @php
                                // Hide free plans if user has already used one
                                $shouldHide = $plan->price == 0 && $hasUsedFreePlan;
                            @endphp
                            
                            @if (!$shouldHide)
                                <div class="col-xl-4 col-lg-4 col-md-6 rt-mb-24">
                                    <div class="single-price-table mb-4 mb-md-0 {{ $plan->recommended ? 'active' : '' }}">
                                        <div class="price-header">
                                            <h6 class="rt-mb-10">{{ $plan->name }}</h6>
                                            @if ($plan->recommended)
                                                <span class="badge bg-primary-500 text-white">{{ __('recommended') }}</span>
                                            @endif
                                            <span class="text-gray-500 body-font-3 rt-mb-15 d-block">
                                                {{ $plan->description }}
                                            </span>
                                            <div>
                                                <span class="tw-text-[#0A65CC] tw-text-[36px] tw-leading-[44px] tw-font-medium">
                                                    {{ currencyPosition($plan->price, true, $current_currency) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="price-body">
                                            <ul class="rt-list">
                                                <li>
                                                    <span class="tw-inline-flex tw-justify-center tw-items-center tw-w-6 tw-h-6 tw-rounded-full tw-bg-[#eef5fc]">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M13.3334 4L6.00008 11.3333L2.66675 8" stroke="#007BFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                    </span>
                                                    <span>
                                                        <b>{{ $plan->job_apply_limit }}</b> {{ __('Job Applications') }}
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="price-footer">
                                            @auth('user')
                                                @if ($plan->price == 0)
                                                    <form action="{{ route('candidate.plan.purchase.free') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="plan" value="{{ $plan->id }}">
                                                        <button class="btn btn-primary-50 d-block">
                                                            <span class="button-content-wrapper">
                                                                <span class="button-icon align-icon-right">
                                                                    <i class="ph-arrow-right"></i>
                                                                </span>
                                                                <span class="button-text">
                                                                    {{ __('get_started') }}
                                                                </span>
                                                            </span>
                                                        </button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('candidate.plan.details', $plan->id) }}" class="btn btn-primary-50 d-block">
                                                        <span class="button-content-wrapper">
                                                            <span class="button-icon align-icon-right">
                                                                <i class="ph-arrow-right"></i>
                                                            </span>
                                                            <span class="button-text">
                                                                {{ __('get_started') }}
                                                            </span>
                                                        </span>
                                                    </a>
                                                @endif
                                            @else
                                                <button type="button" class="btn btn-primary-50 d-block login_required">
                                                    <span class="button-content-wrapper">
                                                        <span class="button-icon align-icon-right">
                                                            <i class="ph-arrow-right"></i>
                                                        </span>
                                                        <span class="button-text">
                                                            {{ __('get_started') }}
                                                        </span>
                                                    </span>
                                                </button>
                                            @endauth
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    @empty
                        <div class="col-md-12">
                            <div class="card text-center">
                                <x-not-found message="{{ __('no_candidate_plans_found') }}" />
                            </div>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </section>

    @if ($cms_setting->payment_logo1 || $cms_setting->payment_logo2 || $cms_setting->payment_logo3 || $cms_setting->payment_logo4 || $cms_setting->payment_logo5 || $cms_setting->payment_logo6)
        <section class="tw-py-10">
            <div class="container">
                <div class="tw-max-w-max tw-mx-auto tw-px-8 tw-py-4 tw-rounded-lg tw-shadow-[0px_0px_32px_0px_rgba(0,0,0,0.12)] tw-bg-white">
                    <ul class="tw-flex tw-justify-center tw-gap-2 tw-items-center tw-list-none tw-p-0 tw-m-0">
                        @if ($cms_setting->payment_logo1)
                            <li><img class="tw-w-14 tw-h-14 tw-object-contain" src="{{ asset($cms_setting->payment_logo1) }}" alt="payment_logo"></li>
                        @endif
                        @if ($cms_setting->payment_logo2)
                            <li><img class="tw-w-14 tw-h-14 tw-object-contain" src="{{ asset($cms_setting->payment_logo2) }}" alt="payment_logo"></li>
                        @endif
                        @if ($cms_setting->payment_logo3)
                            <li><img class="tw-w-14 tw-h-14 tw-object-contain" src="{{ asset($cms_setting->payment_logo3) }}" alt="payment_logo"></li>
                        @endif
                        @if ($cms_setting->payment_logo4)
                            <li><img class="tw-w-14 tw-h-14 tw-object-contain" src="{{ asset($cms_setting->payment_logo4) }}" alt="payment_logo"></li>
                        @endif
                        @if ($cms_setting->payment_logo5)
                            <li><img class="tw-w-14 tw-h-14 tw-object-contain" src="{{ asset($cms_setting->payment_logo5) }}" alt="payment_logo"></li>
                        @endif
                        @if ($cms_setting->payment_logo6)
                            <li><img class="tw-w-14 tw-h-14 tw-object-contain" src="{{ asset($cms_setting->payment_logo6) }}" alt="payment_logo"></li>
                        @endif
                    </ul>
                </div>
            </div>
        </section>
    @endif

    <section class="tw-py-10">
        <div class="container">
            <div class="tw-flex md:tw-flex-row tw-flex-col tw-gap-5 tw-items-center">
                <div class="tw-w-full tw-rounded-lg tw-flex tw-flex-col tw-gap-3 tw-p-4 tw-shadow-[0px_0px_32px_0px_rgba(0,0,0,0.12)] tw-bg-white">
                    <h4 class="tw-text-xl">{{ __('connect_with_us') }}</h4>
                    <div class="tw-flex tw-flex-wrap tw-h-auto tw-gap-4 tw-items-center tw-min-h-[38px]">
                        <a href="tel:{{ $cms_setting?->footer_phone_no }}" class="tw-inline-flex tw-text-base tw-font-medium tw-text-primary-500 hover:tw-text-primary-700 tw-gap-1.5 tw-items-center">
                            <span class="tw-text-base tw-inline-flex tw-justify-center tw-items-center tw-bg-[#D7E9E9] tw-p-2 tw-rounded-full">
                                <i class="ph-phone"></i>
                            </span>
                            <span>{{ $cms_setting?->footer_phone_no ?? '' }}</span>
                        </a>
                        <a href="mailto:{{ $setting->email }}" class="tw-inline-flex tw-text-base tw-font-medium tw-text-primary-500 hover:tw-text-primary-700 tw-gap-1.5 tw-items-center">
                            <span class="tw-text-base tw-inline-flex tw-justify-center tw-items-center tw-bg-[#D7E9E9] tw-p-2 tw-rounded-full">
                                <i class="ph-arrow-right"></i>
                            </span>
                            <span>{{ $setting->email ?? '' }}</span>
                        </a>
                    </div>
                </div>
                <div class="tw-w-full tw-rounded-lg tw-flex tw-flex-col tw-gap-3 tw-p-4 tw-shadow-[0px_0px_32px_0px_rgba(0,0,0,0.12)] tw-bg-white">
                    <h4 class="tw-text-xl">{{ __('are_you_interested_with_us') }}</h4>
                    <a href="{{ route('register') }}" class="btn tw-bg-primary-500 hover:tw-bg-transparent tw-border tw-border-transparent hover:tw-border-primary-500 hover:tw-text-primary-500 tw-text-white tw-py-2 tw-text-sm">{{ __('register_now') }}</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Subscribe Newsletter --}}
    <x-website.subscribe-newsletter />
@endsection

@section('css')
    <style>
        .breadcrumbs-custom {
            padding: 20px;
            background-color: var(--gray-20);
            transition: all 0.24s ease-in-out;
        }

        .pricing-options {
            display: flex;
            align-items: center;
            gap: 24px;
            margin-top: 48px;
        }

        @media (max-width: 991px) {
            .pricing-options {
                flex-direction: column;
            }
        }

        .pricing-options .choose-pricing h2 {
            font-weight: 500;
            font-size: 24px;
            line-height: 32px;
            color: #18191C;
            margin-bottom: 16px;
        }

        .pricing-options .choose-pricing p {
            font-weight: 400;
            font-size: 16px;
            line-height: 24px;
            color: #5E6670;
            margin-bottom: 16px;
        }

        .pricing-options .choose-pricing a {
            font-weight: 600;
            font-size: 16px;
            line-height: 24px;
            text-align: justify;
            text-transform: capitalize;
            color: #0A65CC;
        }
    </style>
@endsection 