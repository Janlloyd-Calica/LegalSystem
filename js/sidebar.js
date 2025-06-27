document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.querySelector(".sidebar");
  const toggleBtn = document.querySelector(".sidebar-toggle");
  const themeToggle = document.querySelector(".theme-toggle");
  const themeIcon = document.querySelector(".theme-icon");

  console.log("Theme toggle found:", themeToggle);
  console.log("Theme icon found:", themeIcon);

  // Toggle Dark/Light Theme
  if (themeToggle && themeIcon) {
    themeToggle.addEventListener("click", () => {
      document.body.classList.toggle("dark-theme");
      const isDark = document.body.classList.contains("dark-theme");
      console.log("Dark theme active:", isDark);
      themeIcon.textContent = isDark ? "light_mode" : "dark_mode";
    });
  } else {
    console.log("Theme toggle or icon not found!");
  }
});