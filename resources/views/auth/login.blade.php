@extends('layouts.base')

@section('title', 'Sign in')
@section('body-class', 'page-auth')

@section('content')
<main class="auth">
    {{-- Left: animated showcase --}}
    <aside class="showcase" aria-hidden="true" data-parallax>
        <div class="showcase__aurora">
            <span class="blob blob--1"></span>
            <span class="blob blob--2"></span>
            <span class="blob blob--3"></span>
        </div>
        <div class="showcase__grid"></div>

        <div class="showcase__inner">
            @include('partials.brand')

            <div class="showcase__copy">
                <span class="pill"><i></i> Candidate records, simplified</span>
                <h1>Every candidate.<br><span class="text-gradient">Found in seconds.</span></h1>
                <p>Enter, search and export manpower records from one fast, secure workspace built for your team.</p>
            </div>

            <div class="stage">
                <div class="glass card-stats float-a" data-depth="18">
                    <div class="card-stats__head">
                        <span>Applications this week</span>
                        <span class="trend">▲ 18.4%</span>
                    </div>
                    <div class="card-stats__value"><span data-count="1284">1,284</span></div>
                    <div class="bars">
                        @foreach ([42, 58, 46, 72, 64, 88, 100] as $i => $h)
                            <span style="--h: {{ $h }}%; --d: {{ $i * 90 }}ms"></span>
                        @endforeach
                    </div>
                    <div class="bars__labels">
                        @foreach (['M', 'T', 'W', 'T', 'F', 'S', 'S'] as $d)<span>{{ $d }}</span>@endforeach
                    </div>
                </div>

                <div class="glass card-ring float-b" data-depth="30">
                    <svg viewBox="0 0 64 64" class="ring">
                        <circle cx="32" cy="32" r="26" class="ring__track"/>
                        <circle cx="32" cy="32" r="26" class="ring__value"/>
                    </svg>
                    <div>
                        <small>Profiles complete</small>
                        <strong><span data-count="86">86</span>%</strong>
                    </div>
                </div>

                <div class="glass card-toast float-c" data-depth="24">
                    <span class="avatar">RS</span>
                    <div>
                        <strong>New candidate registered</strong>
                        <small>Jaipur district · just now</small>
                    </div>
                    <span class="dot-live"></span>
                </div>
            </div>

            <div class="showcase__foot">
                <span>© {{ date('Y') }} {{ config('app.company') }}</span>
                <span class="status"><i></i> All systems operational</span>
            </div>
        </div>
    </aside>

    {{-- Right: sign-in form --}}
    <section class="panel">
        <div class="panel__inner">
            <div class="brand-mobile reveal" style="--i:0">@include('partials.brand')</div>

            <header class="panel__head">
                <span class="wave reveal" style="--i:1">👋</span>
                <h2 class="reveal" style="--i:2">Welcome back</h2>
                <p class="reveal" style="--i:3">Sign in to continue to your workspace.</p>
            </header>

            @if ($errors->any())
                <div class="alert reveal shake" style="--i:3" role="alert">
                    <svg viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="1.6"/><path d="M10 6v4.5M10 13.5h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="form" data-login-form novalidate>
                @csrf

                <div class="field reveal @error('email') field--error @enderror" style="--i:4">
                    <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder=" " autocomplete="username" required autofocus>
                    <label for="email">Email address</label>
                    <svg class="field__icon" viewBox="0 0 20 20" fill="none"><rect x="2.5" y="4" width="15" height="12" rx="2.5" stroke="currentColor" stroke-width="1.5"/><path d="m3.5 5.5 6.5 5 6.5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>

                <div class="field reveal" style="--i:5">
                    <input id="password" name="password" type="password" placeholder=" " autocomplete="current-password" required>
                    <label for="password">Password</label>
                    <svg class="field__icon" viewBox="0 0 20 20" fill="none"><rect x="3.5" y="8.5" width="13" height="9" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M6.5 8.5V6a3.5 3.5 0 0 1 7 0v2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <button type="button" class="field__toggle" data-toggle-password aria-label="Show password">
                        <svg class="i-show" viewBox="0 0 20 20" fill="none"><path d="M1.7 10S4.7 4.5 10 4.5 18.3 10 18.3 10 15.3 15.5 10 15.5 1.7 10 1.7 10Z" stroke="currentColor" stroke-width="1.5"/><circle cx="10" cy="10" r="2.5" stroke="currentColor" stroke-width="1.5"/></svg>
                        <svg class="i-hide" viewBox="0 0 20 20" fill="none"><path d="M3 3l14 14M8.2 5a8.8 8.8 0 0 1 1.8-.2c5.3 0 8.3 5.2 8.3 5.2a14 14 0 0 1-2.4 3M5.2 6.6C3 8.1 1.7 10 1.7 10s3 5.5 8.3 5.5c1.3 0 2.5-.3 3.5-.8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </button>
                </div>

                <div class="form__row reveal" style="--i:6">
                    <label class="switch">
                        <input type="checkbox" name="remember" @checked(old('remember'))>
                        <span class="switch__track"><span class="switch__thumb"></span></span>
                        <span>Remember me</span>
                    </label>
                    <a href="#" class="link" onclick="return false">Forgot password?</a>
                </div>

                <button type="submit" class="btn reveal" style="--i:7">
                    <span class="btn__label">Sign in</span>
                    <svg class="btn__arrow" viewBox="0 0 20 20" fill="none"><path d="M4 10h12m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="btn__spinner"></span>
                </button>
            </form>

            @if (app()->environment('local'))
                <button type="button" class="demo reveal" style="--i:8" data-demo-fill data-email="admin@crowdmanpower.com" data-password="password">
                    <span class="demo__icon">
                        <svg viewBox="0 0 20 20" fill="none"><path d="M11 2 4 11h5l-1 7 7-9h-5l1-7Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
                    </span>
                    <span class="demo__text">
                        <strong>Use demo account</strong>
                        <small>admin@crowdmanpower.com · password</small>
                    </span>
                    <span class="demo__go">Fill →</span>
                </button>
            @endif

            <p class="panel__foot reveal" style="--i:9">Protected by rate limiting &amp; encrypted sessions.</p>
        </div>
    </section>
</main>
@endsection
