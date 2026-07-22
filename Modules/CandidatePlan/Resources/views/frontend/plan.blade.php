@extends('frontend.layouts.app')

@section('title')
    {{ __('candidate_plan') }}
@endsection

@section('main')
    <div class="dashboard-wrapper">
        <div class="container">
            <div class="row">
                {{-- Sidebar --}}
                <x-website.candidate.sidebar />
                <div class="col-lg-9">
                    <div class="dashboard-right tw-ps-0 lg:tw-ps-5">
                        @if(isset($userplan) && $userplan)
                            <div class="row tw-my-5">
                                <div class="col-lg-5">
                                    <div class="plan-card">
                                        <h2 class="title">{{ $userplan->plan->name }}</h2>
                                        <p class="short-desc">
                                            @if (isset($userplan->plan->description))
                                                {!! $userplan->plan->description !!}
                                            @else
                                                <span class="text-danger">{!! __('no_description_has_been_added_to_this_language', ['current' => $current_language_code]) !!}</span>
                                            @endif
                                        </p>
                                        <div class="plan-status">
                                            {{-- <p class="mb-2">
                                                <strong>{{ __('status') }}:</strong>
                                                @if($userplan->is_active)
                                                    <span class="badge bg-success">{{ __('active') }}</span>
                                                @else
                                                    <span class="badge bg-danger">{{ __('expired') }}</span>
                                                @endif
                                            </p> --}}
                                            {{-- <p class="mb-2">
                                                <strong>{{ __('expires_at') }}:</strong>
                                                {{ $userplan->expire_date ? formatTime($userplan->expire_date, 'M, d Y') : __('unlimited') }}
                                            </p> --}}
                                        </div>
                                        <div class="mt-3">
                                            <a href="{{ route('candidate.plan') }}" class="btn btn-primary">
                                                {{ __('upgrade_plan') }}</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <div class="benefits-card">
                                        <h4 class="title">{{ __('plan_benefits') }}</h4>
                                        <ul class="benefit-list">
                                            <li>
                                                <x-svg.double-check-icon />
                                                <span>{{ $userplan->plan->job_apply_limit }} {{ __('job_apply_limit') }}</span>
                                            </li>
                                            @if($userplan->plan->featured_profile)
                                                <li>
                                                    <x-svg.double-check-icon />
                                                    <span>{{ __('featured_profile') }}</span>
                                                </li>
                                            @endif
                                            @if($userplan->plan->profile_views)
                                                <li>
                                                    <x-svg.double-check-icon />
                                                    <span>{{ __('profile_views') }}</span>
                                                </li>
                                            @endif
                                        </ul>
                                        <div class="remaining">
                                            <h4 class="title">{{ __('remaining') }}</h4>
                                            <ul class="remaining-list">
                                                <li>
                                                    @if (!$userplan->job_apply_limit)
                                                        <x-svg.cross-round2-icon />
                                                    @else
                                                        <x-svg.double-check-icon />
                                                    @endif
                                                    <span>{{ $userplan->job_apply_limit }} {{ __('job_apply_limit') }}</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                {{ __('you_do_not_have_an_active_plan_please_choose_a_plan_to_continue') }}
                                <a href="{{ route('candidate.plan') }}" class="btn btn-primary ms-3">
                                    {{ __('choose_plan') }}
                                </a>
                            </div>
                        @endif

                        <div class="invoices-table">
                            <h2 class="title">{{ __('latest_invoice') }}</h2>
                            <div class="table-wrapper pb-1">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('date') }}</th>
                                            <th>{{ __('plan') }}</th>
                                            <th>{{ __('amount') }}</th>
                                            <th>{{ __('payment_provider') }}</th>
                                            <th>{{ __('payment_status') }}</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if ($transactions->count() > 0)
                                            @foreach ($transactions as $transaction)
                                                <tr>
                                                    <td>#{{ $transaction->id }}</td>
                                                    <td>{{ formatTime($transaction->created_at, 'M, d Y') }}</td>
                                                    <td>
                                                        <span class="badge bg-primary">{{ $transaction->plan->label }}</span>
                                                    </td>
                                                    <td>
                                                        {{ currencyConversion($transaction->amount, 'USD') }}
                                                        {{ currentCurrencyCode() }}
                                                    </td>
                                                    <td>
                                                        {{ ucfirst($transaction->payment_method) }}
                                                    </td>
                                                    <td>
                                                        @if ($transaction->payment_status == 'paid')
                                                            <span class="badge badge-pill bg-success">{{ __('paid') }}</span>
                                                        @else
                                                            <span class="badge badge-pill bg-success">{{ __('paid') }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="tw-inline-flex tw-gap-2 tw-items-center">
                                                            <form action="{{ route('candidate.transaction.invoice.download', $transaction->id) }}" method="POST">
                                                                @csrf
                                                                <button type="submit" class="btn tw-p-0">
                                                                    <x-svg.download-icon />
                                                                </button>
                                                            </form>
                                                            <a href="{{ route('candidate.transaction.invoice.view', $transaction->id) }}" target="_blank">
                                                                {{ __('view_invoice') }}
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <x-website.not-found />
                                        @endif
                                    </tbody>
                                </table>
                                @if (request('perpage') != 'all' && $transactions->total() > $transactions->count())
                                    <div class="rt-pt-30 mb-3">
                                        <nav>
                                            {{ $transactions->onEachSide(0)->links('vendor.pagination.frontend') }}
                                        </nav>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
