<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel Revisionable & Audit Studio</title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            --dark-card: #0f172a;
        }

        body {
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #334155;
            min-height: 100vh;
        }

        .navbar-custom {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            background: #ffffff;
        }

        .card-header-gradient {
            background: var(--primary-gradient);
            color: #ffffff;
            padding: 1rem 1.25rem;
            border-bottom: none;
        }

        /* Diff Highlighting */
        .diff-del {
            background-color: #fee2e2 !important;
            color: #b91c1c !important;
            text-decoration: line-through;
            padding: 2px 4px;
            border-radius: 4px;
        }

        .diff-add {
            background-color: #dcfce7 !important;
            color: #15803d !important;
            text-decoration: none;
            font-weight: 600;
            padding: 2px 4px;
            border-radius: 4px;
        }

        .diff-box {
            font-family: "Fira Code", Consolas, "Courier New", monospace;
            font-size: 0.92rem;
            line-height: 1.6;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1rem;
            min-height: 80px;
        }

        /* Time-Machine Scrubber */
        .timeline-slider-card {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
            color: #ffffff;
            border-radius: 16px;
        }

        .time-slider {
            -webkit-appearance: none;
            width: 100%;
            height: 8px;
            border-radius: 5px;
            background: #4338ca;
            outline: none;
        }

        .time-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #38bdf8;
            cursor: pointer;
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.8);
            border: 2px solid #ffffff;
        }

        .time-slider::-moz-range-thumb {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #38bdf8;
            cursor: pointer;
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.8);
            border: 2px solid #ffffff;
        }

        .badge-step {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: 700;
            font-size: 0.8rem;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-4">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="/">
                <i class="fa-solid fa-clock-rotate-left text-primary fa-lg"></i>
                <span>Revisionable Studio</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('posts.index') ? 'active text-primary fw-bold' : '' }}" href="{{ route('posts.index') }}">
                            <i class="fa-solid fa-file-lines me-1"></i> Posts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('posts.create') ? 'active text-primary fw-bold' : '' }}" href="{{ route('posts.create') }}">
                            <i class="fa-solid fa-square-plus me-1"></i> Create Post
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('revision.dashboard') ? 'active text-primary fw-bold' : '' }}" href="{{ route('revision.dashboard') }}">
                            <i class="fa-solid fa-chart-line me-1"></i> Analytics Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('revision.auditReport') ? 'active text-primary fw-bold' : '' }}" href="{{ route('revision.auditReport') }}">
                            <i class="fa-solid fa-clipboard-check me-1"></i> Compliance Audit Report
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>

</html>
