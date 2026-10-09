@extends('layouts.app')

@section('title', 'Records')

@php($hasFilters = collect($filters)->filter()->isNotEmpty())

@section('page')
<div class="page-head reveal">
    <div>
        <h1>Candidate Records</h1>
        <p>{{ number_format($candidates->total()) }} {{ Str::plural('record', $candidates->total()) }}{{ $hasFilters ? ' match your filters' : '' }}</p>
    </div>
    <div class="page-head__actions">
        <a href="{{ route('candidates.export.excel', $filters) }}" class="abtn abtn--excel">
            <svg viewBox="0 0 20 20" fill="none"><path d="M5 3h7l4 4v10H5V3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="m8 10 4 4m0-4-4 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            Excel
        </a>
        <a href="{{ route('candidates.export.pdf', $filters) }}" class="abtn abtn--pdf">
            <svg viewBox="0 0 20 20" fill="none"><path d="M5 3h7l4 4v10H5V3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M8 11h4M8 14h4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            PDF
        </a>
        <a href="{{ route('candidates.import') }}" class="abtn">
            <svg viewBox="0 0 20 20" fill="none"><path d="M10 3v10m-4-4 4 4 4-4M4 16h12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Import
        </a>
        <a href="{{ route('candidates.create') }}" class="abtn abtn--primary">
            <svg viewBox="0 0 20 20" fill="none"><path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            New Entry
        </a>
    </div>
</div>

<form method="GET" class="filters reveal" style="--i:1">
    <div class="filters__search">
        <svg viewBox="0 0 20 20" fill="none"><circle cx="9" cy="9" r="5.5" stroke="currentColor" stroke-width="1.6"/><path d="m13 13 3.5 3.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search name, Id, village, district, pincode…">
    </div>
    <select name="gender" aria-label="Gender">
        <option value="">All genders</option>
        @foreach (\App\Models\Candidate::GENDERS as $g)
            <option @selected(($filters['gender'] ?? '') === $g)>{{ $g }}</option>
        @endforeach
    </select>
    <select name="district" aria-label="District">
        <option value="">All districts</option>
        @foreach ($districts as $d)
            <option @selected(($filters['district'] ?? '') === $d)>{{ $d }}</option>
        @endforeach
    </select>
    <label class="filters__date"><span>Applied from</span><input type="date" name="from" value="{{ $filters['from'] ?? '' }}"></label>
    <label class="filters__date"><span>to</span><input type="date" name="to" value="{{ $filters['to'] ?? '' }}"></label>
    <button type="submit" class="abtn abtn--primary">Filter</button>
    @if ($hasFilters)
        <a href="{{ route('candidates.index') }}" class="abtn">Reset</a>
    @endif
</form>

<div class="table-card reveal" style="--i:2">
    <div class="table-scroll">
        <table class="table">
            <thead>
                <tr>
                    @foreach (\App\Models\Candidate::COLUMNS as $label)
                        <th>{{ $label }}</th>
                    @endforeach
                    <th class="table__actions"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($candidates as $i => $c)
                    <tr style="--r: {{ $i }}" data-href="{{ route('candidates.edit', $c) }}">
                        @foreach (array_keys(\App\Models\Candidate::COLUMNS) as $key)
                            @if ($key === 'name')
                                <td class="table__name">{{ $c->name }}</td>
                            @elseif ($key === 'gender' && $c->gender)
                                <td><span class="tag tag--{{ strtolower($c->gender) }}">{{ $c->gender }}</span></td>
                            @else
                                <td @class(['table__muted' => $key === 'id', 'table__wrap' => $key === 'address'])>{{ $c->display($key) }}</td>
                            @endif
                        @endforeach
                        <td class="table__actions"><div class="table__btns">
                            <a href="{{ route('candidates.edit', $c) }}" class="icon-btn" title="Edit">
                                <svg viewBox="0 0 20 20" fill="none"><path d="M4 16h3l8.5-8.5a2.1 2.1 0 0 0-3-3L4 13v3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                            </a>
                            <form method="POST" action="{{ route('candidates.destroy', $c) }}" onsubmit="return confirm('Delete record #{{ $c->id }} ({{ addslashes($c->name) }})?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="icon-btn icon-btn--danger" title="Delete">
                                    <svg viewBox="0 0 20 20" fill="none"><path d="M4 6h12M8 6V4h4v2m-6 0 .7 10h6.6L14 6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                            </form>
                        </div></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count(\App\Models\Candidate::COLUMNS) + 1 }}" class="empty">
                            <strong>{{ $hasFilters ? 'No records match your filters.' : 'No records yet.' }}</strong>
                            <span>{{ $hasFilters ? 'Try a different search or reset the filters.' : 'Add your first entry or import an Excel file.' }}</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $candidates->links('partials.pagination') }}
</div>
@endsection
