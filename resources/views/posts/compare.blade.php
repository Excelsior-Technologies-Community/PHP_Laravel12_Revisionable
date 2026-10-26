@extends('layouts.app')

@section('content')

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-1">
            <i class="fa-solid fa-code-compare text-primary me-2"></i> Revision Comparison Studio
        </h3>
        <p class="text-muted mb-0">
            Compare any two arbitrary revisions of Post #{{ $post->id }} to inspect historical changes.
        </p>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('posts.revisions', $post) }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-list me-1"></i> Revision History
        </a>
        <a href="{{ route('posts.show', $post) }}" class="btn btn-dark">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Post
        </a>
    </div>
</div>

<!-- Comparison Selector Form -->
<div class="card shadow-sm p-4 mb-4 border">
    <form method="GET" action="{{ route('posts.revisions.compare', $post) }}" class="row g-3 align-items-end">
        <div class="col-md-5">
            <label class="form-label fw-bold text-danger">
                <i class="fa-solid fa-minus me-1"></i> Base / Older Revision (A):
            </label>
            <select name="rev_a" class="form-select">
                @foreach($allRevisions as $rev)
                <option value="{{ $rev->id }}" {{ $revAId == $rev->id ? 'selected' : '' }}>
                    Revision #{{ $rev->id }} - {{ $rev->created_at->format('d M Y, H:i') }} ({{ $rev->fieldName() }})
                </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-5">
            <label class="form-label fw-bold text-success">
                <i class="fa-solid fa-plus me-1"></i> Compared / Newer Revision (B):
            </label>
            <select name="rev_b" class="form-select">
                @foreach($allRevisions as $rev)
                <option value="{{ $rev->id }}" {{ $revBId == $rev->id ? 'selected' : '' }}>
                    Revision #{{ $rev->id }} - {{ $rev->created_at->format('d M Y, H:i') }} ({{ $rev->fieldName() }})
                </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100 fw-bold">
                <i class="fa-solid fa-arrows-split-up-and-left me-1"></i> Compare
            </button>
        </div>
    </form>
</div>

<!-- Diff Results -->
@if(!empty($diffs))
<div class="card shadow-sm mb-4">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger">Revision #{{ $revAId }}</span>
            <i class="fa-solid fa-arrow-right"></i>
            <span class="badge bg-success">Revision #{{ $revBId }}</span>
        </div>

        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-light active" id="btnModeInline">
                <i class="fa-solid fa-align-left me-1"></i> Unified Inline
            </button>
            <button type="button" class="btn btn-outline-light" id="btnModeSplit">
                <i class="fa-solid fa-columns me-1"></i> Side-by-Side Split
            </button>
        </div>
    </div>

    <div class="card-body p-4">

        @foreach($diffs as $key => $diff)
        <div class="mb-4">
            <h5 class="fw-bold text-dark border-bottom pb-2">
                <i class="fa-solid fa-tag text-primary me-2"></i> {{ $diff['label'] }}
            </h5>

            <!-- Unified Inline Container -->
            <div class="diff-box diff-inline-block">
                {!! $diff['inline_html'] !!}
            </div>

            <!-- Side-by-Side Split Container -->
            <div class="row g-2 diff-split-block d-none">
                <div class="col-md-6">
                    <div class="small fw-bold text-danger mb-1">Revision #{{ $revAId }}</div>
                    <div class="diff-box bg-light">{!! $diff['side_by_side']['old'] !!}</div>
                </div>
                <div class="col-md-6">
                    <div class="small fw-bold text-success mb-1">Revision #{{ $revBId }}</div>
                    <div class="diff-box bg-light">{!! $diff['side_by_side']['new'] !!}</div>
                </div>
            </div>
        </div>
        @endforeach

    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const inlineBtn = document.getElementById('btnModeInline');
    const splitBtn = document.getElementById('btnModeSplit');
    const inlineBlocks = document.querySelectorAll('.diff-inline-block');
    const splitBlocks = document.querySelectorAll('.diff-split-block');

    inlineBtn?.addEventListener('click', function () {
        this.classList.add('active');
        splitBtn.classList.remove('active');
        inlineBlocks.forEach(b => b.classList.remove('d-none'));
        splitBlocks.forEach(b => b.classList.add('d-none'));
    });

    splitBtn?.addEventListener('click', function () {
        this.classList.add('active');
        inlineBtn.classList.remove('active');
        inlineBlocks.forEach(b => b.classList.add('d-none'));
        splitBlocks.forEach(b => b.classList.remove('d-none'));
    });
});
</script>
@endsection
