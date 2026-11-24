<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/catalogo.php';
exigir_login();

$usuario = usuario_logado();
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim((string) ($_POST['nome'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $telefone = trim((string) ($_POST['telefone'] ?? ''));
    $endereco = trim((string) ($_POST['endereco'] ?? ''));
    $cidade = trim((string) ($_POST['cidade'] ?? ''));
    $cep = trim((string) ($_POST['cep'] ?? ''));
    $pagamento = (string) ($_POST['pagamento'] ?? '');

    $itensJson = json_decode((string) ($_POST['itens'] ?? '[]'), true);
    if (!is_array($itensJson) || $itensJson === []) {
        $erro = 'Seu carrinho está vazio.';
    } elseif ($nome === '' || $email === '' || $telefone === '' || $endereco === '' || $cidade === '' || $cep === '') {
        $erro = 'Preencha todos os dados de entrega.';
    } elseif (!in_array($pagamento, ['pix', 'cartao', 'boleto'], true)) {
        $erro = 'Selecione uma forma de pagamento.';
    } else {
        $catalogo = catalogo_produtos();
        $itens = [];
        $total = 0.0;

        foreach ($itensJson as $item) {
            $id = (string) ($item['id'] ?? '');
            $quantidade = (int) ($item['quantidade'] ?? 0);
            if (!isset($catalogo[$id]) || $quantidade < 1) {
                continue;
            }
            $produto = $catalogo[$id];
            $cor = (string) ($item['cor'] ?? '');
            $tamanho = (string) ($item['tamanho'] ?? '');
            $coresDisponiveis = $produto['cores'] ?? [];
            $tamanhosDisponiveis = ($produto['publico'] ?? '') === 'infantil'
                ? ['2', '4', '6', '8', '10', '12']
                : ['P', 'M', 'G', 'GG'];
            if (!array_key_exists($cor, $coresDisponiveis) || !in_array($tamanho, $tamanhosDisponiveis, true)) {
                continue;
            }
            $subtotal = $produto['preco'] * $quantidade;
            $itens[] = [
                'id' => $id,
                'nome' => $produto['nome'],
                'cor' => $cor,
                'tamanho' => $tamanho,
                'preco' => $produto['preco'],
                'quantidade' => $quantidade,
                'subtotal' => $subtotal,
            ];
            $total += $subtotal;
        }

        if ($itens === []) {
            $erro = 'Nenhum produto válido foi encontrado.';
        } else {
            $pedidos = ler_json('pedidos.json');
            $pedido = [
                'id' => 'VT-' . strtoupper(bin2hex(random_bytes(4))),
                'usuario_id' => $usuario['id'],
                'cliente' => $nome,
                'email' => $email,
                'telefone' => $telefone,
                'endereco' => $endereco,
                'cidade' => $cidade,
                'cep' => $cep,
                'pagamento' => $pagamento,
                'status' => 'Pendente',
                'itens' => $itens,
                'total' => $total,
                'criado_em' => date('Y-m-d H:i:s'),
            ];
            array_unshift($pedidos, $pedido);

            if (!escrever_json('pedidos.json', $pedidos)) {
                $erro = 'Não foi possível registrar o pedido.';
            } else {
                header('Location: ' . url_site('pedido-confirmado.php') . '?id=' . urlencode($pedido['id']));
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar compra | VANTROX</title>
    <link rel="stylesheet" href="<?= escapar(url_site('css/style.css')) ?>">
</head>
<body class="pagina-checkout">
    <header class="checkout-topo">
        <a href="<?= escapar(url_site('index.php')) ?>" class="logo">VANTROX<span>.</span></a>
        <a href="<?= escapar(url_site('index.php')) ?>#produtos">← Continuar comprando</a>
    </header>

    <main class="checkout-layout">
        <form class="checkout-form" method="post" id="form-checkout">
            <h1>FINALIZAR COMPRA</h1>
            <?php if ($erro): ?><div class="mensagem erro"><?= escapar($erro) ?></div><?php endif; ?>

            <section>
                <h2>Dados de entrega</h2>
                <div class="grid-form">
                    <label>Nome completo
                        <input type="text" name="nome" value="<?= escapar((string) ($_POST['nome'] ?? $usuario['nome'])) ?>" required>
                    </label>
                    <label>E-mail
                        <input type="email" name="email" value="<?= escapar((string) ($_POST['email'] ?? $usuario['email'])) ?>" required>
                    </label>
                    <label>Telefone
                        <input type="tel" name="telefone" value="<?= escapar((string) ($_POST['telefone'] ?? '')) ?>" required>
                    </label>
                    <label>CEP
                        <input type="text" name="cep" value="<?= escapar((string) ($_POST['cep'] ?? '')) ?>" required>
                    </label>
                    <label class="campo-largo">Endereço
                        <input type="text" name="endereco" value="<?= escapar((string) ($_POST['endereco'] ?? '')) ?>" required>
                    </label>
                    <label>Cidade
                        <input type="text" name="cidade" value="<?= escapar((string) ($_POST['cidade'] ?? '')) ?>" required>
                    </label>
                </div>
            </section>

            <section>
                <h2>Pagamento</h2>
                <div class="opcoes-pagamento">
                    <label><input type="radio" name="pagamento" value="pix" required> PIX</label>
                    <label><input type="radio" name="pagamento" value="cartao" required> Cartão de crédito</label>
                    <label><input type="radio" name="pagamento" value="boleto" required> Boleto</label>
                </div>
                <div class="pagamento-detalhes" id="pagamento-detalhes">
                    <p class="aviso-pagamento" id="aviso-pagamento">Selecione como deseja pagar para ver as instruções.</p>
                    <fieldset id="dados-cartao" hidden>
                        <legend>Dados do cartão (demonstração)</legend>
                        <p class="aviso-pagamento">Não use dados reais. Estes campos são apenas para a apresentação e não são enviados nem armazenados.</p>
                        <div class="grid-form">
                            <label class="campo-largo">Nome impresso no cartão
                                <input type="text" id="titular-cartao" autocomplete="off" disabled>
                            </label>
                            <label class="campo-largo">Número do cartão
                                <input type="text" id="numero-cartao" inputmode="numeric" autocomplete="off" placeholder="0000 0000 0000 0000" maxlength="19" pattern="[0-9]{4} [0-9]{4} [0-9]{4} [0-9]{4}" title="Digite 16 números" disabled>
                            </label>
                            <label>Validade
                                <input type="text" id="validade-cartao" inputmode="numeric" autocomplete="off" placeholder="MM/AA" maxlength="5" pattern="(0[1-9]|1[0-2])/[0-9]{2}" title="Use o formato MM/AA" disabled>
                            </label>
                            <label>Código de segurança
                                <input type="password" id="codigo-cartao" inputmode="numeric" autocomplete="off" placeholder="000" maxlength="4" pattern="[0-9]{3,4}" title="Digite 3 ou 4 números" disabled>
                            </label>
                        </div>
                    </fieldset>
                </div>
            </section>

            <input type="hidden" name="itens" id="itens-checkout">
            <button type="submit" class="botao-finalizar">CONFIRMAR PEDIDO</button>
        </form>

        <aside class="resumo-pedido">
            <h2>RESUMO</h2>
            <div id="resumo-itens"></div>
            <div class="resumo-total"><span>TOTAL</span><strong id="resumo-total">R$ 0,00</strong></div>
        </aside>
    </main>

    <script src="<?= escapar(url_site('js/checkout.js')) ?>"></script>
</body>
</html>
