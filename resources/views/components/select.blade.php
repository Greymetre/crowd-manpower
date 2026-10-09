@props(['name', 'label', 'options' => [], 'value' => null])

<div @class(['fld', 'fld--error' => $errors->has($name)])>
    <label for="{{ $name }}">{{ $label }}</label>
    <select id="{{ $name }}" name="{{ $name }}" {{ $attributes }}>
        <option value="">Select…</option>
        @foreach ($options as $option)
            <option value="{{ $option }}" @selected($value === $option)>{{ $option }}</option>
        @endforeach
    </select>
    @error($name)<small class="fld__msg">{{ $message }}</small>@enderror
</div>
