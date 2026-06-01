/**
 * Sidebar toggle - SenaiGMP
 */
(function () {
    const OPEN_CLASS = 'senai-sidebar-open';
    const LOGO_TOGGLE_SELECTOR = '#senai-sidebar-logo-toggle';
    const DESKTOP_QUERY = '(min-width: 1024px)';

    const isDesktop = () => window.matchMedia(DESKTOP_QUERY).matches;

    function getSidebarStore() {
        if (!window.Alpine || typeof window.Alpine.store !== 'function') {
            return null;
        }

        return window.Alpine.store('sidebar');
    }

    function updateLogoState(isOpen) {
        document.querySelectorAll(LOGO_TOGGLE_SELECTOR).forEach((button) => {
            button.setAttribute('aria-expanded', String(isOpen));
            button.classList.toggle('is-active', isOpen);
        });
    }

    function setSidebarOpen(isOpen) {
        const sidebarStore = getSidebarStore();

        if (sidebarStore) {
            isOpen ? sidebarStore.open() : sidebarStore.close();
        }

        document.body.classList.toggle(OPEN_CLASS, isDesktop() && isOpen);
        updateLogoState(isOpen);
    }

    function currentSidebarState() {
        if (isDesktop()) {
            return document.body.classList.contains(OPEN_CLASS);
        }

        return Boolean(getSidebarStore()?.isOpen);
    }

    function syncAfterNativeChanges() {
        const isOpen = currentSidebarState();

        document.body.classList.toggle(OPEN_CLASS, isDesktop() && isOpen);
        updateLogoState(isOpen);
    }

    document.addEventListener('click', function (event) {
        if (!(event.target instanceof Element)) {
            return;
        }

        const toggle = event.target.closest(LOGO_TOGGLE_SELECTOR);

        if (!toggle) {
            return;
        }

        event.preventDefault();
        setSidebarOpen(!currentSidebarState());
    });

    document.addEventListener('DOMContentLoaded', function () {
        setSidebarOpen(false);
    });

    document.addEventListener('livewire:navigated', function () {
        window.requestAnimationFrame(syncAfterNativeChanges);
    });

    window.addEventListener('resize', syncAfterNativeChanges);
})();
