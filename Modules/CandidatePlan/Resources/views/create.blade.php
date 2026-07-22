@extends('backend.layouts.app')

@section('title', __('Create Plan'))

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        {{ __('Create Plan') }}
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('module.candidateplan.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="name">{{ __('Plan Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">{{ __('Description') }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="price">{{ __('Price') }} <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" required>
                            @error('price')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="job_apply_limit">{{ __('Job Apply Limit') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('job_apply_limit') is-invalid @enderror" id="job_apply_limit" name="job_apply_limit" value="{{ old('job_apply_limit') }}" required>
                            @error('job_apply_limit')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="recommended" name="recommended" value="1" {{ old('recommended') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="recommended">{{ __('Recommended Plan') }}</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">{{ __('Active') }}</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Create Plan') }}
                            </button>
                            <a href="{{ route('module.candidateplan.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection 