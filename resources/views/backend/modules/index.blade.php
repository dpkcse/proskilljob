@extends('backend.settings.setting-layout')
@section('title')
    {{ __('module_management') }}
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title line-height-36">{{ __('module_management') }}</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('modules.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                @foreach($modules as $module => $status)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card {{ $status ? 'border-success' : 'border-secondary' }}">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="card-title mb-0">{{ ucfirst($module) }}</h5>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" 
                                                           id="module_{{ $module }}" 
                                                           name="modules[{{ $module }}]" 
                                                           value="1" 
                                                           data-bootstrap-switch data-on-color="success" data-off-color="default" data-on-text="{{ __('on') }}" data-off-text="{{ __('off') }}" data-size="small"
                                                           {{ $status ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="module_{{ $module }}">
                                                        {{ $status ? __('enabled') : __('disabled') }}
                                                    </label>
                                                </div>
                                            </div>
                                            <p class="card-text text-muted mt-2">
                                                {{ __('module_description_' . strtolower($module)) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-1"></i>
                                        {{ __('save_changes') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
<style>
    .module-card {
        transition: all 0.3s ease;
    }
    .module-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .form-check-input:checked {
        background-color: #28a745;
        border-color: #28a745;
    }
</style>
@endpush
