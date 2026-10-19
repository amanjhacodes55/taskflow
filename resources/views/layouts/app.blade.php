<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TaskFlow') · TaskFlow</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background: #f5f7fb; }
        .task-row.is-done .task-title { text-decoration: line-through; color: #6c757d; }
        .task-row .btn-delete { opacity: .4; } .task-row:hover .btn-delete { opacity: 1; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand bg-white border-bottom mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="{{ url('/') }}">TaskFlow</a>
        <div class="ms-auto d-flex align-items-center gap-3">
            @auth
                <span class="text-muted small">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary">Log out</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-sm btn-link">Log in</a>
                <a href="{{ route('register') }}" class="btn btn-sm btn-primary">Sign up</a>
            @endauth
        </div>
    </div>
</nav>

<main class="container pb-5">
    @if (session('status'))
        <x-alert>{{ session('status') }}</x-alert>
    @endif
    @yield('content')
</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
