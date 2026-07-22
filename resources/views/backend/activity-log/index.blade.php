@extends('backend.layouts.app')
@section('title')
    {{ __('activity_log') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('activity_log') }}</h3>
                </div>

                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>{{ __('date') }}</th>
                                <th>{{ __('admin') }}</th>
                                <th>{{ __('role') }}</th>
                                <th>{{ __('method') }}</th>
                                <th>{{ __('action') }}</th>
                                <th>{{ __('ip_address') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('d M, Y h:i A') }}</td>
                                    <td>{{ $log->admin_name ?? '-' }}</td>
                                    <td>{{ $log->roles ?? '-' }}</td>
                                    <td><span class="badge badge-info">{{ $log->method }}</span></td>
                                    <td>
                                        <div>{{ $log->route_name ?? '-' }}</div>
                                        <small class="text-muted">{{ $log->url }}</small>
                                    </td>
                                    <td>{{ $log->ip_address ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">{{ __('no_data_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($logs->count())
                    <div class="card-footer d-flex justify-content-center">
                        {{ $logs->onEachSide(1)->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
