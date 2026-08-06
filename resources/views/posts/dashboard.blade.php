@extends('layouts.app')

@section('content')

<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>📊 Revision Analytics Dashboard</h2>

        <a href="{{ route('posts.index') }}"
            class="btn btn-primary">

            Back to Posts

        </a>

    </div>

    <div class="row">

        <div class="col-md-4 mb-3">

            <div class="card border-primary shadow">

                <div class="card-body text-center">

                    <h6>Total Posts</h6>

                    <h2>{{ $totalPosts }}</h2>

                </div>

            </div>

        </div>

        <div class="col-md-4 mb-3">

            <div class="card border-success shadow">

                <div class="card-body text-center">

                    <h6>Total Revisions</h6>

                    <h2>{{ $totalRevisions }}</h2>

                </div>

            </div>

        </div>

        <div class="col-md-4 mb-3">

            <div class="card border-warning shadow">

                <div class="card-body text-center">

                    <h6>Today's Revisions</h6>

                    <h2>{{ $todayRevisions }}</h2>

                </div>

            </div>

        </div>

    </div>

    <div class="row mt-4">

        <div class="col-md-6">

            <div class="card shadow">

                <div class="card-header bg-dark text-white">

                    Most Edited Post

                </div>

                <div class="card-body">

                    @if($mostEditedPost)

                    <h5>{{ $mostEditedPost->title }}</h5>

                    <p>

                        Author :

                        {{ $mostEditedPost->user->name }}

                    </p>

                    <span class="badge bg-success">

                        Published :
                        {{ $mostEditedPost->is_published ? 'Yes' : 'No' }}

                    </span>

                    @else

                    <p>No Data</p>

                    @endif

                </div>

            </div>

        </div>

        <div class="col-md-6">

            <div class="card shadow">

                <div class="card-header bg-dark text-white">

                    Top Editor

                </div>

                <div class="card-body">

                    @if($topEditor)

                    <h5>{{ $topEditor->name }}</h5>

                    <p>{{ $topEditor->email }}</p>

                    @else

                    <p>No Data</p>

                    @endif

                </div>

            </div>

        </div>

    </div>

    <div class="row mt-4">

    <div class="col-md-4 mb-3">

        <div class="card border-info shadow">

            <div class="card-body text-center">

                <h6>Average Revisions / Post</h6>

                <h2>{{ $averageRevisions }}</h2>

            </div>

        </div>

    </div>

    <div class="col-md-4 mb-3">

        <div class="card border-danger shadow">

            <div class="card-body text-center">

                <h6>Most Edited Field</h6>

                <h4>

                    {{ $mostEditedField->key ?? 'No Data' }}

                </h4>

            </div>

        </div>

    </div>

    <div class="col-md-4 mb-3">

        <div class="card border-success shadow">

            <div class="card-body text-center">

                <h6>Most Active Day</h6>

                <h5>

                    {{ $mostActiveDay->day ?? '-' }}

                </h5>

            </div>

        </div>

    </div>

</div>

    <div class="card mt-4 shadow">

        <div class="card-header bg-primary text-white">

            Revisions Per Day

        </div>

        <div class="card-body">

            <canvas id="revisionChart" height="100"></canvas>

        </div>

    </div>

    <div class="card mt-4 shadow">

    <div class="card-header bg-dark text-white">

        Top 5 Edited Posts

    </div>

    <div class="card-body">

        <table class="table table-hover">

            <thead>

                <tr>

                    <th>#</th>

                    <th>Post</th>

                    <th>Total Revisions</th>

                </tr>

            </thead>

            <tbody>

                @forelse($topEditedPosts as $item)

                    @php

                        $post = \App\Models\Post::find($item->revisionable_id);

                    @endphp

                    <tr>

                        <td>{{ $loop->iteration }}</td>

                        <td>{{ $post?->title }}</td>

                        <td>

                            <span class="badge bg-primary">

                                {{ $item->total }}

                            </span>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="3">

                            No Data

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

    <div class="card mt-4 shadow">

        <div class="card-header bg-secondary text-white">

            Recent Revision Activity

        </div>

        <div class="card-body">

            @if($recentActivities->count())

            <table class="table table-striped">

                <thead>

                    <tr>

                        <th>Date</th>

                        <th>User</th>

                        <th>Field</th>

                        <th>Old Value</th>

                        <th>New Value</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach($recentActivities as $activity)

                    <tr>

                        <td>

                            {{ $activity->created_at->format('d M Y H:i') }}

                        </td>

                        <td>

                            @php

                            $user = $activity->userResponsible();

                            @endphp

                            {{ $user ? $user->name : 'System' }}

                        </td>

                        <td>

                            {{ $activity->fieldName() }}

                        </td>

                        <td>

                            {{ $activity->oldValue() }}

                        </td>

                        <td>

                            {{ $activity->newValue() }}

                        </td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

            @else

            <p>No revision activity found.</p>

            @endif

        </div>

    </div>

</div>

@endsection

@section('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const labels = @json($chartData->pluck('day'));

const values = @json($chartData->pluck('total'));

const ctx = document.getElementById('revisionChart');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Revisions Per Day',
            data: values,
            borderWidth: 3,
            tension: 0.3,
            fill: false
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});

</script>

@endsection