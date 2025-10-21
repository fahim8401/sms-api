@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Admin Dashboard</h2>
    <div class="text-muted">Welcome, {{ auth()->user()->name }}</div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="card-title">Total Users</h6>
                <h2 class="mb-0">{{ $stats['total_users'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="card-title">Total SMS Sent</h6>
                <h2 class="mb-0">{{ number_format($stats['total_sms']) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="card-title">Total Revenue</h6>
                <h2 class="mb-0">৳{{ number_format($stats['total_revenue'], 2) }}</h2>
            </div>
        </div>
    </div>
</div>

<!-- SMS Status Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-warning">
            <div class="card-body">
                <h6 class="card-title text-warning">Pending SMS</h6>
                <h3 class="mb-0">{{ number_format($stats['pending_sms']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-primary">
            <div class="card-body">
                <h6 class="card-title text-primary">Sent SMS</h6>
                <h3 class="mb-0">{{ number_format($stats['sent_sms']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-success">
            <div class="card-body">
                <h6 class="card-title text-success">Delivered SMS</h6>
                <h3 class="mb-0">{{ number_format($stats['delivered_sms']) }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Recent SMS -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Recent SMS</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>To</th>
                        <th>Sender ID</th>
                        <th>Message</th>
                        <th>Cost</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentSms as $sms)
                        <tr>
                            <td>{{ $sms->id }}</td>
                            <td>{{ $sms->user->name }}</td>
                            <td>{{ $sms->to }}</td>
                            <td>{{ $sms->senderid }}</td>
                            <td>{{ Str::limit($sms->message, 30) }}</td>
                            <td>৳{{ number_format($sms->cost, 2) }}</td>
                            <td>
                                @if($sms->status == 'delivered')
                                    <span class="badge bg-success">Delivered</span>
                                @elseif($sms->status == 'sent')
                                    <span class="badge bg-primary">Sent</span>
                                @elseif($sms->status == 'failed')
                                    <span class="badge bg-danger">Failed</span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
                                @endif
                            </td>
                            <td>{{ $sms->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No SMS logs found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
