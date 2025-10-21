@extends('admin.layout')

@section('title', 'Reseller Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Reseller Dashboard</h2>
    <div class="text-muted">Welcome, {{ auth()->user()->name }}</div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="card-title">Balance</h6>
                <h2 class="mb-0">৳{{ number_format($stats['balance'], 2) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="card-title">SMS Rate</h6>
                <h2 class="mb-0">৳{{ number_format($stats['rate'], 2) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="card-title">Sub Users</h6>
                <h2 class="mb-0">{{ $stats['total_sub_users'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6 class="card-title">Total SMS</h6>
                <h2 class="mb-0">{{ number_format($stats['total_sms']) }}</h2>
            </div>
        </div>
    </div>
</div>

<!-- This Month Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">This Month SMS</h6>
                <h3 class="mb-0">{{ number_format($stats['this_month_sms']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">This Month Cost</h6>
                <h3 class="mb-0">৳{{ number_format($stats['this_month_cost'], 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- API Information -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">API Information</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label fw-bold">API Key</label>
            <div class="input-group">
                <input type="text" class="form-control" value="{{ auth()->user()->api_key }}" readonly id="apiKey">
                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('apiKey')">
                    <i class="bi bi-clipboard"></i> Copy
                </button>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">Send SMS API</label>
            <input type="text" class="form-control" value="{{ config('app.url') }}/api/smsapi2?api_key=YOUR_API_KEY&type=text&contacts=PHONE&senderid=SENDER&msg=MESSAGE" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">Check Balance API</label>
            <input type="text" class="form-control" value="{{ config('app.url') }}/api/getBalance?api_key=YOUR_API_KEY" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">Get DLR API</label>
            <input type="text" class="form-control" value="{{ config('app.url') }}/api/getDLR?message_id=MESSAGE_ID" readonly>
        </div>
    </div>
</div>

<!-- Sub Users -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Sub Users</h5>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createSubUserModal">
            <i class="bi bi-plus"></i> Add Sub User
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Balance</th>
                        <th>Rate</th>
                        <th>SMS Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subUsers as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>৳{{ number_format($user->balance, 2) }}</td>
                            <td>৳{{ number_format($user->rate, 2) }}</td>
                            <td>{{ $user->sms_logs_count }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#transferModal{{ $user->id }}">
                                    Transfer Credit
                                </button>
                            </td>
                        </tr>

                        <!-- Transfer Credit Modal -->
                        <div class="modal fade" id="transferModal{{ $user->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('reseller.sub-users.transfer', $user->id) }}">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Transfer Credit to {{ $user->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Your Balance: <strong>৳{{ number_format($stats['balance'], 2) }}</strong></p>
                                            <div class="mb-3">
                                                <label class="form-label">Amount</label>
                                                <input type="number" name="amount" class="form-control" step="0.01" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Transfer</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No sub users yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
                            <td colspan="6" class="text-center">No SMS logs found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Sub User Modal -->
<div class="modal fade" id="createSubUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('reseller.sub-users.create') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create Sub User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Your Balance: <strong>৳{{ number_format($stats['balance'], 2) }}</strong></p>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Initial Balance</label>
                        <input type="number" name="balance" class="form-control" step="0.01" value="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">SMS Rate</label>
                        <input type="number" name="rate" class="form-control" step="0.01" value="0.30" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    document.execCommand('copy');
    alert('Copied to clipboard!');
}
</script>
@endsection
