<?php require_once APPROOT . '/app/views/layout/header.php'; ?>
<div class="container-xl">
    <div class="page-header">
        <h1><i class="fa-solid fa-list-check"></i> Formulários Disponíveis</h1>
        <a href="<?php echo URLROOT; ?>/my/history" class="btn btn-secondary">
            <i class="fa-solid fa-clock-rotate-left"></i> Meu Histórico
        </a>
    </div>

    <div class="row mb-3">
        <div class="col-md-5">
            <div class="df-search">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" id="searchForms" placeholder="Pesquisar formulários..." class="form-control">
            </div>
        </div>
        <?php
        $answeredCount = count($data['answeredFormIds'] ?? []);
        $totalCount    = count($data['forms']);
        ?>
        <div class="col-md-7 d-flex align-items-center justify-content-end gap-3 mt-2 mt-md-0">
            <span class="text-muted small">
                <i class="fa-solid fa-circle-check text-success me-1"></i>
                <strong><?php echo $answeredCount; ?></strong> de <strong><?php echo $totalCount; ?></strong> respondidos
            </span>
            <?php if ($answeredCount > 0 && $answeredCount == $totalCount): ?>
                <span class="df-badge df-badge-published">
                    <i class="fa-solid fa-trophy"></i> Todos respondidos!
                </span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($data['forms'])): ?>
        <div class="empty-state">
            <i class="fa-solid fa-folder-open"></i>
            <h5>Nenhum formulário disponível</h5>
            <p>Não existem formulários publicados de momento.</p>
        </div>
    <?php else: ?>
        <div class="forms-grid" id="formsGrid">
            <?php foreach ($data['forms'] as $form):
                $isAnswered  = isset($data['answeredFormIds'][$form->id]);
                $responseId  = $isAnswered ? $data['answeredFormIds'][$form->id] : null;
            ?>
                <div class="form-card <?php echo $isAnswered ? 'form-card-answered' : ''; ?>"
                    data-title="<?php echo strtolower(htmlspecialchars($form->title)); ?>">

                    <!-- Imagem de capa ou gradiente -->
                    <?php if (!empty($form->cover_image)): ?>
                        <div class="form-card-cover">
                            <img src="<?php echo URLROOT . '/cover/' . urlencode($form->cover_image); ?>"
                                alt="<?php echo htmlspecialchars($form->title); ?>"
                                class="form-card-cover-img">
                            <?php if ($isAnswered): ?>
                                <div class="form-card-answered-overlay">
                                    <i class="fa-solid fa-circle-check"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="form-card-accent <?php echo $isAnswered ? 'form-card-accent-done' : ''; ?>"></div>
                    <?php endif; ?>

                    <div class="form-card-body">
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                            <h5 class="form-card-title mb-0"><?php echo htmlspecialchars($form->title); ?></h5>
                            <?php if ($isAnswered): ?>
                                <span class="df-badge df-badge-published flex-shrink-0" style="font-size:.65rem;">
                                    <i class="fa-solid fa-check"></i> Respondido
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($form->description)): ?>
                            <p class="form-card-desc"><?php echo htmlspecialchars($form->description); ?></p>
                        <?php else: ?>
                            <p class="form-card-desc text-muted fst-italic">Sem descrição.</p>
                        <?php endif; ?>
                    </div>

                    <div class="form-card-footer">
                        <?php if ($isAnswered): ?>
                            <!-- Já respondido: ver ou preencher novamente -->
                            <a href="<?php echo URLROOT; ?>/my/history/<?php echo $responseId; ?>"
                                class="btn btn-sm btn-outline-success">
                                <i class="fa-solid fa-eye me-1"></i> Ver resposta
                            </a>
                            <!--   <a href="<?php echo URLROOT; ?>/forms/<?php echo htmlspecialchars($form->slug); ?>"
                           class="btn btn-sm btn-secondary">
                            <i class="fa-solid fa-rotate-right me-1"></i> Preencher novamente
                        </a>--->
                    <?php else: ?>
                        <a href="<?php echo URLROOT; ?>/forms/<?php echo htmlspecialchars($form->slug); ?>"
                           class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-arrow-right me-1"></i> Preencher
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('searchForms').addEventListener('input', function() {
    var q = this.value.toLowerCase();
    document.querySelectorAll('#formsGrid .form-card').forEach(function(card) {
        card.style.display = card.dataset.title.includes(q) ? '' : 'none';
    });
});
</script>
<?php require_once APPROOT . '/app/views/layout/footer.php'; ?>