@extends('backend.layouts.app')
@section('title')
    {{ __('update') }} {{ __('employer') }}
@endsection
@section('content')
    <div class="container-fluid">
        <form class="form-horizontal" action="{{ route('company.update', $company->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title line-height-36">{{ __('update') }} {{ __('employer') }}</h4>
                    <button type="submit"
                        class="btn bg-success float-right d-flex align-items-center justify-content-center">
                        <i class="fas fa-sync mr-1"></i>
                        {{ __('save') }}
                    </button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            {{ __('account_details') }}
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <x-forms.label name="employer_name" :required="true" />
                                <x-forms.input type="text" name="name" data-show-errors="true" placeholder="name"
                                    value="{{ old('name', $user->name) }}" />
                            </div>
                            <div class="row">
                                <div class="form-group col-sm-6">
                                    <x-forms.label name="username" :required="false" />
                                    <x-forms.input type="text" name="username" placeholder="username"
                                        value="{{ old('username', $user->username) }}" />
                                </div>
                                <div class="form-group col-sm-6">
                                    <x-forms.label name="email" />
                                    <x-forms.input type="email" name="email" placeholder="email"
                                        value="{{ old('email', $user->email) }}" />
                                </div>

                            </div>
                            <div class="form-group">
                                <x-forms.label name="change_password" />
                                <x-forms.input type="password" name="password" placeholder="password" />
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        @if (config('templatecookie.map_show'))
                            <div class="card-header">
                                {{ __('location') }}
                                <span class="text-red font-weight-bold">*</span>
                                <small class="h6">
                                    ({{ __('click_to_add_a_pointer') }})
                                </small>
                            </div>
                            <div class="card-body">
                                <x-website.map.map-warning />

                                @php
                                    $map = $setting->default_map;
                                @endphp
                                <div id="google-map-div" class="{{ $map == 'google-map' ? '' : 'd-none' }}">
                                    <input id="searchInput" class="mapClass" type="text" placeholder="Enter a location">
                                    <div class="map mymap" id="google-map"></div>
                                </div>
                                <div class="{{ $map == 'leaflet' ? '' : 'd-none' }}">
                                    <input type="text" autocomplete="off" id="leaflet_search"
                                        placeholder="{{ __('enter_city_name') }}" class="form-control" /> <br>
                                    <div id="leaflet-map"></div>
                                </div>
                                @error('location')
                                    <span class="ml-3 text-md text-danger">{{ $message }}</span>
                                @enderror

                            </div>
                            @php
                                $location = session()->get('location');

                            @endphp
                            <div class="card-footer location_footer d-none">
                                <span>
                                    <img src="{{ asset('frontend/assets/images/loader.gif') }}" alt="loading"
                                        width="50px" height="50px" class="loader_position d-none">
                                </span>
                                <div class="location_secion">
                                    {{ __('country') }}: <span
                                        class="location_country">{{ $location && array_key_exists('country', $location) ? $location['country'] : '-' }}</span>
                                    <br>
                                    {{ __('full_address') }}: <span
                                        class="location_full_address">{{ $location && array_key_exists('exact_location', $location) ? $location['exact_location'] : '-' }}</span>
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
                                    'selectedCountryId' => $company->country,
                                    'selectedStateId' => $company->region,
                                    'selectedCityId' => $company->district,
                                ]);
                            @endphp
                            <div class="card-header border-0">
                                {{ __('location') }}
                            </div>
                            <div class="card-body pt-0 row">
                                <div class="col-12">
                                    @livewire('country-state-city')
                                    @error('location')
                                        <span class="ml-3 text-md text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="card">
                        <div class="card-header">
                            {{ __('contact') }}
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-sm-6">
                                    <x-forms.label name="phone" />
                                    <x-forms.input type="text" name="contact_phone" placeholder="phone"
                                        value="{{ old('contact_phone', $user->contactInfo->phone) }}" />
                                </div>
                                <div class="form-group col-sm-6">
                                    <x-forms.label name="email" />
                                    <x-forms.input type="email" name="contact_email" placeholder="email"
                                        value="{{ old('contact_email', $user->contactInfo->email) }}" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            {{ __('images') }}
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-xl-4">
                                    <x-forms.label name="logo" :required="false" />
                                    <input name="logo" type="file" data-show-errors="true" data-width="50%"
                                        class="dropify" data-default-file="{{ $company->logo_url }}">
                                    <p class="tw-text-gray-500 tw-text-xs tw-text-left mt-2 recommended-img-note mb-0">
                                        {{ __('recommended_image_size') }}: 68x68</p>
                                </div>
                                <div class="form-group col-xl-8">
                                    <x-forms.label name="banner" :required="false" />
                                    <input name="image" type="file" data-show-errors="true" data-width="100%"
                                        data-default-file="{{ $company->banner_url }}" class="dropify">
                                    <p class="tw-text-gray-500 tw-text-xs tw-text-left mt-2 recommended-img-note mb-0">
                                        {{ __('recommended_image_size') }}: 1920x312</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            {{ __('social_details') }}
                        </div>
                        <div class="card-body">
                            <div id="multiple_feature_part">
                                <div class="row justify-content-center">
                                    <div class="form-group col-md-4">
                                        <select
                                            class="form-control select2bs4 @error('social_media') border-danger @enderror"
                                            name="social_media[]">
                                            <option value="" class="d-none" disabled>{{ __('select_one') }}
                                            </option>
                                            <option {{ old('social_media') == 'facebook' ? 'selected' : '' }}
                                                value="facebook">{{ __('facebook') }}</option>
                                            <option {{ old('social_media') == 'twitter' ? 'selected' : '' }}
                                                value="twitter">{{ __('twitter') }}</option>
                                            <option {{ old('social_media') == 'instagram' ? 'selected' : '' }}
                                                value="instagram">{{ __('instagram') }}
                                            </option>
                                            <option {{ old('social_media') == 'youtube' ? 'selected' : '' }}
                                                value="youtube">{{ __('youtube') }}</option>
                                            <option {{ old('social_media') == 'linkedin' ? 'selected' : '' }}
                                                value="linkedin">{{ __('linkedin') }}</option>
                                            <option {{ old('social_media') == 'pinterest' ? 'selected' : '' }}
                                                value="pinterest">{{ __('pinterest') }}
                                            </option>
                                            <option {{ old('social_media') == 'reddit' ? 'selected' : '' }}
                                                value="reddit">{{ __('reddit') }}</option>
                                            <option {{ old('social_media') == 'github' ? 'selected' : '' }}
                                                value="github">{{ __('github') }}</option>
                                            <option {{ old('social_media') == 'other' ? 'selected' : '' }} value="other">
                                                {{ __('other') }}</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <input type="url" name="url[]" class="form-control">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <a role="button" onclick="add_features_field()"
                                            class="btn bg-primary text-light"><i class="fas fa-plus"></i></a>
                                    </div>
                                </div>
                                @forelse($socials as $social)
                                    <div class="row justify-content-center">
                                        <div class="form-group col-md-4">
                                            <select class="form-control @error('social_media') border-danger @enderror"
                                                name="social_media[]">
                                                <option value="" class="d-none" disabled>{{ __('select_one') }}
                                                </option>
                                                <option {{ $social->social_media == 'facebook' ? 'selected' : '' }}
                                                    value="facebook">{{ __('facebook') }}</option>
                                                <option {{ $social->social_media == 'twitter' ? 'selected' : '' }}
                                                    value="twitter">{{ __('twitter') }}</option>
                                                <option {{ $social->social_media == 'instagram' ? 'selected' : '' }}
                                                    value="instagram">{{ __('instagram') }}
                                                </option>
                                                <option {{ $social->social_media == 'youtube' ? 'selected' : '' }}
                                                    value="youtube">{{ __('youtube') }}</option>
                                                <option {{ $social->social_media == 'linkedin' ? 'selected' : '' }}
                                                    value="linkedin">{{ __('linkedin') }}</option>
                                                <option {{ $social->social_media == 'pinterest' ? 'selected' : '' }}
                                                    value="pinterest">{{ __('pinterest') }}
                                                </option>
                                                <option {{ $social->social_media == 'reddit' ? 'selected' : '' }}
                                                    value="reddit">{{ __('reddit') }}</option>
                                                <option {{ $social->social_media == 'github' ? 'selected' : '' }}
                                                    value="github">{{ __('github') }}</option>
                                                <option {{ $social->social_media == 'other' ? 'selected' : '' }}
                                                    value="other">
                                                    {{ __('other') }}</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <input type="url" name="url[]" class="form-control"
                                                value="{{ $social->url }}" placeholder="{{ __('url') }}">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <a role="button" id="remove_item" class="btn bg-danger text-light"><i
                                                    class="fas fa-times"></i></a>
                                        </div>
                                    </div>
                                @empty
                                    <!-- No social media entries found -->
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            {{ __('profile_details') }}
                        </div>
                        <div class="card-body">

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <x-forms.label name="organization_type" />
                                    <select name="organization_type_id"
                                        class="form-control select2bs4 {{ error('organization_type_id') }}"
                                        id="organization_type_id">
                                        @foreach ($organization_types as $type)
                                            <option
                                                {{ $type->id == old('organization_type_id', $company->organization_type_id) ? 'selected' : '' }}
                                                value="{{ $type->id }}">
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-forms.error name="organization_type_id" />
                                </div>
                                <div class="form-group col-md-6">
                                    <x-forms.label name="industry_type" />
                                    <select name="industry_type_id"
                                        class="form-control select2bs4 {{ error('industry_type_id') }}"
                                        id="organization_type_id">
                                        <option value="" class="d-none">
                                            {{ __('select_one') }}
                                        </option>
                                        @foreach ($industry_types as $type)
                                            <option
                                                {{ $type->id == old('industry_type_id', $company->industry_type_id) ? 'selected' : '' }}
                                                value="{{ $type->id }}">
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-forms.error name="industry_type_id" />
                                </div>
                                <div class="form-group col-md-6">
                                    <x-forms.label name="team_size" :required="false" />
                                    <select name="team_size_id" class="form-control {{ error('team_size_id') }}"
                                        id="organization_type_id">
                                        <option value="" class="d-none">
                                            {{ __('select_one') }}
                                        </option>
                                        @foreach ($team_sizes as $size)
                                            <option
                                                {{ $size->id == old('team_size_id', $company->team_size_id) ? 'selected' : '' }}
                                                value="{{ $size->id }}">
                                                {{ $size->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-forms.error name="team_size_id" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group datepicker col-md-6">
                                    <x-forms.label name="website" :required="false" />
                                    <x-forms.input type="text" name="website" placeholder="website"
                                        value="{{ old('website', $company->website) }}" />
                                    <x-forms.error name="establishment_date" />
                                </div>
                                <div class="form-group datepicker col-md-6">
                                    <x-forms.label name="establishment_date" :required="false" />
                                    <x-forms.input type="text" name="establishment_date" placeholder="select_one"
                                        id="establishment_date"
                                        value="{{ old('establishment_date', formatTime($company->establishment_date, 'd-m-Y')) }}" />
                                    <x-forms.error name="establishment_date" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <x-forms.label name="bio" :required="false" />
                                    <textarea id="image_ckeditor" rows="8" name="bio" placeholder="{{ __('bio') }}"
                                        class="form-control">{{ old('bio', $company->bio) }}</textarea>
                                </div>
                                <div class="form-group col-md-6">
                                    <x-forms.label name="vision" :required="false" />
                                    <textarea id="image_ckeditor_2" rows="8" name="vision" placeholder="{{ __('vision') }}"
                                        class="form-control">{{ old('vision', $company->vision) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
@section('style')
    <link rel="stylesheet" href="{{ asset('backend') }}/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('backend') }}/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">

    <link rel="stylesheet" href="{{ asset('frontend') }}/assets/css/bootstrap-datepicker.min.css">

    <style>
        .ck-editor__editable_inline {
            min-height: 400px;
        }
    </style>
    <!-- >=>Leaflet Map<=< -->
    <x-map.leaflet.map_links />
    <x-map.leaflet.autocomplete_links />

    @include('map::links')
@endsection

@section('script')
    @livewireScripts
    <script>
        $(document).ready(function() {
            $('.select21').select2();
        });
        window.addEventListener('render-select2', event => {
            console.log('fired');
            $('.select21').select2();
        })
    </script>
    @stack('js')
    <script>
        $('.dropify').dropify();

        //init datepicker
        $(document).ready(function() {
            $('#establishment_date').datepicker({
                format: 'dd-mm-yyyy',
                isRTL: "{{ app()->getLocale() == 'ar' ? true : false }}",
                language: "{{ app()->getLocale() }}",
            });
        });

        $(document).on("click", "#remove_item", function() {
            $(this).parent().parent('div').remove();
        });

        function add_features_field() {
            $("#multiple_feature_part").append(`
            <div class="row justify-content-center">
                <div class="form-group col-md-4">
                    <select class="form-control @error('social_media') border-danger @enderror"
                        name="social_media[]">
                        <option value="" class="d-none" disabled>{{ __('select_one') }}
                        </option>
                        <option {{ old('social_media') == 'facebook' ? 'selected' : '' }}
                            value="facebook">{{ __('facebook') }}</option>
                        <option {{ old('social_media') == 'twitter' ? 'selected' : '' }}
                            value="twitter">{{ __('twitter') }}</option>
                        <option {{ old('social_media') == 'instagram' ? 'selected' : '' }}
                            value="instagram">{{ __('instagram') }}
                        </option>
                        <option {{ old('social_media') == 'youtube' ? 'selected' : '' }}
                            value="youtube">{{ __('youtube') }}</option>
                        <option {{ old('social_media') == 'linkedin' ? 'selected' : '' }}
                            value="linkedin">{{ __('linkedin') }}</option>
                        <option {{ old('social_media') == 'pinterest' ? 'selected' : '' }}
                            value="pinterest">{{ __('pinterest') }}
                        </option>
                        <option {{ old('social_media') == 'reddit' ? 'selected' : '' }}
                            value="reddit">{{ __('reddit') }}</option>
                        <option {{ old('social_media') == 'github' ? 'selected' : '' }}
                            value="github">{{ __('github') }}</option>
                        <option {{ old('social_media') == 'other' ? 'selected' : '' }} value="other">
                            {{ __('other') }}</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <input type="url" name="url[]" class="form-control">
                </div>
                <div class="form-group col-md-2">
                    <a role="button" id="remove_item"
                        class="btn bg-danger text-light"><i class="fas fa-times"></i></a>
                </div>
            </div>
            `);
        }
    </script>

    @include('map::set-edit-' . $setting->default_map. 'map', ['lat' => $company->lat, 'long' => $company->long])

@endsection
