/**
 * Sidebar toggle - SenaiGMP
 */
(function () {
    const OPEN_CLASS = 'senai-sidebar-open';
    const STORAGE_KEY = 'senai-sidebar-open';
    const LOGO_TOGGLE_SELECTOR = '#senai-sidebar-logo-toggle';
    const DESKTOP_QUERY = '(min-width: 1024px)';

    const isDesktop = () => window.matchMedia(DESKTOP_QUERY).matches;

    function getSidebarStore() {
        if (!window.Alpine || typeof window.Alpine.store !== 'function') {
            return null;
        }

        return window.Alpine.store('sidebar');
    }

    function readStoredState() {
        try {
            return window.localStorage.getItem(STORAGE_KEY) === '1';
        } catch (error) {
            return false;
        }
    }

    function writeStoredState(isOpen) {
        try {
            window.localStorage.setItem(STORAGE_KEY, isOpen ? '1' : '0');
        } catch (error) {
            // localStorage can be unavailable in private or restricted contexts.
        }
    }

    function updateLogoState(isOpen) {
        document.querySelectorAll(LOGO_TOGGLE_SELECTOR).forEach((button) => {
            button.setAttribute('aria-expanded', String(isOpen));
            button.classList.toggle('is-active', isOpen);
        });
    }

    function setSidebarOpen(isOpen, persist = true) {
        const sidebarStore = getSidebarStore();

        if (sidebarStore) {
            isOpen ? sidebarStore.open() : sidebarStore.close();
        }

        document.body.classList.toggle(OPEN_CLASS, isDesktop() && isOpen);
        updateLogoState(isOpen);

        if (persist) {
            writeStoredState(isOpen);
        }
    }

    function currentSidebarState() {
        if (isDesktop()) {
            return document.body.classList.contains(OPEN_CLASS);
        }

        return Boolean(getSidebarStore()?.isOpen);
    }

    function applyStoredState() {
        setSidebarOpen(readStoredState(), false);
    }

    document.addEventListener('click', function (event) {
        if (!(event.target instanceof Element)) {
            return;
        }

        const toggle = event.target.closest(LOGO_TOGGLE_SELECTOR);

        if (toggle) {
            event.preventDefault();
            setSidebarOpen(!currentSidebarState());
            return;
        }

        if (event.target.closest('.fi-sidebar-close-overlay')) {
            setSidebarOpen(false);
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !isDesktop() && currentSidebarState()) {
            setSidebarOpen(false);
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        window.requestAnimationFrame(applyStoredState);
    });

    document.addEventListener('livewire:navigated', function () {
        window.requestAnimationFrame(applyStoredState);
    });

    window.addEventListener('resize', applyStoredState);
})();
