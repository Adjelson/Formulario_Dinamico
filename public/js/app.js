// ============================================================
// APP.JS — Dynamic Forms
// Utilitários globais (toast, tooltips Bootstrap, confirmações)
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    // Activar tooltips Bootstrap
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el);
    });

    // Activar popovers Bootstrap
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
        new bootstrap.Popover(el);
    });

    // Auto-fechar alertas após 5s
    document.querySelectorAll('.df-alert[data-auto-close]').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity .4s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 400);
        }, 5000);
    });
});

// Toast global reutilizável
function showToast(message, type = 'success') {
    let toast = document.getElementById('globalToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'globalToast';
        toast.className = 'df-toast';
        document.body.appendChild(toast);
    }
    const icons = { success: 'fa-check-circle', danger: 'fa-circle-exclamation', info: 'fa-circle-info', warning: 'fa-triangle-exclamation' };
    const colors = { success: 'var(--green-500)', danger: '#ef4444', info: '#3b82f6', warning: '#f59e0b' };
    toast.style.borderLeftColor = colors[type] || colors.success;
    toast.innerHTML = `<i class="fa-solid ${icons[type] || icons.success}" style="color:${colors[type]||colors.success};"></i> ${message}`;
    toast.classList.add('show');
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => toast.classList.remove('show'), 3000);
}

// Confirmação melhorada (substitui o confirm() nativo)
function confirmAction(msg, callback) {
    if (confirm(msg)) callback();
}

//console.log('Dynamic Forms — App initialized');
