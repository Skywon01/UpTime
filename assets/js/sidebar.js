const initSidebar = () => {
    const sidebar = document.getElementById('sidebar');
    const btns = [
        document.getElementById('sidebarCollapseDesktop'),
        document.getElementById('sidebarCollapseMobile'),
        document.getElementById('sidebarCloseMobile') // On ajoute la croix ici
    ];

    if (!sidebar) return;

    if (localStorage.getItem('sidebar-state') === 'collapsed') {
        sidebar.classList.add('active');
    }

    const toggleSidebar = (e) => {
        if(e) e.preventDefault();
        sidebar.classList.toggle('active');
        const state = sidebar.classList.contains('active') ? 'collapsed' : 'expanded';
        localStorage.setItem('sidebar-state', state);
    };

    // On boucle sur tous nos boutons pour leur mettre le même écouteur
    btns.forEach(btn => {
        if (btn) btn.addEventListener('click', toggleSidebar);
    });


    // Dans initSidebar, après la boucle des boutons
    const navLinks = sidebar.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('active');
            }
        });
    });
};



if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSidebar);
} else {
    initSidebar();
}
