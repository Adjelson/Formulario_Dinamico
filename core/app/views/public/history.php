<?php require_once APPROOT . '/app/views/layout/header.php'; ?>
<div class="container-xl">
    <div class="page-header">
        <h1><i class="fa-solid fa-clock-rotate-left"></i> Meu Histórico</h1>
        <a href="<?php echo URLROOT; ?>/home" class="btn btn-secondary">
            <i class="fa-solid fa-list-check"></i> Ver Formulários
        </a>
    </div>

    <?php if (empty($data['responses'])): ?>
        <div class="empty-state">
            <i class="fa-solid fa-inbox"></i>
            <h5>Sem respostas ainda</h5>
            <p>Ainda não preencheu nenhum formulário.</p>
            <a href="<?php echo URLROOT; ?>/home" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-arrow-right"></i> Ver Formulários Disponíveis
            </a>
        </div>
    <?php else: ?>
        <div class="df-table-wrapper">
            <div class="table-responsive">
            <table class="table">
                <thead><tr>
                    <th><i class="fa-solid fa-file-alt me-1"></i>Formulário</th>
                    <th><i class="fa-solid fa-calendar me-1"></i>Data de Submissão</th>
                    <th><i class="fa-solid fa-gear me-1"></i>Ações</th>
                </tr></thead>
                <tbody>
                    <?php foreach ($data['responses'] as $response): ?>
                    <tr>
                        <td class="fw-600"><?php echo htmlspecialchars($response->form_title); ?></td>
                        <td class="text-muted"><?php echo date('d/m/Y \à\s H:i', strtotime($response->submitted_at)); ?></td>
                        <td>
                            <div class="d-flex gap-2 flex-wrap">
                                <!-- Ver detalhe -->
                                <a href="<?php echo URLROOT; ?>/my/history/<?php echo $response->id; ?>"
                                   class="btn btn-sm btn-outline-success">
                                    <i class="fa-solid fa-eye"></i> Ver
                                </a>
                                <!-- Preencher novamente (vai para o formulário que mostra o estado) 
                                <?php if (!empty($response->form_slug)): ?>
                                <a href="<?php echo URLROOT; ?>/forms/<?php echo htmlspecialchars($response->form_slug); ?>"
                                   class="btn btn-sm btn-secondary">
                                    <i class="fa-solid fa-rotate-right"></i> Preencher novamente
                                </a>
                                <?php endif; ?>
                                 Eliminar própria resposta -->
                              
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php require_once APPROOT . '/app/views/layout/footer.php'; ?>
