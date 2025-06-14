document.addEventListener("DOMContentLoaded", function () {
  var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  const forms = document.querySelectorAll(".needs-validation");
  Array.from(forms).forEach((form) => {
    form.addEventListener(
      "submit",
      (event) => {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add("was-validated");
      },
      false
    );
  });

  const alerts = document.querySelectorAll(".alert");
  alerts.forEach((alert) => {
    setTimeout(() => {
      alert.style.opacity = "0";
      setTimeout(() => {
        alert.remove();
      }, 300);
    }, 5000);
  });

  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute("href"));
      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
        });
      }
    });
  });

  document.querySelectorAll('button[type="submit"]').forEach((button) => {
    button.addEventListener("click", function () {
      if (this.form && this.form.checkValidity()) {
        this.disabled = true;
        this.innerHTML =
          '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Yükleniyor...';
      }
    });
  });

  document
    .querySelectorAll('button[name="leave"], button[name="delete"]')
    .forEach((button) => {
      button.addEventListener("click", function (e) {
        if (
          !confirm("Bu işlemi gerçekleştirmek istediğinizden emin misiniz?")
        ) {
          e.preventDefault();
        }
      });
    });

  const activityDateInput = document.querySelector(
    'input[name="activity_date"]'
  );
  if (activityDateInput) {
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    activityDateInput.min = now.toISOString().slice(0, 16);
  }
  document.querySelectorAll("textarea[maxlength]").forEach((textarea) => {
    const maxLength = textarea.getAttribute("maxlength");
    const counter = document.createElement("small");
    counter.className = "text-muted float-end";
    textarea.parentNode.appendChild(counter);

    textarea.addEventListener("input", function () {
      const remaining = maxLength - this.value.length;
      counter.textContent = `${remaining} karakter kaldı`;
    });
  });
});
