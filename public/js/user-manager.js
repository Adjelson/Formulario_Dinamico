// ============================================
// USER MANAGER
// window.BASE_URL injectado pelo PHP na view
// ============================================

function openCreateUserModal() {
    const form    = document.getElementById('userForm');
    const pw      = document.getElementById('userPassword');
    const pwGroup = document.getElementById('passwordGroup');

    document.getElementById('modalTitle').textContent = 'Novo Utilizador';
    form.action           = window.BASE_URL + '/admin/users/store';
    pwGroup.style.display = 'block';
    pw.required           = true;
    document.getElementById('formErrors').style.display = 'none';
    form.reset();
    document.getElementById('userActive').checked = true;
    document.getElementById('userModal').style.display = 'block';
}

function openEditUserModal(user) {
    const form    = document.getElementById('userForm');
    const pw      = document.getElementById('userPassword');
    const pwGroup = document.getElementById('passwordGroup');

    document.getElementById('modalTitle').textContent = 'Editar Utilizador';
    form.action           = window.BASE_URL + '/admin/users/' + user.id + '/update';
    pwGroup.style.display = 'none';
    pw.required           = false;   // campo oculto — não pode bloquear o submit
    pw.value              = '';

    document.getElementById('userName').value        = user.name;
    document.getElementById('userEmail').value       = user.email;
    document.getElementById('userRole').value        = user.role;
    document.getElementById('userActive').checked    = user.is_active == 1;
    document.getElementById('formErrors').style.display = 'none';
    document.getElementById('userModal').style.display  = 'block';
}

function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
}

window.addEventListener('click', function(e) {
    const modal = document.getElementById('userModal');
    if (modal && e.target === modal) modal.style.display = 'none';
});

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('userForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        const name    = document.getElementById('userName').value.trim();
        const email   = document.getElementById('userEmail').value.trim();
        const pwGroup = document.getElementById('passwordGroup');
        const pwVis   = pwGroup.style.display !== 'none';
        const pw      = document.getElementById('userPassword').value;
        const errBox  = document.getElementById('formErrors');

        let errs = [];
        if (!name)  errs.push('Por favor, insira o nome.');
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email))
            errs.push('Por favor, insira um email válido.');
        if (pwVis && pw.length < 6)
            //senha deve ter pelo maos um caractere especial, um número e uma letra maiúscula
            if (!/[!@#$%^&*(),.?":{}|<>]/.test(pw) || !/\d/.test(pw) || !/[A-Z]/.test(pw))
                errs.push('A senha deve conter pelo menos um caractere especial, um número e uma letra maiúscula.');
             else
            errs.push('A password deve ter pelo menos 6 caracteres.');

        if (errs.length) {
            e.preventDefault();
            errBox.textContent    = errs.join(' ');
            errBox.style.display  = 'block';
        }
    });
});
