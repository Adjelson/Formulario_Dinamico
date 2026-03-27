<?php require_once APPROOT . '/app/views/layout/header.php'; ?>
<div class="container-xl">
    <div class="page-header">
        <h1><i class="fa-solid fa-inbox"></i> Respostas: <?php echo htmlspecialchars($data['form']->title); ?></h1>
        <div class="d-flex gap-2">
            <a href="<?php echo URLROOT; ?>/admin/forms/<?php echo $data['form']->id; ?>/export-zip" class="btn btn-outline-success">
                <i class="fa-solid fa-file-zipper"></i> Exportar ZIP
            </a>
            <a href="<?php echo URLROOT; ?>/admin/forms" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="df-search">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" id="searchResp" placeholder="Pesquisar por utilizador ou email..." class="form-control">
            </div>
        </div>
    </div>

    <?php if (empty($data['responses'])): ?>
        <div class="empty-state">
            <i class="fa-solid fa-inbox"></i>
            <h5>Sem respostas ainda</h5>
            <p>Nenhuma resposta submetida para este formulário.</p>
        </div>
    <?php else: ?>
        <div class="df-table-wrapper">
            <div class="table-responsive">
            <table class="table" id="respTable">
                <thead><tr>
                    <th>#ID</th>
                    <th><i class="fa-solid fa-user me-1"></i>Utilizador</th>
                    <th><i class="fa-solid fa-envelope me-1"></i>Email</th>
                    <th><i class="fa-solid fa-calendar me-1"></i>Data</th>
                    <th><i class="fa-solid fa-network-wired me-1"></i>IP</th>
                    <th><i class="fa-solid fa-gear me-1"></i>Ações</th>
                </tr></thead>
                <tbody id="respBody">
                    <?php foreach ($data['responses'] as $resp): ?>
                    <tr data-search="<?php echo strtolower(htmlspecialchars(($resp->user_name??'') . ' ' . ($resp->user_email??''))); ?>">
                        <td class="text-muted small">#<?php echo $resp->id; ?></td>
                        <td class="fw-600"><?php echo htmlspecialchars($resp->user_name ?? 'Anónimo'); ?></td>
                        <td class="text-muted"><?php echo htmlspecialchars($resp->user_email ?? 'N/A'); ?></td>
                        <td class="text-muted small"><?php echo date('d/m/Y H:i', strtotime($resp->submitted_at)); ?></td>
                        <td class="text-muted small"><?php echo htmlspecialchars($resp->ip_address); ?></td>
                        <td>
                            <a href="<?php echo URLROOT; ?>/admin/responses/<?php echo $resp->id; ?>/detail" class="btn btn-sm btn-outline-success me-1">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <form action="<?php echo URLROOT; ?>/admin/responses/<?php echo $resp->id; ?>/delete" method="POST" class="d-inline">
                                <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Eliminar esta resposta?')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
        <div id="respPagination" class="df-pagination mt-3"></div>
    <?php endif; ?>
</div>

<script>
document.getElementById('searchResp')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#respBody tr[data-search]').forEach(r => {
        r.style.display = r.dataset.search.includes(q) ? '' : 'none';
    });
});
</script>
<?php require_once APPROOT . '/app/views/layout/footer.php'; ?>
