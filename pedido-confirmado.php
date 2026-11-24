<?php
require_once __DIR__ . '/includes/auth.php';
exigir_login();

$id = (string) ($_GET['id'] ?? '');
$usuario = usuario_logado();
$pedido = null;
foreach (ler_json('pedidos.json') as $item) {
    if (($item['id'] ?? '') === $id && ($item['usuario_id'] ?? '') === $usuario['id']) {
        $pedido = $item;
        break;
    }
}

if (!$pedido) {
    http_response_code(404);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido confirmado | VANTROX</title>
    <link rel="stylesheet" href="<?= escapar(url_site('css/style.css')) ?>">
</head>
<body class="pagina-confirmacao">
    <main>
        <?php if ($pedido): ?>
            <span class="marca-confirmacao">✓</span>
            <h1>PEDIDO CONFIRMADO</h1>
            <p>Seu pedido <strong><?= escapar($pedido['id']) ?></strong> foi registrado.</p>
            <p>Total: <strong>R$ <?= number_format((float) $pedido['total'], 2, ',', '.') ?></strong></p>
            <p>Status: <strong><?= escapar($pedido['status']) ?></strong></p>
            <?php if (!empty($pedido['itens'])): ?>
                <div class="avaliacoes-pedido">
                    <p>Avalie os produtos do seu pedido:</p>
                    <?php foreach ($pedido['itens'] as $item): ?>
                        <div class="avaliacao-pedido" data-pedido="<?= escapar($pedido['id']) ?>" data-produto="<?= escapar((string) $item['id']) ?>">
                            <strong><?= escapar($item['nome']) ?><?= !empty($item['cor']) ? ' - ' . escapar($item['cor']) : '' ?><?= !empty($item['tamanho']) ? ' / ' . escapar($item['tamanho']) : '' ?></strong>
                            <div class="avaliacao-estrelas">
                                <?php for ($estrela = 1; $estrela <= 5; $estrela++): ?>
                                    <button type="button" data-star="<?= $estrela ?>" aria-label="<?= $estrela ?> estrelas">★</button>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <a href="<?= escapar(url_site('index.php')) ?>" class="botao">VOLTAR À LOJA</a>
        <?php else: ?>
            <h1>PEDIDO NÃO ENCONTRADO</h1>
            <a href="<?= escapar(url_site('index.php')) ?>" class="botao">VOLTAR À LOJA</a>
        <?php endif; ?>
    </main>
    <?php if ($pedido): ?>
    <script>
        localStorage.removeItem("carrinho");
        const avaliacoesPedido = JSON.parse(localStorage.getItem("avaliacoes-pedidos") || "{}");
        document.querySelectorAll(".avaliacao-pedido").forEach(grupo => {
            const chave = grupo.dataset.pedido + ":" + grupo.dataset.produto;
            const notaSalva = Number(avaliacoesPedido[chave] || 0);

            function pintar(nota) {
                grupo.querySelectorAll("[data-star]").forEach(botao => {
                    botao.classList.toggle("ativa", Number(botao.dataset.star) <= nota);
                });
            }

            pintar(notaSalva);
            grupo.querySelectorAll("[data-star]").forEach(botao => {
                botao.addEventListener("click", () => {
                    const nota = Number(botao.dataset.star);
                    avaliacoesPedido[chave] = nota;
                    localStorage.setItem("avaliacoes-pedidos", JSON.stringify(avaliacoesPedido));
                    pintar(nota);
                });
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>
