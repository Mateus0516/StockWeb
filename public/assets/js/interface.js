'use strict';

const alternarMenu = document.querySelector('[data-sidebar-toggle]');
if (alternarMenu) {
    const chaveMenu = 'stockweb-menu-recolhido';

    function atualizarMenu(recolhido) {
        document.body.classList.toggle('sw-sidebar-collapsed', recolhido);
        alternarMenu.setAttribute('aria-expanded', String(!recolhido));
        const rotulo = recolhido ? 'Expandir menu' : 'Recolher menu';
        alternarMenu.setAttribute('aria-label', rotulo);
        alternarMenu.title = rotulo;
    }

    try {
        atualizarMenu(localStorage.getItem(chaveMenu) === 'true');
    } catch {
        // O menu pode ser recolhido mesmo quando o navegador bloqueia o armazenamento.
    }

    alternarMenu.hidden = false;
    alternarMenu.addEventListener('click', () => {
        const recolhido = alternarMenu.getAttribute('aria-expanded') === 'true';
        atualizarMenu(recolhido);
        try {
            localStorage.setItem(chaveMenu, String(recolhido));
        } catch {
            // A preferência vale até a navegação seguinte.
        }
    });
}

const alternarSenha = document.querySelector('[data-password-toggle]');
if (alternarSenha) {
    const senha = document.getElementById(alternarSenha.getAttribute('aria-controls'));
    alternarSenha.hidden = false;
    alternarSenha.addEventListener('click', () => {
        const visivel = senha.type === 'password';
        senha.type = visivel ? 'text' : 'password';
        alternarSenha.setAttribute('aria-pressed', String(visivel));
        alternarSenha.title = visivel ? 'Ocultar senha' : 'Mostrar senha';
        alternarSenha.querySelector('.bi').className = visivel ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
}

const dialogo = document.querySelector('#sw-confirmacao');
if (dialogo && typeof dialogo.showModal === 'function') {
    const mensagem = dialogo.querySelector('#sw-confirmacao-mensagem');
    let formularioPendente = null;
    let botaoPendente = null;
    let formularioConfirmado = null;

    document.addEventListener('submit', (evento) => {
        const formulario = evento.target;
        if (!formulario.matches('form[data-confirmacao]')) {
            return;
        }
        // Intercepta a confirmação nativa quando o diálogo já está disponível.
        evento.stopPropagation();
        if (formulario === formularioConfirmado) {
            return;
        }

        evento.preventDefault();
        if (!dialogo.open) {
            formularioPendente = formulario;
            botaoPendente = evento.submitter;
            mensagem.textContent = formulario.dataset.confirmacao;
            dialogo.returnValue = 'cancelar';
            dialogo.showModal();
        }
    }, true);

    dialogo.addEventListener('close', () => {
        const formulario = formularioPendente;
        const botao = botaoPendente;
        formularioPendente = null;
        botaoPendente = null;

        if (dialogo.returnValue === 'excluir' && formulario) {
            formularioConfirmado = formulario;
            formulario.requestSubmit(botao ?? undefined);
            formularioConfirmado = null;
        }
    });
}
