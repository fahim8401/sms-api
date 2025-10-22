@extends('admin.layout')

@section('title', 'Invoices')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Generate Invoice</h2>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Download Monthly Invoice</h5>
    </div>
    <div class="card-body">
        <p>Select a month to generate and download your invoice as PDF</p>
        <form method="POST" action="{{ route('reseller.invoices.generate') }}">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Month</label>
                        <input type="month" name="month" class="form-control" value="{{ now()->format('Y-m') }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block w-100">
                        <i class="bi bi-download"></i> Generate & Download PDF
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
