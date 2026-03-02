const initSidebar = () => {
    const sidebar = document.getElementById('sidebar');
    const collapseBtn = document.getElementById('sidebarCollapseDesktop');
    const mobileBtn = document.getElementById('sidebarCollapseMobile');
    const closeMobileBtn = document.getElementById('sidebarCloseMobile');

    if (!sidebar) return;

    // Appliquer l'état sauvegardé immédiatement
    const isCollapsed = localStorage.getItem('sidebar-state') === 'collapsed';
    if (isCollapsed) {
        sidebar.classList.add('active');
    }

    const toggleSidebar = (e) => {
        if (e) e.preventDefault();
        sidebar.classList.toggle('active');
        const state = sidebar.classList.contains('active') ? 'collapsed' : 'expanded';
        localStorage.setItem('sidebar-state', state);
    };

    // Attribution des événements
    [collapseBtn, mobileBtn, closeMobileBtn].forEach(btn => {
        if (btn) {
            // On clone le bouton pour supprimer les anciens écouteurs (évite les doublons)
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            newBtn.addEventListener('click', toggleSidebar);
        }
    });
};

// Se lance au premier chargement ET à chaque navigation Turbo
document.addEventListener('turbo:load', initSidebar);
// Fallback si Turbo n'est pas utilisé
document.addEventListener('DOMContentLoaded', initSidebar);
