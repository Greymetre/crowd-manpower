@extends('layouts.app')

@section('title', 'Dashboard')

@section('page')
@php($user = auth()->user())
<section class="hero reveal" style="--i:0">
    <div class="hero__aurora"><span class="blob blob--1"></span><span class="blob blob--2"></span></div>
    <div class="hero__content">
        <span class="pill"><i></i> {{ now()->format('l, d M Y') }}</span>
        <h1>Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ explode(' ', $user->name)[0] }}.</h1>
        <p>{{ config('app.company') }}: candidate records at a glance.</p>
    </div>
    <div class="hero__stats">
        @foreach ([['Total records', $stats['total']], ['Added today', $stats['today']], ['This month', $stats['month']]] as [$label, $n])
            <div class="stat"><strong data-count="{{ $n }}">{{ number_format($n) }}</strong><small>{{ $label }}</small></div>
        @endforeach
    </div>
</section>

<section class="tiles">
    @foreach ([
        ['candidates.create', 'New Entry', 'Add a candidate using the entry form.', 'M10 4v12M4 10h12'],
        ['candidates.index', 'View Records', 'Search, filter, edit and export to Excel or PDF.', 'M4 5h12M4 10h12M4 15h12'],
        ['candidates.import', 'Import', 'Bring in many candidates from an Excel or CSV file.', 'M10 3v10m-4-4 4 4 4-4M4 16h12'],
    ] as $i => [$route, $title, $text, $path])
        <a href="{{ route($route) }}" class="tile reveal" style="--i:{{ $i + 1 }}">
            <span class="tile__icon">
                <svg viewBox="0 0 20 20" fill="none"><path d="{{ $path }}" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <h3>{{ $title }}</h3>
            <p>{{ $text }}</p>
            <span class="tile__go">→</span>
        </a>
    @endforeach
</section>

<section class="table-card recent reveal" style="--i:4">
    <div class="recent__head">
        <h2>Recent entries</h2>
        <a href="{{ route('candidates.index') }}" class="link">View all →</a>
    </div>
    @if ($recent->isEmpty())
        <p class="empty"><strong>No records yet.</strong><span>Your latest entries will show up here.</span></p>
    @else
        <div class="table-scroll">
            <table class="table">
                <thead><tr><th>Sr No</th><th>Name</th><th>Village</th><th>District</th><th>Gender</th><th>Applied Date</th></tr></thead>
                <tbody>
                    @foreach ($recent as $i => $c)
                        <tr style="--r: {{ $i }}" data-href="{{ route('candidates.edit', $c) }}">
                            <td class="table__muted">{{ $c->id }}</td>
                            <td class="table__name">{{ $c->name }}</td>
                            <td>{{ $c->village }}</td>
                            <td>{{ $c->district }}</td>
                            <td>@if ($c->gender)<span class="tag tag--{{ strtolower($c->gender) }}">{{ $c->gender }}</span>@endif</td>
                            <td>{{ $c->display('applied_date') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
