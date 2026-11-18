'use strict';

const formulario = document.querySelector('#venda-form');
const lista = document.querySelector('#venda-itens');
const modelo = document.querySelector('#venda-item-template');
const adicionar = document.querySelector('#venda-adicionar');
const concluir = document.querySelector('#venda-concluir');
const totalExibido = document.querySelector('#venda-total');
const aviso = document.querySelector('#venda-erro');
const maxItens = Number(formulario.dataset.maxItens);
const maxCentavos = BigInt(formulario.dataset.maxCentavos);
const formato = new Intl.NumberFormat('pt-BR');
let proximoIndice = lista.children.length;

function moeda(centavos) {
    const parteInteira = formato.format(centavos / 100n);
    const parteDecimal = String(centavos % 100n).padStart(2, '0');
    return `R$ ${parteInteira},${parteDecimal}`;
}

function atualizar() {
    const itens = Array.from(lista.children);
    const quantidades = new Map();
    let total = 0n;
    let mensagem = '';

    for (const item of itens) {
        const produto = item.querySelector('[data-produto]').selectedOptions[0];
        const campoQuantidade = item.querySelector('[data-quantidade]');
        const precoExibido = item.querySelector('[data-preco]');
        const subtotalExibido = item.querySelector('[data-subtotal]');
        item.querySelector('[data-remover]').disabled = itens.length === 1;
        campoQuantidade.max = produto?.dataset.estoque ?? '2147483647';

        if (!produto?.value) {
            precoExibido.value = '—';
            subtotalExibido.value = '—';
            continue;
        }

        const preco = BigInt(produto.dataset.centavos);
        precoExibido.value = moeda(preco);
        subtotalExibido.value = '—';

        const quantidade = campoQuantidade.valueAsNumber;
        if (!Number.isSafeInteger(quantidade) || quantidade < 1) {
            continue;
        }

        const quantidadeAcumulada = (quantidades.get(produto.value) ?? 0) + quantidade;
        quantidades.set(produto.value, quantidadeAcumulada);
        if (quantidadeAcumulada > Number(produto.dataset.estoque)) {
            mensagem = 'A quantidade solicitada ultrapassa o estoque de um dos produtos.';
        }

        const subtotal = preco * BigInt(quantidade);
        subtotalExibido.value = moeda(subtotal);
        total += subtotal;
    }

    if (total > maxCentavos) {
        mensagem = 'O total ultrapassa o limite por venda.';
    }

    totalExibido.value = moeda(total);
    aviso.textContent = mensagem;
    aviso.hidden = !mensagem;
    adicionar.disabled = itens.length >= maxItens;
    concluir.disabled = Boolean(mensagem);
}

adicionar.addEventListener('click', () => {
    const item = modelo.content.firstElementChild.cloneNode(true);
    for (const campo of item.querySelectorAll('[name], [id], [for]')) {
        for (const atributo of ['name', 'id', 'for']) {
            if (campo.hasAttribute(atributo)) {
                campo.setAttribute(atributo, campo.getAttribute(atributo).replace('novo', proximoIndice));
            }
        }
    }
    proximoIndice++;
    lista.append(item);
    atualizar();
    item.querySelector('[data-produto]').focus();
});

lista.addEventListener('input', atualizar);
lista.addEventListener('click', (evento) => {
    const botao = evento.target.closest('[data-remover]');
    if (botao && lista.children.length > 1) {
        botao.closest('[data-item]').remove();
        atualizar();
        adicionar.focus();
    }
});

formulario.addEventListener('submit', () => {
    concluir.disabled = true;
});
window.addEventListener('pageshow', atualizar);
atualizar();
