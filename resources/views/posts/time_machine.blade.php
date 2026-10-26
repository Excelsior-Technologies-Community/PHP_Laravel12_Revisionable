@extends('layouts.app')

@section('content')

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-1">
            <i class="fa-solid fa-clock text-primary me-2"></i> Time-Machine Snapshot Previewer
        </h3>
        <p class="text-muted mb-0">
            Scrub backward through time to inspect and restore the exact historical state of post #{{ $post->id }}.
        </p>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('posts.revisions', $post) }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-list me-1"></i> Revision Ledger
        </a>
        <a href="{{ route('posts.show', $post) }}" class="btn btn-dark">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Post
        </a>
    </div>
</div>

<!-- Interactive Scrubber Control Card -->
<div class="card timeline-slider-card p-4 shadow-lg mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <span class="badge bg-info text-dark px-3 py-2 fw-bold text-uppercase fs-6">
                Step <span id="currentStepDisplay">{{ count($timeline) }}</span> of {{ count($timeline) }}
            </span>
            <span class="ms-2 opacity-75" id="timelineStepMeta">Live Current State</span>
        </div>

        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-light" id="btnStepPrev">
                <i class="fa-solid fa-chevron-left me-1"></i> Older
            </button>
            <button type="button" class="btn btn-outline-light" id="btnStepNext">
                Newer <i class="fa-solid fa-chevron-right ms-1"></i>
            </button>
            <button type="button" class="btn btn-primary" id="btnStepLatest">
                <i class="fa-solid fa-forward-fast me-1"></i> Latest (Live)
            </button>
        </div>
    </div>

    <!-- Scrubber Range Slider -->
    <div class="py-2">
        <input type="range"
            class="time-slider"
            id="timeMachineSlider"
            min="1"
            max="{{ count($timeline) }}"
            value="{{ count($timeline) }}"
            step="1">
    </div>

    <div class="d-flex justify-content-between text-white-50 small mt-1">
        <span><i class="fa-solid fa-circle-dot me-1 text-success"></i> Initial Creation</span>
        <span>Drag slider to time-travel</span>
        <span><i class="fa-solid fa-bolt me-1 text-warning"></i> Current State</span>
    </div>
</div>

<!-- Restoring Action Banner & Preview Card -->
<div class="card shadow-sm border mb-4">
    <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center py-3">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary px-3 py-2 fs-6" id="snapshotBadge">
                Snapshot: Live
            </span>
            <span class="text-muted small" id="snapshotDateText">
                Last modified: {{ $post->updated_at->format('d M Y, H:i') }}
            </span>
        </div>

        <div class="d-flex gap-2">
            <!-- Copy button -->
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCopySnapshot">
                <i class="fa-regular fa-copy me-1"></i> Copy Text
            </button>

            <!-- Restore Button -->
            <form id="restoreSnapshotForm" method="POST" action="">
                @csrf
                <button type="submit" class="btn btn-sm btn-success fw-bold" id="btnRestoreSnapshot" onclick="return confirm('Restore this historical snapshot as the current live post?')">
                    <i class="fa-solid fa-rotate-left me-1"></i> Restore Post to this Snapshot
                </button>
            </form>
        </div>
    </div>

    <div class="card-body p-4">

        <!-- Snapshot Content Display -->
        <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-success" id="previewStatusBadge">Published</span>
                <span class="text-muted small">Author: <strong id="previewAuthor">{{ $post->user->name ?? 'Unknown' }}</strong></span>
            </div>
            <h2 class="fw-bold mb-3 text-dark" id="previewTitle">{{ $post->title }}</h2>
        </div>

        <hr>

        <div class="p-3 bg-light rounded border">
            <div class="text-muted small fw-bold text-uppercase mb-2">Article Content:</div>
            <div class="fs-5 text-secondary" id="previewContent" style="white-space: pre-wrap; line-height: 1.7;">{{ $post->content }}</div>
        </div>

    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const timelineData = @json($timeline);
    const totalSteps = timelineData.length;
    const postId = {{ $post->id }};

    const slider = document.getElementById('timeMachineSlider');
    const stepDisplay = document.getElementById('currentStepDisplay');
    const stepMeta = document.getElementById('timelineStepMeta');
    const snapshotBadge = document.getElementById('snapshotBadge');
    const snapshotDateText = document.getElementById('snapshotDateText');
    const previewTitle = document.getElementById('previewTitle');
    const previewContent = document.getElementById('previewContent');
    const previewAuthor = document.getElementById('previewAuthor');
    const previewStatusBadge = document.getElementById('previewStatusBadge');
    const restoreForm = document.getElementById('restoreSnapshotForm');
    const restoreBtn = document.getElementById('btnRestoreSnapshot');

    function updatePreview(stepIndex) {
        const item = timelineData[stepIndex - 1];
        if (!item) return;

        stepDisplay.innerText = item.step;
        stepMeta.innerText = item.revision_id === 0 ? 'Live Current State' : `Modified '${item.field}' by ${item.user_name} (${item.date})`;

        const snapshot = item.snapshot;
        previewTitle.innerText = snapshot.title || '(No Title)';
        previewContent.innerText = snapshot.content || '(No Content)';
        previewAuthor.innerText = snapshot.user_name || 'System';

        if (snapshot.is_published) {
            previewStatusBadge.className = 'badge bg-success';
            previewStatusBadge.innerText = 'Published';
        } else {
            previewStatusBadge.className = 'badge bg-warning text-dark';
            previewStatusBadge.innerText = 'Draft';
        }

        if (item.revision_id === 0) {
            snapshotBadge.innerText = 'Snapshot: Live State';
            snapshotBadge.className = 'badge bg-primary px-3 py-2 fs-6';
            snapshotDateText.innerText = `Currently active on site`;
            restoreBtn.disabled = true;
            restoreBtn.classList.add('opacity-50');
            restoreForm.action = '#';
        } else {
            snapshotBadge.innerText = `Snapshot: Revision #${item.revision_id}`;
            snapshotBadge.className = 'badge bg-warning text-dark px-3 py-2 fs-6';
            snapshotDateText.innerText = `As of ${item.date} (${item.relative_time})`;
            restoreBtn.disabled = false;
            restoreBtn.classList.remove('opacity-50');
            restoreForm.action = `/posts/${postId}/revisions/${item.revision_id}/rollback-snapshot`;
        }
    }

    slider.addEventListener('input', function () {
        updatePreview(parseInt(this.value));
    });

    document.getElementById('btnStepPrev')?.addEventListener('click', function () {
        let val = parseInt(slider.value);
        if (val > 1) {
            slider.value = val - 1;
            updatePreview(val - 1);
        }
    });

    document.getElementById('btnStepNext')?.addEventListener('click', function () {
        let val = parseInt(slider.value);
        if (val < totalSteps) {
            slider.value = val + 1;
            updatePreview(val + 1);
        }
    });

    document.getElementById('btnStepLatest')?.addEventListener('click', function () {
        slider.value = totalSteps;
        updatePreview(totalSteps);
    });

    document.getElementById('btnCopySnapshot')?.addEventListener('click', function () {
        const text = `${previewTitle.innerText}\n\n${previewContent.innerText}`;
        navigator.clipboard.writeText(text).then(() => {
            alert('Snapshot title and content copied to clipboard!');
        });
    });

    // Initialize to latest
    updatePreview(totalSteps);
});
</script>
@endsection
