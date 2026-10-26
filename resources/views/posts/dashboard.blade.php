@extends('layouts.app')

@section('content')

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="fw-bold mb-1">
            <i class="fa-solid fa-chart-pie text-primary me-2"></i> Revision Analytics & Audit Studio
        </h3>
        <p class="text-muted mb-0">Live forensic analytics, editor leaderboard, and activity trends.</p>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('revision.auditReport') }}" class="btn btn-outline-primary">
            <i class="fa-solid fa-clipboard-check me-1"></i> Compliance Audit Report
        </a>
        <a href="{{ route('posts.index') }}" class="btn btn-dark">
            <i class="fa-solid fa-file-lines me-1"></i> View All Posts
        </a>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="small text-uppercase opacity-75 fw-bold">Total Posts</div>
                    <h2 class="fw-bold mb-0 mt-1">{{ $totalPosts }}</h2>
                </div>
                <i class="fa-solid fa-newspaper fa-2x opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 bg-success text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="small text-uppercase opacity-75 fw-bold">Total Revisions</div>
                    <h2 class="fw-bold mb-0 mt-1">{{ $totalRevisions }}</h2>
                </div>
                <i class="fa-solid fa-clock-rotate-left fa-2x opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 bg-warning text-dark">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="small text-uppercase opacity-75 fw-bold">Today's Edits</div>
                    <h2 class="fw-bold mb-0 mt-1">{{ $todayRevisions }}</h2>
                </div>
                <i class="fa-solid fa-calendar-day fa-2x opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 bg-dark text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="small text-uppercase opacity-75 fw-bold">Avg Revisions / Post</div>
                    <h2 class="fw-bold mb-0 mt-1">{{ $averageRevisions }}</h2>
                </div>
                <i class="fa-solid fa-chart-simple fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row g-4 mb-4">
    <!-- Chart 1: Activity Timeline -->
    <div class="col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3">
                <span><i class="fa-solid fa-chart-line text-primary me-2"></i> Revision Activity Timeline (Last 30 Days)</span>
                <span class="badge bg-light text-muted">Daily Volume</span>
            </div>
            <div class="card-body">
                <canvas id="revisionVolumeChart" height="130"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 2: Field Modification Breakdown -->
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold py-3">
                <i class="fa-solid fa-chart-pie text-success me-2"></i> Field Modification Ratio
            </div>
            <div class="card-body d-flex flex-column justify-content-center">
                <canvas id="fieldDonutChart" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Second Row: Leaderboard & Most Edited -->
<div class="row g-4 mb-4">
    <!-- Chart 3: Top Contributors Leaderboard -->
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold py-3">
                <i class="fa-solid fa-trophy text-warning me-2"></i> Top Contributors Leaderboard
            </div>
            <div class="card-body">
                <canvas id="editorsBarChart" height="160"></canvas>
            </div>
        </div>
    </div>

    <!-- Top 5 Edited Posts -->
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3">
                <span><i class="fa-solid fa-fire text-danger me-2"></i> Top Edited Posts</span>
                <span class="badge bg-danger-subtle text-danger">Highest Churn</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="10%">#</th>
                                <th>Post Title</th>
                                <th width="25%" class="text-center">Revisions</th>
                                <th width="20%" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topEditedPosts as $item)
                            @php $post = \App\Models\Post::find($item->revisionable_id); @endphp
                            <tr>
                                <td class="fw-bold text-muted">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-semibold text-truncate" style="max-width: 240px;">
                                        {{ $post ? $post->title : 'Deleted Post #' . $item->revisionable_id }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1 fs-6">
                                        {{ $item->total }} edits
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($post)
                                    <a href="{{ route('posts.revisions', $post) }}" class="btn btn-sm btn-outline-dark">
                                        Ledger
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-3 text-muted">No edit data available yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Revision Activities Table -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center py-3">
        <span><i class="fa-solid fa-list-check text-info me-2"></i> Recent Revision Ledger Activity</span>
        <a href="{{ route('revision.auditReport') }}" class="btn btn-sm btn-primary">
            View Full Audit Trail <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="card-body p-0">
        @if($recentActivities->count())
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date & Time</th>
                        <th>Editor</th>
                        <th>Post ID</th>
                        <th>Field Changed</th>
                        <th>Old Value</th>
                        <th>New Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentActivities as $activity)
                    @php $user = $activity->userResponsible(); @endphp
                    <tr>
                        <td class="small">{{ $activity->created_at->format('d M Y, H:i') }}</td>
                        <td>
                            @if($user)
                            <span class="badge bg-primary-subtle text-primary"><i class="fa-solid fa-user me-1"></i> {{ $user->name }}</span>
                            @else
                            <span class="badge bg-secondary-subtle text-secondary">System</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('posts.show', $activity->revisionable_id) }}" class="fw-bold text-decoration-none">
                                #{{ $activity->revisionable_id }}
                            </a>
                        </td>
                        <td>
                            <span class="badge bg-info-subtle text-info-emphasis font-monospace">{{ $activity->fieldName() }}</span>
                        </td>
                        <td class="small text-danger text-truncate" style="max-width: 200px;">
                            {{ $activity->oldValue() ?: '-' }}
                        </td>
                        <td class="small text-success text-truncate" style="max-width: 200px;">
                            {{ $activity->newValue() ?: '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-center py-4 text-muted mb-0">No recent revision activities.</p>
        @endif
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // -------------------------------------------------------------
    // Chart 1: Line Chart (Activity Volume Timeline)
    // -------------------------------------------------------------
    const lineLabels = @json($chartData->pluck('day'));
    const lineValues = @json($chartData->pluck('total'));

    const ctxLine = document.getElementById('revisionVolumeChart');
    if (ctxLine) {
        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: lineLabels.length ? lineLabels : ['No Data'],
                datasets: [{
                    label: 'Revisions',
                    data: lineValues.length ? lineValues : [0],
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#4f46e5',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }

    // -------------------------------------------------------------
    // Chart 2: Donut Chart (Field Modification Breakdown)
    // -------------------------------------------------------------
    const donutLabels = @json($fieldDistribution->pluck('key'));
    const donutValues = @json($fieldDistribution->pluck('total'));

    const ctxDonut = document.getElementById('fieldDonutChart');
    if (ctxDonut) {
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: donutLabels.length ? donutLabels : ['None'],
                datasets: [{
                    data: donutValues.length ? donutValues : [1],
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'],
                    borderWidth: 2,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    // -------------------------------------------------------------
    // Chart 3: Horizontal Bar Chart (Top Editors Leaderboard)
    // -------------------------------------------------------------
    const editorLabels = @json($topContributors->map(fn($item) => $item->responsibleUser ? $item->responsibleUser->name : 'User #' . $item->user_id));
    const editorValues = @json($topContributors->pluck('total'));

    const ctxBar = document.getElementById('editorsBarChart');
    if (ctxBar) {
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: editorLabels.length ? editorLabels : ['No Editors'],
                datasets: [{
                    label: 'Total Revisions Made',
                    data: editorValues.length ? editorValues : [0],
                    backgroundColor: 'rgba(59, 130, 246, 0.85)',
                    borderRadius: 6
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }
});
</script>
@endsection
