@extends('backend.layouts.app')
@section('title')
    {{ __('add_coupon') }}
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title line-height-36">{{ __('add_coupon') }}</h3>
                    <a href="{{ route('cupon.index') }}"
                        class="btn bg-primary float-right d-flex align-items-center justify-content-center">
                        <i class="fas fa-arrow-left mr-1"></i> {{ __('back') }}
                    </a>
                </div>
                <div class="row pt-3 pb-4">
                    <div class="col-md-6 offset-md-3">
                        <form class="form-horizontal" action="{{ route('cupon.store') }}" method="POST">
                            @csrf

                            <div class="form-group row">
                                <x-forms.label name="{{ __('code') }}" class="col-sm-3" />
                                <div class="col-sm-9">
                                    <input type="text" name="code"
                                           class="form-control @error('code') is-invalid @enderror"
                                           value="{{ old('code') }}" required>
                                    @error('code')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <x-forms.label name="{{ __('type') }}" class="col-sm-3" />
                                <div class="col-sm-9">
                                    <select name="type"
                                            class="form-control select2bs4 @error('type') is-invalid @enderror"
                                            required>
                                        <option value="fixed">{{ __('Fixed') }}</option>
                                        <option value="percent">{{ __('Percent') }}</option>
                                    </select>
                                    @error('type')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <x-forms.label name="{{ __('value') }}" class="col-sm-3" />
                                <div class="col-sm-9">
                                    <input type="number" step="0.01" name="value"
                                           class="form-control @error('value') is-invalid @enderror"
                                           value="{{ old('value') }}" required>
                                    @error('value')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <x-forms.label name="{{ __('max_uses') }}" class="col-sm-3" />
                                <div class="col-sm-9">
                                    <input type="number" name="max_uses"
                                           class="form-control @error('max_uses') is-invalid @enderror"
                                           value="{{ old('max_uses') }}">
                                    @error('max_uses')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <x-forms.label name="{{ __('expires_at') }}" class="col-sm-3" />
                                <div class="col-sm-9">
                                    <input type="datetime-local" name="expires_at"
                                           class="form-control @error('expires_at') is-invalid @enderror"
                                           value="{{ old('expires_at') }}">
                                    @error('expires_at')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <x-forms.label name="{{ __('status') }}" class="col-sm-3" />
                                <div class="col-sm-9">
                                    <select name="status"
                                            class="form-control select2bs4 @error('status') is-invalid @enderror"
                                            required>
                                        <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                                    </select>
                                    @error('status')
                                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="offset-sm-3 col-sm-9">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-plus"></i> {{ __('save') }}
                                    </button>
                                    <a href="{{ route('cupon.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> {{ __('cancel') }}
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div> <!-- /.row -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('style')
    <style>
        .select2-results__option[aria-selected=true] {
            display: none;
        }

        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
            color: #fff;
            background: #007bff;
            border: 1px solid #007bff;
            border-radius: 30px;
        }

        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove {
            color: #fff;
        }
    </style>
@endsection
