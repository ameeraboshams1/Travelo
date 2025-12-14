(function () {
  const $ = (s) => document.querySelector(s);

  // ===== Toast =====
  const toast = $("#toast");
  function showToast(msg, ok = true) {
    if (!toast) return;
    toast.textContent = msg;
    toast.style.background = ok ? "#0f172a" : "#b91c1c";
    toast.classList.add("show");
    setTimeout(() => toast.classList.remove("show"), 2200);
  }

  // ===== Modals open/close =====
  function openModal(id) {
    const m = document.getElementById(id);
    if (!m) return;
    m.classList.add("show");
    m.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
  }

  function closeModal(id) {
    const m = document.getElementById(id);
    if (!m) return;
    m.classList.remove("show");
    m.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
  }

  document.addEventListener("click", (e) => {
    const openBtn = e.target.closest("[data-open]");
    if (openBtn) openModal(openBtn.getAttribute("data-open"));

    const closeBtn = e.target.closest("[data-close]");
    if (closeBtn) closeModal(closeBtn.getAttribute("data-close"));
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      ["modalEdit", "modalPass"].forEach(closeModal);
    }
  });

  // ===== Update DOM after profile update =====
  function refreshUI(u) {
    if (!u) return;

    const first = u.first_name || "";
    const last = u.last_name || "";
    const username = u.username || "";
    const email = u.email || "";
    const birth = u.birth_date || "";

    const fullName = (first + " " + last).trim() || username || "Traveler";
    const letter = (first || username || "U").trim().charAt(0).toUpperCase();

    $("#displayName").textContent = fullName;
    $("#displayEmail").textContent = email;

    $("#vFirst").textContent = first;
    $("#vLast").textContent = last;
    $("#vUser").textContent = username;
    $("#vEmail").textContent = email;
    $("#vBirth").textContent = birth;

    const letterNode = $("#avatarLetter");
    if (letterNode) letterNode.textContent = letter;
  }

  // ===== Edit profile submit =====
  const formEdit = $("#formEdit");
  if (formEdit) {
    formEdit.addEventListener("submit", async (e) => {
      e.preventDefault();

      const payload = {
        first_name: $("#inFirst")?.value.trim() || "",
        last_name: $("#inLast")?.value.trim() || "",
        username: $("#inUser")?.value.trim() || "",
        email: $("#inEmail")?.value.trim() || "",
        birth_date: $("#inBirth")?.value || "",
      };

      try {
        const res = await fetch("./API/profile-update.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload),
        });

        const data = await res.json().catch(() => null);

        if (!res.ok || !data?.success) {
          showToast(data?.message || "Update failed", false);
          return;
        }

        refreshUI(data.user);
        closeModal("modalEdit");
        showToast("Profile updated ✅", true);
      } catch {
        showToast("Network error", false);
      }
    });
  }

  // ===== Password submit =====
  const formPass = $("#formPass");
  if (formPass) {
    formPass.addEventListener("submit", async (e) => {
      e.preventDefault();

      const payload = {
        old: $("#oldPass")?.value || "",
        new: $("#newPass")?.value || "",
        confirm: $("#newPass2")?.value || "",
      };

      try {
        const res = await fetch("./API/profile-password.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload),
        });

        const data = await res.json().catch(() => null);

        if (!res.ok || !data?.success) {
          showToast(data?.message || "Password update failed", false);
          return;
        }

        $("#oldPass").value = "";
        $("#newPass").value = "";
        $("#newPass2").value = "";

        closeModal("modalPass");
        showToast("Password updated ✅", true);
      } catch {
        showToast("Network error", false);
      }
    });
  }

  // ===== Deactivate =====
  const btnDeactivate = $("#btnDeactivate");
  if (btnDeactivate) {
    btnDeactivate.addEventListener("click", async () => {
      const ok = confirm("Are you sure you want to deactivate your account?");
      if (!ok) return;

      try {
        const res = await fetch("./API/profile-deactivate.php", { method: "POST" });
        const data = await res.json().catch(() => null);

        if (!res.ok || !data?.success) {
          showToast(data?.message || "Failed", false);
          return;
        }

        showToast("Account deactivated", true);
        setTimeout(() => {
          window.location.href = data.redirect || "login.html";
        }, 700);
      } catch {
        showToast("Network error", false);
      }
    });
  }
})();
