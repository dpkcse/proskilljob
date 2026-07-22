@extends('frontend.layouts.app')

@section('title')
    {{ __('edit_job') }}
@endsection

@section('main')
    <div class="dashboard-wrapper">
        <div class="container">
            <div class="row">
                {{-- Sidebar --}}
                <x-website.company.sidebar />
                <div class="col-lg-9">
                    <div class="dashboard-right">
                        <div class="dashboard-right-header">
                            <span class="sidebar-open-nav">
                                <i class="ph-list"></i>
                            </span>
                        </div>
                        <h2 class="tw-text-2xl tw-font-medium tw-text-[#18191C] tw-mb-8">
                            {{ __('edit_job') }}
                        </h2>
                        <form action="{{ route('company.job.update', $job->slug) }}" method="POST" class="rt-from">
                            @csrf
                            @method('PUT')
                            <div class="post-job-item rt-mb-15">
                                <div class="row">
                                    <div class="col-lg-8 rt-mb-20">
                                        <x-forms.label name="job_title" :required="true" class="tw-text-sm tw-mb-2" />
                                        <input value="{{ $job->title }}" name="title"
                                            class="form-control @error('title') is-invalid @enderror" type="text"
                                            placeholder="{{ __('job_title') }}" id="m">
                                        @error('title')
                                            <span class="error invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-4 rt-mb-20 col-md-4">
                                        <x-forms.label name="job_category" :required="true" />
                                        <select
                                            class=" select2-taggable form-control @error('category_id') is-invalid @enderror"
                                            name="category_id">
                                            @foreach ($jobCategories as $category)
                                                <option {{ $job->category_id == $category->id ? 'selected' : '' }}
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
                                                    @foreach ($job->tags as $job_tag)
                                                    {{ $job_tag->id == $tag->id ? 'selected' : '' }} @endforeach
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
                                            class=" select2-taggable form-control @error('role_id') is-invalid @enderror"
                                            name="role_id">
                                            @foreach ($roles as $role)
                                                <option {{ $job->role_id == $role->id ? 'selected' : '' }}
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
                                                <option {{ $job->profession_id == $profession->id ? 'selected' : '' }} value="{{ $profession->id }}">{{ $profession->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('profession_id')
                                            <span class="error invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="post-job-item rt-mb-15">
                                <h4 class="f-size-18 ft-wt-5 rt-mb-20 lh-1">{{ __('salary') }}</h4>
                                <div class="tw-flex tw-gap-5 mb-3">
                                    <div
                                        class="ll-radio tw-flex tw-items-center tw-pl-4 tw-border tw-border-gray-200 tw-rounded tw-ps-1">
                                        <input checked onclick="salaryModeChange('range')" id="salary_rangee" type="radio"
                                            value="range" name="salary_mode" class="tw-scale-150">
                                        <label for="salary_rangee"
                                            class="tw-w-full tw-py-4 tw-ms-2 tw-text-sm tw-font-medium">{{ __('salary_range') }}</label>
                                    </div>
                                    <div
                                        class="ll-radio tw-flex tw-items-center tw-pl-4 tw-border tw-border-gray-200 tw-ps-1">
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
                                            <input value="{{ old('custom_salary', $job->custom_salary) }}"
                                                name="custom_salary"
                                                class="form-control @error('custom_salary') is-invalid @enderror"
                                                type="text" id="m">
                                            @error('custom_salary')
                                                <span class="error invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="rt-mb-20 col-md-4 salary_range_part">
                                        <x-forms.label name="min_salary" :required="false" class="tw-text-sm tw-mb-2" />
                                        <div class="position-relative">
                                            <input step="0.01" value="{{ $job->min_salary }}"
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
                                            <input step="0.01" value="{{ $job->max_salary }}"
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
                                                <option {{ $job->salary_type_id == $type->id ? 'selected' : '' }}
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
                            <div class="post-job-item rt-mb-15">
                                <h4 class="f-size-18 ft-wt-5 rt-mb-20 lh-1">{{ __('advance_information') }}</h4>
                                <div class="row">
                                    <div class="col-lg-4 col-md-6 rt-mb-20">
                                        <x-forms.label name="education" :required="true" class="tw-text-sm tw-mb-2" />
                                        <select
                                            class="select2-taggable form-control @error('education') is-invalid @enderror "
                                            name="education">
                                            @foreach ($educations as $education)
                                                <option {{ $job->education_id == $education->id ? 'selected' : '' }}
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
                                                <option {{ $job->experience_id == $experience->id ? 'selected' : '' }}
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
                                                <option {{ $job->job_type_id == $job_type->id ? 'selected' : '' }}
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
                                        <input type="text" name="experience_area" value="{{ old('experience_area', $job->experience_area) }}"
                                            class="form-control @error('experience_area') is-invalid @enderror"
                                            placeholder="{{ __('required_experience_type_area') }}">
                                    </div>
                                    <div class="col-lg-4 col-md-6 rt-mb-20">
                                        <x-forms.label name="business_area_industry" :required="false" class="tw-text-sm tw-mb-2" />
                                        <select class="rt-selectactive form-control @error('business_area') is-invalid @enderror" name="business_area">
                                            <option value="">{{ __('select_one') }}</option>
                                            @foreach (['sales','marketing','it','education','healthcare','garments_textile','finance_banking','customer_support','others'] as $ba)
                                                <option value="{{ $ba }}" @selected(old('business_area', $job->business_area)===$ba)>{{ __($ba) }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="business_area_other" value="{{ old('business_area_other', $job->business_area_other) }}"
                                            class="form-control mt-2 @error('business_area_other') is-invalid @enderror"
                                            placeholder="{{ __('please_specify') }}">
                                    </div>
                                    <div class="col-lg-12 col-md-12 rt-mb-20">
                                        <x-forms.label name="experience_description" :required="false" class="tw-text-sm tw-mb-2" />
                                        <textarea name="experience_description" rows="3" class="form-control @error('experience_description') is-invalid @enderror"
                                            placeholder="{{ __('experience_description') }}">{{ old('experience_description', $job->experience_description) }}</textarea>
                                    </div>
                                    <div class="col-lg-8 col-md-12 rt-mb-20">
                                        <x-forms.label name="required_degrees" :required="false" class="tw-text-sm tw-mb-2" />
                                        <select name="required_degrees[]" multiple class="select2-taggable form-control @error('required_degrees') is-invalid @enderror">
                                            @php $selectedDegrees = old('required_degrees', json_decode($job->required_degrees ?? '[]', true) ?: []); @endphp
                                            @foreach (['ba','bsc','bba','b_com','ma','msc','mba','diploma','hsc','ssc','others'] as $deg)
                                                <option value="{{ $deg }}" @selected(collect($selectedDegrees)->contains($deg))>{{ __($deg) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-4 col-md-12 rt-mb-20">
                                        <x-forms.label name="other_degree" :required="false" class="tw-text-sm tw-mb-2" />
                                        <input type="text" name="required_degrees_other" value="{{ old('required_degrees_other', $job->required_degrees_other) }}"
                                            class="form-control @error('required_degrees_other') is-invalid @enderror" placeholder="{{ __('please_specify') }}">
                                    </div>
                                    <div class="col-lg-12 col-md-12 rt-mb-20">
                                        <x-forms.label name="preferred_educational_institution" :required="false" class="tw-text-sm tw-mb-2" />
                                        @php $preferredInstitutions = old('preferred_institutions', json_decode($job->preferred_institutions ?? '[]', true) ?: []); @endphp
                                        <select name="preferred_institutions[]" multiple class="select2tags form-control @error('preferred_institutions') is-invalid @enderror">
                                            @foreach ($preferredInstitutions as $inst)
                                                <option value="{{ $inst }}" selected>{{ $inst }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-4 col-md-6 rt-mb-20">
                                        <x-forms.label name="gender" :required="false" class="tw-text-sm tw-mb-2" />
                                        <select class="rt-selectactive form-control @error('gender') is-invalid @enderror " name="gender">
                                            <option value="any" {{ old('gender', $job->gender ?? 'any') == 'any' ? 'selected' : '' }}>{{ __('any') }}</option>
                                            <option value="male" {{ old('gender', $job->gender) == 'male' ? 'selected' : '' }}>{{ __('male') }}</option>
                                            <option value="female" {{ old('gender', $job->gender) == 'female' ? 'selected' : '' }}>{{ __('female') }}</option>
                                        </select>
                                        @error('gender')
                                            <span class="error invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-6 col-md-6 rt-mb-20">
                                        <x-forms.label name="vacancies" :required="true" class="tw-text-sm tw-mb-2" />
                                        <input value="{{ $job->vacancies }}" name="vacancies" type="text"
                                            placeholder="{{ __('vacancies') }}"
                                            class="form-control @error('vacancies') is-invalid @enderror" id="vacancies">
                                        @error('vacancies')
                                            <span class="error invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-3 col-md-6 rt-mb-20">
                                        <x-forms.label name="min_age" :required="false" class="tw-text-sm tw-mb-2" />
                                        <input value="{{ old('min_age', $job->min_age) }}" name="min_age" type="number" min="0" max="100"
                                            placeholder="{{ __('min_age') }}"
                                            class="form-control @error('min_age') is-invalid @enderror">
                                        @error('min_age')
                                            <span class="error invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-lg-3 col-md-6 rt-mb-20">
                                        <x-forms.label name="max_age" :required="false" class="tw-text-sm tw-mb-2" />
                                        <input value="{{ old('max_age', $job->max_age) }}" name="max_age" type="number" min="0" max="100"
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
                                                <input value="{{ date('d-m-Y', strtotime($job->deadline)) }}"
                                                    name="deadline"
                                                    class="form-control !tw-ps-[55px] @error('deadline') is-invalid @enderror"
                                                    type="text" id="date" placeholder="{{ __('dd-mm-yyyy') }}">
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
                                                       value="{{ old('job_start') && old('job_end') ? old('job_start') . ' - ' . old('job_end') : ($job->job_start && $job->job_end ? $job->job_start . ' - ' . $job->job_end : '') }}" readonly autocomplete="off"
                                                       placeholder="{{ __('job_start_end_title') }}">
                                                <input type="hidden" id="job_start" name="job_start" value="{{ old('job_start', $job->job_start) }}">
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
                                                            placeholder="{{ __('enter_city_name') }}" class="full-width"
                                                            value="{{ $job->exact_location ? $job->exact_location : $job->full_address }}" />
                                                        <br>
                                                        <div id="leaflet-map"></div>
                                                    </div>
                                                    @error('location')
                                                        <span class="ml-3 text-md text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-12 mt-4 custom-checkbox-wrap">
                                                    <label class="main tw-text-sm"
                                                        for="remoteWork">{{ __('fully_remote_position') }}- <span
                                                            class="tw-font-medium">{{ __('worldwide') }}</span>
                                                        <input type="checkbox" name="is_remote" id="remoteWork"
                                                            value="1" {{ $job->is_remote ? 'checked' : '' }}>
                                                        <span class="custom-checkbox"></span>
                                                    </label>
                                                </div>

                                                <div class="col-12 mt-4">
                                                    @php
                                                        $session_location = session()->get('location');
                                                        $session_country = $session_location && array_key_exists('country', $session_location) ? $session_location['country'] : '-';
                                                        $session_exact_location = $session_location && array_key_exists('exact_location', $session_location) ? $session_location['exact_location'] : '-';
                                                        $company_country = $job->country;
                                                        $company_exact_location = $job->exact_location;
                                                    @endphp
                                                    <div class="card-footer row mt-4 border-0">
                                                        <span>
                                                            <img src="{{ asset('frontend/assets/images/loader.gif') }}"
                                                                alt="loading" width="50px" height="50px"
                                                                class="loader_position d-none">
                                                        </span>
                                                        <div class="location_secion">
                                                            {{ __('country') }}: <span
                                                                class="location_country">{{ $company_country ?: $session_country }}</span>
                                                            <br/>
                                                            {{ __('full_address') }}: <span
                                                                class="location_full_address">{{ $company_exact_location ?: $session_exact_location }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    @php
                                        session([
                                            'selectedCountryId' => null,
                                            'selectedStateId' => null,
                                            'selectedCityId' => null,
                                        ]);
                                        session([
                                            'selectedCountryId' => $job->country,
                                            'selectedStateId' => $job->region,
                                            'selectedCityId' => $job->district,
                                        ]);
                                    @endphp
                                    <x-forms.label name="location" :required="true" class="tw-text-sm tw-mb-2" />
                                    <div class="card-body pt-0 row">
                                        <div class="col-12">
                                            @livewire('country-state-city', ['row' => true])
                                            @error('location')
                                                <span class="ml-3 text-md text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="post-job-item rt-mb-32">
                                <h4 class="f-size-18 ft-wt-5 rt-mb-20 lh-1">{{ __('benefits') }}</h4>
                                <div class="benefits-tags">
                                    @foreach ($benefits as $benefit)
                                        <label for="benefit_{{ $benefit->id }}">
                                            <input
                                                @foreach ($job->benefits as $job_benefit)
                                            {{ $job_benefit->id == $benefit->id ? 'checked' : '' }} @endforeach
                                                type="checkbox" id="benefit_{{ $benefit->id }}" name="benefits[]"
                                                value="{{ $benefit->id }}">
                                            <span>{{ $benefit->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('benefits')
                                    <span class="error invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-4">
                                <div class="form-group">
                                    <x-forms.label name="skills" :required="false" />
                                    <select id="skills" name="skills[]"
                                        class="select2-taggable form-control @error('skills') is-invalid @enderror"
                                        multiple>
                                        @foreach ($skills as $skill)
                                            <option
                                                @foreach ($job->skills as $job_skill)
                                                {{ $job_skill->id == $skill->id ? 'selected' : '' }} @endforeach
                                                value="{{ $skill->id }}">{{ $skill->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('skills')
                                        <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="post-job-item rt-mb-32">
                                <h4 class="f-size-18 ft-wt-5 tw-mb-3 lh-1">{{ __('job_description') }}</h4>
                                <div class="col-md-12">
                                    <textarea id="image_ckeditor" class="form-control @error('description') is-invalid @enderror" name="description">{{ $job->description }}</textarea>
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
                                        <div class=" rt-mb-20">
                                            <div class="col-lg-12">
                                                <div x-show="isAddingNewQuestion" class="tw-flex justify-content-between">
                                                    <label class="tw-text-sm tw-mb-2 mb-2" for="for">
                                                        {{ __('create_new_screening_question') }}
                                                    </label>
                                                    <a x-show="isAddingNewQuestion" href="#"
                                                        @click.prevent="isAddingNewQuestion = false">{{ __('choose_from_existing_question') }}</a>
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
                                                    class="form-control " type="text" placeholder="Add Question ">
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

                                            data-placeholder="{{ __('select_questions') }}" name="companyQuestions[]" class="select2 form-control">

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
                            @endif

                            <div class="row tw-mb-8">
                                <div class="col-12">
                                    <div class="applied-job-on">
                                        <div class="row">
                                            <h2>{{ __('apply_job_on') }}:</h2>
                                            <!-- apply_on -->
                                            <div id="applied_on_app"
                                                class="radio-check col-lg-4 d-flex {{ $job->apply_on === 'app' ? 'checked' : '' }}"
                                                onclick="RadioChecked('app')">
                                                <input type="radio" {{ $job->apply_on === 'app' ? 'checked' : '' }}
                                                    checked name="apply_on" value="app" id="app-app">
                                                <label for="app-app">
                                                    <h4 class="d-inline-block">{{ __('on_appname', ['appname' => config('app.name')]) }}</h4>
                                                    <p class="tw-mb-0">{{ __('candidate_will_apply_job_using') }}
                                                        {{ config('app.name') }} &
                                                        {{ __('all_application_will_show_on_your_dashboard') }}.</p>
                                                </label>
                                            </div>
                                            <div id="applied_on_custom_url"
                                                class="radio-check col-lg-4 d-flex {{ $job->apply_on === 'custom_url' ? 'checked' : '' }}"
                                                onclick="RadioChecked('custom_url')">
                                                <input type="radio"
                                                    {{ $job->apply_on === 'custom_url' ? 'checked' : '' }}
                                                    name="apply_on" value="custom_url" id="app-custom_url">
                                                <label for="app-custom_url">
                                                    <h4 class="d-inline-block">{{ __('external_platform') }}</h4>
                                                    <p class="tw-mb-0">
                                                        {{ __('candidate_apply_job_on_your_website_all_application_on_your_own_website') }}.
                                                    </p>
                                                </label>
                                            </div>
                                            <div id="applied_on_email"
                                                class="radio-check col-lg-4 d-flex {{ $job->apply_on === 'email' ? 'checked' : '' }}"
                                                onclick="RadioChecked('email')">
                                                <input type="radio" {{ $job->apply_on === 'email' ? 'checked' : '' }}
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
                                                        <input value="{{ $job->apply_url }}" name="apply_url"
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
                                                        <input value="{{ $job->apply_email }}" name="apply_email"
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
                            <div class="post-job-item rt-mb-15">
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
            $('.select2-question').select2();
        });
    </script>
    @stack('js')
    <script>
        function appQuestion() {
            return {
                allQuestions: @json($questions),
                selectedQuestions: @json($job->questions),
                selectedQuestionsIds: @json($job->questions->pluck('id')->toArray()),
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

            this.select2 = $(this.$refs.select).select2();

            this.select2.val(this.selectedQuestionsIds);

            this.select2.trigger('change');
            this.select2.on("select2:select", (event) => {
                var values = [];
                var old_values = [];

                // copy all option values from selected
                $(event.currentTarget).find("option:selected").each(function(i, selected) {
                    values[i] = $(selected).val();
                });

                this.selectedQuestionsIds = values;
                console.log(this.allQuestions);
                var items = [];

                this.allQuestions.forEach((item) => {
                    if (values.includes(item.id.toString())) {
                        items.push(item);
                    }

                });
                this.selectedQuestions = items;
            });
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
        }
    </script>

    @include('map::set-edit-' . $setting->default_map. 'map', ['lat' => $job->lat, 'long' => $job->long])

    <script>
        var max_days = '{{ $setting->job_deadline_expiration_limit }}'

        document.addEventListener('DOMContentLoaded', function () {
            // Easepick for deadline
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
        var salary_mode = "{!! old('salary_mode', $job->salary_mode) !!}";

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
    </script>
@endsection
