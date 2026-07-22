@extends('frontend.layouts.app')

@section('title')
    {{ $resource['title'] }}
@endsection

@section('main')
    <div class="breadcrumbs breadcrumbs-height">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                <div class="breadcrumb-menu">
                    <h6 class="f-size-18 m-0">{{ $resource['title'] }}</h6>
                                        <ul>
                        <li><a href="{{ route('website.home') }}">Home</a></li>
                        <li>/</li>
                        <li><a href="{{ route('website.career.resources') }}">Career Resources</a></li>
                        <li>/</li>
                        <li>{{ $resource['title'] }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <section class="py-5 bg-light">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-lg-7">
                    <h2 class="rt-mb-16">{{ $resource['subtitle'] }}</h2>
                    <p class="body-font-3 text-gray-600">{{ $resource['description'] }}</p>
                    <div class="d-flex flex-wrap gap-3 rt-mt-24">
                        <a href="{{ route('website.job') }}" class="btn btn-primary">Browse Jobs</a>
                        <a href="{{ route('website.posts') }}" class="btn btn-outline-primary">Read Career Blog</a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <img class="w-100 rt-rounded-8" src="{{ asset($resource['image']) }}" alt="{{ $resource['title'] }}">
                </div>
            </div>

            <div class="row g-4 rt-mt-16">
                @foreach ($resource['highlights'] as $highlight)
                    <div class="col-md-6 col-lg-4">
                        <div class="card jobcardStyle1 h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start">
                                    <div class="icon-40 bg-primary-50 text-primary-500 rounded-circle d-flex align-items-center justify-content-center rt-mr-12">
                                        <i class="{{ $highlight['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <h5 class="f-size-18 ft-wt-5 rt-mb-8">{{ $highlight['title'] }}</h5>
                                        <p class="body-font-4 text-gray-600 rt-mb-0">{{ $highlight['text'] }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row g-4 rt-mt-24">
                <div class="col-lg-8">
                    <div class="card jobcardStyle1">
                        <div class="card-body p-4">
                            <h4 class="rt-mb-20">Actionable Tips</h4>
                            <ol class="mb-0 ps-3">
                                @foreach ($resource['tips'] as $tip)
                                    <li class="body-font-3 text-gray-700 rt-mb-12">{{ $tip }}</li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card jobcardStyle1 h-100">
                        <div class="card-body p-4">
                            <h5 class="ft-wt-5 rt-mb-16">More Resources</h5>
                            <ul class="list-unstyled rt-mb-0">
                                @foreach ($resourceLinks as $link)
                                    <li class="rt-mb-10">
                                        <a class="d-flex align-items-center justify-content-between" href="{{ $link['url'] }}">
                                            <span>{{ $link['title'] }}</span><i class="ph-arrow-right"></i>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
