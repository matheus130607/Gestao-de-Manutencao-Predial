/**
 * Sidebar hover — SenaiGMP
 */
document.addEventListener('DOMContentLoaded', function () {
    const CHAVE_FIXADA = 'senai-sidebar-fixed';
    const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;

    const sidebar = document.querySelector('.fi-sidebar');
    const trigger = document.getElementById('sidebar-hover-trigger');

    if (!sidebar || !trigger) return;

    // Botão dentro da sidebar (não no body)
    const pinBtn = document.createElement('button');
    pinBtn.id = 'sidebar-pin-btn';
    pinBtn.setAttribute('aria-label', 'Fixar ou soltar menu');
    pinBtn.innerHTML = `
        <span class="pin-icone">📌</span>
        <span class="pin-texto">Fixar menu</span>
    `;
    // Inserir no topo da sidebar, antes do primeiro filho
    sidebar.insertBefore(pinBtn, sidebar.firstChild);

    let fixada = localStorage.getItem(CHAVE_FIXADA) === 'true';
    let hoverAtivo = false;

    function aplicarEstado() {
        if (fixada) {
            sidebar.classList.add('sidebar-fixada');
            sidebar.classList.remove('sidebar-aberta');
            document.body.classList.add('sidebar-esta-fixada');
            pinBtn.classList.add('ativo');
            pinBtn.querySelector('.pin-texto').textContent = 'Soltar menu';
        } else {
            sidebar.classList.remove('sidebar-fixada');
            document.body.classList.remove('sidebar-esta-fixada');
            pinBtn.classList.remove('ativo');
            pinBtn.querySelector('.pin-texto').textContent = 'Fixar menu';
            if (!hoverAtivo) {
                sidebar.classList.remove('sidebar-aberta');
            }
        }
    }

    aplicarEstado();

    trigger.addEventListener('mouseenter', function () {
        if (!isDesktop() || fixada) return;
        hoverAtivo = true;
        sidebar.classList.add('sidebar-aberta');
    });

    sidebar.addEventListener('mouseleave', function () {
        if (!isDesktop() || fixada) return;
        hoverAtivo = false;
        sidebar.classList.remove('sidebar-aberta');
        aplicarEstado();
    });

    pinBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        fixada = !fixada;
        localStorage.setItem(CHAVE_FIXADA, fixada);
        aplicarEstado();
    });

    window.addEventListener('resize', function () {
        if (!isDesktop()) {
            hoverAtivo = false;
            sidebar.classList.remove('sidebar-aberta', 'sidebar-fixada');
            document.body.classList.remove('sidebar-esta-fixada');
        }
    });
});
