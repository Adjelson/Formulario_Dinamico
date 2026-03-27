<?php require_once APPROOT . '/app/views/layout/header.php'; ?>
<div class="container-xl" style="max-width:800px;">

    <div class="page-header">
        <h1><i class="fa-solid fa-clipboard-list"></i> Resposta #<?php echo $data['response']->id; ?></h1>
        <a href="<?php echo URLROOT; ?>/admin/forms/<?php echo $data['response']->form_id; ?>/responses" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </div>

    <!-- Info da submissão -->
    <div class="df-card mb-4">
        <div class="df-card-header">
            <i class="fa-solid fa-circle-info"></i>
            <h5>Informação da Submissão</h5>
        </div>
        <div class="df-card-body">
            <div class="row g-3">
                <div class="col-sm-6">
                    <div class="text-muted small mb-1"><i class="fa-solid fa-file-alt me-1 text-success"></i>Formulário</div>
                    <div class="fw-600"><?php echo htmlspecialchars($data['response']->form_title ?? 'N/A'); ?></div>
                </div>
                <div class="col-sm-6">
                    <div class="text-muted small mb-1"><i class="fa-solid fa-user me-1 text-success"></i>Utilizador</div>
                    <div class="fw-600"><?php echo htmlspecialchars($data['response']->user_name ?? 'Anónimo'); ?></div>
                </div>
                <div class="col-sm-6">
                    <div class="text-muted small mb-1"><i class="fa-solid fa-envelope me-1 text-success"></i>Email</div>
                    <div><?php echo htmlspecialchars($data['response']->user_email ?? 'N/A'); ?></div>
                </div>
                <div class="col-sm-6">
                    <div class="text-muted small mb-1"><i class="fa-solid fa-calendar me-1 text-success"></i>Data de Submissão</div>
                    <div><?php echo date('d/m/Y \à\s H:i:s', strtotime($data['response']->submitted_at)); ?></div>
                </div>
                <div class="col-sm-6">
                    <div class="text-muted small mb-1"><i class="fa-solid fa-network-wired me-1 text-success"></i>Endereço IP</div>
                    <div class="font-monospace small"><?php echo htmlspecialchars($data['response']->ip_address); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Respostas -->
    <div class="section-title"><i class="fa-solid fa-list-check"></i> Respostas às Perguntas</div>

    <?php if (empty($data['answers'])): ?>
        <div class="empty-state"><i class="fa-solid fa-circle-question"></i><h5>Sem respostas registadas</h5></div>
    <?php else: ?>
        <?php foreach ($data['answers'] as $i => $answer): ?>
        <div class="df-card mb-3">
            <div class="df-card-body">
                <div class="d-flex align-items-start gap-2 mb-2">
                    <span class="badge rounded-pill" style="background:var(--green-600);font-size:.7rem;flex-shrink:0;margin-top:.1rem;"><?php echo $i+1; ?></span>
                    <div>
                        <div class="fw-700 mb-1"><?php echo htmlspecialchars($answer->question_label ?? '(pergunta removida)'); ?></div>
                        <span class="df-badge df-badge-draft" style="font-size:.68rem;">
                            <?php
                            $typeNames = ['short_text'=>'Texto Curto','long_text'=>'Texto Longo','numeric'=>'Numérico',
                                          'checkbox'=>'Múltipla Escolha','radio'=>'Escolha Única','upload'=>'Upload'];
                            echo $typeNames[$answer->question_type ?? ''] ?? ($answer->question_type ?? 'N/A');
                            ?>
                        </span>
                    </div>
                </div>
                <div class="ps-3 border-start border-2" style="border-color:var(--green-400) !important;">
                    <?php if (($answer->question_type ?? '') === 'upload'): ?>
                        <?php if (!empty($answer->file_path)): ?>
                            <a href="<?php echo URLROOT; ?>/download/<?php echo urlencode($answer->file_path); ?>"
                               class="btn btn-sm btn-outline-success" target="_blank">
                                <i class="fa-solid fa-download me-1"></i>Descarregar Ficheiro
                            </a>
                        <?php else: ?>
                            <span class="text-muted fst-italic">Sem ficheiro</span>
                        <?php endif; ?>
                    <?php elseif (($answer->question_type ?? '') === 'checkbox'): ?>
                        <?php
                        $vals = json_decode($answer->value ?? '[]', true) ?? [];
                        foreach ($vals as $v): ?>
                            <span class="df-badge df-badge-published me-1 mb-1"><?php echo htmlspecialchars($v); ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($answer->value ?? '—')); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>
<?php require_once APPROOT . '/app/views/layout/footer.php'; ?>
