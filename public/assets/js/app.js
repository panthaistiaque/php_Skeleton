/* Skeleton App: global frontend behaviour */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        // Auto-dismiss alerts after a few seconds.
        document.querySelectorAll('.alert[data-bs-dismiss]').forEach(function (alertEl) {
            setTimeout(function () {
                var instance = bootstrap.Alert.getOrCreateInstance(alertEl);
                if (instance) {
                    instance.close();
                }
            }, 6000);
        });

        // Confirm deletes are handled inline (onsubmit). Nothing extra needed.

        // Navbar profile/sidebar auto close on mobile nav click.
        document.querySelectorAll('#appSidebar a').forEach(function (link) {
            link.addEventListener('click', function () {
                var navbarCollapse = document.querySelector('.navbar-collapse.show');
                if (navbarCollapse) {
                    bootstrap.Collapse.getOrCreateInstance(navbarCollapse).hide();
                }
            });
        });
    });
})();