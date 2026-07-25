<?php require_once APPROOT . '/app/views/layout/header.php'; ?>
<div class="container-xl">

    <div class="page-header">
        <h1><i class="fa-solid fa-users"></i> Utilizadores</h1>
        <button type="button" class="btn btn-primary" onclick="openCreateUserModal()">
            <i class="fa-solid fa-user-plus"></i> Novo Utilizador
        </button>
    </div>

    <?php if (!empty($_GET['err'])): ?>
        <div class="df-alert df-alert-danger mb-3">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?php echo htmlspecialchars($_GET['err']); ?>
        </div>
    <?php endif; ?>

    <!-- Pesquisa -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="df-search">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <label for="searchUsers" class="visually-hidden">Pesquisar utilizadores</label>
                <input type="search" id="searchUsers" placeholder="Pesquisar por nome ou email" class="form-control" autocomplete="off" aria-controls="usersTable">
            </div>
        </div>
    </div>

    <div class="df-table-wrapper">
        <div class="table-responsive">
        <table class="table" id="usersTable">
            <caption class="visually-hidden">Lista de utilizadores, perfis, estados e ações disponíveis</caption>
            <thead>
                <tr>
                    <th><i class="fa-solid fa-user me-1"></i>Nome</th>
                    <th><i class="fa-solid fa-envelope me-1"></i>Email</th>
                    <th><i class="fa-solid fa-shield-halved me-1"></i>Perfil</th>
                    <th><i class="fa-solid fa-circle-dot me-1"></i>Estado</th>
                    <th><i class="fa-solid fa-calendar me-1"></i>Registado em</th>
                    <th><i class="fa-solid fa-gear me-1"></i>Ações</th>
                </tr>
            </thead>
            <tbody id="usersBody">
                <?php if (empty($data['users'])): ?>
                <tr><td colspan="6">
                    <div class="empty-state py-3">
                        <i class="fa-solid fa-users-slash"></i>
                        <h5>Nenhum utilizador encontrado</h5>
                    </div>
                </td></tr>
                <?php else: ?>
                <?php foreach ($data['users'] as $user): ?>
                <tr data-search="<?php echo strtolower(htmlspecialchars($user->name . ' ' . $user->email)); ?>">
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="user-avatar-sm" style="width:34px;height:34px;font-size:.85rem;">
                                <?php echo strtoupper(substr($user->name, 0, 1)); ?>
                            </div>
                            <span class="fw-600"><?php echo htmlspecialchars($user->name); ?></span>
                        </div>
                    </td>
                    <td class="text-muted"><?php echo htmlspecialchars($user->email); ?></td>
                    <td>
                        <span class="df-badge df-badge-<?php echo $user->role; ?>">
                            <i class="fa-solid fa-<?php echo $user->role=='admin'?'shield-halved':'user'; ?>"></i>
                            <?php echo ucfirst($user->role); ?>
                        </span>
                    </td>
                    <td>
                        <span class="df-badge df-badge-<?php echo $user->is_active ? 'active' : 'inactive'; ?>">
                            <i class="fa-solid fa-circle" style="font-size:.5rem;"></i>
                            <?php echo $user->is_active ? 'Ativo' : 'Inativo'; ?>
                        </span>
                    </td>
                    <td class="text-muted small"><?php echo date('d/m/Y', strtotime($user->created_at)); ?></td>
                    <td>
                        <button class="btn btn-sm btn-secondary me-1"
                            type="button" aria-label="Editar <?php echo e($user->name); ?>"
                            onclick="openEditUserModal(<?php echo e(json_encode($user, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)); ?>)">
                            <i class="fa-solid fa-pencil" aria-hidden="true"></i>
                        </button>
                        <form action="<?php echo URLROOT; ?>/admin/users/<?php echo $user->id; ?>/delete"
                              method="POST" class="d-inline">
                                  <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-sm btn-danger"
                                aria-label="Desativar <?php echo e($user->name); ?>"
                                onclick="return confirm('Desativar este utilizador? O histórico será preservado.')">
                                <i class="fa-solid fa-user-slash" aria-hidden="true"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- Paginação -->
    <div id="usersPagination" class="df-pagination mt-3"></div>
</div>

<!-- Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">
                    <i class="fa-solid fa-user-plus me-2"></i>Novo Utilizador
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="userForm" method="POST" action="#">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label for="userName" class="form-label"><i class="fa-solid fa-user text-success" aria-hidden="true"></i> Nome <span class="text-danger">*</span></label>
                        <input type="text" id="userName" name="name" class="form-control" placeholder="Nome completo" autocomplete="name" minlength="2" maxlength="100" required>
                    </div>
                    <div class="mb-3">
                        <label for="userEmail" class="form-label"><i class="fa-solid fa-envelope text-success" aria-hidden="true"></i> Email <span class="text-danger">*</span></label>
                        <input type="email" id="userEmail" name="email" class="form-control" placeholder="email@exemplo.com" autocomplete="email" maxlength="190" required>
                    </div>
                    <div id="passwordGroup" class="mb-3">
                        <label for="userPassword" class="form-label"><i class="fa-solid fa-lock text-success" aria-hidden="true"></i> Palavra-passe</label>
                        <div class="input-group">
                            <input type="password" id="userPassword" name="password" class="form-control" placeholder="Mínimo 8 caracteres" minlength="8" maxlength="128" autocomplete="new-password" aria-describedby="passwordHelp">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="togglePwModal()" aria-label="Mostrar ou ocultar palavra-passe" aria-controls="userPassword">
                                <i class="fa-solid fa-eye" id="eyeIcon" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="form-text" id="passwordHelp">Na criação: pelo menos 8 caracteres, com maiúscula, minúscula e número.</div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label for="userRole" class="form-label"><i class="fa-solid fa-shield-halved text-success" aria-hidden="true"></i> Perfil</label>
                            <select id="userRole" name="role" class="form-select">
                                <option value="user">Utilizador</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                        <div class="col-6 d-flex align-items-end pb-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="userActive" name="is_active" checked>
                                <label class="form-check-label fw-600" for="userActive">Ativo</label>
                            </div>
                        </div>
                    </div>
                    <div id="formErrors" class="df-alert df-alert-danger mt-3" role="alert" aria-live="assertive" tabindex="-1" hidden></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('userForm').requestSubmit()">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
window.BASE_URL = '<?php echo URLROOT; ?>';

// Paginação acessível e compatível com a pesquisa
const PER_PAGE = 10;
let curPage = 1;
let filteredRows = [];

function refreshUserRows() {
    const query = document.getElementById('searchUsers').value.trim().toLocaleLowerCase('pt-PT');
    filteredRows = Array.from(document.querySelectorAll('#usersBody tr[data-search]'))
        .filter(row => row.dataset.search.toLocaleLowerCase('pt-PT').includes(query));
    curPage = 1;
    renderUsersPagination();
}

function renderUsersPagination() {
    const allRows = Array.from(document.querySelectorAll('#usersBody tr[data-search]'));
    const totalPages = Math.max(1, Math.ceil(filteredRows.length / PER_PAGE));
    curPage = Math.min(curPage, totalPages);
    const startIndex = (curPage - 1) * PER_PAGE;
    const visibleSet = new Set(filteredRows.slice(startIndex, startIndex + PER_PAGE));
    allRows.forEach(row => { row.hidden = !visibleSet.has(row); });

    const pagination = document.getElementById('usersPagination');
    pagination.innerHTML = '';
    pagination.setAttribute('aria-label', 'Paginação de utilizadores');
    if (filteredRows.length <= PER_PAGE) return;

    const makeButton = (label, page, disabled = false, current = false) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = `page-btn${current ? ' active' : ''}`;
        button.textContent = label;
        button.disabled = disabled;
        if (current) button.setAttribute('aria-current', 'page');
        button.addEventListener('click', () => { curPage = page; renderUsersPagination(); });
        pagination.appendChild(button);
    };

    makeButton('‹', curPage - 1, curPage === 1);
    for (let page = 1; page <= totalPages; page += 1) {
        makeButton(String(page), page, false, page === curPage);
    }
    makeButton('›', curPage + 1, curPage === totalPages);
}

document.getElementById('searchUsers')?.addEventListener('input', refreshUserRows);
filteredRows = Array.from(document.querySelectorAll('#usersBody tr[data-search]'));
renderUsersPagination();

// Modal helpers — inicializar de forma lazy para garantir que Bootstrap já foi carregado
let _bsModal = null;
function getBsModal() {
    if (!_bsModal) {
        _bsModal = new bootstrap.Modal(document.getElementById('userModal'));
    }
    return _bsModal;
}

function openCreateUserModal() {
    const form = document.getElementById('userForm');
    document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-user-plus me-2"></i>Novo Utilizador';
    form.action = window.BASE_URL + '/admin/users/store';
    document.getElementById('passwordGroup').style.display = 'block';
    document.getElementById('userPassword').required = true;
    document.getElementById('formErrors').hidden = true;
    form.reset(); document.getElementById('userActive').checked = true;
    getBsModal().show();
}

function openEditUserModal(user) {
    const form = document.getElementById('userForm');
    document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-pencil me-2"></i>Editar Utilizador';
    form.action = window.BASE_URL + '/admin/users/' + user.id + '/update';
    document.getElementById('passwordGroup').style.display = 'none';
    document.getElementById('userPassword').required = false;
    document.getElementById('userName').value         = user.name;
    document.getElementById('userEmail').value        = user.email;
    document.getElementById('userRole').value         = user.role;
    document.getElementById('userActive').checked     = user.is_active == 1;
    document.getElementById('formErrors').hidden = true;
    getBsModal().show();
}

function togglePwModal() {
    const inp = document.getElementById('userPassword');
    const ico = document.getElementById('eyeIcon');
    if (inp.type === 'password') { inp.type='text'; ico.classList.replace('fa-eye','fa-eye-slash'); }
    else { inp.type='password'; ico.classList.replace('fa-eye-slash','fa-eye'); }
}

document.getElementById('userForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const name  = document.getElementById('userName').value.trim();
    const email = document.getElementById('userEmail').value.trim();
    const pwVis = document.getElementById('passwordGroup').style.display !== 'none';
    const pw    = document.getElementById('userPassword').value;
    const errBox = document.getElementById('formErrors');
    let errs = [];
    if (!name) errs.push('Insira o nome.');
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errs.push('Email inválido.');
    if (pwVis && !/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,128}$/.test(pw)) errs.push('A palavra-passe deve ter 8 caracteres, maiúscula, minúscula e número.');
    if (errs.length) {
        errBox.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + errs.join(' ');
        errBox.hidden = false; errBox.focus(); return;
    }
    this.submit();
});
</script>
<?php require_once APPROOT . '/app/views/layout/footer.php'; ?>
