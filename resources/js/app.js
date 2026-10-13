import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Helper to trigger RSI Fatimah Custom Confirmation Modal
window.rsiConfirm = function({ title, message, confirmText, cancelText, variant = 'danger', targetForm, onConfirm } = {}) {
    window.dispatchEvent(new CustomEvent('rsi-confirm', {
        detail: {
            title,
            message,
            confirmText,
            cancelText,
            variant,
            targetForm,
            callback: onConfirm
        }
    }));
};

// Helper to trigger RSI Fatimah Custom Alert Modal
window.rsiAlert = function(message, title = 'Pemberitahuan', variant = 'warning', confirmText = 'Saya Mengerti') {
    window.dispatchEvent(new CustomEvent('rsi-alert', {
        detail: {
            title,
            message: typeof message === 'object' ? message.message : message,
            variant: typeof message === 'object' ? (message.variant || variant) : variant,
            confirmText: typeof message === 'object' ? (message.confirmText || confirmText) : confirmText
        }
    }));
};

// Global click delegation for [data-confirm] buttons and links
document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-confirm]');
    if (!trigger) return;

    // Prevent immediate form submission or link following
    event.preventDefault();
    event.stopPropagation();

    const message = trigger.getAttribute('data-confirm');
    const title = trigger.getAttribute('data-confirm-title') || 'Konfirmasi Tindakan';
    const confirmText = trigger.getAttribute('data-confirm-btn') || 'Ya, Lanjutkan';
    const cancelText = trigger.getAttribute('data-confirm-cancel') || 'Batal';
    const variant = trigger.getAttribute('data-confirm-variant') || 'danger';
    const form = trigger.closest('form');

    window.rsiConfirm({
        title,
        message,
        confirmText,
        cancelText,
        variant,
        targetForm: form,
        onConfirm: () => {
            if (trigger.tagName === 'A' && trigger.href) {
                window.location.href = trigger.href;
            } else if (form) {
                form.submit();
            }
        }
    });
}, true);

Alpine.start();
