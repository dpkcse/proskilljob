@props(['educations'])
<div class="tw-flex rt-mb-32 lg:tw-mt-0 tw-items-center tw-justify-between">
    <h3 class="f-size-18 lh-1 m-0">{{ __('educations') }}</h3>
    <button id="addEducation" type="button" class="btn btn-primary ">
        {{ __('add_education') }}
    </button>
</div>
<div class="db-job-card-table -tw-mx-2">
    <table class="tw-px-2">
        <thead>
            <tr>
                <th class="!tw-text-base !tw-font-medium">{{ __('exam_name') }}</th>
                <th class="!tw-text-base !tw-font-medium">{{ __('degree_name') }}</th>
                <th class="!tw-text-base !tw-font-medium">{{ __('passing_year') }}</th>
                <th class="!tw-text-base !tw-font-medium">{{ __('result') }}</th>
                <th class="!tw-text-base !tw-font-medium tw-text-right">{{ __('action') }}</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($educations as $education)
                <tr>
                    <td>{{ $education->exam_name ?? $education->level }}</td>
                    <td>{{ $education->degree_name ?? $education->degree }}</td>
                    <td>{{ $education->passing_year ?? $education->year }}</td>
                    <td>
                        @if($education->result)
                            {{ $education->result_type ? __(str_replace('gpa_5', 'gpa_5', $education->result_type)) . ': ' : '' }}{{ number_format((float) $education->result, 2) }}
                        @else
                            -
                        @endif
                    </td>

                    <td>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-icon" id="dropdownMenuButton5"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
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
                                    <a href="javascript:void(0)" class="dropdown-item"
                                        onclick="educationDetail({{ json_encode([
                                                                    'id' => $education->id,
                                                                    'is_institute_accredited' => $education->is_institute_accredited,
                                                                    'exam_name' => $education->exam_name,
                                                                    'degree_name' => $education->degree_name,
                                                                    'major_subject' => $education->major_subject,
                                                                    'education_institution_id' => $education->education_institution_id,
                                                                    'institute_name' => $education->institute_name,
                                                                    'passing_year' => $education->passing_year,
                                                                    'result_type' => $education->result_type,
                                                                    'result' => $education->result,
                                                                    'board' => $education->board,
                                                                    'skill_ids' => $education->skills->pluck('id')->values(),
                                                                ]) }})">
                                        <x-svg.edit-icon />
                                        {{ __('edit') }}
                                    </a>
                                </li>
                                <li>
                                    <form method="POST"
                                        action="{{ route('candidate.educations.destroy', $education->id) }}">
                                        @csrf
                                        @method('Delete')
                                        <button type="submit" class="dropdown-item"
                                            onclick="return confirm('{{ __('are_you_sure_you_want_to_delete_this_item') }}');">
                                            <x-svg.trash-icon />
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
                    <td colspan="5" class="text-center">
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
        #addEducationModal .modal-dialog,
        #editEducationModal .modal-dialog {
            z-index: 999999 !important;
            max-width: 950px !important;
            padding: 20px;
        }
        .select2-container--open,
        .select2-dropdown {
             z-index: 99999 !important;
        }
        .modal-body { overflow: visible !important; }

    </style>
@endpush

@push('frontend_scripts')
<script>
  function initSelect2InModal(modalId) {
    const $modal = $(modalId);
    const $selects = $modal.find('.select2-taggable');

    $selects.each(function () {
      // Already initialized হলে destroy করে নতুন করে init
      if ($(this).hasClass('select2-hidden-accessible')) {
        $(this).select2('destroy');
      }
    });

    $selects.select2({
      dropdownParent: $modal,
      width: '100%',
      tags: true
    });
  }

  // Global functions
  window.closeAddEducationModal = function () {
    const $m = $('#addEducationModal');
    const form = $m.find('form')[0];
    if (form) form.reset();

    // select2 reset (init থাকলে)
    $m.find('select').val(null).trigger('change');

    $m.modal('hide');
  }

  window.closeEditEducationModal = function () {
    const $m = $('#editEducationModal');
    const form = $m.find('form')[0];
    if (form) form.reset();

    $m.find('select').val(null).trigger('change');

    $('#edu-accredit-yes').prop('checked', false);
    $('#edu-accredit-no').prop('checked', false);

    $m.modal('hide');
  }

  window.educationDetail = function (education) {
    $('#education-modal-id').val(education.id);

    // radio
    $('#edu-accredit-yes').prop('checked', education.is_institute_accredited == 1);
    $('#edu-accredit-no').prop('checked', education.is_institute_accredited == 0);

    // fields
    $('#education-modal-exam').val(education.exam_name || '').trigger('change');
    $('#education-modal-degree-name').val(education.degree_name || '').trigger('change');
    $('#education-modal-major').val(education.major_subject || '');
   
    $('#education-modal-inst').val(education.institute_name || '').trigger('change');

    $('#education-modal-year').val(education.passing_year || '');
    $('#education-modal-result-type').val(education.result_type || '').trigger('change');
    $('#education-modal-result').val(education.result || '');
    $('#education-modal-board').val(education.board || '');

    if (education.skill_ids) {
      $('#education-modal-skills').val(education.skill_ids).trigger('change');
    } else {
      $('#education-modal-skills').val(null).trigger('change');
    }

    $('#editEducationModal').modal('show');
  }

  document.addEventListener('DOMContentLoaded', function () {
    $('#addEducation').on('click', function () {
      $('#addEducationModal').modal('show');
    });

    // year picker
    $('.year_picker').datepicker({
      format: 'yyyy',
      viewMode: "years",
      minViewMode: "years",
      autoclose: true
    });

    // modal open হলে select2 init (BEST)
    $('#addEducationModal').on('shown.bs.modal', function () {
      initSelect2InModal('#addEducationModal');
    });

    $('#editEducationModal').on('shown.bs.modal', function () {
      initSelect2InModal('#editEducationModal');
    });
  });
</script>

@endpush
