@props(['experiences','jobCategories'=>collect([]),'skills'=>collect([]),'experienceSkills'=>collect([])])
<div class="tw-flex rt-mb-32 lg:tw-mt-0 tw-items-center tw-justify-between">
    <h3 class="f-size-18 tw-flex-shrink-0 lh-1 m-0">{{ __('experience') }}</h3>
    <button id="addExperience" type="button" class="btn btn-primary">
        {{ __('add_experience') }}
    </button>
</div>

{{-- Experience Skills (Category + Skill + Learned From) --}}
<div class="dashboard-account-setting-item rt-mb-24">
    <div class="rt-mb-16">
        <h4 class="f-size-16 m-0">{{ __('experience_skill') }}</h4>
    </div>

    <form action="{{ route('candidate.settingUpdate') }}" method="POST">
        @csrf
        @method('put')
        <input type="hidden" name="type" value="experience_skill">

        <div class="row">
            <div class="col-md-6 mb-3">
                <x-forms.label :required="true" name="select_category" class="body-font-4 d-block text-gray-900 rt-mb-8" />
                <select name="experience_skill_category_id" class="rt-selectactive w-100-p" required>
                    <option value="">{{ __('select_one') }}</option>
                    @foreach($jobCategories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('experience_skill_category_id')
                    <span class="text-danger">{{ __($message) }}</span>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <x-forms.label :required="true" name="select_work_type_skills" class="body-font-4 d-block text-gray-900 rt-mb-8" />
                <select name="experience_skill_id" class="rt-selectactive w-100-p select2-taggable" required>
                    <option value="">{{ __('select_one') }}</option>
                    @foreach($skills as $skill)
                        <option value="{{ $skill->id }}">{{ $skill->name }}</option>
                    @endforeach
                </select>
                @error('experience_skill_id')
                    <span class="text-danger">{{ __($message) }}</span>
                @enderror
            </div>

            <div class="col-12 mb-3">
                <x-forms.label :required="false" name="how_did_you_learn_this_skill" class="body-font-4 d-block text-gray-900 rt-mb-8" />
                <div class="d-flex flex-wrap gap-3">
                    <label class="d-flex align-items-center gap-2">
                        <input type="checkbox" name="learned_from[]" value="self"> <span>{{ __('self') }}</span>
                    </label>
                    <label class="d-flex align-items-center gap-2">
                        <input type="checkbox" name="learned_from[]" value="job"> <span>{{ __('job') }}</span>
                    </label>
                    <label class="d-flex align-items-center gap-2">
                        <input type="checkbox" name="learned_from[]" value="educational"> <span>{{ __('educational') }}</span>
                    </label>
                    <label class="d-flex align-items-center gap-2">
                        <input type="checkbox" name="learned_from[]" value="professional_training"> <span>{{ __('professional_training') }}</span>
                    </label>
                    <label class="d-flex align-items-center gap-2">
                        <input type="checkbox" name="learned_from[]" value="ntvqf"> <span>{{ __('ntvqf') }}</span>
                    </label>
                </div>
            </div>

            <div class="col-12 mb-3">
                <button type="submit" class="btn btn-primary">
                    + {{ __('add_experience_skill') }}
                </button>
            </div>
        </div>
    </form>

    <div class="rt-mt-16">
        <div class="db-job-card-table -tw-mx-2 tw-pb-0">
            <table class="tw-px-2">
                <thead>
                    <tr>
                        <th class="!tw-text-base !tw-font-medium">{{ __('category') }}</th>
                        <th class="!tw-text-base !tw-font-medium">{{ __('skill') }}</th>
                        <th class="!tw-text-base !tw-font-medium">{{ __('learned_from') }}</th>
                        <th class="!tw-text-base !tw-font-medium tw-text-right">{{ __('action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($experienceSkills as $row)
                        <tr>
                            <td>{{ optional($row->category)->name }}</td>
                            <td>{{ optional($row->skill)->name }}</td>
                            <td>
                                @php
                                    $lf = is_array($row->learned_from) ? $row->learned_from : [];
                                @endphp
                                {{ $lf ? implode(', ', $lf) : '-' }}
                            </td>
                            <td>
                                <div class="d-flex justify-content-end">
                                    <form action="{{ route('candidate.settingUpdate') }}" method="POST" onsubmit="return confirm('{{ __('are_you_sure_you_want_to_delete_this_item') }}');">
                                        @csrf
                                        @method('put')
                                        <input type="hidden" name="type" value="experience_skill_delete">
                                        <input type="hidden" name="experience_skill_row_id" value="{{ $row->id }}">
                                        <button type="submit" class="btn btn-icon">
                                            <x-svg.trash-icon/>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">
                                <x-svg.not-found-icon />
                                <p class="mt-4">{{ __('no_data_found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="db-job-card-table -tw-mx-2 tw-pb-16">
    <table class="tw-px-2">
        <thead>
            <tr>
                <th class="!tw-text-base !tw-font-medium">{{ __('company') }}</th>
                <th class="!tw-text-base !tw-font-medium">{{ __('department') }}</th>
                <th class="!tw-text-base !tw-font-medium">{{ __('designation') }}</th>
                <th class="!tw-text-base !tw-font-medium">{{ __('period') }}</th>
                <th class="!tw-text-base !tw-font-medium tw-text-right">{{ __('action') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($experiences as $experience)
                <tr>
                    <td>{{ $experience->company }}</td>
                    <td>{{ $experience->department }}</td>
                    <td>{{ $experience->designation }}</td>
                    <td>
                        {{ formatTime($experience->start, 'd M Y') }} -
                        {{ $experience->currently_working ?  __('currently_working') :formatTime($experience->end, 'd M Y') }}
                    </td>
                    <td>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-icon" id="dropdownMenuButton5"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <svg width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M12 13.125C12.6213 13.125 13.125 12.6213 13.125 12C13.125 11.3787 12.6213 10.875 12 10.875C11.3787 10.875 10.875 11.3787 10.875 12C10.875 12.6213 11.3787 13.125 12 13.125Z"
                                        fill="#767F8C" stroke="#767F8C" />
                                    <path
                                        d="M12 6.65039C12.6213 6.65039 13.125 6.14671 13.125 5.52539C13.125 4.90407 12.6213 4.40039 12 4.40039C11.3787 4.40039 10.875 4.90407 10.875 5.52539C10.875 6.14671 11.3787 6.65039 12 6.65039Z"
                                        fill="#767F8C" stroke="#767F8C" />
                                    <path
                                        d="M12 19.6094C12.6213 19.6094 13.125 19.1057 13.125 18.4844C13.125 17.8631 12.6213 17.3594 12 17.3594C11.3787 17.3594 10.875 17.8631 10.875 18.4844C10.875 19.1057 11.3787 19.6094 12 19.6094Z"
                                        fill="#767F8C" stroke="#767F8C" />
                                </svg>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end company-dashboard-dropdown"
                                aria-labelledby="dropdownMenuButton5">
                                <li>
                                    <a href="javascript:void(0)" class="dropdown-item" onclick="experienceDetail({{ json_encode($experience) }}, '{{ date('d-m-Y', strtotime($experience->start)) }}', '{{ date('d-m-Y', strtotime($experience->end)) }}')">
                                        <x-svg.edit-icon/>
                                        {{ __('edit') }}
                                    </a>
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('candidate.experiences.destroy', $experience->id) }}">
                                        @csrf
                                        @method('Delete')
                                        <button type="submit" class="dropdown-item" onclick="return confirm('{{ __('are_you_sure_you_want_to_delete_this_item') }}');">
                                            <x-svg.trash-icon/>
                                            {{ __('delete') }}
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">
                        <x-svg.not-found-icon />
                        <p class="mt-4">{{ __('no_data_found') }}</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@push('frontend_links')
<link rel="stylesheet" href="{{ asset('frontend') }}/assets/css/bootstrap-datepicker.min.css">
<style>
    #addExperienceModal .modal-dialog,
    #editExperienceModal .modal-dialog{
        z-index:999999 !important;
        max-width: 950px !important;
        padding: 20px !important;
    }
</style>

@endpush

@push('frontend_scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('#addExperience').on('click', function () {
                $('#addExperienceModal').modal('show');
            });

            $('.date_picker').datepicker({
                format: "yyyy-mm-dd",
                autoclose: true
            });

            window.closeAddExperienceModal = function () {
                const form = $('#addExperienceModal').find('form')[0];
                if (form) form.reset();
                $('#addExperienceModal').modal('hide');
            }

            window.closeEditExperienceModal = function () {
                const form = $('#editExperienceModal').find('form')[0];
                if (form) form.reset();
                $('#editExperienceModal').modal('hide');
            }

            window.experienceDetail = function (experience, start, end) {
                $('#experience-modal-id').val(experience.id);
                $('#experience-modal-company').val(experience.company);
                $('#experience-modal-department').val(experience.department);
                $('#experience-modal-designation').val(experience.designation);
                $('#experience-modal-start').val(start);
                $('#experience-modal-end').val(end);
                $('#experience-responsibilities').val(experience.responsibilities);
                $('#experience-modal-checkbox_edit').prop("checked", experience.currently_working ? true : false);

                $('#editExperienceModal').modal('show');
            }
        });
    </script>
@endpush
