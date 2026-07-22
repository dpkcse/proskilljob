@extends('frontend.layouts.app')

@section('title')
    {{ __('post_job') }}
@endsection

@section('main')
    <div class="dashboard-wrapper">
        <div class="container">
            <div class="row">
                {{-- Sidebar --}}
                <x-website.company.sidebar />
                <div class="col-lg-9">
                    <div class="dashboard-right tw-ps-0 lg:tw-ps-5">
                        <div class="dashboard-right-header">
                            <span class="sidebar-open-nav">
                                <i class="ph-list"></i>
                            </span>
                        </div>
                        <h2 class="tw-text-2xl tw-font-medium tw-text-[#18191C] tw-mb-8">
                            {{ __('post_a_job') }}
                        </h2>
                        <form action="{{ route('company.job.store') }}" method="POST" class="rt-from">
                            @csrf
                            <input type="hidden" name="location" data-map-location-field="location" value="{{ session('location') ? 1 : '' }}">
                            <input type="hidden" name="lat" data-map-location-field="lat" value="{{ session('location.lat') }}">
                            <input type="hidden" name="lng" data-map-location-field="lng" value="{{ session('location.lng') }}">
                            <input type="hidden" name="country" data-map-location-field="country" value="{{ session('location.country') }}">
                            <input type="hidden" name="region" data-map-location-field="region" value="{{ session('location.region') }}">
                            <input type="hidden" name="district" data-map-location-field="district" value="{{ session('location.district') }}">
                            <input type="hidden" name="place" data-map-location-field="place" value="{{ session('location.place') }}">
                            <input type="hidden" name="exact_location" data-map-location-field="exact_location" value="{{ session('location.exact_location') }}">
                            <div class="post-job-item rt-mb-15 tw-w-full tw-overflow-hidden">
                                <div class="row">
                                    <div class="col-lg-8 rt-mb-20">
                                        <x-forms.label name="job_title" :required="true" class="tw-text-sm tw-mb-2" />
                                        <input value="{{ old('title') }}" name="title"
                                            class="form-control @error('title') is-invalid @enderror" type="text"
                                            placeholder="{{ __('job_title') }}" id="m">
                                        @error('title')
                                            <span class="error invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-4 rt-mb-20 col-md-4">
                                        <x-forms.label name="job_category" :required="true" class="tw-text-sm tw-mb-2" />
                                        <select
                                            class=" select2-taggable select2-search form-control @error('category_id') is-invalid @enderror"
                                            name="category_id">
                                            @foreach ($jobCategories as $category)
                                                <option {{ old('category_id') == $category->id ? 'selected' : '' }}
                                                    value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('category_id')
                                            <span class="error invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-8 rt-mb-20 col-md-8">
                                        <x-forms.label name="tags" :required="false" class="tw-text-sm tw-mb-2">
                                            ({{ __('saerch_or_write_tag_and_hit_enter') }})
                                        </x-forms.label>

                                        <select
                                            class=" rt-selectactive select2-taggable form-control @error('tags') is-invalid @enderror"
                                            name="tags[]" multiple>
                                            @foreach ($tags as $tag)
                                                <option
                                                    {{ old('tags') ? (in_array($tag->id, old('tags')) ? 'selected' : '') : '' }}
                                                    value="{{ $tag->id }}">{{ $tag->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('tags')
                                            <span class="error invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-4 rt-mb-20 col-md-4">
                                        <x-forms.label name="job_role" :required="true" class="tw-text-sm tw-mb-2" />
                                        <select
                                            class=" select2-taggable select2-search form-control @error('role_id') is-invalid @enderror"
                                            name="role_id">
                                            @foreach ($roles as $role)
                                                <option {{ old('role_id') == $role->id ? 'selected' : '' }}
                                                    value="{{ $role->id }}">{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('role_id')
                                            <span class="error invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-4 rt-mb-20 col-md-4">
                                        <x-forms.label name="profession" :required="true" class="tw-text-sm tw-mb-2" />
                                        <select class="select2-taggable select2-search form-control @error('profession_id') is-invalid @enderror" name="profession_id">
                                            <option value="">{{ __('select_profession') }}</option>
                                            @foreach ($professions as $profession)
                                                <option {{ old('profession_id') == $profession->id ? 'selected' : '' }} value="{{ $profession->id }}">{{ $profession->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('profession_id')
                                            <span class="error invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="post-job-item rt-mb-15 tw-w-full tw-overflow-hidden">
                                <h4 class="f-size-18 ft-wt-5 rt-mb-20 lh-1">{{ __('salary') }}</h4>
                                <div class="tw-flex tw-gap-5 mb-3">
                                    <div
                                        class="ll-radio tw-flex tw-items-center tw-border tw-border-gray-200 tw-rounded tw-ps-1">
                                        <input checked onclick="salaryModeChange('range')" id="salary_rangee" type="radio"
                                            value="range" name="salary_mode" class="tw-scale-150">
                                        <label for="salary_rangee"
                                            class="tw-w-full tw-py-4 tw-ms-2 tw-text-sm tw-font-medium">{{ __('salary_range') }}</label>
                                    </div>
                                    <div
                                        class="ll-radio tw-flex tw-items-center tw-border tw-border-gray-200 tw-rounded tw-ps-1">
                                        <input onclick="salaryModeChange('custom')" id="custom_salary" type="radio"
                                            value="custom" name="salary_mode" class="tw-scale-150">
                                        <label for="custom_salary"
                                            class="tw-w-full tw-py-4 tw-ms-2 tw-text-sm tw-font-medium">{{ __('custom_salary') }}</label>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="rt-mb-20 col-md-8 d-none" id="custom_salary_part">
                                        <x-forms.label name="custom_salary" :required="true" class="tw-text-sm tw-mb-2" />
                                        <div class="position-relative">
                                            <input value="{{ old('custom_salary', '') }}" name="custom_salary"
                                                class="form-control @error('custom_salary') is-invalid @enderror"
                                                type="text" placeholder="{{ __('custom_salary') }}" id="m">
                                            @error('custom_salary')
                                                <span class="error invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="rt-mb-20 col-md-4 salary_range_part">
                                        <x-forms.label name="min_salary" :required="false" class="tw-text-sm tw-mb-2" />
                                        <div class="position-relative">
                                            <input step="0.01" value="{{ old('min_salary', '50.00') }}"
                                                class="form-control @error('min_salary') is-invalid @enderror"
                                                name="min_salary" type="number" placeholder="{{ __('min_salary') }}"
                                                id="m">
                                            <div class="usd">{{ $currency_symbol }}</div>
                                            @error('min_salary')
                                                <span class="error invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="rt-mb-20 col-md-4 salary_range_part">
                                        <x-forms.label name="max_salary" :required="false" class="tw-text-sm tw-mb-2" />
                                        <div class="position-relative">
                                            <input step="0.01" value="{{ old('max_salary', '100.00') }}"
                                                class="form-control @error('max_salary') is-invalid @enderror"
                                                name="max_salary" type="number" placeholder="{{ __('max_salary') }}"
                                                id="m">
                                            <div class="usd">{{ $currency_symbol }}</div>
                                            @error('max_salary')
                                                <span class="error invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-4 rt-mb-20 col-md-6">
                                        <x-forms.label name="{{ __('salary_type') }}" :required="true"
                                            class="tw-text-sm tw-mb-2" />
                                        <select
                                            class="rt-selectactive form-control @error('salary_type') is-invalid @enderror "
                                            name="salary_type">
                                            @foreach ($salary_types as $type)
                                                <option {{ old('salary_type') == $type->id ? 'selected' : '' }}
                                                    value="{{ $type->id }}">
                                                    {{ $type->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('salary_type')
                                            <span class="error invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>


                            <div class="post-job-item rt-mb-15 tw-w-full">
                                <h4 class="f-size-18 ft-wt-5 rt-mb-20 lh-1">{{ __('advance_information') }}</h4>
                                <div class="row">
                                    <div class="col-lg-4 col-md-6 rt-mb-20">
                                        <x-forms.label name="education" :required="true" class="tw-text-sm tw-mb-2" />
                                        <select
                                            class="select2-taggable form-control @error('education') is-invalid @enderror "
                                            name="education">
                                            @foreach ($educations as $education)
                                                <option {{ old('education') == $education->id ? 'selected' : '' }}
                                                    value="{{ $education->id }}">
                                                    {{ $education->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('education')
                                            <span class="error invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-4 col-md-6 rt-mb-20">
                                        <x-forms.label name="experience" :required="true" class="tw-text-sm tw-mb-2" />
                                        <select
                                            class="select2-taggable form-control @error('experience') is-invalid @enderror "
                                            name="experience">
                                            @foreach ($experiences as $experience)
                                                <option {{ old('experience') == $experience->id ? 'selected' : '' }}
                                                    value="{{ $experience->id }}">
                                                    {{ $experience->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('experience')
                                            <span class="error invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-4 col-md-6 rt-mb-20">
                                        <x-forms.label name="job_type" :required="true" class="tw-text-sm tw-mb-2" />
                                        <select
                                            class="rt-selectactive form-control @error('job_type') is-invalid @enderror "
                                            name="job_type">
                                            @foreach ($job_types as $job_type)
                                                <option {{ old('job_type') == $job_type->id ? 'selected' : '' }}
                                                    value="{{ $job_type->id }}">
                                                    {{ $job_type->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('job_type')
                                            <span class="error invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-4 col-md-6 rt-mb-20">
                                        <x-forms.label name="required_experience_type_area" :required="false" class="tw-text-sm tw-mb-2" />
                                        <input type="text" name="experience_area" value="{{ old('experience_area') }}"
                                            class="form-control @error('experience_area') is-invalid @enderror"
                                            placeholder="{{ __('required_experience_type_area') }}">
                                    </div>
                                    <div class="col-lg-4 col-md-6 rt-mb-20">
                                        <x-forms.label name="business_area_industry" :required="false" class="tw-text-sm tw-mb-2" />
                                        <select class="rt-selectactive form-control @error('business_area') is-invalid @enderror" name="business_area">
                                            <option value="">{{ __('select_one') }}</option>
                                            <option value="sales" @selected(old('business_area')==='sales')>{{ __('sales') }}</option>
                                            <option value="marketing" @selected(old('business_area')==='marketing')>{{ __('marketing') }}</option>
                                            <option value="it" @selected(old('business_area')==='it')>{{ __('it') }}</option>
                                            <option value="education" @selected(old('business_area')==='education')>{{ __('education') }}</option>
                                            <option value="healthcare" @selected(old('business_area')==='healthcare')>{{ __('healthcare') }}</option>
                                            <option value="garments_textile" @selected(old('business_area')==='garments_textile')>{{ __('garments_textile') }}</option>
                                            <option value="finance_banking" @selected(old('business_area')==='finance_banking')>{{ __('finance_banking') }}</option>
                                            <option value="customer_support" @selected(old('business_area')==='customer_support')>{{ __('customer_support') }}</option>
                                            <option value="others" @selected(old('business_area')==='others')>{{ __('others') }}</option>
                                        </select>
                                        <input type="text" name="business_area_other" value="{{ old('business_area_other') }}"
                                            class="form-control mt-2 @error('business_area_other') is-invalid @enderror"
                                            placeholder="{{ __('please_specify') }}">
                                    </div>
                                    <div class="col-lg-12 col-md-12 rt-mb-20">
                                        <x-forms.label name="experience_description" :required="false" class="tw-text-sm tw-mb-2" />
                                        <textarea name="experience_description" rows="3" class="form-control @error('experience_description') is-invalid @enderror"
                                            placeholder="{{ __('experience_description') }}">{{ old('experience_description') }}</textarea>
                                    </div>
                                    <div class="col-lg-8 col-md-12 rt-mb-20">
                                        <x-forms.label name="required_degrees" :required="false" class="tw-text-sm tw-mb-2" />
                                        <select name="required_degrees[]" multiple class="select2-taggable form-control @error('required_degrees') is-invalid @enderror">
                                            @foreach (['ba','bsc','bba','b_com','ma','msc','mba','diploma','hsc','ssc','others'] as $deg)
                                                <option value="{{ $deg }}" @selected(collect(old('required_degrees', []))->contains($deg))>{{ __($deg) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-4 col-md-12 rt-mb-20">
                                        <x-forms.label name="other_degree" :required="false" class="tw-text-sm tw-mb-2" />
                                        <input type="text" name="required_degrees_other" value="{{ old('required_degrees_other') }}"
                                            class="form-control @error('required_degrees_other') is-invalid @enderror" placeholder="{{ __('please_specify') }}">
                                    </div>
                                    <div class="col-lg-12 col-md-12 rt-mb-20">
                                        <x-forms.label name="preferred_educational_institution" :required="false" class="tw-text-sm tw-mb-2" />
                                        <select name="preferred_institutions[]" multiple class="select2tags form-control @error('preferred_institutions') is-invalid @enderror">
                                            @foreach (old('preferred_institutions', []) as $inst)
                                                <option value="{{ $inst }}" selected>{{ $inst }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-4 col-md-6 rt-mb-20">
                                        <x-forms.label name="gender" :required="false" class="tw-text-sm tw-mb-2" />
                                        <select class="rt-selectactive form-control @error('gender') is-invalid @enderror " name="gender">
                                            <option value="any" {{ old('gender', 'any') == 'any' ? 'selected' : '' }}>{{ __('any') }}</option>
                                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>{{ __('male') }}</option>
                                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>{{ __('female') }}</option>
                                        </select>
                                        @error('gender')
                                            <span class="error invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-6 col-md-6 rt-mb-20">
                                        <x-forms.label name="vacancies" :required="true" class="tw-text-sm tw-mb-2" />
                                        <input value="{{ old('vacancies', 1) }}" name="vacancies" type="text"
                                            placeholder="{{ __('vacancies') }}"
                                            class="form-control @error('vacancies') is-invalid @enderror" id="vacancies">
                                        @error('vacancies')
                                            <span class="error invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-3 col-md-6 rt-mb-20">
                                        <x-forms.label name="min_age" :required="false" class="tw-text-sm tw-mb-2" />
                                        <input value="{{ old('min_age') }}" name="min_age" type="number" min="0" max="100"
                                            placeholder="{{ __('min_age') }}"
                                            class="form-control @error('min_age') is-invalid @enderror">
                                        @error('min_age')
                                            <span class="error invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-3 col-md-6 rt-mb-20">
                                        <x-forms.label name="max_age" :required="false" class="tw-text-sm tw-mb-2" />
                                        <input value="{{ old('max_age') }}" name="max_age" type="number" min="0" max="100"
                                            placeholder="{{ __('max_age') }}"
                                            class="form-control @error('max_age') is-invalid @enderror">
                                        @error('max_age')
                                            <span class="error invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-6 col-md-6 rt-mb-20">
                                        <x-forms.label name="deadline_expired" :required="true"
                                            class="tw-text-sm tw-mb-2" />
                                        <div class="fromGroup">
                                            <div class="form-control-icon date datepicker">
                                                <input name="deadline"
                                                    class="form-control @error('deadline') is-invalid @enderror"
                                                    type="text" value="{{ old('deadline') ? old('deadline') : '' }}"
                                                    id="date" placeholder="{{ __('dd-mm-yyyy') }}"  >
                                                <span class="input-group-addon has-badge">
                                                    <span @error('deadline') rt-mr-12 @enderror>
                                                        <x-svg.calendar-icon />
                                                    </span>
                                                </span>
                                                @error('deadline')
                                                    <span class="error invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="tw-text-sm tw-font-medium tw-text-red-500">
                                            {{ __('maximum_deadline_limit') }}:
                                            {{ $setting->job_deadline_expiration_limit }} {{ __('days') }}
                                        </div>
                                    </div>
                                    <div class="col-lg-12 col-md-12 rt-mb-20">
                                        <x-forms.label name="{!! __('job_start_end_title') !!}" :required="false" class="tw-text-sm tw-mb-2" />
                                        <div class="fromGroup">
                                            <div class="form-control-icon date datepicker">
                                                <input id="job_period" type="text" name="job_period"
                                                    class="form-control @error('job_start') is-invalid @enderror @error('job_end') is-invalid @enderror"
                                                    value="{{ old('job_start') && old('job_end') ? old('job_start') . ' - ' . old('job_end') : '' }}"  autocomplete="off"
                                                    placeholder="{{ __('job_start_end_title') }}">
                                                <input type="hidden" id="job_start" name="job_start" value="{{ old('job_start') }}">
                                                <input type="hidden" id="job_end" name="job_end" value="{{ old('job_end') }}">
                                                <span class="input-group-addon has-badge">
                                                    <span>
                                                        <x-svg.calendar-icon />
                                                    </span>
                                                </span>
                                                @error('job_start')
                                                    <span class="error invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                                @error('job_end')
                                                    <span class="error invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                @if (config('templatecookie.map_show'))
                                    <div class="col-12 rt-mb-15">
                                        @php
                                            $map = $setting->default_map;
                                        @endphp
                                        <div class="location-wrapper">
                                            <div class="row">
                                                <div class="col-12">
                                                    <h2>
                                                        {{ __('location') }} <span class="text-danger">*</span>
                                                        <small class="h6">
                                                            ({{ __('click_to_add_a_pointer') }})
                                                        </small>
                                                    </h2>
                                                </div>
                                                <div class="col-md-12 col-sm-12 rt-mb-24">
                                                    <x-website.map.map-warning />

                                                    <div id="google-map-div"
                                                        class="{{ $map == 'google-map' ? '' : 'd-none' }}">
                                                        <input id="searchInput" class="mapClass" type="text"
                                                            placeholder="{{ __('enter_location') }}">
                                                        <div class="map mymap" id="google-map"></div>
                                                    </div>
                                                    <div class="{{ $map == 'leaflet' ? '' : 'd-none' }}">
                                                        <input type="text" autocomplete="off" id="leaflet_search"
                                                            placeholder="{{ __('enter_city_name') }}"
                                                            class="full-width" />
                                                        <br>
                                                        <div id="leaflet-map"></div>
                                                    </div>
                                                    @error('location')
                                                        <span class="ml-3 text-md text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-12 mt-4 custom-checkbox-wrap">
                                                    <label class="main tw-text-sm"
                                                        for="remoteWork">{{ __('fully_remote_position') }}-<span
                                                            class="tw-font-medium">{{ __('worldwide') }}</span>
                                                        <input type="checkbox" name="is_remote" id="remoteWork"
                                                            value="1" {{ old('is_remote') ? 'checked' : '' }}>
                                                        <span class="custom-checkbox"></span>
                                                    </label>
                                                    <input type="checkbox" name="is_remote" id="remoteWork"
                                                        value="1" {{ old('is_remote') ? 'checked' : '' }}>
                                                </div>

                                                <div class="col-12 mt-4">
                                                    @php
                                                        $session_location = session()->get('location');
                                                        $session_country = $session_location && array_key_exists('country', $session_location) ? $session_location['country'] : '-';
                                                        $session_exact_location = $session_location && array_key_exists('exact_location', $session_location) ? $session_location['exact_location'] : '-';
                                                    @endphp
                                                    <div class="card-footer row mt-4 border-0">
                                                        <span>
                                                            <img src="{{ asset('frontend/assets/images/loader.gif') }}"
                                                                alt="loading" width="50px" height="50px"
                                                                class="loader_position d-none">
                                                        </span>
                                                        <div class="location_secion">
                                                            {{ __('country') }}: <span
                                                                class="location_country">{{ $session_country }}</span>
                                                            <br />
                                                            {{ __('full_address') }}: <span
                                                                class="location_full_address">{{ $session_exact_location }}</span>
                                                        </div>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <x-forms.label name="location" :required="true" class="tw-text-sm tw-mb-2" />
                                    <div class="card-body pt-0">
                                        <div>
                                            @livewire('country-state-city', ['row' => true])
                                            @error('location')
                                                <span class="ml-3 text-md text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="post-job-item rt-mb-32 tw-w-full tw-overflow-hidden">
                                <h4 class="f-size-18 ft-wt-5 rt-mb-20 lh-1">{{ __('benefits') }}</h4>
                                <div class="benefits-tags" id="benefit_list">
                                    @foreach ($benefits as $benefit)
                                        <label for="benefit_{{ $benefit->id }}">
                                            <input
                                                {{ old('benefits') ? (in_array($benefit->id, old('benefits')) ? 'checked' : '') : '' }}
                                                type="checkbox" id="benefit_{{ $benefit->id }}" name="benefits[]"
                                                value="{{ $benefit->id }}">
                                            <span>{{ $benefit->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('benefits')
                                    <span class="error invalid-feedback d-block">{{ $message }}</span>
                                @enderror

                                <div class="mt-3">
                                    <a onclick="showHideCreateBenefit()" href="javascript:void(0)"
                                        class="text-decoration-underline">{{ __('create_new') }} {{ __('benefit') }}</a>

                                    <div class="d-flex tw-justify-between tw-gap-2 mt-3 d-none" id="create_benefit">
                                        <input value="{{ old('title') }}" name="new_benefit"
                                            class="form-control @error('title') is-invalid @enderror" type="text"
                                            placeholder="{{ __('benefit') }}" id="m">

                                        <button onclick="createBenefit()" type="button"
                                            class="btn btn-primary rt-mr-10">
                                            <span class="button-content-wrapper ">
                                                <span class="button-text">
                                                    {{ __('create') }} {{ __('benefit') }}
                                                </span>
                                                <span class="button-icon align-icon-right">
                                                    <i class="ph ph-plus"></i>
                                                </span>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 rt-mb-20 ">
                                <x-forms.label name="skills" :required="false" />
                                <select id="skills" name="skills[]"
                                    class="select2-taggable form-control @error('skills') is-invalid @enderror" multiple>
                                    @foreach ($skills as $skill)
                                        <option
                                            {{ old('skills') ? (in_array($skill->id, old('skills')) ? 'selected' : '') : '' }}
                                            value="{{ $skill->id }}">{{ $skill->name }}</option>
                                    @endforeach
                                </select>
                                @error('skills')
                                    <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
                                @enderror
                            </div>
                            <div class="post-job-item rt-mb-32 tw-w-full tw-overflow-hidden">
                                <h4 class="f-size-18 ft-wt-5 tw-mb-3 lh-1">
                                    {{ __('job_description') }}
                                    <span class="form-label-required text-danger">*</span>
                                </h4>
                                <div class="col-md-12">
                                    <textarea id="image_ckeditorx" class="form-control @error('description') is-invalid @enderror" name="description">{{ old('description') }}
                                    </textarea>
                                    @error('description')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Additional Questions --}}
                            @if (currentCompany()->question_feature_enable)
                                <div x-data="appQuestion()" x-init="select2Alpine"
                                    class="post-job-item rt-mb-15 tw-w-full tw-overflow-hidden ">
                                    <h4 class="f-size-18 ft-wt-5 rt-mb-20 lh-1">{{ __('add_screening_questions') }}</h4>
                                    <div class="row">
                                        <div class="rt-mb-20">
                                            <div class="col-lg-12">
                                                <div x-show="isAddingNewQuestion" class="tw-flex justify-content-between">
                                                    <label class="tw-text-sm tw-mb-2 mb-2" for="for">
                                                        {{ __('create_new_screening_question') }}
                                                    </label>
                                                    <a x-show="isAddingNewQuestion" href="#"
                                                        @click.prevent="isAddingNewQuestion = false">
                                                        {{ __('choose_from_existing_question') }}
                                                    </a>
                                                </div>
                                                <div x-show="!isAddingNewQuestion"
                                                    class="tw-flex justify-content-between">
                                                    <label class="tw-text-sm tw-mb-2 mb-2" for="for">
                                                        {{ __('choose_from_existing_question') }}
                                                    </label>
                                                    <a href="#" x-show="!isAddingNewQuestion"
                                                        @click.prevent="isAddingNewQuestion = true"
                                                        href="#">{{ __('create_new_screening_question') }}</a>
                                                </div>
                                                <input x-show="isAddingNewQuestion" value="" x-model="newQuestion"
                                                    class="form-control " type="text" placeholder="Add Question">
                                            </div>
                                            <div x-show="isAddingNewQuestion"
                                                class="tw-flex tw-gap-5 mb-3 flex justify-content-between tw-mt-4">
                                                <div class="tw-flex justify-between ">
                                                    <div
                                                        class="ll-radio tw-flex tw-items-center tw-border tw-border-gray-200 tw-rounded tw-ps-1 tw-mr-4">
                                                        <label class="mt-2">
                                                            <input x-model="newQuestionSave" class="tw-scale-150"
                                                                type="checkbox" style="margin-right: 10px">
                                                            {{ __('save_for_letter') }}
                                                        </label>
                                                    </div>
                                                    <div
                                                        class="ll-radio tw-flex tw-items-center tw-border tw-border-gray-200 tw-rounded tw-ps-1">
                                                        <label class="mt-2">
                                                            <input x-model="isRequired" class="tw-scale-150"
                                                                type="checkbox" style="margin-right: 10px">
                                                            {{ __('candidate_must_answer') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <div>
                                                    <button @click.prevent="addQuestion" type="button"
                                                        class="btn btn-primary"> {{ __('save') }} </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div x-show="isAddingNewQuestion == false" class="q-select">
                                        <select id="questionSelect" multiple="multiple" x-ref="select"
                                            data-placeholder="{{ __('select_questions') }}" name="companyQuestions[]" class="select2-taggable form-control">
                                            <option></option>
                                            @foreach ($questions as $question)
                                                <option value="{{ $question->id }}"> {{ $question->title }}
                                                    {{ $question->required ? '(required)' : '' }} </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div x-show="selectedQuestions.length">
                                        <h4 class="f-size-18 ft-wt-5 rt-mb-20 lh-1 mt-4">
                                            {{ __('selected_screening_questions') }}</h4>
                                        <ul>
                                            <template x-for="question in selectedQuestions">
                                                <div class="tw-flex justify-content-between my-2">
                                                    <li class="flex-grow-1"
                                                        x-text="question.required  ? question.title+' (required)' : question.title ">
                                                    </li>
                                                    <div class="cursor-pointer f" style="color:red;">
                                                        <svg @click="remove(question.id)" width="20" height="20"
                                                            xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                            class="w-6 h-6">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>

                                                    </div>
                                                </div>
                                            </template>
                                        </ul>
                                    </div>
                                </div>
                            @else
                                <!-- Question feature is DISABLED -->
                                <!-- Company ID: {{ currentCompany()->id }} -->
                                <!-- Question feature enable: {{ currentCompany()->question_feature_enable }} -->
                                <div class="alert alert-info">
                                    <strong>Question Feature Disabled:</strong> The screening questions feature is not enabled for your company.
                                </div>
                            @endif

                            <div class="row tw-mb-8">
                                <div class="col-12">
                                    <div class="applied-job-on">
                                        <div class="row">
                                            <h2>{{ __('apply_job_on') }}:</h2>
                                            <!-- apply_on -->
                                            <div id="applied_on_app"
                                                class="radio-check col-lg-4 d-flex {{ old('apply_on') === 'app' ? 'checked' : '' }}"
                                                onclick="RadioChecked('app')">
                                                <input type="radio" {{ old('apply_on') === 'app' ? 'checked' : '' }}
                                                    checked name="apply_on" value="app" id="app-app">
                                                <label for="app-app">
                                                    <h4 class="d-inline-block">{{ __('on_appname', ['appname' => config('app.name')]) }}</h4>
                                                    <p class="tw-mb-0">{{ __('candidate_will_apply_job_using') }}
                                                        {{ config('app.name') }} &
                                                        {{ __('all_application_will_show_on_your_dashboard') }}.</p>
                                                </label>
                                            </div>
                                            <div id="applied_on_custom_url"
                                                class="radio-check col-lg-4 d-flex {{ old('apply_on') === 'custom_url' ? 'checked' : '' }}"
                                                onclick="RadioChecked('custom_url')">
                                                <input type="radio"
                                                    {{ old('apply_on') === 'custom_url' ? 'checked' : '' }}
                                                    name="apply_on" value="custom_url" id="app-custom_url">
                                                <label for="app-custom_url">
                                                    <h4 class="d-inline-block">{{ __('external_platform') }}</h4>
                                                    <p class="tw-mb-0">
                                                        {{ __('candidate_apply_job_on_your_website_all_application_on_your_own_website') }}.
                                                    </p>
                                                </label>
                                            </div>
                                            <div id="applied_on_email"
                                                class="radio-check col-lg-4 d-flex {{ old('apply_on') === 'email' ? 'checked' : '' }}"
                                                onclick="RadioChecked('email')">
                                                <input type="radio" {{ old('apply_on') === 'email' ? 'checked' : '' }}
                                                    name="apply_on" value="email" id="app-email">
                                                <label for="app-email">
                                                    <h4 class="d-inline-block">{{ __('on_your_email') }}</h4>
                                                    <p class="tw-mb-0">
                                                        {{ __('candidate_apply_job_on_your_email_address_and_all_application_in_your_email') }}.
                                                    </p>
                                                </label>
                                            </div>
                                            <!-- apply_on end-->
                                            <div class="col-12 tw-mt-2 d-none" id="apply_on_custom_url">
                                                <x-forms.label name="website_url" :required="true" />
                                                <div class="fromGroup has-icon2">
                                                    <div class="form-control-icon">
                                                        <input value="{{ old('apply_url') }}" name="apply_url"
                                                            class="form-control @error('apply_url') is-invalid @enderror"
                                                            type="url" placeholder="{{ __('website') }}">
                                                        <div class="icon-badge-2 @error('apply_url') mt-n-11 @enderror">
                                                            <x-svg.link-icon />
                                                        </div>
                                                        @error('apply_url')
                                                            <span class="error invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 tw-mt-2 d-none" id="apply_on_email">
                                                <x-forms.label name="email_address" :required="true" />
                                                <div class="fromGroup has-icon2">
                                                    <div class="form-control-icon">
                                                        <input value="{{ old('apply_email') }}" name="apply_email"
                                                            class="form-control @error('apply_email') is-invalid @enderror"
                                                            type="email" placeholder="{{ __('email_address') }}">
                                                        <div class="icon-badge-2 @error('apply_email') mt-n-11 @enderror">
                                                            <x-svg.envelope-icon />
                                                        </div>
                                                        @error('apply_email')
                                                            <span class="error invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="post-job-item rt-mb-15 tw-w-full tw-overflow-hidden">
                                <button type="submit" class="btn btn-primary rt-mr-10">
                                    <span class="button-content-wrapper ">
                                        <span class="button-icon align-icon-right">
                                            <i class="ph-arrow-right"></i>
                                        </span>
                                        <span class="button-text">
                                            {{ __('post_job') }}
                                        </span>
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @section('css')

        <x-map.leaflet.map_links />
        <x-map.leaflet.autocomplete_links />
        @include('map::links')
        <style>
            .form-control:disabled, .form-control[readonly] {
                background-color: #fff !important;
                opacity: 1;
            }
            .ck-editor__editable_inline {
                min-height: 300px;
            }

            .mymap {
                border-radius: 12px;
            }

            .mt-n-11 {
                margin-top: -11px;
            }

            .custom-checkbox-wrap .main input:checked~.custom-checkbox:after {
                left: 8% !important;
            }
        </style>
    @endsection

    @section('frontend_scripts')
        <!-- Alpine.js -->
        <script defer src="{{ asset('backend/js/alpine.min.js') }}"></script>
        
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                $('.select21').select2();
            })
        </script>
        @stack('js')

        <script>
            function appQuestion() {
                return {
                    allQuestions: @json($questions),
                    selectedQuestions: [],
                    selectedQuestionsIds: [],
                    newQuestion: '',
                    isAddingNewQuestion: false,
                    newQuestionSave: true,
                    isRequired: false,
                    
                    addQuestion: function() {
                        if (!this.newQuestion.trim()) {
                            return;
                        }
                        
                        axios.post('/company/questions', {
                            newQuestion: this.newQuestion,
                            newQuestionSave: this.newQuestionSave,
                            isRequired: this.isRequired
                        }).then((response) => {
                            // Add to selected questions
                            this.selectedQuestions.push(response.data);
                            this.allQuestions.push(response.data);
                            this.selectedQuestionsIds.push(response.data.id);
                            
                            // Update select2 if it exists
                            if (this.select2) {
                                var optionValue = response.data.id;
                                var optionText = response.data.title;
                                if (response.data.required) {
                                    optionText += ' (required)';
                                }
                                var newOption = new Option(optionText, optionValue, false, true);
                                this.select2.append(newOption).trigger('change');
                            }
                            
                            // Reset form
                            this.newQuestion = "";
                            this.newQuestionSave = true;
                            this.isRequired = false;
                        }).catch((error) => {
                            alert('Error creating question. Please try again.');
                        });
                    },
                    
                    remove: function(idToRemove) {
                        this.selectedQuestionsIds = this.selectedQuestionsIds.filter((id) => {
                            return id != idToRemove;
                        });
                        
                        this.selectedQuestions = this.selectedQuestions.filter((ques) => {
                            return ques.id != idToRemove;
                        });
                        
                        // Update select2 if it exists
                        if (this.select2) {
                            this.select2.val(this.selectedQuestionsIds);
                            this.select2.trigger('change');
                        }
                    }
                }
            }

            function select2Alpine() {
                try {
                    // Initialize select2
                    this.select2 = $(this.$refs.select).select2();
                    
                    // Handle select event
                    this.select2.on("select2:select", (event) => {
                        var values = [];
                        $(event.currentTarget).find("option:selected").each(function(i, selected) {
                            values[i] = $(selected).val();
                        });

                        this.selectedQuestionsIds = values;
                        
                        var items = [];
                        this.allQuestions.forEach((item) => {
                            if (values.includes(item.id.toString())) {
                                items.push(item);
                            }
                        });

                        this.selectedQuestions = items;
                    });
                    
                    // Handle unselect event
                    this.select2.on("select2:unselect", (event) => {
                        var values = [];
                        $(event.currentTarget).find("option:selected").each(function(i, selected) {
                            values[i] = $(selected).val();
                        });

                        this.selectedQuestionsIds = values;
                        
                        var items = [];
                        this.allQuestions.forEach((item) => {
                            if (values.includes(item.id.toString())) {
                                items.push(item);
                            }
                        });

                        this.selectedQuestions = items;
                    });
                    
                } catch (error) {
                    alert('Error initializing question selector. Please refresh the page.');
                }
            }
        </script>


        <script>
            ClassicEditor
                .create(document.querySelector('#image_ckeditorx'), {
                    ckfinder: {
                        uploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}"
                    },
                })
                .catch(error => {
                    console.error(error);
                });

            // ClassicEditor
            //     .create(document.querySelector('#image_ckeditor_2'), {
            //         ckfinder: {
            //             uploadUrl: "{{ route('ckeditor.upload', ['_token' => csrf_token()]) }}"
            //         },
            //     })
            //     .catch(error => {
            //         console.error(error);
            //     });
        </script>
        @include('map::set-' . $setting->default_map. 'map')
        <script>
            var max_days = '{{ $setting->job_deadline_expiration_limit }}'

            document.addEventListener('DOMContentLoaded', function () {
                // Easepick for deadline
                const maxDate = new Date();
                maxDate.setDate(maxDate.getDate() + {{ $setting->job_deadline_expiration_limit }});
                const deadlinePicker = new easepick.create({
                    element: document.getElementById('date'),
                    css: [
                        'https://cdn.jsdelivr.net/npm/@easepick/bundle@1.2.1/dist/index.css',
                    ],
                    format: 'DD-MM-YYYY',
                    zIndex: 10000,
                    lang: '{{ app()->getLocale() == 'fr' ? 'fr-FR' : 'en-US' }}',
                    setup(picker) {
                        picker.on('select', (e) => {
                            const date = picker.getDate();
                            document.getElementById('date').value = date ? date.format('DD-MM-YYYY') : '';
                        });
                    }
                });

                // Easepick for job period
                const periodPicker = new easepick.create({
                    element: document.getElementById('job_period'),
                    css: [
                        'https://cdn.jsdelivr.net/npm/@easepick/bundle@1.2.1/dist/index.css',
                    ],
                    plugins: [easepick.RangePlugin],
                    format: 'YYYY-MM-DD',
                    RangePlugin: {
                        tooltip: true,
                        locale: { one: '{{ __('day') }}', other: '{{ __('days') }}' }
                    },
                    zIndex: 10000,
                    lang: '{{ app()->getLocale() == 'fr' ? 'fr-FR' : 'en-US' }}',
                    setup(picker) {
                        picker.on('select', (e) => {
                            const start = picker.getStartDate();
                            const end = picker.getEndDate();
                            document.getElementById('job_start').value = start ? start.format('YYYY-MM-DD') : '';
                            document.getElementById('job_end').value = end ? end.format('YYYY-MM-DD') : '';
                        });
                    }
                });

                // Target the input-group-addon that contains calendar icons
                document.querySelectorAll('.input-group-addon').forEach(function(addon) {
                    addon.addEventListener('click', function() {
                        const dateInput = this.closest('.form-control-icon').querySelector('#date');
                        const periodInput = this.closest('.form-control-icon').querySelector('#job_period');
                        
                        if (dateInput) {
                            deadlinePicker.show();
                        } else if (periodInput) {
                            periodPicker.show();
                        }
                    });
                });
            });
        </script>

        <script>
            var salary_mode = "{!! old('salary_mode') !!}";

            if (salary_mode) {
                salaryModeChange(salary_mode);
            }

            function salaryModeChange(param) {
                var value = param;

                if (value === 'range') {
                    $('#custom_salary_part').addClass('d-none');
                    $('.salary_range_part').removeClass('d-none');
                    $('#salary_rangee').prop('checked', true)
                    $('#custom_salary').prop('checked', false)
                } else {
                    $('#custom_salary_part').removeClass('d-none');
                    $('.salary_range_part').addClass('d-none');
                    $('#salary_rangee').prop('checked', false)
                    $('#custom_salary').prop('checked', true)
                }
            }

            function RadioChecked(param) {
                var value = param;
                if (value === 'email') {
                    $('#applied_on_email').addClass('checked');
                    $('#apply_on_custom_url').addClass('d-none');
                    $('#apply_on_email').removeClass('d-none');
                    $('#applied_on_app').removeClass('checked');
                    $('#applied_on_custom_url').removeClass('checked');
                }
                if (value === 'custom_url') {
                    $('#applied_on_custom_url').addClass('checked');
                    $('#apply_on_email').addClass('d-none');
                    $('#apply_on_custom_url').removeClass('d-none');
                    $('#applied_on_app').removeClass('checked');
                    $('#applied_on_email').removeClass('checked');
                }
                if (value === 'app') {
                    $('#applied_on_app').addClass('checked');
                    $('#applied_on_email').removeClass('checked');
                    $('#applied_on_custom_url').removeClass('checked');
                    $('#apply_on_email').addClass('d-none');
                    $('#apply_on_custom_url').addClass('d-none');
                }
            }
            $('.radio-check').on('click', function() {
                $('input:radio', this).prop('checked', true);
            });

            if ($('#app-app').is(':checked')) {
                $('#applied_on_app').addClass('checked');
            }
            if ($('#app-custom_url').is(':checked')) {
                $('#apply_on_custom_url').removeClass('d-none');
            }
            if ($('#app-email').is(':checked')) {
                $('#apply_on_email').removeClass('d-none');
            }

            var apply_url = "{!! $errors->first('apply_url') !!}";
            var apply_url1 = "{!! old('apply_email') !!}";
            var apply_email = "{!! $errors->first('apply_email') !!}";
            var apply_email1 = "{!! old('apply_email') !!}";

            if (apply_url) {
                $('#apply_on_custom_url').removeClass('d-none');
            }
            if (apply_url1) {
                $('#apply_on_custom_url').removeClass('d-none');
            }
            if (apply_email) {
                $('#apply_on_email').removeClass('d-none');
            }
            if (apply_email1) {
                $('#apply_on_email').removeClass('d-none');
            }


            function showHideCreateBenefit() {
                $('#create_benefit').toggleClass('d-none');
            }

            function createBenefit() {
                var benefit = $('input[name="new_benefit"]').val();

                if (benefit == '') {
                    alert('Please enter benefit name');
                    return false;
                }

                axios.post("/job/benefits/create", {
                    benefit: benefit
                }).then((response) => {
                    var data = response.data;

                    if (data.length && typeof data == 'string') {
                        return Swal.fire('Error', data, 'error');
                    }

                    $('#benefit_list').append(`<label for="benefit_${data.id}">
                    <input type="checkbox" id="benefit_${data.id}" name="benefits[]" value="${data.id}">
                    <span>${data.name}</span>
                </label>`);

                    $('input[name="new_benefit"]').val('');
                }).catch((err) => {
                    this.errors = err.response.data.errors;
                });
            }
        </script>
    @endsection
