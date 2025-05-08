
document.addEventListener('DOMContentLoaded', function() {
    // Get the saved theme preference or default to 'light'
    const savedTheme = localStorage.getItem('theme') || 'light';
    
    // Apply the saved theme
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    // Create the toggle button in the navbar
    createThemeToggle(savedTheme);
    
    // Add transition class after initial load (to prevent transition flash)
    setTimeout(() => {
      document.body.classList.add('theme-transition');
    }, 100);
  });
  
  // Create the theme toggle button
  function createThemeToggle(currentTheme) {
    const headerNav = document.querySelector('.navbar-nav');
    if (!headerNav) return;
    
    // Create the toggle button
    const toggleBtn = document.createElement('li');
    toggleBtn.className = 'nav-item me-2';
    toggleBtn.innerHTML = `
      <button id="themeToggle" class="btn btn-icon btn-outline-primary" title="Toggle Dark Mode">
        <i class="bx ${currentTheme === 'dark' ? 'bx-sun' : 'bx-moon'}"></i>
      </button>
    `;
    
    // Insert at the beginning of the navbar
    headerNav.prepend(toggleBtn);
    
    // Add click event listener
    document.getElementById('themeToggle').addEventListener('click', toggleTheme);
  }
  
  // Toggle between light and dark themes
  function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    
    // Update HTML attribute
    document.documentElement.setAttribute('data-theme', newTheme);
    
    // Save preference to localStorage
    localStorage.setItem('theme', newTheme);
    
    // Update button icon
    const icon = document.querySelector('#themeToggle i');
    if (icon) {
      icon.className = `bx ${newTheme === 'dark' ? 'bx-sun' : 'bx-moon'}`;
    }
  }