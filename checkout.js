const catalogo = {
    "1": { nome: "Moletom Vantrox Logo", preco: 249.90 },
    "2": { nome: "Camiseta Vantrox Graphic", preco: 139.90 },
    "3": { nome: "Camiseta Vantrox Branca", preco: 129.90 },
    "4": { nome: "Conjunto Street Black", preco: 139.90 },
    "5": { nome: "Moletom Street", preco: 249.90 },
    "6": { nome: "Camiseta Vantrox Azul", preco: 129.90 },
    "7": { nome: "Moletom Urban Verde", preco: 249.90 },
    "8": { nome: "Camiseta Street Roxa", preco: 119.90 },
    "9": { nome: "Jaqueta Vantrox Verde", preco: 279.90 },
    "10": { nome: "Jaqueta Urban Bege", preco: 259.90 },
    "11": { nome: "Camiseta Feminina Vantrox", preco: 119.90 },
    "14": { nome: "Camiseta Infantil", preco: 79.90 }
};

const carrinho = JSON.parse(localStorage.getItem("carrinho") || "[]");
const resumo = document.getElementById("resumo-itens");
const resumoTotal = document.getElementById("resumo-total");
const itensCheckout = document.getElementById("itens-checkout");
const opcoesPagamento = [...document.querySelectorAll('input[name="pagamento"]')];
const dadosCartao = document.getElementById("dados-cartao");
const avisoPagamento = document.getElementById("aviso-pagamento");
const camposCartao = [...document.querySelectorAll("#dados-cartao input")];

function formatar(valor) {
    return Number(valor).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
}

let total = 0;
resumo.innerHTML = "";

carrinho.forEach(item => {
    const produto = catalogo[String(item.id)] || item;
    const quantidade = Number(item.quantidade) || 1;
    const preco = Number(produto.preco) || 0;
    const subtotal = preco * quantidade;
    total += subtotal;

    resumo.innerHTML += `
        <div class="resumo-item">
            <div><strong>${produto.nome}</strong><span>Quantidade: ${quantidade} · Cor: ${item.cor || "—"} · Tamanho: ${item.tamanho || "—"}</span></div>
            <strong>${formatar(subtotal)}</strong>
        </div>
    `;
});

resumoTotal.textContent = formatar(total);
itensCheckout.value = JSON.stringify(carrinho);

if (carrinho.length === 0) {
    resumo.innerHTML = '<p class="resumo-vazio">Seu carrinho está vazio.</p>';
}

function atualizarPagamento() {
    const metodo = opcoesPagamento.find(opcao => opcao.checked)?.value || "";
    const mostrarCartao = metodo === "cartao";
    dadosCartao.hidden = !mostrarCartao;
    camposCartao.forEach(campo => {
        campo.disabled = !mostrarCartao;
        campo.required = mostrarCartao;
    });

    if (metodo === "pix") {
        avisoPagamento.textContent = "Pagamento PIX demonstrativo. Nenhuma cobrança será realizada.";
    } else if (metodo === "boleto") {
        avisoPagamento.textContent = "Boleto demonstrativo. Nenhum boleto real será emitido.";
    } else if (mostrarCartao) {
        avisoPagamento.textContent = "Preencha os campos de demonstração abaixo. Não informe dados reais.";
    } else {
        avisoPagamento.textContent = "Selecione como deseja pagar para ver as instruções.";
    }
}

opcoesPagamento.forEach(opcao => opcao.addEventListener("change", atualizarPagamento));
atualizarPagamento();

const numeroCartao = document.getElementById("numero-cartao");
numeroCartao.addEventListener("input", () => {
    const digitos = numeroCartao.value.replace(/\D/g, "").slice(0, 16);
    numeroCartao.value = digitos.replace(/(\d{4})(?=\d)/g, "$1 ");
});

const validadeCartao = document.getElementById("validade-cartao");
validadeCartao.addEventListener("input", () => {
    const digitos = validadeCartao.value.replace(/\D/g, "").slice(0, 4);
    validadeCartao.value = digitos.length > 2 ? digitos.slice(0, 2) + "/" + digitos.slice(2) : digitos;
});

document.getElementById("codigo-cartao").addEventListener("input", event => {
    event.target.value = event.target.value.replace(/\D/g, "").slice(0, 4);
});
