<?php require_once APPROOT . '/app/views/layout/header.php'; ?>
<div class="container" style="max-width:720px;">

    <div class="page-header">
        <h1><i class="fa-solid fa-clipboard-check"></i> Detalhe da Resposta</h1>
        <a href="<?php echo URLROOT; ?>/my/history" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Histórico
        </a>
    </div>

    <div class="df-card mb-4">
        <div class="df-card-header">
            <i class="fa-solid fa-file-alt"></i>
            <h5><?php echo htmlspecialchars($data['response']->form_title ?? 'Formulário'); ?></h5>
        </div>
        <div class="df-card-body">
            <p class="text-muted mb-0">
                <i class="fa-solid fa-calendar me-1 text-success"></i>
                Submetido em <?php echo date('d/m/Y \à\s H:i', strtotime($data['response']->submitted_at)); ?>
            </p>
        </div>
    </div>

    <?php foreach ($data['answers'] as $i => $answer): ?>
    <div class="question-block mb-3">
        <div class="q-label">
            <span class="badge rounded-pill me-1" style="background:var(--green-600);font-size:.7rem;"><?php echo $i+1; ?></span>
            <?php echo htmlspecialchars($answer->question_label ?? '(pergunta removida)'); ?>
        </div>
        <div class="ps-2 border-start border-2 mt-2" style="border-color:var(--green-400) !important;">
            <?php if (($answer->question_type ?? '') === 'upload'): ?>
                <?php if (!empty($answer->file_path)): ?>
                    <a href="<?php echo URLROOT; ?>/download/<?php echo urlencode($answer->file_path); ?>"
                       class="btn btn-sm btn-outline-success" target="_blank">
                        <i class="fa-solid fa-download me-1"></i>Ver Ficheiro
                    </a>
                <?php else: ?>
                    <span class="text-muted fst-italic">Sem ficheiro</span>
                <?php endif; ?>
            <?php elseif (($answer->question_type ?? '') === 'checkbox'): ?>
                <?php foreach (json_decode($answer->value ?? '[]', true) ?? [] as $v): ?>
                    <span class="df-badge df-badge-published me-1"><?php echo htmlspecialchars($v); ?></span>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="mb-0"><?php echo nl2br(htmlspecialchars($answer->value ?? '—')); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

</div>
<?php require_once APPROOT . '/app/views/layout/footer.php'; ?>
