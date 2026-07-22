@php
    $candidate = $candidate ?? null;
    $contactInfo = $contactInfo ?? null;
    $selectedPreferredLocationsRaw = old('preferred_job_locations');

    if (is_null($selectedPreferredLocationsRaw)) {
        $savedPreferredLocationsRaw = $candidate->preferred_job_locations ?? null;
        if (is_string($savedPreferredLocationsRaw) && $savedPreferredLocationsRaw !== '') {
            $decodedPreferredLocations = json_decode($savedPreferredLocationsRaw, true);
            $selectedPreferredLocationsRaw = json_last_error() === JSON_ERROR_NONE && is_array($decodedPreferredLocations)
                ? $decodedPreferredLocations
                : array_map('trim', explode(',', $savedPreferredLocationsRaw));
        } else {
            $selectedPreferredLocationsRaw = [];
        }
    }

    $selectedLanguageIds = old('languages', $candidate?->languages?->pluck('id')->toArray() ?? []);
    $languageProficiencies = old('language_proficiencies', $candidate?->languages?->mapWithKeys(function ($language) {
        return [$language->id => data_get($language, 'pivot.proficiency_level', 'basic')];
    })->toArray() ?? []);
@endphp

<div class="col-md-4">
    <div class="form-group">
        <x-forms.label name="professional_title_tagline" :required="false" />
        <input type="text" name="title" value="{{ old('title', $candidate->title ?? '') }}"
            class="form-control @error('title') is-invalid @enderror" placeholder="{{ __('title') }}">
        @error('title')
            <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
        @enderror
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        <x-forms.label name="nationality" :required="false" />
        <select name="nationality" class="form-control select2bs4 @error('nationality') is-invalid @enderror">
            <option value="">{{ __('select_country') }}</option>
            @foreach ($countries as $country)
                <option value="{{ $country->name }}" {{ old('nationality', $candidate->nationality ?? '') == $country->name ? 'selected' : '' }}>
                    {{ $country->name }}
                </option>
            @endforeach
        </select>
        @error('nationality')
            <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
        @enderror
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        <x-forms.label name="your_availability" :required="false" />
        <select name="status" class="form-control select2bs4 @error('status') is-invalid @enderror">
            <option value="">{{ __('select_one') }}</option>
            <option value="available" {{ old('status', $candidate->status ?? '') == 'available' ? 'selected' : '' }}>{{ __('available') }}</option>
            <option value="not_available" {{ old('status', $candidate->status ?? '') == 'not_available' ? 'selected' : '' }}>{{ __('not_available') }}</option>
            <option value="available_in" {{ old('status', $candidate->status ?? '') == 'available_in' ? 'selected' : '' }}>{{ __('available_in') }}</option>
        </select>
        @error('status')
            <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
        @enderror
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        <x-forms.label name="available_in" :required="false" />
        <input type="text" name="available_in" value="{{ old('available_in', ! empty($candidate?->available_in) ? date('d-m-Y', strtotime($candidate->available_in)) : '') }}"
            class="form-control @error('available_in') is-invalid @enderror" placeholder="dd-mm-yyyy">
        @error('available_in')
            <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
        @enderror
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        <x-forms.label name="father_name" :required="false" />
        <input type="text" name="father_name" value="{{ old('father_name', $candidate->father_name ?? '') }}"
            class="form-control @error('father_name') is-invalid @enderror" placeholder="{{ __('type_father_name') }}">
        @error('father_name')
            <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
        @enderror
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        <x-forms.label name="mother_name" :required="false" />
        <input type="text" name="mother_name" value="{{ old('mother_name', $candidate->mother_name ?? '') }}"
            class="form-control @error('mother_name') is-invalid @enderror" placeholder="{{ __('type_mother_name') }}">
        @error('mother_name')
            <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
        @enderror
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        <x-forms.label name="religion" :required="false" />
        <input type="text" name="religion" value="{{ old('religion', $candidate->religion ?? '') }}"
            class="form-control @error('religion') is-invalid @enderror" placeholder="{{ __('type_religion') }}">
        @error('religion')
            <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
        @enderror
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        <x-forms.label name="age" :required="false" />
        <input type="number" name="age" value="{{ old('age', ! empty($candidate?->birth_date) ? \Carbon\Carbon::parse($candidate->birth_date)->age : '') }}"
            class="form-control @error('age') is-invalid @enderror" min="1" max="100" placeholder="{{ __('age') }}">
        @error('age')
            <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
        @enderror
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        <x-forms.label name="phone" :required="false" />
        <input type="text" name="phone" value="{{ old('phone', $contactInfo->phone ?? '') }}"
            class="form-control @error('phone') is-invalid @enderror" placeholder="{{ __('phone') }}">
        @error('phone')
            <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
        @enderror
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        <x-forms.label name="secondary_phone" :required="false" />
        <input type="text" name="secondary_phone" value="{{ old('secondary_phone', $contactInfo->secondary_phone ?? '') }}"
            class="form-control @error('secondary_phone') is-invalid @enderror" placeholder="{{ __('phone') }}">
        @error('secondary_phone')
            <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
        @enderror
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        <x-forms.label name="whatsapp_number" :required="false" />
        <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $contactInfo->whatsapp_number ?? $candidate->whatsapp_number ?? '') }}"
            class="form-control @error('whatsapp_number') is-invalid @enderror" placeholder="{{ __('whatsapp_number') }}">
        @error('whatsapp_number')
            <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
        @enderror
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        <x-forms.label name="contact_email" :required="false" />
        <input type="email" name="contact_email" value="{{ old('contact_email', $contactInfo->email ?? '') }}"
            class="form-control @error('contact_email') is-invalid @enderror" placeholder="{{ __('email') }}">
        @error('contact_email')
            <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
        @enderror
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        <x-forms.label name="secondary_email" :required="false" />
        <input type="email" name="secondary_email" value="{{ old('secondary_email', $contactInfo->secondary_email ?? '') }}"
            class="form-control @error('secondary_email') is-invalid @enderror" placeholder="{{ __('secondary_email') }}">
        @error('secondary_email')
            <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
        @enderror
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        <x-forms.label name="district" :required="false" />
        <input type="text" name="bd_district_name" value="{{ old('bd_district_name', $candidate->bd_district ?? $candidate->district ?? '') }}"
            class="form-control @error('bd_district_name') is-invalid @enderror" placeholder="{{ __('district') }}">
        @error('bd_district_name')
            <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
        @enderror
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        <x-forms.label name="thana_upazila" :required="false" />
        <input type="text" name="bd_thana_name" value="{{ old('bd_thana_name', $candidate->bd_thana ?? $candidate->place ?? '') }}"
            class="form-control @error('bd_thana_name') is-invalid @enderror" placeholder="{{ __('thana_upazila') }}">
        @error('bd_thana_name')
            <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
        @enderror
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        <x-forms.label name="postcode" :required="false" />
        <input type="text" name="postcode" value="{{ old('postcode', $candidate->postcode ?? $candidate->bd_post_office ?? '') }}"
            class="form-control @error('postcode') is-invalid @enderror" placeholder="{{ __('postcode') }}">
        @error('postcode')
            <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
        @enderror
    </div>
</div>

<div class="col-md-12">
    <div class="form-group">
        <x-forms.label name="house_no_road_village" :required="false" />
        <input type="text" name="neighborhood" value="{{ old('neighborhood', $candidate->neighborhood ?? $candidate->house_road_village ?? '') }}"
            class="form-control @error('neighborhood') is-invalid @enderror" placeholder="{{ __('type_house_no_road_village') }}">
        @error('neighborhood')
            <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
        @enderror
    </div>
</div>

<div class="col-md-6">
    <div class="form-group">
        <x-forms.label name="permanent_address" :required="false" />
        <textarea name="permanent_address" class="form-control @error('permanent_address') is-invalid @enderror" rows="3" placeholder="{{ __('permanent_address') }}">{{ old('permanent_address', $candidate->permanent_address ?? '') }}</textarea>
        @error('permanent_address')
            <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
        @enderror
    </div>
</div>

<div class="col-md-6">
    <div class="form-group">
        <x-forms.label name="international_address" :required="false" />
        <textarea name="international_address" class="form-control @error('international_address') is-invalid @enderror" rows="3" placeholder="{{ __('international_address') }}">{{ old('international_address', $candidate->international_address ?? '') }}</textarea>
        @error('international_address')
            <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
        @enderror
    </div>
</div>

<div class="col-md-12">
    <div class="form-group">
        <x-forms.label name="preferred_job_locations" :required="false" />
        <select name="preferred_job_locations[]" class="select2-taggable form-control @error('preferred_job_locations') is-invalid @enderror" multiple>
            @foreach ($countries as $country)
                <option value="{{ $country->name }}" {{ in_array($country->name, $selectedPreferredLocationsRaw ?? [], true) ? 'selected' : '' }}>
                    {{ $country->name }}
                </option>
            @endforeach
        </select>
        @error('preferred_job_locations')
            <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
        @enderror
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        <x-forms.label name="passport_no" :required="false" />
        <input type="text" name="passport_no" value="{{ old('passport_no', $candidate->passport_no ?? '') }}"
            class="form-control @error('passport_no') is-invalid @enderror" placeholder="{{ __('passport_no') }}">
        @error('passport_no')
            <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
        @enderror
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        <x-forms.label name="issue_date" :required="false" />
        <input type="date" name="passport_issue_date" value="{{ old('passport_issue_date', ! empty($candidate?->passport_issue_date) ? date('Y-m-d', strtotime($candidate->passport_issue_date)) : '') }}"
            class="form-control @error('passport_issue_date') is-invalid @enderror">
        @error('passport_issue_date')
            <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
        @enderror
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        <x-forms.label name="place_of_issue" :required="false" />
        <input type="text" name="passport_place_of_issue" value="{{ old('passport_place_of_issue', $candidate->passport_place_of_issue ?? '') }}"
            class="form-control @error('passport_place_of_issue') is-invalid @enderror" placeholder="{{ __('place_of_issue') }}">
        @error('passport_place_of_issue')
            <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
        @enderror
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        <x-forms.label name="expiry_date" :required="false" />
        <input type="date" name="passport_expiry_date" value="{{ old('passport_expiry_date', ! empty($candidate?->passport_expiry_date) ? date('Y-m-d', strtotime($candidate->passport_expiry_date)) : '') }}"
            class="form-control @error('passport_expiry_date') is-invalid @enderror">
        @error('passport_expiry_date')
            <span class="invalid-feedback" role="alert">{{ __($message) }}</span>
        @enderror
    </div>
</div>

<div class="col-md-12">
    <div class="form-group">
        <x-forms.label name="language_proficiencies" :required="false" />
        <div class="row">
            @foreach ($candidate_languages as $language)
                <div class="col-md-6 mb-2 candidate-language-proficiency {{ in_array($language->id, $selectedLanguageIds ?? []) ? '' : 'd-none' }}" data-language-id="{{ $language->id }}">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">{{ $language->name }}</span>
                        </div>
                        <select name="language_proficiencies[{{ $language->id }}]" class="form-control">
                            @foreach (['basic', 'intermediate', 'fluent', 'native'] as $proficiency)
                                <option value="{{ $proficiency }}" {{ ($languageProficiencies[$language->id] ?? 'basic') === $proficiency ? 'selected' : '' }}>
                                    {{ __($proficiency) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
