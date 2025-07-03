document.addEventListener("DOMContentLoaded", () => {
  const sidebarToggle = document.getElementById("toggleSidebar");
  const sidebar = document.querySelector(".sidebar");

  // Sidebar toggle
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener("click", () => {
      sidebar.classList.toggle("collapsed");
    });
  }

  // Manual menu navigation (if needed)
  const menuLinks = document.querySelectorAll(".menu-link");

  menuLinks.forEach(link => {
    link.addEventListener("click", (event) => {
      const href = link.getAttribute("href");

      if (href && href !== "#") {
        event.preventDefault();
        window.location.href = href;
      } else {
        event.preventDefault();
        console.warn("No navigation target set for:", link);
      }
    });
  });
});
