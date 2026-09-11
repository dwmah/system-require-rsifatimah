@props([
    'name',
    'label' => null,
    'allowedMimes' => 'PDF',
    'maxSizeKb' => 2048,
    'required' => false,
    'disabled' => false,
    'helper' => null,
    'id' => null,
])

@php
    $inputId = $id ?? $name;
    $hasError = $errors->has($name);
    $formattedSize = $maxSizeKb >= 1024 ? round($maxSizeKb / 1024, 1) . ' MB' : $maxSizeKb . ' KB';
@endphp

<div class="w-full" x-data="{ fileName: null }">
    @if ($label)
        <label for="{{ $inputId }}" class="block text-xs font-semibold text-slate-700 mb-1.5">
            {{ $label }}
            @if ($required)
                <span class="text-red-500 font-bold" title="Wajib diunggah">*</span>
            @endif
        </label>
    @endif

    <div
        @class([
            'border rounded-xl p-3.5 bg-white transition-all duration-150 flex items-center justify-between gap-3',
            'border-red-300 ring-1 ring-red-200' => $hasError,
            'border-slate-200 hover:border-slate-300' => ! $hasError,
            'opacity-60 pointer-events-none bg-slate-50' => $disabled,
        ])
    >
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-9 h-9 rounded-lg bg-rsi-50 text-rsi-700 flex items-center justify-center shrink-0" aria-hidden="true">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-800 truncate" x-text="fileName ? fileName : 'Pilih file dokumen...'"></p>
                <p class="text-[11px] text-slate-400">
                    Format: {{ $allowedMimes }} &bull; Maks. {{ $formattedSize }}
                </p>
            </div>
        </div>

        <label
            for="{{ $inputId }}"
            class="shrink-0 text-xs font-medium px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100 hover:text-slate-900 cursor-pointer transition-colors select-none focus-within:ring-2 focus-within:ring-rsi-500"
        >
            <span>Telusuri</span>
            <input
                type="file"
                name="{{ $name }}"
                id="{{ $inputId }}"
                class="sr-only"
                {{ $required ? 'required' : '' }}
                {{ $disabled ? 'disabled' : '' }}
                @change="fileName = $event.target.files[0]?.name"
                {{ $attributes }}
            >
        </label>
    </div>

    @if ($helper && !$hasError)
        <p class="text-xs text-slate-500 mt-1">{{ $helper }}</p>
    @endif

    <x-form.error :name="$name" />
</div>
