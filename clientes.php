<?php
require_once __DIR__ . '/../includes/auth.php';
exigir_admin();
$usuarios = ler_json('usuarios.json');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes | VANTROX</title>
    <link rel="stylesheet" href="<?= escapar(url_site('css/style.css')) ?>">
</head>
<body class="pagina-admin">
    <aside class="admin-menu">
        <a href="<?= escapar(url_site('index.php')) ?>" class="logo">VANTROX<span>.</span></a>
        <nav>
            <a href="<?= escapar(url_site('admin/index.php')) ?>">Pedidos</a>
            <a href="<?= escapar(url_site('admin/clientes.php')) ?>" class="ativo">Clientes</a>
            <a href="<?= escapar(url_site('conta/logout.php')) ?>">Sair</a>
        </nav>
    </aside>

    <main class="admin-conteudo">
        <header>
            <div><span>PAINEL</span><h1>Clientes</h1></div>
            <strong><?= escapar(usuario_logado()['nome']) ?></strong>
        </header>

        <section class="admin-tabela">
            <h2>Clientes cadastrados</h2>
            <?php if (!$usuarios): ?>
                <p class="admin-vazio">Nenhum cliente cadastrado.</p>
            <?php else: ?>
                <div class="tabela-scroll">
                    <table>
                        <thead><tr><th>Nome</th><th>E-mail</th><th>Tipo</th><th>Cadastro</th></tr></thead>
                        <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><strong><?= escapar($usuario['nome']) ?></strong></td>
                                <td><?= escapar($usuario['email']) ?></td>
                                <td><?= escapar(ucfirst($usuario['papel'] ?? 'cliente')) ?></td>
                                <td><?= escapar($usuario['criado_em'] ?? '') ?></td>
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
