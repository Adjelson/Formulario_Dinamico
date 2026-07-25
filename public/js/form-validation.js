document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("responseForm");
  if (!form) return;

  const alertBox = document.getElementById("formAlert");
  const submitBtn = document.getElementById("submitBtn");

  // Eventos em tempo real
  form.querySelectorAll("input, textarea").forEach((field) => {
    field.addEventListener("blur", () => validateField(field));
    field.addEventListener("input", () => {
      if (field.classList.contains("is-invalid")) validateField(field);
    });
  });

  form.addEventListener("submit", function (e) {
    let valid = true;

    // Campos normais
    form.querySelectorAll("[required]").forEach((field) => {
      if (!validateField(field)) valid = false;
    });

    // RADIO obrigatório
    const radioGroups = {};
    form.querySelectorAll('input[type="radio"][required]').forEach((r) => {
      radioGroups[r.name] = true;
    });

    Object.keys(radioGroups).forEach((name) => {
      const checked = form.querySelector(`input[name="${name}"]:checked`);
      const block = form
        .querySelector(`input[name="${name}"]`)
        ?.closest(".qfill-block");

      if (!checked) {
        valid = false;
        block?.querySelectorAll('input[type="radio"]').forEach((input) => input.setAttribute("aria-invalid", "true"));
        showError(block, "Selecione uma opção.");
      } else {
        block?.querySelectorAll('input[type="radio"]').forEach((input) => input.removeAttribute("aria-invalid"));
        clearError(block);
      }
    });

    // CHECKBOX obrigatório (mínimo 1)
    const checkboxGroups = {};
    form.querySelectorAll('input[type="checkbox"]').forEach((c) => {
      const block = c.closest(".qfill-block");
      if (block && block.querySelector(".qfill-required")) {
        checkboxGroups[c.name] = true;
      }
    });

    Object.keys(checkboxGroups).forEach((name) => {
      const checked = form.querySelectorAll(`input[name="${name}"]:checked`);
      const block = form
        .querySelector(`input[name="${name}"]`)
        ?.closest(".qfill-block");

      if (checked.length === 0) {
        valid = false;
        block?.querySelectorAll('input[type="checkbox"]').forEach((input) => input.setAttribute("aria-invalid", "true"));
        showError(block, "Selecione pelo menos uma opção.");
      } else {
        block?.querySelectorAll('input[type="checkbox"]').forEach((input) => input.removeAttribute("aria-invalid"));
        clearError(block);
      }
    });

    if (!valid) {
      e.preventDefault();

      if (alertBox) alertBox.classList.remove("d-none");

      const firstErr = form.querySelector(
        ".is-invalid, .qfill-block.has-error",
      );
      if (firstErr) {
        firstErr.scrollIntoView({ behavior: window.matchMedia("(prefers-reduced-motion: reduce)").matches ? "auto" : "smooth", block: "center" });
        const focusTarget = firstErr.matches("input,textarea,select,button") ? firstErr : firstErr.querySelector("input,textarea,select,button");
        if (focusTarget) focusTarget.focus({preventScroll:true});
      }
    } else {
      if (alertBox) alertBox.classList.add("d-none");

      // bloquear botão (anti double submit)
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML =
          '<i class="fa-solid fa-spinner fa-spin me-2"></i> A enviar...';
      }
    }
  });
});

// =========================
// VALIDAR CAMPO
// =========================
function validateField(field) {
  const block = field.closest(".qfill-block");
  if (!field.hasAttribute("required")) return true;

  // FILE
  if (field.type === "file") {
    if (field.files.length === 0) {
      field.classList.add("is-invalid");
      field.setAttribute("aria-invalid", "true");
      showError(block, "Selecione um ficheiro.");
      return false;
    }

    if (field.files[0].size > 5 * 1024 * 1024) {
      field.classList.add("is-invalid");
      field.setAttribute("aria-invalid", "true");
      showError(block, "O ficheiro não pode exceder 5 MB.");
      return false;
    }
  }

  // TEXT / NUMBER / DATE
  if (!field.value.trim()) {
    field.classList.add("is-invalid");
    field.setAttribute("aria-invalid", "true");
    showError(block, "Campo obrigatório.");
    return false;
  }

  field.classList.remove("is-invalid");
  field.removeAttribute("aria-invalid");
  field.classList.add("is-valid");
  clearError(block);

  return true;
}

// =========================
// ERROS UI
// =========================
function showError(block, msg) {
  if (!block) return;

  block.classList.add("has-error");

  let err = block.querySelector(".qfill-error");
  if (err) {
    err.classList.remove("d-none");
    err.querySelector("span").textContent = msg;
  }
}

function clearError(block) {
  if (!block) return;

  block.classList.remove("has-error");

  let err = block.querySelector(".qfill-error");
  if (err) err.classList.add("d-none");
}
