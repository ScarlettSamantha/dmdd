// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', () => {
    // Create the dark mode toggle button element
    const toggleButton = document.createElement('button');
    toggleButton.id = 'darkModeToggle';
    toggleButton.className = 'dark-mode-toggle fixed bottom-4 right-4 w-10 h-10 flex items-center justify-center rounded-full shadow-lg hover:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-dark-purple transition-colors duration-200';
    toggleButton.textContent = '🌙'; // Default emoji

    // Append the button as the last element of the body
    document.body.appendChild(toggleButton);

    const root = document.documentElement;

    // Update the button's emoji based on theme
    function updateEmoji(theme) {
        toggleButton.textContent = theme === 'dark' ? '☀️' : '🌙';
    }

    // Add the click event listener to toggle the theme
    toggleButton.addEventListener('click', () => {
        const currentTheme = root.getAttribute('data-theme') || 'light';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        root.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateEmoji(newTheme);
    });

    // Load the saved theme
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        root.setAttribute('data-theme', savedTheme);
        updateEmoji(savedTheme);
    }
});
