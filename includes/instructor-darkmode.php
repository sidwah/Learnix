    <!-- Right Sidebar -->
    <div class="end-bar">

        <div class="rightbar-title">
            <a href="javascript:void(0);" class="end-bar-toggle float-end">
                <i class="dripicons-cross noti-icon"></i>
            </a>
            <h5 class="m-0">Settings</h5>
        </div>

        <div class="rightbar-content h-100" data-simplebar>

            <div class="p-3">
                <div class="alert alert-warning" role="alert">
                    <strong>Customize </strong> the overall color scheme, sidebar menu, etc.
                </div>

                <!-- Settings -->
                <h5 class="mt-3">Color Scheme</h5>
                <hr class="mt-1" />

                <div class="form-check form-switch mb-1">
                    <input class="form-check-input" type="checkbox" name="color-scheme-mode" value="light" id="light-mode-check" checked>
                    <label class="form-check-label" for="light-mode-check">Light Mode</label>
                </div>

                <div class="form-check form-switch mb-1">
                    <input class="form-check-input" type="checkbox" name="color-scheme-mode" value="dark" id="dark-mode-check">
                    <label class="form-check-label" for="dark-mode-check">Dark Mode</label>
                </div>


                <!-- Width -->
                <h5 class="mt-4">Width</h5>
                <hr class="mt-1" />
                <div class="form-check form-switch mb-1">
                    <input class="form-check-input" type="checkbox" name="width" value="fluid" id="fluid-check" checked>
                    <label class="form-check-label" for="fluid-check">Fluid</label>
                </div>

                <div class="form-check form-switch mb-1">
                    <input class="form-check-input" type="checkbox" name="width" value="boxed" id="boxed-check">
                    <label class="form-check-label" for="boxed-check">Boxed</label>
                </div>


                <!-- Left Sidebar-->
                <h5 class="mt-4">Left Sidebar</h5>
                <hr class="mt-1" />
                <div class="form-check form-switch mb-1">
                    <input class="form-check-input" type="checkbox" name="theme" value="default" id="default-check">
                    <label class="form-check-label" for="default-check">Default</label>
                </div>

                <div class="form-check form-switch mb-1">
                    <input class="form-check-input" type="checkbox" name="theme" value="light" id="light-check" checked>
                    <label class="form-check-label" for="light-check">Light</label>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="theme" value="dark" id="dark-check">
                    <label class="form-check-label" for="dark-check">Dark</label>
                </div>

                <div class="form-check form-switch mb-1">
                    <input class="form-check-input" type="checkbox" name="compact" value="fixed" id="fixed-check" checked>
                    <label class="form-check-label" for="fixed-check">Fixed</label>
                </div>

                <div class="form-check form-switch mb-1">
                    <input class="form-check-input" type="checkbox" name="compact" value="condensed" id="condensed-check">
                    <label class="form-check-label" for="condensed-check">Condensed</label>
                </div>

                <div class="form-check form-switch mb-1">
                    <input class="form-check-input" type="checkbox" name="compact" value="scrollable" id="scrollable-check">
                    <label class="form-check-label" for="scrollable-check">Scrollable</label>
                </div>

                <div class="d-grid mt-4">
                    <button class="btn btn-primary" id="resetBtn">Reset to Default</button>
                </div>
            </div> <!-- end padding-->

        </div>
    </div>

    <div class="rightbar-overlay"></div>
    <!-- /End-bar -->
    <script>
       // Save settings to localStorage when changed
document.addEventListener('DOMContentLoaded', function() {
    // Load saved settings on page load after a slight delay to ensure DOM is ready
    setTimeout(function() {
        loadSavedSettings();
    }, 100);
    
    // Add event listeners for setting changes
    const settingsCheckboxes = document.querySelectorAll('.end-bar input[type=checkbox]');
    settingsCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Allow time for the built-in handlers to run and apply changes
            setTimeout(saveCurrentSettings, 200);
        });
    });
    
    // Override reset button to also clear localStorage
    const resetBtn = document.getElementById('resetBtn');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            // Clear localStorage settings
            clearSettings();
        });
    }
});

// Function to save current settings to localStorage
function saveCurrentSettings() {
    const settings = {
        // Color scheme
        isDarkMode: document.getElementById('dark-mode-check').checked,
        isLightMode: document.getElementById('light-mode-check').checked,
        
        // Width
        isFluid: document.getElementById('fluid-check').checked,
        isBoxed: document.getElementById('boxed-check').checked,
        
        // Left sidebar theme
        isDefaultTheme: document.getElementById('default-check').checked,
        isLightTheme: document.getElementById('light-check').checked,
        isDarkTheme: document.getElementById('dark-check').checked,
        
        // Sidebar layout
        isFixed: document.getElementById('fixed-check').checked,
        isCondensed: document.getElementById('condensed-check').checked,
        isScrollable: document.getElementById('scrollable-check').checked,
        
        // Save data attributes for more reliable restoration
        layoutColor: document.body.getAttribute('data-layout-color'),
        layoutMode: document.body.getAttribute('data-layout-mode'),
        leftbarTheme: document.body.getAttribute('data-leftbar-theme'),
        leftbarCompactMode: document.body.getAttribute('data-leftbar-compact-mode')
    };
    
    // Save settings to localStorage
    localStorage.setItem('hyperAppSettings', JSON.stringify(settings));
    console.log('Settings saved:', settings);
}

// Function to load and apply settings from localStorage
function loadSavedSettings() {
    const savedSettings = localStorage.getItem('hyperAppSettings');
    if (!savedSettings) return;
    
    try {
        const settings = JSON.parse(savedSettings);
        console.log('Loading saved settings:', settings);
        
        // First, apply data attributes directly to ensure state consistency
        if (settings.layoutColor) {
            document.body.setAttribute('data-layout-color', settings.layoutColor);
        }
        if (settings.layoutMode) {
            document.body.setAttribute('data-layout-mode', settings.layoutMode);
        }
        if (settings.leftbarTheme) {
            document.body.setAttribute('data-leftbar-theme', settings.leftbarTheme);
        }
        if (settings.leftbarCompactMode) {
            document.body.setAttribute('data-leftbar-compact-mode', settings.leftbarCompactMode);
        }
        
        // Apply Color Scheme (Dark/Light Mode)
        if (settings.isDarkMode) {
            document.getElementById('dark-mode-check').checked = true;
            document.getElementById('light-mode-check').checked = false;
            $.LayoutThemeApp.activateDarkMode();
        } else if (settings.isLightMode) {
            document.getElementById('light-mode-check').checked = true;
            document.getElementById('dark-mode-check').checked = false;
            $.LayoutThemeApp.deactivateDarkMode();
        }
        
        // Apply Width settings (Fluid/Boxed)
        if (settings.isBoxed) {
            document.getElementById('boxed-check').checked = true;
            document.getElementById('fluid-check').checked = false;
            $.LayoutThemeApp.activateBoxed();
        } else if (settings.isFluid) {
            document.getElementById('fluid-check').checked = true;
            document.getElementById('boxed-check').checked = false;
            $.LayoutThemeApp.activateFluid();
        }
        
        // Apply Left Sidebar Theme
        if (settings.isDefaultTheme) {
            document.getElementById('default-check').checked = true;
            document.getElementById('light-check').checked = false;
            document.getElementById('dark-check').checked = false;
            $.LayoutThemeApp.activateDefaultSidebarTheme();
        } else if (settings.isLightTheme) {
            document.getElementById('default-check').checked = false;
            document.getElementById('light-check').checked = true;
            document.getElementById('dark-check').checked = false;
            $.LayoutThemeApp.activateLightSidebarTheme();
        } else if (settings.isDarkTheme) {
            document.getElementById('default-check').checked = false;
            document.getElementById('light-check').checked = false;
            document.getElementById('dark-check').checked = true;
            $.LayoutThemeApp.activateDarkSidebarTheme();
        }
        
        // Apply Sidebar Layout - ensure consistent state
        // First, deactivate all sidebar modes to avoid conflicts
        $.LayoutThemeApp.deactivateCondensedSidebar();
        $.LayoutThemeApp.deactivateScrollableSidebar();
        
        // Then activate the appropriate one
        if (settings.isCondensed) {
            document.getElementById('fixed-check').checked = false;
            document.getElementById('condensed-check').checked = true;
            document.getElementById('scrollable-check').checked = false;
            $.LayoutThemeApp.activateCondensedSidebar();
        } else if (settings.isScrollable) {
            document.getElementById('fixed-check').checked = false;
            document.getElementById('condensed-check').checked = false;
            document.getElementById('scrollable-check').checked = true;
            $.LayoutThemeApp.activateScrollableSidebar();
        } else {
            // Default to fixed
            document.getElementById('fixed-check').checked = true;
            document.getElementById('condensed-check').checked = false;
            document.getElementById('scrollable-check').checked = false;
        }
    } catch (e) {
        console.error('Error loading saved settings:', e);
        clearSettings();
    }
}

// Function to clear all saved settings
function clearSettings() {
    localStorage.removeItem('hyperAppSettings');
    console.log('Settings cleared');
}
    </script>

