<?php require_once APPROOT . '/app/views/layout/header.php'; ?>
<div class="container-xl">

    <div class="page-header">
        <h1><i class="fa-solid fa-users"></i> Utilizadores</h1>
        <button class="btn btn-primary" onclick="openCreateUserModal()">
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
                <input type="text" id="searchUsers" placeholder="Pesquisar utilizadores..." class="form-control">
            </div>
        </div>
    </div>

    <div class="df-table-wrapper">
        <div class="table-responsive">
        <table class="table" id="usersTable">
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
                            onclick="openEditUserModal(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                            <i class="fa-solid fa-pencil"></i>
                        </button>
                        <form action="<?php echo URLROOT; ?>/admin/users/<?php echo $user->id; ?>/delete"
                              method="POST" class="d-inline">
                            <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm('Eliminar este utilizador?')">
                                <i class="fa-solid fa-trash"></i>
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
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">
                    <i class="fa-solid fa-user-plus me-2"></i>Novo Utilizador
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="userForm" method="POST" action="#">
                    <div class="mb-3">
                        <label class="form-label"><i class="fa-solid fa-user text-success"></i> Nome <span class="text-danger">*</span></label>
                        <input type="text" id="userName" name="name" class="form-control" placeholder="Nome completo" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fa-solid fa-envelope text-success"></i> Email <span class="text-danger">*</span></label>
                        <input type="email" id="userEmail" name="email" class="form-control" placeholder="email@exemplo.com" required>
                    </div>
                    <div id="passwordGroup" class="mb-3">
                        <label class="form-label"><i class="fa-solid fa-lock text-success"></i> Password</label>
                        <div class="input-group">
                            <input type="password" id="userPassword" name="password" class="form-control" placeholder="Mínimo 6 caracteres" minlength="6">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="togglePwModal()">
                                <i class="fa-solid fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                        <div class="form-text">Apenas necessário na criação.</div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label"><i class="fa-solid fa-shield-halved text-success"></i> Perfil</label>
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
                    <div id="formErrors" class="df-alert df-alert-danger mt-3" style="display:none;"></div>
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

// Pesquisa
document.getElementById('searchUsers')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    const rows = document.querySelectorAll('#usersBody tr[data-search]');
    rows.forEach(r => r.style.display = r.dataset.search.includes(q) ? '' : 'none');
    renderUsersPagination();
});

// Paginação
const PER_PAGE = 10; let curPage = 1;
function getVisibleRows() { return Array.from(document.querySelectorAll('#usersBody tr[data-search]')).filter(r => r.style.display !== 'none'); }
function renderUsersPagination() {
    const vis = getVisibleRows(); const total = Math.ceil(vis.length / PER_PAGE);
    const pg = document.getElementById('usersPagination');
    if (total <= 1) { pg.innerHTML = ''; return; }
    let html = `<a class="page-btn ${curPage===1?'disabled':''}" onclick="goUserPage(${curPage-1})"><i class="fa-solid fa-chevron-left"></i></a>`;
    for (let i=1;i<=total;i++) html += `<a class="page-btn ${i===curPage?'active':''}" onclick="goUserPage(${i})">${i}</a>`;
    html += `<a class="page-btn ${curPage===total?'disabled':''}" onclick="goUserPage(${curPage+1})"><i class="fa-solid fa-chevron-right"></i></a>`;
    pg.innerHTML = html;
    vis.forEach((r,i) => r.style.display = (i>=(curPage-1)*PER_PAGE && i<curPage*PER_PAGE) ? '' : 'none');
}
function goUserPage(p) {
    const total = Math.ceil(getVisibleRows().length / PER_PAGE);
    if (p<1||p>total) return; curPage=p; renderUsersPagination();
}
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
    document.getElementById('formErrors').style.display = 'none';
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
    document.getElementById('formErrors').style.display = 'none';
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
    if (pwVis && pw.length < 6) errs.push('Password com mínimo 6 caracteres.');
    if (errs.length) {
        errBox.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + errs.join(' ');
        errBox.style.display = 'flex'; return;
    }
    this.submit();
});
</script>
<?php require_once APPROOT . '/app/views/layout/footer.php'; ?>
