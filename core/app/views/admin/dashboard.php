<?php require_once APPROOT . '/app/views/layout/header.php'; ?>
<div class="container-xl">

    <div class="page-header">
        <h1><i class="fa-solid fa-gauge-high"></i> Dashboard</h1>
        <a href="<?php echo URLROOT; ?>/admin/forms/create" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Novo Formulário
        </a>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-file-alt"></i></div>
                <div class="stat-value"><?php echo $data['totalForms']; ?></div>
                <div class="stat-label">Total de Formulários</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-inbox"></i></div>
                <div class="stat-value"><?php echo $data['totalResponses']; ?></div>
                <div class="stat-label">Total de Respostas</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                <div class="stat-value"><?php echo $data['activeUsers']; ?></div>
                <div class="stat-label">Utilizadores Registados</div>
            </div>
        </div>
    </div>

    <!-- Recent Forms -->
    <div class="section-title"><i class="fa-solid fa-clock-rotate-left"></i> Formulários Recentes</div>
    <div class="df-table-wrapper">
        <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th><i class="fa-solid fa-heading me-1"></i>Título</th>
                    <th><i class="fa-solid fa-circle-dot me-1"></i>Status</th>
                    <th><i class="fa-solid fa-inbox me-1"></i>Respostas</th>
                    <th><i class="fa-solid fa-gear me-1"></i>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['recentForms'])): ?>
                <tr><td colspan="4" class="text-center py-4 text-muted">
                    <i class="fa-solid fa-folder-open me-2"></i>Nenhum formulário criado ainda.
                </td></tr>
                <?php else: ?>
                <?php foreach ($data['recentForms'] as $form): ?>
                <tr>
                    <td class="fw-600"><?php echo htmlspecialchars($form->title); ?></td>
                    <td>
                        <span class="df-badge df-badge-<?php echo $form->status; ?>">
                            <?php if ($form->status=='published'): ?><i class="fa-solid fa-circle-check"></i><?php endif; ?>
                            <?php if ($form->status=='draft'): ?><i class="fa-solid fa-pencil"></i><?php endif; ?>
                            <?php if ($form->status=='closed'): ?><i class="fa-solid fa-lock"></i><?php endif; ?>
                            <?php echo ucfirst($form->status); ?>
                        </span>
                    </td>
                    <td><span class="badge bg-secondary"><?php echo $form->response_count; ?></span></td>
                    <td>
                        <a href="<?php echo URLROOT; ?>/admin/forms/<?php echo $form->id; ?>/edit" class="btn btn-sm btn-secondary me-1">
                            <i class="fa-solid fa-pencil"></i> Editar
                        </a>
                        <a href="<?php echo URLROOT; ?>/admin/forms/<?php echo $form->id; ?>/responses" class="btn btn-sm btn-outline-success">
                            <i class="fa-solid fa-eye"></i> Respostas
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
    <div class="text-end mt-2">
        <a href="<?php echo URLROOT; ?>/admin/forms" class="btn btn-sm btn-secondary">
            Ver todos <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    </div>
</div>
<?php require_once APPROOT . '/app/views/layout/footer.php'; ?>
