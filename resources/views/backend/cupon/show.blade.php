@extends('backend.layouts.app')
@section('title', 'Coupon Details')

@section('content')
<div class="container">
    <h1>Coupon Details</h1>
    <table class="table table-bordered">
        <tr><th>ID</th><td>{{ $cupon->id }}</td></tr>
        <tr><th>Code</th><td>{{ $cupon->code }}</td></tr>
        <tr><th>Type</th><td>{{ $cupon->type }}</td></tr>
        <tr><th>Value</th><td>{{ $cupon->value }}</td></tr>
        <tr><th>Max Uses</th><td>{{ $cupon->max_uses }}</td></tr>
        <tr><th>Used</th><td>{{ $cupon->used }}</td></tr>
        <tr><th>Status</th><td>{{ $cupon->status ? 'Active' : 'Inactive' }}</td></tr>
        <tr><th>Expires At</th><td>{{ $cupon->expires_at }}</td></tr>
        <tr><th>Created At</th><td>{{ $cupon->created_at }}</td></tr>
        <tr><th>Updated At</th><td>{{ $cupon->updated_at }}</td></tr>
    </table>
    <a href="{{ route('cupon.edit', $cupon) }}" class="btn btn-warning">Edit</a>
    <a href="{{ route('cupon.index') }}" class="btn btn-secondary">Back</a>
</div>
@endsection 