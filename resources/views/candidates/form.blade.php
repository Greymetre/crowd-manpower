@extends('layouts.app')

@section('title', $candidate->exists ? 'Record #'.$candidate->id : 'New Entry')

@php
    $value = fn (string $key) => old($key, $candidate->{$key} instanceof \Illuminate\Support\Carbon
        ? $candidate->{$key}->format('Y-m-d')
        : $candidate->{$key});
    $states = ['Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh', 'Delhi', 'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jammu and Kashmir', 'Jharkhand', 'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab', 'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh', 'Uttarakhand', 'West Bengal'];
    $educations = ['Illiterate', '5th', '8th', '10th', '12th', 'ITI', 'Diploma', 'Graduate', 'Post Graduate'];
@endphp

@section('page')
<form method="POST"
      action="{{ $candidate->exists ? route('candidates.update', $candidate) : route('candidates.store') }}"
      class="sheet reveal" data-entry-form novalidate>
    @csrf
    @if ($candidate->exists) @method('PUT') @endif

    <div class="sheet__head">
        <div>
            <h1>{{ $candidate->exists ? 'Edit Record' : 'New Entry' }}</h1>
            <p>{{ $candidate->exists ? 'Last updated '.$candidate->updated_at->diffForHumans().'.' : 'Fill in the candidate details and save.' }}</p>
        </div>
        <div class="srno">
            <small>Sr No</small>
            <strong>{{ $srNo }}</strong>
            @unless ($candidate->exists)<span class="srno__tag">New</span>@endunless
        </div>
    </div>

    @if ($errors->any())
        <div class="alert shake" role="alert">
            <svg viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="1.6"/><path d="M10 6v4.5M10 13.5h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            <span>Please fix the highlighted {{ Str::plural('field', $errors->count()) }}.</span>
        </div>
    @endif

    <div class="grid">
        <x-input name="name" label="Name" :value="$value('name')" required autofocus />
        <x-input name="id_no" label="Id" :value="$value('id_no')" />
        <x-input name="working" label="Working" :value="$value('working')" list="working-options" />
        <x-input name="address" label="Address" :value="$value('address')" />

        <x-input name="village" label="Village" :value="$value('village')" />
        <x-input name="tehsil" label="Tehsil" :value="$value('tehsil')" />
        <x-input name="district" label="District" :value="$value('district')" />
        <x-input name="state" label="State" :value="$value('state')" list="state-options" />

        <x-input name="pincode" label="Pincode" :value="$value('pincode')" inputmode="numeric" maxlength="6" pattern="\d{6}" />
        <x-input name="dob" label="DOB" type="date" :value="$value('dob')" max="{{ now()->subDay()->format('Y-m-d') }}" data-dob />
        <x-input name="age" label="Age" type="number" :value="$value('age')" min="0" max="120" data-age />
        <x-select name="marital_status" label="Marital Status" :options="\App\Models\Candidate::MARITAL_STATUSES" :value="$value('marital_status')" />

        <x-select name="gender" label="Gender" :options="\App\Models\Candidate::GENDERS" :value="$value('gender')" />
        <x-input name="education" label="Education" :value="$value('education')" list="education-options" />
        <x-input name="applied_date" label="Applied Date" type="date" :value="$value('applied_date')" />
    </div>

    <datalist id="working-options"><option value="Yes"><option value="No"></datalist>
    <datalist id="state-options">@foreach ($states as $s)<option value="{{ $s }}">@endforeach</datalist>
    <datalist id="education-options">@foreach ($educations as $e)<option value="{{ $e }}">@endforeach</datalist>

    <div class="actions">
        <div class="actions__group">
            <button type="submit" name="action" value="save" class="abtn abtn--primary" title="Save (Ctrl+S)">
                <svg viewBox="0 0 20 20" fill="none"><path d="M4 4h9l3 3v9H4V4Zm3 0v4h6V4M7 16v-5h6v5" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                Save Record
            </button>
            <a href="{{ route('candidates.create') }}" class="abtn" data-nav>
                <svg viewBox="0 0 20 20" fill="none"><path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                New Record
            </a>
        </div>

        <div class="actions__nav" role="group" aria-label="Record navigation">
            @foreach ([
                ['first', 'First Record', 'M14 5l-5 5 5 5M6 5v10'],
                ['prev', 'Previous Record', 'M12 5l-5 5 5 5'],
                ['next', 'Next Record', 'M8 5l5 5-5 5'],
                ['last', 'Last Record', 'M6 5l5 5-5 5M14 5v10'],
            ] as [$key, $label, $icon])
                @if ($nav[$key])
                    <a href="{{ route('candidates.edit', $nav[$key]) }}" class="abtn abtn--nav" data-nav title="{{ $label }}">
                        <svg viewBox="0 0 20 20" fill="none"><path d="{{ $icon }}" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span>{{ $label }}</span>
                    </a>
                @else
                    <span class="abtn abtn--nav is-disabled" title="{{ $label }}">
                        <svg viewBox="0 0 20 20" fill="none"><path d="{{ $icon }}" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span>{{ $label }}</span>
                    </span>
                @endif
            @endforeach
        </div>

        <button type="submit" name="action" value="submit" class="abtn abtn--submit">
            Submit
            <svg viewBox="0 0 20 20" fill="none"><path d="M4 10h12m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </div>
</form>
@endsection
