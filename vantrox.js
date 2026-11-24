const campoBusca = document.getElementById("busca-produtos");
const produtos = [...document.querySelectorAll(".produto")];
const botoesFiltro = [...document.querySelectorAll("[data-filtro]")];
const mensagemVazio = document.getElementById("nenhum-produto");
const limparBusca = document.getElementById("limpar-busca");
const modalProduto = document.getElementById("produto-modal");
const modalImagem = document.getElementById("modal-imagem");
const modalCores = document.getElementById("modal-cores");
const modalTamanhos = document.getElementById("modal-tamanhos");
const botaoAdicionarModal = document.getElementById("modal-adicionar-carrinho");
let corAtual = "";
let tamanhoAtual = "";
let filtroAtual = "todos";

function filtrarProdutos() {
    const termo = (campoBusca?.value || "").trim().toLocaleLowerCase("pt-BR");
    let encontrados = 0;

    produtos.forEach(produto => {
        const correspondeBusca = !termo ||
            produto.dataset.nome.toLocaleLowerCase("pt-BR").includes(termo) ||
            produto.dataset.categoria.toLocaleLowerCase("pt-BR").includes(termo);
        const correspondePublico = filtroAtual === "todos" || produto.dataset.publico === filtroAtual;
        const mostrar = correspondeBusca && correspondePublico;
        produto.hidden = !mostrar;
        if (mostrar) encontrados++;
    });

    mensagemVazio.hidden = encontrados !== 0;
}

campoBusca?.addEventListener("input", filtrarProdutos);
limparBusca?.addEventListener("click", () => {
    campoBusca.value = "";
    filtrarProdutos();
    campoBusca.focus();
});
botoesFiltro.forEach(botao => {
    botao.addEventListener("click", () => {
        filtroAtual = botao.dataset.filtro;
        botoesFiltro.forEach(item => item.classList.remove("ativo"));
        botao.classList.add("ativo");
        filtrarProdutos();
        document.getElementById("produtos")?.scrollIntoView({ behavior: "smooth" });
    });
});

document.querySelectorAll("[data-filtro-link]").forEach(link => {
    link.addEventListener("click", () => {
        const botao = document.querySelector(`[data-filtro="${link.dataset.filtroLink}"]`);
        if (botao) botao.click();
    });
});

const formContato = document.getElementById("form-contato");
formContato?.addEventListener("submit", evento => {
    evento.preventDefault();
    document.getElementById("contato-status").textContent =
        "O formulário ainda precisa de um e-mail de atendimento para enviar mensagens.";
});

function fecharModalProduto() {
    modalProduto.classList.remove("ativo");
    modalProduto.setAttribute("aria-hidden", "true");
    document.body.classList.remove("modal-aberto");
}

function selecionarCor(botao, cores) {
    modalCores.querySelectorAll("button").forEach(item => item.classList.remove("ativo"));
    botao.classList.add("ativo");
    corAtual = botao.dataset.cor;
    modalImagem.src = cores[corAtual];
    modalImagem.alt = document.getElementById("modal-nome").textContent + " na cor " + corAtual;
    document.getElementById("modal-cor-selecionada").textContent = "— " + corAtual;
    document.getElementById("modal-erro").textContent = "";
    botaoAdicionarModal.dataset.imagem = cores[corAtual];
    botaoAdicionarModal.dataset.cor = corAtual;
}

function abrirModalProduto(card) {
    let cores;
    try {
        cores = JSON.parse(card.dataset.cores);
    } catch (erro) {
        console.error("Não foi possível carregar as cores do produto.", erro);
        return;
    }

    corAtual = "";
    tamanhoAtual = "";
    modalCores.replaceChildren();
    modalTamanhos.replaceChildren();
    modalImagem.src = "";
    modalImagem.alt = "";
    document.getElementById("modal-categoria").textContent = card.dataset.categoria;
    document.getElementById("modal-nome").textContent = card.querySelector("h3").textContent;
    document.getElementById("modal-preco").textContent = Number(card.dataset.preco).toLocaleString("pt-BR", {
        style: "currency",
        currency: "BRL"
    });
    document.getElementById("modal-cor-selecionada").textContent = "";
    document.getElementById("modal-tamanho-selecionado").textContent = "";
    document.getElementById("modal-erro").textContent = "";

    Object.entries(cores).forEach(([cor, imagem]) => {
        const botao = document.createElement("button");
        botao.type = "button";
        botao.className = "opcao-cor";
        botao.textContent = cor;
        botao.dataset.cor = cor;
        botao.addEventListener("click", () => selecionarCor(botao, cores));
        modalCores.appendChild(botao);
    });

    const tamanhos = card.dataset.publico === "infantil"
        ? ["2", "4", "6", "8", "10", "12"]
        : ["P", "M", "G", "GG"];
    tamanhos.forEach(tamanho => {
        const botao = document.createElement("button");
        botao.type = "button";
        botao.className = "opcao-tamanho";
        botao.textContent = tamanho;
        botao.addEventListener("click", () => {
            modalTamanhos.querySelectorAll("button").forEach(item => item.classList.remove("ativo"));
            botao.classList.add("ativo");
            tamanhoAtual = tamanho;
            document.getElementById("modal-tamanho-selecionado").textContent = "— " + tamanho;
            document.getElementById("modal-erro").textContent = "";
            botaoAdicionarModal.dataset.tamanho = tamanho;
        });
        modalTamanhos.appendChild(botao);
    });

    botaoAdicionarModal.dataset.id = card.dataset.id;
    botaoAdicionarModal.dataset.nome = card.querySelector("h3").textContent;
    botaoAdicionarModal.dataset.preco = card.dataset.preco;
    botaoAdicionarModal.dataset.publico = card.dataset.publico;
    delete botaoAdicionarModal.dataset.cor;
    delete botaoAdicionarModal.dataset.tamanho;
    const primeiraCor = modalCores.querySelector("button");
    if (primeiraCor) selecionarCor(primeiraCor, cores);
    modalProduto.classList.add("ativo");
    modalProduto.setAttribute("aria-hidden", "false");
    document.body.classList.add("modal-aberto");
    modalCores.querySelector("button")?.focus();
}

document.querySelectorAll(".abrir-produto").forEach(botao => {
    botao.addEventListener("click", () => abrirModalProduto(botao.closest(".produto")));
});

document.getElementById("fechar-produto-modal")?.addEventListener("click", fecharModalProduto);
modalProduto?.addEventListener("click", evento => {
    if (evento.target === modalProduto) fecharModalProduto();
});
document.addEventListener("keydown", evento => {
    if (evento.key === "Escape" && modalProduto.classList.contains("ativo")) fecharModalProduto();
});
botaoAdicionarModal?.addEventListener("click", evento => {
    if (!corAtual || !tamanhoAtual) {
        evento.preventDefault();
        document.getElementById("modal-erro").textContent = "Escolha uma cor e um tamanho para continuar.";
    }
});
document.addEventListener("click", evento => {
    if (evento.target.closest("#modal-adicionar-carrinho") && corAtual && tamanhoAtual) {
        window.setTimeout(fecharModalProduto, 100);
    }
});

const banners = [
    {
        subtitulo: "NOVA COLEÇÃO 2026",
        titulo: "YOUR STYLE.\nYOUR RULES.",
        descricao: "Moda urbana para todos os estilos.",
        imagem: "img/WhatsApp Image 2026-11-06 at 21.13.13 (1).jpeg",
        textoAlternativo: "Modelo usando moletom Vantrox verde"
    },
    {
        subtitulo: "VANTROX INFANTIL",
        titulo: "ESTILO\nINFANTIL.",
        descricao: "Conforto e atitude para acompanhar cada aventura.",
        imagem: "img/INFANTIL AZUL.jpg.jpeg",
        textoAlternativo: "Camiseta Vantrox azul da coleção infantil"
    },
    {
        subtitulo: "MODA URBANA",
        titulo: "SUA ROUPA.\nSUA IDENTIDADE.",
        descricao: "Peças que mostram quem você é.",
        imagem: "img/WhatsApp Image 2026-11-06 at 21.13.13 (2).jpeg",
        textoAlternativo: "Modelo usando camiseta Vantrox roxa"
    },
    {
        subtitulo: "NOVA COLEÇÃO",
        titulo: "VISTA SUA\nATITUDE.",
        descricao: "Descubra os novos looks da Vantrox.",
        imagem: "img/WhatsApp Image 2026-11-06 at 21.13.13 (4).jpeg",
        textoAlternativo: "Modelo usando jaqueta Vantrox bege"
    }
];

const fotoBanner = document.getElementById("banner-foto");
const indicadoresBanner = document.getElementById("banner-indicadores");
const visualBanner = document.querySelector(".banner-visual");

if (fotoBanner && indicadoresBanner && visualBanner) {
    let bannerAtual = 0;
    let rotacaoBanner;

    banners.forEach((banner, indice) => {
        const ponto = document.createElement("button");
        ponto.type = "button";
        ponto.className = "banner-ponto";
        ponto.setAttribute("aria-label", "Mostrar banner " + (indice + 1));
        ponto.addEventListener("click", () => mostrarBanner(indice));
        indicadoresBanner.appendChild(ponto);
    });

    const pontosBanner = [...indicadoresBanner.querySelectorAll(".banner-ponto")];

    function mostrarBanner(indice) {
        bannerAtual = (indice + banners.length) % banners.length;
        const banner = banners[bannerAtual];

        fotoBanner.classList.add("banner-fade");
        window.setTimeout(() => {
            fotoBanner.src = banner.imagem;
            fotoBanner.alt = banner.textoAlternativo;
            document.getElementById("banner-subtitulo").textContent = banner.subtitulo;
            document.getElementById("banner-titulo").textContent = banner.titulo;
            document.getElementById("banner-descricao").textContent = banner.descricao;
            pontosBanner.forEach((ponto, pontoIndice) => {
                ponto.classList.toggle("ativo", pontoIndice === bannerAtual);
                ponto.setAttribute("aria-current", pontoIndice === bannerAtual ? "true" : "false");
            });
            requestAnimationFrame(() => fotoBanner.classList.remove("banner-fade"));
        }, 120);
    }

    function iniciarRotacaoBanner() {
        window.clearInterval(rotacaoBanner);
        const usuarioInteragindo = visualBanner.matches(":hover") || visualBanner.contains(document.activeElement);
        if (!usuarioInteragindo && !window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
            rotacaoBanner = window.setInterval(() => mostrarBanner(bannerAtual + 1), 5000);
        }
    }

    document.querySelector(".banner-anterior").addEventListener("click", () => {
        mostrarBanner(bannerAtual - 1);
        iniciarRotacaoBanner();
    });
    document.querySelector(".banner-proximo").addEventListener("click", () => {
        mostrarBanner(bannerAtual + 1);
        iniciarRotacaoBanner();
    });
    visualBanner.addEventListener("mouseenter", () => window.clearInterval(rotacaoBanner));
    visualBanner.addEventListener("mouseleave", iniciarRotacaoBanner);
    visualBanner.addEventListener("focusin", () => window.clearInterval(rotacaoBanner));
    visualBanner.addEventListener("focusout", iniciarRotacaoBanner);

    mostrarBanner(0);
    iniciarRotacaoBanner();
}
