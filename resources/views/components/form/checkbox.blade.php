@props([
    'name',
    'label',
    'value' => '1',
    'checked' => false,
    'description' => null,
    'required' => false,
    'disabled' => false,
    'id' => null,
])

@php
    $checkboxId = $id ?? $name;
    $isChecked = old($name, $checked);
    $hasError = $errors->has($name);
@endphp

<div class="flex items-start gap-2.5">
    <div class="flex items-center h-5 mt-0.5">
        <input
            type="checkbox"
            name="{{ $name }}"
            id="{{ $checkboxId }}"
            value="{{ $value }}"
            {{ $isChecked ? 'checked' : '' }}
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            @if ($hasError)
                aria-invalid="true"
                aria-describedby="{{ $name }}-error"
            @endif
            {{ $attributes->merge(['class' => 'w-4 h-4 rounded-sm border-slate-300 text-rsi-700 focus:ring-rsi-500 focus:ring-offset-0 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer']) }}
        >
    </div>

    <div class="text-xs leading-relaxed">
        <label for="{{ $checkboxId }}" class="font-medium text-slate-800 cursor-pointer select-none">
            {{ $label }}
            @if ($required)
                <span class="text-red-500 font-bold">*</span>
            @endif
        </label>

        @if ($description)
            <p class="text-slate-500 mt-0.5">{{ $description }}</p>
        @endif

        <x-form.error :name="$name" />
    </div>
</div>
