<?php
require_once __DIR__ . '/../includes/auth.php';

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$ok, $mensagem] = cadastrar_usuario($_POST);
    if ($ok) {
        header('Location: ' . url_site('index.php'));
        exit;
    }
    $erro = $mensagem;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar conta | VANTROX</title>
    <link rel="stylesheet" href="<?= escapar(url_site('css/style.css')) ?>">
</head>
<body class="pagina-conta">
    <main class="painel-conta">
        <a href="<?= escapar(url_site('index.php')) ?>" class="logo">VANTROX<span>.</span></a>
        <h1>CRIAR SUA CONTA</h1>
        <p>Cadastre-se para acompanhar seus pedidos.</p>

        <?php if ($erro): ?><div class="mensagem erro"><?= escapar($erro) ?></div><?php endif; ?>

        <form class="formulario-conta" method="post">
            <label>Nome completo
                <input type="text" name="nome" required autocomplete="name">
            </label>
            <label>E-mail
                <input type="email" name="email" required autocomplete="email">
            </label>
            <label>Senha
                <input type="password" name="senha" required minlength="6" autocomplete="new-password">
            </label>
            <button type="submit">CADASTRAR</button>
        </form>
        <p class="link-conta">Já tem conta? <a href="login.php">Entrar</a></p>
    </main>
</body>
</html>
