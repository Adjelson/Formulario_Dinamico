// ============================================================
// FORM BUILDER — Dynamic Forms
// ============================================================

let questionCount = 0;
const existingQuestions = (typeof window.existingQuestions !== 'undefined') ? window.existingQuestions : [];

function updateQuestionCounter() {
    const count = document.querySelectorAll('.question-card').length;
    const el = document.getElementById('questionCount');
    if (el) el.textContent = count + (count === 1 ? ' pergunta' : ' perguntas');
}

document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('addQuestionBtn');
    if (btn) btn.addEventListener('click', () => addQuestion());

    if (existingQuestions.length > 0) {
        existingQuestions.forEach((q, i) => addQuestion(q, i));
        // IMPORTANTE: após carregar existentes, questionCount deve começar após os índices usados
        questionCount = existingQuestions.length;
    }
});

const typeLabels = {
    short_text: { icon: 'fa-font',           label: 'Texto Curto',        hint: 'Resposta curta, até uma linha de texto.', color: '#3b82f6' },
    long_text:  { icon: 'fa-align-left',     label: 'Texto Longo',        hint: 'Resposta detalhada com múltiplas linhas.', color: '#8b5cf6' },
    numeric:    { icon: 'fa-hashtag',        label: 'Numérico',           hint: 'Apenas valores numéricos (ex: 25, 3.14).', color: '#f59e0b' },
    date:       { icon: 'fa-calendar-days',  label: 'Data',               hint: 'O utilizador seleciona uma data no calendário.', color: '#ec4899' },
    checkbox:   { icon: 'fa-check-square',   label: 'Múltipla Escolha',   hint: 'Permite selecionar várias opções em simultâneo.', color: '#10b981' },
    radio:      { icon: 'fa-circle-dot',     label: 'Escolha Única',      hint: 'O utilizador seleciona apenas uma opção.', color: '#06b6d4' },
    upload:     { icon: 'fa-cloud-arrow-up', label: 'Upload de Ficheiro', hint: 'O utilizador envia um ficheiro (PDF, PNG, JPEG).', color: '#f97316' },
};

function addQuestion(existingQ = null, index = null) {
    const container = document.getElementById('questionsContainer');
    // Usar índice fornecido (ao editar) ou incrementar questionCount (ao adicionar novo)
    let qId;
    if (index !== null) {
        qId = index;
    } else {
        // Garantir que não colide com nenhum ID existente
        qId = questionCount;
        while (document.getElementById('question-' + qId)) qId++;
        questionCount = qId + 1;
    }

    const label    = existingQ ? existingQ.label       : '';
    const type     = existingQ ? existingQ.type        : 'short_text';
    const required = existingQ ? existingQ.is_required : 0;
    let   config   = {};
    if (existingQ && existingQ.config) {
        try { config = JSON.parse(existingQ.config); } catch(e) { config = {}; }
    }
    const qNum  = container.querySelectorAll('.question-card').length + 1;
    const tInfo = typeLabels[type] || typeLabels.short_text;

    const div = document.createElement('div');
    div.className = 'question-card';
    div.id = 'question-' + qId;

    const optionsHtml = Object.entries(typeLabels).map(([val, info]) =>
        `<option value="${val}" ${type === val ? 'selected' : ''}>${info.label}</option>`
    ).join('');

    const uploadCfgHtml = ['pdf','png','jpeg'].map(t => `
        <label class="type-chip">
            <input type="checkbox" name="questions[${qId}][config][allowed_types][]" value="${t}"
                ${config.allowed_types && config.allowed_types.includes(t) ? 'checked' : ''}>
            <i class="fa-solid fa-file-${t==='pdf'?'pdf':'image'}"></i> ${t.toUpperCase()}
        </label>`).join('');

    // Escapar label para uso seguro em HTML
    const safeLabel = label.replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

    div.innerHTML = `
        <div class="qcard-header" style="--q-color:${tInfo.color}">
            <div class="qcard-num">${qNum}</div>
            <div class="qcard-type-badge">
                <i class="fa-solid ${tInfo.icon}"></i>
                <span id="typeBadgeLabel-${qId}">${tInfo.label}</span>
            </div>
            <div class="qcard-actions ms-auto">
                <button type="button" class="btn-qmove" onclick="moveQuestion(${qId},'up')" title="Subir">
                    <i class="fa-solid fa-chevron-up"></i>
                </button>
                <button type="button" class="btn-qmove" onclick="moveQuestion(${qId},'down')" title="Descer">
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <button type="button" class="btn-qdel" onclick="removeQuestion(${qId})" title="Eliminar pergunta">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
        </div>
        <div class="qcard-body">
            <div class="row g-3 align-items-start">
                <div class="col-md-7">
                    <label class="form-label fw-600 mb-1" style="font-size:.82rem;">
                        <i class="fa-solid fa-pencil" style="color:var(--green-600);"></i>
                        Pergunta <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control form-control-sm"
                        name="questions[${qId}][label]"
                        value="${safeLabel}"
                        placeholder="Ex: Qual é a sua data de nascimento?"
                        required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-600 mb-1" style="font-size:.82rem;">
                        <i class="fa-solid fa-list" style="color:var(--green-600);"></i>
                        Tipo
                    </label>
                    <select class="form-select form-select-sm" name="questions[${qId}][type]"
                        id="typeSelect-${qId}"
                        onchange="updateQuestionType(${qId}, this.value)">
                        ${optionsHtml}
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end pb-1">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox"
                            name="questions[${qId}][is_required]"
                            value="1" id="req-${qId}"
                            ${required ? 'checked' : ''}>
                        <label class="form-check-label" for="req-${qId}" style="font-size:.78rem;font-weight:600;">
                            Obrig.
                        </label>
                    </div>
                </div>
            </div>

            <div class="qcard-hint" id="typeHint-${qId}">
                <i class="fa-solid fa-circle-info"></i>
                <span id="typeHintText-${qId}">${tInfo.hint}</span>
            </div>

            <!-- Config: Data -->
            <div class="qcard-config mt-2" id="date-config-${qId}" style="display:${type==='date'?'block':'none'};">
                <label class="form-label fw-600 mb-2" style="font-size:.82rem;">
                    <i class="fa-solid fa-calendar-days" style="color:var(--green-600);"></i>
                    Intervalo de datas (opcional)
                </label>
                <div class="row g-2">
                    <div class="col-6">
                        <label style="font-size:.75rem;color:var(--black-500);">Data mínima</label>
                        <input type="date" class="form-control form-control-sm"
                            name="questions[${qId}][config][date_min]"
                            value="${config.date_min || ''}">
                    </div>
                    <div class="col-6">
                        <label style="font-size:.75rem;color:var(--black-500);">Data máxima</label>
                        <input type="date" class="form-control form-control-sm"
                            name="questions[${qId}][config][date_max]"
                            value="${config.date_max || ''}">
                    </div>
                </div>
            </div>

            <!-- Config: Checkbox / Radio -->
            <div class="qcard-config mt-2" id="options-${qId}" style="display:${(type==='checkbox'||type==='radio')?'block':'none'};">
                <label class="form-label fw-600 mb-1" style="font-size:.82rem;">
                    <i class="fa-solid fa-list-ul" style="color:var(--green-600);"></i>
                    Opções de resposta
                </label>
                <div class="form-text mb-2">Adicione as opções que os utilizadores poderão escolher.</div>
                <div id="options-list-${qId}"></div>
                <button type="button" class="btn btn-sm btn-outline-success mt-1" onclick="addOption(${qId})">
                    <i class="fa-solid fa-plus"></i> Adicionar Opção
                </button>
            </div>

            <!-- Config: Upload -->
            <div class="qcard-config mt-2" id="upload-config-${qId}" style="display:${type==='upload'?'block':'none'};">
                <label class="form-label fw-600 mb-2" style="font-size:.82rem;">
                    <i class="fa-solid fa-file" style="color:var(--green-600);"></i>
                    Formatos de ficheiro aceites
                </label>
                <div class="d-flex gap-2 flex-wrap">${uploadCfgHtml}</div>
            </div>
        </div>`;

    container.appendChild(div);

    if ((type === 'checkbox' || type === 'radio') && config.options && config.options.length) {
        config.options.forEach((opt, i) => addOptionElement(qId, opt, i));
    } else if (type === 'checkbox' || type === 'radio') {
        addOption(qId);
    }

    // Animação entrada
    div.style.opacity = '0';
    div.style.transform = 'translateY(12px)';
    requestAnimationFrame(() => {
        div.style.transition = 'opacity .25s ease, transform .25s ease';
        div.style.opacity = '1';
        div.style.transform = 'translateY(0)';
    });

    updateQuestionCounter();
    renumberCards();
}

function updateQuestionType(qId, type) {
    const tInfo    = typeLabels[type] || typeLabels.short_text;
    const hintText = document.getElementById('typeHintText-' + qId);
    const badgeLbl = document.getElementById('typeBadgeLabel-' + qId);
    const header   = document.querySelector('#question-' + qId + ' .qcard-header');
    const badgeIcon= document.querySelector('#question-' + qId + ' .qcard-type-badge i');

    if (hintText)   hintText.textContent  = tInfo.hint;
    if (badgeLbl)   badgeLbl.textContent  = tInfo.label;
    if (header)     header.style.setProperty('--q-color', tInfo.color);
    if (badgeIcon)  badgeIcon.className   = 'fa-solid ' + tInfo.icon;

    document.getElementById('options-' + qId).style.display      = (type==='checkbox'||type==='radio') ? 'block' : 'none';
    document.getElementById('upload-config-' + qId).style.display = type==='upload' ? 'block' : 'none';
    document.getElementById('date-config-' + qId).style.display   = type==='date'   ? 'block' : 'none';

    if ((type==='checkbox'||type==='radio') && document.getElementById('options-list-' + qId).children.length === 0) {
        addOption(qId);
    }
}

function addOption(qId) {
    const list = document.getElementById('options-list-' + qId);
    addOptionElement(qId, '', list.children.length);
}

function addOptionElement(qId, value, idx) {
    const list  = document.getElementById('options-list-' + qId);
    const div   = document.createElement('div');
    div.className = 'input-group input-group-sm mb-2';
    div.id = 'opt-' + qId + '-' + idx;
    const safeVal = (value||'').replace(/&/g,'&amp;').replace(/"/g,'&quot;');
    div.innerHTML =
        '<span class="input-group-text" style="background:var(--green-50);border-color:var(--green-200);">' +
            '<i class="fa-solid fa-grip-dots-vertical" style="color:var(--green-500);font-size:.7rem;"></i>' +
        '</span>' +
        '<input type="text" class="form-control"' +
            ' name="questions[' + qId + '][config][options][]"' +
            ' value="' + safeVal + '"' +
            ' placeholder="Opção ' + (idx + 1) + '">' +
        '<button type="button" class="btn btn-outline-danger btn-sm"' +
            ' onclick="document.getElementById(\'opt-' + qId + '-' + idx + '\').remove()"' +
            ' title="Remover"><i class="fa-solid fa-xmark"></i></button>';
    list.appendChild(div);
}

function removeQuestion(qId) {
    const el = document.getElementById('question-' + qId);
    if (!el) return;
    el.style.transition = 'opacity .2s ease, transform .2s ease';
    el.style.opacity    = '0';
    el.style.transform  = 'translateX(30px)';
    setTimeout(() => { el.remove(); updateQuestionCounter(); renumberCards(); }, 220);
}

function moveQuestion(qId, dir) {
    const el   = document.getElementById('question-' + qId);
    const cont = document.getElementById('questionsContainer');
    if (!el) return;
    if (dir === 'up'   && el.previousElementSibling) cont.insertBefore(el, el.previousElementSibling);
    if (dir === 'down' && el.nextElementSibling)     cont.insertBefore(el.nextElementSibling, el);
    renumberCards();
}

function renumberCards() {
    document.querySelectorAll('.question-card').forEach((card, i) => {
        const numEl = card.querySelector('.qcard-num');
        if (numEl) numEl.textContent = i + 1;
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formBuilder');
    if (!form) return;
    form.addEventListener('submit', function(e) {
        if (document.querySelectorAll('.question-card').length === 0) {
            e.preventDefault();
            alert('Por favor, adicione pelo menos uma pergunta ao formulário.');
        }
    });
});
