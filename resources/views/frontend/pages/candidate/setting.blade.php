@extends('frontend.layouts.app')
@section('title')
    {{ __('edit_profile') }}
@endsection
@section('main')

    <div class="dashboard-wrapper candidate-profile-responsive">
        <div class="container">
            <div class="row">
                <x-website.candidate.sidebar />
                <div class="col-lg-9">
                    <div class="dashboard-right">
                        <div class="dashboard-right-header rt-mb-32">
                            <div class="left-text m-0">
                                <h3 class="f-size-18 lh-1 m-0">{{ __('edit_profile') }}</h3>
                            </div>
                            <span class="sidebar-open-nav">
                                <i class="ph-list"></i>
                            </span>
                        </div>
                        <div class="cadidate-dashboard-tabs candidate">
                            <div class="tw-overflow-x-auto">
                                <ul class="nav nav-pills tw-gap-x-8" id="pills-tab" role="tablist">
                                    {{-- Basic Setting  --}}
                                    <li class="nav-item" role="presentation">
                                        <button
                                            class="nav-link {{ !session('type') || session('type') == 'basic' ? 'active' : '' }}"
                                            id="pills-personal-tab" data-bs-toggle="pill" data-bs-target="#pills-personal"
                                            type="button" role="tab" aria-controls="pills-personal"
                                            aria-selected="true">
                                            <x-svg.user-icon />
                                            {{ __('basic') }}
                                        </button>
                                    </li>

                                    {{-- Profile Setting  --}}
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ session('type') == 'profile' ? 'active' : '' }}"
                                            id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile"
                                            type="button" role="tab" aria-controls="pills-profile"
                                            aria-selected="false">
                                            <x-svg.user-round-icon />
                                            {{ __('profile') }}
                                        </button>
                                    </li>

                                    {{-- Experience & Education Setting  --}}
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ in_array(session('type'), ['experience', 'education'], true) ? 'active' : '' }}"
                                            id="pills-experience-tab" data-bs-toggle="pill"
                                            data-bs-target="#pills-experience" type="button" role="tab"
                                            aria-controls="pills-experience" aria-selected="false">
                                            <x-svg.briefcase-icon />
                                            {{ __('experience_and_education') }}
                                        </button>
                                    </li>

                                    {{-- Extracariculler Activities  --}}
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ session('type') == 'extracariculler' ? 'active' : '' }}"
                                            id="pills-extracariculler-tab" data-bs-toggle="pill"
                                            data-bs-target="#pills-extracariculler" type="button" role="tab"
                                            aria-controls="pills-extracariculler" aria-selected="false">
                                            <x-svg.briefcase-icon />
                                            {{ __('extra_activities') }}
                                        </button>
                                    </li>

                                    {{-- Social Setting  --}}
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ session('type') == 'social' ? 'active' : '' }}"
                                            id="pills-social-tab" data-bs-toggle="pill" data-bs-target="#pills-social"
                                            type="button" role="tab" aria-controls="pills-social"
                                            aria-selected="false">
                                            <x-svg.globe2-icon />
                                            {{ __('social_media') }}
                                        </button>
                                    </li>

                                    {{-- Account Setting  --}}
                                    <li class="nav-item" role="presentation">
                                        <button
                                            class="nav-link {{ session('type') == 'alert' || session('type') == 'contact' || session('type') == 'account' || session('type') == 'visibility' || session('type') == 'password' || session('type') == 'account-delete' ? 'active' : '' }} @error('password') active @enderror "
                                            id="pills-setting-tab" data-bs-toggle="pill" data-bs-target="#pills-setting"
                                            type="button" role="tab" aria-controls="pills-setting"
                                            aria-selected="false">
                                            <x-svg.cog-icon />
                                            {{ __('account_setting') }}
                                        </button>
                                    </li>
                                    <span class="glider"></span>
                                </ul>
                            </div>
                            <div class="tab-content" id="pills-tabContent">
                                {{-- Basic Setting  --}}
                                <div class="tab-pane fade {{ !session('type') || session('type') == 'basic' ? 'show active' : '' }}"
                                    id="pills-personal" role="tabpanel" aria-labelledby="pills-personal-tab">
                                    <form action="{{ route('candidate.settingUpdate') }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf
                                        @method('put')
                                        <input type="hidden" name="type" value="basic">
                                        <div class="dashboard-account-setting-item tw-py-0">
                                            <h6> {{ __('basic_information') }}</h6>
                                            <div class="row">
                                                <div class="col-lg-4">
                                                    <x-website.candidate.photo-section :candidate="$candidate" />
                                                    <x-website.candidate.signature-section :candidate="$candidate" />
                                                    <div class="mt-4">
                                                        <h6 class="resume">{{ __('your_cv_resume') }}</h6>
                                                        @if ($errors->has('resume_name') || $errors->has('resume_file'))
                                                            <div class="alert alert-danger" role="alert">
                                                                @error('resume_name')
                                                                    <span class="d-block"><strong>{{ $message }}</strong>
                                                                    </span>
                                                                @enderror
                                                                @error('resume_file')
                                                                    <span class="d-block"><strong>{{ $message }}</strong>
                                                                    </span>
                                                                @enderror
                                                            </div>
                                                        @endif
                                                        <div class="resume-lists">
                                                            @foreach ($resumes as $resume)
                                                                <div class="resume-item">
                                                                    <div class="resume-icon">
                                                                        <x-svg.file-icon2 />
                                                                    </div>
                                                                    <div>
                                                                        <h4 class="resume-title">{{ $resume->name }}</h4>
                                                                        <h6 class="resume-size">{{ $resume->file_size }}</h6>
                                                                    </div>
                                                                    <div class="dot-icon ms-auto">
                                                                        <button type="button" class="btn p-0" id="dropdownMenuButton5"
                                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                                            <x-svg.three-dots />
                                                                        </button>
                                                                        <ul class="dropdown-menu dropdown-menu-end company-dashboard-dropdown"
                                                                            aria-labelledby="dropdownMenuButton5">
                                                                            <li>
                                                                                <form id="cv_show_{{ $resume->id }}"
                                                                                    action="{{ route('candidate.cv.show') }}"
                                                                                    method="POST">
                                                                                    @csrf
                                                                                    <input type="hidden" name="cv"
                                                                                        value="{{ $resume->id }}" class="d-none">
                                                                                    <button type="submit"
                                                                                        class="dropdown-item cv-show-submit-btn">
                                                                                        <x-svg.eye width="20" height="20" />
                                                                                        {{ __('view') }}
                                                                                    </button>
                                                                                </form>
                                                                            </li>
                                                                            <li>
                                                                                <button
                                                                                    onclick="editResume({{ $resume->id }},'{{ $resume->name }}', '{{ $resume->file_size }}')"
                                                                                    type="button" class="dropdown-item">
                                                                                    <x-svg.pen-edit />
                                                                                    {{ __('edit') }}
                                                                                </button>
                                                                            </li>
                                                                            <li>
                                                                                <form
                                                                                    action="{{ route('candidate.resume.delete', $resume->id) }}"
                                                                                    method="POST" id="resumeForm">
                                                                                    @csrf
                                                                                    @method('DELETE')
                                                                                    <button type="button" onclick="resumeDelete()"
                                                                                        class="dropdown-item">
                                                                                        <x-svg.trash-icon />
                                                                                        {{ __('delete') }}
                                                                                    </button>
                                                                                </form>
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            @endforeach

                                                            <div class="resume-item add-resume" data-bs-toggle="modal"
                                                                data-bs-target="#resumeModal">
                                                                <div class="resume-icon">
                                                                    <x-svg.plus-icon />
                                                                </div>
                                                                <div>
                                                                    <h4 class="resume-title">{{ __('add_cv_resume') }}</h4>
                                                                    <h6 class="resume-size">{{ __('browse_file_here_only') }} - pdf</h6>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row col-lg-8">
                                                    <div class="col-lg-6 mb-3">
                                                        <x-forms.label :required="true" name="full_name"
                                                            class="pointer body-font-4 d-block text-gray-900 rt-mb-8" />
                                                        <div class="fromGroup">
                                                            <div class="form-control-icon">
                                                                <x-forms.input type="text" name="name"
                                                                    value="{{ $candidate->user->name }}"
                                                                    placeholder="{{ __('name') }}" class="" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 mb-3">
                                                        <x-forms.label :required="false" name="professional_title_tagline"
                                                            class="pointer body-font-4 d-block text-gray-900 rt-mb-8" />
                                                        <div class="fromGroup">
                                                            <div class="form-control-icon">
                                                                <x-forms.input type="text" name="title"
                                                                    value="{{ $candidate->title ?? '' }}"
                                                                    placeholder="{{ __('title') }}" class="" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 mb-3">
                                                        <x-forms.label :required="true" name="experience_level"
                                                            class="pointer body-font-4 d-block text-gray-900 rt-mb-8" />
                                                        <select name="experience" class="select2-taggable w-100-p">
                                                            @foreach ($experiences as $experience)
                                                                <option
                                                                    {{ $candidate->experience_id == $experience->id ? 'selected' : '' }}
                                                                    value="{{ $experience->id }}">{{ $experience->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('experience')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    <div class="col-lg-6 mb-3">
                                                        <x-forms.label :required="true" name="education_level"
                                                            class="pointer body-font-4 d-block text-gray-900 rt-mb-8" />
                                                        <select name="education" class="select2-taggable w-100-p">
                                                            @foreach ($educations as $education)
                                                                <option
                                                                    {{ $candidate->education_id == $education->id ? 'selected' : '' }}
                                                                    value="{{ $education->id }}">{{ $education->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('education')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    <div class="col-6 mb-3">
                                                        <x-forms.label :required="false" name="personal_website"
                                                            class="pointer body-font-4 d-block text-gray-900 rt-mb-8" />
                                                        <div class="fromGroup has-icon2">
                                                            <div class="form-control-icon">
                                                                <x-forms.input type="url" name="website"
                                                                    value="{{ $candidate->website }}"
                                                                    placeholder="{{ __('website') }}" class="" />
                                                                <div class="icon-badge-2">
                                                                    <x-svg.link-icon />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 mb-3">
                                                        <x-forms.label :required="false" name="nationality"
                                                            class="pointer body-font-4 d-block text-gray-900 rt-mb-8" />

                                                        <div class="fromGroup">
                                                            <div class="form-control-icon">
                                                                <select name="nationality"
                                                                        class="w-100-p select2-country">
                                                                    <option value="">{{ __('select_country') ?? 'Select country' }}</option>

                                                                    @foreach ($countries as $country)
                                                                        <option
                                                                            value="{{ $country->name }}"
                                                                            {{ old('nationality', $candidate->nationality) == $country->name ? 'selected' : '' }}>
                                                                            {{ $country->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>

                                                        @error('nationality')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    <div class="col-lg-6 mb-3">
                                                        <label class="pointer body-font-4 d-block text-gray-900 rt-mb-8">{{ __('father_name') }}</label>
                                                        <x-forms.input type="text" name="father_name"
                                                            value="{{ old('father_name', $candidate->father_name ?? '') }}"
                                                            placeholder="{{ __('type_father_name') }}" />
                                                    </div>

                                                    <div class="col-lg-6 mb-3">
                                                        <label class="pointer body-font-4 d-block text-gray-900 rt-mb-8">{{ __('mother_name') }}</label>
                                                        <x-forms.input type="text" name="mother_name"
                                                            value="{{ old('mother_name', $candidate->mother_name ?? '') }}"
                                                            placeholder="{{ __('type_mother_name') }}" />
                                                    </div>

                                                    <div class="col-lg-6 mb-3">
                                                        <label class="pointer body-font-4 d-block text-gray-900 rt-mb-8">{{ __('religion') }}</label>
                                                        <x-forms.input type="text" name="religion"
                                                            value="{{ old('religion', $candidate->religion ?? '') }}"
                                                            placeholder="{{ __('type_religion') }}" />
                                                    </div>


                                                    @if (setting('candidate_birth_date_active'))
                                                    <div class="col-lg-6 mb-3">
                                                        <x-forms.label :required="false" name="date_of_birth"
                                                            class="body-font-4 d-block text-gray-900 rt-mb-8" />
                                                        <div class="fromGroup">
                                                            <div
                                                                class="d-flex align-items-center form-control-icon date datepicker">
                                                                <input type="text" name="birth_date"
                                                                   value="{{ old('birth_date', $candidate->birth_date ? \Illuminate\Support\Carbon::parse($candidate->birth_date)->format('Y-m-d') : '') }}"
                                                                    id="date" placeholder="yyyy-mm-dd"
                                                                    class="form-control border-cutom @error('birth_date') is-invalid @enderror" />
                                                                <span class="input-group-addon input-group-text-custom">
                                                                    <x-svg.calendar-icon />
                                                                </span>
                                                            </div>
                                                        </div>
                                                         @error('birth_date')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    <div class="col-lg-6 mb-3">
                                                        <x-forms.label :required="false" name="age"
                                                            class="body-font-4 d-block text-gray-900 rt-mb-8" />
                                                        <x-forms.input type="number" name="age"
                                                            value="{{ old('age', $candidate->birth_date ? \Carbon\Carbon::parse($candidate->birth_date)->age : '') }}"
                                                            id="age_basic" placeholder="{{ __('age') }}"
                                                            min="1" max="100" />
                                                        <small class="text-muted">{{ __('date_of_birth') }} / {{ __('age') }}</small>
                                                        @error('age')
                                                            <span class="text-danger d-block">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    @endif
                                                    
                                        {{-- Contact + BD Address (in Basic form) --}}
                                        <div class="dashboard-account-setting-item">
                                            <h6>{{ __('your_contact_information') }}</h6>
                                            <div class="row">
                                                <div class="col-lg-6 mb-3">
                                                    <x-forms.label :required="false" name="phone"
                                                        class="pointer body-font-4 d-block text-gray-900 rt-mb-8" />
                                                    <x-forms.input type="text" name="phone"
                                                        value="{{ $contact->phone ?? '' }}" id="phone_basic"
                                                        placeholder="{{ __('phone') }}" class="phonecode" />
                                                </div>
                                                <div class="col-lg-6 mb-3">
                                                    <x-forms.label :required="false" name="secondary_phone"
                                                        class="pointer body-font-4 d-block text-gray-900 rt-mb-8" />
                                                    <x-forms.input type="text" name="secondary_phone"
                                                        value="{{ $contact->secondary_phone ?? '' }}" id="phone2_basic"
                                                        placeholder="{{ __('phone') }}" class="phonecode" />
                                                </div>
                                                <div class="col-lg-6 mb-3">
                                                    <x-forms.label :required="false" name="email"
                                                        class="pointer body-font-4 d-block text-gray-900 rt-mb-8" />
                                                    <x-forms.input type="email" name="email"
                                                        value="{{ $contact->email ?? '' }}" id="email_basic"
                                                        placeholder="{{ __('email') }}" />
                                                </div>
                                                <div class="col-lg-6 mb-3">
                                                    <x-forms.label :required="false" name="secondary_email"
                                                        class="pointer body-font-4 d-block text-gray-900 rt-mb-8" />
                                                    <x-forms.input type="email" name="secondary_email"
                                                        value="{{ $contact->secondary_email ?? '' }}" id="secondary_email_basic"
                                                        placeholder="{{ __('email') }}" />
                                                </div>
                                                <div class="col-lg-6 mb-3">
                                                    <x-forms.label :required="false" name="whatsapp_number"
                                                        class="pointer body-font-4 d-block text-gray-900 rt-mb-8" />
                                                    <x-forms.input type="text" name="whatsapp_number"
                                                        value="{{ $contact->whatsapp_number ?? $candidate->whatsapp_number ?? '' }}" id="whatsapp_basic"
                                                        placeholder="{{ __('whatsapp_number') }}" class="phonecode" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="dashboard-account-setting-item">
                                            <h6>{{ __('present_address') }}</h6>
                                            <div class="row">
                                                <div class="col-lg-4 mb-3">
                                                    <label class="body-font-4 d-block text-gray-900 rt-mb-8">{{ __('district') }}</label>
                                                        @php
                                                            $districtValue = old('bd_district_name', $candidate->locality ?: ($candidate->bd_district ?: ($candidate->district ?: '')));
                                                            $thanaValue = old('bd_thana_name', $candidate->bd_thana ?: ($candidate->place ?: ''));
                                                        @endphp
                                                        <select id="bd_district_select" class="rt-selectactive w-100-p" name="bd_district_name">
                                                            <option value="">{{ __('select_one') }}</option>
                                                            @if ($districtValue)
                                                                <option value="{{ $districtValue }}" selected>{{ $districtValue }}</option>
                                                            @endif
                                                        </select>
                                                </div>
                                                <div class="col-lg-4 mb-3">
                                                    <label class="body-font-4 d-block text-gray-900 rt-mb-8">{{ __('thana_upazila') }}</label>
                                                   <select id="bd_thana_select" class="rt-selectactive w-100-p" name="bd_thana_name" {{ $districtValue ? '' : 'disabled' }}>
                                                           <option value="">{{ __('select_one') }}</option>
                                                            @if ($thanaValue)
                                                                <option value="{{ $thanaValue }}" selected>{{ $thanaValue }}</option>
                                                            @endif
                                                    </select>
                                                </div>
                                                <div class="col-lg-4 mb-3">
                                                    <x-forms.label :required="false" name="postcode"
                                                        class="body-font-4 d-block text-gray-900 rt-mb-8" />
                                                    <x-forms.input type="text" name="postcode"
                                                        value="{{ old('postcode', $candidate->postcode ?: ($candidate->bd_post_office ?: '')) }}" id="postcode_basic"
                                                        placeholder="{{ __('postcode') }}" />
                                                </div>
                                                <div class="col-lg-12 mb-3">
                                                    <label class="body-font-4 d-block text-gray-900 rt-mb-8">{{ __('house_no_road_village') }}</label>
                                                    <input type="text" name="neighborhood" class="form-control"
                                                        value="{{ old('neighborhood', $candidate->neighborhood ?: ($candidate->house_road_village ?: '')) }}"
                                                        placeholder="{{ __('type_house_no_road_village') }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="dashboard-account-setting-item">
                                            <h6>{{ __('permanent_address') }}</h6>
                                            @php
                                                $permanentAddress = old('permanent_address', $candidate->permanent_address ?? '');
                                            @endphp
                                            <div class="row">
                                                <div class="col-lg-4 mb-3">
                                                    <label class="body-font-4 d-block text-gray-900 rt-mb-8">{{ __('district') }}</label>
                                                    <select id="permanent_bd_district_select" class="rt-selectactive w-100-p" name="permanent_bd_district_name">
                                                        <option value="">{{ __('select_one') }}</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-4 mb-3">
                                                    <label class="body-font-4 d-block text-gray-900 rt-mb-8">{{ __('thana_upazila') }}</label>
                                                    <select id="permanent_bd_thana_select" class="rt-selectactive w-100-p" name="permanent_bd_thana_name" disabled>
                                                        <option value="">{{ __('select_one') }}</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-4 mb-3">
                                                    <x-forms.label :required="false" name="postcode"
                                                        class="body-font-4 d-block text-gray-900 rt-mb-8" />
                                                    <x-forms.input type="text" name="permanent_postcode"
                                                        value="{{ old('permanent_postcode') }}" id="permanent_postcode_basic"
                                                        placeholder="{{ __('postcode') }}" />
                                                </div>
                                                <div class="col-lg-12 mb-3">
                                                    <label class="body-font-4 d-block text-gray-900 rt-mb-8">{{ __('house_no_road_village') }}</label>
                                                    <input type="text" name="permanent_neighborhood" class="form-control"
                                                        value="{{ old('permanent_neighborhood') }}"
                                                        placeholder="{{ __('type_house_no_road_village') }}">
                                                </div>
                                            </div>
                                            <input type="hidden" id="permanent_address_existing" value="{{ $permanentAddress }}">
                                        </div>

                                        <div class="dashboard-account-setting-item">
                                            <h6>{{ __('international_address') }}</h6>
                                            <div class="row">
                                                <div class="col-lg-12 mb-3">
                                                    <label class="body-font-4 d-block text-gray-900 rt-mb-8">{{ __('international_address') }}</label>
                                                    <textarea name="international_address" class="form-control" rows="3" placeholder="{{ __('type_international_address') }}">{{ old('international_address', $candidate->international_address ?? '') }}</textarea>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Account settings (in Basic form) --}}
                                        <div class="dashboard-account-setting-item pb-0">
                                            <h6>{{ __('account_settings') }}</h6>
                                            <div class="row">
                                                <div class="col-lg-6 mb-3">
                                                    <x-forms.label :required="false" name="account_email"
                                                        class="body-font-4 d-block text-gray-900 rt-mb-8" />
                                                    <x-forms.input type="email" name="account_email"
                                                        value="{{ old('account_email', auth()->user()->email) }}" id="account_email_basic"
                                                        placeholder="{{ __('email') }}" />
                                                </div>

                                                <div class="col-lg-6 mb-3">
                                                    <x-forms.label :required="false" name="new_password"
                                                        class="body-font-4 d-block text-gray-900 rt-mb-8" />
                                                    <x-forms.input type="password" name="password"
                                                        value="" id="password_basic"
                                                        placeholder="{{ __('new_password') }}" />
                                                </div>

                                                <div class="col-lg-6 mb-3">
                                                    <x-forms.label :required="false" name="confirm_password"
                                                        class="body-font-4 d-block text-gray-900 rt-mb-8" />
                                                    <x-forms.input type="password" name="password_confirmation"
                                                        value="" id="password_confirmation_basic"
                                                        placeholder="{{ __('confirm_password') }}" />
                                                </div>
                                            </div>
                                        </div>

                                                    <div class="col-lg-12 mt-4">
                                                        <button type="submit" class="btn btn-primary">
                                                            {{ __('save_changes') }}
                                                        </button>
                                                    </div>
                                                </div>

                                            </div>

                                        </div>
                                    </form>
                                    

                                </div>

                                {{-- Profile Setting  --}}
                                <div class="tab-pane fade {{ session('type') == 'profile' ? 'show active' : '' }}"
                                    id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
                                    <form action="{{ route('candidate.settingUpdate') }}" method="POST">
                                        @csrf
                                        @method('put')
                                        <div class="dashboard-account-setting-item pb-0">
                                            <input type="hidden" name="type" value="profile">
                                            <div class="row">
                                                @if (setting('candidate_gender_active'))
                                                <div class="col-lg-6 mb-3">
                                                    <x-forms.label :required="true" name="gender"
                                                        class="body-font-4 d-block text-gray-900 rt-mb-8" />
                                                    <select
                                                        class="rt-selectactive w-100-p @error('gender') is-invalid @enderror"
                                                        name="gender">
                                                        <option @if ($candidate->gender == 'male') selected @endif
                                                            value="male">
                                                            {{ __('male') }}
                                                        </option>
                                                        <option @if ($candidate->gender == 'female') selected @endif
                                                            value="female">
                                                            {{ __('female') }}
                                                        </option>
                                                        <option @if ($candidate->gender == 'other') selected @endif
                                                            value="other">
                                                            {{ __('other') }}
                                                        </option>
                                                    </select>
                                                    @error('gender')
                                                        <span class="invalid-feedback"
                                                            role="alert">{{ __($message) }}</span>
                                                    @enderror
                                                </div>
                                                @endif
                                                @if (setting('candidate_marital_status_active'))
                                                <div class="col-lg-6 mb-3">
                                                    <x-forms.label :required="false" name="marital_status"
                                                        class="body-font-4 d-block text-gray-900 rt-mb-8" />
                                                    <select name="marital_status" class="rt-selectactive w-100-p">
                                                        <option value="">{{ __('select_one') }}</option>
                                                        <option @if ($candidate->marital_status == 'married') selected @endif
                                                            value="married">{{ __('married') }}</option>
                                                        <option @if ($candidate->marital_status == 'single') selected @endif
                                                            value="single">{{ __('single') }}</option>
                                                    </select>
                                                    @error('marital_status')
                                                        <span class="invalid-feedback"
                                                            role="alert">{{ __($message) }}</span>
                                                    @enderror
                                                </div>
                                                @endif
                                                <div class="col-lg-6 mb-3">
                                                    <x-forms.label :required="true" name="profession"
                                                        class="body-font-4 d-block text-gray-900 rt-mb-8" />
                                                    <select name="profession" class="select2-taggable w-100-p">
                                                        @foreach ($professions as $profession)
                                                            <option
                                                                {{ $candidate->profession_id == $profession->id ? 'selected' : '' }}
                                                                value="{{ $profession->id }}">{{ $profession->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('profession')
                                                        <span class="invalid-feedback"
                                                            role="alert">{{ __($message) }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-lg-6 mb-3">
                                                    <x-forms.label :required="true" name="your_availability"
                                                        class="body-font-4 d-block text-gray-900 rt-mb-8" />
                                                    <select id="available_status" name="status"
                                                        class="rt-selectactive form-control w-100-p">
                                                        <option value="">{{ __('select_one') }}</option>
                                                        <option
                                                            {{ old('status', $candidate->status) == 'available' ? 'selected' : '' }}
                                                            value="available">{{ __('available') }}</option>
                                                        <option
                                                            {{ old('status', $candidate->status) == 'not_available' ? 'selected' : '' }}
                                                            value="not_available">{{ __('not_available') }}</option>
                                                        <option
                                                            {{ old('status', $candidate->status) == 'available_in' ? 'selected' : '' }}
                                                            value="available_in">{{ __('available_in') }}</option>
                                                    </select>
                                                    @error('status')
                                                        <span
                                                            class="error invalid-feedback d-block">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-lg-6 d-none" id="available_in_status">
                                                    <div>
                                                        <h4 class="f-size-14 ft-wt-5 rt-mb-20 lh-1">
                                                            {{ __('available_in') }}</h4>
                                                        <div
                                                            class="d-flex align-items-center form-control-icon date datepicker">
                                                            <input type="text" id="available_id_date"
                                                                name="available_in"
                                                                value="{{ old('available_in', date('d-m-Y', strtotime($candidate->available_in))) }}"
                                                                placeholder="dd/mm/yyyy"
                                                                class="form-control border-cutom @error('available_in') is-invalid @enderror">
                                                            <span class="input-group-addon input-group-text-custom">
                                                                <x-svg.calendar-icon />
                                                            </span>
                                                        </div>
                                                        @error('available_in')
                                                            <span
                                                                class="error invalid-feedback d-block">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                 <div class="col-lg-12 mb-3">
                                                    @php
                                                        $savedPreferredLocationsRaw = $candidate->preferred_job_locations ?? null;
                                                        $savedPreferredLocations = [];
                                                        if (is_string($savedPreferredLocationsRaw) && $savedPreferredLocationsRaw !== '') {
                                                            $decodedPreferredLocations = json_decode($savedPreferredLocationsRaw, true);
                                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedPreferredLocations)) {
                                                                $savedPreferredLocations = $decodedPreferredLocations;
                                                            } else {
                                                                $savedPreferredLocations = array_map('trim', explode(',', $savedPreferredLocationsRaw));
                                                            }
                                                        }
                                                        $selectedPreferredLocations = old('preferred_job_locations', $savedPreferredLocations);
                                                    @endphp

                                                    <label class="body-font-4 d-block text-gray-900 rt-mb-8">Preferred Job Location</label>
                                                    <select name="preferred_job_locations[]" class="select2-taggable w-100-p" multiple>
                                                        @foreach ($countries as $country)
                                                            <option value="{{ $country->name }}"
                                                                {{ in_array($country->name, $selectedPreferredLocations ?? [], true) ? 'selected' : '' }}>
                                                                {{ $country->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('preferred_job_locations')
                                                        <span class="error invalid-feedback d-block">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-lg-6 mb-3">
                                                    <x-forms.label :required="false" name="passport_no"
                                                        class="body-font-4 d-block text-gray-900 rt-mb-8" />
                                                    <x-forms.input type="text" name="passport_no"
                                                        value="{{ old('passport_no', $candidate->passport_no) }}"
                                                        placeholder="{{ __('passport_no') }}" />
                                                    @error('passport_no')
                                                        <span class="invalid-feedback d-block"
                                                            role="alert">{{ __($message) }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-lg-6 mb-3">
                                                    <x-forms.label :required="false" name="issue_date"
                                                        class="body-font-4 d-block text-gray-900 rt-mb-8" />
                                                    <x-forms.input type="date" name="passport_issue_date"
                                                        value="{{ old('passport_issue_date', $candidate->passport_issue_date ? date('Y-m-d', strtotime($candidate->passport_issue_date)) : '') }}" />
                                                    @error('passport_issue_date')
                                                        <span class="invalid-feedback d-block"
                                                            role="alert">{{ __($message) }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-lg-6 mb-3">
                                                    <x-forms.label :required="false" name="place_of_issue"
                                                        class="body-font-4 d-block text-gray-900 rt-mb-8" />
                                                    <x-forms.input type="text" name="passport_place_of_issue"
                                                        value="{{ old('passport_place_of_issue', $candidate->passport_place_of_issue) }}"
                                                        placeholder="{{ __('place_of_issue') }}" />
                                                    @error('passport_place_of_issue')
                                                        <span class="invalid-feedback d-block"
                                                            role="alert">{{ __($message) }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-lg-6 mb-3">
                                                    <x-forms.label :required="false" name="expiry_date"
                                                        class="body-font-4 d-block text-gray-900 rt-mb-8" />
                                                    <x-forms.input type="date" name="passport_expiry_date"
                                                        value="{{ old('passport_expiry_date', $candidate->passport_expiry_date ? date('Y-m-d', strtotime($candidate->passport_expiry_date)) : '') }}" />
                                                    @error('passport_expiry_date')
                                                        <span class="invalid-feedback d-block"
                                                            role="alert">{{ __($message) }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-lg-12 mb-3">
                                                    <x-forms.label :required="false" name="skills_you_have"
                                                        class="body-font-4 d-block text-gray-900 rt-mb-8" />
                                                    <select name="skills[]" class="select2-taggable w-100-p" multiple>
                                                        @foreach ($skills as $skill)
                                                            <option
                                                                {{ $candidate->skills ? (in_array($skill->id, $candidate->skills->pluck('id')->toArray()) ? 'selected' : '') : '' }}
                                                                value="{{ $skill->id }}">{{ $skill->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-lg-12 mb-3">
                                                    <x-forms.label :required="false" name="languages_you_know"
                                                        class="body-font-4 d-block text-gray-900 rt-mb-8" />
                                                     @php
                                                        $selectedLanguageIds = old('languages', $candidate->languages->pluck('id')->toArray());
                                                        $savedLanguageProficiencies = $candidate->languages
                                                            ->mapWithKeys(function ($language) {
                                                                return [
                                                                    $language->id => data_get($language, 'pivot.proficiency_level', 'basic'),
                                                                ];
                                                            })
                                                            ->toArray();
                                                        $languageProficiencies = old('language_proficiencies', $savedLanguageProficiencies);
                                                    @endphp
                                                    <select name="languages[]" id="candidate_languages_select" class="rt-selectactive w-100-p" multiple>
                                                         @foreach ($candidate_languages as $lang)
                                                             <option {{ in_array($lang->id, $selectedLanguageIds) ? 'selected' : '' }}
                                                                 value="{{ $lang->id }}">{{ $lang->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                      <div id="language_proficiency_container" class="mt-3">
                                                        @foreach ($candidate_languages as $lang)
                                                            @if (in_array($lang->id, $selectedLanguageIds))
                                                                @php
                                                                    $selectedProficiency = $languageProficiencies[$lang->id] ?? 'basic';
                                                                @endphp
                                                                <div class="row g-2 mb-2">
                                                                    <div class="col-lg-6">
                                                                        <input type="text" class="form-control" value="{{ $lang->name }}" readonly>
                                                                    </div>
                                                                    <div class="col-lg-6">
                                                                        <select name="language_proficiencies[{{ $lang->id }}]" class="form-control">
                                                                            <option value="basic" {{ $selectedProficiency === 'basic' ? 'selected' : '' }}>{{ __('basic') }}</option>
                                                                            <option value="intermediate" {{ $selectedProficiency === 'intermediate' ? 'selected' : '' }}>{{ __('intermediate') }}</option>
                                                                            <option value="fluent" {{ $selectedProficiency === 'fluent' ? 'selected' : '' }}>{{ __('fluent') }}</option>
                                                                            <option value="native" {{ $selectedProficiency === 'native' ? 'selected' : '' }}>{{ __('native') }}</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>

                                                <div class="col-lg-12 mb-3">
                                                    <x-forms.label :required="false" name="biography"
                                                        class="body-font-4 d-block text-gray-900 rt-mb-8" />
                                                    <textarea name="bio" id="image_ckeditor">{!! $candidate->bio !!}</textarea>
                                                    @error('bio')
                                                        <span class="text-danger">{{ __($message) }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-lg-12 mt-4">
                                                    <button type="submit" class="btn btn-primary">
                                                        {{ __('save_changes') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="dashboard-account-setting-item pb-0 mt-4">
                                        <h6 class="mb-3">{{ __('references') }}</h6>
                                        @foreach ($references as $reference)
                                            <form action="{{ route('candidate.references.update') }}" method="POST"
                                                class="border rounded p-3 mb-3">
                                                @csrf
                                                <input type="hidden" name="reference_id" value="{{ $reference->id }}">
                                                <div class="row">
                                                    <div class="col-lg-6 mb-3"><x-forms.label :required="true" name="name" /><x-forms.input type="text" name="name" value="{{ $reference->name }}" /></div>
                                                    <div class="col-lg-6 mb-3"><x-forms.label :required="true" name="designation" /><x-forms.input type="text" name="designation" value="{{ $reference->designation }}" /></div>
                                                    <div class="col-lg-6 mb-3"><x-forms.label :required="true" name="organization" /><x-forms.input type="text" name="organization" value="{{ $reference->organization }}" /></div>
                                                    <div class="col-lg-6 mb-3"><x-forms.label :required="false" name="email" /><x-forms.input type="email" name="email" value="{{ $reference->email }}" /></div>
                                                    <div class="col-lg-6 mb-3"><x-forms.label :required="false" name="relation" /><x-forms.input type="text" name="relation" value="{{ $reference->relation }}" /></div>
                                                    <div class="col-lg-6 mb-3"><x-forms.label :required="false" name="mobile" /><x-forms.input type="text" name="mobile" value="{{ $reference->mobile }}" /></div>
                                                    <div class="col-lg-6 mb-3"><x-forms.label :required="false" name="phone_off" /><x-forms.input type="text" name="phone_off" value="{{ $reference->phone_off }}" /></div>
                                                    <div class="col-lg-6 mb-3"><x-forms.label :required="false" name="phone_res" /><x-forms.input type="text" name="phone_res" value="{{ $reference->phone_res }}" /></div>
                                                    <div class="col-lg-12 mb-3"><x-forms.label :required="false" name="address" /><textarea name="address" class="form-control">{{ $reference->address }}</textarea></div>
                                                    <div class="col-lg-12 d-flex gap-2">
                                                        <button type="submit" class="btn btn-primary">{{ __('save_changes') }}</button>
                                                    </div>
                                                </div>
                                            </form>
                                            <form action="{{ route('candidate.references.destroy', $reference->id) }}" method="POST" class="mb-4">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('{{ __('are_you_sure_you_want_to_delete_this_item') }}')">{{ __('delete') }}</button>
                                            </form>
                                        @endforeach

                                        <form action="{{ route('candidate.references.store') }}" method="POST" class="border rounded p-3">
                                            @csrf
                                            <div class="row">
                                                <div class="col-lg-6 mb-3"><x-forms.label :required="true" name="name" /><x-forms.input type="text" name="name" value="{{ old('name') }}" /></div>
                                                <div class="col-lg-6 mb-3"><x-forms.label :required="true" name="designation" /><x-forms.input type="text" name="designation" value="{{ old('designation') }}" /></div>
                                                <div class="col-lg-6 mb-3"><x-forms.label :required="true" name="organization" /><x-forms.input type="text" name="organization" value="{{ old('organization') }}" /></div>
                                                <div class="col-lg-6 mb-3"><x-forms.label :required="false" name="email" /><x-forms.input type="email" name="email" value="{{ old('email') }}" /></div>
                                                <div class="col-lg-6 mb-3"><x-forms.label :required="false" name="relation" /><x-forms.input type="text" name="relation" value="{{ old('relation') }}" /></div>
                                                <div class="col-lg-6 mb-3"><x-forms.label :required="false" name="mobile" /><x-forms.input type="text" name="mobile" value="{{ old('mobile') }}" /></div>
                                                <div class="col-lg-6 mb-3"><x-forms.label :required="false" name="phone_off" /><x-forms.input type="text" name="phone_off" value="{{ old('phone_off') }}" /></div>
                                                <div class="col-lg-6 mb-3"><x-forms.label :required="false" name="phone_res" /><x-forms.input type="text" name="phone_res" value="{{ old('phone_res') }}" /></div>
                                                <div class="col-lg-12 mb-3"><x-forms.label :required="false" name="address" /><textarea name="address" class="form-control">{{ old('address') }}</textarea></div>
                                                <div class="col-lg-12">
                                                    <button type="submit" class="btn btn-outline-primary">{{ __('add') }} {{ __('references') }}</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                {{-- Experience & Education Setting  --}}
                                <div class="tab-pane fade {{ in_array(session('type'), ['experience', 'education'], true) ? 'show active' : '' }}"
                                    id="pills-experience" role="tabpanel" aria-labelledby="pills-experience-tab">
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <x-website.candidate.tab.candidate-experience-setting-tab :experiences="$candidate->experiences" :jobCategories="$job_categories" :skills="$skills" :experienceSkills="$candidate->experienceSkills" />
                                    <br>
                                    <x-website.candidate.tab.candidate-education-setting-tab :educations="$candidate->educations" />
                                </div>

                               

                                {{-- Extracariculler Activities  --}}
                                <div class="tab-pane fade {{ session('type') == 'extracariculler' ? 'show active' : '' }}"
                                    id="pills-extracariculler" role="tabpanel" aria-labelledby="pills-extracariculler-tab">
                                    <div class="dashboard-account-setting-item">
                                        <form action="{{ route('candidate.settingUpdate') }}" method="POST">
                                            @csrf
                                            @method('put')
                                            <input type="hidden" name="type" value="extracariculer">
                                            <div class="row">
                                                @forelse($extracurriculars as $extracurricular)
                                                    <div class="col-12 custom-select-padding">
                                                        <div class="d-flex tw-items-center">
                                                            <div class="d-flex mborder">
                                                                <div class="w-100">
                                                                    <input class="border-0" type="text" name="extracariculer[]"
                                                                        id=""
                                                                        placeholder="{{ __('extracariculer') }}..."
                                                                        value="{{ $extracurricular->activities }}">
                                                                    <textarea class="form-control mt-2" name="extracariculer_description[]" rows="3"
                                                                        placeholder="{{ __('extracurricular_description_placeholder') }}">{{ $extracurricular->description }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="tw-ms-2">
                                                                <button
                                                                    class="tw-w-12 tw-h-12 tw-border-0 tw-rounded tw-bg-[#F1F2F4] tw-inline-flex tw-justify-center tw-items-center"
                                                                    type="button" id="remove_item">
                                                                    <svg width="24" height="24"
                                                                        viewBox="0 0 24 24" fill="none"
                                                                        xmlns="http://www.w3.org/2000/svg">
                                                                        <path
                                                                            d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z"
                                                                            stroke="#18191C" stroke-width="1.5"
                                                                            stroke-miterlimit="10" />
                                                                        <path d="M15 9L9 15" stroke="#18191C"
                                                                            stroke-width="1.5" stroke-linecap="round"
                                                                            stroke-linejoin="round" />
                                                                        <path d="M15 15L9 9" stroke="#18191C"
                                                                            stroke-width="1.5" stroke-linecap="round"
                                                                            stroke-linejoin="round" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="col-12 custom-select-padding">
                                                        <div class="d-flex tw-items-center">
                                                            <div class="d-flex mborder">
                                                                <div class="w-100">
                                                                    <input class="border-0" type="text" name="extracariculer[]"
                                                                        id=""
                                                                        placeholder="{{ __('extracariculer') }}...">
                                                                    <textarea class="form-control mt-2" name="extracariculer_description[]" rows="3"
                                                                        placeholder="{{ __('extracurricular_description_placeholder') }}"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="tw-ms-2">
                                                                <button
                                                                    class="tw-w-12 tw-h-12 tw-border-0 tw-rounded tw-bg-[#F1F2F4] tw-inline-flex tw-justify-center tw-items-center"
                                                                    type="button" id="remove_item">
                                                                    <svg width="24" height="24"
                                                                        viewBox="0 0 24 24" fill="none"
                                                                        xmlns="http://www.w3.org/2000/svg">
                                                                        <path
                                                                            d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z"
                                                                            stroke="#18191C" stroke-width="1.5"
                                                                            stroke-miterlimit="10" />
                                                                        <path d="M15 9L9 15" stroke="#18191C"
                                                                            stroke-width="1.5" stroke-linecap="round"
                                                                            stroke-linejoin="round" />
                                                                        <path d="M15 15L9 9" stroke="#18191C"
                                                                            stroke-width="1.5" stroke-linecap="round"
                                                                            stroke-linejoin="round" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforelse
                                                <div id="multiple_feature_part">
                                                </div>
                                                <div class="col-12">
                                                    <button class="btn tw-bg-[#F1F2F4] w-100 mt-4 add-new-social"
                                                        onclick="add_new_extracariculer()" type="button">
                                                        <svg width="20" height="20" viewBox="0 0 20 20"
                                                            fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path
                                                                d="M10 17.5C14.1421 17.5 17.5 14.1421 17.5 10C17.5 5.85786 14.1421 2.5 10 2.5C5.85786 2.5 2.5 5.85786 2.5 10C2.5 14.1421 5.85786 17.5 10 17.5Z"
                                                                stroke="#18191C" stroke-width="1.5"
                                                                stroke-miterlimit="10" />
                                                            <path d="M6.875 10H13.125" stroke="#18191C" stroke-width="1.5"
                                                                stroke-linecap="round" stroke-linejoin="round" />
                                                            <path d="M10 6.875V13.125" stroke="#18191C" stroke-width="1.5"
                                                                stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                        <span>{{ __('add_new_extracariculer') }}</span>
                                                    </button>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary mt-4">
                                                {{ __('save_changes') }}
                                            </button>
                                    </div>

                                    </form>
                                </div>

                                 {{-- Social Setting  --}}
                                <div class="tab-pane fade {{ session('type') == 'social' ? 'show active' : '' }}"
                                    id="pills-social" role="tabpanel" aria-labelledby="pills-social-tab">
                                    <div class="dashboard-account-setting-item">
                                        <form action="{{ route('candidate.settingUpdate') }}" method="POST">
                                            @csrf
                                            @method('put')
                                            <input type="hidden" name="type" value="social">
                                            <div class="row">
                                                @forelse($socials as $social)
                                                    <div class="col-12 custom-select-padding">
                                                        <div class="d-flex tw-items-center">
                                                            <div class="d-flex mborder">
                                                                <div class="position-relative">
                                                                    <select class="w-100-p border-0 new-select form-control"
                                                                        name="social_media[]">
                                                                        <option value="" class="d-none" disabled>
                                                                            {{ __('select_one') }}</option>
                                                                        <option
                                                                            {{ $social->social_media == 'facebook' ? 'selected' : '' }}
                                                                            value="facebook">{{ __('facebook') }}</option>
                                                                        <option
                                                                            {{ $social->social_media == 'twitter' ? 'selected' : '' }}
                                                                            value="twitter">{{ __('twitter') }}</option>
                                                                        <option
                                                                            {{ $social->social_media == 'instagram' ? 'selected' : '' }}
                                                                            value="instagram">{{ __('instagram') }}
                                                                        </option>
                                                                        <option
                                                                            {{ $social->social_media == 'youtube' ? 'selected' : '' }}
                                                                            value="youtube">{{ __('youtube') }}</option>
                                                                        <option
                                                                            {{ $social->social_media == 'linkedin' ? 'selected' : '' }}
                                                                            value="linkedin">{{ __('linkedin') }}</option>
                                                                        <option
                                                                            {{ $social->social_media == 'pinterest' ? 'selected' : '' }}
                                                                            value="pinterest">{{ __('pinterest') }}
                                                                        </option>
                                                                        <option
                                                                            {{ $social->social_media == 'reddit' ? 'selected' : '' }}
                                                                            value="reddit">{{ __('reddit') }}</option>
                                                                        <option
                                                                            {{ $social->social_media == 'github' ? 'selected' : '' }}
                                                                            value="github">{{ __('github') }}</option>
                                                                        <option
                                                                            {{ $social->social_media == 'other' ? 'selected' : '' }}
                                                                            value="other">{{ __('other') }}</option>
                                                                    </select>
                                                                </div>
                                                                <div class="w-100">
                                                                    <input class="border-0" type="url" name="url[]"
                                                                        id=""
                                                                        placeholder="{{ __('profile_link_url') }}..."
                                                                        value="{{ $social->url }}">
                                                                </div>
                                                            </div>
                                                            <div class="tw-ms-2">
                                                                <button
                                                                    class="tw-w-12 tw-h-12 tw-border-0 tw-rounded tw-bg-[#F1F2F4] tw-inline-flex tw-justify-center tw-items-center"
                                                                    type="button" id="remove_item">
                                                                    <svg width="24" height="24"
                                                                        viewBox="0 0 24 24" fill="none"
                                                                        xmlns="http://www.w3.org/2000/svg">
                                                                        <path
                                                                            d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z"
                                                                            stroke="#18191C" stroke-width="1.5"
                                                                            stroke-miterlimit="10" />
                                                                        <path d="M15 9L9 15" stroke="#18191C"
                                                                            stroke-width="1.5" stroke-linecap="round"
                                                                            stroke-linejoin="round" />
                                                                        <path d="M15 15L9 9" stroke="#18191C"
                                                                            stroke-width="1.5" stroke-linecap="round"
                                                                            stroke-linejoin="round" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="col-12 custom-select-padding">
                                                        <div class="d-flex tw-items-center">
                                                            <div class="d-flex mborder">
                                                                <div class="position-relative">
                                                                    <select
                                                                        class="w-100-p border-0 new-select form-control"
                                                                        name="social_media[]">
                                                                        <option value="" class="d-none" disabled
                                                                            selected>{{ __('select_one') }}</option>
                                                                        <option value="facebook">{{ __('facebook') }}
                                                                        </option>
                                                                        <option value="twitter">{{ __('twitter') }}
                                                                        </option>
                                                                        <option value="instagram">{{ __('instagram') }}
                                                                        </option>
                                                                        <option value="youtube">{{ __('youtube') }}
                                                                        </option>
                                                                        <option value="linkedin">{{ __('linkedin') }}
                                                                        </option>
                                                                        <option value="pinterest">{{ __('pinterest') }}
                                                                        </option>
                                                                        <option value="reddit">{{ __('reddit') }}
                                                                        </option>
                                                                        <option value="github">{{ __('github') }}
                                                                        </option>
                                                                        <option value="other">{{ __('other') }}
                                                                        </option>
                                                                    </select>
                                                                </div>
                                                                <div class="w-100">
                                                                    <input class="border-0" type="url" name="url[]"
                                                                        id=""
                                                                        placeholder="{{ __('profile_link_url') }}...">
                                                                </div>
                                                            </div>
                                                            <div class="tw-ms-2">
                                                                <button
                                                                    class="tw-w-12 tw-h-12 tw-border-0 tw-rounded tw-bg-[#F1F2F4] tw-inline-flex tw-justify-center tw-items-center"
                                                                    type="button" id="remove_item">
                                                                    <svg width="24" height="24"
                                                                        viewBox="0 0 24 24" fill="none"
                                                                        xmlns="http://www.w3.org/2000/svg">
                                                                        <path
                                                                            d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z"
                                                                            stroke="#18191C" stroke-width="1.5"
                                                                            stroke-miterlimit="10" />
                                                                        <path d="M15 9L9 15" stroke="#18191C"
                                                                            stroke-width="1.5" stroke-linecap="round"
                                                                            stroke-linejoin="round" />
                                                                        <path d="M15 15L9 9" stroke="#18191C"
                                                                            stroke-width="1.5" stroke-linecap="round"
                                                                            stroke-linejoin="round" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforelse
                                                <div id="multiple_feature_part">
                                                </div>
                                                <div class="col-12">
                                                    <button class="btn tw-bg-[#F1F2F4] w-100 mt-4 add-new-social"
                                                        onclick="add_features_field()" type="button">
                                                        <svg width="20" height="20" viewBox="0 0 20 20"
                                                            fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path
                                                                d="M10 17.5C14.1421 17.5 17.5 14.1421 17.5 10C17.5 5.85786 14.1421 2.5 10 2.5C5.85786 2.5 2.5 5.85786 2.5 10C2.5 14.1421 5.85786 17.5 10 17.5Z"
                                                                stroke="#18191C" stroke-width="1.5"
                                                                stroke-miterlimit="10" />
                                                            <path d="M6.875 10H13.125" stroke="#18191C" stroke-width="1.5"
                                                                stroke-linecap="round" stroke-linejoin="round" />
                                                            <path d="M10 6.875V13.125" stroke="#18191C" stroke-width="1.5"
                                                                stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                        <span>{{ __('add_new_social_link') }}</span>
                                                    </button>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary mt-4">
                                                {{ __('save_changes') }}
                                            </button>
                                    </div>

                                    </form>
                                </div>

                                {{-- Account Setting  --}}
                                <div class="tab-pane fade {{ session('type') == 'alert' || session('type') == 'contact' || session('type') == 'account' || session('type') == 'visibility' || session('type') == 'password' || session('type') == 'account-delete' ? 'show active' : '' }} {{ error('password', 'show active') }}"
                                    id="pills-setting" role="tabpanel" aria-labelledby="pills-setting-tab">
                                    <form action="{{ route('candidate.settingUpdate') }}" method="POST">
                                        @csrf
                                        @method('put')
                                        <input type="hidden" name="type" value="contact">
                                        <div class="dashboard-account-setting-item pb-0">
                                            <h6>{{ __('location') }}</h6>
                                            @if (config('templatecookie.map_show'))
                                                <div class="row">

                                                    <div class="col-lg-12 mb-3">
                                                        <x-website.map.map-warning />
                                                        @php
                                                            $map = $setting->default_map;
                                                        @endphp
                                                        <div id="google-map-div"
                                                            class="{{ $map == 'google-map' ? '' : 'd-none' }}">
                                                            <input id="searchInput" class="mapClass" type="text"
                                                                placeholder="Enter a location">
                                                            <div class="map mymap" id="google-map"></div>
                                                        </div>
                                                        <div class="{{ $map == 'leaflet' ? '' : 'd-none' }}">
                                                            <input type="text" autocomplete="off" id="leaflet_search"
                                                                placeholder="{{ __('enter_city_name') }}"
                                                                class="full-width placeholder:tw-normal-case"
                                                                value="{{ $candidate->exact_location ? $candidate->exact_location : $candidate->full_address }}" />
                                                            <br>
                                                            <div id="leaflet-map"></div>
                                                        </div>
                                                        @error('location')
                                                            <span class="ml-3 text-md text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                @php
                                                    $session_location = session()->get('location');
                                                    $session_country = $session_location && array_key_exists('country', $session_location) ? $session_location['country'] : '-';
                                                    $session_exact_location = $session_location && array_key_exists('exact_location', $session_location) ? $session_location['exact_location'] : '-';

                                                    $candidate_country = $candidate->country;
                                                    $candidate_exact_location = $candidate->exact_location;
                                                @endphp
                                                <div class="card-footer row mt-4 border-0">
                                                    <span>
                                                        <img src="{{ asset('frontend/assets/images/loader.gif') }}"
                                                            alt="loading" width="50px" height="50px"
                                                            class="loader_position d-none">
                                                    </span>
                                                    <div class="location_secion">
                                                        {{ __('country') }}: <span
                                                            class="location_country">{{ $candidate_country ?: $session_country }}</span>
                                                        <br>
                                                        {{ __('full_address') }}: <span
                                                            class="location_full_address">{{ $candidate_exact_location ?: $session_exact_location }}</span>
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
                                                        'selectedCountryId' => $candidate->country,
                                                        'selectedStateId' => $candidate->region,
                                                        'selectedCityId' => $candidate->district,
                                                    ]);
                                                @endphp
                                                @livewire('country-state-city')
                                            @endif
                                        </div>
                                        <div class="dashboard-account-setting-item">
                                            <h6>{{ __('your_contact_information') }}</h6>
                                            <div class="row">
                                                <div class="col-lg-6 mb-3">
                                                    <x-forms.label :required="false" name="phone"
                                                        class="pointer body-font-4 d-block text-gray-900 rt-mb-8" />
                                                    <x-forms.input type="text" name="phone"
                                                        value="{{ $contact->phone }}" id="phone"
                                                        placeholder="{{ __('phone') }}" class="phonecode" />
                                                </div>
                                                <div class="col-lg-6 mb-3">
                                                    <x-forms.label :required="false" name="secondary_phone"
                                                        class="pointer body-font-4 d-block text-gray-900 rt-mb-8" />
                                                    <x-forms.input type="text" name="secondary_phone"
                                                        value="{{ $contact->secondary_phone }}" id="phone2"
                                                        placeholder="{{ __('phone') }}" class="phonecode" />
                                                </div>
                                                <div class="col-lg-6 mb-3">
                                                    <x-forms.label :required="false" name="whatsapp_number"
                                                        class="pointer body-font-4 d-block text-gray-900 rt-mb-8" />
                                                    <x-forms.input type="text" name="whatsapp_number"
                                                        value="{{ $candidate->whatsapp_number }}" id="whatsapp_number"
                                                        placeholder="{{ __('whatsapp_number') }}" class="phonecode" />
                                                </div>
                                                <div class="col-lg-6 mb-3">
                                                    <x-forms.label :required="false" name="email"
                                                        class="pointer body-font-4 d-block text-gray-900 rt-mb-8" />
                                                    <div class="fromGroup has-icon2">
                                                        <div class="form-control-icon">
                                                            <x-forms.input type="email" name="email"
                                                                value="{{ $contact->email }}" id=""
                                                                placeholder="{{ __('email_address') }}"
                                                                class="" />
                                                            <div class="icon-badge-2">
                                                                <x-svg.envelope-icon />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary mt-4">
                                                {{ __('save_changes') }}
                                            </button>
                                        </div>

                                    </form>

                                    <hr>
                                    <form action="{{ route('candidate.settingUpdate') }}" method="POST">
                                        @csrf
                                        @method('put')
                                        <input type="hidden" name="type" value="account">
                                        <div class="dashboard-account-setting-item">
                                            <h6>{{ __('change_account_email') }} </h6>
                                            <div class="row tw-mb-8">
                                                <div class="col-lg-6 mt-2">
                                                    <x-forms.label :required="true" name="email"
                                                        class="f-size-14 text-gray-700 rt-mb-8" />
                                                    <div class="fromGroup rt-mb-15">
                                                        <input name="account_email" value="{{ auth()->user()->email }}"
                                                            class="form-control @error('account_email') is-invalid @enderror"
                                                            id="account_email" type="email"
                                                            placeholder="{{ __('email_address') }}" required>

                                                    </div>
                                                    @error('account_email')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                @if (session('requested_email'))
                                                    <small> Your email address {{ session('requested_email') }} is
                                                        unverified . Check you email </small>
                                                @endif


                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                {{ __('update_email') }}
                                            </button>
                                        </div>
                                    </form>

                                    <hr>
                                    <div class="dashboard-account-setting-item setting-border">
                                        {{-- <h6>{{ __('notification') }}</h6> --}}
                                        {{-- <form action="{{ route('candidate.settingUpdate') }}" --}}
                                        {{-- <form id="alert" action="{{ route('candidate.settingUpdate') }}" --}}

                                        <div class="row">
                                            <form id="alert" action="{{ route('candidate.settingUpdate') }}"
                                                method="POST">
                                                @csrf
                                                @method('put')
                                                <input type="hidden" name="type" value="alert">
                                                <input type="hidden" name="alert_type" value="status">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6>{{ __('job_alert') }}</h6>
                                                    <div class="input-group-text bg-transparent border-0"
                                                        id="basic-addon1">
                                                        <div class="form-check form-switch">
                                                            <input type="hidden" value="0"
                                                                name="received_job_alert">
                                                            <input name="received_job_alert" class="form-check-input"
                                                                type="checkbox" id="flexSwitchCheckDefault"
                                                                value="1"
                                                                {{ $candidate->received_job_alert ? 'checked' : '' }}>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                            <form action="{{ route('candidate.settingUpdate') }}" method="POST">
                                                @csrf
                                                @method('put')
                                                <input type="hidden" name="type" value="alert">
                                                <input type="hidden" name="alert_type" value="role">
                                                <div class="col-lg-12">
                                                    <x-forms.label :required="false" name="choose_job_role"
                                                        class="f-size-14 text-gray-700" />
                                                    <div>
                                                        <div class="tw-flex tw-justify-between tw-gap-3">
                                                            <select class="select2-taggable w-100-p" multiple
                                                                name="job_roles[]">
                                                                @foreach ($job_roles as $job_role)
                                                                    <option
                                                                        {{ $candidate->jobRoleAlerts && count($candidate->jobRoleAlerts) ? (in_array($job_role->id, $candidate->jobRoleAlerts->pluck('job_role_id')->toArray()) ? 'selected' : '') : '' }}
                                                                        value="{{ $job_role->id }}">
                                                                        {{ $job_role->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            <div>
                                                                <button type="submit" class="btn btn-primary">
                                                                    {{ __('save_changes') }}
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <br>
                                                        <p>
                                                            [{{ __('note_you_will_be_notified_for_this_role_only') }}]
                                                        </p>
                                                        <div class="form-control-icon">
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="dashboard-account-setting-item setting-border">
                                        <form id="visibility" action="{{ route('candidate.settingUpdate') }}"
                                            method="POST">
                                            @csrf
                                            @method('put')
                                            <input type="hidden" name="type" value="visibility">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <label
                                                        class="text-gray-900 rt-mb-15 fw-medium">{{ __('profile_privacy') }}</label>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-text bg-transparent border border-gray-50 extra-design"
                                                            id="basic-addon1">
                                                            <div class="form-check form-switch">
                                                                <input name="profile_visibility" class="form-check-input"
                                                                    type="checkbox" id="flexSwitchCheckDefault"
                                                                    {{ $candidate->visibility ? 'checked' : '' }}>
                                                                <span
                                                                    class="form-check-label f-size-14">{{ __('yes') }}</span>
                                                            </div>
                                                        </div>
                                                        <input disabled type="text" class="form-control"
                                                            placeholder="{{ __('your_profile_is_now_status', ['status' => $candidate->visibility ? __('public') : __('private')]) }}"
                                                            id="msalary">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <x-forms.label :required="false" name="resume_privacy"
                                                        class="text-gray-900 rt-mb-15 fw-medium" />
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-text bg-transparent border border-gray-50 extra-design"
                                                            id="basic-addon1">
                                                            <div class="form-check form-switch">
                                                                <input name="cv_visibility" class="form-check-input"
                                                                    type="checkbox" id="flexSwitchCheckDefault"
                                                                    {{ $candidate->cv_visibility ? 'checked' : '' }}>
                                                                <span
                                                                    class="form-check-label f-size-14">{{ __('yes') }}</span>
                                                            </div>
                                                        </div>
                                                        <input disabled type="text" class="form-control"
                                                            placeholder="{{ __('your_resume_is_now_status', ['status' => $candidate->cv_visibility ? __('public') : __('private')]) }}"
                                                            id="msalary">
                                                    </div>

                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="dashboard-account-setting-item setting-border">
                                        <h6>{{ __('change_password') }}</h6>
                                        <form action="{{ route('candidate.settingUpdate') }}" method="POST">
                                            @csrf
                                            @method('put')
                                            <input type="hidden" name="type" value="password">
                                            <div class="row">
                                                <div class="col-lg-6 rt-mb-32">
                                                    <x-forms.label :required="true" name="new_password"
                                                        class="f-size-14 text-gray-700 rt-mb-6" />
                                                    <div class="fromGroup rt-mb-15">
                                                        <div class="d-flex">
                                                            <input name="password"
                                                                class="form-control @error('password') is-invalid @enderror"
                                                                id="password-hide_show" type="password"
                                                                placeholder="{{ __('password') }}" required>
                                                            <div
                                                                class="has-badge @error('password') has-badge-cutom @enderror">
                                                                <i class="ph-eye @error('password') m-3 @enderror"></i>
                                                            </div>
                                                        </div>
                                                        @error('password')
                                                            <span role="alert"
                                                                class="text-danger">{{ __($message) }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 rt-mb-32">
                                                    <x-forms.label :required="true" name="confirm_password"
                                                        class="f-size-14 text-gray-700 rt-mb-6" />
                                                    <div class="fromGroup rt-mb-15">
                                                        <input name="password_confirmation"
                                                            class="form-control @error('password_confirmation') is-invalid @enderror"
                                                            id="password-hide_show1" type="password"
                                                            placeholder="{{ __('confirm_password') }}" required>
                                                        <div
                                                            class="has-badge @error('password') has-badge-cutom @enderror select-icon__one">
                                                            <i class="ph-eye"></i>
                                                        </div>
                                                        @error('password_confirmation')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div>
                                                    <button type="submit" class="btn btn-primary">
                                                        {{ __('save_changes') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="dashboard-account-setting-item setting-border">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <h4>{{ __('close_delete_account') }}</h4>
                                                <p>{{ __('account_delete_msg') }}</p>
                                                <form action="{{ route('candidate.settingUpdate') }}" id="AccountDelete"
                                                    method="POST">
                                                    @csrf
                                                    @method('put')
                                                    <input type="hidden" name="type" value="account-delete">
                                                    <button type="button" onclick="AccountDelete()"
                                                        class="btn p-0 text-danger-500">
                                                        <span class="button-content-wrapper ">
                                                            <span class="button-icon">
                                                                <i class="ph-x-circle"></i>
                                                            </span>
                                                            <span class="button-text">
                                                                {{ __('close_account') }}
                                                            </span>
                                                        </span>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-footer text-center body-font-4 text-gray-500">
            <x-website.footer-copyright />
        </div>
    </div>

    {{-- Resume add modal --}}
    <div class="modal fade" id="resumeModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog tw-max-w-[536px]">
            <div class="modal-content">
                <form action="{{ route('candidate.resume.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <h5 class="tw-text-lg tw-text-[#18191C] tw-font-semibold tw-mb-[18px]" id="cvModalLabel">
                            {{ __('add_cv_resume') }}</h5>
                        <div class="from-group py-2">
                            <x-forms.label name="cv_resume_name" :required="true"
                                class="tw-mb-2 tw-text-sm tw-text-[#18191C]" />
                            <input type="text" name="resume_name" id="">
                            @error('is_remote')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group tw-mb-6">
                            <x-forms.label name="upload_cv_resume" class="tw-mb-2 tw-text-sm tw-text-[#18191C]" />
                            <div class="cv-image-upload-wrap">
                                <input name="resume_file" class="resume-file-upload-input" type="file"
                                    onchange="resumeManageReadURL(this, 'add');" accept="application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                    id="resume_add_input" />
                                <div class="drag-text">
                                    <x-svg.upload-icon />
                                    <h3>{{ __('browse_file') }}</h3>
                                    <p>{{ __('available_format') }} - pdf,doc,docx<br>
                                        {{ __('maximum_file_size') }} - 5 MB</p>
                                </div>
                            </div>
                            <div class="resume-file-upload-content none ">
                                <div class="wrap">
                                    <x-svg.file-icon2 />
                                    <h3 class="resume_selected_file_name">file</h3>
                                    <p>
                                        <span><span class="resume_selected_file_size">2.3</span> MB</span> <br>
                                        <span class="resume_selected_file_type">.pdf</span>
                                    </p>
                                    <div class="image-title-wrap">
                                        <button type="button" class="cv-remove-image">
                                            <x-svg.trash-icon />
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="tw-flex tw-justify-between">
                            <button type="button" class="bg-priamry-50 btn btn-primary-50" data-bs-dismiss="modal"
                                aria-label="Close">{{ __('cancel') }}</button>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <span class="button-content-wrapper ">
                                    <span class="button-icon align-icon-right"><i class="ph-arrow-right"></i></span>
                                    <span class="button-text">
                                        {{ __('add_cv_resume') }}
                                    </span>
                                </span>
                            </button>
                        </div>
                        <button type="button"
                            class="tw-rounded-full tw-flex tw-items-center tw-justify-center tw-p-3 tw-absolute -tw-top-[25px] -tw-right-[25px] tw-bg-white tw-border-2 tw-border-[#E7F0FA]"
                            data-bs-dismiss="modal" aria-label="Close">
                            <x-svg.modal-cross-icon />
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- Resume edit modal --}}
    <div class="modal fade" id="resumeEditModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog tw-max-w-[536px]">
            <div class="modal-content">
                <form action="{{ route('candidate.resume.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="resume_id" id="resume_id_input">
                    <div class="modal-body">
                        <h5 class="tw-text-lg tw-text-[#18191C] tw-font-semibold tw-mb-[18px]" id="cvModalLabel">
                            {{ __('update_cv_resume') }}</h5>
                        <div class="from-group py-2">
                            <x-forms.label name="cv_resume_name" :required="true"
                                class="tw-mb-2 tw-text-sm tw-text-[#18191C]" />
                            <input type="text" name="resume_name" id="resume_name_input">
                            @error('is_remote')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group tw-mb-6">
                            <x-forms.label name="upload_cv_resume" class="tw-mb-2 tw-text-sm tw-text-[#18191C]" />
                            <div class="cv-image-upload-wrap">
                                <input name="resume_file" class="resume-file-upload-input" type="file"
                                    onchange="resumeManageReadURL(this, 'edit');" accept="application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                    id="resume_edit_input" />
                                <div class="drag-text">
                                    <x-svg.upload-icon />
                                    <h3>{{ __('change_file') }}</h3>
                                    <p>{{ __('current_resume_size') }}: <span id="resume_file_size"></span></p>
                                </div>
                            </div>
                            <div class="resume-file-upload-content none ">
                                <div class="wrap">
                                    <x-svg.file-icon2 />
                                    <h3 class="resume_selected_file_name">file</h3>
                                    <p>
                                        <span><span class="resume_selected_file_size">2.3</span> MB</span> <br>
                                        <span class="resume_selected_file_type">.pdf</span>
                                    </p>
                                    <div class="image-title-wrap">
                                        <button type="button" class="cv-remove-image">
                                            <x-svg.trash-icon />
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="tw-flex tw-justify-between">
                            <button type="button" class="bg-priamry-50 btn btn-primary-50" data-bs-dismiss="modal"
                                aria-label="Close">{{ __('cancel') }}</button>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <span class="button-content-wrapper ">
                                    <span class="button-icon align-icon-right"><i class="ph-arrow-right"></i></span>
                                    <span class="button-text">
                                        {{ __('add_cv_resume') }}
                                    </span>
                                </span>
                            </button>
                        </div>
                        <button type="button"
                            class="tw-rounded-full tw-flex tw-items-center tw-justify-center tw-p-3 tw-absolute -tw-top-[25px] -tw-right-[25px] tw-bg-white tw-border-2 tw-border-[#E7F0FA]"
                            data-bs-dismiss="modal" aria-label="Close">
                            <x-svg.modal-cross-icon />
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- Add Education Modal --}}
    <div class="modal fade" id="addEducationModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('candidate.educations.store') }}" style="padding:20px" method="POST">
                @csrf

                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="body-font-4 d-block text-gray-900 rt-mb-8">
                            {{ __('do_you_have_institutional_accreditation') }}
                        </label>
                        <div class="d-flex gap-3">
                            <label><input type="radio" name="is_institute_accredited" value="1"> {{ __('yes') }}</label>
                            <label><input type="radio" name="is_institute_accredited" value="0"> {{ __('no') }}</label>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-3">
                    <x-forms.label name="exam_name" class="rt-mb-8" />
                    <select name="exam_name" class="select2-taggable edu-modal-select w-100-p" required>
                        <option value="">{{ __('select_one') }}</option>
                        <option>PSC / 5 pass</option>
                        <option>JSC/JDC/8 pass</option>
                        <option>Secondary</option>
                        <option>Higher Secondary</option>
                        <option>Diploma</option>
                        <option>Bachelor/Honors</option>
                        <option>Masters</option>
                        <option>PhD (Doctor of Philosophy)</option>
                    </select>
                    </div>

                    <div class="col-lg-6 mb-3">
                        <x-forms.label name="degree_name" class="rt-mb-8" />
                        <input type="text" name="degree_name" class="form-control"
                            value="{{ old('degree_name') }}" placeholder="{{ __('type_degree_name') }}">
                    </div>

                    <div class="col-lg-6 mb-3">
                    <x-forms.label name="major_subject" class="rt-mb-8" />
                    <input type="text" name="major_subject" class="form-control" placeholder="{{ __('education_major_placeholder') }}">
                    </div>

                    <div class="col-lg-6 mb-3">
                        <x-forms.label name="institute_name" class="rt-mb-8" />
                        <input type="text" name="institute_name" class="form-control"
                            value="{{ old('institute_name') }}" placeholder="{{ __('type_institute_name') }}" required>

                    </div>

                    <div class="col-lg-4 mb-3">
                    <x-forms.label name="passing_year" class="rt-mb-8" />
                    <input type="text" name="passing_year" class="form-control" placeholder="2020">
                    </div>

                    <div class="col-lg-4 mb-3">
                    <x-forms.label name="result_type" class="rt-mb-8" />
                    <select name="result_type" class="form-control edu-modal-select education-result-type">
                        <option value="">{{ __('select_one') }}</option>
                        <option value="gpa_5">{{ __('gpa_5') }}</option>
                        <option value="cgpa_4">{{ __('cgpa_4') }}</option>
                        <option value="percentage">{{ __('percentage') }}</option>
                        <option value="other">{{ __('other_result') }}</option>
                    </select>
                    </div>

                    <div class="col-lg-4 mb-3">
                    <x-forms.label name="result" class="rt-mb-8" />
                    <input type="number" step="0.01" min="0" name="result" class="form-control education-result-input" placeholder="5.00">
                    </div>

                    <div class="col-lg-4 mb-3">
                    <x-forms.label name="board" class="rt-mb-8" />
                    <input type="text" name="board" class="form-control" placeholder="{{ __('education_board_placeholder') }}">
                    </div>

                    <div class="col-12 mb-3">
                        <x-forms.label name="skills" class="rt-mb-8" />

                        <select name="skills[]" class="select2-taggable edu-modal-select w-100-p" multiple>
                            @foreach($skills as $skill)
                                <option value="{{ $skill->id }}"
                                    {{ in_array($skill->id, old('skills', [])) ? 'selected' : '' }}>
                                    {{ $skill->name }}
                                </option>
                            @endforeach
                        </select>

                    </div>
                </div>

                <button class="btn btn-primary">{{ __('add_education') }}</button>
                </form>

                <button type="button" class="btn-close" onclick="closeAddEducationModal()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M18.75 5.25L5.25 18.75" stroke="var(--primary-500)" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M18.75 18.75L5.25 5.25" stroke="var(--primary-500)" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>
    </div>


    {{-- Edit Education Modal (NEW) --}}
    <div class="modal fade" id="editEducationModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('candidate.educations.update') }}" style="padding:20px" method="POST">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="education_id" id="education-modal-id">

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="body-font-4 d-block text-gray-900 rt-mb-8">
                                {{ __('do_you_have_institutional_accreditation') }}
                            </label>
                            <div class="d-flex gap-3">
                                <label>
                                    <input type="radio" name="is_institute_accredited" id="edu-accredit-yes" value="1">
                                    {{ __('yes') }}
                                </label>
                                <label>
                                    <input type="radio" name="is_institute_accredited" id="edu-accredit-no" value="0">
                                    {{ __('no') }}
                                </label>
                            </div>
                        </div>

                        <div class="col-lg-6 mb-3">
                            <x-forms.label name="exam_name" class="rt-mb-8" />
                            <select name="exam_name" id="education-modal-exam" class="select2-taggable edu-modal-select w-100-p" required>
                                <option value="">{{ __('select_one') }}</option>
                                <option>PSC / 5 pass</option>
                                <option>JSC/JDC/8 pass</option>
                                <option>Secondary</option>
                                <option>Higher Secondary</option>
                                <option>Diploma</option>
                                <option>Bachelor/Honors</option>
                                <option>Masters</option>
                                <option>PhD (Doctor of Philosophy)</option>
                            </select>
                        </div>

                        <div class="col-lg-6 mb-3">
                            <x-forms.label name="degree_name" class="rt-mb-8" />
                            <input type="text" name="degree_name" id="education-modal-degree-name"
                                class="form-control" placeholder="{{ __('type_degree_name') }}">
                        </div>

                        <div class="col-lg-6 mb-3">
                            <x-forms.label name="major_subject" class="rt-mb-8" />
                            <input type="text" name="major_subject" id="education-modal-major"
                                class="form-control"
                                placeholder="{{ __('education_major_placeholder') }}">
                        </div>

                        <div class="col-lg-6 mb-3">
                            <x-forms.label name="institute_name" class="rt-mb-8" />
                            <input type="text" name="institute_name" id="education-modal-inst"
                                class="form-control" placeholder="{{ __('type_institute_name') }}" required>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <x-forms.label name="passing_year" class="rt-mb-8" />
                            <input type="text" name="passing_year" id="education-modal-year"
                                class="year_picker form-control" placeholder="2020">
                        </div>

                        <div class="col-lg-4 mb-3">
                            <x-forms.label name="result_type" class="rt-mb-8" />
                            <select name="result_type" id="education-modal-result-type" class="form-control edu-modal-select education-result-type">
                                <option value="">{{ __('select_one') }}</option>
                                <option value="gpa_5">{{ __('gpa_5') }}</option>
                                <option value="cgpa_4">{{ __('cgpa_4') }}</option>
                                <option value="percentage">{{ __('percentage') }}</option>
                                <option value="other">{{ __('other_result') }}</option>
                            </select>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <x-forms.label name="result" class="rt-mb-8" />
                            <input type="number" step="0.01" min="0" name="result" id="education-modal-result"
                                class="form-control education-result-input" placeholder="5.00">
                        </div>

                        <div class="col-lg-4 mb-3">
                            <x-forms.label name="board" class="rt-mb-8" />
                            <input type="text" name="board" id="education-modal-board"
                                class="form-control" placeholder="{{ __('education_board_placeholder') }}">
                        </div>

                        <div class="col-12 mb-3">
                            <x-forms.label name="skills" class="rt-mb-8" />
                            <select name="skills[]" id="education-modal-skills"
                                    class="select2-taggable edu-modal-select w-100-p" multiple>
                                @foreach($skills as $skill)
                                    <option value="{{ $skill->id }}">{{ $skill->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="d-flex tw-flex-wrap tw-gap-4 justify-content-between">
                        <button type="button" class="bg-priamry-50 btn btn-primary-50"
                            onclick="closeEditEducationModal()">{{ __('cancel') }}</button>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <span class="button-content-wrapper ">
                                <span class="button-icon align-icon-right"><i class="ph-arrow-right"></i></span>
                                <span class="button-text">{{ __('update_education') }}</span>
                            </span>
                        </button>
                    </div>
                </form>

                <button type="button" class="btn-close" onclick="closeEditEducationModal()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M18.75 5.25L5.25 18.75" stroke="var(--primary-500)" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M18.75 18.75L5.25 5.25" stroke="var(--primary-500)" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>
    </div>


    {{-- Add Experience Modal --}}
    <div class="modal fade" id="addExperienceModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('candidate.experiences.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <h5 class="modal-title rt-mb-18 f-size-18" id="cvModalLabel">{{ __('add_experience') }}</h5>
                        <div class="from-group rt-mb-18">
                            <x-forms.label name="company" class="rt-mb-8" />
                            <input type="text" name="company" required class="@error('company') is-invalid @enderror"
                                placeholder="{{ __('enter') }} {{ __('company') }}">

                            @error('company')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="row rt-mb-18">
                            <div class="col-lg-6">
                                <x-forms.label name="department" class="rt-mb-8" />
                                <input type="text" name="department" required
                                    placeholder="{{ __('enter') }} {{ __('department') }}">
                            </div>
                            <div class="col-lg-6">
                                <x-forms.label name="designation" class="rt-mb-8" />
                                <input type="text" name="designation" required
                                    placeholder="{{ __('enter') }} {{ __('designation') }}">
                            </div>
                        </div>
                        <div class="row rt-mb-18">
                            <div class="col-lg-6">
                                <x-forms.label name="start_date" class="rt-mb-8" />
                                <input type="text" name="start" value="{{ old('start') }}" placeholder="yyyy-mm-dd"
                                    class="date_picker form-control border-cutom @error('start') is-invalid @enderror"
                                    required>
                            </div>
                            <div class="col-lg-6 experience_end_date">
                                <x-forms.label name="end_date" class="rt-mb-8" />
                                <input type="text" name="end" value="{{ old('end') }}" placeholder="yyyy-mm-dd"
                                    class="date_picker form-control border-cutom @error('end') is-invalid @enderror">
                            </div>
                        </div>
                        <div class="from-group d-flex gap-2 align-items-center rt-mb-24 custom-checkbox">
                            <input type="checkbox" name="currently_working" id="experience-modal-checkbox_create"
                                value="1">
                            <x-forms.label name="i_am_currently_working" for="experience-modal-checkbox_create"
                                :required="false" class="!tw-mb-0 tw-cursor-pointer" />
                        </div>
                        <div class="row rt-mb-18">
                            <div class="col-lg-12">
                                <x-forms.label name="responsibilities" class="rt-mb-8" :required="false" />
                                <textarea class="form-control @error('responsibilities') is-invalid @enderror"
                                    placeholder="{{ __('enter') }} {{ __('responsibilities') }}" name="responsibilities" rows="5"></textarea>
                            </div>
                        </div>
                        <div class="d-flex tw-flex-wrap tw-gap-4 justify-content-between">
                            <button type="button" class="bg-priamry-50 btn btn-primary-50"
                                onclick="closeAddExperienceModal()">{{ __('cancel') }}</button>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <span class="button-content-wrapper ">
                                    <span class="button-icon align-icon-right"><i class="ph-arrow-right"></i></span>
                                    <span class="button-text">
                                        {{ __('add_experience') }}
                                    </span>
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
                <button type="button" class="btn-close" onclick="closeAddExperienceModal()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M18.75 5.25L5.25 18.75" stroke="var(--primary-500)" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M18.75 18.75L5.25 5.25" stroke="var(--primary-500)" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>
    </div>


    {{-- Edit Experience Modal --}}
    <div class="modal fade" id="editExperienceModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('candidate.experiences.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <h5 class="modal-title rt-mb-18 f-size-18" id="cvModalLabel">{{ __('edit_experience') }}</h5>
                        <input type="hidden" name="experience_id" id="experience-modal-id">
                        <div class="from-group rt-mb-18">
                            <x-forms.label name="company" class="rt-mb-8" />
                            <input id="experience-modal-company" type="text" name="company" required
                                placeholder="{{ __('enter') }} {{ __('company') }}">
                        </div>
                        <div class="row rt-mb-18">
                            <div class="col-lg-6">
                                <x-forms.label name="department" class="rt-mb-8" />
                                <input id="experience-modal-department" type="text" name="department" required
                                    placeholder="{{ __('enter') }} {{ __('department') }}">
                            </div>
                            <div class="col-lg-6">
                                <x-forms.label name="designation" class="rt-mb-8" />
                                <input id="experience-modal-designation" type="text" name="designation" required
                                    placeholder="{{ __('enter') }} {{ __('designation') }}">
                            </div>
                        </div>
                        <div class="row rt-mb-18">
                            <div class="col-lg-6">
                                <x-forms.label name="start_date" class="rt-mb-8" />
                                <input id="experience-modal-start" type="text" name="start"
                                    value="{{ old('start') }}" placeholder="yyyy-mm-dd"
                                    class="date_picker form-control border-cutom @error('start') is-invalid @enderror"
                                    required>
                            </div>
                            <div class="col-lg-6 experience_end_date">
                                <x-forms.label name="end_date" class="rt-mb-8" :required="false" />
                                <input id="experience-modal-end" type="text" name="end"
                                    value="{{ old('end') }}" placeholder="yyyy-mm-dd"
                                    class="date_picker form-control border-cutom @error('end') is-invalid @enderror">
                            </div>
                        </div>
                        <div class="from-group d-flex gap-2 align-items-center rt-mb-24">
                            <input type="checkbox" name="currently_working" id="experience-modal-checkbox_edit"
                                value="1">
                            <x-forms.label name="i_am_currently_working" for="experience-modal-checkbox_edit"
                                :required="false" class="!tw-mb-0 !tw-cursor-pointer" />
                        </div>
                        <div class="row rt-mb-18">
                            <div class="col-lg-12">
                                <x-forms.label name="responsibilities" class="rt-mb-8" :required="false" />
                                <textarea id="experience-responsibilities" class="form-control @error('responsibilities') is-invalid @enderror"
                                    placeholder="{{ __('enter') }} {{ __('responsibilities') }}" name="responsibilities" rows="5"></textarea>
                            </div>
                        </div>
                        <div class="d-flex tw-flex-wrap tw-gap-4 justify-content-between">
                            <button type="button" class="bg-priamry-50 btn btn-primary-50"
                                onclick="closeEditExperienceModal()">{{ __('cancel') }}</button>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <span class="button-content-wrapper ">
                                    <span class="button-icon align-icon-right"><i class="ph-arrow-right"></i></span>
                                    <span class="button-text">
                                        {{ __('update_experience') }}
                                    </span>
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
                <button type="button" class="btn-close" onclick="closeEditExperienceModal()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path d="M18.75 5.25L5.25 18.75" stroke="var(--primary-500)" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M18.75 18.75L5.25 5.25" stroke="var(--primary-500)" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
@endsection

@section('frontend_links')
    <link rel="stylesheet" href="{{ asset('frontend') }}/assets/css/bootstrap-datepicker.min.css">
    <!-- >=>Leaflet Map<=< -->
    <x-map.leaflet.map_links />
    <x-map.leaflet.autocomplete_links />
    @include('map::links')
    <style>
        .ck-editor__editable_inline {
            min-height: 300px;
        }

        .w-100-percent {
            width: 100% !important;
        }

        #jobrole #basic-addon1 {
            width: 50px !important;
            margin-left: 28px !important;
        }

        .border-cutom {
            border-radius: 5px 0 0 5px !important;
        }

        .input-group-text-custom {
            max-height: 48px;
            padding: 12px;
            background-color: #e9ecef;
            border-radius: 0 5px 5px 0;
        }

        .has-badge-cutom {
            top: 34% !important;
        }

        .mymap {
            border-radius: 12px;
            z-index: 999;
        }

        /* শুধুমাত্র signature অংশের জন্য override */
        .signature-upload .profile-file-upload-content,
        .signature-upload .profile-file-upload-content2 {
            max-width: 300px;
        }

        /* ইমেজকে 300x80 করে দিচ্ছি */
        .signature-upload .profile-file-upload-image {
            width: 300px !important;
            height: 80px !important;
            object-fit: contain; /* চাইলে cover দিলে crop হবে */
            display: block;
        }

        /* delete বাটন যেন দেখা যায় */
        .signature-upload .image-title-wrap {
            display: flex;
            justify-content: center;
            margin-top: 4px;
        }

        .profile-file-upload-image {
            height: 300px !important;
            max-height: 300px !important;
        }

        /* Education modal Select2 dropdown should appear over modal */
        #addEducationModal .select2-container,
        #editEducationModal .select2-container {
            width: 100% !important;
            z-index: 2055;
        }

        #addEducationModal .select2-container--open,
        #editEducationModal .select2-container--open,
        #addEducationModal .select2-dropdown,
        #editEducationModal .select2-dropdown {
            z-index: 999999  !important;
        }


        .candidate-profile-responsive .modal-dialog {
            max-width: calc(100% - 24px);
            margin-left: auto;
            margin-right: auto;
        }

        .dashboard-right, .dashboard-account-setting-item, .modal-content {
            max-width: 100%;
        }

        .db-job-card-table {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .db-job-card-table table {
            min-width: 640px;
        }

        @media (max-width: 767.98px) {
            .dashboard-right-header,
            .tw-flex.rt-mb-32,
            .modal-body .d-flex.justify-content-between,
            .modal-content form > .d-flex.justify-content-between {
                gap: 12px;
                flex-wrap: wrap;
            }

            .cadidate-dashboard-tabs .nav-pills {
                flex-wrap: nowrap;
                min-width: max-content;
            }

            #addEducationModal .modal-dialog,
            #editEducationModal .modal-dialog,
            #addExperienceModal .modal-dialog,
            #editExperienceModal .modal-dialog {
                max-width: calc(100% - 16px) !important;
                padding: 8px !important;
            }

            #addEducationModal form,
            #editEducationModal form {
                padding: 16px !important;
            }

            .mborder,
            .custom-select-padding .d-flex.mborder {
                width: 100%;
            }

            .custom-select-padding > .d-flex {
                align-items: flex-start !important;
            }
        }


    </style>


@endsection

@section('frontend_scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ageInput = document.getElementById('age_basic');
            const birthDateInput = document.getElementById('date');
            if (!ageInput || !birthDateInput) return;

            ageInput.addEventListener('input', function () {
                if (ageInput.value && birthDateInput.value) {
                    birthDateInput.value = '';
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', async function () {
            const presentDistrictEl = document.getElementById('bd_district_select');
            const presentThanaEl = document.getElementById('bd_thana_select');
            const permanentDistrictEl = document.getElementById('permanent_bd_district_select');
            const permanentThanaEl = document.getElementById('permanent_bd_thana_select');

            if (!presentDistrictEl || !presentThanaEl || !permanentDistrictEl || !permanentThanaEl) return;

            const currentDistrict = @json(old('bd_district_name', $candidate->locality ?: ($candidate->bd_district ?: ($candidate->district ?: ''))));
            const currentThana = @json(old('bd_thana_name', $candidate->bd_thana ?: ($candidate->place ?: '')));

            const permanentExisting = document.getElementById('permanent_address_existing')?.value || '';
            const permanentParts = permanentExisting
                ? permanentExisting.split(',').map(v => v.trim()).filter(Boolean)
                : [];
            const parsedPermanentPostcode = permanentParts.length ? permanentParts[permanentParts.length - 1] : '';
            const parsedPermanentDistrict = permanentParts.length > 1 ? permanentParts[permanentParts.length - 2] : '';
            const parsedPermanentThana = permanentParts.length > 2 ? permanentParts[permanentParts.length - 3] : '';
            const parsedPermanentNeighborhood = permanentParts.length > 3
                ? permanentParts.slice(0, permanentParts.length - 3).join(', ')
                : (permanentParts[0] || '');

            const currentPermanentNeighborhood = @json(old('permanent_neighborhood')) || parsedPermanentNeighborhood;
            const currentPermanentThana = @json(old('permanent_bd_thana_name')) || parsedPermanentThana;
            const currentPermanentDistrict = @json(old('permanent_bd_district_name')) || parsedPermanentDistrict;
            const currentPermanentPostcode = @json(old('permanent_postcode')) || parsedPermanentPostcode;

            const permanentNeighborhoodEl = document.querySelector('input[name="permanent_neighborhood"]');
            if (permanentNeighborhoodEl && !permanentNeighborhoodEl.value && currentPermanentNeighborhood) {
                permanentNeighborhoodEl.value = currentPermanentNeighborhood;
            }

            const permanentPostcodeEl = document.getElementById('permanent_postcode_basic');
            if (permanentPostcodeEl && !permanentPostcodeEl.value && currentPermanentPostcode) {
                permanentPostcodeEl.value = currentPermanentPostcode;
            }

            let districts = [];
            let thanaByDistrict = {};

            function isSelect2(el){
                return (window.jQuery && jQuery(el).data('select2'));
            }
            function refreshSelect2(el){
                if (isSelect2(el)) {
                    jQuery(el).trigger('change.select2');
                }
            }
             const normalize = (val) => String(val || '').trim().toLowerCase();
            
            function setOptions(selectEl, options, selectedValue = '') {
                selectEl.innerHTML = '';
                const def = document.createElement('option');
                def.value = '';
                def.textContent = "{{ __('select_one') }}";
                selectEl.appendChild(def);

                const selectedNormalized = normalize(selectedValue);
                const frag = document.createDocumentFragment();
                options.forEach(({value, text, data={}}) => {
                    const opt = document.createElement('option');
                    opt.value = value;
                    opt.textContent = text;
                    Object.keys(data).forEach(k => opt.dataset[k] = data[k]);
                    if (selectedNormalized && normalize(value) === selectedNormalized) opt.selected = true;
                    frag.appendChild(opt);
                });
                selectEl.appendChild(frag);
                refreshSelect2(selectEl);
            }

            function fillThanasByDistrictId(thanaEl, districtId, selectedThana = '') {
                thanaEl.disabled = true;
                const list = thanaByDistrict[String(districtId)] || [];
                const opts = list.map(t => ({value: t.name, text: t.name}));
                setOptions(thanaEl, opts, selectedThana);

                const selectedNormalized = normalize(selectedThana);
                const hasSelected = selectedNormalized && [...thanaEl.options].some(o => normalize(o.value) === selectedNormalized);
                if (selectedThana && !hasSelected) {
                    const opt = document.createElement('option');
                    opt.value = selectedThana;
                    opt.textContent = selectedThana;
                    opt.selected = true;
                    thanaEl.appendChild(opt);
                }

                thanaEl.disabled = false;
                if (isSelect2(thanaEl)) jQuery(thanaEl).prop('disabled', false);
                refreshSelect2(thanaEl);
            }

            function getSelectedDistrictId(districtEl) {
                const opt = districtEl.options[districtEl.selectedIndex];
                return opt ? (opt.dataset.districtId || '') : '';
            }

            function bindDistrictThana(districtEl, thanaEl) {
                const onDistrictChange = function () {
                    const districtId = getSelectedDistrictId(districtEl);
                    setOptions(thanaEl, [], '');
                    thanaEl.disabled = true;
                    if (!districtId) return;
                    fillThanasByDistrictId(thanaEl, districtId, '');
                };

                districtEl.addEventListener('change', onDistrictChange);
                if (isSelect2(districtEl)) {
                    jQuery(districtEl)
                        .off('select2:select.candidateAddress')
                        .on('select2:select.candidateAddress', onDistrictChange);
                }
            }

            Promise.all([
                fetch("{{ asset('frontend/assets/json/bd_districts.json') }}", { cache: "no-store" }).then(r => r.json()),
                fetch("{{ asset('frontend/assets/json/bd_thana_by_district.json') }}", { cache: "no-store" }).then(r => r.json())
            ]).then(([dJson, tJson]) => {
                districts = Array.isArray(dJson) ? dJson : (dJson.districts || []);
                thanaByDistrict = (tJson.thanas_by_district || tJson) || {};
                const districtOpts = districts.map(d => ({
                    value: d.name,
                    text: d.name,
                    data: { districtId: d.id }
                }));
                setOptions(presentDistrictEl, districtOpts, currentDistrict);
                setOptions(permanentDistrictEl, districtOpts, currentPermanentDistrict);
                if (currentDistrict) {
                    const selectedOpt = [...presentDistrictEl.options].find(o => normalize(o.value) === normalize(currentDistrict));
                    const districtId = selectedOpt ? selectedOpt.dataset.districtId : '';
                    if (districtId) fillThanasByDistrictId(presentThanaEl, districtId, currentThana || '');
                }
                if (currentPermanentDistrict) {
                    const selectedPermanentOpt = [...permanentDistrictEl.options].find(o => normalize(o.value) === normalize(currentPermanentDistrict));
                    const permanentDistrictId = selectedPermanentOpt ? selectedPermanentOpt.dataset.districtId : '';
                    if (permanentDistrictId) fillThanasByDistrictId(permanentThanaEl, permanentDistrictId, currentPermanentThana || '');
                }
                bindDistrictThana(presentDistrictEl, presentThanaEl);
                bindDistrictThana(permanentDistrictEl, permanentThanaEl);
            }).catch((e) => {
                console.error('BD district/thana JSON load failed', e);
            });

        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const languageSelect = document.getElementById('candidate_languages_select');
            const proficiencyContainer = document.getElementById('language_proficiency_container');
            if (!languageSelect || !proficiencyContainer) return;

            const proficiencyOptions = [
                { value: 'basic', label: @json(__('basic')) },
                { value: 'intermediate', label: @json(__('intermediate')) },
                { value: 'fluent', label: @json(__('fluent')) },
                { value: 'native', label: @json(__('native')) },
            ];
            const proficiencyMap = @json($languageProficiencies ?? []);

            function renderLanguageProficiencies() {
                const selectedOptions = Array.from(languageSelect.selectedOptions || []);

                // Preserve current UI-selected proficiency values before re-rendering.
                const currentRenderedValues = {};
                proficiencyContainer.querySelectorAll('select[name^="language_proficiencies["]').forEach((el) => {
                    const match = el.name.match(/language_proficiencies\[(\d+)\]/);
                    if (match) {
                        currentRenderedValues[match[1]] = el.value;
                    }
                });

                proficiencyContainer.innerHTML = '';

                selectedOptions.forEach(option => {
                    const languageId = option.value;
                    const languageName = option.textContent.trim();
                    const selectedProficiency = currentRenderedValues[languageId] || proficiencyMap[languageId] || 'basic';

                    const row = document.createElement('div');
                    row.className = 'row g-2 mb-2';

                    const labelCol = document.createElement('div');
                    labelCol.className = 'col-lg-6';
                    labelCol.innerHTML = `<input type="text" class="form-control" value="${languageName}" readonly>`;

                    const selectCol = document.createElement('div');
                    selectCol.className = 'col-lg-6';
                    const select = document.createElement('select');
                    select.name = `language_proficiencies[${languageId}]`;
                    select.className = 'form-control';

                    proficiencyOptions.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item.value;
                        opt.textContent = item.label;
                        if (item.value === selectedProficiency) {
                            opt.selected = true;
                        }
                        select.appendChild(opt);
                    });

                    select.addEventListener('change', function () {
                        proficiencyMap[languageId] = select.value;
                    });

                    selectCol.appendChild(select);
                    row.appendChild(labelCol);
                    row.appendChild(selectCol);
                    proficiencyContainer.appendChild(row);
                });
            }

            languageSelect.addEventListener('change', renderLanguageProficiencies);
            if (window.jQuery && jQuery(languageSelect).data('select2')) {
                jQuery(languageSelect).on('change', renderLanguageProficiencies);
            }

            renderLanguageProficiencies();
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('.select21').select2();
        })
    </script>
    @stack('js')
     <script>
        document.addEventListener('DOMContentLoaded', function () {
            function initEducationModalSelect2(modalSelector) {
                const $modal = $(modalSelector);
                const $selects = $modal.find('.edu-modal-select');
                $selects.each(function () {
                    const $el = $(this);
                    if ($el.hasClass('select2-hidden-accessible')) {
                        $el.select2('destroy');
                    }
                    $el.select2({
                        width: '100%',
                        dropdownParent: $modal,
                        tags: true
                    });
                });
            }

            $('#addEducationModal').on('shown.bs.modal', function () {
                initEducationModalSelect2('#addEducationModal');
            });

            $('#editEducationModal').on('shown.bs.modal', function () {
                initEducationModalSelect2('#editEducationModal');
            });

            function syncEducationResultLimit($modal) {
                const $type = $modal.find('.education-result-type');
                const $result = $modal.find('.education-result-input');
                const type = $type.val();
                $result.removeAttr('max');
                if (type === 'gpa_5') {
                    $result.attr('max', '5');
                    $result.attr('placeholder', '5.00');
                } else if (type === 'cgpa_4') {
                    $result.attr('max', '4');
                    $result.attr('placeholder', '4.00');
                } else if (type === 'percentage') {
                    $result.attr('max', '100');
                    $result.attr('placeholder', '100.00');
                } else {
                    $result.attr('placeholder', '5.00');
                }
            }

            $(document).on('change', '.education-result-type', function () {
                syncEducationResultLimit($(this).closest('.modal'));
            });

        });
    </script>
    <script>
        //init datepicker
        $("#available_id_date").attr("autocomplete", "off");

        availableStatus('{{ old('status', $candidate->status) }}');

        $('#available_status').on('change', function() {
            availableStatus(this.value);
        });

        function availableStatus(status) {
            if (status == 'available_in') {
                $('#available_in_status').removeClass('d-none');
            } else {
                $('#available_in_status').addClass('d-none');
                $('#available_id_date').val('');
            }
        }
        //init datepicker
        $(document).ready(function() {
            $('#available_id_date').datepicker({
                format: 'yyyy-mm-dd',
                isRTL: "{{ app()->getLocale() == 'ar' ? true : false }}",
                language: "{{ app()->getLocale() }}",
            });
        });

        function UploadMode(param) {
            if (param === 'photo') {
                $('#photo-uploadMode').removeClass('d-none');
                $('#photo-oldMode').addClass('d-none');
            } else {
                $('#banner-uploadMode').removeClass('d-none');
                $('#banner-oldMode').addClass('d-none');
            }
        }
        //init datepicker
        $("#date").attr("autocomplete", "off");
        //init datepicker
        $('#date').datepicker({
            format: 'yyyy-mm-dd',
            isRTL: "{{ app()->getLocale() == 'ar' ? true : false }}",
            language: "{{ app()->getLocale() }}",
        });
    </script>
    <script>
        $('#visibility').on('change', function() {
            $(this).submit();
        });
        $('#alert').on('change', function() {
            $(this).submit();
        });

        function AccountDelete() {
            if (confirm("{{ __('are_you_sure') }}") == true) {
                $('#AccountDelete').submit();
            } else {
                return false;
            }
        }

        function resumeDelete() {
            if (confirm("{{ __('are_you_sure') }}") == true) {
                $('#resumeForm').submit();
            } else {
                return false;
            }
        }

        function editResume(id, name, size) {
            $('#resume_id_input').val(id);
            $('#resume_name_input').val(name);
            $('#resume_file_size').html(size);
            $('#resumeEditModal').modal('show');
        }
        $('.cv-remove-image').on('click', function() {
            $('.resume-file-upload-input').replaceWith($('.resume-file-upload-input').clone());
            $('.resume-file-upload-content').hide();
            $('.cv-image-upload-wrap').show();
            $('.resume-file-upload-input').val('');
        })

       function resumeManageReadURL(input, type) {
            // উপরের অংশ: ফাইলের নাম, সাইজ, টাইপ – আগের মতোই
            if (type == 'add') {
                var fileName = document.querySelector('#resume_add_input').files[0].name;
                var fileSize = document.querySelector('#resume_add_input').files[0].size / 1024 / 1024;
                var fileType = document.querySelector('#resume_add_input').files[0].type;
            } else {
                var fileName = document.querySelector('#resume_edit_input').files[0].name;
                var fileSize = document.querySelector('#resume_edit_input').files[0].size / 1024 / 1024;
                var fileType = document.querySelector('#resume_edit_input').files[0].type;
            }

            $('.resume_selected_file_name').html(fileName);
            $('.resume_selected_file_size').html(fileSize.toFixed(4));
            $('.resume_selected_file_type').html(fileType);

            // ইমেজ / ফাইল preview
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    // ১) প্রোফাইল ফটো (photo-uploadMode)
                    if ($(input).closest('#photo-uploadMode').length) {
                        var $wrap = $('#photo-uploadMode');
                        $wrap.find('.profile-image-upload-wrap').hide();
                        $wrap.find('.profile-file-upload-image').attr('src', e.target.result);
                        $wrap.find('.profile-file-upload-content').show();
                    }

                    // ২) সিগনেচার (signature-uploadMode)
                    if ($(input).closest('#signature-uploadMode').length) {
                        var $wrap = $('#signature-uploadMode');
                        $wrap.find('.profile-image-upload-wrap').hide();
                        $wrap.find('.profile-file-upload-image').attr('src', e.target.result);
                        $wrap.find('.profile-file-upload-content').show();
                    }

                    // ৩) ব্যানার
                    if (input.className === 'banner-file-upload-input') {
                        $('.banner-image-upload-wrap').hide();
                        $('.banner-file-upload-image').attr('src', e.target.result);
                        $('.banner-file-upload-content').show();
                    }

                    // ৪) রিজিউম (CV) preview
                    if (input.className === 'resume-file-upload-input') {
                        $('.cv-image-upload-wrap').hide();
                        $('.resume-file-upload-content.none').show();
                    }
                };

                reader.readAsDataURL(input.files[0]);
            } else {
                // প্রোফাইল ফটো রিমুভ
                $('.profile-remove-image').off('click').on('click', function () {
                    var $wrap = $('#photo-uploadMode');
                    $wrap.find('.profile-file-upload-input').val('');
                    $wrap.find('.profile-file-upload-content').hide();
                    $wrap.find('.profile-file-upload-image').attr('src', '');
                    $wrap.find('.profile-image-upload-wrap').show();
                });

                // সিগনেচার রিমুভ
                $('.signature-remove-image').off('click').on('click', function () {
                    var $wrap = $('#signature-uploadMode');
                    $wrap.find('.profile-file-upload-input').val('');
                    $wrap.find('.profile-file-upload-content').hide();
                    $wrap.find('.profile-file-upload-image').attr('src', '');
                    $wrap.find('.profile-image-upload-wrap').show();

                    // চাইলে এখানে hidden input ইত্যাদি reset করতে পারো
                    // যেমন:
                    // $('#existing_signature').val('');
                });

                // ব্যানার রিমুভ
                $('.banner-remove-image').off('click').on('click', function () {
                    $('.banner-file-upload-input').replaceWith($('.banner-file-upload-input').clone());
                    $('.banner-file-upload-content').hide();
                    $('.banner-file-upload-image').attr('src', '');
                    $('.banner-image-upload-wrap').show();
                });
            }
        }
        
        setTimeout(function() {
            {{ session()->forget('type') }}
        }, 10000);        
        
        $(document).on('click', '.signature-remove-image', function (e) {
            e.preventDefault();

            // পুরোনো signature থাকলে first oldMode hide করব
            $('#signature-oldMode').addClass('d-none');

            // নতুন আপলোড মোড দেখাবো
            const $upload = $('#signature-uploadMode');
            $upload.removeClass('d-none');

            // ইনপুট reset
            $upload.find('.profile-file-upload-input').val('');
            // ইমেজ reset
            $upload.find('.profile-file-upload-image').attr('src', '#');
            // preview hide, drag-text দেখাও
            $upload.find('.profile-file-upload-content').hide();
            $upload.find('.profile-image-upload-wrap').show();
        });

    </script>

    @include('map::set-edit-' . $setting->default_map. 'map', ['lat' => $candidate->lat, 'long' => $candidate->long])

    <script>
        $('#pills-setting-tab').on('click', function() {
            setTimeout(() => {
                map.resize();
                leaflet_map.invalidateSize(true);
            }, 200);
        })
    </script>
    <script>
        $(".new-select").select2({ // minimumResultsForSearch: Infinity,
        });
    </script>
    <script type="text/javascript">
        // feature field
        function add_features_field() {
            $("#multiple_feature_part").append(`
        <div class="col-12 custom-select-padding">
            <div class="d-flex tw-items-center">
                <div class="d-flex mborder">
                    <div class="position-relative">
                        <select
                            class="w-100-p border-0 rt-selectactive-2 form-control" name="social_media[]">
                            <option value="" class="d-none" disabled selected>{{ __('select_one') }}</option>
                            <option value="facebook">{{ __('facebook') }}</option>
                            <option value="twitter">{{ __('twitter') }}</option>
                            <option value="instagram">{{ __('instagram') }}</option>
                            <option value="youtube">{{ __('youtube') }}</option>
                            <option value="linkedin">{{ __('linkedin') }}</option>
                            <option value="pinterest">{{ __('pinterest') }}</option>
                            <option value="reddit">{{ __('reddit') }}</option>
                            <option value="github">{{ __('github') }}</option>
                            <option value="other">{{ __('other') }}</option>
                        </select>
                    </div>
                    <div class="w-100">
                        <input class="border-0" type="url" name="url[]" id="" placeholder="{{ __('profile_link_url') }}...">
                    </div>
                </div>
                <div class="tw-ms-2">
                    <button class="tw-w-12 tw-h-12 tw-border-0 tw-rounded tw-bg-[#F1F2F4] tw-inline-flex tw-justify-center tw-items-center" type="button" id="remove_item">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z" stroke="#18191C" stroke-width="1.5" stroke-miterlimit="10"/>
                            <path d="M15 9L9 15" stroke="#18191C" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M15 15L9 9" stroke="#18191C" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    `);
        $(".rt-selectactive-2").select2({ // minimumResultsForSearch: Infinity,
    }); 
    }

        function add_new_extracariculer() {
            $("#multiple_feature_part").append(`
                <div class="col-12 custom-select-padding">
                    <div class="d-flex tw-items-center">
                        <div class="d-flex mborder">
                            <div class="w-100">
                                <input class="border-0" type="text" name="extracariculer[]"
                                    id=""
                                    placeholder="{{ __('extracariculer') }}...">
                                <textarea class="form-control mt-2" name="extracariculer_description[]" rows="3"
                                    placeholder="{{ __('extracurricular_description_placeholder') }}"></textarea>
                            </div>
                        </div>
                        <div class="tw-ms-2">
                            <button
                                class="tw-w-12 tw-h-12 tw-border-0 tw-rounded tw-bg-[#F1F2F4] tw-inline-flex tw-justify-center tw-items-center"
                                type="button" id="remove_item">
                                <svg width="24" height="24"
                                    viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z"
                                        stroke="#18191C" stroke-width="1.5"
                                        stroke-miterlimit="10" />
                                    <path d="M15 9L9 15" stroke="#18191C"
                                        stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M15 15L9 9" stroke="#18191C"
                                        stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            `);
            $(".rt-selectactive-2").select2({ // minimumResultsForSearch: Infinity,
            });
        }

        $(document).on("click", "#remove_item", function() {
            $(this).parent().parent().parent('div').remove();
        });
        $(function () {
            $('.select2-country').select2({
                placeholder: "{{ __('select_country') ?? 'Select country' }}",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
@endsection
