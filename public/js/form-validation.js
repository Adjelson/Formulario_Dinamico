// ============================================================
// FORM-VALIDATION.JS — Validação do formulário público
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('responseForm');
    if (!form) return;

    // Validação em tempo real ao sair do campo
    form.querySelectorAll('input, textarea').forEach(field => {
        field.addEventListener('blur', () => validateField(field));
        field.addEventListener('input', () => {
            if (field.classList.contains('is-invalid')) validateField(field);
        });
    });

    // Validação no submit
    form.addEventListener('submit', function(e) {
        let valid = true;
        // Campos required directos
        form.querySelectorAll('[required]').forEach(field => {
            if (!validateField(field)) valid = false;
        });
        // Radio groups obrigatórias
        const radioGroups = {};
        form.querySelectorAll('input[type="radio"][required]').forEach(r => {
            radioGroups[r.name] = radioGroups[r.name] || r;
        });
        Object.entries(radioGroups).forEach(([name, first]) => {
            const checked = form.querySelector(`input[name="${name}"]:checked`);
            const block = first.closest('.question-block');
            if (!checked) {
                valid = false;
                showError(block, 'Por favor, selecione uma opção.');
            } else {
                clearError(block);
            }
        });
        if (!valid) {
            e.preventDefault();
            // Scroll para o primeiro erro
            const firstErr = form.querySelector('.is-invalid, .question-block.has-error');
            if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
});

function validateField(field) {
    const block = field.closest('.question-block');
    if (!field.hasAttribute('required')) return true;

    if (field.type === 'file') {
        if (field.files.length === 0) {
            field.classList.add('is-invalid');
            if (block) showError(block, 'Por favor, selecione um ficheiro.');
            return false;
        }
        if (field.files[0].size > 5 * 1024 * 1024) {
            field.classList.add('is-invalid');
            if (block) showError(block, 'O ficheiro excede o tamanho máximo de 5MB.');
            return false;
        }
    } else if (field.type === 'checkbox' || field.type === 'radio') {
        return true; // tratado no submit
    } else if (!field.value.trim()) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        if (block) showError(block, 'Este campo é obrigatório.');
        return false;
    }

    field.classList.remove('is-invalid');
    field.classList.add('is-valid');
    if (block) clearError(block);
    return true;
}

function showError(block, msg) {
    block.classList.add('has-error');
    block.style.borderColor = '#ef4444';
    let err = block.querySelector('.field-error-msg');
    if (!err) {
        err = document.createElement('div');
        err.className = 'invalid-feedback field-error-msg d-flex align-items-center gap-1 mt-2';
        block.appendChild(err);
    }
    err.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> ${msg}`;
    err.style.display = 'flex';
}

function clearError(block) {
    block.classList.remove('has-error');
    block.style.borderColor = '';
    const err = block.querySelector('.field-error-msg');
    if (err) err.style.display = 'none';
}
