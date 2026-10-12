<div
    x-data="{
        open: false,
        isAlert: false,
        title: 'Konfirmasi Tindakan',
        message: '',
        confirmText: 'Ya, Lanjutkan',
        cancelText: 'Batal',
        variant: 'danger', // 'danger' | 'primary' | 'warning' | 'info'
        targetForm: null,
        callback: null,
        isLoading: false,

        openConfirm(detail) {
            this.isAlert = false;
            this.title = detail.title || 'Konfirmasi Tindakan';
            this.message = detail.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
            this.confirmText = detail.confirmText || 'Ya, Lanjutkan';
            this.cancelText = detail.cancelText || 'Batal';
            this.variant = detail.variant || 'danger';
            this.targetForm = detail.targetForm || null;
            this.callback = detail.callback || null;
            this.isLoading = false;
            this.open = true;
        },

        openAlert(detail) {
            this.isAlert = true;
            if (typeof detail === 'string') {
                this.message = detail;
                this.title = 'Pemberitahuan';
                this.variant = 'warning';
            } else {
                this.title = detail.title || 'Pemberitahuan';
                this.message = detail.message || '';
                this.variant = detail.variant || 'warning';
            }
            this.confirmText = detail.confirmText || 'Saya Mengerti';
            this.isLoading = false;
            this.open = true;
        },

        close() {
            if (this.isLoading) return;
            this.open = false;
        },

        handleConfirm() {
            if (this.isAlert) {
                this.close();
                return;
            }

            this.isLoading = true;
            if (this.targetForm) {
                this.targetForm.submit();
            } else if (typeof this.callback === 'function') {
                try {
                    this.callback();
                } finally {
                    this.open = false;
                    this.isLoading = false;
                }
            } else {
                this.open = false;
                this.isLoading = false;
            }
        }
    }"
    @rsi-confirm.window="openConfirm($event.detail)"
    @rsi-alert.window="openAlert($event.detail)"
    @keydown.escape.window="close()"
    x-cloak
>
    {{-- Modal Backdrop & Container --}}
    <div
        x-show="open"
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog"
        aria-modal="true"
    >
        {{-- Backdrop --}}
        <div
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="close()"
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity"
            aria-hidden="true"
        ></div>

        {{-- Dialog Alignment Shell --}}
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0 relative z-10 pointer-events-none">
            <div
                x-show="open"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                @click.stop
                class="relative w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl border border-slate-200 pointer-events-auto space-y-4"
            >
                {{-- Header with Icon & Title --}}
                <div class="flex items-start justify-between gap-3 pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        {{-- Danger Icon --}}
                        <template x-if="variant === 'danger'">
                            <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </div>
                        </template>

                        {{-- Warning Icon --}}
                        <template x-if="variant === 'warning'">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                        </template>

                        {{-- Primary / RSI Green Icon --}}
                        <template x-if="variant === 'primary'">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                        </template>

                        {{-- Info Icon --}}
                        <template x-if="variant === 'info'">
                            <div class="w-10 h-10 rounded-xl bg-sky-50 border border-sky-100 text-sky-600 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </template>

                        <div>
                            <h3 class="text-sm sm:text-base font-bold text-slate-900 leading-snug" x-text="title"></h3>
                            <p class="text-[11px] text-slate-500 font-medium">RSI Fatimah Banyuwangi</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="close()"
                        :disabled="isLoading"
                        class="text-slate-400 hover:text-slate-600 focus:outline-hidden cursor-pointer p-1 rounded-lg hover:bg-slate-50 transition-colors"
                        title="Tutup"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Message Body --}}
                <div class="text-xs text-slate-700 leading-relaxed font-medium">
                    <p x-text="message" class="whitespace-pre-line"></p>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    {{-- Cancel Button (only for Confirm mode) --}}
                    <button
                        type="button"
                        x-show="!isAlert"
                        @click="close()"
                        :disabled="isLoading"
                        class="px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer disabled:opacity-50"
                        x-text="cancelText"
                    ></button>

                    {{-- Primary / Action Button --}}
                    <button
                        type="button"
                        @click="handleConfirm()"
                        :disabled="isLoading"
                        :class="{
                            'bg-rose-600 hover:bg-rose-700 text-white': variant === 'danger',
                            'bg-emerald-700 hover:bg-emerald-800 text-white': variant === 'primary',
                            'bg-amber-600 hover:bg-amber-700 text-white': variant === 'warning',
                            'bg-sky-600 hover:bg-sky-700 text-white': variant === 'info'
                        }"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-lg shadow-xs transition-colors cursor-pointer disabled:opacity-70"
                    >
                        <svg x-show="isLoading" x-cloak class="animate-spin w-3.5 h-3.5 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span x-text="confirmText"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
