<?php
require_once __DIR__ . '/../includes/auth.php';
exigir_admin();

$pedidos = ler_json('pedidos.json');
$usuarios = ler_json('usuarios.json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pedidoId = (string) ($_POST['pedido_id'] ?? '');
    $novoStatus = (string) ($_POST['status'] ?? '');
    $statusPermitidos = ['Pendente', 'Pago', 'Enviado', 'Entregue', 'Cancelado'];

    if (in_array($novoStatus, $statusPermitidos, true)) {
        foreach ($pedidos as &$pedido) {
            if (($pedido['id'] ?? '') === $pedidoId) {
                $pedido['status'] = $novoStatus;
                break;
            }
        }
        unset($pedido);
        escrever_json('pedidos.json', $pedidos);
        header('Location: ' . url_site('admin/index.php'));
        exit;
    }
}

$totalVendas = 0.0;
foreach ($pedidos as $pedido) {
    $totalVendas += (float) ($pedido['total'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel administrativo | VANTROX</title>
    <link rel="stylesheet" href="<?= escapar(url_site('css/style.css')) ?>">
</head>
<body class="pagina-admin">
    <aside class="admin-menu">
        <a href="<?= escapar(url_site('index.php')) ?>" class="logo">VANTROX<span>.</span></a>
        <nav>
            <a href="<?= escapar(url_site('admin/index.php')) ?>" class="ativo">Pedidos</a>
            <a href="<?= escapar(url_site('admin/clientes.php')) ?>">Clientes</a>
            <a href="<?= escapar(url_site('conta/logout.php')) ?>">Sair</a>
        </nav>
    </aside>

    <main class="admin-conteudo">
        <header>
            <div><span>PAINEL</span><h1>Pedidos</h1></div>
            <strong><?= escapar(usuario_logado()['nome']) ?></strong>
        </header>

        <section class="admin-metricas">
            <article><span>Vendas</span><strong><?= count($pedidos) ?></strong></article>
            <article><span>Clientes</span><strong><?= count($usuarios) ?></strong></article>
            <article><span>Faturamento</span><strong>R$ <?= number_format($totalVendas, 2, ',', '.') ?></strong></article>
        </section>

        <section class="admin-tabela">
            <h2>Todos os pedidos</h2>
            <?php if (!$pedidos): ?>
                <p class="admin-vazio">Nenhum pedido realizado ainda.</p>
            <?php else: ?>
                <div class="tabela-scroll">
                    <table>
                        <thead><tr><th>Pedido</th><th>Cliente</th><th>Itens</th><th>Total</th><th>Status</th><th>Data</th></tr></thead>
                        <tbody>
                        <?php foreach ($pedidos as $pedido): ?>
                            <?php $quantidade = array_sum(array_map(fn($item) => (int) $item['quantidade'], $pedido['itens'] ?? [])); ?>
                            <tr>
                                <td><strong><?= escapar($pedido['id']) ?></strong></td>
                                <td><?= escapar($pedido['cliente']) ?><small><?= escapar($pedido['email']) ?></small></td>
                                <td><?= $quantidade ?></td>
                                <td>R$ <?= number_format((float) $pedido['total'], 2, ',', '.') ?></td>
                                <td>
                                    <form method="post" class="status-form">
                                        <input type="hidden" name="pedido_id" value="<?= escapar($pedido['id']) ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <?php foreach (['Pendente', 'Pago', 'Enviado', 'Entregue', 'Cancelado'] as $status): ?>
                                                <option value="<?= $status ?>" <?= ($pedido['status'] ?? '') === $status ? 'selected' : '' ?>><?= $status ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </td>
                                <td><?= escapar($pedido['criado_em'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
