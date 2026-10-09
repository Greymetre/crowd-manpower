@extends('layouts.base')

@section('body-class', 'page-app')

@section('content')
@php($user = auth()->user())
<header class="topbar">
    <a href="{{ route('dashboard') }}" class="topbar__brand">@include('partials.brand')</a>

    <nav class="nav" aria-label="Main">
        @foreach ([
            ['dashboard', 'Dashboard', 'M3 10.5 10 4l7 6.5V16a1 1 0 0 1-1 1h-3.5v-4.5h-5V17H4a1 1 0 0 1-1-1v-5.5Z', 'dashboard'],
            ['candidates.create', 'New Entry', 'M10 4v12M4 10h12', 'candidates.create'],
            ['candidates.index', 'Records', 'M4 5h12M4 10h12M4 15h12', 'candidates.index|candidates.edit'],
            ['candidates.import', 'Import', 'M10 3v10m-4-4 4 4 4-4M4 16h12', 'candidates.import'],
        ] as [$route, $label, $icon, $active])
            <a href="{{ route($route) }}" @class(['nav__link', 'is-active' => request()->routeIs(...explode('|', $active))])>
                <svg viewBox="0 0 20 20" fill="none"><path d="{{ $icon }}" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>{{ $label }}</span>
            </a>
        @endforeach
    </nav>

    <div class="topbar__user">
        <span class="avatar avatar--sm">{{ collect(explode(' ', $user->name))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->join('') }}</span>
        <div class="topbar__meta">
            <strong>{{ $user->name }}</strong>
            <small>{{ $user->email }}</small>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-ghost" title="Sign out">
                <svg viewBox="0 0 20 20" fill="none"><path d="M8 4H5a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h3m5-3 3-3-3-3m3 3H8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Sign out</span>
            </button>
        </form>
    </div>
</header>

<main class="app">
    @yield('page')
</main>

@if (session('status'))
    <div class="toast" role="status">
        <svg viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="1.6"/><path d="m6.5 10.2 2.3 2.3 4.7-4.8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <span>{{ session('status') }}</span>
    </div>
@endif
@endsection
