/* Funções compartilhadas por todas as telas. */

const PAGINAS = [
    { arquivo: 'chamados.html',     rotulo: 'Chamados',     perfis: ['atendente', 'tecnico', 'admin'] },
    { arquivo: 'funcionarios.html', rotulo: 'Funcionários', perfis: ['admin'] },
    { arquivo: 'categorias.html',   rotulo: 'Categorias',   perfis: ['admin'] },
    { arquivo: 'setores.html',      rotulo: 'Setores',      perfis: ['admin'] },
];

const ROTULO_PERFIL = { atendente: 'Atendente', tecnico: 'Técnico', admin: 'Administrador' };

/** Escapa texto antes de colocar em innerHTML (evita XSS). */
function esc(valor) {
    return String(valor ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function formatarData(texto) {
    if (!texto) return '—';
    const d = new Date(texto.replace(' ', 'T'));
    return isNaN(d) ? texto : d.toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' });
}

/** fetch com JSON; se a sessão caiu (401) volta para o login. */
async function chamarApi(url, opcoes = {}) {
    const resposta = await fetch(url, opcoes);
    if (resposta.status === 401) {
        window.location.href = 'login.html';
        throw new Error('Não autenticado');
    }
    return resposta.json();
}

function enviarJson(url, metodo, corpo) {
    return chamarApi(url, {
        method: metodo,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(corpo),
    });
}

function mostrarMensagem(texto, tipo) {
    const el = document.getElementById('mensagem');
    if (!el) return;
    el.textContent = texto;
    el.className = tipo;
}

function preencherSelect(select, itens, campoValor, campoTexto, opcaoVazia = null) {
    const vazia = opcaoVazia !== null ? `<option value="">${esc(opcaoVazia)}</option>` : '';
    select.innerHTML = vazia + itens
        .map(i => `<option value="${esc(i[campoValor])}">${esc(i[campoTexto])}</option>`)
        .join('');
}

/**
 * Confere a sessão, monta o cabeçalho com menu e botão Sair.
 * perfisPermitidos: se informado e o usuário não estiver na lista, volta para chamados.html.
 */
async function iniciarPagina(perfisPermitidos = null) {
    const resultado = await chamarApi('../api/auth.php');
    const usuario = resultado.dados;

    if (perfisPermitidos && !perfisPermitidos.includes(usuario.tipo_usuario)) {
        window.location.href = 'chamados.html';
        throw new Error('Sem permissão');
    }

    const atual = window.location.pathname.split('/').pop();
    const menu = PAGINAS
        .filter(p => p.perfis.includes(usuario.tipo_usuario))
        .map(p => `<a href="${p.arquivo}" class="${p.arquivo === atual ? 'ativo' : ''}">${p.rotulo}</a>`)
        .join('');

    const topo = document.getElementById('topo');
    if (topo) {
        topo.innerHTML = `
            <nav>${menu}</nav>
            <span class="usuario">${esc(usuario.nome)} (${esc(ROTULO_PERFIL[usuario.tipo_usuario])})
                | <a href="#" id="link-sair">Sair</a></span>`;
        document.getElementById('link-sair').addEventListener('click', async (e) => {
            e.preventDefault();
            await fetch('../api/auth.php', { method: 'DELETE' });
            window.location.href = 'login.html';
        });
    }
    return usuario;
}
