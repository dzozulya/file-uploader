<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'File Uploader' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .navbar-brand {
            font-weight: 600;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ route('files.create') }}">File Storage</a>

        <div class="navbar-nav ms-auto">
            <a class="nav-link {{ request()->routeIs('files.create') ? 'active' : '' }}"
               href="{{ route('files.create') }}">
                Upload
            </a>
            <a class="nav-link {{ request()->routeIs('files.index') ? 'active' : '' }}"
               href="{{ route('files.index') }}">
                Manage Files
            </a>
        </div>
    </div>
</nav>

<main class="container py-5">
    @yield('content')
</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
@stack('scripts')
</body>
</html>
