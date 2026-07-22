@extends('backend.layouts.app')
@section('title')
    {{ __('candidate_list') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h3 class="card-title line-height-36">{{ __('candidate_list') }}</h3>
                        <div>
                            <div class="btn-group">
                                <a href="#" class="btn bg-primary">
                                    <i class="fas fa-download mr-1"></i> Export
                                </a>
                                <button type="button" class="btn bg-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item export-filtered-btn" href="{{ route('candidate.export', 'csv') }}?{{ request()->getQueryString() }}">CSV (all filtered)</a>
                                    <a class="dropdown-item export-filtered-btn" href="{{ route('candidate.export', 'xlsx') }}?{{ request()->getQueryString() }}">Excel (all filtered)</a>
                                    <a class="dropdown-item export-filtered-btn" href="{{ route('candidate.export', 'pdf') }}?{{ request()->getQueryString() }}">PDF (all filtered, max 500)</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item export-candidate-btn" href="{{ route('candidate.export', 'csv') }}?{{ request()->getQueryString() }}" data-type="csv">CSV (selected on this page)</a>
                                    <a class="dropdown-item export-candidate-btn" href="{{ route('candidate.export', 'xlsx') }}?{{ request()->getQueryString() }}" data-type="xlsx">Excel (selected on this page)</a>
                                    <a class="dropdown-item export-candidate-btn" href="{{ route('candidate.export', 'pdf') }}?{{ request()->getQueryString() }}" data-type="pdf">PDF (selected on this page)</a>
                                    <!-- Add more options for different export formats if needed -->
                                </div>
                            </div>

                            @if (userCan('candidate.create'))
                                <a href="{{ route('candidate.create') }}" class="btn bg-primary"><i
                                        class="fas fa-plus mr-1"></i> {{ __('create') }}
                                </a>
                            @endif
                            @if (userCan('candidate.delete'))
                                <button type="button" class="btn bg-danger" id="bulk-delete">
                                    <i class="fas fa-trash mr-1"></i> {{ __('delete_selected') }}
                                </button>
                            @endif
                            @if (request()->query())
                                <a href="{{ route('candidate.index') }}" class="btn bg-danger"><i
                                        class="fas fa-times"></i>&nbsp; {{ __('clear') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Filter  --}}
                <form id="formSubmit" action="{{ route('candidate.index') }}" method="GET" onchange="this.submit();">
                    <div class="card-body border-bottom row">
                        <div class="col-12 mb-2"><strong>Advanced filters</strong> <span class="badge badge-primary">{{ collect(request()->query())->except(['page', 'sort_by'])->filter(fn ($value) => filled($value))->count() }} active</span></div>
                        <div class="col-lg-4 col-md-6 col-12">
                            <label>{{ __('search') }}</label>
                            <input name="keyword" type="text" placeholder="{{ __('search') }}" class="form-control"
                                value="{{ request('keyword') }}">
                        </div>
                        <div class="col-lg-4 col-md-6 col-12">
                            <label>{{ __('email_verification') }}</label>
                            <select name="ev_status" class="form-control select2bs4 w-100-p">
                                <option value="">
                                    {{ __('all') }}
                                </option>
                                <option {{ request('ev_status') == 'true' ? 'selected' : '' }} value="true">
                                    {{ __('verified') }}
                                </option>
                                <option {{ request('ev_status') == 'false' ? 'selected' : '' }} value="false">
                                    {{ __('not_verified') }}
                                </option>
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12"><label>Email</label><input name="email" type="search" class="form-control" value="{{ request('email') }}"></div>
                        <div class="col-lg-4 col-md-6 col-12"><label>Profession</label><select name="profession_ids[]" multiple class="form-control select2bs4">@foreach($filterOptions['professions'] as $option)<option value="{{ $option->id }}" @selected(in_array($option->id, request('profession_ids', [])))>{{ $option->name }}</option>@endforeach</select></div>
                        <div class="col-lg-4 col-md-6 col-12"><label>Job Role</label><select name="job_role_ids[]" multiple class="form-control select2bs4">@foreach($filterOptions['jobRoles'] as $option)<option value="{{ $option->id }}" @selected(in_array($option->id, request('job_role_ids', [])))>{{ $option->name }}</option>@endforeach</select></div>
                        <div class="col-lg-4 col-md-6 col-12"><label>Skills (any)</label><select name="skill_ids[]" multiple class="form-control select2bs4">@foreach($filterOptions['skills'] as $option)<option value="{{ $option->id }}" @selected(in_array($option->id, request('skill_ids', [])))>{{ $option->name }}</option>@endforeach</select></div>
                        <div class="col-lg-4 col-md-6 col-12"><label>Reference relation</label><select name="reference_relations[]" multiple class="form-control select2bs4">@foreach($filterOptions['referenceRelations'] as $option)<option value="{{ $option }}" @selected(in_array($option, request('reference_relations', [])))>{{ $option }}</option>@endforeach</select></div>
                        <div class="col-lg-4 col-md-6 col-12"><label>Preferred work location</label><select name="preferred_locations[]" multiple class="form-control select2bs4">@foreach($filterOptions['locations'] as $option)<option value="{{ $option }}" @selected(in_array($option, request('preferred_locations', [])))>{{ $option }}</option>@endforeach</select></div>
                        <div class="col-lg-2 col-md-4 col-6"><label>Min age</label><input name="min_age" type="number" min="0" max="120" class="form-control" value="{{ request('min_age') }}"></div>
                        <div class="col-lg-2 col-md-4 col-6"><label>Max age</label><input name="max_age" type="number" min="0" max="120" class="form-control" value="{{ request('max_age') }}"></div>
                        <div class="col-lg-2 col-md-4 col-6"><label>Experience min</label><input name="min_experience" type="number" min="0" step="0.5" class="form-control" value="{{ request('min_experience') }}"></div>
                        <div class="col-lg-2 col-md-4 col-6"><label>Experience max</label><input name="max_experience" type="number" min="0" step="0.5" class="form-control" value="{{ request('max_experience') }}"></div>
                        <div class="col-lg-2 col-md-4 col-6"><label>Created from</label><input name="created_from" type="date" class="form-control" value="{{ request('created_from') }}"></div>
                        <div class="col-lg-2 col-md-4 col-6"><label>Created to</label><input name="created_to" type="date" class="form-control" value="{{ request('created_to') }}"></div>
                        <div class="col-12 mt-2"><button class="btn btn-primary" type="submit">Apply filters</button> <a class="btn btn-outline-secondary" href="{{ route('candidate.index') }}">Clear all filters</a><span class="ml-2">{{ $candidates->total() }} matching candidates</span></div>
                        <div class="col-lg-4 col-md-6 col-12">
                            <label>{{ __('sort_by') }}</label>
                            <select name="sort_by" class="form-control select2bs4 w-100-p">
                                <option {{ !request('sort_by') || request('sort_by') == 'latest' ? 'selected' : '' }}
                                    value="latest" selected>
                                    {{ __('latest') }}
                                </option>
                                <option {{ request('sort_by') == 'oldest' ? 'selected' : '' }} value="oldest">
                                    {{ __('oldest') }}
                                </option>
                            </select>
                        </div>
                    </div>
                </form>

                {{-- Table  --}}
                <div class="card-body table-responsive p-0">
                    <div class="row">
                        <div class="col-sm-12 py-2" style="padding-left: 32px;">
                            <label class="d-inline-flex align-items-center gap-2">
                                <input type="checkbox" id="select-all" class="mr-2">
                                <span>Select all rows on this page</span>
                            </label>
                        </div>
                    </div>
                    <table class="ll-table table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th width="5%">{{ __('select') }}</th>
                                <th>{{ __('candidate') }}</th>
                                <th>{{ __('role') }}/{{ __('profession') }}</th><th>Experience</th>
                                <th>{{ __('profile_completion') }}</th>
                                <th>{{ __('applied_jobs') }}</th>
                                @if (userCan('candidate.update'))
                                <th width="10%">{{ __('account') }} {{ __('status') }}</th>
                                @endif
                                @if (userCan('candidate.update'))
                                <th>{{ __('email_verification') }}</th>
                                @endif
                                <th>{{ __('joined_date') }}</th>
                                @if (userCan('candidate.update') || userCan('candidate.delete'))
                                    <th width="12%">{{ __('action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @if ($candidates->count() > 0)
                                @foreach ($candidates as $candidate)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="candidate-checkbox" value="{{ $candidate->id }}">
                                        </td>
                                        <td tabindex="0">
                                            <a href="{{ route('candidate.show', $candidate->id) }}"  class="company">
                                                <img src="{{ $candidate->photo }}" alt="image">
                                                <div>
                                                    <h2>{{ $candidate->user->name }}</h2>
                                                    <p>{{ $candidate->user->email }}</p>
                                                </div>
                                            </a>
                                        </td>
                                        <td tabindex="0">
                                            <p class="job-role">{{ $candidate->jobRole->name ?? '' }}</p>
                                            <p class="profession">{{ $candidate->profession->name ?? '' }}</p>
                                        </td>
                                        <td>{{ $candidate->experience->name ?? '' }}</td>
                                        <td tabindex="0">
                                            @php($completion = $candidate->profile_completion_percentage)
                                            <div class="d-flex align-items-center">
                                                <span class="badge {{ $completion === 100 ? 'badge-success' : 'badge-warning' }} mr-2">
                                                    {{ $completion }}%
                                                </span>
                                                <div class="progress candidate-progress">
                                                    <div class="progress-bar {{ $completion === 100 ? 'bg-success' : 'bg-warning' }}"
                                                        role="progressbar" style="width: {{ $completion }}%;"
                                                        aria-valuenow="{{ $completion }}" aria-valuemin="0"
                                                        aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td tabindex="0">
                                            {{ $candidate->applied_jobs_count }} {{ __('applied_jobs') }}
                                        </td>
                                        @if (userCan('candidate.update'))
                                            <td tabindex="0">
                                                <a href="javascript:void(0)" class="active-status">
                                                    <label class="switch ">
                                                        <input data-id="{{ $candidate->user_id }}" type="checkbox"
                                                            class="success status-switch change-active-status"
                                                            {{ $candidate->user->status == 1 ? 'checked' : '' }}>
                                                        <span class="slider round"></span>
                                                    </label>
                                                    <p class="{{ $candidate->user->status == 1 ? 'active' : '' }}" id="status_{{ $candidate->user_id }}">
                                                        {{ $candidate->user->status == 1 ? __('activated') : __('deactivated') }}</p>
                                                </a>
                                            </td>
                                        @endif
                                        @if (userCan('candidate.update'))
                                            <td tabindex="0">
                                                <a href="javascript:void(0)" class="active-status">
                                                    <label class="switch ">
                                                        <input data-userid="{{ $candidate->user_id }}" type="checkbox"
                                                            class="success email-verification-switch"
                                                            {{ $candidate->user->email_verified_at ? 'checked' : '' }}>
                                                        <span class="slider round"></span>
                                                    </label>
                                                    <p class="{{ $candidate->user->email_verified_at ? 'active' : '' }}" id="verification_status_{{ $candidate->user_id }}">
                                                        {{ $candidate->user->email_verified_at ? __('verified') : __('unverified') }}</p>
                                                </a>
                                            </td>
                                        @endif
                                        <td tabindex="0">
                                            {{ Carbon\Carbon::parse($candidate->created_at)->format('d M, Y') }}
                                        </td>
                                        @if (userCan('candidate.update') || userCan('candidate.delete'))
                                            <td>
                                                @if (userCan('candidate.view'))
                                                    <a href="{{ route('candidate.show', $candidate->id) }}"
                                                        class="btn ll-btn ll-border-none">
                                                        {{__('view_profile')}}
                                                        <x-svg.table-btn-arrow />
                                                    </a>
                                                @endif
                                                @if (userCan('candidate.update'))
                                                    <a href="{{ route('candidate.edit', $candidate->id) }}"
                                                        class="btn ll-p-0">
                                                        <x-svg.table-edit />
                                                    </a>
                                                @endif
                                                @if (userCan('candidate.delete'))
                                                    <form action="{{ route('candidate.destroy', $candidate->id) }}"
                                                        method="POST" class="d-inline">
                                                        @method('DELETE')
                                                        @csrf
                                                        <button
                                                            onclick="return confirm('{{ __('are_you_sure_you_want_to_delete_this_item') }}');"
                                                            class="btn ll-p-0">
                                                            <x-svg.table-delete />
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="10">
                                        {{ __('no_data_found') }}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                    @if ($candidates->count())
                        <div class="mt-3 d-flex justify-content-center">
                            {{ $candidates->onEachSide(1)->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('style')
    <style>
        .switch {
            position: relative;
            display: inline-block;
            width: 35px;
            height: 19px;
        }

        /* Hide default HTML checkbox */
        .switch input {
            display: none;
        }

        /* The slider */
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 15px;
            width: 15px;
            left: 3px;
            bottom: 2px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .candidate-progress {
            width: 90px;
            height: 8px;
            border-radius: 100px;
        }

        input.success:checked+.slider {
            background-color: #28a745;
        }

        input:checked+.slider:before {
            -webkit-transform: translateX(15px);
            -ms-transform: translateX(15px);
            transform: translateX(15px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }
    </style>
@endsection

@section('script')
    <script src="{{ asset('backend') }}/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
    <script>
        $('.status-switch').on('change', function() {
            var status = $(this).prop('checked') == true ? 1 : 0;
            var id = $(this).data('id');
            $.ajax({
                type: "GET",
                dataType: "json",
                url: '{{ route('candidate.status.change') }}',
                data: {
                    'status': status,
                    'id': id
                },
                success: function(response) {
                    toastr.success(response.message, 'Success');
                }
            });

            if (status == 1) {
                $(`#status_${id}`).text("{{ __('activated') }}")
            }else{
                $(`#status_${id}`).text("{{ __('deactivated') }}")
            }
        });

        $('.email-verification-switch').on('change', function() {
            var status = $(this).prop('checked') == true ? 1 : 0;
            var id = $(this).data('userid');

            $.ajax({
                type: "GET",
                dataType: "json",
                url: '{{ route('company.verify.change') }}',
                data: {
                    'status': status,
                    'id': id
                },
                success: function(response) {
                    toastr.success(response.message, 'Success');
                }
            });

            if (status == 1) {
                $(`#verification_status_${id}`).text("{{ __('verified') }}")
            }else{
                $(`#verification_status_${id}`).text("{{ __('unverified') }}")
            }
        });

        // Add these new scripts for bulk delete functionality
        $(document).ready(function() {
            $('.export-candidate-btn').on('click', function(e) {
                e.preventDefault();

                var selectedCandidates = $('.candidate-checkbox:checked');
                if (selectedCandidates.length === 0) {
                    toastr.error('{{ __("please_select_at_least_one_candidate") }}');
                    return;
                }

                var candidateIds = [];
                selectedCandidates.each(function() {
                    candidateIds.push($(this).val());
                });

                var exportType = $(this).data('type');
                var exportUrl = '{{ route('candidate.export', ':type') }}'.replace(':type', exportType);
                var params = new URLSearchParams(window.location.search); params.set('ids', candidateIds.join(',')); window.location.href = exportUrl + '?' + params.toString();
            });

            // Select all checkbox functionality
            $('#select-all').change(function() {
                $('.candidate-checkbox').prop('checked', $(this).prop('checked'));
                toggleIndividualDeleteButtons();
            });

            // Individual checkbox change handler
            $('.candidate-checkbox').change(function() {
                toggleIndividualDeleteButtons();
            });

            // Function to toggle individual delete buttons
            function toggleIndividualDeleteButtons() {
                var hasSelected = $('.candidate-checkbox:checked').length > 0;
                $('.individual-delete-form').toggle(!hasSelected);
            }

            // Bulk delete button click handler
            $('#bulk-delete').click(function(e) {
                e.preventDefault();
                var selectedCandidates = $('.candidate-checkbox:checked');

                if (selectedCandidates.length === 0) {
                    toastr.error('{{ __("please_select_at_least_one_candidate") }}');
                    return;
                }

                if (confirm('{{ __("are_you_sure_you_want_to_delete_selected_candidates") }}')) {
                    var candidateIds = [];
                    selectedCandidates.each(function() {
                        candidateIds.push($(this).val());
                    });

                    // AJAX request to delete selected candidates
                    $.ajax({
                        url: '{{ route('candidate.bulk.delete') }}',
                        type: 'POST',
                        data: {
                            ids: candidateIds,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            toastr.success('{{ __("selected_candidates_deleted_successfully") }}');
                            window.location.reload();
                        },
                        error: function(xhr, status, error) {
                            toastr.error('{{ __("an_error_occurred") }}');
                            console.error(error);
                        }
                    });
                }
            });

            // Initial state check
            toggleIndividualDeleteButtons();
        });
    </script>
    <script>
        $(document).ready(function() {
            validate();
            $('#title').keyup(validate);
        });

        function validate() {
            if ($('#title')?.val()?.length > 0) {
                $('#crossB').removeClass('d-none');
            } else {
                $('#crossB').addClass('d-none');
            }
        }

        $('#formSubmit').on('change', function() {
            $(this).submit();
        });

        function RemoveFilter(id) {
            $('#' + id).val('');
            $('#formSubmit').submit();
        }
    </script>
@endsection
