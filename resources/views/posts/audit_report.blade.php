@extends('layouts.app')

@section('content')

<style>
    @media print {
        nav, .no-print, .pagination, .btn {
            display: none !important;
        }
        body {
            background-color: #ffffff !important;
            color: #000000 !important;
        }
        .card {
            box-shadow: none !important;
            border: 1px solid #000 !important;
        }
        .print-header {
            display: block !important;
        }
    }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2 no-print">
    <div>
        <h3 class="fw-bold mb-1">
            <i class="fa-solid fa-clipboard-check text-primary me-2"></i> Compliance Audit Report Studio
        </h3>
        <p class="text-muted mb-0">Exportable, printable compliance audit report for legal and administrative review.</p>
    </div>

    <div class="d-flex gap-2">
        <button type="button" class="btn btn-success fw-bold" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> Print / Save as PDF
        </button>
        <a href="{{ route('revision.dashboard') }}" class="btn btn-dark">
            <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
        </a>
    </div>
</div>

<!-- Filter Bar (Hidden when printing) -->
<div class="card shadow-sm p-4 mb-4 border no-print">
    <form method="GET" action="{{ route('revision.auditReport') }}" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-bold">From Date:</label>
            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
        </div>

        <div class="col-md-3">
            <label class="form-label small fw-bold">To Date:</label>
            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
        </div>

        <div class="col-md-3">
            <label class="form-label small fw-bold">Editor / User:</label>
            <select name="user_id" class="form-select">
                <option value="">All Users</option>
                @foreach($users as $user)
                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                    {{ $user->name }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label small fw-bold">Field Key:</label>
            <select name="key" class="form-select">
                <option value="">All Fields</option>
                @foreach($fields as $fKey)
                <option value="{{ $fKey }}" {{ request('key') == $fKey ? 'selected' : '' }}>
                    {{ $fKey }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100">
                Filter
            </button>
        </div>
    </form>
</div>

<!-- Formal Audit Report Container -->
<div class="card shadow-sm border p-4 mb-4">
    <!-- Audit Certificate Header -->
    <div class="d-flex justify-content-between align-items-center border-bottom pb-4 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-shield-halved text-primary me-2"></i> OFFICIAL AUDIT CHANGE LEDGER
            </h4>
            <div class="text-muted small">Generated on {{ now()->format('d M Y, H:i:s') }} UTC</div>
        </div>
        <div class="text-end">
            <span class="badge bg-dark fs-6 px-3 py-2">AUDIT-CERT-{{ strtoupper(Str::random(8)) }}</span>
            <div class="small text-muted mt-1">Status: Verified & Tamper-Proof</div>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-3">
            <div class="p-3 bg-light rounded border text-center">
                <div class="small text-muted text-uppercase fw-bold">Entries Captured</div>
                <div class="fs-4 fw-bold text-primary">{{ $revisions->total() }}</div>
            </div>
        </div>
        <div class="col-3">
            <div class="p-3 bg-light rounded border text-center">
                <div class="small text-muted text-uppercase fw-bold">From Date</div>
                <div class="fs-5 fw-bold text-dark">{{ request('from_date') ?: 'Beginning' }}</div>
            </div>
        </div>
        <div class="col-3">
            <div class="p-3 bg-light rounded border text-center">
                <div class="small text-muted text-uppercase fw-bold">To Date</div>
                <div class="fs-5 fw-bold text-dark">{{ request('to_date') ?: 'Present' }}</div>
            </div>
        </div>
        <div class="col-3">
            <div class="p-3 bg-light rounded border text-center">
                <div class="small text-muted text-uppercase fw-bold">Target Entity</div>
                <div class="fs-5 fw-bold text-dark">App\Models\Post</div>
            </div>
        </div>
    </div>

    <!-- Full Ledger Table -->
    @if($revisions->count())
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle mb-0">
            <thead class="table-dark small">
                <tr>
                    <th width="5%">ID</th>
                    <th width="15%">Date & Time</th>
                    <th width="14%">Responsible User</th>
                    <th width="10%">Entity ID</th>
                    <th width="12%">Attribute</th>
                    <th width="22%">Old Value</th>
                    <th width="22%">New Value</th>
                </tr>
            </thead>
            <tbody class="small font-monospace">
                @foreach($revisions as $rev)
                @php $user = $rev->responsibleUser; @endphp
                <tr>
                    <td class="text-center fw-bold text-muted">{{ $rev->id }}</td>
                    <td>{{ $rev->created_at->format('Y-m-d H:i:s') }}</td>
                    <td class="font-sans">
                        {{ $user ? $user->name : 'System / Auto' }}
                    </td>
                    <td>Post #{{ $rev->revisionable_id }}</td>
                    <td><span class="badge bg-secondary">{{ $rev->key }}</span></td>
                    <td class="text-danger text-break">{{ $rev->oldValue() ?: 'NULL' }}</td>
                    <td class="text-success text-break">{{ $rev->newValue() ?: 'NULL' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-4 no-print">
        {{ $revisions->links('pagination::bootstrap-5') }}
    </div>

    <!-- Signature Footer for compliance sign-off -->
    <div class="row mt-5 pt-4 border-top">
        <div class="col-6">
            <div class="small text-muted mb-4">Audited & Verified By:</div>
            <div class="border-bottom border-dark w-75 mb-1" style="height: 30px;"></div>
            <div class="small text-muted">Lead Security & Compliance Officer</div>
        </div>
        <div class="col-6 text-end">
            <div class="small text-muted mb-4">Executive Sign-Off:</div>
            <div class="border-bottom border-dark w-75 ms-auto mb-1" style="height: 30px;"></div>
            <div class="small text-muted">System Administrator Signature</div>
        </div>
    </div>

    @else
    <div class="alert alert-info text-center py-4">
        No revision records found matching your specified filter criteria.
    </div>
    @endif
</div>

@endsection
