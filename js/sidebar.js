document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.querySelector(".sidebar");
  const toggleBtn = document.querySelector(".sidebar-toggle");
  const themeToggle = document.querySelector(".theme-toggle");
  const themeIcon = document.querySelector(".theme-icon");

  // Toggle Sidebar Collapse and Icon
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener("click", () => {
      sidebar.classList.toggle("collapsed");
      toggleBtn.querySelector("span").textContent =
        sidebar.classList.contains("collapsed") ? "chevron_right" : "chevron_left";
    });
  }

  // Toggle Dark/Light Theme
  if (themeToggle && themeIcon) {
    themeToggle.addEventListener("click", () => {
      document.body.classList.toggle("dark-theme");
      const isDark = document.body.classList.contains("dark-theme");
      themeIcon.textContent = isDark ? "light_mode" : "dark_mode";
    });
  }
});
