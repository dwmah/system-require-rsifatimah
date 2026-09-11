@props([
    'variant' => 'public', // 'public' or 'applicant'
])

{{-- Mobile Drawer Backdrop & Container (Alpine.js controlled) --}}
<div
    x-show="mobileMenuOpen"
    x-cloak
    class="relative z-50 lg:hidden"
    aria-modal="true"
    role="dialog"
>
    {{-- Dim Backdrop --}}
    <div
        x-show="mobileMenuOpen"
        x-transition:enter="transition-opacity ease-linear duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="mobileMenuOpen = false"
        class="fixed inset-0 bg-slate-900/40 backdrop-blur-2xs"
        aria-hidden="true"
    ></div>

    {{-- Slide-over Panel --}}
    <div class="fixed inset-0 flex">
        <div
            x-show="mobileMenuOpen"
            x-transition:enter="transition ease-in-out duration-250 transform"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in-out duration-200 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="relative mr-16 flex w-full max-w-xs flex-1 bg-white shadow-xl flex-col"
        >
            {{-- Close Button Header --}}
            <div class="flex items-center justify-between px-4 h-16 border-b border-slate-100 shrink-0">
                <x-ui.brand size="sm" />

                <button
                    type="button"
                    @click="mobileMenuOpen = false"
                    class="p-2 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors focus:outline-hidden focus:ring-2 focus:ring-rsi-500"
                    aria-label="Tutup menu"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Sidebar Content --}}
            <div class="flex-1 overflow-y-auto">
                @if ($variant === 'applicant')
                    <x-navigation.applicant-sidebar class="w-full border-r-0 min-h-0" />
                @else
                    <x-navigation.public-sidebar class="w-full border-r-0 min-h-0" />
                @endif
            </div>
        </div>
    </div>
</div>
