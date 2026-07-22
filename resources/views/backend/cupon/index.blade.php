@extends('backend.layouts.app')

@section('title')
    {{ __('coupon_list') }}
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            {{-- Header --}}
            <div class="card-header">
                <div class="d-flex justify-content-between">
                    <h3 class="card-title line-height-36">{{ __('coupon_list') }}</h3>
                    <div>
                    

                        <a href="{{ route('cupon.create') }}" class="btn bg-primary">
                            <i class="fas fa-plus mr-1"></i> {{ __('create') }}
                        </a>

                        <button type="button" class="btn bg-danger" id="bulk-delete">
                            <i class="fas fa-trash mr-1"></i> {{ __('delete_selected') }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- Filter --}}
            <form id="formSubmit" action="{{ route('cupon.index') }}" method="GET">
                <div class="card-body border-bottom row">
                    <div class="col-lg-4 col-md-6 col-12">
                        <label>{{ __('search') }}</label>
                        <input name="keyword" type="text" class="form-control" placeholder="{{ __('search') }}"
                            value="{{ request('keyword') }}">
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <label>{{ __('status') }}</label>
                        <select name="status" class="form-control select2bs4 w-100-p">
                            <option value="">{{ __('all') }}</option>
                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>{{ __('active') }}</option>
                            <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>{{ __('inactive') }}</option>
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <label>{{ __('sort_by') }}</label>
                        <select name="sort_by" class="form-control select2bs4 w-100-p">
                            <option value="latest" {{ request('sort_by') == 'latest' ? 'selected' : '' }}>{{ __('latest') }}</option>
                            <option value="oldest" {{ request('sort_by') == 'oldest' ? 'selected' : '' }}>{{ __('oldest') }}</option>
                        </select>
                    </div>
                </div>
            </form>

            {{-- Table --}}
            <div class="card-body table-responsive p-0">
                <div class="row">
                    <div class="col-sm-12 py-2 pl-4">
                        <label class="d-inline-flex align-items-center gap-2">
                            <input type="checkbox" id="select-all" class="mr-2">
                            <span>{{ __('select_all') }}</span>
                        </label>
                    </div>
                </div>

                <table class="ll-table table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th width="5%">{{ __('select') }}</th>
                            <th>{{ __('code') }}</th>
                            <th>{{ __('type') }}</th>
                            <th>{{ __('value') }}</th>
                            <th>{{ __('max_uses') }}</th>
                            <th>{{ __('used') }}</th>
                            <th>{{ __('status') }}</th>
                            <th>{{ __('expires_at') }}</th>
                            <th>{{ __('action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($coupons as $coupon)
                            <tr>
                                <td><input type="checkbox" class="coupon-checkbox" value="{{ $coupon->id }}"></td>
                                <td>{{ $coupon->code }}</td>
                                <td>{{ ucfirst($coupon->type) }}</td>
                                <td>{{ $coupon->value }}</td>
                                <td>{{ $coupon->max_uses ?? '∞' }}</td>
                                <td>{{ $coupon->used }}</td>
                                <td>
                                    <label class="switch">
                                        <input type="checkbox" class="status-switch" data-id="{{ $coupon->id }}" {{ $coupon->status ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td>{{ $coupon->expires_at }}</td>
                                <td>
                                    <a href="{{ route('cupon.show', $coupon) }}" class="btn ll-btn ll-border-none">{{ __('view') }}</a>
                                    <a href="{{ route('cupon.edit', $coupon) }}" class="btn ll-p-0"><x-svg.table-edit /></a>
                                    <form action="{{ route('cupon.destroy', $coupon) }}" method="POST" class="d-inline individual-delete-form">
                                        @csrf @method('DELETE')
                                        <button onclick="return confirm('{{ __('are_you_sure_you_want_to_delete_this_item') }}');" class="btn ll-p-0">
                                            <x-svg.table-delete />
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-danger py-4">{{ __('no_data_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($coupons->count())
                    <div class="mt-3 d-flex justify-content-center">
                        {{ $coupons->onEachSide(1)->links() }}
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
    .switch input {
        display: none;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #ccc;
        transition: .4s;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 15px; width: 15px;
        left: 3px; bottom: 2px;
        background-color: white;
        transition: .4s;
    }
    input:checked + .slider {
        background-color: #28a745;
    }
    input:checked + .slider:before {
        transform: translateX(15px);
    }
    .slider.round {
        border-radius: 34px;
    }
    .slider.round:before {
        border-radius: 50%;
    }
</style>
@endsection

@section('script')
<script>
    $('#select-all').on('change', function () {
        $('.coupon-checkbox').prop('checked', this.checked);
    });

    $('#bulk-delete').on('click', function () {
        let selected = $('.coupon-checkbox:checked');
        if (selected.length == 0) {
            return toastr.error('{{ __("please_select_at_least_one_coupon") }}');
        }
        if (confirm('{{ __("are_you_sure_you_want_to_delete_selected_coupons") }}')) {
            let ids = [];
            selected.each(function () {
                ids.push($(this).val());
            });

            $.ajax({
                url: '{{ route('cupon.bulk.delete') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    ids: ids
                },
                success: function (res) {
                    toastr.success('{{ __("selected_coupons_deleted_successfully") }}');
                    location.reload();
                },
                error: function () {
                    toastr.error('{{ __("an_error_occurred") }}');
                }
            });
        }
    });

    $('.status-switch').on('change', function () {
        let status = $(this).prop('checked') ? 1 : 0;
        let id = $(this).data('id');
        $.ajax({
            url: '{{ route('cupon.status.change') }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: id,
                status: status
            },
            success: function (res) {
                toastr.success('{{ __("status_updated_successfully") }}');
            }
        });
    });

    $('#formSubmit').on('change', function () {
        this.submit();
    });
</script>
@endsection
