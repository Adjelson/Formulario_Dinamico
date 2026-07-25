'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    document.querySelectorAll('form[method="post"], form[method="POST"]').forEach((form) => {
        if (csrf && !form.querySelector('input[name="_csrf"]')) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_csrf';
            input.value = csrf;
            form.prepend(input);
        }
    });

    if (window.bootstrap) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => new bootstrap.Tooltip(element));
    }

    document.querySelectorAll('.df-alert[data-auto-close]').forEach((element) => {
        window.setTimeout(() => element.remove(), 5000);
    });

    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('button[type="submit"]');
            if (!button || button.dataset.loading === 'true') return;
            button.dataset.loading = 'true';
            button.setAttribute('aria-busy', 'true');
            const original = button.innerHTML;
            button.dataset.originalHtml = original;
            button.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> A processar…';
            window.setTimeout(() => {
                if (button.isConnected) {
                    button.dataset.loading = 'false';
                    button.removeAttribute('aria-busy');
                    button.innerHTML = button.dataset.originalHtml || original;
                }
            }, 12000);
        });
    });
});

function showToast(message, type = 'success') {
    let toast = document.getElementById('globalToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'globalToast';
        toast.className = 'df-toast';
        toast.setAttribute('role', 'status');
        toast.setAttribute('aria-live', 'polite');
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.dataset.type = type;
    toast.classList.add('show');
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => toast.classList.remove('show'), 3500);
}
