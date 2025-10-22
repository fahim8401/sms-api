@extends('admin.layout')

@section('title', 'Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gateway Settings</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createGatewayModal">
        <i class="bi bi-plus"></i> Add Gateway
    </button>
</div>

<!-- Gateways Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>API URL</th>
                        <th>API Key</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($gateways as $gateway)
                        <tr>
                            <td>{{ $gateway->id }}</td>
                            <td>{{ $gateway->name }}</td>
                            <td>{{ $gateway->api_url }}</td>
                            <td>{{ Str::limit($gateway->api_key, 20) }}</td>
                            <td>
                                <span class="badge bg-{{ $gateway->status == 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($gateway->status) }}
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editGatewayModal{{ $gateway->id }}">
                                    Edit
                                </button>
                            </td>
                        </tr>

                        <!-- Edit Gateway Modal -->
                        <div class="modal fade" id="editGatewayModal{{ $gateway->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('admin.gateways.update', $gateway->id) }}">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Gateway</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Name</label>
                                                <input type="text" name="name" class="form-control" value="{{ $gateway->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">API URL</label>
                                                <input type="url" name="api_url" class="form-control" value="{{ $gateway->api_url }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">API Key</label>
                                                <input type="text" name="api_key" class="form-control" value="{{ $gateway->api_key }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Secret Key</label>
                                                <input type="text" name="secret_key" class="form-control" value="{{ $gateway->secret_key }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-select" required>
                                                    <option value="active" {{ $gateway->status == 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="inactive" {{ $gateway->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Update Gateway</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No gateways configured</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Gateway Modal -->
<div class="modal fade" id="createGatewayModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.gateways.create') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Gateway</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" placeholder="DigitalSquare" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">API URL</label>
                        <input type="url" name="api_url" class="form-control" placeholder="http://isms.digitalsquare.ltd:5683" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">API Key</label>
                        <input type="text" name="api_key" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Secret Key</label>
                        <input type="text" name="secret_key" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create Gateway</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
