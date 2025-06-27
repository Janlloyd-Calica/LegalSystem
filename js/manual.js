document.addEventListener("DOMContentLoaded", () => {
  const sidebarToggle = document.getElementById("toggleSidebar");
  const sidebar = document.querySelector(".sidebar");

  // Sidebar toggle functionality
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener("click", () => {
      sidebar.classList.toggle("collapsed");
    });
  }

  // Manual menu link navigation handler
  const menuLinks = document.querySelectorAll(".menu-link");

  menuLinks.forEach(link => {
    link.addEventListener("click", (event) => {
      const href = link.getAttribute("href");

      if (href && href !== "#") {
        // Manually navigate to the page
        event.preventDefault();
        window.location.href = href;
      } else {
        // Optional: Log or handle unlinked items
        event.preventDefault();
        console.warn("No navigation target set for:", link);
      }
    });
  });
});
