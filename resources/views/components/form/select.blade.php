@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => '-- Pilih Opsi --',
    'required' => false,
    'disabled' => false,
    'helper' => null,
    'id' => null,
])

@php
    $selectId = $id ?? $name;
    $hasError = $errors->has($name);
    $currentValue = old($name, $selected);

    $baseClasses = 'w-full rounded-lg text-sm transition-all duration-150 py-2 px-3.5 border bg-white focus:outline-hidden focus:ring-2 disabled:bg-slate-50 disabled:text-slate-400 disabled:cursor-not-allowed';

    $borderClasses = $hasError
        ? 'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-200'
        : 'border-slate-300 text-slate-900 focus:border-rsi-600 focus:ring-rsi-100';
@endphp

<div class="w-full">
    @if ($label)
        <label for="{{ $selectId }}" class="block text-xs font-semibold text-slate-700 mb-1.5">
            {{ $label }}
            @if ($required)
                <span class="text-red-500 font-bold" title="Wajib diisi">*</span>
            @endif
        </label>
    @endif

    <select
        name="{{ $name }}"
        id="{{ $selectId }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        @if ($hasError)
            aria-invalid="true"
            aria-describedby="{{ $name }}-error"
        @endif
        {{ $attributes->merge(['class' => "{$baseClasses} {$borderClasses}"]) }}
    >
        @if ($placeholder)
            <option value="" {{ $currentValue === null || $currentValue === '' ? 'selected' : '' }}>
                {{ $placeholder }}
            </option>
        @endif

        @if (!empty($options))
            @foreach ($options as $val => $text)
                <option value="{{ $val }}" {{ (string)$currentValue === (string)$val ? 'selected' : '' }}>
                    {{ $text }}
                </option>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </select>

    @if ($helper && !$hasError)
        <p class="text-xs text-slate-500 mt-1">{{ $helper }}</p>
    @endif

    <x-form.error :name="$name" />
</div>
