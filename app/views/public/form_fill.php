<?php require_once APPROOT . '/app/views/layout/header.php'; ?>
<div class="container" style="max-width:720px; padding-bottom: 3rem;">
    <div class="form-fill-wrapper">

        <!-- ── Cabeçalho ───────────────────────────────────────────── -->
        <div class="form-fill-header">
            <?php if (!empty($data['form']->cover_image)): ?>
                <div class="form-cover-img-wrapper">
                    <img src="<?php echo URLROOT . '/cover/' . urlencode($data['form']->cover_image); ?>"
                         alt="<?php echo htmlspecialchars($data['form']->title); ?>"
                         class="form-cover-img">
                </div>
            <?php else: ?>
                <div class="form-icon-fallback">
                    <i class="fa-solid fa-file-alt"></i>
                </div>
            <?php endif; ?>

            <div class="form-fill-header-text">
                <h1><?php echo htmlspecialchars($data['form']->title); ?></h1>
                <?php if (!empty($data['form']->description)): ?>
                    <p><?php echo htmlspecialchars($data['form']->description); ?></p>
                <?php endif; ?>
                <div class="form-fill-meta">
                    <span><i class="fa-solid fa-list-check"></i> <?php echo count($data['questions']); ?> pergunta(s)</span>
                    <span><i class="fa-solid fa-asterisk" style="font-size:.6rem;color:#fca5a5;"></i> Campos obrigatórios</span>
                </div>
            </div>
        </div>

        <!-- ── Corpo ────────────────────────────────────────────────── -->
        <div class="form-fill-body">
            <?php if (!empty($data['admin_view'])): ?>
                <div class="df-alert df-alert-warning mb-4">
                    <i class="fa-solid fa-eye"></i>
                    <span>Está a <strong>visualizar</strong> este formulário como administrador. Não é possível submeter respostas.</span>
                </div>
            <?php endif; ?>

            <?php if (empty($data['questions'])): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <h5>Formulário sem perguntas</h5>
                    <p>Este formulário ainda não tem perguntas configuradas.</p>
                </div>
            <?php else: ?>

            <form action="<?php echo URLROOT; ?>/forms/<?php echo $data['form']->slug; ?>/submit"
                  method="POST" enctype="multipart/form-data" id="responseForm" novalidate>

                <?php foreach ($data['questions'] as $idx => $question):
                    $qConfig = json_decode($question->config ?? '{}');
                ?>
                <div class="qfill-block" id="qblock-<?php echo $question->id; ?>">

                    <!-- Número + Label -->
                    <div class="qfill-label">
                        <span class="qfill-num"><?php echo $idx + 1; ?></span>
                        <span class="qfill-text">
                            <?php echo htmlspecialchars($question->label); ?>
                            <?php if ($question->is_required): ?>
                                <span class="qfill-required" title="Obrigatório">*</span>
                            <?php endif; ?>
                        </span>
                    </div>

                    <!-- Dica contextual por tipo -->
                    <?php
                    $hints = [
                        'short_text' => ['fa-pen-to-square',   'Digite a sua resposta de forma concisa.'],
                        'long_text'  => ['fa-align-left',      'Pode escrever uma resposta mais detalhada.'],
                        'numeric'    => ['fa-hashtag',         'Insira apenas valores numéricos (ex: 25).'],
                        'date'       => ['fa-calendar-days',   'Selecione uma data no calendário.'],
                        'checkbox'   => ['fa-check-square',    'Pode selecionar uma ou mais opções.'],
                        'radio'      => ['fa-circle-dot',      'Selecione apenas uma das opções abaixo.'],
                        'upload'     => ['fa-cloud-arrow-up',  'Clique para selecionar ou arraste o ficheiro.'],
                    ];
                    if (isset($hints[$question->type])):
                        [$hIcon, $hText] = $hints[$question->type];
                    ?>
                    <div class="qfill-hint">
                        <i class="fa-solid <?php echo $hIcon; ?>"></i>
                        <?php echo $hText; ?>
                    </div>
                    <?php endif; ?>

                    <!-- ── Campos por tipo ───────────────────────── -->
                    <?php if ($question->type === 'short_text'): ?>
                        <input type="text" class="form-control"
                            id="question_<?php echo $question->id; ?>"
                            name="question_<?php echo $question->id; ?>"
                            placeholder="A sua resposta aqui..."
                            <?php echo $question->is_required ? 'required' : ''; ?>>

                    <?php elseif ($question->type === 'long_text'): ?>
                        <textarea class="form-control" rows="4"
                            id="question_<?php echo $question->id; ?>"
                            name="question_<?php echo $question->id; ?>"
                            placeholder="Escreva a sua resposta aqui..."
                            <?php echo $question->is_required ? 'required' : ''; ?>></textarea>

                    <?php elseif ($question->type === 'numeric'): ?>
                        <div class="input-group" style="max-width:220px;">
                            <span class="input-group-text"><i class="fa-solid fa-hashtag"></i></span>
                            <input type="number" class="form-control"
                                id="question_<?php echo $question->id; ?>"
                                name="question_<?php echo $question->id; ?>"
                                placeholder="0"
                                <?php echo $question->is_required ? 'required' : ''; ?>>
                        </div>

                    <?php elseif ($question->type === 'date'): ?>
                        <div class="input-group" style="max-width:260px;">
                            <span class="input-group-text"><i class="fa-solid fa-calendar-days"></i></span>
                            <input type="date" class="form-control"
                                id="question_<?php echo $question->id; ?>"
                                name="question_<?php echo $question->id; ?>"
                                <?php if (!empty($qConfig->date_min)): ?>
                                    min="<?php echo htmlspecialchars($qConfig->date_min); ?>"
                                <?php endif; ?>
                                <?php if (!empty($qConfig->date_max)): ?>
                                    max="<?php echo htmlspecialchars($qConfig->date_max); ?>"
                                <?php endif; ?>
                                <?php echo $question->is_required ? 'required' : ''; ?>>
                        </div>
                        <?php if (!empty($qConfig->date_min) || !empty($qConfig->date_max)): ?>
                            <div class="form-text mt-1">
                                <i class="fa-solid fa-circle-info me-1"></i>
                                <?php
                                if (!empty($qConfig->date_min) && !empty($qConfig->date_max)) {
                                    echo 'Datas entre ' . date('d/m/Y', strtotime($qConfig->date_min)) . ' e ' . date('d/m/Y', strtotime($qConfig->date_max));
                                } elseif (!empty($qConfig->date_min)) {
                                    echo 'Data a partir de ' . date('d/m/Y', strtotime($qConfig->date_min));
                                } elseif (!empty($qConfig->date_max)) {
                                    echo 'Data até ' . date('d/m/Y', strtotime($qConfig->date_max));
                                }
                                ?>
                            </div>
                        <?php endif; ?>

                    <?php elseif ($question->type === 'checkbox'): ?>
                        <?php $opts = $qConfig->options ?? []; ?>
                        <div class="qfill-options mt-1">
                            <?php foreach ($opts as $option): ?>
                            <label class="qfill-check-option">
                                <input type="checkbox"
                                    name="question_<?php echo $question->id; ?>[]"
                                    value="<?php echo htmlspecialchars($option); ?>">
                                <span class="qfill-check-box"><i class="fa-solid fa-check"></i></span>
                                <span><?php echo htmlspecialchars($option); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>

                    <?php elseif ($question->type === 'radio'): ?>
                        <?php $opts = $qConfig->options ?? []; ?>
                        <div class="qfill-options mt-1">
                            <?php foreach ($opts as $option): ?>
                            <label class="qfill-radio-option">
                                <input type="radio"
                                    name="question_<?php echo $question->id; ?>"
                                    value="<?php echo htmlspecialchars($option); ?>"
                                    <?php echo $question->is_required ? 'required' : ''; ?>>
                                <span class="qfill-radio-dot"></span>
                                <span><?php echo htmlspecialchars($option); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>

                    <?php elseif ($question->type === 'upload'): ?>
                        <?php $types = $qConfig->allowed_types ?? []; ?>
                        <label class="qfill-upload-zone" for="question_<?php echo $question->id; ?>">
                            <div class="qfill-upload-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                            <div class="qfill-upload-text">
                                <strong>Clique para selecionar</strong> ou arraste o ficheiro aqui
                            </div>
                            <div class="qfill-upload-info">
                                <?php if ($types): ?>
                                    Formatos: <strong><?php echo strtoupper(implode(', ', $types)); ?></strong>
                                <?php endif; ?>
                                · Máx. 5MB
                            </div>
                            <input type="file" class="d-none"
                                id="question_<?php echo $question->id; ?>"
                                name="question_<?php echo $question->id; ?>"
                                accept="<?php echo implode(',', array_map(fn($t) => '.'.$t, $types)); ?>"
                                <?php echo $question->is_required ? 'required' : ''; ?>
                                onchange="showUploadFile(this)">
                        </label>
                        <div id="fname-<?php echo $question->id; ?>" class="qfill-upload-selected d-none">
                            <i class="fa-solid fa-file-circle-check text-success"></i>
                            <span></span>
                            <button type="button" onclick="clearUpload('<?php echo $question->id; ?>')" class="btn-clear-upload">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="qfill-error d-none" id="err-<?php echo $question->id; ?>">
                        <i class="fa-solid fa-triangle-exclamation"></i> <span></span>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="form-fill-footer">
                    <div class="form-fill-footer-info">
                        <i class="fa-solid fa-lock text-success"></i>
                        As suas respostas são guardadas de forma segura.
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg px-5"
                        <?php echo !empty($data['admin_view']) ? 'disabled' : ''; ?>>
                        <i class="fa-solid fa-paper-plane me-2"></i>Submeter
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function showUploadFile(input) {
    const id  = input.id.replace('question_', '');
    const box = document.getElementById('fname-' + id);
    if (!box) return;
    if (input.files && input.files[0]) {
        const f = input.files[0];
        box.querySelector('span').textContent = f.name + ' (' + (f.size/1024).toFixed(0) + ' KB)';
        box.classList.remove('d-none');
        input.closest('.qfill-upload-zone').classList.add('has-file');
    }
}
function clearUpload(id) {
    const input = document.getElementById('question_' + id);
    const box   = document.getElementById('fname-' + id);
    if (input) input.value = '';
    if (box)   { box.classList.add('d-none'); }
    const zone = input?.closest('.qfill-upload-zone');
    if (zone) zone.classList.remove('has-file');
}
</script>
<script src="<?php echo URLROOT; ?>/js/form-validation.js"></script>
<?php require_once APPROOT . '/app/views/layout/footer.php'; ?>
