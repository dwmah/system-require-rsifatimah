@props([
    'name',
    'label' => null,
    'value' => null,
    'rows' => 4,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'helper' => null,
    'id' => null,
])

@php
    $textareaId = $id ?? $name;
    $hasError = $errors->has($name);

    $baseClasses = 'w-full rounded-lg text-sm transition-all duration-150 py-2.5 px-3.5 border bg-white placeholder:text-slate-400 focus:outline-hidden focus:ring-2 disabled:bg-slate-50 disabled:text-slate-400 disabled:cursor-not-allowed';

    $borderClasses = $hasError
        ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200'
        : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100';
@endphp

<div class="w-full">
    @if ($label)
        <label for="{{ $textareaId }}" class="block text-xs font-semibold text-slate-700 mb-1.5">
            {{ $label }}
            @if ($required)
                <span class="text-red-500 font-bold" title="Wajib diisi">*</span>
            @endif
        </label>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $textareaId }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        @if ($hasError)
            aria-invalid="true"
            aria-describedby="{{ $name }}-error"
        @endif
        {{ $attributes->merge(['class' => "{$baseClasses} {$borderClasses}"]) }}
    >{{ old($name, $value) }}</textarea>

    @if ($helper && !$hasError)
        <p class="text-xs text-slate-500 mt-1">{{ $helper }}</p>
    @endif

    <x-form.error :name="$name" />
</div>
