@extends('layouts.app')

@section('content')

<div class="card shadow mb-4">
    <div class="card-header card-header-gradient d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="mb-0 fw-bold">
                <i class="fa-solid fa-clock-rotate-left me-2"></i> Revision History
            </h5>
            <small class="text-white-50">
                Post: <span class="fw-semibold text-white">#{{ $post->id }} - {{ $post->title }}</span>
            </small>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <!-- Time Machine Button -->
            <a href="{{ route('posts.timeMachine', $post) }}" class="btn btn-sm btn-light fw-bold text-dark">
                <i class="fa-solid fa-clock me-1 text-primary"></i> ⏳ Time-Machine
            </a>

            <!-- Compare Revisions Button -->
            <a href="{{ route('posts.revisions.compare', $post) }}" class="btn btn-sm btn-outline-light">
                <i class="fa-solid fa-code-compare me-1"></i> Compare Revisions
            </a>

            <!-- Export CSV -->
            <a href="{{ route('posts.revisions.export', $post) }}" class="btn btn-sm btn-success">
                <i class="fa-solid fa-file-csv me-1"></i> Export CSV
            </a>

            <!-- Delete All -->
            <form action="{{ route('posts.revisions.deleteAll', $post) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-light" onclick="return confirm('Are you sure you want to permanently delete all revision history for this post?')">
                    <i class="fa-solid fa-trash me-1"></i> Purge All
                </button>
            </form>

            <a href="{{ route('posts.show', $post) }}" class="btn btn-sm btn-dark">
                <i class="fa-solid fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="card-body p-4">

        @if($revisions->count())

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th width="4%" class="text-center">#</th>
                        <th width="14%">Date & Time</th>
                        <th width="12%">Editor</th>
                        <th width="12%">Field</th>
                        <th width="22%">Old Value</th>
                        <th width="22%">New Value</th>
                        <th width="14%" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($revisions as $revision)
                    @php
                        $user = $revision->userResponsible();
                        $isCreation = ($revision->key == 'created_at' && !$revision->old_value);
                    @endphp
                    <tr>
                        <td class="text-center text-muted fw-bold">
                            {{ $revision->id }}
                        </td>

                        <td>
                            <div class="fw-semibold">{{ $revision->created_at->format('d M Y, H:i') }}</div>
                            <small class="text-muted">{{ $revision->created_at->diffForHumans() }}</small>
                        </td>

                        <td>
                            @if($user)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                <i class="fa-solid fa-user me-1"></i> {{ $user->name }}
                            </span>
                            @else
                            <span class="badge bg-secondary-subtle text-secondary border">
                                System
                            </span>
                            @endif
                        </td>

                        <td>
                            @if($isCreation)
                            <span class="badge bg-success">CREATED</span>
                            @elseif($revision->key == 'deleted_at')
                            <span class="badge bg-danger">DELETED</span>
                            @else
                            <span class="badge bg-info text-dark font-monospace">{{ $revision->fieldName() }}</span>
                            @endif
                        </td>

                        <td>
                            <div class="text-danger small font-monospace text-break" style="max-height: 80px; overflow-y: auto;">
                                {{ $revision->oldValue() ? Str::limit($revision->oldValue(), 120) : '-' }}
                            </div>
                        </td>

                        <td>
                            <div class="text-success small font-monospace text-break" style="max-height: 80px; overflow-y: auto;">
                                {{ $revision->newValue() ? Str::limit($revision->newValue(), 120) : '-' }}
                            </div>
                        </td>

                        <td class="text-center">
                            @if(!$isCreation)
                            <div class="btn-group btn-group-sm" role="group">
                                <!-- Feature 2: Diff Button -->
                                <button type="button"
                                    class="btn btn-outline-info btn-view-diff"
                                    data-revision-id="{{ $revision->id }}"
                                    title="View Visual Diff">
                                    <i class="fa-solid fa-eye"></i>
                                </button>

                                <!-- Feature 1: Revert Field Button -->
                                <button type="button"
                                    class="btn btn-outline-warning text-dark btn-revert-field"
                                    data-revision-id="{{ $revision->id }}"
                                    data-field="{{ $revision->fieldName() }}"
                                    data-old-val="{{ $revision->oldValue() }}"
                                    title="Revert Only This Field">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </button>

                                <!-- Feature 1: Rollback to Snapshot -->
                                <button type="button"
                                    class="btn btn-outline-primary btn-rollback-snapshot"
                                    data-revision-id="{{ $revision->id }}"
                                    data-date="{{ $revision->created_at->format('d M Y, H:i') }}"
                                    title="Rollback Entire Post to this Snapshot">
                                    <i class="fa-solid fa-backward-step"></i>
                                </button>
                            </div>
                            @else
                            <span class="text-muted small">Initial record</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $revisions->links('pagination::bootstrap-5') }}
        </div>

        @else
        <div class="alert alert-info text-center py-4">
            <i class="fa-solid fa-circle-info fa-2x mb-2 text-muted"></i>
            <p class="mb-0 fw-semibold">No revision history found for this post.</p>
        </div>
        @endif

    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: VISUAL DIFF STUDIO (Feature 2)                          -->
<!-- ============================================================== -->
<div class="modal fade" id="diffStudioModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-code-compare text-info"></i>
                    <h5 class="modal-title fw-bold mb-0">Visual Diff Studio</h5>
                    <span class="badge bg-secondary font-monospace" id="diffFieldBadge">field</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <small class="text-muted" id="diffMetaInfo">Edited by User on Date</small>

                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary active" id="btnDiffInline">
                            <i class="fa-solid fa-align-left me-1"></i> Unified Inline
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btnDiffSplit">
                            <i class="fa-solid fa-columns me-1"></i> Side-by-Side Split
                        </button>
                    </div>
                </div>

                <!-- Inline Diff Container -->
                <div id="diffInlineContainer" class="diff-box">
                    Loading diff...
                </div>

                <!-- Side-by-Side Split Container -->
                <div id="diffSplitContainer" class="row g-2 d-none">
                    <div class="col-6">
                        <div class="fw-bold small text-danger mb-1"><i class="fa-solid fa-minus me-1"></i> Previous Value</div>
                        <div class="diff-box bg-light" id="diffSplitOld"></div>
                    </div>
                    <div class="col-6">
                        <div class="fw-bold small text-success mb-1"><i class="fa-solid fa-plus me-1"></i> New Value</div>
                        <div class="diff-box bg-light" id="diffSplitNew"></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: REVERT FIELD CONFIRMATION (Feature 1)                   -->
<!-- ============================================================== -->
<div class="modal fade" id="revertFieldModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form id="revertFieldForm" method="POST">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-rotate-left me-2"></i> Revert Field Confirmation
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <p class="mb-3">Are you sure you want to revert this field back to its previous historical value?</p>

                    <div class="card bg-light border p-3 mb-3">
                        <div class="row g-2 small">
                            <div class="col-4 fw-bold text-muted">Field Name:</div>
                            <div class="col-8 fw-semibold" id="revertModalField">field</div>

                            <div class="col-4 fw-bold text-muted">Current Live Value:</div>
                            <div class="col-8 text-danger text-break" id="revertModalCurrent">current</div>

                            <div class="col-4 fw-bold text-muted">Target Restore Value:</div>
                            <div class="col-8 text-success fw-bold text-break" id="revertModalTarget">target</div>
                        </div>
                    </div>

                    <div class="alert alert-info py-2 small mb-0">
                        <i class="fa-solid fa-circle-info me-1"></i> This rollback will be recorded as a new audit event for full tracking continuity.
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold text-dark">
                        <i class="fa-solid fa-check me-1"></i> Confirm & Revert Field
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: ROLLBACK SNAPSHOT CONFIRMATION (Feature 1)              -->
<!-- ============================================================== -->
<div class="modal fade" id="rollbackSnapshotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form id="rollbackSnapshotForm" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-backward-step me-2"></i> Rollback Post to Historical Snapshot
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <p>You are about to restore the <strong>entire post</strong> to the exact state it was at this historical revision.</p>

                    <div class="alert alert-warning py-2 small">
                        <strong>Target Snapshot Timestamp:</strong> <span id="snapshotModalDate">-</span>
                    </div>

                    <p class="text-muted small mb-0">
                        All fields (Title, Content, and Publication Status) will be restored to their values at this point in time. Any changes made after this point will be superseded, and a new revision audit record will be logged.
                    </p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i class="fa-solid fa-check me-1"></i> Confirm Full Rollback
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const postId = {{ $post->id }};

    // -------------------------------------------------------------
    // FEATURE 2: Diff Studio Handler
    // -------------------------------------------------------------
    const diffModal = new bootstrap.Modal(document.getElementById('diffStudioModal'));
    const diffInlineContainer = document.getElementById('diffInlineContainer');
    const diffSplitContainer = document.getElementById('diffSplitContainer');
    const diffSplitOld = document.getElementById('diffSplitOld');
    const diffSplitNew = document.getElementById('diffSplitNew');
    const diffFieldBadge = document.getElementById('diffFieldBadge');
    const diffMetaInfo = document.getElementById('diffMetaInfo');

    document.querySelectorAll('.btn-view-diff').forEach(btn => {
        btn.addEventListener('click', function () {
            const revId = this.dataset.revisionId;
            diffInlineContainer.innerHTML = '<div class="text-muted text-center py-3"><i class="fa-solid fa-spinner fa-spin me-2"></i> Loading visual diff...</div>';
            diffModal.show();

            fetch(`/posts/${postId}/revisions/${revId}/diff`)
                .then(res => res.json())
                .then(data => {
                    diffFieldBadge.innerText = data.field;
                    diffMetaInfo.innerText = `Edited by ${data.author} on ${data.date}`;
                    diffInlineContainer.innerHTML = data.inline_html || '<em>No differences found</em>';
                    diffSplitOld.innerHTML = data.side_by_side.old || '<em>empty</em>';
                    diffSplitNew.innerHTML = data.side_by_side.new || '<em>empty</em>';
                })
                .catch(err => {
                    diffInlineContainer.innerHTML = `<span class="text-danger">Failed to load diff: ${err}</span>`;
                });
        });
    });

    document.getElementById('btnDiffInline').addEventListener('click', function () {
        this.classList.add('active', 'btn-outline-primary');
        this.classList.remove('btn-outline-secondary');
        document.getElementById('btnDiffSplit').classList.remove('active', 'btn-outline-primary');
        document.getElementById('btnDiffSplit').classList.add('btn-outline-secondary');
        diffInlineContainer.classList.remove('d-none');
        diffSplitContainer.classList.add('d-none');
    });

    document.getElementById('btnDiffSplit').addEventListener('click', function () {
        this.classList.add('active', 'btn-outline-primary');
        this.classList.remove('btn-outline-secondary');
        document.getElementById('btnDiffInline').classList.remove('active', 'btn-outline-primary');
        document.getElementById('btnDiffInline').classList.add('btn-outline-secondary');
        diffInlineContainer.classList.add('d-none');
        diffSplitContainer.classList.remove('d-none');
    });

    // -------------------------------------------------------------
    // FEATURE 1: Revert Field Handler
    // -------------------------------------------------------------
    const revertFieldModal = new bootstrap.Modal(document.getElementById('revertFieldModal'));
    const revertForm = document.getElementById('revertFieldForm');

    document.querySelectorAll('.btn-revert-field').forEach(btn => {
        btn.addEventListener('click', function () {
            const revId = this.dataset.revisionId;
            revertForm.action = `/posts/${postId}/revisions/${revId}/revert-field`;

            fetch(`/posts/${postId}/revisions/${revId}/preview-revert`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('revertModalField').innerText = data.field;
                    document.getElementById('revertModalCurrent').innerText = data.current_value || '(empty)';
                    document.getElementById('revertModalTarget').innerText = data.target_value || '(empty)';
                    revertFieldModal.show();
                })
                .catch(() => {
                    document.getElementById('revertModalField').innerText = btn.dataset.field;
                    document.getElementById('revertModalCurrent').innerText = '...';
                    document.getElementById('revertModalTarget').innerText = btn.dataset.oldVal || '(empty)';
                    revertFieldModal.show();
                });
        });
    });

    // -------------------------------------------------------------
    // FEATURE 1: Snapshot Rollback Handler
    // -------------------------------------------------------------
    const rollbackSnapshotModal = new bootstrap.Modal(document.getElementById('rollbackSnapshotModal'));
    const rollbackForm = document.getElementById('rollbackSnapshotForm');

    document.querySelectorAll('.btn-rollback-snapshot').forEach(btn => {
        btn.addEventListener('click', function () {
            const revId = this.dataset.revisionId;
            const date = this.dataset.date;
            document.getElementById('snapshotModalDate').innerText = `${date} (Revision #${revId})`;
            rollbackForm.action = `/posts/${postId}/revisions/${revId}/rollback-snapshot`;
            rollbackSnapshotModal.show();
        });
    });
});
</script>
@endsection
