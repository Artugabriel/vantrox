
// RECUPERAR CARRINHO
let carrinho = [];
try {
    carrinho = JSON.parse(localStorage.getItem("carrinho")) || [];
    if (!Array.isArray(carrinho)) carrinho = [];
} catch (erro) {
    carrinho = [];
}

// ELEMENTOS HTML
const botaoAbrir = document.getElementById("abrir-carrinho");
const botaoFechar = document.getElementById("fechar-carrinho");
const overlay = document.getElementById("carrinho-overlay");
const listaCarrinho = document.getElementById("lista-carrinho");
const contadorCarrinho = document.getElementById("contador-carrinho");
const valorTotal = document.getElementById("valor-total");

// VERIFICAR ELEMENTOS
if (!botaoAbrir || !botaoFechar || !overlay ||
    !listaCarrinho || !contadorCarrinho || !valorTotal) {
    console.error("Erro: algum elemento do carrinho não foi encontrado no HTML.");
} else {

    // ABRIR CARRINHO
    botaoAbrir.addEventListener("click", () => {
        atualizarCarrinho();
        overlay.classList.add("ativo");
    });

    // FECHAR CARRINHO
    botaoFechar.addEventListener("click", () => {
        overlay.classList.remove("ativo");
    });

    overlay.addEventListener("click", (event) => {
        if (event.target === overlay) {
            overlay.classList.remove("ativo");
        }
    });

    document.addEventListener("keydown", event => {
        if (event.key === "Escape") overlay.classList.remove("ativo");
    });

    // SALVAR
    function salvarCarrinho() {
        localStorage.setItem("carrinho", JSON.stringify(carrinho));
    }

    // FORMATAR PREÇO
    function formatarPreco(valor) {
        return Number(valor).toLocaleString("pt-BR", {
            style: "currency",
            currency: "BRL"
        });
    }

    // ADICIONAR PRODUTO CONFIGURADO
    document.querySelectorAll("#modal-adicionar-carrinho").forEach(botao => {
        botao.addEventListener("click", () => {

            const id = String(botao.dataset.id);
            const nome = botao.dataset.nome;
            const preco = Number(botao.dataset.preco);
            const corSelecionada = botao.dataset.cor || "";
            const tamanhoSelecionado = botao.dataset.tamanho || "";
            const imagem = botao.dataset.imagem || "";

            if (!corSelecionada || !tamanhoSelecionado) {
                document.getElementById("modal-erro").textContent = "Escolha uma cor e um tamanho para continuar.";
                return;
            }

            const chave = id + ":" + corSelecionada + ":" + tamanhoSelecionado;
            const existente = carrinho.find(p => (p.chave || p.id) === chave);

            if (existente) {
                existente.quantidade++;
            } else {
                carrinho.push({
                    id,
                    chave,
                    nome,
                    preco,
                    imagem,
                    cor: corSelecionada,
                    tamanho: tamanhoSelecionado,
                    quantidade: 1
                });
            }

            salvarCarrinho();
            atualizarCarrinho();

            let toast = document.getElementById("toast-carrinho");
            if (!toast) {
                toast = document.createElement("div");
                toast.id = "toast-carrinho";
                toast.className = "toast-carrinho";
                document.body.appendChild(toast);
            }
            toast.textContent = nome + " (" + corSelecionada + ", " + tamanhoSelecionado + ") foi adicionado ao carrinho";
            toast.classList.add("ativo");
            clearTimeout(window.toastCarrinhoTimer);
            window.toastCarrinhoTimer = setTimeout(() => toast.classList.remove("ativo"), 2500);
        });
    });

    // REMOVER PRODUTO
    function removerProduto(id) {
        carrinho = carrinho.filter(p => (p.chave || p.id) !== id);
        salvarCarrinho();
        atualizarCarrinho();
    }

    // ALTERAR QUANTIDADE
    function alterarQuantidade(id, operacao) {
        const produto = carrinho.find(p => (p.chave || p.id) === id);

        if (!produto) return;

        if (operacao === "aumentar") {
            produto.quantidade++;
        } else {
            produto.quantidade--;

            if (produto.quantidade <= 0) {
                removerProduto(id);
                return;
            }
        }

        salvarCarrinho();
        atualizarCarrinho();
    }

    // ATUALIZAR CARRINHO
    function atualizarCarrinho() {

        listaCarrinho.innerHTML = "";

        let total = 0;
        let quantidadeTotal = 0;

        if (carrinho.length === 0) {
            listaCarrinho.innerHTML = `
                <div class="carrinho-vazio">
                    <h3>SEU CARRINHO ESTÁ VAZIO</h3>
                    <p>Adicione produtos da nossa coleção!</p>
                </div>
            `;
        }

        carrinho.forEach(produto => {

            const subtotal = produto.preco * produto.quantidade;

            total += subtotal;
            quantidadeTotal += produto.quantidade;

            listaCarrinho.innerHTML += `
                <div class="item-carrinho">

                    ${produto.imagem
                        ? `<img src="${produto.imagem}" alt="${produto.nome}">`
                        : `<div class="imagem-vazia">VANTROX</div>`
                    }

                    <div class="item-info">
                        <h3>${produto.nome}</h3>
                        <p>${formatarPreco(produto.preco)}</p>

                        <div class="quantidade">
                            <button data-acao="diminuir" data-id="${produto.chave || produto.id}">-</button>
                            <span>${produto.quantidade}</span>
                            <button data-acao="aumentar" data-id="${produto.chave || produto.id}">+</button>
                        </div>

                        <button class="remover"
                            data-acao="remover"
                            data-id="${produto.chave || produto.id}">
                            Remover
                        </button>
                    </div>

                    <strong>${formatarPreco(subtotal)}</strong>
                </div>
            `;
        });

        valorTotal.textContent = formatarPreco(total);
        contadorCarrinho.textContent = quantidadeTotal;

        const botaoFinalizar = document.querySelector(".finalizar-compra");
        if (botaoFinalizar) {
            botaoFinalizar.disabled = carrinho.length === 0;
        }
    }

    // BOTÕES DO CARRINHO
    listaCarrinho.addEventListener("click", (event) => {

        const botao = event.target.closest("button");

        if (!botao) return;

        const id = botao.dataset.id;
        const acao = botao.dataset.acao;

        if (acao === "aumentar") alterarQuantidade(id, "aumentar");
        if (acao === "diminuir") alterarQuantidade(id, "diminuir");
        if (acao === "remover") removerProduto(id);
    });

    // FINALIZAR COMPRA
    const botaoFinalizar = document.querySelector(".finalizar-compra");
    if (botaoFinalizar) {
        botaoFinalizar.addEventListener("click", () => {
            if (carrinho.length === 0) return;
            if (carrinho.some(produto => !produto.cor || !produto.tamanho)) {
                let toast = document.getElementById("toast-carrinho");
                if (!toast) {
                    toast = document.createElement("div");
                    toast.id = "toast-carrinho";
                    toast.className = "toast-carrinho";
                    document.body.appendChild(toast);
                }
                toast.textContent = "Remova e adicione novamente os itens antigos, escolhendo cor e tamanho.";
                toast.classList.add("ativo");
                clearTimeout(window.toastCarrinhoTimer);
                window.toastCarrinhoTimer = setTimeout(() => toast.classList.remove("ativo"), 4000);
                return;
            }

            fetch(new URL("conta/estado.php", window.location.href))
                .then(resposta => resposta.json())
                .then(sessao => {
                    window.location.href = new URL(sessao.logado ? "checkout.php" : "conta/login.php", window.location.href).href;
                })
                .catch(() => {
                    window.location.href = new URL("conta/login.php", window.location.href).href;
                });
        });
    }

    // INICIALIZAR
    atualizarCarrinho();
}

