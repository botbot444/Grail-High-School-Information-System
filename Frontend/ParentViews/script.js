document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebarToggle');
    const navItems = document.querySelectorAll('.nav-item[data-view]');
    const views = document.querySelectorAll('.view');
    const profileNavButtons = document.querySelectorAll('[data-navigate="profile"]');
    const profileTabs = document.querySelectorAll('.profile-tab');

    // Toggle sidebar
    toggle.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
    });

    // Handle sidebar navigation
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const viewId = this.getAttribute('data-view');
            showView(viewId);
        });
    });

    // Handle profile navigation from View Profile buttons
    profileNavButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            showView('profile');
        });
    });

    // Handle profile tabs
    profileTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            profileTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Handle toggle pills (Daily/Monthly in attendance)
    document.querySelectorAll('.toggle-pill').forEach(pillGroup => {
        pillGroup.querySelectorAll('.pill').forEach(pill => {
            pill.addEventListener('click', function() {
                pillGroup.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
                this.classList.add('active');
            });
        });
    });

    // Handle announcements filter buttons
    document.querySelectorAll('.announcements-filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.announcements-filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Handle announcements pagination
    document.querySelectorAll('.announcements-page-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!this.querySelector('svg')) {
                document.querySelectorAll('.announcements-page-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });

    // Handle privacy navigation from settings
    document.querySelectorAll('[data-navigate="privacy"]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            showView('privacy');
        });
    });

    // Show view helper function
    function showView(viewId) {
        // Update active nav item
        navItems.forEach(nav => {
            nav.classList.remove('active');
            if (nav.getAttribute('data-view') === viewId || 
                (viewId === 'profile' && nav.getAttribute('data-view') === 'children')) {
                nav.classList.add('active');
            }
        });

        // Show corresponding view
        views.forEach(view => view.classList.remove('active'));
        const targetView = document.getElementById('view-' + viewId);
        if (targetView) {
            targetView.classList.add('active');
        }

        // Close sidebar on mobile after navigation
        if (window.innerWidth <= 1024) {
            sidebar.classList.add('collapsed');
        }
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 1024) {
            if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.add('collapsed');
            }
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 1024) {
            sidebar.classList.remove('collapsed');
        }
    });
});
