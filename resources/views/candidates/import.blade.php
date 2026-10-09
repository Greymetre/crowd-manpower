@extends('layouts.app')

@section('title', 'Import')

@section('page')
<div class="page-head reveal">
    <div>
        <h1>Import Records</h1>
        <p>Upload an Excel (.xlsx) or CSV file to add many candidates at once.</p>
    </div>
    <div class="page-head__actions">
        <a href="{{ route('candidates.import.template') }}" class="abtn abtn--excel">
            <svg viewBox="0 0 20 20" fill="none"><path d="M10 3v10m-4-4 4 4 4-4M4 16h12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Download template
        </a>
    </div>
</div>

<div class="import-grid">
    <form method="POST" action="{{ route('candidates.import.store') }}" enctype="multipart/form-data" class="sheet reveal" style="--i:1" data-import-form>
        @csrf
        <label @class(['drop', 'drop--error' => $errors->has('file')]) data-drop>
            <input type="file" name="file" accept=".xlsx,.csv" required>
            <span class="drop__icon">
                <svg viewBox="0 0 24 24" fill="none"><path d="M12 15V4m-4 4 4-4 4 4M5 15v3a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <strong data-drop-label>Drop your file here or <u>browse</u></strong>
            <small>.xlsx or .csv · up to 10 MB</small>
        </label>
        @error('file')<p class="fld__msg">{{ $message }}</p>@enderror

        @if (session('import_error_total'))
            <div class="alert alert--warn">
                <div>
                    <strong>{{ session('import_error_total') }} {{ Str::plural('row', session('import_error_total')) }} skipped</strong>
                    <ul>
                        @foreach (session('import_errors', []) as $msg)<li>{{ $msg }}</li>@endforeach
                    </ul>
                    @if (session('import_error_total') > count(session('import_errors', [])))
                        <small>…and {{ session('import_error_total') - count(session('import_errors', [])) }} more.</small>
                    @endif
                </div>
            </div>
        @endif

        <button type="submit" class="btn">
            <span class="btn__label">Import records</span>
            <svg class="btn__arrow" viewBox="0 0 20 20" fill="none"><path d="M4 10h12m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span class="btn__spinner"></span>
        </button>
    </form>

    <aside class="sheet help reveal" style="--i:2">
        <h3>How it works</h3>
        <ol>
            <li>The first row must contain column headings. Only <b>Name</b> is required.</li>
            <li>Dates can be <code>dd-mm-yyyy</code>, <code>dd/mm/yyyy</code> or Excel dates.</li>
            <li>Age is calculated from DOB automatically when DOB is given.</li>
            <li>Rows with errors are skipped and listed here, the rest are imported.</li>
        </ol>
        <h3>Recognised columns</h3>
        <div class="chips">
            @foreach ($columns as $label)<span>{{ $label }}</span>@endforeach
        </div>
    </aside>
</div>
@endsection
