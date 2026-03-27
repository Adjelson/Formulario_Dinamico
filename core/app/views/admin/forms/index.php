<?php require_once APPROOT . '/app/views/layout/header.php'; ?>
<div class="container-xl">

    <div class="page-header">
        <h1><i class="fa-solid fa-file-alt"></i> Formulários</h1>
        <a href="<?php echo URLROOT; ?>/admin/forms/create" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Novo Formulário
        </a>
    </div>

    <!-- Pesquisa -->
    <div class="row mb-3">
        <div class="col-md-5">
            <div class="df-search">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" id="searchForms" placeholder="Pesquisar formulários..." class="form-control">
            </div>
        </div>
        <div class="col-md-3 mt-2 mt-md-0">
            <select id="filterStatus" class="form-select" style="height:40px;font-size:.875rem;">
                <option value="">Todos os status</option>
                <option value="published">Publicado</option>
                <option value="draft">Rascunho</option>
                <option value="closed">Fechado</option>
            </select>
        </div>
    </div>

    <div class="df-table-wrapper">
        <div class="table-responsive">
            <table class="table" id="formsTable">
                <thead>
                    <tr style="align-items: center;text-align: center;">
                        <th><i class="fa-solid fa-heading me-1"></i>Título</th>
                        <th><i class="fa-solid fa-circle-dot me-1"></i>Status</th>
                        <th><i class="fa-solid fa-inbox me-1"></i>Respostas</th>
                        <th><i class="fa-solid fa-calendar me-1"></i>Criado em</th>
                        <th><i class="fa-solid fa-gear me-1"></i>Ações</th>
                    </tr>
                </thead>
                <tbody id="formsBody">
                    <?php if (empty($data['forms'])): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state py-4">
                                    <i class="fa-solid fa-folder-open"></i>
                                    <h5>Nenhum formulário criado</h5>
                                    <p>Crie o primeiro formulário para começar a recolher respostas.</p>
                                    <a href="<?php echo URLROOT; ?>/admin/forms/create" class="btn btn-primary btn-sm">
                                        <i class="fa-solid fa-plus"></i> Criar Formulário
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['forms'] as $form): ?>
                            <tr data-title="<?php echo strtolower(htmlspecialchars($form->title)); ?>"
                                data-status="<?php echo $form->status; ?>">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="form-card-icon" style="width:32px;height:32px;font-size:.9rem;">
                                            <i class="<?php echo !empty($form->icon ?? '') ? htmlspecialchars($form->icon) : 'fa-solid fa-file-alt'; ?>"></i>
                                        </div>
                                        <span class="fw-600"><?php echo htmlspecialchars($form->title); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="df-badge df-badge-<?php echo $form->status; ?>">
                                        <?php if ($form->status == 'published'): ?><i class="fa-solid fa-circle-check"></i><?php endif; ?>
                                        <?php if ($form->status == 'draft'): ?><i class="fa-solid fa-pencil"></i><?php endif; ?>
                                        <?php if ($form->status == 'closed'): ?><i class="fa-solid fa-lock"></i><?php endif; ?>
                                        <?php echo ucfirst($form->status); ?>
                                    </span>
                                </td>
                                <td><span class="badge bg-secondary rounded-pill"><?php echo $form->response_count; ?></span></td>
                                <td class="text-muted small"><?php echo date('d/m/Y H:i', strtotime($form->created_at)); ?></td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <a href="<?php echo URLROOT; ?>/admin/forms/<?php echo $form->id; ?>/edit"
                                            class="btn btn-sm btn-secondary" title="Editar">Editar
                                            <i class="fa-solid fa-pencil"></i>
                                        </a>
                                        <a href="<?php echo URLROOT; ?>/admin/forms/<?php echo $form->id; ?>/responses"
                                            class="btn btn-sm btn-outline-success" title="Ver Respostas">Respostas
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <?php if ($form->status == 'published'): ?>
                                            <button class="btn btn-sm btn-share" title="Copiar link"
                                                onclick="copyFormLink('<?php echo URLROOT . '/forms/' . htmlspecialchars($form->slug); ?>')">
                                                Link <i class="fa-solid fa-link"></i>
                                            </button>
                                            <a href="<?php echo URLROOT; ?>/forms/<?php echo $form->slug; ?>" target="_blank"
                                                class="btn btn-sm btn-secondary" title="Visualizar">Visualizar
                                                <i class="fa-solid fa-external-link-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                        <form action="<?php echo URLROOT; ?>/admin/forms/<?php echo $form->id; ?>/delete" method="POST" class="d-inline">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar"
                                                onclick="return confirm('Tem a certeza que quer eliminar este formulário?')">Eliminar
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination placeholder -->
    <div id="formsPagination" class="df-pagination mt-3"></div>
</div>

<!-- Toast -->
<div class="df-toast" id="copyToast">
    <i class="fa-solid fa-check-circle text-success"></i> Link copiado para a área de transferência!
</div>

<script>
    // Pesquisa + filtro
    const searchInput = document.getElementById('searchForms');
    const filterSelect = document.getElementById('filterStatus');
    const rows = Array.from(document.querySelectorAll('#formsBody tr[data-title]'));

    function filterTable() {
        const q = searchInput.value.toLowerCase();
        const st = filterSelect.value;
        rows.forEach(r => {
            const matchQ = r.dataset.title.includes(q);
            const matchSt = !st || r.dataset.status === st;
            r.style.display = matchQ && matchSt ? '' : 'none';
        });
        renderPagination();
    }
    searchInput.addEventListener('input', filterTable);
    filterSelect.addEventListener('change', filterTable);

    // Paginação simples
    const PER_PAGE = 10;
    let currentPage = 1;

    function getVisible() {
        return rows.filter(r => r.style.display !== 'none');
    }

    function renderPagination() {
        const vis = getVisible();
        const total = Math.ceil(vis.length / PER_PAGE);
        const pg = document.getElementById('formsPagination');
        if (total <= 1) {
            pg.innerHTML = '';
            return;
        }
        let html = '';
        html += `<a class="page-btn ${currentPage===1?'disabled':''}" onclick="goPage(${currentPage-1})"><i class="fa-solid fa-chevron-left"></i></a>`;
        for (let i = 1; i <= total; i++) {
            html += `<a class="page-btn ${i===currentPage?'active':''}" onclick="goPage(${i})">${i}</a>`;
        }
        html += `<a class="page-btn ${currentPage===total?'disabled':''}" onclick="goPage(${currentPage+1})"><i class="fa-solid fa-chevron-right"></i></a>`;
        pg.innerHTML = html;
        // show/hide rows
        vis.forEach((r, i) => r.style.display = (i >= (currentPage - 1) * PER_PAGE && i < currentPage * PER_PAGE) ? '' : 'none');
    }

    function goPage(p) {
        const total = Math.ceil(getVisible().length / PER_PAGE);
        if (p < 1 || p > total) return;
        currentPage = p;
        renderPagination();
    }
    renderPagination();

    function copyFormLink(url) {
        navigator.clipboard.writeText(url).then(() => {
            const t = document.getElementById('copyToast');
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 2500);
        });
    }
</script>
<?php require_once APPROOT . '/app/views/layout/footer.php'; ?>