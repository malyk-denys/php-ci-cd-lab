@props(['name', 'label', 'type' => 'text', 'value' => '', 'help' => null, 'required' => false])

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">
        {{ $label }}@if($required)<span class="required-mark" aria-hidden="true"> *</span>@endif
    </label>
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        @if($required) required @endif
        {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }}
    >
    @if ($help)
        <div class="form-text">{{ $help }}</div>
    @endif
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
