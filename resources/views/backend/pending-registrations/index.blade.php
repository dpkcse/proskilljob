@extends('backend.layouts.app')

@section('title', 'Pending Registrations')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <h3 class="card-title line-height-36">Pending Registrations</h3>
                    <form method="GET" action="{{ route('pending-registration.index') }}" class="form-inline">
                        <input type="search" name="search" value="{{ request('search') }}"
                            class="form-control mr-2" placeholder="Name or email">
                        <select name="status" class="form-control mr-2">
                            <option value="">All statuses</option>
                            <option value="active" @selected(request('status') === 'active')>Active</option>
                            <option value="expired" @selected(request('status') === 'expired')>Expired</option>
                            <option value="mail_failed" @selected(request('status') === 'mail_failed')>Mail failed</option>
                        </select>
                        <button class="btn btn-primary" type="submit">Filter</button>
                    </form>
                </div>
            </div>

            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Type</th>
                            <th>Registered</th>
                            <th>Expires</th>
                            <th>Status</th>
                            <th>Mail attempts</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingUsers as $pendingUser)
                            <tr>
                                <td>{{ $pendingUser->name }}</td>
                                <td>{{ $pendingUser->email }}</td>
                                <td><span class="badge badge-info">{{ ucfirst($pendingUser->role) }}</span></td>
                                <td>{{ $pendingUser->created_at?->format('d M Y, h:i A') }}</td>
                                <td>{{ $pendingUser->expires_at?->format('d M Y, h:i A') }}</td>
                                <td>
                                    @if ($pendingUser->verification_last_error)
                                        <span class="badge badge-danger" title="{{ $pendingUser->verification_last_error }}">Mail failed</span>
                                    @elseif ($pendingUser->isExpired())
                                        <span class="badge badge-secondary">Expired</span>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $pendingUser->verification_send_attempts }}
                                    @if ($pendingUser->last_verification_sent_at)
                                        <small class="d-block text-muted">{{ $pendingUser->last_verification_sent_at->diffForHumans() }}</small>
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('pending-registration.resend', $pendingUser) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary"
                                            onclick="return confirm('Send a new verification link and extend the expiry by 24 hours?')">
                                            <i class="fas fa-paper-plane"></i> Resend
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('pending-registration.destroy', $pendingUser) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Delete this pending registration?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-4">No pending registrations found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($pendingUsers->hasPages())
                <div class="card-footer d-flex justify-content-center">
                    {{ $pendingUsers->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
