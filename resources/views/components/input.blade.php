@props(['name', 'label', 'type' => 'text', 'value' => null, 'required' => false])

<div @class(['fld', 'fld--error' => $errors->has($name)])>
    <label for="{{ $name }}">{{ $label }}@if ($required)<em>*</em>@endif</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}" @required($required) autocomplete="off" {{ $attributes }}>
    @error($name)<small class="fld__msg">{{ $message }}</small>@enderror
</div>
