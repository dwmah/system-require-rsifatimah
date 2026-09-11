@props([
    'name',
])

@error($name)
    <p id="{{ $name }}-error" {{ $attributes->merge(['class' => 'text-xs text-red-600 mt-1.5 flex items-center gap-1 font-medium']) }} role="alert">
        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ $message }}</span>
    </p>
@enderror
