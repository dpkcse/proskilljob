@extends('frontend.layouts.app')

@section('description')
    @php
        $data = metaData('contact');
    @endphp
    {{ $data->description }}
@endsection
@section('og:image')
    {{ asset($data->image) }}
@endsection
@section('title')
    {{ $data->title }}
@endsection

@section('main')
    <div class="breadcrumbs-custom breadcrumbs-height">
        <div class="container">
            <div class="breadcrumb-menu">
                <h6 class="f-size-18 m-0">{{ __('contact') }}</h6>
                <ul>
                    <li><a href="{{ route('website.home') }}">{{ __('home') }}</a></li>
                    <li>/</li>
                    <li>{{ __('contact') }}</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="rt-contact">
        <div class="container">
            <div class="rt-spacer-100 rt-spacer-md-50"></div>
            <div class="row align-items-center">
                <div class="col-xl-6 col-lg-6 rt-mb-lg-30 ">
                    <div class="pl30">
                        <span
                            class="body-font-3 ft-wt-5 text-primary-500 rt-mb-15 d-inline-block">{{ __('contact_title') }}</span>
                        <h2 class="lg:tw-mb-8 md:tw-mb-6 tw-mb-4">{{ __('we_care_about_customer_services') }}</h2>
                        <p class="body-font-2 text-gray-500 rt-mb-32">
                            {{ __('want_to_chat_We_love_to_hear_from_you_get_in_touch_with_our_customer_success_team_to_inquire_rates_or_just_say_hello') }}
                        </p>
                        
                        <!-- Contact Information Cards -->
                        <div class="row rt-mb-32">
                            <div class="col-md-12">
                                <div class="contact-info-card bg-white p-4 rounded-3 shadow-sm mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="contact-icon me-3">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="#0066CC"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 text-dark fw-semibold">{{ __('address') }}</h6>
                                            <p class="mb-0 text-gray-600 small">{{ $cms_setting?->footer_address ?? __('footer_address') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="contact-info-card bg-white p-4 rounded-3 shadow-sm mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="contact-icon me-3">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M22 12C22 17.52 17.52 22 12 22C6.48 22 2 17.52 2 12C2 6.48 6.48 2 12 2C17.52 2 22 6.48 22 12Z" stroke="#0066CC" stroke-width="1.5"/>
                                                <path d="M8 12H16M12 8V16" stroke="#0066CC" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 text-dark fw-semibold">{{ __('phone') }}</h6>
                                            <p class="mb-0 text-gray-600 small">
                                                <a href="tel:{{ $cms_setting?->footer_phone_no ?? $setting->phone }}" class="text-decoration-none text-gray-600">
                                                    {{ $cms_setting?->footer_phone_no ?? $setting->phone ?? __('call_us') }}
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="contact-info-card bg-white p-4 rounded-3 shadow-sm mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="contact-icon me-3">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M4 7L10.94 13.06C11.65 13.77 12.35 13.77 13.06 13.06L20 7" stroke="#0066CC" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M3 7L10.94 13.06C11.65 13.77 12.35 13.77 13.06 13.06L21 7" stroke="#0066CC" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M3 7H21V18C21 19.1 20.1 20 19 20H5C3.9 20 3 19.1 3 18V7Z" stroke="#0066CC" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 text-dark fw-semibold">{{ __('email') }}</h6>
                                            <p class="mb-0 text-gray-600 small">
                                                <a href="mailto:{{ $setting->email }}" class="text-decoration-none text-gray-600">
                                                    {{ $setting->email }}
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-lg-6">
                    <div class="contact-auth-box ct-wrap">
                        <form action="{{ route('module.contact.store') }}" class="rt-form" method="POST">
                            @csrf
                            <h5 class="rt-mb-32">{{ __('get_in_touch') }}</h5>
                            <div class="row">
                                <div class="col-xl-6 col-lg-6">
                                    <div class="fromGroup rt-mb-15">
                                        <input id="name" class=" form-control @error('name') is-invalid @enderror"
                                            type="text" placeholder="{{ __('name') }}" name="name"
                                            value="{{ old('name') }}">
                                        @error('name')
                                            <span class="invalid-feedback"
                                                role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6">
                                    <div class="fromGroup rt-mb-15">
                                        <input id="email" class="form-control @error('email') is-invalid @enderror"
                                            type="email" placeholder="{{ __('email') }}" name="email"
                                            value="{{ old('email') }}">
                                        @error('email')
                                            <span class="invalid-feedback"
                                                role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="fromGroup rt-mb-15">
                                <input id="subject" class="form-control @error('subject') is-invalid @enderror"
                                    type="text" placeholder="{{ __('subjects') }}" name="subject"
                                    value="{{ old('subject') }}">
                                @error('subject')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="rt-mb-15 tarea-dafault">
                                <textarea id="message" class="form-control @error('message') is-invalid @enderror" type="text"
                                    placeholder="{{ __('message') }}" name="message">{{ old('message') }}</textarea>
                                @error('message')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            @if (config('captcha.active'))
                                <div class="rt-mb-10 tarea-dafault g-custom-css">
                                    {!! NoCaptcha::display() !!}
                                    @error('g-recaptcha-response')
                                        <span class="text-danger">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            @endif
                            <button type="submit" class="btn btn-primary d-block rt-mb-15" id="submitButton">
                                <span class="button-content-wrapper ">
                                    <span class="button-icon align-icon-right">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path d="M22 2L11 13" stroke="white" stroke-width="1.5" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                            <path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="white" stroke-width="1.5"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>

                                    </span>
                                    <span class="button-text rt-mr-8">
                                        {{ __('send_message') }}
                                    </span>
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="rt-spacer-100 rt-spacer-md-50"></div>
        </div>
    </div>

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
        
        .contact-info-card {
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .contact-info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
            border-color: #0066CC;
        }
        
        .contact-icon {
            width: 48px;
            height: 48px;
            background: rgba(0, 102, 204, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .contact-info-card h6 {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
        }
        
        .contact-info-card p {
            font-size: 13px;
            line-height: 1.4;
        }
        
        .contact-info-card a:hover {
            color: #0066CC !important;
        }
        
        @media (max-width: 768px) {
            .contact-info-card {
                margin-bottom: 15px;
            }
            
            .contact-icon {
                width: 40px;
                height: 40px;
            }
            
            .contact-icon svg {
                width: 20px;
                height: 20px;
            }
        }
    </style>
@endsection
@section('script')
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <script>
        $(document).ready(function() {
            validate();
            $('#name, #email, #subject, #message').keyup(validate);
        });

        function validate() {
            if (
                $('#name').val().length > 0 &&
                $('#email').val().length > 0 &&
                $('#subject').val().length > 0 &&
                $('#message').val().length > 0
            ) {
                $('#submitButton').attr('disabled', false);
            } else {
                $('#submitButton').attr('disabled', true);
            }
        }
    </script>
@endsection
