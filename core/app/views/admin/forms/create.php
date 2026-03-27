<?php require_once APPROOT . '/app/views/layout/header.php'; ?>
<div class="container-xl">

    <div class="page-header">
        <h1><i class="fa-solid fa-plus-circle"></i> Criar Formulário</h1>
        <a href="<?php echo URLROOT; ?>/admin/forms" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Voltar
        </a>
    </div>

    <form action="<?php echo URLROOT; ?>/admin/forms/store" method="POST"
          id="formBuilder" enctype="multipart/form-data">
        <div class="row g-4">

            <!-- Coluna principal -->
            <div class="col-lg-8">
                <div class="df-card mb-4">
                    <div class="df-card-header">
                        <i class="fa-solid fa-circle-info"></i>
                        <h5>Informações do Formulário</h5>
                    </div>
                    <div class="df-card-body">
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fa-solid fa-heading"></i> Título
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="title"
                                   class="form-control <?php echo !empty($data['title_err']) ? 'is-invalid' : ''; ?>"
                                   value="<?php echo htmlspecialchars($data['title'] ?? ''); ?>"
                                   placeholder="Ex: Inquérito de Satisfação 2025">
                            <?php if (!empty($data['title_err'])): ?>
                                <div class="invalid-feedback">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    <?php echo $data['title_err']; ?>
                                </div>
                            <?php endif; ?>
                            <div class="form-text">Escolha um título claro que identifique o propósito do formulário.</div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label">
                                <i class="fa-solid fa-align-left"></i> Descrição
                            </label>
                            <textarea name="description" class="form-control" rows="3"
                                placeholder="Explique brevemente o objetivo deste formulário..."><?php
                                    echo htmlspecialchars($data['description'] ?? '');
                                ?></textarea>
                            <div class="form-text">Uma boa descrição ajuda os utilizadores a perceber o que é pedido.</div>
                        </div>
                    </div>
                </div>

                <!-- Perguntas -->
                <div class="df-card">
                    <div class="df-card-header">
                        <i class="fa-solid fa-list-check"></i>
                        <h5>Perguntas</h5>
                        <span class="badge bg-secondary ms-auto" id="questionCount">0 pergunta(s)</span>
                    </div>
                    <div class="df-card-body">
                        <div id="questionsContainer"></div>
                        <button type="button" class="btn btn-outline-success w-100 mt-2" id="addQuestionBtn">
                            <i class="fa-solid fa-plus-circle"></i> Adicionar Pergunta
                        </button>
                    </div>
                </div>
            </div>

            <!-- Coluna lateral -->
            <div class="col-lg-4">

                <!-- Imagem de capa -->
                <div class="df-card mb-4">
                    <div class="df-card-header">
                        <i class="fa-solid fa-image"></i>
                        <h5>Imagem de Capa</h5>
                    </div>
                    <div class="df-card-body">
                        <p class="form-text mb-3">
                            <i class="fa-solid fa-circle-info me-1 text-success"></i>
                            Carregue o logótipo ou imagem representativa do formulário.<br>
                            <strong>Formato:</strong> JPG, PNG, WEBP · <strong>Tamanho máx.:</strong> 2MB · <strong>Dimensão:</strong> 800×400px recomendado
                        </p>

                        <!-- Preview da imagem -->
                        <div class="cover-preview-wrapper mb-3" id="coverPreviewWrapper">
                            <div class="cover-preview-empty" id="coverPreviewEmpty">
                                <i class="fa-solid fa-image"></i>
                                <span>Sem imagem</span>
                            </div>
                            <img id="coverPreviewImg" src="" alt="Pré-visualização"
                                 class="cover-preview-img d-none">
                        </div>

                        <!-- Input de upload -->
                        <label class="cover-upload-btn" for="cover_image">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span id="coverBtnText">Selecionar Imagem</span>
                            <input type="file" id="cover_image" name="cover_image"
                                   accept="image/jpeg,image/png,image/gif,image/webp"
                                   class="d-none" onchange="previewCover(this)">
                        </label>
                        <div class="form-text mt-2" id="coverFileName"></div>
                    </div>
                </div>

                <!-- Configurações -->
                <div class="df-card mb-4">
                    <div class="df-card-header">
                        <i class="fa-solid fa-sliders"></i>
                        <h5>Configurações</h5>
                    </div>
                    <div class="df-card-body">
                        <div class="mb-0">
                            <label class="form-label">
                                <i class="fa-solid fa-toggle-on"></i> Status
                            </label>
                            <select name="status" class="form-select">
                                <option value="draft"     <?php echo ($data['status']??'draft')=='draft'     ? 'selected':''?>>
                                    📝 Rascunho — visível só para admin
                                </option>
                                <option value="published" <?php echo ($data['status']??'')=='published' ? 'selected':''?>>
                                    ✅ Publicado — aceita respostas
                                </option>
                            </select>
                            <div class="form-text">Pode alterar o status a qualquer momento.</div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Criar Formulário
                    </button>
                    <a href="<?php echo URLROOT; ?>/admin/forms" class="btn btn-secondary">
                        <i class="fa-solid fa-xmark"></i> Cancelar
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function previewCover(input) {
    const preview = document.getElementById('coverPreviewImg');
    const empty   = document.getElementById('coverPreviewEmpty');
    const nameEl  = document.getElementById('coverFileName');
    const btnText = document.getElementById('coverBtnText');

    if (input.files && input.files[0]) {
        const file = input.files[0];

        // Validar tamanho no cliente (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('O ficheiro é demasiado grande. Tamanho máximo: 2MB.');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
            empty.classList.add('d-none');
        };
        reader.readAsDataURL(file);

        nameEl.innerHTML  = `<i class="fa-solid fa-check-circle text-success me-1"></i>${file.name} (${(file.size/1024).toFixed(0)} KB)`;
        btnText.textContent = 'Alterar Imagem';
    }
}
</script>
<script src="<?php echo URLROOT; ?>/js/form-builder.js"></script>
<?php require_once APPROOT . '/app/views/layout/footer.php'; ?>
